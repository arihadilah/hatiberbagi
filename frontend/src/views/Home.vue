<template>
  <div class="home">
    <!-- Hero Section -->
    <div class="hero">
      <div class="hero-content">
        <h1>Wujudkan Kebaikan Bersama</h1>
        <p>Platform crowdfunding terpercaya untuk membantu sesama</p>
        <router-link to="/campaigns" class="btn-hero">Lihat Campaign</router-link>
      </div>
    </div>
    
    <!-- Featured Campaigns -->
    <div class="featured-section" v-if="featuredCampaigns.length > 0">
      <div class="container">
        <h2>Campuran Unggulan</h2>
        <div class="campaign-grid">
          <div v-for="campaign in featuredCampaigns" :key="campaign.id" class="campaign-card">
            <router-link :to="`/campaigns/${campaign.slug}`">
              <img :src="getThumbnailUrl(campaign)" :alt="campaign.title">
              <div class="campaign-info">
                <h3>{{ campaign.title }}</h3>
                <div class="progress-bar">
                  <div class="progress" :style="{ width: campaign.progress_percent + '%' }"></div>
                </div>
                <div class="stats">
                  <span>Rp {{ formatMoney(campaign.raised_amount) }}</span>
                  <span>{{ campaign.donor_count || 0 }} donatur</span>
                </div>
              </div>
            </router-link>
          </div>
        </div>
      </div>
    </div>
    
    <!-- All Campaigns -->
    <div class="campaigns-section">
      <div class="container">
        <h2>Semua Campaign</h2>
        
        <div class="filters">
          <select v-model="filters.category_id" @change="loadCampaigns">
            <option value="">Semua Kategori</option>
            <option v-for="cat in categories" :key="cat.id" :value="cat.id">
              {{ cat.icon }} {{ cat.name }}
            </option>
          </select>
          
          <input type="text" v-model="filters.search" placeholder="Cari campaign..." @keyup.enter="loadCampaigns">
          <button @click="loadCampaigns" class="btn-search">Cari</button>
        </div>
        
        <div v-if="loading" class="loading">Memuat...</div>
        
        <div class="campaign-grid" v-else>
          <div v-for="campaign in campaigns" :key="campaign.id" class="campaign-card">
            <router-link :to="`/campaigns/${campaign.slug}`">
              <img :src="campaign.thumbnail || 'https://via.placeholder.com/400x200'" :alt="campaign.title">
              <div class="campaign-info">
                <h3>{{ campaign.title }}</h3>
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
            </router-link>
          </div>
          
          <div v-if="campaigns.length === 0" class="no-data">
            Belum ada campaign. <router-link to="/create-campaign">Buat campaign</router-link> sekarang!
          </div>
        </div>
        
        <!-- Pagination -->
        <div class="pagination" v-if="pagination">
          <button @click="loadPage(pagination.current_page - 1)" :disabled="pagination.current_page === 1">
            Sebelumnya
          </button>
          <span>Halaman {{ pagination.current_page }} dari {{ pagination.last_page }}</span>
          <button @click="loadPage(pagination.current_page + 1)" :disabled="pagination.current_page === pagination.last_page">
            Selanjutnya
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '@/services/api'

const campaigns = ref([])
const featuredCampaigns = ref([])
const categories = ref([])
const pagination = ref(null)
const loading = ref(false)
const filters = ref({
  category_id: '',
  search: ''
})

const getThumbnailUrl = (campaign) => {
  if (campaign.thumbnail) {
    return 'http://localhost:8000/storage/' + campaign.thumbnail
  }
  return 'https://via.placeholder.com/400x200?text=No+Image'
}

onMounted(() => {
  loadCategories()
  loadCampaigns()
  loadFeaturedCampaigns()
})

const loadCategories = async () => {
  try {
    const { data } = await api.get('/categories')
    categories.value = data
  } catch (error) {
    console.error('Failed to load categories:', error)
  }
}

const loadCampaigns = async () => {
  loading.value = true
  try {
    const params = { ...filters.value }
    const { data } = await api.get('/campaigns', { params })
    campaigns.value = data.data
    pagination.value = {
      current_page: data.current_page,
      last_page: data.last_page,
      per_page: data.per_page,
      total: data.total
    }
  } catch (error) {
    console.error('Failed to load campaigns:', error)
  } finally {
    loading.value = false
  }
}

const loadFeaturedCampaigns = async () => {
  try {
    const { data } = await api.get('/campaigns', { params: { featured: true, per_page: 4 } })
    featuredCampaigns.value = data.data
  } catch (error) {
    console.error('Failed to load featured campaigns:', error)
  }
}

const loadPage = (page) => {
  if (page < 1 || page > pagination.value.last_page) return
  loadCampaigns()
}

const formatMoney = (value) => {
  return new Intl.NumberFormat('id-ID').format(value || 0)
}
</script>

<style scoped>
.hero {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  text-align: center;
  padding: 80px 20px;
}

.hero h1 {
  font-size: 48px;
  margin-bottom: 20px;
}

.hero p {
  font-size: 20px;
  margin-bottom: 30px;
  opacity: 0.9;
}

.btn-hero {
  display: inline-block;
  background: white;
  color: #667eea;
  padding: 12px 30px;
  border-radius: 30px;
  text-decoration: none;
  font-weight: bold;
}

.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 20px;
}

.featured-section, .campaigns-section {
  padding: 60px 0;
}

h2 {
  font-size: 32px;
  margin-bottom: 30px;
  text-align: center;
}

.campaign-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 30px;
}

.campaign-card {
  background: white;
  border-radius: 10px;
  overflow: hidden;
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
  transition: transform 0.3s;
}

.campaign-card:hover {
  transform: translateY(-5px);
}

.campaign-card a {
  text-decoration: none;
  color: inherit;
}

.campaign-card img {
  width: 100%;
  height: 200px;
  object-fit: cover;
}

.campaign-info {
  padding: 15px;
}

.campaign-info h3 {
  margin: 0 0 10px 0;
  font-size: 18px;
}

.category {
  color: #667eea;
  font-size: 14px;
  margin-bottom: 10px;
}

.progress-bar {
  background: #e0e0e0;
  border-radius: 10px;
  height: 8px;
  margin: 10px 0;
}

.progress {
  background: linear-gradient(90deg, #667eea, #764ba2);
  border-radius: 10px;
  height: 100%;
}

.stats {
  display: flex;
  justify-content: space-between;
  font-size: 14px;
  color: #666;
}

.days-left {
  font-size: 12px;
  color: #f59e0b;
  margin-top: 10px;
}

.filters {
  display: flex;
  gap: 10px;
  margin-bottom: 30px;
  justify-content: center;
}

.filters select, .filters input {
  padding: 10px 15px;
  border: 1px solid #ddd;
  border-radius: 5px;
  font-size: 14px;
}

.btn-search {
  padding: 10px 25px;
  background: #667eea;
  color: white;
  border: none;
  border-radius: 5px;
  cursor: pointer;
}

.loading, .no-data {
  text-align: center;
  padding: 50px;
  color: #666;
}

.pagination {
  display: flex;
  justify-content: center;
  gap: 20px;
  margin-top: 40px;
  align-items: center;
}

.pagination button {
  padding: 8px 20px;
  border: 1px solid #ddd;
  background: white;
  border-radius: 5px;
  cursor: pointer;
}

.pagination button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}
</style>