import axios from 'axios'

// Create axios instance
const instance = axios.create({
  baseURL: window.location.origin + '/api/v1',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    'X-Requested-With': 'XMLHttpRequest'
  }
})

// Request interceptor
instance.interceptors.request.use(
  (config) => {
    // Add CSRF token
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
    if (token) {
      config.headers['X-CSRF-TOKEN'] = token
    }

    // Add auth token if available
    const authToken = localStorage.getItem('token')
    if (authToken) {
      config.headers['Authorization'] = `Bearer ${authToken}`
    }

    return config
  },
  (error) => {
    return Promise.reject(error)
  }
)

// Response interceptor
instance.interceptors.response.use(
  (response) => response,
  async (error) => {
    const originalRequest = error.config

    // Handle 401 errors (unauthorized)
    if (error.response?.status === 401 && !originalRequest._retry) {
      originalRequest._retry = true

      try {
        // Try to refresh token
        const { useAuthStore } = await import('@/stores/auth')
        const authStore = useAuthStore()
        await authStore.refreshToken()
        
        // Retry original request
        return instance(originalRequest)
      } catch (refreshError) {
        // Refresh failed, redirect to login
        const { useAuthStore } = await import('@/stores/auth')
        const authStore = useAuthStore()
        authStore.clearAuth()
        window.location.href = '/login'
        return Promise.reject(refreshError)
      }
    }

    // Handle other errors
    if (error.response?.status === 403) {
      console.error('Access forbidden:', error.response.data)
    }

    if (error.response?.status === 422) {
      console.error('Validation error:', error.response.data)
    }

    if (error.response?.status >= 500) {
      console.error('Server error:', error.response.data)
    }

    return Promise.reject(error)
  }
)

export default instance 