<template>
  <div style="width: 100%; display: flex; justify-content: center;">
    <form @submit.prevent="submitForm" style="width: 40%;">
      <input type="hidden" name="_token" :value="csrfToken" />
      <div class="mb-3">
        <label for="email" class="form-label">Email address</label>
        <input type="email" name="email" class="form-control" id="email" v-model="email" placeholder="Enter your email" required>
      </div>
      <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <input type="password" name="password" class="form-control" id="password" v-model="password" placeholder="Enter your password" required>
      </div>
      <button type="submit" class="btn btn-primary w-100">Login</button>
      <p  class="error-message">{{ errorMessage }}</p>

    </form>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import axios from 'axios';

// Reactive state
const csrfToken = ref(document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
const email = ref('');
const password = ref('');
const errorMessage = ref('');

// Submit form function
const submitForm = async () => {
  try {
    // Send POST request with Axios
    const response = await axios.post('/api/login', {
      email: email.value,
      password: password.value,
      _token: csrfToken.value, // Include CSRF token
    });

    // Handle response (success)
    console.log('Login successful:', response.data);

    // Redirect to the start page
    window.location.href = '/';
  } catch (error) {
    // Handle error
    console.error('Login failed:', error.response ? error.response.data.message : error.message);
    errorMessage.value = error.response ? error.response.data.message : error.message;
    console.log('Error message set:', errorMessage.value);
    setTimeout(function(){errorMessage.value =  " "}, 3500)
  }
};
</script>

