<template>
  <AppLayout>
    <div class="py-12">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="md:flex md:items-center md:justify-between mb-8">
          <div class="flex-1 min-w-0">
            <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
              Campaigns
            </h2>
          </div>
          <div class="mt-4 flex md:mt-0 md:ml-4">
            <router-link
              :to="{ name: 'campaigns.create' }"
              class="ml-3 inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
            >
              Create Campaign
            </router-link>
          </div>
        </div>

        <!-- Filters -->
        <div class="bg-white shadow rounded-lg p-6 mb-6">
          <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
            <div>
              <label for="search" class="block text-sm font-medium text-gray-700">Search</label>
              <input
                v-model="filters.search"
                type="text"
                id="search"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                placeholder="Search campaigns..."
              />
            </div>
            <div>
              <label for="category" class="block text-sm font-medium text-gray-700">Category</label>
              <select
                v-model="filters.category"
                id="category"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
              >
                <option value="">All Categories</option>
                <option value="environmental">Environmental</option>
                <option value="social">Social</option>
                <option value="education">Education</option>
                <option value="healthcare">Healthcare</option>
                <option value="other">Other</option>
              </select>
            </div>
            <div>
              <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
              <select
                v-model="filters.status"
                id="status"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
              >
                <option value="">All Statuses</option>
                <option value="active">Active</option>
                <option value="completed">Completed</option>
                <option value="inactive">Inactive</option>
              </select>
            </div>
          </div>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="text-center py-8">
          <div class="inline-flex items-center">
            <svg class="animate-spin h-8 w-8 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span class="ml-2 text-gray-600">Loading campaigns...</span>
          </div>
        </div>

        <!-- Campaign Grid -->
        <div v-else-if="campaigns.length > 0" class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
          <CampaignCard
            v-for="campaign in campaigns"
            :key="campaign.id"
            :campaign="campaign"
          />
        </div>

        <!-- Empty State -->
        <div v-else class="bg-white shadow rounded-lg p-12 text-center">
          <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
          <h3 class="mt-2 text-sm font-medium text-gray-900">No campaigns found</h3>
          <p class="mt-1 text-sm text-gray-500">Get started by creating a new campaign.</p>
          <div class="mt-6">
            <router-link
              :to="{ name: 'campaigns.create' }"
              class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
            >
              New Campaign
            </router-link>
          </div>
        </div>

        <!-- Pagination -->
        <div v-if="campaigns.length > 0 && pagination.total > pagination.per_page" class="mt-6">
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
import { ref, onMounted, watch } from 'vue'
import { debounce } from 'lodash'
import axios from '@/utils/axios'
import AppLayout from '@/Components/Layout/AppLayout.vue'
import CampaignCard from '@/Components/Campaigns/CampaignCard.vue'
import Pagination from '@/Components/UI/Pagination.vue'

const campaigns = ref([])
const loading = ref(true)
const filters = ref({
  search: '',
  category: '',
  status: ''
})
const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 12,
  total: 0
})

const fetchCampaigns = async (page = 1) => {
  loading.value = true
  try {
    const response = await axios.get('/campaigns', {
      params: {
        page,
        search: filters.value.search,
        category: filters.value.category,
        status: filters.value.status
      }
    })
    campaigns.value = response.data.data
    pagination.value = response.data.meta
  } catch (error) {
    console.error('Failed to fetch campaigns:', error)
  } finally {
    loading.value = false
  }
}

const changePage = (page) => {
  fetchCampaigns(page)
}

const debouncedFetch = debounce(() => {
  fetchCampaigns(1)
}, 300)

watch(filters, debouncedFetch, { deep: true })

onMounted(() => {
  fetchCampaigns()
})
</script> 