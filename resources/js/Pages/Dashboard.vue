<template>
  <AppLayout>
    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Welcome Section -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
          <div class="p-6 bg-white border-b border-gray-200">
            <h1 class="text-2xl font-semibold text-gray-900">
              Welcome back, {{ authStore.user?.name }}!
            </h1>
            <p class="mt-1 text-sm text-gray-600">
              Here's what's happening with your CSR initiatives today.
            </p>
          </div>
        </div>

        <!-- Statistics Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
          <StatCard
            title="Total Donated"
            :value="`$${formatNumber(stats.totalDonated)}`"
            icon="currency-dollar"
            color="green"
          />
          <StatCard
            title="Campaigns Created"
            :value="stats.campaignsCreated"
            icon="flag"
            color="blue"
          />
          <StatCard
            title="Active Campaigns"
            :value="stats.activeCampaigns"
            icon="lightning-bolt"
            color="yellow"
          />
          <StatCard
            title="Total Donors"
            :value="stats.totalDonors"
            icon="users"
            color="purple"
          />
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <!-- Recent Donations -->
          <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
              <h2 class="text-lg font-medium text-gray-900 mb-4">Recent Donations</h2>
              <div v-if="recentDonations.length > 0" class="space-y-4">
                <div v-for="donation in recentDonations" :key="donation.id" class="flex items-center justify-between">
                  <div class="flex items-center">
                    <img
                      :src="donation.user.avatar_url"
                      :alt="donation.user.name"
                      class="h-10 w-10 rounded-full"
                    >
                    <div class="ml-3">
                      <p class="text-sm font-medium text-gray-900">
                        {{ donation.is_anonymous ? 'Anonymous' : donation.user.name }}
                      </p>
                      <p class="text-sm text-gray-500">
                        donated to {{ donation.campaign.title }}
                      </p>
                    </div>
                  </div>
                  <div class="text-right">
                    <p class="text-sm font-medium text-gray-900">${{ donation.amount }}</p>
                    <p class="text-xs text-gray-500">{{ formatDate(donation.created_at) }}</p>
                  </div>
                </div>
              </div>
              <div v-else class="text-center py-4">
                <p class="text-gray-500">No recent donations</p>
              </div>
            </div>
          </div>

          <!-- Active Campaigns -->
          <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
              <h2 class="text-lg font-medium text-gray-900 mb-4">Active Campaigns</h2>
              <div v-if="activeCampaigns.length > 0" class="space-y-4">
                <div v-for="campaign in activeCampaigns" :key="campaign.id">
                  <router-link
                    :to="{ name: 'campaigns.show', params: { slug: campaign.slug } }"
                    class="block hover:bg-gray-50 -mx-2 px-2 py-2 rounded transition"
                  >
                    <div class="flex items-center justify-between mb-1">
                      <h3 class="text-sm font-medium text-gray-900">{{ campaign.title }}</h3>
                      <span class="text-sm text-gray-500">{{ campaign.days_remaining }} days left</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                      <div
                        class="bg-blue-600 h-2 rounded-full"
                        :style="`width: ${campaign.progress_percentage}%`"
                      ></div>
                    </div>
                    <div class="flex justify-between mt-1">
                      <span class="text-xs text-gray-500">${{ formatNumber(campaign.current_amount) }} raised</span>
                      <span class="text-xs text-gray-500">${{ formatNumber(campaign.goal_amount) }} goal</span>
                    </div>
                  </router-link>
                </div>
              </div>
              <div v-else class="text-center py-4">
                <p class="text-gray-500">No active campaigns</p>
                <router-link
                  :to="{ name: 'campaigns.create' }"
                  class="mt-2 inline-flex items-center px-3 py-1 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700"
                >
                  Create Campaign
                </router-link>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import AppLayout from '@/Components/Layout/AppLayout.vue'
import StatCard from '@/Components/UI/StatCard.vue'
import axios from '@/utils/axios'

const authStore = useAuthStore()

const stats = ref({
  totalDonated: 0,
  campaignsCreated: 0,
  activeCampaigns: 0,
  totalDonors: 0
})

const recentDonations = ref([])
const activeCampaigns = ref([])

const formatNumber = (num) => {
  return new Intl.NumberFormat('en-US').format(num)
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const fetchDashboardData = async () => {
  try {
    // Fetch statistics
    const [statsResponse, donationsResponse, campaignsResponse] = await Promise.all([
      axios.get('/api/v1/donations/statistics'),
      axios.get('/api/v1/donations/recent'),
      axios.get('/api/v1/campaigns/featured')
    ])

    // Handle stats data safely
    const statsData = statsResponse.data.data || statsResponse.data || {}
    const donationsData = donationsResponse.data.data || donationsResponse.data || []
    const campaignsData = campaignsResponse.data.data || campaignsResponse.data || []

    stats.value = {
      totalDonated: authStore.user?.total_donated || 0,
      campaignsCreated: authStore.user?.campaigns_created || 0,
      activeCampaigns: Array.isArray(campaignsData) ? campaignsData.length : 0,
      totalDonors: statsData.unique_donors || 0
    }

    recentDonations.value = Array.isArray(donationsData) ? donationsData.slice(0, 5) : []
    activeCampaigns.value = Array.isArray(campaignsData) ? campaignsData.slice(0, 5) : []
  } catch (error) {
    console.error('Failed to fetch dashboard data:', error)
    // Set safe defaults
    stats.value = {
      totalDonated: 0,
      campaignsCreated: 0,
      activeCampaigns: 0,
      totalDonors: 0
    }
    recentDonations.value = []
    activeCampaigns.value = []
  }
}

onMounted(() => {
  fetchDashboardData()
})
</script> 