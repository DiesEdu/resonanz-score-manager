<template>
  <div class="card-custom card card-lux p-3 mb-4 fade-in-up">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h5 class="mb-0 fw-bold"><i class="bi bi-calendar-event me-2"></i>Orchestra Events</h5>
      <button class="btn btn-sm btn-soft" @click="$emit('open-create-modal')">
        <i class="bi bi-plus-lg"></i> New Event
      </button>
    </div>

    <div v-if="events.length === 0" class="text-center py-4 text-muted">
      <i class="bi bi-inbox fs-1"></i>
      <p class="mt-2">No events yet. Create one!</p>
    </div>

    <div v-else>
      <div
        v-for="ev in events"
        :key="ev.id"
        class="event-card card mb-3 p-3 hover-lift shimmer-border"
        :class="{ 'active-event': selectedEventId === ev.id }"
        @click="$emit('select-event', ev.id)"
      >
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <h6 class="fw-bold mb-1">{{ ev.name }}</h6>
            <div class="small text-secondary">
              <i class="bi bi-music-note"></i> {{ ev.songsCount }} songs ·
              <i class="bi bi-instruments"></i> {{ ev.instrumentIds?.length || 0 }} instruments
            </div>
          </div>
          <div class="dropdown" @click.stop>
            <button class="btn btn-sm btn-link text-secondary p-0" data-bs-toggle="dropdown">
              <i class="bi bi-three-dots-vertical"></i>
            </button>
            <ul class="dropdown-menu">
              <li>
                <a class="dropdown-item" href="#" @click.prevent="$emit('edit-event', ev)">
                  <i class="bi bi-pencil"></i> Edit
                </a>
              </li>
              <li>
                <a
                  class="dropdown-item text-danger"
                  href="#"
                  @click.prevent="$emit('delete-event', ev.id)"
                >
                  <i class="bi bi-trash"></i> Delete
                </a>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  events: {
    type: Array,
    required: true,
  },
  selectedEventId: {
    type: String,
    default: null,
  },
});

defineEmits(["open-create-modal", "select-event", "edit-event", "delete-event"]);
</script>

<style scoped>
.event-card {
  cursor: pointer;
  transition: 0.25s ease;
  border: 1px solid rgba(206, 178, 122, 0.35);
  background: linear-gradient(135deg, #fffefe, #faf5ed);
  border-left: 5px solid #c44536;
}

.event-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 24px 32px -22px rgba(0, 0, 0, 0.28);
}

.active-event {
  border-color: rgba(196, 69, 54, 0.45);
  box-shadow: 0 0 0 2px rgba(196, 69, 54, 0.15);
}

.btn-soft {
  background: #f1ede8;
  border-radius: 40px;
  padding: 6px 16px;
  font-weight: 500;
  border: none;
}

.btn-soft:hover {
  background: #e5ddd2;
}
</style>
