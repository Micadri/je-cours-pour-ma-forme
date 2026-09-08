<script setup>
import { ref, watch, onMounted, onUnmounted } from 'vue'
import { RouterView } from 'vue-router'
import { useProgramStore } from './stores/program'

const store = useProgramStore()
const isOffline = ref(!navigator.onLine)

const updateOnlineStatus = async () => {
  isOffline.value = !navigator.onLine
  
  // Si le réseau revient, on vide la file d'attente et on rafraîchit
  if (navigator.onLine) {
    await store.syncQueue()
    await store.initApp() 
  }
}

const updateTheme = () => {
  if (store.userProfile?.theme === 'dark') {
    document.documentElement.classList.add('dark')
  } else {
    document.documentElement.classList.remove('dark')
  }
}

onMounted(() => {
  updateTheme()
  window.addEventListener('online', updateOnlineStatus)
  window.addEventListener('offline', updateOnlineStatus)
})

onUnmounted(() => {
  window.removeEventListener('online', updateOnlineStatus)
  window.removeEventListener('offline', updateOnlineStatus)
})

watch(() => store.userProfile?.theme, updateTheme)
</script>

<template>
  <div class="min-h-screen bg-background text-text transition-colors duration-300 font-body dark:bg-gray-900 dark:text-gray-100 overflow-x-hidden">
    
    <!-- Indicateur Hors-Ligne -->
    <transition name="slide-down">
      <div v-if="isOffline" class="bg-red-500 text-white text-center py-2 text-xs font-bold uppercase tracking-widest sticky top-0 z-50 shadow-md">
        ⚡ Mode Hors-Ligne Actif
      </div>
    </transition>

    <header class="text-center py-6">
      <img src="/banner.png" alt="Je Cours Pour Ma Forme" class="mx-auto max-w-full h-auto max-h-[120px] w-[300px] object-contain rounded-xl shadow-sm dark:bg-gray-200 dark:p-2 transition-transform duration-300 hover:scale-105" />
    </header>
    
    <!-- Transitions de navigation (Native Feel) -->
    <RouterView v-slot="{ Component }">
      <transition name="fade-slide" mode="out-in">
        <component :is="Component" />
      </transition>
    </RouterView>
  </div>
</template>

<style>
.fade-slide-enter-active, .fade-slide-leave-active {
  transition: opacity 0.3s ease, transform 0.3s ease;
}
.fade-slide-enter-from { opacity: 0; transform: translateY(15px); }
.fade-slide-leave-to { opacity: 0; transform: translateY(-15px); }

.slide-down-enter-active, .slide-down-leave-active {
  transition: transform 0.3s ease, opacity 0.3s ease;
}
.slide-down-enter-from, .slide-down-leave-to { opacity: 0; transform: translateY(-100%); }
</style>