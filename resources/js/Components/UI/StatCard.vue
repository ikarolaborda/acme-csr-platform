<template>
  <div class="bg-white overflow-hidden shadow rounded-lg">
    <div class="p-5">
      <div class="flex items-center">
        <div class="flex-shrink-0">
          <div :class="[iconColorClass, 'rounded-md p-3']">
            <component :is="iconComponent" class="h-6 w-6 text-white" />
          </div>
        </div>
        <div class="ml-5 w-0 flex-1">
          <dl>
            <dt class="text-sm font-medium text-gray-500 truncate">
              {{ title }}
            </dt>
            <dd class="text-lg font-semibold text-gray-900">
              {{ value }}
            </dd>
          </dl>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import {
  CurrencyDollarIcon,
  FlagIcon,
  BoltIcon,
  UsersIcon,
  HeartIcon,
  CircleStackIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  title: {
    type: String,
    required: true
  },
  value: {
    type: [String, Number],
    required: true
  },
  icon: {
    type: String,
    default: 'currency-dollar'
  },
  color: {
    type: String,
    default: 'blue'
  }
})

const iconComponent = computed(() => {
  const icons = {
    'currency-dollar': CurrencyDollarIcon,
    'flag': FlagIcon,
    'lightning-bolt': BoltIcon,
    'bolt': BoltIcon,
    'users': UsersIcon,
    'heart': HeartIcon,
    'collection': CircleStackIcon
  }
  return icons[props.icon] || CurrencyDollarIcon
})

const iconColorClass = computed(() => {
  const colors = {
    blue: 'bg-blue-500',
    green: 'bg-green-500',
    yellow: 'bg-yellow-500',
    purple: 'bg-purple-500',
    red: 'bg-red-500'
  }
  return colors[props.color] || 'bg-blue-500'
})
</script>