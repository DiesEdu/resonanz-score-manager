<template>
  <div class="card-custom card card-lux p-3 fade-in-up" v-if="instrument">
    <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
      <div>
        <h5 class="fw-bold mb-0">
          <i class="bi bi-file-music"></i> {{ instrument.name }} sheet music
        </h5>
        <small class="text-secondary">
          Event: {{ event?.name }} · {{ event?.songsCount }} songs total
        </small>
      </div>
      <button
        class="btn btn-primary-custom btn-sm mt-2 mt-sm-0 btn-glow"
        @click="$emit('open-sheet-modal')"
      >
        <i class="bi bi-plus-circle"></i> Add sheet music
      </button>
    </div>

    <div v-if="programSongs.length === 0 && sheets.length === 0" class="empty-sheet-placeholder rounded-3 pill-soft">
      <i class="bi bi-music-note-list fs-3"></i>
      <div>No sheet music assigned yet for {{ instrument.name }}.</div>
      <small>Click "Add sheet music" to upload or link a score.</small>
    </div>

    <div v-else>
      <div
        v-for="song in programSongs"
        :key="song"
        class="sheet-row hover-lift shimmer-border mb-3"
      >
        <div class="d-flex justify-content-between align-items-center flex-wrap">
          <div>
            <div class="fw-semibold">{{ song || "Untitled song" }}</div>
            <small class="text-secondary">Assign this instrument's part for the song.</small>
          </div>
          <button class="btn btn-sm btn-primary-custom btn-glow" @click="openForSong(song)">
            <i class="bi bi-plus-circle"></i> Add part
          </button>
        </div>

        <div v-if="songSheets(song).length === 0" class="text-secondary small mt-2">
          No part yet; leave empty if this instrument rests.
        </div>
        <div
          v-else
          class="d-flex flex-column gap-2 mt-2"
        >
          <div
            v-for="sheet in songSheets(song)"
            :key="sheet.id"
            class="d-flex justify-content-between align-items-center flex-wrap bg-white rounded-3 p-2 border"
          >
            <div>
              <div class="fw-semibold">{{ sheet.title }}</div>
              <div class="small text-secondary">
                {{ sheet.composer || "Unknown composer" }}
                <span v-if="sheet.notes" class="ms-2">
                  <i class="bi bi-chat"></i> {{ sheet.notes }}
                </span>
              </div>
            </div>
            <div class="d-flex align-items-center gap-2">
              <a
                v-if="sheet.fileUrl"
                :href="sheet.fileUrl"
                target="_blank"
                class="btn btn-sm btn-outline-secondary"
              >
                <i class="bi bi-eye"></i>
              </a>
              <button class="btn btn-sm btn-link text-danger" @click="$emit('remove-sheet', sheet.id)">
                <i class="bi bi-trash"></i>
              </button>
            </div>
          </div>
        </div>
      </div>

      <div v-if="unassignedSheets.length" class="mt-3">
        <div class="fw-semibold mb-2">Unassigned to a program song</div>
        <div
          v-for="sheet in unassignedSheets"
          :key="sheet.id"
          class="sheet-row d-flex justify-content-between align-items-center hover-lift shimmer-border"
        >
          <div class="d-flex align-items-center gap-3 flex-wrap">
            <i class="bi bi-file-pdf-fill text-danger fs-5"></i>
            <div>
              <div class="fw-semibold">{{ sheet.title }}</div>
              <div class="small text-secondary">
                {{ sheet.composer || "Unknown composer" }}
                <span v-if="sheet.notes" class="ms-2">
                  <i class="bi bi-chat"></i> {{ sheet.notes }}
                </span>
              </div>
            </div>
          </div>
          <div>
            <a
              v-if="sheet.fileUrl"
              :href="sheet.fileUrl"
              target="_blank"
              class="btn btn-sm btn-outline-secondary me-2"
            >
              <i class="bi bi-eye"></i> Preview
            </a>
            <button class="btn btn-sm btn-link text-danger" @click="$emit('remove-sheet', sheet.id)">
              <i class="bi bi-trash"></i>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div v-else class="card-custom card p-4 text-center text-secondary mt-3">
    <i class="bi bi-fingerprint fs-2"></i>
    <p class="mt-2">Select an instrument from the list to manage its sheet music</p>
  </div>
</template>

<script setup>
import { computed } from "vue";

const props = defineProps({
  event: {
    type: Object,
    default: null,
  },
  instrument: {
    type: Object,
    default: null,
  },
  sheets: {
    type: Array,
    default: () => [],
  },
});

const emit = defineEmits(["open-sheet-modal", "remove-sheet"]);

const programSongs = computed(() => {
  if (!props.event || !Array.isArray(props.event.songs)) return [];
  return props.event.songs.filter((s) => s && s.trim().length > 0);
});

const songSheets = (songName) => props.sheets.filter((s) => s.song === songName);
const unassignedSheets = computed(() => props.sheets.filter((s) => !s.song));

const openForSong = (songName) => {
  emit("open-sheet-modal", songName);
};
</script>

<style scoped>
.sheet-row {
  background: linear-gradient(120deg, #fffdf9, #f5ede2);
  border-radius: 1rem;
  padding: 12px 16px;
  margin-bottom: 10px;
  border: 1px solid rgba(206, 178, 122, 0.35);
  transition: 0.2s ease;
}

.empty-sheet-placeholder {
  color: #988872;
  font-style: italic;
  padding: 24px;
  text-align: center;
  border: 1px dashed rgba(206, 178, 122, 0.55);
}
</style>
