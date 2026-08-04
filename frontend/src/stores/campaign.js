import { defineStore } from 'pinia'
import api from '@/services/api'

export const useCampaignStore = defineStore('campaign', {
    state: () => ({
        campaigns: [],
        currentCampaign: null,
        loading: false,
        pagination: null
    }),
    
    actions: {
        async fetchAll(params = {}) {
            this.loading = true
            try {
                const { data } = await api.get('/campaigns', { params })
                this.campaigns = data.data
                this.pagination = {
                    current_page: data.current_page,
                    last_page: data.last_page,
                    per_page: data.per_page,
                    total: data.total
                }
            } catch (error) {
                console.error('Fetch campaigns failed:', error)
            } finally {
                this.loading = false
            }
        },
        
        async fetchBySlug(slug) {
            this.loading = true
            try {
                const { data } = await api.get(`/campaigns/${slug}`)
                this.currentCampaign = data
            } catch (error) {
                console.error('Fetch campaign failed:', error)
            } finally {
                this.loading = false
            }
        },
        
        clearCurrent() {
            this.currentCampaign = null
        }
    }
})