<template>
  <div style="width: 100%; display: flex; justify-content: center;">
    <form @submit.prevent="submitForm" style="width: 40%;">
      <div class="mb-3">
        <label for="name" class="form-label">Name</label>
        <input type="text" name="name" class="form-control" id="name" v-model="name" placeholder="Enter your name" required>
      </div>
      <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input type="email" name="email" class="form-control" id="email" v-model="email" placeholder="Enter your email" required>
      </div>
      <div class="mb-3">
        <label for="password" class="form-label">Passwort</label>
        <input type="password" name="password" class="form-control" id="password" v-model="password" placeholder="Enter your password" required>
      </div>
      <div class="mb-3">
        <label for="password_confirmation" class="form-label">Bestätige Passwort</label>
        <input type="password" name="password_confirmation" class="form-control" id="password_confirmation" v-model="passwordConfirmation" placeholder="Confirm your password" required>
      </div>
      <div class="mb-3">
        <label for="captcha" class="form-label">Schreibe das Wort rückwärts: <strong>{{ captchaFruit }}</strong></label>
        <input type="text" name="captcha" class="form-control" id="captcha" v-model="captcha" placeholder="Enter the fruit backward" required>
      </div>
      <button type="submit" class="btn btn-primary w-100">Register</button>
      <p class="error-message">{{ errorMessage }}</p>
    </form>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import axios from 'axios';

// Reactive state
const name = ref('');
const email = ref('');
const password = ref('');
const passwordConfirmation = ref('');
const captcha = ref('');
const errorMessage = ref('');
const fruits = ['apple', 'banana', 'cherry', 'grape', 'orange'];
const captchaFruit = ref(fruits[Math.floor(Math.random() * fruits.length)]);

// Submit form function
const submitForm = async () => {
  try {
      const response = await axios.post('/api/register', {
          name: name.value,
          email: email.value,
          password: password.value,
          password_confirmation: passwordConfirmation.value,
          captcha: captcha.value,
          captcha_fruit: captchaFruit.value, // Send the fruit to validate on the server
      });
      console.log('Registration successful:', response.data);
      window.location.href = '/registration-success';
  } catch (error) {
      errorMessage.value = error.response ? error.response.data.message : error.message;
      setTimeout(() => (errorMessage.value = ''), 3500);
  }
};
</script>
