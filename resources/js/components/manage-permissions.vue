<template>
    <div class="container mt-4">
      <h2>Benutzerrechte verwalten</h2>
      <div class="row">
        <div class="col-md-4">
          <h5>Benutzer</h5>
          <input type="text" class="form-control mb-3" v-model="userNameSearch" placeholder="Benutzer nach Name suchen..." @input="onUserSearchInput" />
         <div class="list-group overflow-auto" style="max-height: 300px;">
            <a
              v-for="user in filteredUsers"
              :key="user.id"
              class="list-group-item list-group-item-action"
              :class="{ 'active': selectedUser && selectedUser.id === user.id }"
              href="#"
              @click="selectUser(user)"
            >
              {{ user.name }} ({{ user.email }})
            </a>
          </div>
          <div class="mt-3">
            <ul class="pagination justify-content-center">
              <li v-for="page in totalPages" :key="page" :class="['page-item', { active: currentPage === page }]">
                <button class="page-link" @click="goToPage(page)" :disabled="currentPage === page">{{ page }}</button>
              </li>
            </ul>
          </div>
        </div>
        <div class="col-md-4">
          <h5>Berechtigungen</h5>
          <input type="text" class="form-control mb-3" v-model="permissionSearch" placeholder="Berechtigungen suchen..." />
          <div class="list-group overflow-auto" style="max-height: 300px;">
            <div
              v-for="permission in filteredPermissions"
              :key="permission.id"
              class="list-group-item"
              draggable="true"
              @dragstart="dragStart(permission)"
            >
              {{ permission.name }}
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <h5>Berechtigungen des Benutzers</h5>
          <div
            class="list-group overflow-auto border p-3"
            style="min-height: 300px;"
            @dragover.prevent
            @drop="dropPermission"
          >
            <div
              v-for="permission in selectedUserPermissions"
              :key="permission.permission_id"
              class="list-group-item"
              draggable="true"
              @dragstart="dragPermissionToTrash(permission)"
            >
              {{ permission.permission_name }}
            </div>
          </div>
          <div class="trash" @dragover.prevent @drop="dropToTrash">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">
              <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0z"/>
              <path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4zM2.5 3h11V2h-11z"/>
            </svg> Hierhin ziehen, um Berechtigung zu entfernen
          </div>
        </div>
      </div>
    </div>
  </template>
  
  <script>
  import axios from "axios";
  
  export default {
    data() {
      return {
        users: [],
        permissions: [],
        selectedUser: null,
        draggedPermission: null,
        draggedPermissionToTrash: null,
        userNameSearch: "",
        userEmailSearch: "",
        permissionSearch: "",
        currentPage: 1,
        totalPages: 1,
      };
    },
    computed: {
      filteredUsers() {
        return this.users.filter(user =>
          user.name.toLowerCase().includes(this.userNameSearch.toLowerCase()) &&
          user.email.toLowerCase().includes(this.userEmailSearch.toLowerCase())
        );
      },
      filteredPermissions() {
        return this.permissions.filter(permission =>
          permission.name.toLowerCase().includes(this.permissionSearch.toLowerCase())
        );
      },
      selectedUserPermissions() {
        if (!this.selectedUser) return [];
        return this.selectedUser.permissions.map(permission => ({
          permission_id: permission.id,
          permission_name: permission.name,
        }));
      }
    },
    methods: {
  onUserSearchInput() {
    this.currentPage = 1;
    this.fetchUsers();
  },
  fetchUsers() {
    axios.get("/api/users", { params: { page: this.currentPage, per_page: 100, name: this.userNameSearch } })
      .then(response => {
        this.users = response.data.users;
        this.totalPages = Math.ceil(response.data.total / 100);
        if (this.users.length > 0) {
          this.selectUser(this.users[0]); // Automatically select the first user
        }
      })
      .catch(error => {
        console.error("Fehler beim Abrufen der Benutzer", error);
      });
  },
  fetchPermissions() {
    axios.get("/api/permissions", { params: { search: this.permissionSearch } })
      .then(response => {
        this.permissions = response.data;
      })
      .catch(error => {
        console.error("Fehler beim Abrufen der Berechtigungen", error);
      });
  },



      selectUser(user) {
        console.log("Benutzer ausgewählt", user);
        this.selectedUser = user;
      },
      dragStart(permission) {
        this.draggedPermission = permission;
      },
      dragPermissionToTrash(permission) {
        this.draggedPermissionToTrash = permission;
      },
      dropPermission() {
        if (this.selectedUser && this.draggedPermission) {
          axios
            .post("/api/assign-permission", {
              user_id: this.selectedUser.id,
              permission_id: this.draggedPermission.id,
            })
            .then(() => {
              // Update selected user's permissions directly
              this.selectedUser.permissions.push(this.draggedPermission);
            })
            .catch(error => {
              console.error("Fehler beim Zuweisen der Berechtigung", error);
            });
        }
      },
      dropToTrash() {
        if (this.selectedUser && this.draggedPermissionToTrash) {
          axios
            .post("/api/revoke-permission", {
              user_id: this.selectedUser.id,
              permission_id: this.draggedPermissionToTrash.permission_id,
            })
            .then(() => {
              // Remove the permission from selected user's permissions  
              this.selectedUser.permissions = this.selectedUser.permissions.filter(permission => 
                permission.id !== this.draggedPermissionToTrash.permission_id
              );
            })
            .catch(error => {
              console.error("Error revoking permission", error);
            });
        }
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
      this.fetchPermissions();
    },
  };
  </script>
  
  <style>
  .list-group-item[draggable="true"] {
    cursor: grab;
  }
  
  .pagination {
    display: flex;
    justify-content: center;
  }
  
  .page-item.disabled .page-link {
    background-color: #f8f9fa;
    border-color: #dee2e6;
    color: #6c757d;
  }
  
  .page-item.active .page-link {
    background-color: #007bff;
    border-color: #007bff;
    color: #fff;
  }
  
  .page-link {
    cursor: pointer;
  }
  
  .list-group-item.active {
    background-color: #007bff;
    color: white;
  }
  
  .trash {
    margin-top: 20px;
    border: 2px dashed #007bff;
    padding: 10px;
    text-align: center;
    color: #007bff;
  }
  </style>
  