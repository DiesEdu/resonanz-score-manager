<template>
  <nav class="navbar navbar-expand-lg bg-white py-3 sticky-top glass-nav fade-in-down">
    <div class="container">
      <span class="navbar-brand fs-4 d-flex align-items-center gap-2">
        <span class="lux-icon pill-soft d-inline-flex align-items-center justify-content-center">
          <i class="bi bi-music-note-beamed text-danger"></i>
        </span>
        <span>Conductor's Desk</span>
      </span>
      <span class="badge lux-badge text-dark px-3 py-2 rounded-pill pill-soft">
        <i class="bi bi-journal-bookmark-fill me-1"></i> Sheet Music Manager
      </span>
    </div>
  </nav>

  <div class="container py-4 fade-in-up">
    <div class="row g-4">
      <!-- Left Column -->
      <div class="col-lg-4">
        <EventList
          :events="events"
          :selected-event-id="selectedEventId"
          @open-create-modal="openCreateModal"
          @select-event="selectEvent"
          @edit-event="editEvent"
          @delete-event="deleteEvent"
        />
        <EventStats :event="currentEvent" />
      </div>

      <!-- Right Column -->
      <div class="col-lg-8">
        <div v-if="!currentEvent" class="card-custom card card-lux p-5 text-center hover-lift">
          <i class="bi bi-folder-symlink fs-1 text-secondary d-block mb-2"></i>
          <h5 class="mt-1 fw-semibold">No event selected</h5>
          <p class="text-secondary">
            Select an event from the left or create a new concert to manage sheet music per
            instrument.
          </p>
          <div>
            <button class="btn btn-primary-custom btn-glow" @click="openCreateModal">
              <i class="bi bi-plus-circle"></i> Create event
            </button>
          </div>
        </div>

        <div v-else>
          <InstrumentSelector
            :event="currentEvent"
            :selected-instrument-id="selectedInstrumentId"
            @select-instrument="selectInstrument"
          />
<SheetMusicManager
            :event="currentEvent"
            :instrument="selectedInstrument"
            :sheets="currentSheets"
            @open-sheet-modal="openSheetModal"
            @remove-sheet="removeSheet"
          />
          <div class="mt-3">
            <SongSelector
              :event="currentEvent"
              :assignments="songAssignments"
              @assign="assignSong"
            />
          </div>
        </div>
      </div>
    </div>
  </div>

  <footer class="text-center py-4 mt-5 text-secondary">
    <div class="container">
      Orchestra Sheet Music Manager — keep every instrument part organized for each concert.
    </div>
  </footer>

  <!-- Modals -->
  <EventModal ref="eventModalRef" :mode="modalMode" :event-data="formEvent" @save="saveEvent" />
  <SheetModal
    ref="sheetModalRef"
    :instrument="selectedInstrument"
    :songs="currentEvent?.songs || []"
    @add="addSheet"
  />
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import EventList from "./components/EventList.vue";
import EventStats from "./components/EventStats.vue";
import InstrumentSelector from "./components/InstrumentSelector.vue";
import SheetMusicManager from "./components/SheetMusicManager.vue";
import SongSelector from "./components/SongSelector.vue";
import EventModal from "./components/EventModal.vue";
import SheetModal from "./components/SheetModal.vue";
import { masterInstruments, exampleEvents } from "./data/instruments";

// State
const events = ref([]);
const selectedEventId = ref(null);
const selectedInstrumentId = ref(null);
const eventModalRef = ref(null);
const sheetModalRef = ref(null);
const modalMode = ref("create");
const formEvent = ref({
  id: null,
  name: "",
  songsCount: 3,
  songs: [],
  instrumentIds: [],
});

// Computed
const currentEvent = computed(() => {
  if (!selectedEventId.value) return null;
  return events.value.find((ev) => ev.id === selectedEventId.value) || null;
});

const selectedInstrument = computed(() => {
  if (!currentEvent.value || !selectedInstrumentId.value) return null;
  const instr = masterInstruments.find((i) => i.id === selectedInstrumentId.value);
  if (!instr) return null;
  if (!currentEvent.value.instrumentIds.includes(selectedInstrumentId.value)) return null;
  return instr;
});

const currentSheets = computed(() => {
  if (!currentEvent.value || !selectedInstrumentId.value) return [];
  const instrumentSheets = currentEvent.value.sheets || {};
  return instrumentSheets[selectedInstrumentId.value] || [];
});

const songAssignments = computed(() => {
  if (!currentEvent.value) return [];
  const instrumentSheets = currentEvent.value.sheets || {};
  return Object.entries(instrumentSheets).flatMap(([instrumentId, sheets]) =>
    (sheets || []).map((sheet) => ({
      ...sheet,
      instrumentId,
    })),
  );
});

// Methods
const loadEvents = () => {
  const stored = localStorage.getItem("orchestra_sheet_mgr");
  if (stored) {
    events.value = JSON.parse(stored);
    events.value.forEach((ev) => {
      if (!ev.sheets) ev.sheets = {};
      if (!ev.songs) ev.songs = Array(ev.songsCount || 0).fill("");
    });
  } else {
    events.value = JSON.parse(JSON.stringify(exampleEvents));
    events.value.forEach((ev) => {
      if (!ev.sheets) ev.sheets = {};
      if (!ev.songs) ev.songs = Array(ev.songsCount || 0).fill("");
    });
    saveToLocal();
  }
  if (events.value.length) {
    selectedEventId.value = events.value[0].id;
  }
};

const saveToLocal = () => {
  localStorage.setItem("orchestra_sheet_mgr", JSON.stringify(events.value));
};

const selectEvent = (id) => {
  selectedEventId.value = id;
  selectedInstrumentId.value = null;
};

