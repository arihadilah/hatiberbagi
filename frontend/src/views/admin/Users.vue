<template>
  <div class="admin-users">
    <div class="filters">
      <select v-model="filters.role" @change="loadUsers">
        <option value="">Semua Role</option>
        <option value="donor">Donatur</option>
        <option value="campaigner">Penggalang Dana</option>
        <option value="admin">Admin</option>
      </select>
      
      <input 
        type="text" 
        v-model="filters.search" 
        placeholder="Cari user..."
        @keyup.enter="loadUsers"
      >
      
      <button @click="loadUsers" class="btn-search">Cari</button>
    </div>
    
    <table class="data-table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Nama</th>
          <th>Email</th>
          <th>Role</th>
          <th>Status</th>
          <th>Bergabung</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="user in users" :key="user.id">
          <td>{{ user.id }}</td>
          <td>{{ user.name }}</td>
          <td>{{ user.email }}</td>
          <td>
            <select :value="user.role" @change="updateRole(user.id, $event.target.value)">
              <option value="donor">Donatur</option>
              <option value="campaigner">Campaigner</option>
              <option value="admin">Admin</option>
            </select>
          </td>
          <td>
            <span :class="'status-badge ' + (user.is_verified ? 'verified' : 'unverified')">
              {{ user.is_verified ? 'Terverifikasi' : 'Belum Verifikasi' }}
            </span>
          </td>
          <td>{{ formatDate(user.created_at) }}</td>
          <td class="actions">
            <button @click="toggleVerified(user.id)" class="btn-verify">
              {{ user.is_verified ? 'Unverify' : 'Verify' }}
            </button>
            <button v-if="user.role !== 'admin'" @click="suspendUser(user.id)" class="btn-suspend">
              Suspend
            </button>
          </td>
        </tr>
      </tbody>
    </table>
    
    <div class="pagination" v-if="pagination">
      <button @click="loadPage(pagination.current_page - 1)" :disabled="pagination.current_page === 1">
        Previous
      </button>
      <span>Halaman {{ pagination.current_page }} dari {{ pagination.last_page }}</span>
      <button @click="loadPage(pagination.current_page + 1)" :disabled="pagination.current_page === pagination.last_page">
        Next
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '@/services/api'

const users = ref([])
const pagination = ref(null)
const filters = ref({
  role: '',
  search: ''
})

onMounted(() => {
  loadUsers()
})

const loadUsers = async () => {
  try {
    const params = {}
    if (filters.value.role) params.role = filters.value.role
    if (filters.value.search) params.search = filters.value.search
    
    const { data } = await api.get('/admin/users', { params })
    users.value = data.data
    pagination.value = {
      current_page: data.current_page,
      last_page: data.last_page
    }
  } catch (error) {
    console.error('Failed to load users:', error)
  }
}

const loadPage = (page) => {
  if (page < 1 || page > pagination.value.last_page) return
  loadUsers()
}

const updateRole = async (userId, newRole) => {
  try {
    await api.patch(`/admin/users/${userId}/role`, { role: newRole })
    alert('Role berhasil diupdate')
    loadUsers()
  } catch (error) {
    alert('Gagal mengupdate role')
  }
}

const toggleVerified = async (userId) => {
  try {
    await api.post(`/admin/users/${userId}/toggle-verified`)
    loadUsers()
  } catch (error) {
    alert('Gagal mengubah status verifikasi')
  }
}

const suspendUser = async (userId) => {
  if (confirm('Suspend user ini?')) {
    try {
      await api.post(`/admin/users/${userId}/suspend`)
      alert('User berhasil di-suspend')
      loadUsers()
    } catch (error) {
      alert('Gagal suspend user')
    }
  }
}

const formatDate = (date) => {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('id-ID')
}
</script>

<style scoped>
.admin-users {
  padding: 24px;
}

.filters {
  display: flex;
  gap: 10px;
  margin-bottom: 20px;
}

.filters select, .filters input {
  padding: 8px 12px;
  border: 1px solid #ddd;
  border-radius: 5px;
}

.btn-search {
  padding: 8px 20px;
  background: #667eea;
  color: white;
  border: none;
  border-radius: 5px;
  cursor: pointer;
}

.data-table {
  width: 100%;
  border-collapse: collapse;
  background: white;
  border-radius: 10px;
  overflow: hidden;
}

.data-table th,
.data-table td {
  padding: 12px;
  text-align: left;
  border-bottom: 1px solid #e0e0e0;
}

.data-table th {
  background: #f8f9fa;
  font-weight: 600;
}

.status-badge {
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 12px;
}

.status-badge.verified {
  background: #d1fae5;
  color: #059669;
}

.status-badge.unverified {
  background: #fef3c7;
  color: #d97706;
}

.actions {
  display: flex;
  gap: 5px;
}

.actions button {
  padding: 4px 8px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 12px;
}

.btn-verify {
  background: #3b82f6;
  color: white;
}

.btn-suspend {
  background: #ef4444;
  color: white;
}

.pagination {
  display: flex;
  justify-content: center;
  gap: 20px;
  margin-top: 20px;
  align-items: center;
}

.pagination button {
  padding: 8px 16px;
  border: 1px solid #ddd;
  background: white;
  cursor: pointer;
}
</style>