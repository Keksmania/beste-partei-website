<template>
    <div class="container mt-5">
      <form @submit.prevent="submitForm">
        <div class="mb-3">
          <label for="email" class="form-label">Email</label>
          <input type="email" v-model="email" class="form-control" id="email" placeholder="Email eingeben" required />
        </div>
        <button type="submit" class="btn btn-primary w-100">Passwort zurücksetzen</button>
        <div v-if="errorMessage" class="text-danger mt-3">{{ errorMessage }}</div>
      </form>
    </div>
  </template>
  
  <script>
  import Swal from 'sweetalert2';
  import { ref } from 'vue';
  
  export default {
    data() {
      return {
        email: '',
        errorMessage: null
      };
    },
    methods: {
      async submitForm() {
        try {
          const response = await fetch('/api/forgot-password', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ email: this.email })
          });
          const data = await response.json();
  
          if (data.status === 'success') {
            Swal.fire({
              icon: 'success',
              title: 'Erfolg',
              text: data.message
            }).then((result) => {
              if (result.isConfirmed) {
                // Redirect after SweetAlert confirmation
                window.location.href = '/';
              }
            });
          } else {
            this.errorMessage = data.message;
            Swal.fire({
              icon: 'error',
              title: 'Fehler',
              text: data.message
            });
          }
        } catch (error) {
          console.error('Error:', error);
        }
      }
    }
  };
  </script>
  
