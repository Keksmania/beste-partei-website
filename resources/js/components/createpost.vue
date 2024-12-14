<template>
  <div style="width: 100%; display: flex; justify-content: center;">
    <form @submit.prevent="saveEvent" style="width:100%" enctype="multipart/form-data">
      <div class="mb-3">
        <label for="eventName" class="form-label">Event Name</label>
        <input
          type="text"
          class="form-control"
          id="eventName"
          v-model="eventName"
          placeholder="Enter event name"
          required
        />
      </div>

      <div class="mb-3">
        <label for="eventDate" class="form-label">Event Date</label>
        <input
          type="date"
          class="form-control"
          id="eventDate"
          v-model="eventDate"
          required
        />
      </div>

      <div class="mb-3">
        <label for="eventDescription" class="form-label">Event Beschreibung</label>
        <div id="editor" ref="editor"></div>
      </div>

      <div class="mb-3">
        <label for="eventImage" class="form-label">Event Bild</label>
        <input
          type="file"
          class="form-control"
          id="eventImage"
          ref="image"
          accept="image/*"
          @change="onFileChange"
        />
        <img v-if="thumbnailSrc" :src="thumbnailSrc" alt="Thumbnail" class="img-thumbnail mt-3" style="max-height: 200px;">
      </div>

      <button type="submit" class="btn btn-primary w-100">{{ isEditMode ? 'Update' : 'Erstelle Event' }}</button>
      <p class="error-message" v-if="errorMessage">{{ errorMessage }}</p>
      <p class="success-message" v-if="successMessage">{{ successMessage }}</p>
    </form>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import axios from 'axios';

// Props
const props = defineProps({
  eventId: Number,
  isEditMode: Boolean,
});

// Reactive state
const csrfToken = ref(document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
const eventName = ref('');
const eventDate = ref('');
const errorMessage = ref('');
const successMessage = ref('');
const editor = ref(null);
const image = ref(null);
const thumbnailSrc = ref(null);

// Base URL of the website
const baseURL = ref(`${window.location.origin}/`);

// Initialize editor
onMounted(() => {
  if (editor.value) {
    new RichTextEditor(editor.value);
  }
  if (props.isEditMode) {
    fetchEvent();
  }
});

// Function to get content from iframe
const getEditorContent = () => {
  const iframe = editor.value?.querySelector('iframe.rte-editable');
  return iframe?.contentDocument?.body.innerHTML || '';
};

// Function to set editor content
const setEditorContent = (content) => {
  const iframe = editor.value?.querySelector('iframe.rte-editable');
  if (iframe) {
    iframe.contentDocument.body.innerHTML = content;
  }
};

// Function to handle file change
const onFileChange = () => {
  const file = image.value?.files[0];
  if (file) {
    thumbnailSrc.value = URL.createObjectURL(file);
  }
};

// Fetch event details for editing
const fetchEvent = async () => {
  try {
    const response = await axios.get(`/api/events/${props.eventId}`);
    const event = response.data.event;
    eventName.value = event.name;
    eventDate.value = event.date;
    setEditorContent(event.description);
    thumbnailSrc.value = event.thumbnail ? `${baseURL.value}${event.thumbnail}` : `${baseURL.value}images/1.jpg`; // Use thumbnail field
  } catch (error) {
    errorMessage.value = 'Error fetching event details';
    setTimeout(() => (errorMessage.value = ''), 3500);
  }
};

// Create or update event function
const saveEvent = async () => {
  const description = getEditorContent();
  const imageFile = image.value?.files[0];

  if (!eventName.value || !eventDate.value || !description) {
    errorMessage.value = 'All fields are required except the image.';
    setTimeout(() => (errorMessage.value = ''), 3500);
    return;
  }

  const formData = new FormData();
  formData.append('name', eventName.value);
  formData.append('date', eventDate.value);
  formData.append('description', description);
  if (imageFile) {
    formData.append('image', imageFile);
  }
  formData.append('_token', csrfToken.value);

  try {
    let response;
    if (props.isEditMode) {
      response = await axios.post(`/api/events/${props.eventId}`, formData);
      successMessage.value = 'Event updated successfully!';
    } else {
      response = await axios.post('/api/events', formData);
      successMessage.value = 'Event created successfully!';
    }
    setTimeout(() => (successMessage.value = ''), 3500);

    // Reset form
    eventName.value = '';
    eventDate.value = '';
    setEditorContent('');
    image.value.value = '';
    thumbnailSrc.value = null;
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'An error occurred while saving the event.';
    setTimeout(() => (errorMessage.value = ''), 3500);
  }
};
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

.img-thumbnail {
  display: block;
  margin: auto;
}
</style>
