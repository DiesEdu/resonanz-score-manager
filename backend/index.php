<?php
require __DIR__ . '/bootstrap.php';

cors($config);

try {
    $pdo = db_connect($config);
} catch (Throwable $e) {
    json_response(['error' => 'Database connection failed', 'details' => $e->getMessage()], 500);
}

$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Normalise leading slash and strip optional /api prefix
$path = preg_replace('#^/+#', '/', $path);
$path = preg_replace('#^/api#', '', $path);

$segments = array_values(array_filter(explode('/', $path)));

switch ($segments[0] ?? '') {
    case 'events':
        handleEvents($method, $segments, $pdo);
        break;
    case 'instruments':
        handleInstruments();
        break;
    default:
        json_response(['message' => 'Sheet Music Manager API'], 200);
}

// --- Handlers ---
function handleInstruments(): void
{
    $instruments = require __DIR__ . '/instruments.php';
    json_response($instruments);
}

function handleEvents(string $method, array $segments, PDO $pdo): void
{
    if (count($segments) === 1) {
        if ($method === 'GET') {
            $events = loadEvents($pdo);
            json_response($events);
        }
        if ($method === 'POST') {
            $payload = readJson();
            $eventId = createEvent($pdo, $payload);
            $event = loadEvents($pdo, $eventId);
            json_response($event, 201);
        }
    }

    $eventId = $segments[1] ?? null;
    if (!$eventId) {
        json_response(['error' => 'Event ID required'], 400);
    }

    // /events/{id}
    if (count($segments) === 2) {
        if ($method === 'GET') {
            $event = loadEvents($pdo, $eventId);
            if (!$event) json_response(['error' => 'Not found'], 404);
            json_response($event);
        }
        if ($method === 'PUT') {
            $payload = readJson();
            updateEvent($pdo, $eventId, $payload);
            $event = loadEvents($pdo, $eventId);
            json_response($event);
        }
        if ($method === 'DELETE') {
            deleteEvent($pdo, $eventId);
            json_response(['status' => 'deleted']);
        }
    }

    // /events/{id}/instruments/{instrumentId}/sheets
    if (($segments[2] ?? '') === 'instruments' && ($segments[4] ?? '') === 'sheets') {
        $instrumentId = $segments[3] ?? null;
        if (!$instrumentId) json_response(['error' => 'Instrument ID required'], 400);

        if (count($segments) === 5 && $method === 'POST') {
            $payload = readJson();
            $sheet = addSheet($pdo, $eventId, $instrumentId, $payload);
            json_response($sheet, 201);
        }

        // /events/{id}/instruments/{instrumentId}/sheets/{sheetId}
        if (count($segments) === 6 && $method === 'DELETE') {
            $sheetId = $segments[5];
            deleteSheet($pdo, $eventId, $instrumentId, $sheetId);
            json_response(['status' => 'deleted']);
        }
    }

    json_response(['error' => 'Route not found'], 404);
}

// --- Data helpers ---
function readJson(): array
{
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    if (!is_array($data)) {
        json_response(['error' => 'Invalid JSON payload'], 400);
    }
    return $data;
}

function loadEvents(PDO $pdo, ?string $id = null)
{
    $query = 'SELECT id, name, songs_count, created_at FROM events';
    $params = [];
    if ($id) {
        $query .= ' WHERE id = ?';
        $params[] = $id;
    }
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $events = $stmt->fetchAll();
    if (!$events) return $id ? null : [];

    $eventIds = array_column($events, 'id');

    // Songs
    $songStmt = $pdo->prepare('SELECT event_id, position, title FROM event_songs WHERE event_id IN (' . placeholders($eventIds) . ') ORDER BY position ASC');
    $songStmt->execute($eventIds);
    $songsByEvent = [];
    foreach ($songStmt as $row) {
        $songsByEvent[$row['event_id']][$row['position']] = $row['title'];
    }

    // Instruments
    $instStmt = $pdo->prepare('SELECT event_id, instrument_id FROM event_instruments WHERE event_id IN (' . placeholders($eventIds) . ')');
    $instStmt->execute($eventIds);
    $instrumentsByEvent = [];
    foreach ($instStmt as $row) {
        $instrumentsByEvent[$row['event_id']][] = $row['instrument_id'];
    }

    // Sheets
    $sheetStmt = $pdo->prepare('SELECT id, event_id, instrument_id, title, composer, file_url, notes, song_title, created_at FROM sheets WHERE event_id IN (' . placeholders($eventIds) . ')');
    $sheetStmt->execute($eventIds);
    $sheetsByEvent = [];
    foreach ($sheetStmt as $row) {
        $sheet = [
            'id' => $row['id'],
            'title' => $row['title'],
            'composer' => $row['composer'],
            'fileUrl' => $row['file_url'],
            'notes' => $row['notes'],
            'song' => $row['song_title'],
        ];
        $sheetsByEvent[$row['event_id']][$row['instrument_id']][] = $sheet;
    }

    $result = [];
    foreach ($events as $ev) {
        $eventId = $ev['id'];
        $songsList = $songsByEvent[$eventId] ?? [];
        ksort($songsList);
        $result[] = [
            'id' => $eventId,
            'name' => $ev['name'],
            'songsCount' => (int) $ev['songs_count'],
            'songs' => array_values($songsList),
            'instrumentIds' => $instrumentsByEvent[$eventId] ?? [],
            'sheets' => $sheetsByEvent[$eventId] ?? new stdClass(),
        ];
    }

    return $id ? $result[0] : $result;
}

