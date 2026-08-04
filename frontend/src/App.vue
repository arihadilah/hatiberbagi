<template>
  <div id="app">
    <nav class="navbar">
      <div class="nav-container">
        <router-link to="/" class="logo">HatiBerbagi</router-link>
        
        <div class="nav-links">
          <router-link to="/">Home</router-link>
          <router-link to="/create-campaign" v-if="auth.isLoggedIn">Buat Campaign</router-link>
          <router-link to="/admin/dashboard" v-if="auth.isAdmin">Admin Panel</router-link>
          <button v-if="!auth.isLoggedIn" @click="goToLogin" class="login-btn">Login</button>
          <button v-if="auth.isLoggedIn" @click="handleLogout" class="logout-btn">Logout</button>
        </div>
      </div>
    </nav>
    
    <router-view />
  </div>
</template>

<script setup>
import { onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const auth = useAuthStore()

onMounted(() => {
  auth.fetchMe()
})

const goToLogin = () => {
  router.push('/login')
}

const handleLogout = async () => {
  await auth.logout()
  router.push('/')
}
</script>

<style>
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.navbar {
  background: white;
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
  position: sticky;
  top: 0;
  z-index: 100;
}

.nav-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 15px 20px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.logo {
  font-size: 24px;
  font-weight: bold;
  text-decoration: none;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

.nav-links {
  display: flex;
  gap: 20px;
  align-items: center;
}

.nav-links a {
  text-decoration: none;
  color: #333;
}

.login-btn, .logout-btn {
  padding: 8px 20px;
  border: none;
  border-radius: 5px;
  cursor: pointer;
}

.login-btn {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
}

.logout-btn {
  background: #dc3545;
  color: white;
}
</style>