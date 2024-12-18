<template>
  <div class="container mt-4">
    <h2>Benutzerrechte verwalten</h2>
    <div class="row">
      <!-- User List -->
      <div class="col-md-4">
        <h5>Benutzer</h5>
        <input
          type="text"
          class="form-control mb-3"
          v-model="userNameSearch"
          placeholder="Benutzer nach Name suchen..."
          @input="onUserSearchInput"
        />
        <div class="list-group overflow-auto" style="max-height: 300px;">
          <a
            v-for="user in filteredUsers"
            :key="user.id"
            class="list-group-item list-group-item-action"
            :class="{ active: selectedUser && selectedUser.id === user.id }"
            href="#"
            @click.prevent="selectUser(user)"
          >
            {{ user.name }} ({{ user.email }})
          </a>
        </div>
        <div class="mt-3">
          <ul class="pagination justify-content-center">
            <li
              v-for="page in totalPages"
              :key="page"
              :class="['page-item', { active: currentPage === page }]"
            >
              <button
                class="page-link"
                @click="goToPage(page)"
                :disabled="currentPage === page"
              >
                {{ page }}
              </button>
            </li>
          </ul>
        </div>
      </div>

      <!-- Permissions List -->
      <div class="col-md-4">
        <h5>Berechtigungen</h5>
        <input
          type="text"
          class="form-control mb-3"
          v-model="permissionSearch"
          placeholder="Berechtigungen suchen..."
        />
        <div class="list-group overflow-auto" style="max-height: 300px;">
          <div
            v-for="permission in filteredPermissions"
            :key="permission.id"
            class="list-group-item"
            draggable="true"
            @dragstart="dragStart(permission, $event)"
            @touchstart="touchStart(permission, $event)"
          >
            {{ permission.name }}
          </div>
        </div>
      </div>

      <!-- User Permissions -->
      <div class="col-md-4">
        <h5>Berechtigungen des Benutzers</h5>
        <div
          class="list-group overflow-auto border p-3"
          style="min-height: 300px; max-height: 300px;"
          @dragover.prevent
          @drop="dropPermission"
          @touchmove="touchMove"
          @touchend="dropPermission"
        >
          <div
            v-for="permission in selectedUserPermissions"
            :key="permission.permission_id"
            class="list-group-item"
            draggable="true"
            @dragstart="dragPermissionToTrash(permission, $event)"
            @touchstart="touchPermissionToTrash(permission, $event)"
          >
            {{ permission.permission_name }}
          </div>
        </div>
        <div class="trash" @dragover.prevent @drop="dropToTrash" @touchend="dropToTrash">
          <svg
            xmlns="http://www.w3.org/2000/svg"
            width="16"
            height="16"
            fill="currentColor"
            class="bi bi-trash"
            viewBox="0 0 16 16"
          >
            <path
              d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0z"
            />
            <path
              d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4zM2.5 3h11V2h-11z"
            />
          </svg>
          Hierhin ziehen, um Berechtigung zu entfernen
        </div>
      </div>
    </div>
  </div>
</template>



<script setup>
import { ref, computed, onMounted } from "vue";
import axios from "axios";

const users = ref([]);
const permissions = ref([]);
const selectedUser = ref(null);
const draggedPermission = ref(null);
const isTouchDragging = ref(false); // Tracks if touch dragging is active
const touchPosition = ref({ x: 0, y: 0 }); // Tracks touch position for visual feedback
const userNameSearch = ref("");
const permissionSearch = ref("");
const currentPage = ref(1);
const totalPages = ref(1);
const itemsPerPage = ref(10); // Reduce items per page

const filteredUsers = computed(() =>
  users.value.filter(
    (user) =>
      user.name.toLowerCase().includes(userNameSearch.value.toLowerCase()) ||
      user.email.toLowerCase().includes(userNameSearch.value.toLowerCase())
  )
);

const filteredPermissions = computed(() =>
  permissions.value.filter((permission) =>
    permission.name.toLowerCase().includes(permissionSearch.value.toLowerCase())
  )
);

