<template>
    <div>
      <h2>Registrierungen verwalten</h2>
      <ul class="nav nav-tabs mb-3">
        <li class="nav-item" @click="setFilter('all')">
          <a class="nav-link" :class="{ active: filter === 'all' }">Alle</a>
        </li>
        <li class="nav-item" @click="setFilter('approved')">
          <a class="nav-link" :class="{ active: filter === 'approved' }">Genehmigt</a>
        </li>
        <li class="nav-item" @click="setFilter('pending')">
          <a class="nav-link" :class="{ active: filter === 'pending' }">Warten auf Genehmigung</a>
        </li>
      </ul>
      <table class="table">
        <thead>
          <tr>
            <th @click="sort('name')">Name</th>
            <th @click="sort('email')">Email</th>
            <th @click="sort('created_at')">Registrierungsdatum</th>
            <th>Aktionen</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="user in users" :key="user.id">
            <td>{{ user.name }}</td>
            <td>{{ user.email }}</td>
            <td>{{ new Date(user.created_at).toLocaleString() }}</td>
            <td>
              <button v-if="!user.activated" class="btn btn-success btn-sm" @click="approveRegistration(user.id)">Genehmigen</button>
            </td>
          </tr>
        </tbody>
      </table>
      <ul class="pagination justify-content-center">
        <li v-for="page in totalPages" :key="page" :class="['page-item', { active: currentPage === page }]">
          <button class="page-link" @click="goToPage(page)" :disabled="currentPage === page">{{ page }}</button>
        </li>
      </ul>
    </div>
  </template>
  
  <script>
  import axios from 'axios';
  
  export default {
    data() {
      return {
        users: [],
        filter: 'all',
        sortKey: 'created_at',
        sortOrder: 'desc',
        currentPage: 1,
        totalPages: 1,
      };
    },
    methods: {
      setFilter(filter) {
        this.filter = filter;
        this.currentPage = 1;
        this.fetchUsers();
      },
      sort(key) {
        if (this.sortKey === key) {
          this.sortOrder = this.sortOrder === 'asc' ? 'desc' : 'asc';
        } else {
          this.sortKey = key;
          this.sortOrder = 'asc';
        }
        this.fetchUsers();
      },
      fetchUsers() {
        axios.get('/api/registrations', {
          params: {
            status: this.filter,
            page: this.currentPage,
            per_page: 10,
            sort_key: this.sortKey,
            sort_order: this.sortOrder
          }
        }).then(response => {
          this.users = response.data.users;
          this.totalPages = Math.ceil(response.data.total / 10);
        }).catch(error => {
          console.error('Fehler beim Abrufen der Benutzer', error);
        });
      },
      approveRegistration(userId) {
        axios.post(`/api/approve-registration/${userId}`).then(() => {
          this.fetchUsers();
        }).catch(error => {
          console.error('Fehler beim Genehmigen der Registrierung', error);
        });
      },
      goToPage(page) {
        if (this.currentPage !== page) {
          this.currentPage = page;
          this.fetchUsers();
        }
      }
    },
    mounted() {
      this.fetchUsers();
    }
  };
  </script>
  
  <style>
  .nav-tabs .nav-link {
    background-color: #f8f9fa;
    color: #007bff;
  }
  .nav-tabs .nav-link:hover,
  .nav-tabs .nav-link.active {
    background-color: #007bff;
    color: white;
  }
  .table th {
    cursor: pointer;
  }
  </style>
  