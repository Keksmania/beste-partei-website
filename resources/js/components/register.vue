<template>
    <div style="width: 100%; display: flex; justify-content: center;">
      <form @submit.prevent="submitForm" style="width: 30%;">
        <div class="mb-3">
          <label for="name" class="form-label">Name</label>
          <input type="text" name="name" class="form-control" id="name" v-model="name" placeholder="Enter your name" required>
        </div>
        <div class="mb-3">
          <label for="email" class="form-label">Email address</label>
          <input type="email" name="email" class="form-control" id="email" v-model="email" placeholder="Enter your email" required>
        </div>
        <div class="mb-3">
          <label for="password" class="form-label">Password</label>
          <input type="password" name="password" class="form-control" id="password" v-model="password" placeholder="Enter your password" required>
        </div>
        <div class="mb-3">
          <label for="password_confirmation" class="form-label">Confirm Password</label>
          <input type="password" name="password_confirmation" class="form-control" id="password_confirmation" v-model="passwordConfirmation" placeholder="Confirm your password" required>
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
  const errorMessage = ref('');
  
  // Submit form function
  const submitForm = async () => {
    try {
      // Send POST request with Axios
      const response = await axios.post('/register', {
        name: name.value,
        email: email.value,
        password: password.value,
        password_confirmation: passwordConfirmation.value,
      });
  
      // Handle response (success)
      console.log('Registration successful:', response.data);
  
      // Redirect or perform further actions
      window.location.href = '/';
    } catch (error) {
      // Handle error
      errorMessage.value = error.response ? error.response.data.message : error.message;
      setTimeout(function(){errorMessage.value = " "}, 3500)
    }
  };
  </script>
  
