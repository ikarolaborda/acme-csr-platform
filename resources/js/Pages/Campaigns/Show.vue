<template>
  <AppLayout>
    <div class="py-12">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Loading State -->
        <div v-if="loading" class="text-center py-8">
          <div class="inline-flex items-center">
            <svg class="animate-spin h-8 w-8 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span class="ml-2 text-gray-600">Loading campaign...</span>
          </div>
        </div>

        <!-- Campaign Content -->
        <div v-else-if="campaign" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
          <!-- Main Content -->
          <div class="lg:col-span-2">
            <!-- Campaign Image -->
            <div class="bg-white shadow rounded-lg overflow-hidden mb-6">
              <img
                v-if="campaign.image_url"
                :src="campaign.image_url"
                :alt="campaign.title"
                class="w-full h-96 object-cover"
              />
              <div v-else class="w-full h-96 bg-gray-200 flex items-center justify-center">
                <svg class="w-20 h-20 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
              </div>
            </div>

            <!-- Campaign Details -->
            <div class="bg-white shadow rounded-lg p-6">
              <div class="flex items-center justify-between mb-4">
                <span :class="[
                  'inline-flex items-center px-3 py-1 rounded-full text-sm font-medium',
                  categoryClasses[campaign.category] || 'bg-gray-100 text-gray-800'
                ]">
                  {{ formatCategory(campaign.category) }}
                </span>
                <div v-if="isOwner" class="flex space-x-2">
                  <router-link
                    :to="{ name: 'campaigns.edit', params: { slug: campaign.slug } }"
                    class="text-indigo-600 hover:text-indigo-900"
                  >
                    Edit
                  </router-link>
                </div>
              </div>

              <h1 class="text-3xl font-bold text-gray-900 mb-4">{{ campaign.title }}</h1>
              
              <div class="flex items-center text-gray-500 mb-6">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                <span>Created by {{ campaign.user?.name || 'Anonymous' }}</span>
                <span class="mx-2">•</span>
                <span>{{ formatDate(campaign.created_at) }}</span>
              </div>

              <div class="prose max-w-none">
                <p class="whitespace-pre-wrap">{{ campaign.description }}</p>
              </div>

              <!-- Recent Donations -->
              <div class="mt-8">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Donations</h3>
                <div v-if="donations.length > 0" class="space-y-3">
                  <div v-for="donation in donations" :key="donation.id" class="flex items-center justify-between py-3 border-b border-gray-200">
                    <div class="flex items-center">
                      <div class="w-10 h-10 bg-gray-300 rounded-full flex items-center justify-center">
                        <span class="text-sm font-medium text-gray-600">{{ getInitials(donation.user?.name || 'Anonymous') }}</span>
                      </div>
                      <div class="ml-3">
                        <p class="text-sm font-medium text-gray-900">{{ donation.is_anonymous ? 'Anonymous' : donation.user?.name }}</p>
                        <p class="text-xs text-gray-500">{{ formatDate(donation.created_at) }}</p>
                      </div>
                    </div>
                    <span class="text-sm font-semibold text-gray-900">${{ formatAmount(donation.amount) }}</span>
                  </div>
                </div>
                <div v-else class="text-center py-6 text-gray-500">
                  No donations yet. Be the first to donate!
                </div>
              </div>
            </div>
          </div>

          <!-- Sidebar -->
          <div class="lg:col-span-1">
            <!-- Donation Box -->
            <div class="bg-white shadow rounded-lg p-6 sticky top-4">
              <div class="mb-6">
                <div class="flex justify-between text-sm text-gray-600 mb-2">
                  <span>${{ formatAmount(campaign.current_amount) }} raised</span>
                  <span>${{ formatAmount(campaign.goal_amount) }} goal</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-3 mb-2">
                  <div
                    class="bg-indigo-600 h-3 rounded-full transition-all duration-300"
                    :style="{ width: `${progressPercentage}%` }"
                  ></div>
                </div>
                <p class="text-sm text-gray-500">{{ progressPercentage }}% of goal reached</p>
              </div>

              <div class="mb-6 space-y-2">
                <div class="flex justify-between text-sm">
                  <span class="text-gray-600">Status:</span>
                  <span :class="[
                    'font-medium',
                    campaign.status === 'active' ? 'text-green-600' : 'text-gray-500'
                  ]">
                    {{ campaign.status }}
                  </span>
                </div>
                <div class="flex justify-between text-sm">
                  <span class="text-gray-600">Days remaining:</span>
                  <span class="font-medium text-gray-900">{{ daysRemaining }}</span>
                </div>
                <div class="flex justify-between text-sm">
                  <span class="text-gray-600">Donors:</span>
                  <span class="font-medium text-gray-900">{{ campaign.donors_count || 0 }}</span>
                </div>
              </div>

              <router-link
                v-if="campaign.status === 'active' && !campaign.is_expired"
                :to="{ name: 'donations.create', params: { campaignSlug: campaign.slug } }"
                class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-base font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
              >
                Donate Now
              </router-link>
              <div v-else class="text-center text-gray-500">
                This campaign is no longer accepting donations
              </div>

              <!-- Share Buttons -->
              <div class="mt-6 pt-6 border-t border-gray-200">
                <p class="text-sm font-medium text-gray-900 mb-3">Share this campaign</p>
                <div class="flex space-x-3">
                  <button
                    @click="shareOnFacebook"
                    class="flex-1 inline-flex justify-center items-center px-3 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
                  >
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                      <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                    </svg>
                  </button>
                  <button
                    @click="shareOnTwitter"
                    class="flex-1 inline-flex justify-center items-center px-3 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
                  >
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                      <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
                    </svg>
                  </button>
                  <button
                    @click="copyLink"
                    class="flex-1 inline-flex justify-center items-center px-3 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
                  >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                    </svg>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Error State -->
        <div v-else class="bg-white shadow rounded-lg p-12 text-center">
          <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <h3 class="mt-2 text-sm font-medium text-gray-900">Campaign not found</h3>
          <p class="mt-1 text-sm text-gray-500">The campaign you're looking for doesn't exist.</p>
          <div class="mt-6">
            <router-link
              :to="{ name: 'campaigns.index' }"
              class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700"
            >
              Browse Campaigns
            </router-link>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import axios from '@/utils/axios'
