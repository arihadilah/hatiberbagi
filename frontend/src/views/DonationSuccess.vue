<template>
  <div class="success-container">
    <div class="success-card">
      <div class="success-icon">🎉</div>
      <h1>Donasi Berhasil!</h1>
      <p>Terima kasih atas donasi Anda. Kebaikan Anda sangat berarti.</p>
      
      <div class="donation-info" v-if="donation">
        <div class="info-row">
          <span class="label">Order ID:</span>
          <span class="value">{{ donation.midtrans_order_id }}</span>
        </div>
        <div class="info-row">
          <span class="label">Jumlah Donasi:</span>
          <span class="value">Rp {{ formatMoney(donation.amount) }}</span>
        </div>
        <div class="info-row">
          <span class="label">Status:</span>
          <span class="value status-paid">{{ donation.status }}</span>
        </div>
      </div>
      
      <div class="actions">
        <button @click="goToHome" class="btn-primary">Kembali ke Beranda</button>
        <button @click="goToCampaign" class="btn-secondary" v-if="campaignId">
          Lihat Campaign
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '@/services/api'

const route = useRoute()
const router = useRouter()

const donation = ref(null)
const campaignId = ref(null)

onMounted(async () => {
  const orderId = route.query.order_id
  if (orderId) {
    try {
      const { data } = await api.get(`/donations/${orderId}`)
      donation.value = data
      campaignId.value = data.campaign_id
    } catch (error) {
      console.error('Failed to fetch donation:', error)
    }
  }
})

const formatMoney = (value) => {
  return new Intl.NumberFormat('id-ID').format(value)
}

const goToHome = () => {
  router.push('/')
}

const goToCampaign = () => {
  router.push(`/campaigns/${campaignId.value}`)
}
</script>

<style scoped>
.success-container {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 80vh;
  background: #f5f5f5;
  padding: 20px;
}

.success-card {
  background: white;
  border-radius: 10px;
  box-shadow: 0 10px 40px rgba(0,0,0,0.1);
  padding: 50px;
  text-align: center;
  max-width: 500px;
  width: 100%;
}

.success-icon {
  font-size: 80px;
  margin-bottom: 20px;
}

h1 {
  color: #28a745;
  margin-bottom: 15px;
}

p {
  color: #666;
  margin-bottom: 30px;
}

.donation-info {
  background: #f8f9fa;
  border-radius: 8px;
  padding: 20px;
  margin-bottom: 30px;
  text-align: left;
}

.info-row {
  display: flex;
  justify-content: space-between;
  padding: 8px 0;
  border-bottom: 1px solid #e0e0e0;
}

.info-row:last-child {
  border-bottom: none;
}

.label {
  font-weight: 500;
  color: #666;
}

.value {
  color: #333;
}

.status-paid {
  color: #28a745;
  font-weight: 500;
}

.actions {
  display: flex;
  gap: 15px;
  justify-content: center;
}

.btn-primary, .btn-secondary {
  padding: 12px 24px;
  border: none;
  border-radius: 5px;
  font-size: 16px;
  cursor: pointer;
}

.btn-primary {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
}

.btn-secondary {
  background: #6c757d;
  color: white;
}

.btn-primary:hover, .btn-secondary:hover {
  opacity: 0.9;
}
</style>