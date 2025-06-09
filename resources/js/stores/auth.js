import { defineStore } from 'pinia'
import axios from '@/utils/axios'
import router from '@/router'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    token: localStorage.getItem('token'),
    initialized: false
  }),

  getters: {
    isAuthenticated: (state) => !!state.token,
    isAdmin: (state) => state.user?.role === 'admin'
  },

  actions: {
    async initializeAuth() {
      if (this.initialized) return
      
      if (this.token) {
        try {
          await this.fetchUser()
        } catch (error) {
          console.error('Failed to fetch user during initialization:', error)
          this.logout()
        }
      }
      
      this.initialized = true
    },

    async login(credentials) {
      try {
        const response = await axios.post('/auth/login', credentials)
        const { access_token, user } = response.data

        this.token = access_token
        this.user = user
        localStorage.setItem('token', access_token)

        // Redirect to intended route or dashboard
        const redirectTo = router.currentRoute.value.query.redirect || '/dashboard'
        router.push(redirectTo)

        return response
      } catch (error) {
        console.error('Login failed:', error)
        throw error
      }
    },

    async logout() {
      try {
        await axios.post('/auth/logout')
      } catch (error) {
        console.error('Logout error:', error)
      } finally {
        this.token = null
        this.user = null
        localStorage.removeItem('token')
        router.push('/login')
      }
    },

    async fetchUser() {
      try {
        const response = await axios.get('/auth/me')
        this.user = response.data.data
        return response.data.data
      } catch (error) {
        console.error('Failed to fetch user:', error)
        throw error
      }
    },

    async refresh() {
      try {
        const response = await axios.post('/auth/refresh')
        const { access_token } = response.data

        this.token = access_token
        localStorage.setItem('token', access_token)

        return response
      } catch (error) {
        console.error('Token refresh failed:', error)
        this.logout()
        throw error
      }
    },

    async refreshToken() {
      return this.refresh()
    },

    clearAuth() {
      this.token = null
      this.user = null
      localStorage.removeItem('token')
    }
  }
}) 