import AppLayout from '@/Components/Layout/AppLayout.vue'

const route = useRoute()
const authStore = useAuthStore()

const campaign = ref(null)
const donations = ref([])
const loading = ref(true)

const categoryClasses = {
  environmental: 'bg-green-100 text-green-800',
  social: 'bg-blue-100 text-blue-800',
  education: 'bg-purple-100 text-purple-800',
  healthcare: 'bg-red-100 text-red-800',
  other: 'bg-gray-100 text-gray-800'
}

const isOwner = computed(() => {
  return authStore.user?.id === campaign.value?.user_id
})

const progressPercentage = computed(() => {
  if (!campaign.value?.goal_amount) return 0
  const percentage = (campaign.value.current_amount / campaign.value.goal_amount) * 100
  return Math.min(Math.round(percentage), 100)
})

const daysRemaining = computed(() => {
  if (!campaign.value?.end_date) return 0
  const endDate = new Date(campaign.value.end_date)
  const today = new Date()
  const diffTime = endDate - today
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))
  return Math.max(0, diffDays)
})

const formatAmount = (amount) => {
  return new Intl.NumberFormat('en-US', {
    minimumFractionDigits: 0,
    maximumFractionDigits: 0
  }).format(amount)
}

const formatCategory = (category) => {
  return category.charAt(0).toUpperCase() + category.slice(1)
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

const getInitials = (name) => {
  return name
    .split(' ')
    .map(n => n[0])
    .join('')
    .toUpperCase()
    .slice(0, 2)
}

const shareOnFacebook = () => {
  const url = window.location.href
  window.open(`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}`, '_blank')
}

const shareOnTwitter = () => {
  const url = window.location.href
  const text = `Check out this campaign: ${campaign.value.title}`
  window.open(`https://twitter.com/intent/tweet?url=${encodeURIComponent(url)}&text=${encodeURIComponent(text)}`, '_blank')
}

const copyLink = () => {
  navigator.clipboard.writeText(window.location.href)
  // TODO: Show success notification
}

const fetchCampaign = async () => {
  loading.value = true
  try {
    const response = await axios.get(`/campaigns/${route.params.slug}`)
    campaign.value = response.data.data
    
    // Fetch recent donations
    const donationsResponse = await axios.get(`/donations/campaign/${campaign.value.id}/summary`)
    donations.value = donationsResponse.data.data.recent_donations || []
  } catch (error) {
    console.error('Failed to fetch campaign:', error)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchCampaign()
})
</script> 