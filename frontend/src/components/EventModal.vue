<template>
  <div class="modal fade" id="eventModal" tabindex="-1" ref="modal">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content rounded-4 border-0 shadow-lg">
        <div class="modal-header bg-light border-0">
          <h5 class="modal-title fw-bold">
            {{ mode === "create" ? "Create new concert event" : "Edit event" }}
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label fw-semibold">Event name</label>
            <input
              type="text"
              class="form-control"
              v-model="localEvent.name"
              placeholder="e.g., Classical Concert, Spring Gala"
            />
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Number of songs / pieces</label>
            <input
              type="number"
              class="form-control"
              v-model.number="localEvent.songsCount"
              min="1"
              placeholder="e.g., 5"
            />
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Song titles</label>
            <p class="small text-secondary mb-2">
              Add the exact titles for this program. Inputs will expand to match the total songs.
            </p>
            <div class="d-flex flex-column gap-2">
              <div
                v-for="(song, idx) in localEvent.songs"
                :key="`song-${idx}`"
                class="input-group"
              >
                <span class="input-group-text bg-light">#{{ idx + 1 }}</span>
                <input
                  v-model="localEvent.songs[idx]"
                  type="text"
                  class="form-control"
                  :placeholder="`Song ${idx + 1} title`"
                />
              </div>
            </div>
            <small class="text-secondary d-block mt-1">
              Instruments will assign sheet music against these song names; leave blank if an
              instrument rests for a piece.
            </small>
          </div>

          <label class="form-label fw-semibold">Select instruments for this event</label>
          <div class="border rounded-3 p-3 bg-light" style="max-height: 280px; overflow-y: auto">
            <div v-for="family in instrumentFamilies" :key="family.name">
              <div class="fw-semibold small mt-2">{{ family.label }}</div>
              <div class="d-flex flex-wrap gap-2 mb-2">
                <div
                  v-for="inst in family.instruments"
                  :key="inst.id"
                  class="form-check form-check-inline"
                >
                  <input
                    class="form-check-input"
                    type="checkbox"
                    :value="inst.id"
                    v-model="localEvent.instrumentIds"
                  />
                  <label class="form-check-label">{{ inst.name }}</label>
                </div>
              </div>
            </div>
          </div>
          <small class="text-secondary"
            >Tip: Uncheck any instrument not used in this concert.</small
          >
        </div>

        <div class="modal-footer border-0">
          <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button class="btn btn-primary-custom" @click="save">Save Event</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from "vue";
import { Modal } from "bootstrap";
import { instrumentFamilies } from "@/data/instruments";

const props = defineProps({
  mode: {
    type: String,
    default: "create",
  },
  eventData: {
    type: Object,
    default: () => ({
      id: null,
      name: "",
      songsCount: 3,
      songs: [],
      instrumentIds: [],
    }),
  },
});

const emit = defineEmits(["save"]);
const modal = ref(null);
let modalInstance = null;

const normalizeSongs = (payload) => {
  const count = Number(payload.songsCount) || 0;
  const baseSongs = Array.isArray(payload.songs) ? [...payload.songs] : [];
  if (baseSongs.length < count) {
    return [...baseSongs, ...Array(count - baseSongs.length).fill("")];
  }
  return baseSongs.slice(0, count);
};

const localEvent = ref({ ...props.eventData, songs: normalizeSongs(props.eventData) });

watch(
  () => props.eventData,
  (newVal) => {
    localEvent.value = {
      ...newVal,
      songs: normalizeSongs(newVal),
    };
  },
  { deep: true },
);

watch(
  () => localEvent.value.songsCount,
  (newCount) => {
    localEvent.value.songs = normalizeSongs({
      ...localEvent.value,
      songsCount: newCount,
    });
  },
);

const save = () => {
  if (!localEvent.value.name.trim()) {
    alert("Please enter event name");
    return;
  }
  emit("save", { ...localEvent.value, songs: [...localEvent.value.songs] });
  hide();
};

const show = () => {
  if (!modalInstance) {
    modalInstance = new Modal(modal.value);
  }
  modalInstance.show();
};

const hide = () => {
  if (modalInstance) {
    modalInstance.hide();
  }
};

defineExpose({ show, hide });
</script>

<style scoped>
.btn-primary-custom {
  background: #c44536;
  border: none;
  border-radius: 40px;
  padding: 8px 20px;
  font-weight: 500;
  color: white;
}

.btn-primary-custom:hover {
  background: #a7372a;
}
</style>
