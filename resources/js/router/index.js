import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

// Lazy load components
const Login = () => import('@/Pages/Auth/Login.vue')
const Dashboard = () => import('@/Pages/Dashboard.vue')
const CampaignList = () => import('@/Pages/Campaigns/Index.vue')
const CampaignShow = () => import('@/Pages/Campaigns/Show.vue')
const CampaignCreate = () => import('@/Pages/Campaigns/Create.vue')
const CampaignEdit = () => import('@/Pages/Campaigns/Edit.vue')
const DonationList = () => import('@/Pages/Donations/Index.vue')
const DonationCreate = () => import('@/Pages/Donations/Create.vue')
const DonationShow = () => import('@/Pages/Donations/Show.vue')
const Profile = () => import('@/Pages/Profile/Show.vue')
const ProfileEdit = () => import('@/Pages/Profile/Edit.vue')

// Admin components
const AdminDashboard = () => import('@/Pages/Admin/Dashboard.vue')

const routes = [
  // Public routes
  {
    path: '/login',
    name: 'login',
    component: Login,
    meta: { guest: true }
  },

  // Authenticated routes
  {
    path: '/',
    name: 'dashboard',
    component: Dashboard,
    meta: { requiresAuth: true }
  },

  // Campaigns
  {
    path: '/campaigns',
    name: 'campaigns.index',
    component: CampaignList,
    meta: { requiresAuth: true }
  },
  {
    path: '/campaigns/create',
    name: 'campaigns.create',
    component: CampaignCreate,
    meta: { requiresAuth: true }
  },
  {
    path: '/campaigns/:slug',
    name: 'campaigns.show',
    component: CampaignShow,
    meta: { requiresAuth: true }
  },
  {
    path: '/campaigns/:slug/edit',
    name: 'campaigns.edit',
    component: CampaignEdit,
    meta: { requiresAuth: true }
  },

  // Donations
  {
    path: '/donations',
    name: 'donations.index',
    component: DonationList,
    meta: { requiresAuth: true }
  },
  {
    path: '/campaigns/:campaignSlug/donate',
    name: 'donations.create',
    component: DonationCreate,
    meta: { requiresAuth: true }
  },
  {
    path: '/donations/:donationNumber',
    name: 'donations.show',
    component: DonationShow,
    meta: { requiresAuth: true }
  },

  // Profile
  {
    path: '/profile',
    name: 'profile.show',
    component: Profile,
    meta: { requiresAuth: true }
  },
  {
    path: '/profile/edit',
    name: 'profile.edit',
    component: ProfileEdit,
    meta: { requiresAuth: true }
  },

  // Admin routes
  {
    path: '/admin',
    redirect: '/admin/dashboard'
  },
  {
    path: '/admin/dashboard',
    name: 'admin.dashboard',
    component: AdminDashboard,
    meta: { requiresAuth: true, requiresAdmin: true }
  },

  // Catch all
  {
    path: '/:pathMatch(.*)*',
    redirect: '/'
  }
]

export const router = createRouter({
  history: createWebHistory(),
  routes
})

// Navigation guards
router.beforeEach(async (to, from, next) => {
  const authStore = useAuthStore()
  
  // Initialize auth state if not already done
  if (!authStore.initialized) {
    await authStore.initializeAuth()
  }
  
  const isAuthenticated = authStore.isAuthenticated
  const requiresAuth = to.matched.some(record => record.meta.requiresAuth)
  const isGuestRoute = to.matched.some(record => record.meta.guest)
  const requiresAdmin = to.matched.some(record => record.meta.requiresAdmin)
  
  if (requiresAuth && !isAuthenticated) {
    // Redirect to login if trying to access protected route
    next({ name: 'login', query: { redirect: to.fullPath } })
  } else if (isGuestRoute && isAuthenticated) {
    // Redirect to dashboard if trying to access guest route while authenticated
    next({ name: 'dashboard' })
  } else if (requiresAdmin && authStore.user?.role !== 'admin') {
    // Redirect to dashboard if trying to access admin route without admin role
    next({ name: 'dashboard' })
  } else {
    next()
  }
})

export default router 