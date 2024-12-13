// resources/js/app.js

import './bootstrap';  // Custom Bootstrap JavaScript
import { createApp } from 'vue';
import createpost from './components/createpost.vue';
import eventlist from './components/eventlist.vue';
import register from './components/register.vue';
import login from './components/login.vue';
import managepermissions from './components/manage-permissions.vue';
import approveregistration from './components/approve-registration.vue';



import '/node_modules/bootstrap/dist/js/bootstrap.bundle.min.js';  // Bootstrap JS

const app = createApp({});

app.component('createpost', createpost);
app.component('eventlist', eventlist);
app.component('register', register);
app.component('login', login);
app.component('manage-permissions', managepermissions);
app.component('approve-registration', approveregistration);
app.mount("#app");

