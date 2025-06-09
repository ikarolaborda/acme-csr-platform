<template>
  <AppLayout>
    <div class="py-12">
      <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Loading State -->
        <div v-if="loadingCampaign" class="text-center py-8">
          <div class="inline-flex items-center">
            <svg class="animate-spin h-8 w-8 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span class="ml-2 text-gray-600">Loading campaign...</span>
          </div>
        </div>

        <!-- Donation Form -->
        <div v-else-if="campaign" class="bg-white shadow rounded-lg">
          <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-900">Make a Donation</h2>
            <p class="mt-1 text-sm text-gray-600">to {{ campaign.title }}</p>
          </div>
          
          <form @submit.prevent="submit" class="p-6 space-y-6">
            <!-- Campaign Summary -->
            <div class="bg-gray-50 rounded-lg p-4">
              <div class="flex items-center justify-between mb-2">
                <h3 class="text-sm font-medium text-gray-900">{{ campaign.title }}</h3>
                <span :class="[
                  'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                  categoryClasses[campaign.category] || 'bg-gray-100 text-gray-800'
                ]">
                  {{ formatCategory(campaign.category) }}
                </span>
              </div>
              <div class="text-sm text-gray-600">
                <div class="flex justify-between mb-1">
                  <span>${{ formatAmount(campaign.current_amount) }} raised</span>
                  <span>${{ formatAmount(campaign.goal_amount) }} goal</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                  <div
                    class="bg-indigo-600 h-2 rounded-full transition-all duration-300"
                    :style="{ width: `${progressPercentage}%` }"
                  ></div>
                </div>
              </div>
            </div>

            <!-- Donation Amount -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-3">Donation Amount</label>
              
              <!-- Quick Amount Buttons -->
              <div class="grid grid-cols-3 gap-3 mb-4">
                <button
                  v-for="amount in quickAmounts"
                  :key="amount"
                  type="button"
                  @click="form.amount = amount"
                  :class="[
                    'px-4 py-2 border rounded-md text-sm font-medium transition-colors',
                    form.amount === amount
                      ? 'border-indigo-500 text-indigo-700 bg-indigo-50'
                      : 'border-gray-300 text-gray-700 bg-white hover:bg-gray-50'
                  ]"
                >
                  ${{ amount }}
                </button>
              </div>

              <!-- Custom Amount Input -->
              <div class="relative rounded-md shadow-sm">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <span class="text-gray-500 sm:text-sm">$</span>
                </div>
                <input
                  v-model.number="form.amount"
                  type="number"
                  min="1"
                  step="1"
                  class="pl-7 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                  :class="{ 'border-red-300': errors.amount }"
                  placeholder="Enter custom amount"
                  required
                />
              </div>
              <p v-if="errors.amount" class="mt-1 text-sm text-red-600">{{ errors.amount[0] }}</p>
            </div>

            <!-- Personal Information -->
            <div class="space-y-4">
              <h3 class="text-sm font-medium text-gray-900">Personal Information</h3>
              
              <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                  <label for="first_name" class="block text-sm font-medium text-gray-700">First Name</label>
                  <input
                    v-model="form.first_name"
                    type="text"
                    id="first_name"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                    :class="{ 'border-red-300': errors.first_name }"
                  />
                  <p v-if="errors.first_name" class="mt-1 text-sm text-red-600">{{ errors.first_name[0] }}</p>
                </div>

                <div>
                  <label for="last_name" class="block text-sm font-medium text-gray-700">Last Name</label>
                  <input
                    v-model="form.last_name"
                    type="text"
                    id="last_name"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                    :class="{ 'border-red-300': errors.last_name }"
                  />
                  <p v-if="errors.last_name" class="mt-1 text-sm text-red-600">{{ errors.last_name[0] }}</p>
                </div>
              </div>

              <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
                <input
                  v-model="form.email"
                  type="email"
                  id="email"
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                  :class="{ 'border-red-300': errors.email }"
                />
                <p v-if="errors.email" class="mt-1 text-sm text-red-600">{{ errors.email[0] }}</p>
              </div>
            </div>

            <!-- Message -->
            <div>
              <label for="message" class="block text-sm font-medium text-gray-700">Message (Optional)</label>
              <textarea
                v-model="form.message"
                id="message"
                rows="3"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                :class="{ 'border-red-300': errors.message }"
                placeholder="Leave a message of support..."
              ></textarea>
              <p v-if="errors.message" class="mt-1 text-sm text-red-600">{{ errors.message[0] }}</p>
            </div>

            <!-- Anonymous Donation -->
            <div class="flex items-center">
              <input
                v-model="form.is_anonymous"
                id="is_anonymous"
                type="checkbox"
                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
              />
              <label for="is_anonymous" class="ml-2 block text-sm text-gray-900">
                Make this donation anonymous
              </label>
            </div>

            <!-- Payment Method -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-3">Payment Method</label>
              <div class="bg-yellow-50 border border-yellow-200 rounded-md p-4 text-sm text-yellow-800">
                <p class="font-medium">Test Mode - Credit Card</p>
                <p>This is a mock payment system. No real charges will be made.</p>
                <p class="text-xs mt-1 text-yellow-700">Using simulated credit card payment for testing.</p>
              </div>
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end space-x-4">
              <router-link
                :to="{ name: 'campaigns.show', params: { slug: campaign.slug } }"
                class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
              >
                Cancel
              </router-link>
              <button
                type="submit"
                :disabled="loading || !form.amount"
                class="inline-flex justify-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                <svg v-if="loading" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                {{ loading ? 'Processing...' : `Donate $${form.amount || 0}` }}
              </button>
            </div>
          </form>
        </div>

        <!-- Error State -->
        <div v-else class="bg-white shadow rounded-lg p-12 text-center">
          <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <h3 class="mt-2 text-sm font-medium text-gray-900">Campaign not found</h3>
          <p class="mt-1 text-sm text-gray-500">The campaign you're trying to donate to doesn't exist.</p>
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
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import axios from '@/utils/axios'
import AppLayout from '@/Components/Layout/AppLayout.vue'

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()

