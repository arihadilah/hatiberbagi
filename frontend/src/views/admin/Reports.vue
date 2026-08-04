<template>
  <div class="admin-reports">
    <div class="filters">
      <input type="date" v-model="filters.start_date">
      <span>-</span>
      <input type="date" v-model="filters.end_date">
      <select v-model="filters.category_id">
        <option value="">Semua Kategori</option>
        <option v-for="cat in categories" :key="cat.id" :value="cat.id">
          {{ cat.name }}
        </option>
      </select>
      <button @click="loadReports" class="btn-search">Filter</button>
      <button @click="exportCSV" class="btn-export">Export CSV</button>
    </div>
    
    <div class="summary-cards">
      <div class="summary-card">
        <h4>Total Donasi</h4>
        <p class="amount">Rp {{ formatMoney(summary.total_amount) }}</p>
      </div>
      <div class="summary-card">
        <h4>Jumlah Transaksi</h4>
        <p class="count">{{ summary.total_donations }}</p>
      </div>
    </div>
    
    <table class="data-table">
      <thead>
        <tr>
          <th>Order ID</th>
          <th>Tanggal</th>
          <th>Donatur</th>
          <th>Campaign</th>
          <th>Kategori</th>
          <th>Jumlah</th>
          <th>Metode</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="donation in donations" :key="donation.id">
          <td>{{ donation.midtrans_order_id }}</td>
          <td>{{ formatDate(donation.paid_at) }}</td>
          <td>{{ donation.is_anonymous ? 'Anonim' : donation.donor_name }}</td>
          <td>{{ donation.campaign?.title }}</td>
          <td>{{ donation.campaign?.category?.name }}</td>
          <td>Rp {{ formatMoney(donation.amount) }}</td>
          <td>{{ donation.payment_type || '-' }}</td>
        </tr>
        <tr v-if="donations.length === 0">
          <td colspan="7" class="text-center">Tidak ada data</td>
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

const donations = ref([])
const categories = ref([])
const summary = ref({
  total_amount: 0,
  total_donations: 0
})
const pagination = ref(null)
const filters = ref({
  start_date: '',
  end_date: '',
  category_id: ''
})

onMounted(() => {
  loadCategories()
  loadReports()
})

const loadCategories = async () => {
  try {
    const { data } = await api.get('/categories')
    categories.value = data
  } catch (error) {
    console.error('Failed to load categories:', error)
  }
}

const loadReports = async () => {
  try {
    const params = {}
    if (filters.value.start_date) params.start_date = filters.value.start_date
    if (filters.value.end_date) params.end_date = filters.value.end_date
    if (filters.value.category_id) params.category_id = filters.value.category_id
    
    const { data } = await api.get('/admin/reports/donations', { params })
    donations.value = data.data
    pagination.value = {
      current_page: data.current_page,
      last_page: data.last_page
    }
    
    // Load summary
    const summaryData = await api.get('/admin/reports/summary', { params })
    summary.value = summaryData.data
  } catch (error) {
    console.error('Failed to load reports:', error)
  }
}

const loadPage = (page) => {
  if (page < 1 || page > pagination.value.last_page) return
  loadReports()
}

const exportCSV = async () => {
  try {
    const params = new URLSearchParams()
    if (filters.value.start_date) params.append('start_date', filters.value.start_date)
    if (filters.value.end_date) params.append('end_date', filters.value.end_date)
    
    window.open(`/api/admin/reports/export?${params.toString()}`, '_blank')
  } catch (error) {
    alert('Gagal export data')
  }
}

const formatMoney = (value) => {
  return new Intl.NumberFormat('id-ID').format(value || 0)
}

const formatDate = (date) => {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('id-ID')
}
</script>

<style scoped>
.admin-reports {
  padding: 24px;
}

.filters {
  display: flex;
  gap: 10px;
  margin-bottom: 20px;
  align-items: center;
  flex-wrap: wrap;
}

.filters input, .filters select {
  padding: 8px 12px;
  border: 1px solid #ddd;
  border-radius: 5px;
}

.btn-search, .btn-export {
  padding: 8px 20px;
  border: none;
  border-radius: 5px;
  cursor: pointer;
}

.btn-search {
  background: #667eea;
  color: white;
}

.btn-export {
  background: #10b981;
  color: white;
}

.summary-cards {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 20px;
  margin-bottom: 30px;
}

.summary-card {
  background: white;
  padding: 20px;
  border-radius: 10px;
  text-align: center;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.summary-card h4 {
  margin: 0 0 10px 0;
  color: #666;
}

.summary-card .amount, .summary-card .count {
  margin: 0;
  font-size: 32px;
  font-weight: bold;
  color: #333;
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

.text-center {
  text-align: center;
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