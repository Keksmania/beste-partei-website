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
              <th scope="col">Event Datum</th>
              <th scope="col">Aktionen</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="event in events" :key="event.id">
              <td>{{ event.name }}</td>
              <td>{{ event.date }}</td>
              <td>
                <button @click="editEvent(event.id)" class="btn btn-sm btn-secondary">Bearbeiten</button>
                <button @click="confirmDelete(event.id)" class="btn btn-sm btn-danger">Löschen</button>
                <button @click="downloadQrCode(event.id)" class="btn btn-sm btn-primary">QR Code</button>
              </td>
            </tr>
          </tbody>
        </table>
        <!-- Pagination -->
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
  </div>
</template>

<script setup>
import { ref } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';

const searchQuery = ref('');
const events = ref([]);
const currentPage = ref(1);
const totalPages = ref(1);
const maxItemsPerPage = 10;

// Fetch events from the API
const fetchEvents = async () => {
  const response = await axios.get('/api/events', {
    params: { 
      search: searchQuery.value,
      page: currentPage.value,
      per_page: maxItemsPerPage
    }
  });
  events.value = response.data.events;
  totalPages.value = Math.ceil(response.data.total / maxItemsPerPage);
};

// Handle search input
const onSearchInput = () => {
  currentPage.value = 1;
  fetchEvents();
};

// Edit event
const editEvent = (eventId) => {
  window.location.href = `/edit-event/${eventId}`;
};

// Confirm delete event
const confirmDelete = (eventId) => {
  Swal.fire({
    title: 'Sind Sie sicher?',
    text: 'Möchten Sie dieses Event wirklich löschen?',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Ja, löschen!',
    cancelButtonText: 'Abbrechen'
  }).then((result) => {
    if (result.isConfirmed) {
      deleteEvent(eventId);
    }
  });
};

// Delete event
const deleteEvent = async (eventId) => {
  await axios.delete(`/api/events/${eventId}`);
  fetchEvents();
};

// Download QR code
const downloadQrCode = (eventId) => {
  window.location.href = `/events/${eventId}/download-qrcode`;
};

// Pagination handlers
const goToPage = (page) => {
  if (page >= 1 && page <= totalPages.value) {
    currentPage.value = page;
    fetchEvents();
  }
};

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

// Fetch events on component mount
fetchEvents();
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
  margin-right: 3px;
}
</style>

