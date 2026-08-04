import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const routes = [
    {
        path: '/',
        name: 'home',
        component: () => import('@/views/Home.vue')
    },
    {
        path: '/campaigns',
        name: 'campaigns',
        component: () => import('@/views/Campaigns.vue')
    },
    {
        path: '/login',
        name: 'login',
        component: () => import('@/views/Login.vue')
    },
    {
        path: '/register',
        name: 'register',
        component: () => import('@/views/Registrasi.vue')
    },
    {
        path: '/campaigns/:slug',
        name: 'campaign-detail',
        component: () => import('@/views/CampaignDetail.vue')
    },
    {
        path: '/create-campaign',
        name: 'create-campaign',
        component: () => import('@/views/CreateCampaign.vue'),
        meta: { requiresAuth: true }
    },
    {
        path: '/donation/success',
        name: 'donation-success',
        component: () => import('@/views/DonationSuccess.vue')
    },
    {
        path: '/admin',
        component: () => import('@/layouts/AdminLayout.vue'),
        meta: { requiresAuth: true, requiresAdmin: true },
        children: [
            { path: '', redirect: '/admin/dashboard' },
            { path: 'dashboard', component: () => import('@/views/admin/Dashboard.vue') },
            { path: 'campaigns', component: () => import('@/views/admin/Campaign.vue') },
            { path: 'users', component: () => import('@/views/admin/Users.vue') },
            { path: 'reports', component: () => import('@/views/admin/Reports.vue') }
        ]
    }
]

const router = createRouter({
    history: createWebHistory(),
    routes
})

// Navigation Guard
router.beforeEach((to, from, next) => {
    const auth = useAuthStore()
    
    if (to.meta.requiresAuth && !auth.isLoggedIn) {
        next({ name: 'login' })
    } else if (to.meta.requiresAdmin && !auth.isAdmin) {
        next({ name: 'home' })
    } else {
        next()
    }
})

export default router