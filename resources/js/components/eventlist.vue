<template>
    <div style="display:flex; width: 100%; flex-direction: column;">
      <div class="event-container" :style="{ transform: `translateX(${-position}em)` }">   
        <div v-for="event in events" :key="event.id" class="event-box">
          <div class="event-image"><img :src="event.imageSrc" alt="Image"></div>
          <div class="event-name"><h4>Box {{ event.name }}</h4></div>
        </div>
      </div>
      <div style="display: flex; flex-direction: row; justify-content: center;">
        <button @click="moveLeft" class="m-3">Move Left</button>
        <button @click="moveRight" class="m-3">Move Right</button>
      </div>
    </div>
  </template>
  
  <script setup>
  import { ref, onMounted } from 'vue';
  import axios from 'axios';
  
  const events = ref([]);
  const position = ref(0);
  const offset = ref(0);
  const limit = 20;
  const totalEvents = ref(0);
  
  const fetchEvents = async () => {
    const response = await axios.get('/api/events', {
      params: { offset: offset.value, limit }
    });
    const loadedEvents = response.data.events.map(event => ({
      ...event,
      imageSrc: `/images/${event.id}.jpg`
    }));
    events.value.push(...loadedEvents);
    totalEvents.value = response.data.total;
    offset.value += limit;
  };
  
  const moveLeft = () => {
    if (position.value < 15) return; 
    position.value -= 15;
  };
  
  const moveRight = async () => {
    // Prevent moving beyond the left boundary
    if(position.value/15 < totalEvents.value-3){
      position.value += 15;
    } else {
      return;
    }

    if ((position.value / 15) >= events.value.length - 10) {
      if (offset.value < totalEvents.value) {
        await fetchEvents();
      }
    }
  };
  
  onMounted(() => {
    fetchEvents();
  });
  </script>
  
