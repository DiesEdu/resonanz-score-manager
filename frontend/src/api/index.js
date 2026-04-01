const API_BASE = import.meta.env.VITE_API_BASE || "http://localhost:8000/api";

async function request(path, options = {}) {
  const res = await fetch(`${API_BASE}${path}`, {
    headers: {
      "Content-Type": "application/json",
    },
    ...options,
  });

  let data = null;
  try {
    data = await res.json();
  } catch (_) {
    data = null;
  }

  if (!res.ok) {
    const message = data?.error || res.statusText || "Request failed";
    throw new Error(message);
  }

  return data;
}

export const api = {
  getEvents() {
    return request("/events");
  },
  getEvent(id) {
    return request(`/events/${id}`);
  },
  createEvent(payload) {
    return request("/events", {
      method: "POST",
      body: JSON.stringify(payload),
    });
  },
  updateEvent(id, payload) {
    return request(`/events/${id}`, {
      method: "PUT",
      body: JSON.stringify(payload),
    });
  },
  deleteEvent(id) {
    return request(`/events/${id}`, { method: "DELETE" });
  },
  addSheet(eventId, instrumentId, payload) {
    return request(`/events/${eventId}/instruments/${instrumentId}/sheets`, {
      method: "POST",
      body: JSON.stringify(payload),
    });
  },
  deleteSheet(eventId, instrumentId, sheetId) {
    return request(`/events/${eventId}/instruments/${instrumentId}/sheets/${sheetId}`, {
      method: "DELETE",
    });
  },
  getInstruments() {
    return request("/instruments");
  },
};

export default api;
