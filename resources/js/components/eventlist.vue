<template>
  <!-- Filters -->
  <div style="display: flex; gap: 1em; margin: 1em 0; justify-content: center;">
    <select v-model="selectedYear" @change="applyFilters" class="filter-dropdown">
      <option value="" disabled selected>Select Year</option>
      <option v-for="year in availableYears" :key="year" :value="year">{{ year }}</option>
    </select>

    <select v-model="selectedMonth" @change="applyFilters" class="filter-dropdown">
      <option value="" disabled selected>Select Month</option>
      <option v-for="(month, index) in months" :key="index" :value="index + 1">{{ month }}</option>
    </select>

    <button @click="resetFilters" class="btn btn-secondary">Reset</button>
  </div>

  <!-- Carousel -->
  <div style="display:flex; width: 100%; flex-direction: column; min-height: 16em; max-width: 80%; overflow: hidden; margin-left: 10%;">
    <div class="event-container" :style="{ transform: `translateX(${-position}em)` }">
      <a 
        v-for="event in events" 
        :key="event.id" 
        class="event-box" 
        :href="'/post/' + event.id"
        :class="{ 
          'future-event': new Date(event.date) > new Date(),
          'today-event': new Date(event.date).toDateString() === new Date().toDateString() 
        }"
      >
        <div class="event-image">
          <img :src="event.imageSrc" alt="Image">
          <div class="event-date">{{ new Date(event.date).toLocaleDateString() }}</div>
        </div>
        <div class="event-name"><h5>{{ event.name }}</h5></div>
      </a>
    </div>
    <div style="display: flex; flex-direction: row; justify-content: space-between; margin-top: -8em;">
      <button @click="moveLeft" class="mr-5 arrow"><</button>
      <button @click="moveRight" class="ml-5 arrow">></button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

// State variables
const events = ref([]);
const position = ref(0);
const currentPage = ref(1);
const limit = 20;
const totalEvents = ref(0);

// Filters
const selectedYear = ref('');
const selectedMonth = ref('');
const availableYears = ref([]);
const months = [
  "January", "February", "March", "April", "May", "June",
  "July", "August", "September", "October", "November", "December"
];

// Generate available years dynamically
const generateYears = () => {
  const currentYear = new Date().getFullYear();
  for (let year = currentYear; year >= 2000; year--) {
    availableYears.value.push(year);
  }
};

// Fetch events
const fetchEvents = async (reset = true) => {
  if (reset) {
    events.value = [];
    position.value = 0;
    currentPage.value = 1;
  }

  const response = await axios.get('/api/events', {
    params: { 
      page: currentPage.value, 
      limit: limit,
      year: selectedYear.value || null,
      month: selectedMonth.value || null
    }
  });

  const loadedEvents = response.data.events.map(event => ({
    ...event,
    imageSrc: event.image || '/images/1.jpg',
  }));

  events.value.push(...loadedEvents);
  totalEvents.value = response.data.total;
};

// Apply filters
const applyFilters = () => {
  fetchEvents(true);
};

// Reset filters
const resetFilters = () => {
  selectedYear.value = '';
  selectedMonth.value = '';
  fetchEvents(true);
};

// Navigation
const moveLeft = () => {
  if (position.value < 16) return; 
  position.value -= 16;
};

const moveRight = async () => {
  if (position.value / 16 < events.value.length - 3) {
    position.value += 16;
  } else {
    if (currentPage.value * limit < totalEvents.value) {
      currentPage.value += 1;
      await fetchEvents(false);
      position.value += 16;
    }
  }
};

// Lifecycle hooks
onMounted(() => {
  generateYears();
  fetchEvents();
});
</script>

<style>
.filter-dropdown {
  padding: 0.5em;
  font-size: 1em;
  border: 1px solid #ccc;
  border-radius: 5px;
  cursor: pointer;
}

.btn {
  padding: 0.5em 1em;
  border: none;
  background-color: #007BFF;
  color: white;
  cursor: pointer;
  border-radius: 5px;
}

.btn-secondary {
  background-color: #6c757d;
}

.btn:hover {
  background-color: #0056b3;
}
</style>
