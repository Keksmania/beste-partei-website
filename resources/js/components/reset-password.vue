<template>
    <div class="container mt-5">
      <form @submit.prevent="submitForm">
        <div class="mb-3">
          <label for="email" class="form-label">Email</label>
          <input
            type="email"
            v-model="email"
            class="form-control"
            id="email"
            placeholder="Email eingeben"
            required
          />
        </div>
  
        <div class="mb-3">
          <label for="password" class="form-label">Neues Passwort</label>
          <input
            type="password"
            v-model="password"
            class="form-control"
            id="password"
            placeholder="Neues Passwort"
            required
          />
        </div>
  
        <div class="mb-3">
          <label for="password_confirmation" class="form-label">Passwort bestätigen</label>
          <input
            type="password"
            v-model="password_confirmation"
            class="form-control"
            id="password_confirmation"
            placeholder="Passwort bestätigen"
            required
          />
        </div>
  
        <button type="submit" class="btn btn-primary w-100">Passwort zurücksetzen</button>
  
        <div v-if="errorMessage" class="text-danger mt-3">{{ errorMessage }}</div>
      </form>
    </div>
  </template>
  
  <script>
  import { ref, onMounted } from 'vue';
  import Swal from 'sweetalert2';
  
  export default {
    setup() {
      const email = ref('');
      const password = ref('');
      const password_confirmation = ref('');
      const errorMessage = ref(null);
      const token = ref(null); // To store the reset token
  
      // Access query parameters on component mount
      onMounted(() => {
        token.value = new URLSearchParams(window.location.search).get('token');
        email.value = new URLSearchParams(window.location.search).get('email'); // Ensure email is available from the query
      });
  
      const submitForm = async () => {
        // Validate that passwords match
        if (password.value !== password_confirmation.value) {
          errorMessage.value = 'Passwörter stimmen nicht überein.';
          return;
        }
  
        try {
          const response = await fetch('/api/reset-password', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
              email: email.value,
              password: password.value,
              password_confirmation: password_confirmation.value,
              token: token.value, // Send the token from the URL
            })
          });
  
          const data = await response.json();
  
          if (data.status === 'success') {
            Swal.fire({
              icon: 'success',
              title: 'Erfolg',
              text: data.message
            }).then(() => {
              // Redirect after success
              window.location.href = '/login'; // Redirect to login page
            });
          } else {
            errorMessage.value = data.message;
            Swal.fire({
              icon: 'error',
              title: 'Fehler',
              text: data.message
            });
          }
        } catch (error) {
          console.error('Error:', error);
          errorMessage.value = 'Ein Fehler ist aufgetreten. Bitte versuchen Sie es später noch einmal.';
          Swal.fire({
            icon: 'error',
            title: 'Fehler',
            text: 'Ein Fehler ist aufgetreten. Bitte versuchen Sie es später noch einmal.'
          });
        }
      };
  
      return {
        email,
        password,
        password_confirmation,
        errorMessage,
        submitForm,
      };
    }
  };
  </script>
  