const selectInstrument = (instId) => {
  if (currentEvent.value && currentEvent.value.instrumentIds.includes(instId)) {
    selectedInstrumentId.value = instId;
  } else {
    alert("This instrument is not active in current event. Edit event to add it.");
  }
};

const openCreateModal = () => {
  modalMode.value = "create";
  formEvent.value = {
    id: null,
    name: "",
    songsCount: 3,
    songs: Array(3).fill(""),
    instrumentIds: [...masterInstruments.map((i) => i.id)],
  };
  eventModalRef.value.show();
};

const editEvent = (ev) => {
  modalMode.value = "edit";
  formEvent.value = {
    id: ev.id,
    name: ev.name,
    songsCount: ev.songsCount,
    songs: ev.songs ? [...ev.songs] : Array(ev.songsCount || 0).fill(""),
    instrumentIds: [...ev.instrumentIds],
  };
  eventModalRef.value.show();
};

const saveEvent = (eventData) => {
  if (modalMode.value === "create") {
    const newEvent = {
      id: "ev_" + Date.now(),
      name: eventData.name,
      songsCount: eventData.songsCount,
      songs: (eventData.songs || [])
        .slice(0, eventData.songsCount)
        .map((s) => (s ? s.trim() : "")),
      instrumentIds: eventData.instrumentIds,
      sheets: {},
    };
    events.value.push(newEvent);
    selectedEventId.value = newEvent.id;
    selectedInstrumentId.value = null;
  } else {
    const idx = events.value.findIndex((ev) => ev.id === eventData.id);
    if (idx !== -1) {
      const oldSheets = events.value[idx].sheets || {};
      const newSheets = {};
      eventData.instrumentIds.forEach((instId) => {
        if (oldSheets[instId]) newSheets[instId] = oldSheets[instId];
        else newSheets[instId] = [];
      });
      events.value[idx] = {
        ...events.value[idx],
        name: eventData.name,
        songsCount: eventData.songsCount,
        songs: (eventData.songs || [])
          .slice(0, eventData.songsCount)
          .map((s) => (s ? s.trim() : "")),
        instrumentIds: eventData.instrumentIds,
        sheets: newSheets,
      };
      if (
        selectedInstrumentId.value &&
        !eventData.instrumentIds.includes(selectedInstrumentId.value)
      ) {
        selectedInstrumentId.value = null;
      }
    }
  }
  saveToLocal();
};

const deleteEvent = (id) => {
  if (confirm("Delete this concert event? All sheet music assignments will be lost.")) {
    events.value = events.value.filter((ev) => ev.id !== id);
    if (selectedEventId.value === id) {
      selectedEventId.value = events.value.length ? events.value[0].id : null;
      selectedInstrumentId.value = null;
    }
    saveToLocal();
  }
};

const openSheetModal = (songName = "") => {
  if (selectedInstrument.value) {
    sheetModalRef.value.show(songName);
  }
};

const addSheet = (sheetData) => {
  if (!currentEvent.value || !selectedInstrumentId.value) return;

  if (!currentEvent.value.sheets) {
    currentEvent.value.sheets = {};
  }
  if (!currentEvent.value.sheets[selectedInstrumentId.value]) {
    currentEvent.value.sheets[selectedInstrumentId.value] = [];
  }

  currentEvent.value.sheets[selectedInstrumentId.value].push(sheetData);
  saveToLocal();
};

const removeSheet = (sheetId) => {
  if (!currentEvent.value || !selectedInstrumentId.value) return;
  const sheetsArr = currentEvent.value.sheets[selectedInstrumentId.value];
  if (sheetsArr) {
    const idx = sheetsArr.findIndex((s) => s.id === sheetId);
    if (idx !== -1) {
      sheetsArr.splice(idx, 1);
      saveToLocal();
    }
  }
};

const assignSong = (payload) => {
  if (!currentEvent.value) return;
  if (!currentEvent.value.sheets) currentEvent.value.sheets = {};
  if (!currentEvent.value.sheets[payload.instrumentId]) {
    currentEvent.value.sheets[payload.instrumentId] = [];
  }
  currentEvent.value.sheets[payload.instrumentId].push({
    id: payload.id || "sheet_" + Date.now(),
    title: payload.title,
    composer: payload.composer,
    fileUrl: payload.fileUrl,
    notes: payload.notes,
  });
  saveToLocal();
};

onMounted(() => {
  loadEvents();
});
</script>

<style>
.card-custom {
  border: none;
  border-radius: 1.5rem;
  background: linear-gradient(145deg, #ffffff, #f9f3ea);
  box-shadow:
    0 18px 45px -28px rgba(0, 0, 0, 0.45),
    0 10px 22px -18px rgba(196, 69, 54, 0.25);
  border: 1px solid rgba(206, 178, 122, 0.2);
}

.lux-icon {
  width: 42px;
  height: 42px;
  background: linear-gradient(135deg, #fff, #f3e4d2);
  box-shadow:
    inset 0 1px 0 rgba(255, 255, 255, 0.85),
    0 12px 24px -18px rgba(0, 0, 0, 0.4);
}

.btn-primary-custom {
  background: linear-gradient(120deg, #c44536, #9f2e23);
  border: none;
  border-radius: 40px;
  padding: 10px 22px;
  font-weight: 600;
  color: white;
  transition: all 0.25s ease;
}

.btn-primary-custom:hover {
  background: linear-gradient(120deg, #d05444, #b13b2f);
  color: white;
  transform: translateY(-2px);
}

.navbar-brand {
  font-weight: 700;
  letter-spacing: 0.4px;
}

footer {
  font-size: 0.85rem;
  border-top: 1px solid #e9e2d8;
}
</style>


