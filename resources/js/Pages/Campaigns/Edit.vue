<template>
  <AppLayout>
    <div class="py-12">
      <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
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

        <!-- Edit Form -->
        <div v-else-if="campaign" class="bg-white shadow rounded-lg">
          <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-900">Edit Campaign</h2>
          </div>
          
          <form @submit.prevent="submit" class="p-6 space-y-6">
            <!-- Title -->
            <div>
              <label for="title" class="block text-sm font-medium text-gray-700">Campaign Title</label>
              <input
                v-model="form.title"
                type="text"
                id="title"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                :class="{ 'border-red-300': errors.title }"
                required
              />
              <p v-if="errors.title" class="mt-1 text-sm text-red-600">{{ errors.title[0] }}</p>
            </div>
            
            <!-- Description -->
            <div>
              <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
              <textarea
                v-model="form.description"
                id="description"
                rows="4"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                :class="{ 'border-red-300': errors.description }"
                required
              ></textarea>
              <p v-if="errors.description" class="mt-1 text-sm text-red-600">{{ errors.description[0] }}</p>
            </div>
            
            <!-- Category -->
            <div>
              <label for="category" class="block text-sm font-medium text-gray-700">Category</label>
              <select
                v-model="form.category"
                id="category"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                :class="{ 'border-red-300': errors.category }"
                required
              >
                <option value="environmental">Environmental</option>
                <option value="social">Social</option>
                <option value="education">Education</option>
                <option value="healthcare">Healthcare</option>
                <option value="other">Other</option>
              </select>
              <p v-if="errors.category" class="mt-1 text-sm text-red-600">{{ errors.category[0] }}</p>
            </div>
            
            <!-- Goal Amount -->
            <div>
              <label for="goal_amount" class="block text-sm font-medium text-gray-700">Goal Amount ($)</label>
              <div class="mt-1 relative rounded-md shadow-sm">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <span class="text-gray-500 sm:text-sm">$</span>
                </div>
                <input
                  v-model.number="form.goal_amount"
                  type="number"
                  id="goal_amount"
                  min="100"
                  step="1"
                  class="pl-7 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                  :class="{ 'border-red-300': errors.goal_amount }"
                  required
                />
              </div>
              <p v-if="errors.goal_amount" class="mt-1 text-sm text-red-600">{{ errors.goal_amount[0] }}</p>
            </div>
            
            <!-- Date Range -->
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
              <div>
                <label for="start_date" class="block text-sm font-medium text-gray-700">Start Date</label>
                <input
                  v-model="form.start_date"
                  type="date"
                  id="start_date"
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                  :class="{ 'border-red-300': errors.start_date }"
                  required
                />
                <p v-if="errors.start_date" class="mt-1 text-sm text-red-600">{{ errors.start_date[0] }}</p>
              </div>
              
              <div>
                <label for="end_date" class="block text-sm font-medium text-gray-700">End Date</label>
                <input
                  v-model="form.end_date"
                  type="date"
                  id="end_date"
                  :min="form.start_date"
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                  :class="{ 'border-red-300': errors.end_date }"
                  required
                />
                <p v-if="errors.end_date" class="mt-1 text-sm text-red-600">{{ errors.end_date[0] }}</p>
              </div>
            </div>
            
            <!-- Status -->
            <div>
              <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
              <select
                v-model="form.status"
                id="status"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                :class="{ 'border-red-300': errors.status }"
              >
                <option value="draft">Draft</option>
                <option value="pending">Pending Review</option>
                <option value="active">Active</option>
                <option value="completed">Completed</option>
                <option value="cancelled">Cancelled</option>
              </select>
              <p v-if="errors.status" class="mt-1 text-sm text-red-600">{{ errors.status[0] }}</p>
            </div>
            
            <!-- Image Upload -->
            <div>
              <label class="block text-sm font-medium text-gray-700">Campaign Image</label>
              <div v-if="campaign.featured_image && !imagePreview" class="mt-2 mb-4">
                <img :src="`/storage/${campaign.featured_image}`" alt="Current campaign image" class="h-32 w-auto rounded-md" />
              </div>
              <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                <div class="space-y-1 text-center">
                  <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                  </svg>
                  <div class="flex text-sm text-gray-600">
                    <label for="image" class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                      <span>Upload a new image</span>
                      <input
                        id="image"
                        type="file"
                        class="sr-only"
                        accept="image/*"
                        @change="handleImageUpload"
                      />
                    </label>
                    <p class="pl-1">or drag and drop</p>
                  </div>
                  <p class="text-xs text-gray-500">PNG, JPG, GIF up to 2MB</p>
                </div>
              </div>
              <div v-if="imagePreview" class="mt-4">
                <img :src="imagePreview" alt="Preview" class="h-32 w-auto mx-auto rounded-md" />
              </div>
              <p v-if="errors.image" class="mt-1 text-sm text-red-600">{{ errors.image[0] }}</p>
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
                :disabled="submitting"
                class="inline-flex justify-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                <svg v-if="submitting" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                {{ submitting ? 'Saving...' : 'Save Changes' }}
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
          <p class="mt-1 text-sm text-gray-500">The campaign you're trying to edit doesn't exist.</p>
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
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import axios from '@/utils/axios'
import AppLayout from '@/Components/Layout/AppLayout.vue'

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()

const campaign = ref(null)
const loading = ref(true)
const submitting = ref(false)
const errors = ref({})
const imagePreview = ref(null)

const form = ref({
  title: '',
  description: '',
  category: '',
  goal_amount: '',
  start_date: '',
  end_date: '',
  status: '',
  image: null
})

const handleImageUpload = (event) => {
  const file = event.target.files[0]
  if (file) {
    form.value.image = file
    const reader = new FileReader()
    reader.onload = (e) => {
      imagePreview.value = e.target.result
    }
    reader.readAsDataURL(file)
  }
}

const fetchCampaign = async () => {
  loading.value = true
  try {
    const response = await axios.get(`/campaigns/${route.params.slug}`)
    campaign.value = response.data.data
    
    // Check ownership
    if (campaign.value.user_id !== authStore.user?.id) {
      router.push({ name: 'campaigns.show', params: { slug: campaign.value.slug } })
      return
    }
    
    // Populate form
    form.value = {
      title: campaign.value.title,
      description: campaign.value.description,
      category: campaign.value.category,
      goal_amount: campaign.value.goal_amount,
      start_date: campaign.value.start_date,
      end_date: campaign.value.end_date,
      status: campaign.value.status,
      image: null
    }
  } catch (error) {
    console.error('Failed to fetch campaign:', error)
  } finally {
    loading.value = false
  }
}

const submit = async () => {
  submitting.value = true
  errors.value = {}
  
  const formData = new FormData()
  Object.keys(form.value).forEach(key => {
    if (form.value[key] !== null && form.value[key] !== '') {
      formData.append(key, form.value[key])
    }
  })
  
  try {
    await axios.put(`/campaigns/${campaign.value.id}`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })
    
    router.push({ name: 'campaigns.show', params: { slug: campaign.value.slug } })
  } catch (error) {
    if (error.response?.status === 422) {
      errors.value = error.response.data.errors
    } else {
      console.error('Failed to update campaign:', error)
      // TODO: Show error notification
    }
  } finally {
    submitting.value = false
  }
}

onMounted(() => {
  fetchCampaign()
})
</script> 