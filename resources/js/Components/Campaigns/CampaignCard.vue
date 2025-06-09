<template>
  <div class="bg-white overflow-hidden shadow-lg rounded-lg hover:shadow-xl transition-shadow duration-300">
    <router-link :to="{ name: 'campaigns.show', params: { slug: campaign.slug } }">
      <div class="aspect-w-16 aspect-h-9 bg-gray-200">
        <img
          v-if="campaign.image_url"
          :src="campaign.image_url"
          :alt="campaign.title"
          class="object-cover w-full h-48"
        />
        <div v-else class="flex items-center justify-center h-48 bg-gray-300">
          <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
          </svg>
        </div>
      </div>
    </router-link>
    
    <div class="p-6">
      <div class="flex items-center justify-between mb-2">
        <span :class="[
          'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
          categoryClasses[campaign.category] || 'bg-gray-100 text-gray-800'
        ]">
          {{ formatCategory(campaign.category) }}
        </span>
        <span :class="[
          'text-xs font-medium',
          campaign.status === 'active' ? 'text-green-600' : 'text-gray-500'
        ]">
          {{ campaign.status }}
        </span>
      </div>
      
      <router-link :to="{ name: 'campaigns.show', params: { slug: campaign.slug } }">
        <h3 class="text-lg font-semibold text-gray-900 hover:text-indigo-600 transition-colors mb-2">
          {{ campaign.title }}
        </h3>
      </router-link>
      
      <p class="text-gray-600 text-sm mb-4 line-clamp-2">
        {{ campaign.description }}
      </p>
      
      <!-- Progress bar -->
      <div class="mb-4">
        <div class="flex justify-between text-sm text-gray-600 mb-1">
          <span>${{ formatAmount(campaign.current_amount) }} raised</span>
          <span>${{ formatAmount(campaign.goal_amount) }} goal</span>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-2">
          <div
            class="bg-indigo-600 h-2 rounded-full transition-all duration-300"
            :style="{ width: `${progressPercentage}%` }"
          ></div>
        </div>
        <div class="flex justify-between text-xs text-gray-500 mt-1">
          <span>{{ progressPercentage }}% funded</span>
          <span>{{ daysRemaining }} days left</span>
        </div>
      </div>
      
      <!-- Footer -->
      <div class="flex items-center justify-between">
        <div class="flex items-center text-sm text-gray-500">
          <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
          </svg>
          <span>{{ campaign.user?.name || 'Anonymous' }}</span>
        </div>
        <router-link
          :to="{ name: 'donations.create', params: { campaignSlug: campaign.slug } }"
          class="inline-flex items-center px-3 py-1 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
        >
          Donate
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  campaign: {
    type: Object,
    required: true
  }
})

const categoryClasses = {
  environmental: 'bg-green-100 text-green-800',
  social: 'bg-blue-100 text-blue-800',
  education: 'bg-purple-100 text-purple-800',
  healthcare: 'bg-red-100 text-red-800',
  other: 'bg-gray-100 text-gray-800'
}

const progressPercentage = computed(() => {
  if (!props.campaign.goal_amount) return 0
  const percentage = (props.campaign.current_amount / props.campaign.goal_amount) * 100
  return Math.min(Math.round(percentage), 100)
})

const daysRemaining = computed(() => {
  if (!props.campaign.end_date) return 0
  const endDate = new Date(props.campaign.end_date)
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
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style> 