function createEvent(PDO $pdo, array $payload): string
{
    $name = trim($payload['name'] ?? '');
    $songsCount = (int) ($payload['songsCount'] ?? 0);
    $songs = $payload['songs'] ?? [];
    $instrumentIds = $payload['instrumentIds'] ?? [];

    if ($name === '' || $songsCount < 1) {
        json_response(['error' => 'Name and songsCount are required'], 422);
    }

    $eventId = 'ev_' . uniqid();

    $pdo->beginTransaction();
    $stmt = $pdo->prepare('INSERT INTO events (id, name, songs_count) VALUES (?, ?, ?)');
    $stmt->execute([$eventId, $name, $songsCount]);

    insertSongs($pdo, $eventId, $songs, $songsCount);
    insertInstruments($pdo, $eventId, $instrumentIds);

    $pdo->commit();
    return $eventId;
}

function updateEvent(PDO $pdo, string $eventId, array $payload): void
{
    $stmt = $pdo->prepare('SELECT id FROM events WHERE id = ?');
    $stmt->execute([$eventId]);
    if (!$stmt->fetch()) json_response(['error' => 'Not found'], 404);

    $name = trim($payload['name'] ?? '');
    $songsCount = (int) ($payload['songsCount'] ?? 0);
    $songs = $payload['songs'] ?? [];
    $instrumentIds = $payload['instrumentIds'] ?? [];

    if ($name === '' || $songsCount < 1) {
        json_response(['error' => 'Name and songsCount are required'], 422);
    }

    $pdo->beginTransaction();
    $pdo->prepare('UPDATE events SET name = ?, songs_count = ? WHERE id = ?')
        ->execute([$name, $songsCount, $eventId]);

    $pdo->prepare('DELETE FROM event_songs WHERE event_id = ?')->execute([$eventId]);
    insertSongs($pdo, $eventId, $songs, $songsCount);

    // Update instruments and prune sheets for removed instruments
    $oldInst = $pdo->prepare('SELECT instrument_id FROM event_instruments WHERE event_id = ?');
    $oldInst->execute([$eventId]);
    $old = array_column($oldInst->fetchAll(), 'instrument_id');

    $pdo->prepare('DELETE FROM event_instruments WHERE event_id = ?')->execute([$eventId]);
    insertInstruments($pdo, $eventId, $instrumentIds);

    $removed = array_diff($old, $instrumentIds);
    if ($removed) {
        $del = $pdo->prepare('DELETE FROM sheets WHERE event_id = ? AND instrument_id IN (' . placeholders($removed) . ')');
        $del->execute(array_merge([$eventId], array_values($removed)));
    }

    $pdo->commit();
}

function deleteEvent(PDO $pdo, string $eventId): void
{
    $pdo->beginTransaction();
    $pdo->prepare('DELETE FROM sheets WHERE event_id = ?')->execute([$eventId]);
    $pdo->prepare('DELETE FROM event_songs WHERE event_id = ?')->execute([$eventId]);
    $pdo->prepare('DELETE FROM event_instruments WHERE event_id = ?')->execute([$eventId]);
    $pdo->prepare('DELETE FROM events WHERE id = ?')->execute([$eventId]);
    $pdo->commit();
}

function addSheet(PDO $pdo, string $eventId, string $instrumentId, array $payload): array
{
    $title = trim($payload['title'] ?? '');
    if ($title === '') json_response(['error' => 'Title is required'], 422);

    $sheetId = $payload['id'] ?? ('sheet_' . uniqid());
    $composer = trim($payload['composer'] ?? '');
    $fileUrl = trim($payload['fileUrl'] ?? '');
    $notes = trim($payload['notes'] ?? '');
    $song = trim($payload['song'] ?? '');

    // Validate event & instrument membership
    $stmt = $pdo->prepare('SELECT 1 FROM event_instruments WHERE event_id = ? AND instrument_id = ?');
    $stmt->execute([$eventId, $instrumentId]);
    if (!$stmt->fetch()) json_response(['error' => 'Instrument not part of event'], 422);

    $pdo->prepare('INSERT INTO sheets (id, event_id, instrument_id, title, composer, file_url, notes, song_title) VALUES (?, ?, ?, ?, ?, ?, ?, ?)')
        ->execute([$sheetId, $eventId, $instrumentId, $title, $composer, $fileUrl, $notes, $song]);

    return [
        'id' => $sheetId,
        'title' => $title,
        'composer' => $composer,
        'fileUrl' => $fileUrl,
        'notes' => $notes,
        'song' => $song,
        'instrumentId' => $instrumentId,
    ];
}

function deleteSheet(PDO $pdo, string $eventId, string $instrumentId, string $sheetId): void
{
    $pdo->prepare('DELETE FROM sheets WHERE id = ? AND event_id = ? AND instrument_id = ?')
        ->execute([$sheetId, $eventId, $instrumentId]);
}

function insertSongs(PDO $pdo, string $eventId, array $songs, int $count): void
{
    $songs = array_values(array_slice($songs, 0, $count));
    $stmt = $pdo->prepare('INSERT INTO event_songs (event_id, position, title) VALUES (?, ?, ?)');
    for ($i = 0; $i < $count; $i++) {
        $title = isset($songs[$i]) ? trim((string) $songs[$i]) : '';
        $stmt->execute([$eventId, $i, $title]);
    }
}

function insertInstruments(PDO $pdo, string $eventId, array $instrumentIds): void
{
    if (!$instrumentIds) return;
    $stmt = $pdo->prepare('INSERT INTO event_instruments (event_id, instrument_id) VALUES (?, ?)');
    foreach ($instrumentIds as $instId) {
        $stmt->execute([$eventId, $instId]);
    }
}

function placeholders(array $items): string
{
    return implode(',', array_fill(0, count($items), '?'));
}
?>
