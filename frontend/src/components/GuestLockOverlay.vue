<script setup>
import { computed } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const isGuest = computed(() => !localStorage.getItem('auth_token'))
const goToRegister = () => router.push('/register')
</script>

<template>
  <div class="relative">
    <div v-if="isGuest" class="absolute inset-0 z-10 flex items-center justify-center bg-background/40 backdrop-blur-sm rounded-xl dark:bg-gray-900/60">
      <button @click="goToRegister" class="px-6 py-4 bg-primary text-accent rounded-cta text-base font-bold shadow-xl hover:scale-105 transition-transform uppercase font-heading tracking-wider">
        🔒 Créer un compte
      </button>
    </div>
    <div :class="{ 'pointer-events-none blur-[3px] opacity-50': isGuest }" class="transition-all duration-300">
      <slot></slot>
    </div>
  </div>
</template>