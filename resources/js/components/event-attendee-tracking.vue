<template>
  <div class="container mt-4">
    <h2>Event Anwesenheits Management</h2>
    <div class="row">
      <!-- Event List -->
      <div class="col-md-4 mb-3">
        <h5>Events</h5>
        <input
          type="text"
          class="form-control mb-3"
          v-model="eventSearch"
          placeholder="Nach Events suchen..."
          @input="fetchEvents"
        />
        <div class="list-group overflow-auto scroll-box">
          <div
            v-for="event in events"
            :key="event.id"
            class="list-group-item"
            :class="{ 'active': event.id === selectedEvent?.id }"
            @click="selectEvent(event)"
          >
            {{ event.name }} ({{ formatDate(event.date) }})
          </div>
        </div>
        <!-- Pagination -->
        <div class="mt-3">
          <ul class="pagination justify-content-center">
            <li class="page-item" :class="{ disabled: eventCurrentPage === 1 }">
              <button class="page-link" @click="goToEventPage(eventCurrentPage - 1)">«</button>
            </li>
            <li
              v-for="page in pagesToShow(eventTotalPages, eventCurrentPage)"
              :key="page"
              :class="['page-item', { active: eventCurrentPage === page }]"
            >
              <button class="page-link" @click="goToEventPage(page)">
                {{ page }}
              </button>
            </li>
            <li class="page-item" :class="{ disabled: eventCurrentPage === eventTotalPages }">
              <button class="page-link" @click="goToEventPage(eventCurrentPage + 1)">»</button>
            </li>
          </ul>
        </div>
      </div>

      <!-- User List -->
      <div class="col-md-4 mb-3">
        <h5>Benutzer</h5>
        <input
          type="text"
          class="form-control mb-3"
          v-model="userSearch"
          placeholder="Nach Benutzer suchen..."
          @input="fetchUsers"
        />
        <div class="list-group overflow-auto scroll-box">
          <div
            v-for="user in users"
            :key="user.id"
            class="list-group-item"
            :class="{ 'active': user.id === draggedUser?.id }"
            draggable="true"
            @dragstart="dragUserStart(user, $event)"
          >
            {{ user.name }} ({{ user.email }})
          </div>
        </div>
        <!-- Pagination -->
        <div class="mt-3">
          <ul class="pagination justify-content-center">
            <li class="page-item" :class="{ disabled: currentPage === 1 }">
              <button class="page-link" @click="goToPage(currentPage - 1)">«</button>
            </li>
            <li
              v-for="page in pagesToShow(totalPages, currentPage)"
              :key="page"
              :class="['page-item', { active: currentPage === page }]"
            >
              <button class="page-link" @click="goToPage(page)">
                {{ page }}
              </button>
            </li>
            <li class="page-item" :class="{ disabled: currentPage === totalPages }">
              <button class="page-link" @click="goToPage(currentPage + 1)">»</button>
            </li>
          </ul>
        </div>
      </div>

      <!-- Attendee and Trash Column -->
      <div class="col-md-4 mb-3 d-flex flex-column">
        <!-- Attendee List -->
        <div class="flex-grow-1">
          <h5>Anwesenheit für "{{ selectedEvent?.name || 'No Event Selected' }}"</h5>
          <input
          type="text"
          class="form-control mb-3"
          style="visibility: hidden;"
        />
          <div
            class="list-group border p-3 overflow-auto"
            style="min-height: 350px;"
            @dragover.prevent
            @drop="dropUser"
          >
            <div
              v-for="attendee in attendees"
              :key="attendee.id"
              class="list-group-item"
              draggable="true"
              @dragstart="dragUserToTrash(attendee, $event)"
            >
              {{ attendee.name }} ({{ attendee.email }})
            </div>
          </div>
        </div>

        <!-- Trash Area -->
        <div
          class="mt-3 border p-3 text-center trash-area"
          style="min-height: 100px;"
          @dragover.prevent
          @drop="removeUserFromEvent"
        >
          <svg
            xmlns="http://www.w3.org/2000/svg"
            width="32"
            height="32"
            fill="currentColor"
            class="bi bi-trash text-danger mb-2"
            viewBox="0 0 16 16"
          >
            <path
              d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0z"
            />
            <path
              d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4zM2.5 3h11V2h-11z"
            />
          </svg>
          Hierhin ziehen um Benutzer zu entfernen
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from "vue";
import axios from "axios";

const events = ref([]);
const users = ref([]);
const attendees = ref([]);
const selectedEvent = ref(null);
const draggedUser = ref(null);

