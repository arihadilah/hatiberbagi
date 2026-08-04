<template>
  <div class="dashboard">
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon">💰</div>
        <div class="stat-info">
          <h3>Total Donasi</h3>
          <p class="stat-value">Rp {{ formatMoney(stats.total_donations) }}</p>
        </div>
      </div>
      
      <div class="stat-card">
        <div class="stat-icon">👥</div>
        <div class="stat-info">
          <h3>Total Donatur</h3>
          <p class="stat-value">{{ stats.total_donors }}</p>
        </div>
      </div>
      
      <div class="stat-card">
        <div class="stat-icon">🚀</div>
        <div class="stat-info">
          <h3>Campaign Aktif</h3>
          <p class="stat-value">{{ stats.active_campaigns }}</p>
        </div>
      </div>
      
      <div class="stat-card">
        <div class="stat-icon">⏳</div>
        <div class="stat-info">
          <h3>Menunggu Verifikasi</h3>
          <p class="stat-value warning">{{ stats.pending_campaigns }}</p>
        </div>
      </div>
      
      <div class="stat-card">
        <div class="stat-icon">👤</div>
        <div class="stat-info">
          <h3>Total User</h3>
          <p class="stat-value">{{ stats.total_users }}</p>
        </div>
      </div>
      
      <div class="stat-card">
        <div class="stat-icon">✨</div>
        <div class="stat-info">
          <h3>User Baru (7 hari)</h3>
          <p class="stat-value">{{ stats.new_users_week }}</p>
        </div>
      </div>
    </div>
    
    <div class="recent-section">
      <div class="card">
        <h3>Donasi Terbaru</h3>
        <table class="data-table">
          <thead>
            <tr>
              <th>Order ID</th>
              <th>Donatur</th>
              <th>Campaign</th>
              <th>Jumlah</th>
              <th>Tanggal</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="donation in recentDonations" :key="donation.id">
              <td>{{ donation.midtrans_order_id }}</td>
              <td>{{ donation.is_anonymous ? 'Anonim' : donation.donor_name }}</td>
              <td>{{ donation.campaign?.title }}</td>
              <td>Rp {{ formatMoney(donation.amount) }}</td>
              <td>{{ formatDate(donation.paid_at) }}</td>
            </tr>
            <tr v-if="recentDonations.length === 0">
              <td colspan="5" class="text-center">Belum ada donasi</td>
            </tr>
          </tbody>
        </table>
      </div>
      
      <div class="card">
        <h3>Top Campaign</h3>
        <div class="top-campaigns">
          <div v-for="campaign in topCampaigns" :key="campaign.id" class="campaign-item">
            <div class="campaign-info">
              <span class="campaign-rank">#{{ campaign.rank }}</span>
              <span class="campaign-title">{{ campaign.title }}</span>
            </div>
            <span class="campaign-amount">Rp {{ formatMoney(campaign.total_raised) }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '@/services/api'

const stats = ref({
  total_donations: 0,
  total_donors: 0,
  active_campaigns: 0,
  pending_campaigns: 0,
  total_users: 0,
  new_users_week: 0
})

const recentDonations = ref([])
const topCampaigns = ref([])

onMounted(async () => {
  try {
    const { data } = await api.get('/admin/dashboard')
    stats.value = data.stats
    recentDonations.value = data.recent_donations || []
    
    // Add rank to top campaigns
    topCampaigns.value = (data.top_campaigns || []).map((c, index) => ({
      ...c,
      rank: index + 1
    }))
  } catch (error) {
    console.error('Failed to load dashboard:', error)
  }
})

const formatMoney = (value) => {
  return new Intl.NumberFormat('id-ID').format(value || 0)
}

const formatDate = (date) => {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('id-ID')
}
</script>

<style scoped>
.dashboard {
  padding: 24px;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 20px;
  margin-bottom: 30px;
}

.stat-card {
  background: white;
  border-radius: 10px;
  padding: 20px;
  display: flex;
  align-items: center;
  gap: 15px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.stat-icon {
  font-size: 40px;
}

.stat-info h3 {
  margin: 0 0 5px 0;
  font-size: 14px;
  color: #666;
}

.stat-value {
  margin: 0;
  font-size: 28px;
  font-weight: bold;
  color: #333;
}

.stat-value.warning {
  color: #f59e0b;
}

.recent-section {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
}

.card {
  background: white;
  border-radius: 10px;
  padding: 20px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.card h3 {
  margin: 0 0 20px 0;
  color: #333;
}

.data-table {
  width: 100%;
  border-collapse: collapse;
}

.data-table th,
.data-table td {
  padding: 10px;
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

.top-campaigns {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.campaign-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 10px 0;
  border-bottom: 1px solid #e0e0e0;
}

.campaign-info {
  display: flex;
  align-items: center;
  gap: 10px;
}

.campaign-rank {
  font-weight: bold;
  color: #667eea;
}

.campaign-amount {
  font-weight: 500;
  color: #28a745;
}
</style>