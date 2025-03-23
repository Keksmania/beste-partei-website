<template>
  <div style="width: 100%; display: flex; justify-content: center;">
    <form @submit.prevent="savePost" style="width:100%" enctype="multipart/form-data">
      <div class="mb-3">
        <label for="postName" class="form-label">Post Name</label>
        <input
          type="text"
          class="form-control"
          id="postName"
          v-model="postName"
          placeholder="Enter post name"
          required
        />
      </div>

      <div class="mb-3">
        <label for="postDescription" class="form-label">Post Description</label>
        <div id="editor" ref="editor"></div>
      </div>

      <div class="mb-3">
        <label for="postImage" class="form-label">Post Image</label>
        <input
          type="file"
          class="form-control"
          id="postImage"
          ref="image"
          accept="image/*"
          @change="onFileChange"
        />
        <img v-if="thumbnailSrc" :src="thumbnailSrc" alt="Thumbnail" class="img-thumbnail mt-3" style="max-height: 200px;">
      </div>

      <div class="form-check mb-3">
        <input
          class="form-check-input"
          type="checkbox"
          id="isEvent"
          v-model="isEvent"
        />
        <label for="isEvent" class="form-check-label">Is this an event?</label>
      </div>

      <div class="mb-3" v-if="isEvent">
        <label for="eventDate" class="form-label">Event Date</label>
        <input
          type="date"
          class="form-control"
          id="eventDate"
          v-model="eventDate"
          required
        />
      </div>

      <button type="submit" class="btn btn-primary w-100">{{ isEditMode ? 'Update' : 'Create Post' }}</button>
      <p class="error-message" v-if="errorMessage">{{ errorMessage }}</p>
      <p class="success-message" v-if="successMessage">{{ successMessage }}</p>
    </form>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

// Props
const props = defineProps({
  postId: Number,
  isEditMode: Boolean,
});

// Reactive state
const csrfToken = ref(document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
const postName = ref('');
const eventDate = ref('');
const errorMessage = ref('');
const successMessage = ref('');
const editor = ref(null);
const image = ref(null);
const thumbnailSrc = ref(null);
const isEvent = ref(false);
const wasEvent = ref(false); // Track if the post was an event

// Base URL of the website
const baseURL = ref(`${window.location.origin}/`);

// Initialize editor
onMounted(() => {
  if (editor.value) {
    new RichTextEditor(editor.value);
  }
  if (props.isEditMode) {
    fetchPost();
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

// Fetch post details for editing
const fetchPost = async () => {
  try {
    const response = await axios.get(`/api/posts/${props.postId}`);
    const post = response.data.post;
    postName.value = post.name;
    setEditorContent(post.description);
    thumbnailSrc.value = post.thumbnail ? `${baseURL.value}${post.thumbnail}` : `${baseURL.value}images/1.jpg`; // Use thumbnail field
    if (post.is_event) {
      isEvent.value = true;
      wasEvent.value = true; // Track that the post was an event
      eventDate.value = post.date;
    }
  } catch (error) {
    errorMessage.value = 'Error fetching post details';
    setTimeout(() => (errorMessage.value = ''), 3500);
  }
};

// Create or update post function
const savePost = async () => {
  const description = getEditorContent();

  // Simple validation
  if (!postName.value || !description) {
    errorMessage.value = 'Name and description are required.';
    setTimeout(() => (errorMessage.value = ''), 3500);
    return;
  }

  if (isEvent.value && !eventDate.value) {
    errorMessage.value = 'Event date is required when creating an event.';
    setTimeout(() => (errorMessage.value = ''), 3500);
    return;
  }

  // Build a FormData payload for file uploads
  let formData = new FormData();
  formData.append('name', postName.value);
  formData.append('description', description);
  formData.append('is_event', isEvent.value ? 1 : 0);  // use string for boolean
  if (isEvent.value) {
    formData.append('date', eventDate.value);
  }
  formData.append('_token', csrfToken.value);

  // Append image file if available
  if (image.value && image.value.files[0]) {
    formData.append('image', image.value.files[0]);
  }

  try {
    let response;
    if (props.isEditMode) {
      response = await axios.post(`/api/posts/${props.postId}`, formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      });
      successMessage.value = 'Post updated successfully!';
    } else {
      response = await axios.post('/api/posts', formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      });
      successMessage.value = 'Post created successfully!';
    }
    setTimeout(() => (successMessage.value = ''), 3500);

    // Reset the form fields
    postName.value = '';
    eventDate.value = '';
    setEditorContent('');
    if (image.value) {
      image.value.value = '';
    }
    thumbnailSrc.value = null;
    isEvent.value = false;
    wasEvent.value = false; // Reset the wasEvent flag
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'An error occurred while saving the post.';
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
