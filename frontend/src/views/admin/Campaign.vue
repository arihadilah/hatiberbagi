<template>
  <div class="admin-campaigns">
    <div class="filters">
      <select v-model="filters.status" @change="loadCampaigns">
        <option value="">Semua Status</option>
        <option value="pending">Menunggu Verifikasi</option>
        <option value="active">Aktif</option>
        <option value="completed">Selesai</option>
        <option value="rejected">Ditolak</option>
      </select>
      
      <input 
        type="text" 
        v-model="filters.search" 
        placeholder="Cari campaign..."
        @keyup.enter="loadCampaigns"
      >
      
      <button @click="loadCampaigns" class="btn-search">Cari</button>
    </div>
    
    <table class="data-table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Judul</th>
          <th>Pembuat</th>
          <th>Target</th>
          <th>Terkumpul</th>
          <th>Status</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="campaign in campaigns" :key="campaign.id">
          <td>{{ campaign.id }}</td>
          <td>{{ campaign.title }}</td>
          <td>{{ campaign.user?.name }}</td>
          <td>Rp {{ formatMoney(campaign.target_amount) }}</td>
          <td>Rp {{ formatMoney(campaign.raised_amount) }}</td>
          <td>
            <span :class="'status-badge status-' + campaign.status">
              {{ getStatusText(campaign.status) }}
            </span>
          </td>
          <td class="actions">
            <button 
              v-if="campaign.status === 'pending'"
              @click="verifyCampaign(campaign.id)" 
              class="btn-verify"
            >
              Verifikasi
            </button>
            <button 
              v-if="campaign.status === 'pending'"
              @click="rejectCampaign(campaign.id)" 
              class="btn-reject"
            >
              Tolak
            </button>
            <button 
              @click="toggleFeatured(campaign.id)" 
              class="btn-featured"
              :class="{ active: campaign.is_featured }"
            >
              {{ campaign.is_featured ? '★' : '☆' }}
            </button>
            <button @click="viewCampaign(campaign.id)" class="btn-view">Lihat</button>
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
import { useRouter } from 'vue-router'
import api from '@/services/api'

const router = useRouter()
const campaigns = ref([])
const pagination = ref(null)
const filters = ref({
  status: '',
  search: ''
})

onMounted(() => {
  loadCampaigns()
})

const loadCampaigns = async () => {
  try {
    const params = {}
    if (filters.value.status) params.status = filters.value.status
    if (filters.value.search) params.search = filters.value.search
    
    const { data } = await api.get('/admin/campaigns', { params })
    campaigns.value = data.data
    pagination.value = {
      current_page: data.current_page,
      last_page: data.last_page,
      per_page: data.per_page,
      total: data.total
    }
  } catch (error) {
    console.error('Failed to load campaigns:', error)
  }
}

const loadPage = (page) => {
  if (page < 1 || page > pagination.value.last_page) return
  loadCampaigns()
}

const verifyCampaign = async (id) => {
  if (confirm('Verifikasi campaign ini?')) {
    try {
      await api.post(`/admin/campaigns/${id}/verify`)
      alert('Campaign berhasil diverifikasi')
      loadCampaigns()
    } catch (error) {
      alert('Gagal memverifikasi campaign')
    }
  }
}

const rejectCampaign = async (id) => {
  const reason = prompt('Alasan penolakan:')
  if (reason) {
    try {
      await api.post(`/admin/campaigns/${id}/reject`, { reason })
      alert('Campaign ditolak')
      loadCampaigns()
    } catch (error) {
      alert('Gagal menolak campaign')
    }
  }
}

const toggleFeatured = async (id) => {
  try {
    await api.post(`/admin/campaigns/${id}/toggle-featured`)
    loadCampaigns()
  } catch (error) {
    alert('Gagal mengubah status featured')
  }
}

const viewCampaign = (id) => {
  router.push(`/campaigns/${id}`)
}

const formatMoney = (value) => {
  return new Intl.NumberFormat('id-ID').format(value || 0)
}

const getStatusText = (status) => {
  const statusMap = {
    pending: 'Menunggu',
    active: 'Aktif',
    completed: 'Selesai',
    rejected: 'Ditolak'
  }
  return statusMap[status] || status
}
</script>

<style scoped>
.admin-campaigns {
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

.status-pending {
  background: #fef3c7;
  color: #d97706;
}

.status-active {
  background: #d1fae5;
  color: #059669;
}

.status-completed {
  background: #dbeafe;
  color: #2563eb;
}

.status-rejected {
  background: #fee2e2;
  color: #dc2626;
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
  background: #10b981;
  color: white;
}

.btn-reject {
  background: #ef4444;
  color: white;
}

.btn-featured {
  background: #f59e0b;
  color: white;
}

.btn-featured.active {
  background: #fbbf24;
}

.btn-view {
  background: #3b82f6;
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

.pagination button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}
</style>