const selectedUserPermissions = computed(() =>
  selectedUser.value
    ? selectedUser.value.permissions.map((p) => ({
        permission_id: p.id,
        permission_name: p.name,
      }))
    : []
);

const onUserSearchInput = () => {
  currentPage.value = 1;
  fetchUsers();
};

const fetchUsers = async () => {
  try {
    const { data } = await axios.get("/api/users/permissions", {
      params: { page: currentPage.value, per_page: itemsPerPage.value, name: userNameSearch.value },
    });
    users.value = data.users;
    totalPages.value = Math.ceil(data.total / itemsPerPage.value);
    if (users.value.length > 0) {
      selectUser(users.value[0]);
    }
  } catch (error) {
    console.error("Error fetching users:", error);
  }
};

const fetchPermissions = async () => {
  try {
    const { data } = await axios.get("/api/permissions", {
      params: { search: permissionSearch.value },
    });
    permissions.value = data;
  } catch (error) {
    console.error("Error fetching permissions:", error);
  }
};

const selectUser = (user) => {
  selectedUser.value = user;
};

const dragStart = (permission, event) => {
  draggedPermission.value = permission;
  event.dataTransfer?.setData("text/plain", permission.id);
};

const touchStart = (permission, event) => {
  draggedPermission.value = permission;
  isTouchDragging.value = true;
  touchPosition.value = { x: event.touches[0].clientX, y: event.touches[0].clientY };
};

const touchMove = (event) => {
  if (isTouchDragging.value) {
    event.preventDefault();
    touchPosition.value = { x: event.touches[0].clientX, y: event.touches[0].clientY };
  }
};

const dropPermission = async () => {
  if (selectedUser.value && draggedPermission.value) {
    const alreadyAssigned = selectedUserPermissions.value.some(
      (p) => p.permission_id === draggedPermission.value.id
    );
    if (!alreadyAssigned) {
      try {
        await axios.post("/api/assign-permission", {
          user_id: selectedUser.value.id,
          permission_id: draggedPermission.value.id,
        });
        // Add permission to the user's permissions list
        selectedUser.value.permissions.push(draggedPermission.value);
      } catch (error) {
        console.error("Error assigning permission:", error);
      }
    }
  }
  resetDraggingState();
};

const dragPermissionToTrash = (permission, event) => {
  draggedPermission.value = permission;
  event.dataTransfer?.setData("text/plain", permission.permission_id);
};

const touchPermissionToTrash = (permission, event) => {
  draggedPermission.value = permission;
  isTouchDragging.value = true;
  touchPosition.value = { x: event.touches[0].clientX, y: event.touches[0].clientY };
};

const dropToTrash = async () => {
  if (selectedUser.value && draggedPermission.value) {
    try {
      await axios.post("/api/revoke-permission", {
        user_id: selectedUser.value.id,
        permission_id: draggedPermission.value.permission_id || draggedPermission.value.id,
      });
      // Remove the permission from the user's permissions list
      selectedUser.value.permissions = selectedUser.value.permissions.filter(
        (p) => p.id !== draggedPermission.value.permission_id
      );
    } catch (error) {
      console.error("Error revoking permission:", error);
    }
  }
  resetDraggingState();
};

const resetDraggingState = () => {
  isTouchDragging.value = false;
  draggedPermission.value = null;
  touchPosition.value = { x: 0, y: 0 };
};

const goToPage = (page) => {
  if (currentPage.value !== page) {
    currentPage.value = page;
    fetchUsers();
  }
};

onMounted(() => {
  fetchUsers();
  fetchPermissions();
});
</script>


<style>
/* For visible touch dragging feedback */
.dragged-element {
  position: fixed;
  pointer-events: none;
  z-index: 9999;
  opacity: 0.8;
  font-size: 20px; /* Bigger size */
  font-weight: bold; /* Bold text */
  background-color: rgba(255, 255, 255, 0.9); /* Slight background for contrast */
  padding: 10px;
  border: 1px solid #ccc;
  border-radius: 5px;
  transform: translate(-50%, -50%);
}

.dragged-element.hidden {
  display: none; /* Hide element when not touch dragging */
}
</style>