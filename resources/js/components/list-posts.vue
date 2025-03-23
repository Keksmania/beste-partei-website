<template>
  <div class="container mt-4">
    <h2>Posts verwalten</h2>
    <div class="row">
      <!-- Post List -->
      <div class="col-md-12">
        <input
          type="text"
          class="form-control mb-3"
          v-model="searchQuery"
          placeholder="Posts suchen..."
          @input="onSearchInput"
        />
        <table class="table table-striped table-responsive">
          <thead>
            <tr>
              <th scope="col">Post Name</th>
              <th scope="col">Datum</th>
              <th scope="col">Aktionen</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="post in posts" :key="post.id">
              <td>{{ post.name }}</td>
              <td>{{ post.date }}</td>
              <td>
                <button @click="editPost(post.id)" class="btn btn-sm btn-secondary">Bearbeiten</button>
                <button @click="confirmDelete(post.id)" class="btn btn-sm btn-danger">Löschen</button>
                <button v-if="post.is_event" @click="downloadQrCode(post.event_id)" class="btn btn-sm btn-primary">QR Code</button>
              </td>
            </tr>
          </tbody>
        </table>
        <!-- Pagination -->
        <ul class="pagination justify-content-center">
          <li class="page-item" :class="{ disabled: currentPage === 1 }">
            <button class="page-link" @click="goToPage(currentPage - 1)">«</button>
          </li>
          <li
            v-for="page in pagesToShow(totalPages, currentPage)"
            :key="page"
            :class="['page-item', { active: currentPage === page }]"
          >
            <button class="page-link" @click="goToPage(page)">
              {{ page }}
            </button>
          </li>
          <li class="page-item" :class="{ disabled: currentPage === totalPages }">
            <button class="page-link" @click="goToPage(currentPage + 1)">»</button>
          </li>
        </ul>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';

const searchQuery = ref('');
const posts = ref([]);
const currentPage = ref(1);
const totalPages = ref(1);
const maxItemsPerPage = 10;

// Fetch posts from the API
const fetchPosts = async () => {
  const response = await axios.get('/api/posts', {
    params: { 
      search: searchQuery.value,
      page: currentPage.value,
      per_page: maxItemsPerPage,
      events: false // Set to true if you want to fetch events instead
    }
  });
  posts.value = response.data.posts;
  totalPages.value = Math.ceil(response.data.total / maxItemsPerPage);
};

// Handle search input
const onSearchInput = () => {
  currentPage.value = 1;
  fetchPosts();
};

// Edit post
const editPost = (postId) => {
  window.location.href = `/edit-post/${postId}`;
};

// Confirm delete post
const confirmDelete = (postId) => {
  Swal.fire({
    title: 'Sind Sie sicher?',
    text: 'Möchten Sie diesen Post wirklich löschen?',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Ja, löschen!',
    cancelButtonText: 'Abbrechen'
  }).then((result) => {
    if (result.isConfirmed) {
      deletePost(postId);
    }
  });
};

// Delete post
const deletePost = async (postId) => {
  await axios.delete(`/api/posts/${postId}`);
  fetchPosts();
};

// Download QR code
const downloadQrCode = (eventId) => {
  window.location.href = `/events/${eventId}/download-qrcode`;
};

// Pagination handlers
const goToPage = (page) => {
  if (page >= 1 && page <= totalPages.value) {
    currentPage.value = page;
    fetchPosts();
  }
};

// Show only 4 pages max in the pagination
const pagesToShow = (totalPages, currentPage) => {
  const maxPages = 4;
  let startPage = Math.max(1, currentPage - 1);
  let endPage = Math.min(totalPages, startPage + maxPages - 1);

  if (endPage - startPage < maxPages - 1) {
    startPage = Math.max(1, endPage - maxPages + 1);
  }

  const pages = [];
  for (let page = startPage; page <= endPage; page++) {
    pages.push(page);
  }
  return pages;
};

// Fetch posts on component mount
fetchPosts();
</script>

<style>
.error-message {
  color: red;
  margin-top: 1rem;
  text-align: center;
}

.success-message {
  color: green;
  margin-top: 1rem;
  text-align: center;
}

.btn-sm {
  margin-right: 3px;
}
</style>