const campaign = ref(null)
const loadingCampaign = ref(true)
const loading = ref(false)
const errors = ref({})

const quickAmounts = [10, 25, 50, 100, 250, 500]

const form = ref({
  campaign_id: '',
  amount: 25,
  first_name: authStore.user?.name?.split(' ')[0] || '',
  last_name: authStore.user?.name?.split(' ').slice(1).join(' ') || '',
  email: authStore.user?.email || '',
  message: '',
  is_anonymous: false,
  payment_method: 'credit_card', // Default payment method for test mode
  currency: 'USD' // Default currency
})

const categoryClasses = {
  environmental: 'bg-green-100 text-green-800',
  social: 'bg-blue-100 text-blue-800',
  education: 'bg-purple-100 text-purple-800',
  healthcare: 'bg-red-100 text-red-800',
  other: 'bg-gray-100 text-gray-800'
}

const progressPercentage = computed(() => {
  if (!campaign.value?.goal_amount) return 0
  const percentage = (campaign.value.current_amount / campaign.value.goal_amount) * 100
  return Math.min(Math.round(percentage), 100)
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

const fetchCampaign = async () => {
  loadingCampaign.value = true
  try {
    const response = await axios.get(`/campaigns/${route.params.campaignSlug}`)
    campaign.value = response.data.data
    form.value.campaign_id = campaign.value.id
  } catch (error) {
    console.error('Failed to fetch campaign:', error)
  } finally {
    loadingCampaign.value = false
  }
}

const submit = async () => {
  loading.value = true
  errors.value = {}
  
  try {
    const response = await axios.post('/donations', form.value)
    
    // Redirect to donation confirmation
    router.push({ 
      name: 'donations.show', 
      params: { donationNumber: response.data.data.donation_number } 
    })
  } catch (error) {
    if (error.response?.status === 422) {
      errors.value = error.response.data.errors
    } else {
      console.error('Failed to process donation:', error)
      // TODO: Show error notification
    }
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchCampaign()
})
</script> 