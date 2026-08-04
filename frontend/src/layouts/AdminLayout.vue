<template>
  <div class="admin-layout">
    <aside class="sidebar">
      <div class="logo">
        <h2>HatiBerbagi</h2>
        <span class="admin-badge">Admin Panel</span>
      </div>
      
      <nav class="nav-menu">
        <router-link to="/admin/dashboard" class="nav-item">
          <span class="icon">📊</span>
          <span>Dashboard</span>
        </router-link>
        
        <router-link to="/admin/campaigns" class="nav-item">
          <span class="icon">🎯</span>
          <span>Kelola Campaign</span>
        </router-link>
        
        <router-link to="/admin/users" class="nav-item">
          <span class="icon">👥</span>
          <span>Kelola User</span>
        </router-link>
        
        <router-link to="/admin/reports" class="nav-item">
          <span class="icon">📈</span>
          <span>Laporan</span>
        </router-link>
        
        <router-link to="/" class="nav-item">
          <span class="icon">🏠</span>
          <span>Kembali ke Beranda</span>
        </router-link>
      </nav>
      
      <div class="user-info">
        <p>{{ auth.user?.name }}</p>
        <button @click="logout" class="logout-btn">Logout</button>
      </div>
    </aside>
    
    <main class="main-content">
      <div class="content-header">
        <h1>{{ pageTitle }}</h1>
      </div>
      <router-view />
    </main>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const route = useRoute()
const auth = useAuthStore()

const pageTitle = computed(() => {
  const titles = {
    dashboard: 'Dashboard Admin',
    campaigns: 'Kelola Campaign',
    users: 'Kelola User',
    reports: 'Laporan & Statistik'
  }
  const path = route.path.split('/')[2]
  return titles[path] || 'Admin Panel'
})

const logout = async () => {
  await auth.logout()
  router.push('/login')
}
</script>

<style scoped>
.admin-layout {
  display: flex;
  min-height: 100vh;
}

.sidebar {
  width: 280px;
  background: linear-gradient(180deg, #1a1a2e 0%, #16213e 100%);
  color: white;
  display: flex;
  flex-direction: column;
  position: fixed;
  height: 100vh;
  overflow-y: auto;
}

.logo {
  padding: 30px 20px;
  text-align: center;
  border-bottom: 1px solid rgba(255,255,255,0.1);
}

.logo h2 {
  margin: 0;
  font-size: 24px;
}

.admin-badge {
  display: inline-block;
  background: rgba(255,255,255,0.2);
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 12px;
  margin-top: 8px;
}

.nav-menu {
  flex: 1;
  padding: 20px 0;
}

.nav-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 24px;
  color: rgba(255,255,255,0.8);
  text-decoration: none;
  transition: all 0.3s;
}

.nav-item:hover {
  background: rgba(255,255,255,0.1);
  color: white;
}

.nav-item.router-link-active {
  background: rgba(102, 126, 234, 0.3);
  border-left: 3px solid #667eea;
  color: white;
}

.icon {
  font-size: 20px;
}

.user-info {
  padding: 20px;
  border-top: 1px solid rgba(255,255,255,0.1);
  text-align: center;
}

.user-info p {
  margin-bottom: 10px;
  font-size: 14px;
}

.logout-btn {
  width: 100%;
  padding: 8px;
  background: rgba(220, 53, 69, 0.8);
  color: white;
  border: none;
  border-radius: 5px;
  cursor: pointer;
}

.main-content {
  flex: 1;
  margin-left: 280px;
  background: #f5f7fb;
  min-height: 100vh;
}

.content-header {
  background: white;
  padding: 20px 30px;
  border-bottom: 1px solid #e0e0e0;
}

.content-header h1 {
  margin: 0;
  color: #333;
  font-size: 24px;
}
</style>