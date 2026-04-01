<template>
  <div class="modal fade" id="sheetModal" tabindex="-1" ref="modal">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content rounded-4 border-0 shadow">
        <div class="modal-header bg-light">
          <h5 class="modal-title fw-bold">Add sheet music for {{ instrument?.name }}</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Title / Piece name *</label>
            <input
              type="text"
              class="form-control"
              v-model="sheetForm.title"
              placeholder="e.g., Symphony No. 5, Flute part"
            />
          </div>

          <div class="mb-3" v-if="songs.length">
            <label class="form-label">Song (from program)</label>
            <select v-model="sheetForm.song" class="form-select">
              <option value="">— Select song —</option>
              <option v-for="song in songs" :key="song" :value="song">{{ song || "Untitled" }}</option>
            </select>
            <div class="form-text">Keep empty if this part is not tied to a program song.</div>
          </div>

          <div class="mb-3">
            <label class="form-label">Composer (optional)</label>
            <input
              type="text"
              class="form-control"
              v-model="sheetForm.composer"
              placeholder="Beethoven, Mozart, etc."
            />
          </div>

          <div class="mb-3">
            <label class="form-label">File URL / Preview link</label>
            <input
              type="url"
              class="form-control"
              v-model="sheetForm.fileUrl"
              placeholder="https://...pdf or internal link"
            />
            <div class="form-text">
              You can paste a link to PDF, Google Drive, or any sheet music resource.
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label">Notes (optional)</label>
            <textarea
              rows="2"
              class="form-control"
              v-model="sheetForm.notes"
              placeholder="e.g., annotated version, page turns..."
            ></textarea>
          </div>
        </div>

        <div class="modal-footer">
          <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button class="btn btn-primary-custom" @click="add">Add sheet music</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from "vue";
import { Modal } from "bootstrap";

const props = defineProps({
  instrument: {
    type: Object,
    default: null,
  },
  songs: {
    type: Array,
    default: () => [],
  },
  defaultSong: {
    type: String,
    default: "",
  },
});

const emit = defineEmits(["add"]);
const modal = ref(null);
let modalInstance = null;

const sheetForm = ref({
  title: "",
  song: "",
  composer: "",
  fileUrl: "",
  notes: "",
});

const add = () => {
  if (!sheetForm.value.title.trim()) {
    alert("Please enter a title for the sheet music");
    return;
  }

  emit("add", {
    id: Date.now() + "-" + Math.random().toString(36).substr(2, 6),
    ...sheetForm.value,
  });

  reset();
  hide();
};

const reset = () => {
  sheetForm.value = {
    title: "",
    song: props.defaultSong || "",
    composer: "",
    fileUrl: "",
    notes: "",
  };
};

const show = (selectedSong = "") => {
  if (!modalInstance) {
    modalInstance = new Modal(modal.value);
  }
  reset();
  if (selectedSong) {
    sheetForm.value.song = selectedSong;
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
