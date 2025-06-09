<template>
  <AppLayout>
    <div class="py-12">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="md:flex md:items-center md:justify-between mb-8">
          <div class="flex-1 min-w-0">
            <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
              My Donations
            </h2>
          </div>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
          <StatCard
            title="Total Donated"
            :value="`$${formatAmount(stats.total_amount)}`"
            icon="currency-dollar"
            color="green"
          />
          <StatCard
            title="Total Donations"
            :value="stats.total_count"
            icon="heart"
            color="red"
          />
          <StatCard
            title="Campaigns Supported"
            :value="stats.campaigns_supported"
            icon="collection"
            color="blue"
          />
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="text-center py-8">
          <div class="inline-flex items-center">
            <svg class="animate-spin h-8 w-8 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span class="ml-2 text-gray-600">Loading donations...</span>
          </div>
        </div>

        <!-- Donations List -->
        <div v-else-if="donations && donations.length > 0" class="bg-white shadow overflow-hidden sm:rounded-md">
          <ul class="divide-y divide-gray-200">
            <li v-for="donation in donations" :key="donation.id">
              <div class="px-4 py-4 sm:px-6">
                <div class="flex items-center justify-between">
                  <div class="flex items-center">
                    <div class="flex-shrink-0">
                      <div class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center">
                        <svg class="h-6 w-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7" />
                        </svg>
                      </div>
                    </div>
                    <div class="ml-4">
                      <div class="text-sm font-medium text-gray-900">
                        {{ donation.campaign?.title || 'Unknown Campaign' }}
                      </div>
                      <div class="text-sm text-gray-500">
                        {{ formatDate(donation.created_at) }}
                      </div>
                    </div>
                  </div>
                  <div class="flex items-center">
                    <div class="text-right mr-4">
                      <div class="text-lg font-semibold text-gray-900">
                        ${{ formatAmount(donation.amount) }}
                      </div>
                      <div class="text-xs text-gray-500">
                        {{ donation.donation_number }}
                      </div>
                    </div>
                    <div class="flex items-center space-x-2">
                      <span
                        :class="[
                          'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                          donation.status === 'completed' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'
                        ]"
                      >
                        {{ donation.status }}
                      </span>
                      <router-link
                        :to="{ name: 'donations.show', params: { donationNumber: donation.donation_number } }"
                        class="text-indigo-600 hover:text-indigo-900"
                      >
                        View
                      </router-link>
                    </div>
                  </div>
                </div>
                <div v-if="donation.message" class="mt-2">
                  <p class="text-sm text-gray-600 italic">
                    "{{ donation.message }}"
                  </p>
                </div>
              </div>
            </li>
          </ul>
        </div>

        <!-- Empty State -->
        <div v-else class="bg-white shadow rounded-lg p-12 text-center">
          <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7" />
          </svg>
          <h3 class="mt-2 text-sm font-medium text-gray-900">No donations yet</h3>
          <p class="mt-1 text-sm text-gray-500">Start supporting campaigns that matter to you.</p>
          <div class="mt-6">
            <router-link
              :to="{ name: 'campaigns.index' }"
              class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
            >
              Browse Campaigns
            </router-link>
          </div>
        </div>

        <!-- Pagination -->
        <div v-if="donations && donations.length > 0 && pagination && pagination.total > pagination.per_page" class="mt-6">
          <Pagination
            :current-page="pagination.current_page"
            :total-pages="pagination.last_page"
            @page-changed="changePage"
          />
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import axios from '@/utils/axios'
import AppLayout from '@/Components/Layout/AppLayout.vue'
import StatCard from '@/Components/UI/StatCard.vue'
import Pagination from '@/Components/UI/Pagination.vue'

const donations = ref([])
const loading = ref(true)
const stats = ref({
  total_amount: 0,
  total_count: 0,
  campaigns_supported: 0
})
const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 20,
  total: 0
})

const formatAmount = (amount) => {
  // Handle null, undefined, or non-numeric values
  const numAmount = Number(amount);
  if (isNaN(numAmount) || amount === null || amount === undefined) {
    return '0';
  }
  
  return new Intl.NumberFormat('en-US', {
    minimumFractionDigits: 0,
    maximumFractionDigits: 0
  }).format(numAmount);
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const fetchDonations = async (page = 1) => {
  loading.value = true
  try {
    const response = await axios.get('/donations/my-donations', {
      params: { page, per_page: 20 }
    })
    donations.value = response.data.data || []
    pagination.value = response.data.meta || {
      current_page: 1,
      last_page: 1,
      per_page: 20,
      total: 0
    }
  } catch (error) {
    console.error('Failed to fetch donations:', error)
    // Set safe defaults on error
    donations.value = []
    pagination.value = {
      current_page: 1,
      last_page: 1,
      per_page: 20,
      total: 0
    }
  } finally {
    loading.value = false
  }
}

const fetchStats = async () => {
  try {
    const response = await axios.get('/donations/statistics')
    const data = response.data.data || response.data
    stats.value = {
      total_amount: data.total_amount || 0,
      total_count: data.total_count || 0,
      campaigns_supported: data.unique_campaigns || data.campaigns_supported || 0
    }
  } catch (error) {
    console.error('Failed to fetch statistics:', error)
    stats.value = {
      total_amount: 0,
      total_count: 0,
      campaigns_supported: 0
    }
  }
}

const changePage = (page) => {
  fetchDonations(page)
}

onMounted(async () => {
  // Wait for auth store to be initialized
  const authStore = useAuthStore()
  if (!authStore.initialized) {
    await authStore.initializeAuth()
  }
  
  fetchDonations()
  fetchStats()
})
</script> 