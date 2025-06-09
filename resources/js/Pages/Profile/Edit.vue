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
            <span class="ml-2 text-gray-600">Loading profile...</span>
          </div>
        </div>

        <div v-else class="space-y-6">
          <!-- Profile Form -->
          <div class="bg-white shadow sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6">
              <h3 class="text-lg leading-6 font-medium text-gray-900">
                Edit Profile
              </h3>
              <p class="mt-1 max-w-2xl text-sm text-gray-500">
                Update your personal information and settings.
              </p>
            </div>
            <form @submit.prevent="updateProfile" class="border-t border-gray-200">
              <div class="px-4 py-5 sm:p-6 space-y-6">
                <!-- Name -->
                <div>
                  <label for="name" class="block text-sm font-medium text-gray-700">
                    Full Name
                  </label>
                  <input
                    v-model="form.name"
                    type="text"
                    id="name"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                    :class="{ 'border-red-300': errors.name }"
                  />
                  <p v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name[0] }}</p>
                </div>

                <!-- Email -->
                <div>
                  <label for="email" class="block text-sm font-medium text-gray-700">
                    Email Address
                  </label>
                  <input
                    v-model="form.email"
                    type="email"
                    id="email"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                    :class="{ 'border-red-300': errors.email }"
                  />
                  <p v-if="errors.email" class="mt-1 text-sm text-red-600">{{ errors.email[0] }}</p>
                </div>

                <!-- Phone -->
                <div>
                  <label for="phone" class="block text-sm font-medium text-gray-700">
                    Phone Number
                  </label>
                  <input
                    v-model="form.phone"
                    type="tel"
                    id="phone"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                    :class="{ 'border-red-300': errors.phone }"
                  />
                  <p v-if="errors.phone" class="mt-1 text-sm text-red-600">{{ errors.phone[0] }}</p>
                </div>

                <!-- Department -->
                <div>
                  <label for="department" class="block text-sm font-medium text-gray-700">
                    Department
                  </label>
                  <input
                    v-model="form.department"
                    type="text"
                    id="department"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                    :class="{ 'border-red-300': errors.department }"
                  />
                  <p v-if="errors.department" class="mt-1 text-sm text-red-600">{{ errors.department[0] }}</p>
                </div>
              </div>

              <!-- Action Buttons -->
              <div class="px-4 py-3 bg-gray-50 text-right sm:px-6">
                <router-link
                  :to="{ name: 'profile.show' }"
                  class="inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 mr-3"
                >
                  Cancel
                </router-link>
                <button
                  type="submit"
                  :disabled="submitting"
                  class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed"
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

          <!-- Change Password -->
          <div class="bg-white shadow sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6">
              <h3 class="text-lg leading-6 font-medium text-gray-900">
                Change Password
              </h3>
              <p class="mt-1 max-w-2xl text-sm text-gray-500">
                Leave blank if you don't want to change your password.
              </p>
            </div>
            <form @submit.prevent="updatePassword" class="border-t border-gray-200">
              <div class="px-4 py-5 sm:p-6 space-y-6">
                <!-- New Password -->
                <div>
                  <label for="password" class="block text-sm font-medium text-gray-700">
                    New Password
                  </label>
                  <input
                    v-model="passwordForm.password"
                    type="password"
                    id="password"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                    :class="{ 'border-red-300': passwordErrors.password }"
                  />
                  <p v-if="passwordErrors.password" class="mt-1 text-sm text-red-600">{{ passwordErrors.password[0] }}</p>
                </div>

                <!-- Confirm Password -->
                <div>
                  <label for="password_confirmation" class="block text-sm font-medium text-gray-700">
                    Confirm New Password
                  </label>
                  <input
                    v-model="passwordForm.password_confirmation"
                    type="password"
                    id="password_confirmation"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                  />
                </div>
              </div>

              <!-- Action Button -->
              <div class="px-4 py-3 bg-gray-50 text-right sm:px-6">
                <button
                  type="submit"
                  :disabled="passwordSubmitting"
                  class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  <svg v-if="passwordSubmitting" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  {{ passwordSubmitting ? 'Updating...' : 'Update Password' }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from '@/utils/axios'
import AppLayout from '@/Components/Layout/AppLayout.vue'

const router = useRouter()

const form = ref({
  name: '',
  email: '',
  phone: '',
  department: ''
})

const passwordForm = ref({
  password: '',
  password_confirmation: ''
})

const loading = ref(true)
const submitting = ref(false)
const passwordSubmitting = ref(false)
const errors = ref({})
const passwordErrors = ref({})

const fetchProfile = async () => {
  loading.value = true
  try {
    const response = await axios.get('/profile')
    const profile = response.data.data
    
    form.value = {
      name: profile.name,
      email: profile.email,
      phone: profile.phone || '',
      department: profile.department || ''
    }
  } catch (error) {
    console.error('Failed to fetch profile:', error)
  } finally {
    loading.value = false
  }
}

const updateProfile = async () => {
  submitting.value = true
  errors.value = {}
  
  try {
    await axios.put('/profile', form.value)
    router.push({ name: 'profile.show' })
  } catch (error) {
    if (error.response?.status === 422) {
      errors.value = error.response.data.errors
    } else {
      console.error('Failed to update profile:', error)
    }
  } finally {
    submitting.value = false
  }
}

const updatePassword = async () => {
  if (!passwordForm.value.password) {
    return
  }
  
  passwordSubmitting.value = true
  passwordErrors.value = {}
  
  try {
    await axios.put('/profile', passwordForm.value)
    passwordForm.value = {
      password: '',
      password_confirmation: ''
    }
    // TODO: Show success notification
  } catch (error) {
    if (error.response?.status === 422) {
      passwordErrors.value = error.response.data.errors
    } else {
      console.error('Failed to update password:', error)
    }
  } finally {
    passwordSubmitting.value = false
  }
}

onMounted(() => {
  fetchProfile()
})
</script> 