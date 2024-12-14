<template>
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

const events = ref([]);
const position = ref(0);
const currentPage = ref(1);
const limit = 20;
const totalEvents = ref(0);

const fetchEvents = async () => {
  const response = await axios.get('/api/events', {
    params: { 
      page: currentPage.value, 
      limit: limit 
    }
  });

  const loadedEvents = response.data.events.map(event => ({
    ...event,
    imageSrc: event.image ? `${event.image}` : '/images/1.jpg', // Use thumbnail field
  }));

  events.value.push(...loadedEvents);
  totalEvents.value = response.data.total;
};

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
      await fetchEvents();
      position.value += 16;
    }
  }
};

onMounted(() => {
  fetchEvents();
});
</script>
