<template>
  <div class="p-6">
    <!-- Header -->
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-900">Campaign Management</h1>
      <p class="text-gray-600">Manage all campaigns in the system</p>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-6">
      <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="p-5">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                </svg>
              </div>
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="text-sm font-medium text-gray-500 truncate">Total Campaigns</dt>
                <dd class="text-lg font-medium text-gray-900">{{ statistics.total_campaigns || 0 }}</dd>
              </dl>
            </div>
          </div>
        </div>
      </div>

      <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="p-5">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
              </div>
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="text-sm font-medium text-gray-500 truncate">Active Campaigns</dt>
                <dd class="text-lg font-medium text-gray-900">{{ statistics.by_status?.active || 0 }}</dd>
              </dl>
            </div>
          </div>
        </div>
      </div>

      <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="p-5">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-yellow-500 rounded-full flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
              </div>
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="text-sm font-medium text-gray-500 truncate">Pending Approval</dt>
                <dd class="text-lg font-medium text-gray-900">{{ statistics.pending_approval || 0 }}</dd>
              </dl>
            </div>
          </div>
        </div>
      </div>

      <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="p-5">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-purple-500 rounded-full flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                </svg>
              </div>
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="text-sm font-medium text-gray-500 truncate">Featured</dt>
                <dd class="text-lg font-medium text-gray-900">{{ statistics.featured_campaigns || 0 }}</dd>
              </dl>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Filters and Actions -->
    <div class="bg-white shadow rounded-lg mb-6">
      <div class="p-6">
        <div class="grid grid-cols-1 gap-6 md:grid-cols-5">
          <!-- Search -->
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

          <!-- Status Filter -->
          <div>
            <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
            <select
              v-model="filters.status"
              id="status"
              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
            >
              <option value="">All Statuses</option>
              <option value="draft">Draft</option>
              <option value="pending">Pending</option>
              <option value="active">Active</option>
              <option value="completed">Completed</option>
              <option value="cancelled">Cancelled</option>
            </select>
          </div>

          <!-- Category Filter -->
          <div>
            <label for="category" class="block text-sm font-medium text-gray-700">Category</label>
            <select
              v-model="filters.category"
              id="category"
              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
            >
              <option value="">All Categories</option>
              <option value="environment">Environment</option>
              <option value="education">Education</option>
              <option value="health">Health</option>
              <option value="community">Community</option>
              <option value="disaster_relief">Disaster Relief</option>
              <option value="poverty">Poverty</option>
              <option value="other">Other</option>
            </select>
          </div>

          <!-- Bulk Actions -->
          <div>
            <label for="bulk-action" class="block text-sm font-medium text-gray-700">Bulk Action</label>
            <select
              v-model="bulkAction"
              id="bulk-action"
              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
              :disabled="selectedCampaigns.length === 0"
            >
              <option value="">Select Action</option>
              <option value="active">Enable Selected</option>
              <option value="cancelled">Disable Selected</option>
              <option value="pending">Mark as Pending</option>
            </select>
          </div>

          <!-- Apply Button -->
          <div class="flex items-end">
            <button
              @click="applyBulkAction"
              :disabled="!bulkAction || selectedCampaigns.length === 0"
              class="w-full inline-flex justify-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Apply
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="text-center py-12">
      <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
      <p class="mt-2 text-sm text-gray-500">Loading campaigns...</p>
    </div>

    <!-- Campaigns Table -->
    <div v-else class="bg-white shadow rounded-lg overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th scope="col" class="relative px-6 py-3">
                <input
                  type="checkbox"
                  class="absolute left-4 top-1/2 -mt-2 h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                  :checked="selectedCampaigns.length === campaigns.data?.length && campaigns.data?.length > 0"
                  @change="toggleSelectAll"
                />
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Campaign
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Creator
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Status
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Category
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Goal
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Progress
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Created
              </th>
              <th scope="col" class="relative px-6 py-3">
                <span class="sr-only">Actions</span>
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="campaign in campaigns.data" :key="campaign.id" class="hover:bg-gray-50">
              <td class="relative px-6 py-4">
                <input
                  type="checkbox"
                  class="absolute left-4 top-1/2 -mt-2 h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                  :value="campaign.id"
                  v-model="selectedCampaigns"
                />
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div class="flex-shrink-0 h-10 w-10">
                    <img
                      class="h-10 w-10 rounded-lg object-cover"
                      :src="campaign.featured_image_url || '/images/default-campaign.jpg'"
                      :alt="campaign.title"
                    />
                  </div>
                  <div class="ml-4">
                    <div class="text-sm font-medium text-gray-900">{{ campaign.title }}</div>
                    <div class="text-sm text-gray-500 flex items-center">
                      {{ campaign.slug }}
                      <span v-if="campaign.is_featured" class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-800">
                        Featured
                      </span>
                    </div>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">{{ campaign.user?.name }}</div>
                <div class="text-sm text-gray-500">{{ campaign.user?.email }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="getStatusBadgeClass(campaign.status)" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                  {{ getStatusLabel(campaign.status) }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ getCategoryLabel(campaign.category) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                ${{ formatNumber(campaign.goal_amount) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">${{ formatNumber(campaign.current_amount) }}</div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                  <div class="bg-indigo-600 h-2 rounded-full" :style="`width: ${Math.min(100, campaign.progress_percentage)}%`"></div>
                </div>
                <div class="text-xs text-gray-500">{{ campaign.progress_percentage }}%</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ formatDate(campaign.created_at) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <div class="flex items-center space-x-2">
                  <!-- Enable/Disable Button -->
                  <button
                    v-if="campaign.status === 'active'"
                    @click="disableCampaign(campaign)"
                    class="text-red-600 hover:text-red-900"
                    title="Disable Campaign"
                  >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14L5 9m0 0l5-5m-5 5h14"></path>
                    </svg>
                  </button>
                  <button
                    v-else-if="campaign.status === 'draft' || campaign.status === 'cancelled'"
                    @click="enableCampaign(campaign)"
                    class="text-green-600 hover:text-green-900"
                    title="Enable Campaign"
                  >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10l5 5m0 0l-5 5m5-5H9"></path>
                    </svg>
                  </button>
                  <button
                    v-else-if="campaign.status === 'pending'"
                    @click="approveCampaign(campaign)"
                    class="text-blue-600 hover:text-blue-900"
                    title="Approve Campaign"
                  >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                  </button>

                  <!-- Toggle Featured -->
                  <button
                    @click="toggleFeatured(campaign)"
                    :class="campaign.is_featured ? 'text-purple-600 hover:text-purple-900' : 'text-gray-400 hover:text-gray-600'"
                    title="Toggle Featured"
                  >
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                      <path d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                    </svg>
                  </button>

                  <!-- View Button -->
                  <button
                    @click="viewCampaign(campaign)"
                    class="text-indigo-600 hover:text-indigo-900"
                    title="View Campaign"
                  >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="campaigns.data?.length" class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
        <div class="flex-1 flex justify-between sm:justify-end">
          <button
            @click="prevPage"
            :disabled="!campaigns.prev_page_url"
            class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            Previous
          </button>
          <button
            @click="nextPage"
            :disabled="!campaigns.next_page_url"
            class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            Next
          </button>
        </div>
      </div>

      <!-- Empty State -->
      <div v-if="!campaigns.data?.length && !loading" class="text-center py-12">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">No campaigns found</h3>
        <p class="mt-1 text-sm text-gray-500">Try adjusting your filters or search terms.</p>
      </div>
    </div>

    <!-- Reason Modal -->
    <div v-if="showReasonModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <h3 class="text-lg font-bold text-gray-900 mb-4">{{ reasonModalTitle }}</h3>
        <textarea
          v-model="reasonText"
          rows="4"
          class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
          placeholder="Enter reason (optional)"
        ></textarea>
        <div class="flex justify-end space-x-3 mt-4">
          <button
            @click="cancelReason"
            class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400"
          >
            Cancel
          </button>
          <button
            @click="confirmAction"
            class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700"
          >
            Confirm
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, watch } from 'vue'
import { useAuthStore } from '@/stores/auth'
import axios from 'axios'

const authStore = useAuthStore()

// Reactive data
const loading = ref(true)
const campaigns = ref({ data: [] })
const statistics = ref({})
const selectedCampaigns = ref([])
const bulkAction = ref('')
const showReasonModal = ref(false)
const reasonText = ref('')
const reasonModalTitle = ref('')
const pendingAction = ref(null)

const filters = reactive({
  search: '',
  status: '',
  category: '',
  page: 1
})

// Watch filters for auto-search
let searchTimeout
watch(() => filters.search, () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    filters.page = 1
    fetchCampaigns()
  }, 500)
})

