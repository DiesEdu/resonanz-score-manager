-- MySQL schema for Sheet Music Manager
CREATE TABLE IF NOT EXISTS events (
  id VARCHAR(64) PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  songs_count INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS event_songs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  event_id VARCHAR(64) NOT NULL,
  position INT NOT NULL,
  title VARCHAR(255) DEFAULT '',
  CONSTRAINT fk_event_songs_event FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
  INDEX idx_event_position (event_id, position)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS event_instruments (
  event_id VARCHAR(64) NOT NULL,
  instrument_id VARCHAR(64) NOT NULL,
  PRIMARY KEY (event_id, instrument_id),
  CONSTRAINT fk_event_instruments_event FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS sheets (
  id VARCHAR(64) PRIMARY KEY,
  event_id VARCHAR(64) NOT NULL,
  instrument_id VARCHAR(64) NOT NULL,
  title VARCHAR(255) NOT NULL,
  composer VARCHAR(255) DEFAULT '',
  file_url TEXT,
  notes TEXT,
  song_title VARCHAR(255) DEFAULT '',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_sheets_event FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
  INDEX idx_sheets_event_instrument (event_id, instrument_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
