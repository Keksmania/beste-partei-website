<template>
  <div style="width: 100%; display: flex; justify-content: center;">
    <form @submit.prevent="submitForm" style="width: 40%;">
      <div class="mb-3">
        <label for="firstname" class="form-label">Vorname</label>
        <input type="text" name="firstname" class="form-control" id="firstname" v-model="firstname" placeholder="Vorname eingeben" required>
      </div>
      <div class="mb-3">
        <label for="name" class="form-label">Name</label>
        <input type="text" name="name" class="form-control" id="name" v-model="name" placeholder="Name eingeben" required>
      </div>
      <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input type="email" name="email" class="form-control" id="email" v-model="email" placeholder="Email eingeben" required>
      </div>
      <div class="mb-3">
        <label for="password" class="form-label">Passwort</label>
        <input type="password" name="password" class="form-control" id="password" v-model="password" placeholder="Passwort eingeben" required>
      </div>
      <div class="mb-3">
        <label for="password_confirmation" class="form-label">Bestätige Passwort</label>
        <input type="password" name="password_confirmation" class="form-control" id="password_confirmation" v-model="passwordConfirmation" placeholder="Passwort bestätigen" required>
      </div>
      <div class="mb-3">
        <label for="captcha" class="form-label">Schreibe das Wort rückwärts: <strong>{{ captchaFruit }}</strong></label>
        <input type="text" name="captcha" class="form-control" id="captcha" v-model="captcha" placeholder="Schreibe das Wort rückwärts" required>
      </div>
      <div class="mb-3 form-check" style="float:left;     display: flex;">
        <input style="    margin-top: 0;width: 30px;height: 30px;" type="checkbox" class="form-check-input" id="terms" v-model="termsAccepted" required>
        <label style= "    margin-left: 1em;" class="form-check-label" for="terms">
          Ich habe die <a href="/Impressum#Datenschutz" target="_blank">Datenschutzbedingungen</a>  gelesen und bin einverstanden.
        </label>
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
const firstname = ref('');
const name = ref('');
const email = ref('');
const password = ref('');
const passwordConfirmation = ref('');
const captcha = ref('');
const termsAccepted = ref(false); // Added termsAccepted field
const errorMessage = ref('');
const fruits = ['apple', 'banana', 'cherry', 'grape', 'orange'];
const captchaFruit = ref(fruits[Math.floor(Math.random() * fruits.length)]);

// Submit form function
const submitForm = async () => {
  if (!termsAccepted.value) {
    errorMessage.value = 'Sie müssen die Datenschutzbedingungen akzeptieren.';
    setTimeout(() => (errorMessage.value = ''), 3500);
    return;
  }

  try {
      const response = await axios.post('/api/register', {
          firstname: firstname.value,
          name: name.value,
          email: email.value,
          password: password.value,
          password_confirmation: passwordConfirmation.value,
          captcha: captcha.value,
          captcha_fruit: captchaFruit.value,
      });
      console.log('Registration successful:', response.data);
      window.location.href = '/registration-success';
  } catch (error) {
      errorMessage.value = error.response ? error.response.data.message : error.message;
      setTimeout(() => (errorMessage.value = ''), 3500);
  }
};
</script>