watch(() => [filters.status, filters.category], () => {
  filters.page = 1
  fetchCampaigns()
})

// Methods
const fetchCampaigns = async () => {
  loading.value = true
  try {
    const params = new URLSearchParams()
    if (filters.search) params.append('search', filters.search)
    if (filters.status) params.append('status', filters.status)
    if (filters.category) params.append('category', filters.category)
    params.append('page', filters.page)
    params.append('per_page', 20)

    const response = await axios.get(`/admin/campaigns?${params}`)
    campaigns.value = response.data
  } catch (error) {
    console.error('Failed to fetch campaigns:', error)
  } finally {
    loading.value = false
  }
}

const fetchStatistics = async () => {
  try {
    const response = await axios.get('/admin/campaigns/statistics')
    statistics.value = response.data
  } catch (error) {
    console.error('Failed to fetch statistics:', error)
  }
}

const enableCampaign = async (campaign) => {
  try {
    await axios.post(`/admin/campaigns/${campaign.id}/enable`)
    await fetchCampaigns()
    await fetchStatistics()
  } catch (error) {
    console.error('Failed to enable campaign:', error)
  }
}

const disableCampaign = (campaign) => {
  pendingAction.value = { type: 'disable', campaign }
  reasonModalTitle.value = 'Disable Campaign'
  reasonText.value = ''
  showReasonModal.value = true
}

