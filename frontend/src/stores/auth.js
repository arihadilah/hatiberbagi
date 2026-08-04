import { defineStore } from 'pinia'
import api from '@/services/api'

export const useAuthStore = defineStore('auth', {
    state: () => ({
        user: null,
        token: localStorage.getItem('token') || null
    }),
    
    getters: {
        isLoggedIn: (state) => !!state.token,
        isAdmin: (state) => state.user?.role === 'admin',
        userName: (state) => state.user?.name || ''
    },
    
    actions: {
        async login(credentials) {
            try {
                const { data } = await api.post('/login', credentials)
                this.token = data.token
                this.user = data.user
                localStorage.setItem('token', data.token)
                return true
            } catch (error) {
                console.error('Login failed:', error)
                return false
            }
        },
        
        async logout() {
            try {
                await api.post('/logout')
            } catch (error) {
                console.error('Logout error:', error)
            } finally {
                this.token = null
                this.user = null
                localStorage.removeItem('token')
            }
        },
        
        async fetchMe() {
            if (!this.token) return
            try {
                const { data } = await api.get('/me')
                this.user = data
            } catch (error) {
                console.error('Fetch user failed:', error)
            }
        }
    }
})