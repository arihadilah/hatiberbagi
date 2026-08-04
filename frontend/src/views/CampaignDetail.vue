<template>
  <div class="campaign-detail" v-if="campaign">
    <div class="campaign-header">
      <img :src="campaign.thumbnail || 'https://via.placeholder.com/1200x400'" :alt="campaign.title">
      <div class="overlay">
        <h1>{{ campaign.title }}</h1>
        <p>oleh {{ campaign.user?.name || 'Anonymous' }}</p>
      </div>
    </div>
    
    <div class="campaign-content">
      <div class="main-content">
        <div class="campaign-stats">
          <div class="stat">
            <span class="value">Rp {{ formatMoney(campaign.raised_amount) }}</span>
            <span class="label">Terkumpul</span>
          </div>
          <div class="stat">
            <span class="value">{{ campaign.donor_count || 0 }}</span>
            <span class="label">Donatur</span>
          </div>
          <div class="stat">
            <span class="value">{{ campaign.days_left || 0 }} hari</span>
            <span class="label">Tersisa</span>
          </div>
        </div>
        
        <div class="progress-bar">
          <div class="progress" :style="{ width: campaign.progress_percent + '%' }"></div>
        </div>
        
        <div class="donation-section">
          <h3>Donasi Sekarang</h3>
          
          <div class="nominal-buttons">
            <button 
              v-for="nominal in [25000, 50000, 100000, 250000]" 
              :key="nominal"
              :class="{ active: selectedAmount === nominal }"
              @click="selectedAmount = nominal"
            >
              Rp {{ formatMoney(nominal) }}
            </button>
            <button :class="{ active: selectedAmount === 'custom' }" @click="selectedAmount = 'custom'">
              Custom
            </button>
          </div>
          
          <input 
            v-if="selectedAmount === 'custom'"
            type="number"
            v-model="customAmount"
            placeholder="Masukkan nominal"
            class="custom-input"
          >
          
          <div class="donor-info">
            <label>
              <input type="checkbox" v-model="isAnonymous">
              Donasi Anonim
            </label>
          </div>
          <input
            v-if="!isAnonymous"
            type="text"
            v-model="donorName"
            placeholder="Nama kamu (opsional)"
            class="custom-input"
            style="margin-bottom: 10px;"/>        
          <textarea 
            v-model="donationMessage" 
            placeholder="Tulis pesan dukungan (opsional)"
            rows="3"
          ></textarea>
          
          <button @click="processDonation" :disabled="donating" class="donate-btn">
            {{ donating ? 'Memproses...' : 'Donasi Sekarang' }}
          </button>
        </div>
        
        <div class="campaign-description">
          <h3>Cerita Campaign</h3>
          <p>{{ campaign.description }}</p>
        </div>
      </div>
    </div>
  </div>
  
  <div v-else class="loading">
    Loading...
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useCampaignStore } from '@/stores/campaign'
import api from '@/services/api'

const route         = useRoute()
const router        = useRouter()
const campaignStore = useCampaignStore()

const campaign       = computed(() => campaignStore.currentCampaign)
const selectedAmount = ref(25000)
const customAmount   = ref(null)
const isAnonymous    = ref(false)
const donationMessage = ref('')
const donorName      = ref('')
const donating       = ref(false)

const donationAmount = computed(() => {
  if (selectedAmount.value === 'custom') {
    return parseInt(customAmount.value) || 0
  }
  return selectedAmount.value
})

onMounted(async () => {
  await campaignStore.fetchBySlug(route.params.slug)
})

const formatMoney = (value) => {
  return new Intl.NumberFormat('id-ID').format(value)
}

const processDonation = async () => {
  if (donationAmount.value < 10000) {
    alert('Minimal donasi Rp 10.000')
    return
  }

  // Pastikan snap.js sudah load
  if (!window.snap) {
    alert('Payment gateway belum siap, coba refresh halaman.')
    return
  }

  donating.value = true

  try {
    // 1. Minta snap_token dari Laravel
    const { data } = await api.post('/donations', {
      campaign_id:  campaign.value.id,
      amount:       donationAmount.value,
      is_anonymous: isAnonymous.value,
      message:      donationMessage.value || null,
      donor_name:   isAnonymous.value ? null : (donorName.value || null),
    })

    // 2. Buka popup Midtrans
    // window.snap.pay(data.snap_token, {
    //   onSuccess(result) {
    //     router.push({ 
    //       name: 'donation-success', 
    //       query: { order_id: result.order_id } 
    //     })
    //   },
    //   onPending(result) {
    //     router.push({ 
    //       name: 'donation-success', 
    //       query: { order_id: result.order_id, status: 'pending' } 
    //     })
    //   },
    //   onError(result) {
    //     alert('Pembayaran gagal: ' + (result.status_message ?? 'Coba lagi'))
    //   },
    //   onClose() {
    //     alert('Pembayaran dibatalkan.')
    //   }
    // })

    window.location.href = data.invoice_url

  } catch (error) {
    const msg = error.response?.data?.message ?? 'Gagal memproses donasi'
    alert(msg)
    console.error(error)
  } finally {
    donating.value = false
  }
}
</script>

<style scoped>
.campaign-header {
  position: relative;
  height: 400px;
  overflow: hidden;
}

.campaign-header img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.overlay {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  background: linear-gradient(transparent, rgba(0,0,0,0.8));
  color: white;
  padding: 40px;
}

.campaign-content {
  max-width: 1200px;
  margin: 0 auto;
  padding: 40px 20px;
}

.campaign-stats {
  display: flex;
  gap: 40px;
  margin-bottom: 20px;
}

.stat {
  text-align: center;
}

.stat .value {
  font-size: 28px;
  font-weight: bold;
  color: #333;
}

.stat .label {
  display: block;
  color: #666;
  font-size: 14px;
}

.progress-bar {
  background: #e0e0e0;
  border-radius: 10px;
  height: 10px;
  margin-bottom: 40px;
}

.progress {
  background: linear-gradient(90deg, #667eea, #764ba2);
  border-radius: 10px;
  height: 100%;
  transition: width 0.3s;
}

.donation-section {
  background: #f5f5f5;
  padding: 20px;
  border-radius: 10px;
  margin-bottom: 40px;
}

.nominal-buttons {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
  margin-bottom: 20px;
}

.nominal-buttons button {
  padding: 10px 20px;
  background: white;
  border: 1px solid #ddd;
  border-radius: 5px;
  cursor: pointer;
}

.nominal-buttons button.active {
  background: #667eea;
  color: white;
  border-color: #667eea;
}

.custom-input {
  width: 100%;
  padding: 10px;
  margin-bottom: 20px;
  border: 1px solid #ddd;
  border-radius: 5px;
}

.donor-info {
  margin-bottom: 20px;
}

textarea {
  width: 100%;
  padding: 10px;
  border: 1px solid #ddd;
  border-radius: 5px;
  margin-bottom: 20px;
}

.donate-btn {
  width: 100%;
  padding: 15px;
  background: linear-gradient(90deg, #667eea, #764ba2);
  color: white;
  border: none;
  border-radius: 5px;
  font-size: 18px;
  cursor: pointer;
}

.loading {
  text-align: center;
  padding: 100px;
}
</style>