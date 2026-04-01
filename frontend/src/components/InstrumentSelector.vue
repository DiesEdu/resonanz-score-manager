<template>
  <div class="card-custom card card-lux p-3 mb-4 fade-in-up hover-lift">
    <h6 class="fw-bold mb-3">
      <i class="bi bi-grid-3x3-gap-fill"></i> Instrumentarium · {{ event?.name }}
    </h6>

    <div v-if="event">
      <!-- Woodwind -->
      <div class="instrument-group-title"><i class="bi bi-tree"></i> Woodwind</div>
      <div class="d-flex flex-wrap gap-2 mb-2">
        <button
          v-for="inst in getInstrumentsByFamily('woodwind')"
          :key="inst.id"
          @click="$emit('select-instrument', inst.id)"
          class="instrument-badge pulse-hover"
          :class="{ 'active-instrument': selectedInstrumentId === inst.id }"
        >
          {{ inst.name }}
        </button>
        <span v-if="getInstrumentsByFamily('woodwind').length === 0" class="text-muted small"
          >—</span
        >
      </div>

      <!-- Strings -->
      <div class="instrument-group-title"><i class="bi bi-grid"></i> Strings</div>
      <div class="d-flex flex-wrap gap-2 mb-2">
        <button
          v-for="inst in getInstrumentsByFamily('string')"
          :key="inst.id"
          @click="$emit('select-instrument', inst.id)"
          class="instrument-badge pulse-hover"
          :class="{ 'active-instrument': selectedInstrumentId === inst.id }"
        >
          {{ inst.name }}
        </button>
        <span v-if="getInstrumentsByFamily('string').length === 0" class="text-muted small">—</span>
      </div>

      <!-- Brass -->
      <div class="instrument-group-title"><i class="bi bi-brightness-alt-high"></i> Brass</div>
      <div class="d-flex flex-wrap gap-2 mb-2">
        <button
          v-for="inst in getInstrumentsByFamily('brass')"
          :key="inst.id"
          @click="$emit('select-instrument', inst.id)"
          class="instrument-badge pulse-hover"
          :class="{ 'active-instrument': selectedInstrumentId === inst.id }"
        >
          {{ inst.name }}
        </button>
        <span v-if="getInstrumentsByFamily('brass').length === 0" class="text-muted small">—</span>
      </div>

      <!-- Timpani -->
      <div class="instrument-group-title"><i class="bi bi-drum"></i> Percussion</div>
      <div class="d-flex flex-wrap gap-2">
        <button
          v-for="inst in getInstrumentsByFamily('timpani')"
          :key="inst.id"
          @click="$emit('select-instrument', inst.id)"
          class="instrument-badge pulse-hover"
          :class="{ 'active-instrument': selectedInstrumentId === inst.id }"
        >
          {{ inst.name }}
        </button>
        <span v-if="getInstrumentsByFamily('timpani').length === 0" class="text-muted small"
          >—</span
        >
      </div>
    </div>
  </div>
</template>

<script setup>
import { masterInstruments } from "@/data/instruments";

const props = defineProps({
  event: {
    type: Object,
    default: null,
  },
  selectedInstrumentId: {
    type: String,
    default: null,
  },
});

defineEmits(["select-instrument"]);

const getInstrumentsByFamily = (family) => {
  if (!props.event) return [];
  const availableIds = props.event.instrumentIds;
  return masterInstruments.filter(
    (inst) => inst.family === family && availableIds.includes(inst.id),
  );
};
</script>

<style scoped>
.instrument-group-title {
  font-weight: 600;
  color: #7b6e5d;
  font-size: 0.75rem;
  letter-spacing: 1px;
  text-transform: uppercase;
  margin-top: 12px;
  margin-bottom: 8px;
}

.instrument-badge {
  background: linear-gradient(120deg, #f8f2ea, #f1e6d5);
  color: #3c2b20;
  border-radius: 40px;
  padding: 6px 14px;
  font-size: 0.8rem;
  font-weight: 500;
  transition: 0.15s ease;
  border: 1px solid rgba(206, 178, 122, 0.35);
  cursor: pointer;
}

.instrument-badge:hover {
  background: linear-gradient(120deg, #fff, #f3e7d6);
}

.active-instrument {
  background: #c44536;
  color: white;
  box-shadow: 0 2px 10px rgba(196, 69, 54, 0.35);
  border-color: rgba(196, 69, 54, 0.4);
}
</style>
