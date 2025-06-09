<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-sm border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
          <div class="flex">
            <!-- Logo -->
            <div class="flex-shrink-0 flex items-center">
              <router-link to="/" class="text-xl font-bold text-blue-600">
                ACME CSR Platform
              </router-link>
            </div>

            <!-- Navigation Links -->
            <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
              <NavLink :to="{ name: 'dashboard' }" :active="route.name === 'dashboard'">
                Dashboard
              </NavLink>
              <NavLink :to="{ name: 'campaigns.index' }" :active="route.name?.startsWith('campaigns')">
                Campaigns
              </NavLink>
              <NavLink :to="{ name: 'donations.index' }" :active="route.name?.startsWith('donations')">
                My Donations
              </NavLink>
              <NavLink v-if="authStore.isAdmin" :to="{ name: 'admin.dashboard' }" :active="route.name?.startsWith('admin')">
                Admin
              </NavLink>
            </div>
          </div>

          <!-- Right side -->
          <div class="hidden sm:flex sm:items-center sm:ml-6">
            <!-- User dropdown -->
            <div class="ml-3 relative">
              <Dropdown align="right" width="48">
                <template #trigger>
                  <button class="flex items-center text-sm font-medium text-gray-500 hover:text-gray-700 focus:outline-none transition">
                    <img 
                      :src="authStore.user?.avatar_url" 
                      :alt="authStore.user?.name"
                      class="h-8 w-8 rounded-full mr-2"
                    >
                    <span>{{ authStore.user?.name }}</span>
                    <svg class="ml-2 -mr-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                      <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                  </button>
                </template>

                <template #content>
                  <DropdownLink :to="{ name: 'profile.show' }">
                    Profile
                  </DropdownLink>
                  <DropdownLink :to="{ name: 'profile.edit' }">
                    Settings
                  </DropdownLink>
                  <div class="border-t border-gray-100"></div>
                  <button @click="logout" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                    Log Out
                  </button>
                </template>
              </Dropdown>
            </div>
          </div>

          <!-- Mobile menu button -->
          <div class="-mr-2 flex items-center sm:hidden">
            <button @click="showingNavigationDropdown = !showingNavigationDropdown" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition">
              <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                <path :class="{'hidden': showingNavigationDropdown, 'inline-flex': !showingNavigationDropdown }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                <path :class="{'hidden': !showingNavigationDropdown, 'inline-flex': showingNavigationDropdown }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
        </div>
      </div>

      <!-- Mobile menu -->
      <div :class="{'block': showingNavigationDropdown, 'hidden': !showingNavigationDropdown}" class="sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
          <ResponsiveNavLink :to="{ name: 'dashboard' }" :active="route.name === 'dashboard'">
            Dashboard
          </ResponsiveNavLink>
          <ResponsiveNavLink :to="{ name: 'campaigns.index' }" :active="route.name?.startsWith('campaigns')">
            Campaigns
          </ResponsiveNavLink>
          <ResponsiveNavLink :to="{ name: 'donations.index' }" :active="route.name?.startsWith('donations')">
            My Donations
          </ResponsiveNavLink>
          <ResponsiveNavLink v-if="authStore.isAdmin" :to="{ name: 'admin.dashboard' }" :active="route.name?.startsWith('admin')">
            Admin
          </ResponsiveNavLink>
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
          <div class="px-4">
            <div class="font-medium text-base text-gray-800">{{ authStore.user?.name }}</div>
            <div class="font-medium text-sm text-gray-500">{{ authStore.user?.email }}</div>
          </div>

          <div class="mt-3 space-y-1">
            <ResponsiveNavLink :to="{ name: 'profile.show' }">
              Profile
            </ResponsiveNavLink>
            <ResponsiveNavLink :to="{ name: 'profile.edit' }">
              Settings
            </ResponsiveNavLink>
            <button @click="logout" class="block w-full text-left pl-3 pr-4 py-2 border-l-4 border-transparent text-base font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300 focus:outline-none focus:text-gray-800 focus:bg-gray-50 focus:border-gray-300 transition">
              Log Out
            </button>
          </div>
        </div>
      </div>
    </nav>

    <!-- Page Content -->
    <main>
      <slot />
    </main>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import NavLink from '@/Components/UI/NavLink.vue'
import ResponsiveNavLink from '@/Components/UI/ResponsiveNavLink.vue'
import Dropdown from '@/Components/UI/Dropdown.vue'
import DropdownLink from '@/Components/UI/DropdownLink.vue'

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()

const showingNavigationDropdown = ref(false)

const logout = async () => {
  await authStore.logout()
}
</script> 