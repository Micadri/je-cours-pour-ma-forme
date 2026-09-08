<script setup>
import { computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useProgramStore } from '../stores/program'
import { useWeather } from '../composables/useWeather'

const router = useRouter()
const store = useProgramStore()
const { temperature, isWeatherLoading, fetchWeather } = useWeather()
const isGuest = computed(() => !localStorage.getItem('auth_token'))

const handleLogout = () => { store.logout(); window.location.href = '/welcome' }
const goToProfile = () => router.push('/profile')
const goToLogin = () => router.push('/login')
const goToRegister = () => router.push('/register')

onMounted(() => {
  if ('geolocation' in navigator) {
    navigator.geolocation.getCurrentPosition((pos) => fetchWeather(pos.coords.latitude, pos.coords.longitude), () => {})
  }
})
</script>

<template>
  <div class="flex flex-col gap-4 mb-6 p-4 bg-surface rounded-xl shadow-sm border border-gray-200 dark:bg-gray-800 dark:border-gray-700">
    <div class="flex flex-wrap justify-between items-center gap-4">
      <div class="flex items-center gap-4 flex-1 min-w-[200px]">
        <div class="w-12 h-12 rounded-full bg-gray-200 border-2 border-accent overflow-hidden flex items-center justify-center shrink-0 dark:bg-gray-700">
          <img v-if="store.userProfile?.avatar" :src="store.userProfile.avatar" class="w-full h-full object-cover" />
          <span v-else class="text-white text-xl">👤</span>
        </div>
        <div>
          <h2 class="m-0 text-lg text-text leading-tight dark:text-gray-100 font-body">
            Bonjour, <br/><span class="text-accent font-bold text-xl font-heading tracking-wide">{{ store.userProfile?.first_name || 'Coureur' }}</span> 
          </h2>
        </div>
      </div>
      
      <!-- Widget Météo -->
      <div class="text-sm font-bold text-gray-500 bg-gray-50 px-3 py-1.5 rounded-lg border border-gray-100 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-300">
        <span v-if="isWeatherLoading">☁️ ...</span>
        <span v-else-if="temperature !== null">🌤️ {{ temperature }}°C</span>
        <span v-else>📍 GPS Requis</span>
      </div>
    </div>
    
    <div v-if="!isGuest" class="flex gap-2 shrink-0 mt-2">
      <button @click="goToProfile" class="flex-1 py-2 bg-gray-100 text-text rounded-cta text-sm font-bold hover:bg-gray-200 transition-colors dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">Profil</button>
      <button @click="handleLogout" class="flex-1 py-2 bg-transparent text-red-500 border border-red-500 rounded-cta text-sm font-bold hover:bg-red-50 transition-colors dark:hover:bg-red-900/30">Déconnexion</button>
    </div>
    <div v-else class="flex gap-2 shrink-0 mt-2">
      <button @click="goToLogin" class="flex-1 py-2 bg-transparent text-primary border border-primary rounded-cta text-sm font-bold hover:bg-primary/5 transition-colors dark:text-gray-300 dark:border-gray-300 dark:hover:bg-gray-800">Se connecter</button>
      <button @click="goToRegister" class="flex-1 py-2 bg-primary text-accent rounded-cta text-sm font-bold hover:bg-opacity-90 transition-colors font-heading tracking-wider uppercase">S'inscrire</button>
    </div>
  </div>
</template>