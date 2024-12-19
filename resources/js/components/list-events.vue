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
                <button @click="downloadQrCode(event.id)" class="btn btn-primary">QR Code</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import axios from 'axios';

const searchQuery = ref('');
const events = ref([]);

// Fetch events from the API
const fetchEvents = async () => {
  const response = await axios.get('/api/events', {
    params: { search: searchQuery.value }
  });
  events.value = response.data.events;
};

// Handle search input
const onSearchInput = () => {
  fetchEvents();
};

// Download QR code
const downloadQrCode = (eventId) => {
  window.location.href = `/events/${eventId}/download-qrcode`;
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
  margin-right: 10px;
}

.page-item.active .page-link {
  background-color: #007bff;
  border-color: #007bff;
  color: white;
}
</style>
