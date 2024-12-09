// resources/js/app.js

import './bootstrap';  // Custom Bootstrap JavaScript
import { createApp } from 'vue';
import test from './components/test.vue';
import eventlist from './components/eventlist.vue';
import register from './components/register.vue';
import login from './components/login.vue';
import '/node_modules/bootstrap/dist/js/bootstrap.bundle.min.js';  // Bootstrap JS

const app = createApp({});
app.component('test', test);
app.component('eventlist', eventlist);

app.component('register', register);
app.component('login', login);
app.mount("#app");
