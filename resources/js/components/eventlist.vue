<template>
  <!-- Filters -->
  <div class="filters-container">
    <!-- Year Dropdown -->
    <select v-model="selectedYear" @change="applyFilters" class="filter-dropdown">
      <option value="" disabled selected>Jahr auswählen</option>
      <option 
        v-for="year in availableYears" 
        :key="year" 
        :value="year"
      >
        {{ year }} ({{ yearCounts[year] || 0 }})
      </option>
    </select>

    <!-- Month Dropdown (only if a year is selected) -->
    <select 
      v-if="selectedYear" 
      v-model="selectedMonth" 
      @change="applyFilters" 
      class="filter-dropdown"
    >
      <option value="" disabled selected>Monat auswählen</option>
      <option 
        v-for="(month, index) in months" 
        :key="index" 
        :value="index + 1"
      >
        {{ month }} ({{ monthCounts[index + 1] || 0 }})
      </option>
    </select>

    <button @click="resetFilters" class="btn btn-secondary">Zurücksetzen</button>
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
          <img :src="event.image ? event.image : '/images/1.jpg'" alt="Image">
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

// Include SweetAlert
import Swal from 'sweetalert2';

// State variables
const events = ref([]);
const position = ref(0);
const currentPage = ref(1);
const limit = 20;
const totalEvents = ref(0);

// Filters and event counts
const selectedYear = ref('');
const selectedMonth = ref('');
const availableYears = ref([]);
const months = [
  "Januar", "Februar", "März", "April", "Mai", "Juni",
  "July", "August", "September", "Oktober", "November", "Dezember"
];
const yearCounts = ref({}); // { 2024: 23, 2023: 17, ... }
const monthCounts = ref({}); // { 1: 3, 2: 5, ... }

// Generate available years dynamically
const generateYears = async () => {
  const currentYear = new Date().getFullYear();
  for (let year = currentYear; year >= 2000; year--) {
    availableYears.value.push(year);
  }
  await fetchYearCounts();
};

// Fetch total events per year
const fetchYearCounts = async () => {
  for (let year of availableYears.value) {
    const response = await axios.get('/api/events/filter/count', {
      params: { year }
    });
    yearCounts.value[year] = response.data.total || 0;
  }
};

// Fetch total events per month for a specific year
const fetchMonthCounts = async (year) => {
  monthCounts.value = {};
  for (let month = 1; month <= 12; month++) {
    const response = await axios.get('/api/events/filter/count', {
      params: { year, month }
    });
    monthCounts.value[month] = response.data.total || 0;
  }
};

// Fetch events
const fetchEvents = async (reset = true) => {
  if (reset) {
    events.value = [];
    position.value = 0;
    currentPage.value = 1;
  }

  const response = await axios.get('/api/posts', {
    params: { 
      page: currentPage.value, 
      limit: limit,
      year: selectedYear.value || null,
      month: selectedMonth.value || null,
      events: true
      
    }
  });

  const loadedEvents = response.data.events.filter(event => event !== null);

  events.value.push(...loadedEvents);
  totalEvents.value = response.data.total;
};

// Apply filters
const applyFilters = async () => {
  if (selectedYear.value) {
    await fetchMonthCounts(selectedYear.value); // Fetch month counts when a year is selected
  } else {
    monthCounts.value = {};
  }
  fetchEvents(true);
};

// Reset filters
const resetFilters = () => {
  selectedYear.value = '';
  selectedMonth.value = '';
  monthCounts.value = {};
  fetchEvents(true);
};

// Navigation
const moveLeft = () => {
  if (position.value < 16) return; 
  position.value -= 16;
};

const moveRight = async () => {
  if (position.value / 16 < events.value.length) {
    position.value += 16;
  } else {
    if (currentPage.value * limit < totalEvents.value) {
      currentPage.value += 1;
      await fetchEvents(false);
      position.value += 16;
    }
  }
};

// Check for 'result' parameter and show SweetAlert
const getQueryParams = () => {
  let params = {};
  window.location.search.substring(1).split("&").forEach(pair => {
    pair = pair.split("=");
    params[pair[0]] = decodeURIComponent(pair[1] || "");
  });
  return params;
};

const params = getQueryParams();
if (params.result) {
  if (params.result === 'success') {
    Swal.fire({
      title: 'Erfolg!',
      text: 'Sie wurden erfolgreich als anwesend markiert.',
      icon: 'success'
    });
  } else if (params.result === 'fail') {
    Swal.fire({
      title: 'Fehler!',
      text: 'Ihre Anwesenheit konnte nicht markiert werden.',
      icon: 'error'
    });
  }
}

// Lifecycle hooks
onMounted(() => {
  generateYears();
  fetchEvents();
});
</script>

<style scoped>
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
.filters-container {
  display: flex;
  flex-wrap: wrap;
  gap: 1em;
  margin: 1em 0;
  justify-content: center;
}

.event-container {
  display: flex;
  transition: transform 0.3s ease;
}

.arrow:hover {
  color: #0056b3;
}
</style>