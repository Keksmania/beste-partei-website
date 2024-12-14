<template>
  <div class="container mt-4">
    <h2>Events verwalten</h2>
    <div class="row">
      <!-- Event List -->
      <div class="col-md-12">
        <input
          type="text"
          class="form-control mb-3"
          v-model="searchQuery"
          placeholder="Events suchen..."
          @input="onSearchInput"
        />
        <table class="table table-striped table-responsive">
          <thead>
            <tr>
              <th scope="col">Event Name</th>
              <th scope="col">Event Date</th>
              <th scope="col">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="event in events" :key="event.id">
              <td>{{ event.name }}</td>
              <td>{{ event.date }}</td>
              <td>
                <button @click="editEvent(event.id)" class="btn btn-warning btn-sm me-2">Bearbeiten</button>
                <button @click="confirmDelete(event.id)" class="btn btn-danger btn-sm">Löschen</button>
              </td>
            </tr>
          </tbody>
        </table>
        <div class="mt-3">
          <ul class="pagination justify-content-center">
            <li v-for="page in totalPages" :key="page" :class="['page-item', { active: currentPage === page }]">
              <button class="page-link" @click="goToPage(page)" :disabled="currentPage === page">
                {{ page }}
              </button>
            </li>
          </ul>
        </div>
        <p class="success-message" v-if="successMessage">{{ successMessage }}</p>
        <p class="error-message" v-if="errorMessage">{{ errorMessage }}</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';

// Reactive state
const events = ref([]);
const errorMessage = ref('');
const successMessage = ref('');
const searchQuery = ref('');
const currentPage = ref(1);
const totalPages = ref(1);

// Fetch events
const fetchEvents = async (page = 1) => {
  currentPage.value = page; // Ensure currentPage is set before fetching
  try {
    const response = await axios.get('/api/events', {
      params: {
        page: currentPage.value,
        search: searchQuery.value,
      },
    });
    events.value = response.data.events;
    totalPages.value = response.data.total_pages;
  } catch (error) {
    errorMessage.value =
      error.response?.data?.message || 'An error occurred while fetching events.';
    setTimeout(() => (errorMessage.value = ''), 3500);
  }
};

// Handle search input
const onSearchInput = () => {
  currentPage.value = 1; // Reset to first page
  fetchEvents(currentPage.value);
};

// Confirm delete function
const confirmDelete = (id) => {
  Swal.fire({
    title: 'Bist du dir sicher?',
    text: 'Diese Aktion kann nicht rückgängig gemacht werden!',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Ja, löschen.'
  }).then((result) => {
    if (result.isConfirmed) {
      deleteEvent(id);
    }
  });
};

// Delete event function
const deleteEvent = async (id) => {
  try {
    await axios.delete(`/api/events/${id}`);
    successMessage.value = 'Event wurde gelöscht';
    fetchEvents(currentPage.value); // Refresh events list
    setTimeout(() => (successMessage.value = ''), 3500);
  } catch (error) {
    errorMessage.value =
      error.response?.data?.message || 'An error occurred while deleting the event.';
    setTimeout(() => (errorMessage.value = ''), 3500);
  }
};

// Edit event function
const editEvent = (id) => {
  // Navigate to create/edit component with edit mode
  window.location.href = `/edit-event/${id}`;
};

// Go to specific page
const goToPage = (page) => {
  fetchEvents(page);
};

onMounted(() => {
  fetchEvents();
});
</script>

<style>
.error-message {
  color: red;
  margin-top: 1rem;
  text-align: center;
}

.success-message {
  color: green;
  margin-top: 1rem;
  text-align: center;
}

.btn-sm {
  margin-right: 10px;
}

.page-item.active .page-link {
  background-color: #007bff;
  border-color: #007bff;
  color: white;
}
</style>
