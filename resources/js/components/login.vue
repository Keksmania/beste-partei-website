<template>
  <div style=" display: flex; justify-content: center;">
    <form @submit.prevent="submitForm">
      <input type="hidden" name="_token" :value="csrfToken" />
      <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input type="email" name="email" class="form-control w-100" id="email" v-model="email" placeholder="Email eingeben" required>
      </div>
      <div class="mb-3">
        <label for="password" class="form-label">Passwort</label>
        <input type="password" name="password" class="form-control w-100" id="password" v-model="password" placeholder="Passwort eingeben" required>
      </div>
      <button type="submit" class="btn btn-primary w-100">Login</button>
      <p class="error-message">{{ errorMessage }}</p>

      <!-- Link to password reset page -->
      <div class="text-center mt-3">
        <a href="/forgot-password" class="text-primary">Passwort vergessen?</a>
      </div>
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
    const response = await axios.post('/api/login', {
      email: email.value,
      password: password.value,
      _token: csrfToken.value,
    });

    window.location.href = '/';
  } catch (error) {
    errorMessage.value = error.response ? error.response.data.message : error.message;
    setTimeout(() => (errorMessage.value = ''), 3500);
  }
};
</script>
