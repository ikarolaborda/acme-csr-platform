<template>
  <router-link
    :to="to"
    :class="classes"
    class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium leading-5 transition duration-150 ease-in-out"
  >
    <slot />
  </router-link>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  to: {
    type: [String, Object],
    required: true
  },
  active: {
    type: Boolean,
    default: false
  }
})

const classes = computed(() => {
  return props.active
    ? 'border-blue-400 text-gray-900 focus:outline-none focus:border-blue-700'
    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300'
})
</script> 