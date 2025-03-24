// resources/js/app.js

import './bootstrap';  // Custom Bootstrap JavaScript
import { createApp } from 'vue';
import createpost from './components/createpost.vue';
import postlist from './components/postlist.vue';
import register from './components/register.vue';
import login from './components/login.vue';
import managepermissions from './components/manage-permissions.vue';
import approveregistration from './components/approve-registration.vue';
import forgotpassword from './components/forgot-password.vue';
import resetpassword from './components/reset-password.vue';
import listposts from './components/list-posts.vue';
import eventattendeetracking from './components/event-attendee-tracking.vue';

import Swal from 'sweetalert2';


import '/node_modules/bootstrap/dist/js/bootstrap.bundle.min.js';  // Bootstrap JS

const app = createApp({});

app.component('createpost', createpost);
app.component('postlist', postlist);
app.component('register', register);
app.component('login', login);
app.component('manage-permissions', managepermissions);
app.component('approve-registration', approveregistration);
app.component('forgot-password', forgotpassword);
app.component('reset-password', resetpassword);
app.component('list-posts', listposts);
app.component('event-attendee-tracking', eventattendeetracking);
app.mount("#app");

