<template>
  <AppLayout>
    <div class="py-12">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
          <h1 class="text-3xl font-bold text-gray-900">Admin Dashboard</h1>
          <p class="mt-2 text-sm text-gray-600">Overview of your CSR platform statistics and activity</p>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="text-center py-8">
          <div class="inline-flex items-center">
            <svg class="animate-spin h-8 w-8 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span class="ml-2 text-gray-600">Loading dashboard data...</span>
          </div>
        </div>

        <div v-else class="space-y-8">
          <!-- Overview Stats -->
          <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
            <div class="bg-white overflow-hidden shadow rounded-lg">
              <div class="p-5">
                <div class="flex items-center">
                  <div class="flex-shrink-0">
                    <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                  </div>
                  <div class="ml-5 w-0 flex-1">
                    <dl>
                      <dt class="text-sm font-medium text-gray-500 truncate">Total Campaigns</dt>
                      <dd class="text-lg font-medium text-gray-900">{{ stats.overview.total_campaigns }}</dd>
                    </dl>
                  </div>
                </div>
              </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
              <div class="p-5">
                <div class="flex items-center">
                  <div class="flex-shrink-0">
                    <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                    </svg>
                  </div>
                  <div class="ml-5 w-0 flex-1">
                    <dl>
                      <dt class="text-sm font-medium text-gray-500 truncate">Active Campaigns</dt>
                      <dd class="text-lg font-medium text-gray-900">{{ stats.overview.active_campaigns }}</dd>
                    </dl>
                  </div>
                </div>
              </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
              <div class="p-5">
                <div class="flex items-center">
                  <div class="flex-shrink-0">
                    <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                  </div>
                  <div class="ml-5 w-0 flex-1">
                    <dl>
                      <dt class="text-sm font-medium text-gray-500 truncate">Total Raised</dt>
                      <dd class="text-lg font-medium text-gray-900">${{ formatAmount(stats.overview.total_raised) }}</dd>
                    </dl>
                  </div>
                </div>
              </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
              <div class="p-5">
                <div class="flex items-center">
                  <div class="flex-shrink-0">
                    <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                  </div>
                  <div class="ml-5 w-0 flex-1">
                    <dl>
                      <dt class="text-sm font-medium text-gray-500 truncate">Total Donors</dt>
                      <dd class="text-lg font-medium text-gray-900">{{ stats.overview.total_donors }}</dd>
                    </dl>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Charts Row -->
          <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
            <!-- Category Breakdown -->
            <div class="bg-white shadow rounded-lg p-6">
              <h2 class="text-lg font-medium text-gray-900 mb-4">Campaigns by Category</h2>
              <div class="space-y-3">
                <div v-for="category in stats.category_breakdown" :key="category.category" class="flex items-center">
                  <div class="flex-1">
                    <div class="flex items-center justify-between">
                      <span class="text-sm font-medium text-gray-600 capitalize">{{ category.category }}</span>
                      <span class="text-sm text-gray-500">{{ category.count }} campaigns</span>
                    </div>
                    <div class="mt-1 relative pt-1">
                      <div class="overflow-hidden h-2 text-xs flex rounded bg-gray-200">
                        <div
                          :style="`width: ${(category.count / stats.overview.total_campaigns) * 100}%`"
                          class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-indigo-500"
                        ></div>
                      </div>
                    </div>
                    <div class="mt-1 text-xs text-gray-500">
                      ${{ formatAmount(category.total_raised) }} raised
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Campaign Stats -->
            <div class="bg-white shadow rounded-lg p-6">
              <h2 class="text-lg font-medium text-gray-900 mb-4">Campaign Statistics</h2>
              <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                <div>
                  <dt class="text-sm font-medium text-gray-500">Success Rate</dt>
                  <dd class="mt-1 text-3xl font-semibold text-gray-900">{{ Math.round(stats.campaigns.success_rate) }}%</dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500">Avg. Goal Amount</dt>
                  <dd class="mt-1 text-3xl font-semibold text-gray-900">${{ formatAmount(stats.campaigns.average_goal) }}</dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500">Avg. Raised</dt>
                  <dd class="mt-1 text-3xl font-semibold text-gray-900">${{ formatAmount(stats.campaigns.average_raised) }}</dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500">Avg. Donation</dt>
                  <dd class="mt-1 text-3xl font-semibold text-gray-900">${{ formatAmount(stats.donations.average_donation) }}</dd>
                </div>
              </dl>
            </div>
          </div>

          <!-- Recent Activity -->
          <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
            <!-- Recent Campaigns -->
            <div class="bg-white shadow rounded-lg">
              <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Recent Campaigns</h3>
              </div>
              <ul class="divide-y divide-gray-200">
                <li v-for="campaign in stats.recent_campaigns" :key="campaign.id" class="px-4 py-4">
                  <div class="flex items-center justify-between">
                    <div class="flex-1 min-w-0">
                      <p class="text-sm font-medium text-gray-900 truncate">{{ campaign.title }}</p>
                      <p class="text-sm text-gray-500">by {{ campaign.creator }}</p>
                    </div>
                    <div class="text-right">
                      <p class="text-sm font-medium text-gray-900">${{ formatAmount(campaign.current_amount) }} / ${{ formatAmount(campaign.goal_amount) }}</p>
                      <span
                        :class="[
                          'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                          campaign.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'
                        ]"
                      >
                        {{ campaign.status }}
                      </span>
                    </div>
                  </div>
                </li>
              </ul>
            </div>

            <!-- Top Donors -->
            <div class="bg-white shadow rounded-lg">
              <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Top Donors</h3>
              </div>
              <ul class="divide-y divide-gray-200">
                <li v-for="donor in stats.top_donors" :key="donor.id" class="px-4 py-4">
                  <div class="flex items-center justify-between">
                    <div class="flex-1 min-w-0">
                      <p class="text-sm font-medium text-gray-900 truncate">{{ donor.name }}</p>
                    </div>
                    <div class="text-right">
                      <p class="text-sm font-medium text-gray-900">${{ formatAmount(donor.total_donated) }}</p>
                    </div>
                  </div>
                </li>
              </ul>
            </div>
          </div>

          <!-- Recent Donations -->
          <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
              <h3 class="text-lg leading-6 font-medium text-gray-900">Recent Donations</h3>
            </div>
            <div class="overflow-hidden">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Donor</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Campaign</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="donation in stats.recent_donations" :key="donation.id">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ donation.donor }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ donation.campaign }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${{ formatAmount(donation.amount) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ formatDate(donation.created_at) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from '@/utils/axios'
import AppLayout from '@/Components/Layout/AppLayout.vue'

const stats = ref({
  overview: {
    total_campaigns: 0,
    active_campaigns: 0,
    total_raised: 0,
    total_donors: 0
  },
  campaigns: {
    success_rate: 0,
    average_goal: 0,
    average_raised: 0
  },
  donations: {
    average_donation: 0
  },
  category_breakdown: [],
  recent_campaigns: [],
  recent_donations: [],
  top_donors: []
})
const loading = ref(true)

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
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const fetchDashboardData = async () => {
  loading.value = true
  try {
    const response = await axios.get('/admin/dashboard')
    stats.value = response.data
  } catch (error) {
    console.error('Failed to fetch dashboard data:', error)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchDashboardData()
})
</script> 