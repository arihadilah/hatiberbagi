<template>
  <div class="create-campaign-container">
    <div class="form-card">
      <h2>Buat Campaign Baru</h2>
      <p class="subtitle">Mulai galang dana untuk kebaikan</p>
      
      <form @submit.prevent="handleSubmit">
        <div class="form-group">
          <label>Judul Campaign *</label>
          <input 
            type="text" 
            v-model="form.title" 
            required 
            placeholder="Contoh: Bantu Pendidikan Anak Yatim"
          >
        </div>
        
        <div class="form-group">
          <label>Kategori *</label>
          <select v-model="form.category_id" required>
            <option value="">Pilih Kategori</option>
            <option v-for="cat in categories" :key="cat.id" :value="cat.id">
              {{ cat.icon }} {{ cat.name }}
            </option>
          </select>
        </div>
        
        <div class="form-group">
          <label>Target Dana *</label>
          <input 
            type="number" 
            v-model="form.target_amount" 
            required 
            placeholder="Minimal Rp 100.000"
            min="100000"
          >
        </div>
        
        <div class="form-group">
          <label>Batas Waktu *</label>
          <input 
            type="date" 
            v-model="form.deadline" 
            required 
            :min="minDate"
          >
        </div>
        
        <div class="form-group">
          <label>Foto Thumbnail</label>
          <input 
            type="file" 
            @change="handleFileUpload" 
            accept="image/*"
          >
          <div v-if="previewImage" class="image-preview">
            <img :src="previewImage" alt="Preview">
          </div>
        </div>
        
        <div class="form-group">
          <label>Deskripsi Campaign *</label>
          <textarea 
            v-model="form.description" 
            required 
            rows="10"
            placeholder="Ceritakan detail campaign Anda..."
          ></textarea>
        </div>
        
        <div class="form-group">
          <label>
            <input type="checkbox" v-model="form.is_featured">
            Jadikan Campaign Unggulan (hanya admin)
          </label>
          <small v-if="!isAdmin">* Hanya admin yang bisa mengaktifkan fitur ini</small>
        </div>
        
        <button type="submit" :disabled="loading" class="submit-btn">
          {{ loading ? 'Menyimpan...' : 'Buat Campaign' }}
        </button>
        
        <p v-if="error" class="error">{{ error }}</p>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import api from '@/services/api'

const router = useRouter()
const auth = useAuthStore()

const form = ref({
  title: '',
  category_id: '',
  target_amount: '',
  deadline: '',
  description: '',
  is_featured: false
})

const categories = ref([])
const loading = ref(false)
const error = ref('')
const selectedFile = ref(null)
const previewImage = ref('')

const isAdmin = computed(() => auth.isAdmin)
const minDate = computed(() => {
  const today = new Date()
  return today.toISOString().split('T')[0]
})

onMounted(async () => {
  await fetchCategories()
})

const fetchCategories = async () => {
  try {
    const { data } = await api.get('/categories')
    categories.value = data
  } catch (err) {
    console.error('Failed to fetch categories:', err)
  }
}

const handleFileUpload = (event) => {
  const file = event.target.files[0]
  if (file) {
    selectedFile.value = file
    previewImage.value = URL.createObjectURL(file)
  }
}

const handleSubmit = async () => {
  loading.value = true
  error.value = ''
  
  try {
    const formData = new FormData()
    formData.append('title', form.value.title)
    formData.append('category_id', form.value.category_id)
    formData.append('target_amount', form.value.target_amount)
    formData.append('deadline', form.value.deadline)
    formData.append('description', form.value.description)
    formData.append('is_featured', form.value.is_featured ? 1 : 0)
    
    if (selectedFile.value) {
      formData.append('thumbnail', selectedFile.value)
    }
    
    const { data } = await api.post('/campaigns', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })
    
    // Redirect ke halaman campaign yang baru dibuat
    router.push(`/campaigns/${data.slug}`)
    
  } catch (err) {
    error.value = err.response?.data?.message || 'Gagal membuat campaign'
    console.error(err)
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.create-campaign-container {
  max-width: 800px;
  margin: 0 auto;
  padding: 40px 20px;
}

.form-card {
  background: white;
  border-radius: 10px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
  padding: 30px;
}

h2 {
  text-align: center;
  color: #333;
  margin-bottom: 10px;
}

.subtitle {
  text-align: center;
  color: #666;
  margin-bottom: 30px;
}

.form-group {
  margin-bottom: 20px;
}

label {
  display: block;
  margin-bottom: 8px;
  font-weight: 500;
  color: #333;
}

input, select, textarea {
  width: 100%;
  padding: 10px;
  border: 1px solid #ddd;
  border-radius: 5px;
  font-size: 16px;
}

input:focus, select:focus, textarea:focus {
  outline: none;
  border-color: #667eea;
}

.image-preview {
  margin-top: 10px;
}

.image-preview img {
  max-width: 200px;
  border-radius: 5px;
}

small {
  display: block;
  color: #999;
  font-size: 12px;
  margin-top: 5px;
}

.submit-btn {
  width: 100%;
  padding: 15px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border: none;
  border-radius: 5px;
  font-size: 16px;
  cursor: pointer;
  margin-top: 20px;
}

.submit-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.error {
  color: red;
  text-align: center;
  margin-top: 15px;
}
</style>