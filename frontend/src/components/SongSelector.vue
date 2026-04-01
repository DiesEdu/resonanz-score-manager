<template>
  <div class="card card-lux p-3 fade-in-up" v-if="event">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
      <div>
        <div class="fw-bold d-flex align-items-center gap-2">
          <span class="lux-dot"></span>
          Song assignments for {{ event.name }}
        </div>
        <small class="text-secondary">Attach sheet music by song, per instrument.</small>
      </div>
      <span class="badge rounded-pill lux-badge text-dark pill-soft">
        <i class="bi bi-music-note-list me-1"></i>
        {{ groupedSongs.length }} songs
      </span>
    </div>

    <div class="row g-3">
      <div class="col-lg-6">
        <div class="p-3 rounded-4 bg-white shadow-sm-sm shimmer-border">
          <div class="fw-semibold mb-2">Add / assign sheet</div>
          <div class="mb-2">
            <label class="form-label small text-uppercase text-muted">Song title *</label>
            <input
              v-model="draft.title"
              type="text"
              class="form-control pill-soft"
              placeholder="Symphony No. 5"
              list="song-options"
            />
            <datalist id="song-options">
              <option v-for="song in availableSongs" :key="song" :value="song" />
            </datalist>
            <small v-if="availableSongs.length" class="text-secondary">
              Pick from event songs or type a new title.
            </small>
          </div>
          <div class="mb-2">
            <label class="form-label small text-uppercase text-muted">Composer</label>
            <input v-model="draft.composer" type="text" class="form-control pill-soft" placeholder="L. van Beethoven" />
          </div>
          <div class="row g-2">
            <div class="col-12 col-md-6">
              <label class="form-label small text-uppercase text-muted">Instrument *</label>
              <select v-model="draft.instrumentId" class="form-select pill-soft">
                <option value="" disabled>Select instrument</option>
                <option
                  v-for="inst in availableInstruments"
                  :key="inst.id"
                  :value="inst.id"
                >
                  {{ inst.name }}
                </option>
              </select>
            </div>
            <div class="col-12 col-md-6">
              <label class="form-label small text-uppercase text-muted">File / link</label>
              <input
                v-model="draft.fileUrl"
                type="url"
                class="form-control pill-soft"
                placeholder="https://..."
              />
            </div>
          </div>
          <div class="mt-2">
            <label class="form-label small text-uppercase text-muted">Notes</label>
            <textarea
              v-model="draft.notes"
              rows="2"
              class="form-control pill-soft"
              placeholder="Edition, page numbers, cues..."
            ></textarea>
          </div>
          <div class="mt-3 d-flex justify-content-end gap-2">
            <button class="btn btn-outline-secondary btn-sm pill-soft" @click="resetDraft">
              Clear
            </button>
            <button class="btn btn-primary-custom btn-sm btn-glow" @click="submit">
              <i class="bi bi-check-lg"></i> Save assignment
            </button>
          </div>
        </div>
      </div>

      <div class="col-lg-6">
        <div class="p-3 rounded-4 bg-white shadow-sm-sm" style="min-height: 100%">
          <div class="fw-semibold mb-2">By song</div>
          <div v-if="groupedSongs.length === 0" class="text-secondary small text-center py-4 pill-soft">
            <i class="bi bi-stars"></i> No songs yet — add your first assignment.
          </div>
          <div v-else class="d-flex flex-column gap-2">
            <div
              v-for="song in groupedSongs"
              :key="song.title"
              class="p-3 rounded-4 hover-lift shimmer-border"
              :class="{ 'active-song': draft.title && draft.title === song.title }"
            >
              <div class="d-flex justify-content-between align-items-start gap-2">
                <div>
                  <div class="fw-semibold">{{ song.title }}</div>
                  <small class="text-secondary">{{ song.composer || "Unknown composer" }}</small>
                </div>
                <span class="badge bg-light text-dark pill-soft">
                  {{ song.entries.length }} part{{ song.entries.length > 1 ? "s" : "" }}
                </span>
              </div>
              <div class="d-flex flex-wrap gap-2 mt-2">
                <span
                  v-for="entry in song.entries"
                  :key="entry.id"
                  class="badge pill-soft text-dark"
                >
                  <i class="bi bi-instrument"></i>
                  {{ instrumentName(entry.instrumentId) }}
                  <a
                    v-if="entry.fileUrl"
                    :href="entry.fileUrl"
                    target="_blank"
                    class="text-decoration-none ms-1"
                    title="Open"
                  >
                    <i class="bi bi-box-arrow-up-right"></i>
                  </a>
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div v-else class="card card-lux p-4 text-center text-secondary">
    <i class="bi bi-fingerprint fs-2"></i>
    <p class="mt-2">Select an event to manage song-based assignments.</p>
  </div>
</template>

<script setup>
import { computed, reactive } from "vue";
import { masterInstruments } from "@/data/instruments";

const props = defineProps({
  event: {
    type: Object,
    default: null,
  },
  assignments: {
    // Array of sheet objects with instrumentId + title + composer + fileUrl + notes + id
    type: Array,
    default: () => [],
  },
});

const emit = defineEmits(["assign"]);

const draft = reactive({
  title: "",
  composer: "",
  instrumentId: "",
  fileUrl: "",
  notes: "",
});

const availableInstruments = computed(() => {
  if (!props.event) return [];
  return masterInstruments.filter((inst) => props.event.instrumentIds?.includes(inst.id));
});

const availableSongs = computed(() =>
  (props.event?.songs || []).filter((s) => s && s.trim().length > 0),
);

const groupedSongs = computed(() => {
  const map = new Map();
  props.assignments.forEach((entry) => {
    if (!map.has(entry.title)) {
      map.set(entry.title, { title: entry.title, composer: entry.composer, entries: [] });
    }
    map.get(entry.title).entries.push(entry);
  });
  return Array.from(map.values());
});

const instrumentName = (id) => {
  return masterInstruments.find((m) => m.id === id)?.name || id;
};

const resetDraft = () => {
  draft.title = "";
  draft.composer = "";
  draft.instrumentId = "";
  draft.fileUrl = "";
  draft.notes = "";
};

const submit = () => {
  if (!draft.title || !draft.instrumentId) return;
  emit("assign", {
    id: "song_" + Date.now(),
    title: draft.title.trim(),
    composer: draft.composer.trim(),
    instrumentId: draft.instrumentId,
    song: draft.title.trim(),
    fileUrl: draft.fileUrl.trim(),
    notes: draft.notes.trim(),
  });
  resetDraft();
};
</script>

<style scoped>
.lux-dot {
  width: 12px;
  height: 12px;
  border-radius: 50%;
  background: linear-gradient(120deg, #c44536, #d8a84f);
  box-shadow: 0 0 0 6px rgba(196, 69, 54, 0.1);
}

.active-song {
  border: 1px solid rgba(196, 69, 54, 0.35);
}

.shadow-sm-sm {
  box-shadow: 0 10px 24px -20px rgba(0, 0, 0, 0.35);
}
</style>
