<script setup>
import { computed } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const isGuest = computed(() => !localStorage.getItem('auth_token'))
</script>

<template>
  <div style="position: relative;">
    <div v-if="isGuest" style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; z-index: 10; display: flex; align-items: center; justify-content: center;">
      <button @click="router.push('/register')" style="padding: 15px 25px; background: #4CAF50; color: white; border: none; border-radius: 8px; font-size: 16px; font-weight: bold; cursor: pointer; box-shadow: 0 6px 20px rgba(0,0,0,0.15);">
        🔒 Créer un compte pour débloquer
      </button>
    </div>
    <div :style="{ pointerEvents: isGuest ? 'none' : 'auto', filter: isGuest ? 'blur(5px)' : 'none', opacity: isGuest ? 0.6 : 1, transition: 'all 0.3s' }">
      <slot></slot>
    </div>
  </div>
</template>