const eventSearch = ref("");
const userSearch = ref("");
const currentPage = ref(1);
const totalPages = ref(1);
const eventCurrentPage = ref(1);
const eventTotalPages = ref(1);
const maxItemsPerPage = 10;

// Format date
const formatDate = (date) => new Date(date).toLocaleDateString();

const fetchEvents = async () => {
  try {
    const { data } = await axios.get("/api/events", {
      params: { search: eventSearch.value, page: eventCurrentPage.value, per_page: maxItemsPerPage },
    });
    events.value = data.events || [];
    eventTotalPages.value = Math.ceil((data.total || 0) / maxItemsPerPage);
    if (users.value.length < events.value.length) {
      await fetchUsers();
    }
    // Ensure at least one event is selected
    if (events.value.length > 0 && !selectedEvent.value) {
      selectEvent(events.value[0]);
    }
  } catch (error) {
    console.error("Error fetching events:", error);
    events.value = [];
    eventTotalPages.value = 1;
  }
};

const fetchUsers = async () => {
  try {
    const { data } = await axios.get("/api/users", {
      params: { search: userSearch.value, page: currentPage.value, per_page: maxItemsPerPage },
    });
    users.value = data.users || [];
    totalPages.value = Math.ceil((data.total || 0) / maxItemsPerPage);
  } catch (error) {
    console.error("Error fetching users:", error);
    users.value = [];
    totalPages.value = 1;
  }
};

// Fetch attendees
const fetchAttendees = async () => {
  if (!selectedEvent.value) return;
  const { data } = await axios.get(`/api/events/${selectedEvent.value.id}/attendees`);
  attendees.value = data.attendees;
};

// Drag events
const dragUserStart = (user, event) => {
  draggedUser.value = user;
  event.dataTransfer.setData("text/plain", user.id);
};

const dragUserToTrash = (user, event) => {
  draggedUser.value = user;
  event.dataTransfer.setData("text/plain", user.id);
};

const dropUser = async (event) => {
  event.preventDefault();
  if (!draggedUser.value || !selectedEvent.value) return;

  // Check if user is already in attendees
  const alreadyAdded = attendees.value.some((attendee) => attendee.id === draggedUser.value.id);
  if (alreadyAdded) {
    console.warn("User already added to this event.");
    draggedUser.value = null;
    return;
  }

  try {
    // Send request to backend
    await axios.post(`/api/events/${selectedEvent.value.id}/attendees`, {
      user_id: draggedUser.value.id,
    });

    // Add user to attendees list
    attendees.value.push(draggedUser.value);
  } catch (error) {
    console.error("Error adding user to event:", error);
  } finally {
    draggedUser.value = null;
  }
};

const removeUserFromEvent = async (event) => {
  event.preventDefault();
  if (draggedUser.value && selectedEvent.value) {
    await axios.delete(`/api/events/${selectedEvent.value.id}/attendees`, {
      data: { user_id: draggedUser.value.id },
    });
    attendees.value = attendees.value.filter((u) => u.id !== draggedUser.value.id);
  }
  draggedUser.value = null;
};

// Pagination handlers
const goToEventPage = (page) => {
  if (page >= 1 && page <= eventTotalPages.value) {
    eventCurrentPage.value = page;
    console.log("test");
        fetchEvents();
  }
};

const goToPage = (page) => {
  if (page >= 1 && page <= totalPages.value) {
    currentPage.value = page;
    fetchUsers();
  }
};

const selectEvent = (event) => {
  selectedEvent.value = event;
  fetchAttendees();
};

onMounted(() => {
  fetchEvents();
  fetchUsers();
});

// Ensure at least one event is selected on page load
watch(events, (newEvents) => {
  if (newEvents.length > 0 && !selectedEvent.value) {
    selectEvent(newEvents[0]);
  }
});

// Show only 4 pages max in the pagination
const pagesToShow = (totalPages, currentPage) => {
  const maxPages = 4;
  let startPage = Math.max(1, currentPage - 1);
  let endPage = Math.min(totalPages, startPage + maxPages - 1);

  if (endPage - startPage < maxPages - 1) {
    startPage = Math.max(1, endPage - maxPages + 1);
  }

  const pages = [];
  for (let page = startPage; page <= endPage; page++) {
    pages.push(page);
  }
  return pages;
};
</script>

<style scoped>
.scroll-box {
  max-height: 400px;
  overflow-y: auto;
}
</style>