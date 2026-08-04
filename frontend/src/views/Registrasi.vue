<template>
  <div class="register-container">
    <div class="register-card">
      <h2>Daftar Akun Baru</h2>
      
      <form @submit.prevent="handleRegister">
        <div class="form-group">
          <label>Nama Lengkap</label>
          <input type="text" v-model="form.name" required>
        </div>
        
        <div class="form-group">
          <label>Email</label>
          <input type="email" v-model="form.email" required>
        </div>
        
        <div class="form-group">
          <label>Password</label>
          <input type="password" v-model="form.password" required>
        </div>
        
        <div class="form-group">
          <label>Konfirmasi Password</label>
          <input type="password" v-model="form.password_confirmation" required>
        </div>
        
        <div class="form-group">
          <label>Daftar Sebagai</label>
          <select v-model="form.role">
            <option value="donor">Donatur</option>
            <option value="campaigner">Penggalang Dana</option>
          </select>
        </div>
        
        <button type="submit" :disabled="loading">
          {{ loading ? 'Memproses...' : 'Daftar' }}
        </button>
        
        <p v-if="error" class="error">{{ error }}</p>
      </form>
      
      <p class="login-link">
        Sudah punya akun? <router-link to="/login">Login</router-link>
      </p>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import api from '@/services/api'

const router = useRouter()
const auth = useAuthStore()

const form = ref({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  role: 'donor'
})

const loading = ref(false)
const error = ref('')

const handleRegister = async () => {
  loading.value = true
  error.value = ''
  
  try {
    const { data } = await api.post('/register', form.value)
    auth.token = data.token
    auth.user = data.user
    localStorage.setItem('token', data.token)
    router.push('/')
  } catch (err) {
    error.value = err.response?.data?.message || 'Registrasi gagal'
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.register-container {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  padding: 20px;
}

.register-card {
  background: white;
  padding: 40px;
  border-radius: 10px;
  width: 100%;
  max-width: 450px;
}

h2 {
  text-align: center;
  margin-bottom: 30px;
  color: #333;
}

.form-group {
  margin-bottom: 20px;
}

label {
  display: block;
  margin-bottom: 5px;
  color: #666;
}

input, select {
  width: 100%;
  padding: 10px;
  border: 1px solid #ddd;
  border-radius: 5px;
  font-size: 16px;
}

button {
  width: 100%;
  padding: 12px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border: none;
  border-radius: 5px;
  font-size: 16px;
  cursor: pointer;
}

.error {
  color: red;
  text-align: center;
  margin-top: 15px;
}

.login-link {
  text-align: center;
  margin-top: 20px;
  color: #666;
}

.login-link a {
  color: #667eea;
  text-decoration: none;
}
</style>