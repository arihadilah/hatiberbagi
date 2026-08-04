<template>
  <div class="campaigns-page">
    <div class="container">
      <h1>Semua Campaign</h1>
      
      <!-- Filter & Search -->
      <div class="filters">
        <select v-model="filters.category_id" @change="fetchCampaigns">
          <option value="">Semua Kategori</option>
          <option v-for="cat in categories" :key="cat.id" :value="cat.id">
            {{ cat.name }}
          </option>
        </select>
        
        <input 
          type="text" 
          v-model="filters.search" 
          @input="debouncedFetch"
          placeholder="Cari campaign..."
        >
      </div>
      
      <!-- Grid Campaign -->
      <div v-if="loading" class="loading">Memuat...</div>
      
      <div v-else-if="campaigns.length === 0" class="no-data">
        Belum ada campaign.
      </div>
      
      <div v-else class="campaign-grid">
        <div v-for="campaign in campaigns" :key="campaign.id" class="campaign-card">
          <img 
            :src="getThumbnailUrl(campaign)" 
            :alt="campaign.title"
            class="campaign-image"
          >
          <div class="campaign-info">
            <router-link :to="`/campaigns/${campaign.slug}`" class="title-link">
              <h3>{{ campaign.title }}</h3>
            </router-link>
            <p class="category">{{ campaign.category?.name }}</p>
            <div class="progress-bar">
              <div class="progress" :style="{ width: campaign.progress_percent + '%' }"></div>
            </div>
            <div class="stats">
              <span>Rp {{ formatMoney(campaign.raised_amount) }}</span>
              <span>Target Rp {{ formatMoney(campaign.target_amount) }}</span>
            </div>
            <div class="days-left">
              ⏱️ {{ campaign.days_left }} hari lagi
            </div>
          </div>
        </div>
      </div>
      
      <!-- Pagination -->
      <div v-if="lastPage > 1" class="pagination">
        <button @click="prevPage" :disabled="currentPage === 1">Prev</button>
        <span>Halaman {{ currentPage }} dari {{ lastPage }}</span>
        <button @click="nextPage" :disabled="currentPage === lastPage">Next</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '@/services/api'

const campaigns = ref([])
const categories = ref([])
const loading = ref(false)
const currentPage = ref(1)
const lastPage = ref(1)

const filters = ref({
  category_id: '',
  search: ''
})

let debounceTimer

const formatMoney = (amount) => {
  return new Intl.NumberFormat('id-ID').format(amount)
}

const getThumbnailUrl = (campaign) => {
     console.log('thumbnail:', campaign.thumbnail) // Cek isinya apa
  if (campaign.thumbnail) {
    // Jika thumbnail sudah berupa URL lengkap
    if (campaign.thumbnail.startsWith('http')) {
      return campaign.thumbnail
    }
    // Jika thumbnail hanya nama file
    return `${import.meta.env.VITE_API_URL}/storage/${campaign.thumbnail}`
  }
  // Placeholder default
  return 'https://via.placeholder.com/400x200?text=No+Image'
}

const fetchCampaigns = async () => {
  loading.value = true
  try {
    const { data } = await api.get('/campaigns', {
      params: {
        page: currentPage.value,
        category_id: filters.value.category_id || undefined,
        search: filters.value.search || undefined
      }
    })
    campaigns.value = data.data
    lastPage.value = data.last_page
  } catch (err) {
    console.error(err)
  } finally {
    loading.value = false
  }
}

const fetchCategories = async () => {
  try {
    const { data } = await api.get('/categories')
    categories.value = data
  } catch (err) {
    console.error(err)
  }
}

const debouncedFetch = () => {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => {
    currentPage.value = 1
    fetchCampaigns()
  }, 500)
}

const prevPage = () => {
  if (currentPage.value > 1) {
    currentPage.value--
    fetchCampaigns()
  }
}

const nextPage = () => {
  if (currentPage.value < lastPage.value) {
    currentPage.value++
    fetchCampaigns()
  }
}

onMounted(() => {
  fetchCategories()
  fetchCampaigns()
})
</script>

<style scoped>
.campaigns-page {
  padding: 40px 20px;
  max-width: 1200px;
  margin: 0 auto;
}

h1 {
  font-size: 32px;
  margin-bottom: 30px;
}

.filters {
  display: flex;
  gap: 15px;
  margin-bottom: 30px;
}

.filters select, .filters input {
  padding: 10px 15px;
  border: 1px solid #ddd;
  border-radius: 8px;
  font-size: 14px;
}

.filters input {
  flex: 1;
}

.campaign-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 25px;
}

.campaign-card {
  border: 1px solid #eee;
  border-radius: 12px;
  overflow: hidden;
  transition: transform 0.2s;
  background: white;
}

.campaign-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 5px 20px rgba(0,0,0,0.1);
}

.campaign-image {
  width: 100%;
  height: 200px;
  object-fit: cover;
  display: block;
}

.campaign-info {
  padding: 15px;
}

.title-link {
  text-decoration: none;
  color: inherit;
}

.title-link:hover h3 {
  color: #4CAF50;
}

.campaign-info h3 {
  font-size: 18px;
  margin-bottom: 5px;
  transition: color 0.2s;
}

.category {
  color: #888;
  font-size: 13px;
  margin-bottom: 10px;
}

.progress-bar {
  height: 8px;
  background: #eee;
  border-radius: 4px;
  overflow: hidden;
  margin: 10px 0;
}

.progress {
  height: 100%;
  background: #4CAF50;
  border-radius: 4px;
}

.stats {
  display: flex;
  justify-content: space-between;
  font-size: 14px;
  font-weight: bold;
  margin: 10px 0;
}

.days-left {
  font-size: 12px;
  color: #888;
}

.loading, .no-data {
  text-align: center;
  padding: 50px;
  color: #888;
}

.pagination {
  display: flex;
  justify-content: center;
  gap: 15px;
  margin-top: 40px;
}

.pagination button {
  padding: 8px 16px;
  border: 1px solid #ddd;
  background: white;
  border-radius: 6px;
  cursor: pointer;
}

.pagination button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}
</style>