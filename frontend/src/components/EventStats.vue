<template>
  <div class="card-custom card card-lux p-3 fade-in-up hover-lift" v-if="event">
    <div class="d-flex align-items-center gap-2 mb-2">
      <i class="bi bi-info-circle-fill text-secondary"></i>
      <span class="fw-semibold">Active event</span>
    </div>
    <div class="fw-bold fs-5">{{ event.name }}</div>
    <div class="text-secondary small">🎵 {{ event.songsCount }} pieces in program</div>
    <hr class="my-2" />
    <div class="small">
      <span class="fw-semibold">Instruments assigned:</span> {{ event.instrumentIds?.length || 0 }}
    </div>
  </div>
</template>

<script setup>
defineProps({
  event: {
    type: Object,
    default: null,
  },
});
</script>

<style scoped>
.card-custom {
  border: none;
  border-radius: 1.5rem;
  background: linear-gradient(145deg, #ffffff, #f9f3ea);
  border: 1px solid rgba(206, 178, 122, 0.25);
  box-shadow:
    0 18px 45px -28px rgba(0, 0, 0, 0.45),
    0 10px 22px -18px rgba(196, 69, 54, 0.25);
}
</style>
