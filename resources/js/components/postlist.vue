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
  <div 
    style="display:flex; width: 100%; flex-direction: column; min-height: 16em; max-width: 100%; overflow: hidden;"
    @touchstart="handleTouchStart"
    @touchmove="handleTouchMove"
    @touchend="handleTouchEnd"
  >
    <div class="event-container" :style="{ transform: `translateX(${-position}em)` }">
      <a 
        v-for="post in posts" 
        :key="post.id" 
        class="event-box" 
        :href="'/post/' + post.id"
        :class="{ 
          'future-event': new Date(post.date) > new Date(),
          'today-event': new Date(post.date).toDateString() === new Date().toDateString() 
        }"
      >
        <div class="event-image">
          <img :src="post.image ? post.image : '/images/1.jpg'" alt="Image">
          <div class="event-date">{{ new Date(post.date).toLocaleDateString() }}</div>
        </div>
        <div class="event-name"><h5>{{ post.name }}</h5></div>
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

// Props
const props = defineProps({
  event: Boolean
});

// State variables
const posts = ref([]);
const position = ref(0);
const currentPage = ref(1);
const limit = 20;
const totalPosts = ref(0);

// Filters and post counts
const selectedYear = ref('');
const selectedMonth = ref('');
const availableYears = ref([]);
const months = [
  "Januar", "Februar", "März", "April", "Mai", "Juni",
  "July", "August", "September", "Oktober", "November", "Dezember"
];
const yearCounts = ref({}); // { 2024: 23, 2023: 17, ... }
const monthCounts = ref({}); // { 1: 3, 2: 5, ... }

// Touch event variables
let touchStartX = 0;
let touchEndX = 0;

// Generate available years dynamically
const generateYears = async () => {
  const currentYear = new Date().getFullYear();
  for (let year = currentYear; year >= 2024; year--) {
    availableYears.value.push(year);
  }
  await fetchYearCounts();
};

// Fetch total posts per year
const fetchYearCounts = async () => {
  for (let year of availableYears.value) {
    const response = await axios.get('/api/posts/filter/count', {
      params: { year,event: props.event ? 'true' : 'false', post:  (!props.event) ? 'true' : 'false' }
    });
    yearCounts.value[year] = response.data.total || 0;
  }
};

// Fetch total posts per month for a specific year
const fetchMonthCounts = async (year) => {
  monthCounts.value = {};
  for (let month = 1; month <= 12; month++) {
    const response = await axios.get('/api/posts/filter/count', {
      params: { year, month, event: props.event ? 'true' : 'false', post:  (!props.event) ? 'true' : 'false' }
    });
    monthCounts.value[month] = response.data.total || 0;
  }
};

// Fetch posts
// Fetch posts
const fetchPosts = async (reset = true) => {
  if (reset) {
    posts.value = [];
    position.value = 0;
    currentPage.value = 1;
  }

  const response = await axios.get('/api/posts', {
    params: { 
      page: currentPage.value, 
      limit: limit,
      year: selectedYear.value || null,
      month: selectedMonth.value || null,
      events: props.event
    }
  });

  let loadedPosts = [];
  if (props.event) {
    loadedPosts = response.data.events ? response.data.events.filter(post => post !== null) : [];
  } else {
    loadedPosts = response.data.posts ? response.data.posts.filter(post => post !== null) : [];
    // Skip posts that have is_event true when event prop is false
    loadedPosts = loadedPosts.filter(post => !post.is_event);
  }

  posts.value.push(...loadedPosts);
  totalPosts.value = response.data.total;
};

// Apply filters
const applyFilters = async () => {
  if (selectedYear.value) {
    await fetchMonthCounts(selectedYear.value); // Fetch month counts when a year is selected
  } else {
    monthCounts.value = {};
  }
  fetchPosts(true);
};

// Reset filters
const resetFilters = () => {
  selectedYear.value = '';
  selectedMonth.value = '';
  monthCounts.value = {};
  fetchPosts(true);
};

// Navigation
const moveLeft = () => {
  if (position.value < 16) return; 
  position.value -= 16;
};

const moveRight = async () => {
  if (position.value / 16 < posts.value.length) {
    position.value += 16;
  } else {
    if (currentPage.value * limit < totalPosts.value) {
      currentPage.value += 1;
      await fetchPosts(false);
      position.value += 16;
    }
  }
};

// Touch event handlers
const handleTouchStart = (event) => {
  touchStartX = event.changedTouches[0].screenX;
};

const handleTouchMove = (event) => {
  touchEndX = event.changedTouches[0].screenX;
};

const handleTouchEnd = () => {
  if (touchEndX < touchStartX) {
    moveRight();
  }
  if (touchEndX > touchStartX) {
    moveLeft();
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
  fetchPosts();
});
</script>

<style scoped>


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