const approveCampaign = async (campaign) => {
  try {
    await axios.post(`/admin/campaigns/${campaign.id}/approve`)
    await fetchCampaigns()
    await fetchStatistics()
  } catch (error) {
    console.error('Failed to approve campaign:', error)
  }
}

const toggleFeatured = async (campaign) => {
  try {
    await axios.post(`/admin/campaigns/${campaign.id}/toggle-featured`)
    await fetchCampaigns()
    await fetchStatistics()
  } catch (error) {
    console.error('Failed to toggle featured status:', error)
  }
}

const viewCampaign = (campaign) => {
  window.open(`/campaigns/${campaign.slug}`, '_blank')
}

const toggleSelectAll = () => {
  if (selectedCampaigns.value.length === campaigns.value.data.length) {
    selectedCampaigns.value = []
  } else {
    selectedCampaigns.value = campaigns.value.data.map(c => c.id)
  }
}

const applyBulkAction = () => {
  if (!bulkAction.value || selectedCampaigns.value.length === 0) return
  
  pendingAction.value = { type: 'bulk', action: bulkAction.value, ids: [...selectedCampaigns.value] }
  reasonModalTitle.value = `${getBulkActionLabel(bulkAction.value)} ${selectedCampaigns.value.length} campaigns`
  reasonText.value = ''
  showReasonModal.value = true
}

const confirmAction = async () => {
  try {
    if (pendingAction.value.type === 'disable') {
      await axios.post(`/admin/campaigns/${pendingAction.value.campaign.id}/disable`, {
        reason: reasonText.value
      })
    } else if (pendingAction.value.type === 'bulk') {
      await axios.post('/admin/campaigns/bulk-update-status', {
        campaign_ids: pendingAction.value.ids,
        status: pendingAction.value.action,
        reason: reasonText.value
      })
      selectedCampaigns.value = []
      bulkAction.value = ''
    }
    
    await fetchCampaigns()
    await fetchStatistics()
    cancelReason()
  } catch (error) {
    console.error('Failed to perform action:', error)
  }
}

const cancelReason = () => {
  showReasonModal.value = false
  pendingAction.value = null
  reasonText.value = ''
}

const prevPage = () => {
  if (campaigns.value.prev_page_url) {
    filters.page--
    fetchCampaigns()
  }
}

const nextPage = () => {
  if (campaigns.value.next_page_url) {
    filters.page++
    fetchCampaigns()
  }
}

// Utility methods
const getStatusBadgeClass = (status) => {
  const classes = {
    draft: 'bg-gray-100 text-gray-800',
    pending: 'bg-yellow-100 text-yellow-800',
    active: 'bg-green-100 text-green-800',
    completed: 'bg-blue-100 text-blue-800',
    cancelled: 'bg-red-100 text-red-800'
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

const getStatusLabel = (status) => {
  const labels = {
    draft: 'Draft',
    pending: 'Pending',
    active: 'Active',
    completed: 'Completed',
    cancelled: 'Cancelled'
  }
  return labels[status] || status
}

const getCategoryLabel = (category) => {
  const labels = {
    environment: 'Environment',
    education: 'Education',
    health: 'Health',
    community: 'Community',
    disaster_relief: 'Disaster Relief',
    poverty: 'Poverty',
    other: 'Other'
  }
  return labels[category] || category
}

const getBulkActionLabel = (action) => {
  const labels = {
    active: 'Enable',
    cancelled: 'Disable',
    pending: 'Mark as Pending'
  }
  return labels[action] || action
}

const formatNumber = (num) => {
  return new Intl.NumberFormat().format(num)
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString()
}

// Initialize
onMounted(() => {
  fetchCampaigns()
  fetchStatistics()
})
</script> 