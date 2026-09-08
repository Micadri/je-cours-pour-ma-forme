<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useProgramStore } from '../stores/program'
import StepProgressBar from '../components/StepProgressBar.vue'
import TimerDisplay from '../components/TimerDisplay.vue'
import { useHaptics } from '../composables/useHaptics'
import { useWeather } from '../composables/useWeather'
import { useGPS } from '../composables/useGPS'
import L from 'leaflet'
import 'leaflet/dist/leaflet.css'

const router = useRouter()
const store = useProgramStore()

// Initialisation des composables
const { vibrateRun, vibrateWalk } = useHaptics()
const { temperature, isWeatherLoading, fetchWeather } = useWeather()
const { pathCoordinates, distanceKm, elevationGain, startTracking, stopTracking, calculatePace } = useGPS()

const isGuest = computed(() => !localStorage.getItem('auth_token'))
const currentSession = computed(() => store.currentSessionDetails?.session || null)
const exercises = computed(() => currentSession.value?.exercises || [])
const totalSteps = computed(() => exercises.value.length)
const currentStepIndex = ref(0)
const timeRemaining = ref(0)
const isRunning = ref(false)
const currentExercise = computed(() => exercises.value[currentStepIndex.value] || {})
const totalElapsedSeconds = ref(0) // Chrono global pour les stats

const showEndPopup = ref(false)
const mapContainer = ref(null)
let map = null
let polyline = null
let timerInterval = null
let stepTargetTime = 0
let wakeLock = null
const hasPlayedAudio = ref(false)

const avgSpeed = computed(() => {
  if (totalElapsedSeconds.value === 0 || distanceKm.value === 0) return "0.0"
  return (distanceKm.value / (totalElapsedSeconds.value / 3600)).toFixed(1)
})

// Mettre à jour la carte en temps réel
watch(pathCoordinates, (newCoords) => {
  if (newCoords.length === 0) return
  if (!map && mapContainer.value) {
    map = L.map(mapContainer.value, { zoomControl: false }).setView(newCoords[0], 16)
    L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', { maxZoom: 19 }).addTo(map)
    polyline = L.polyline(newCoords, { color: '#8cc63f', weight: 5 }).addTo(map)
  } else if (map && polyline) {
    polyline.setLatLngs(newCoords)
    map.panTo(newCoords[newCoords.length - 1])
  }
}, { deep: true })

const playStepAudio = (type) => {
  if (!type) return
  
  if (store.userProfile?.audio_enabled == 1) {
    const textToSpeak = type.replace(/_/g, ' ')
    const utterance = new SpeechSynthesisUtterance(textToSpeak)
    utterance.lang = 'fr-FR'
    const voices = window.speechSynthesis.getVoices()
    const femaleVoice = voices.find(v => v.lang.startsWith('fr') && (v.name.toLowerCase().includes('female') || v.name.toLowerCase().includes('hortense')))
    if (femaleVoice) utterance.voice = femaleVoice
    window.speechSynthesis.cancel()
    window.speechSynthesis.speak(utterance)
  }

  const isRun = type.toLowerCase().includes('trotte') || type.toLowerCase().includes('cours') || type.toLowerCase().includes('sprint')
  if (isRun) vibrateRun()
  else vibrateWalk()
}

const initStep = () => {
  if (currentExercise.value.duration_seconds) {
    timeRemaining.value = currentExercise.value.duration_seconds
    hasPlayedAudio.value = false
  }
}

const startTimer = async () => {
  if (isRunning.value) return
  isRunning.value = true
  startTracking()

  if (!hasPlayedAudio.value && currentExercise.value.type) {
    playStepAudio(currentExercise.value.type)
    hasPlayedAudio.value = true
  }

  try { if ('wakeLock' in navigator) wakeLock = await navigator.wakeLock.request('screen') } catch (err) {}
  
  stepTargetTime = Date.now() + (timeRemaining.value * 1000)
  timerInterval = setInterval(() => {
    const remaining = Math.max(0, Math.round((stepTargetTime - Date.now()) / 1000))
    timeRemaining.value = remaining
    totalElapsedSeconds.value++
    if (remaining <= 0) nextStep()
  }, 1000)
}

const pauseTimer = () => {
  isRunning.value = false
  stopTracking()
  clearInterval(timerInterval)
  if (wakeLock) wakeLock.release()
}

const prevStep = () => {
  if (currentStepIndex.value > 0) {
    const wasRunning = isRunning.value
    pauseTimer(); currentStepIndex.value--; initStep()
    if (wasRunning) startTimer()
  }
}

const nextStep = async () => {
  pauseTimer()
  if (currentStepIndex.value < totalSteps.value - 1) {
    currentStepIndex.value++; initStep(); startTimer()
  } else {
    showEndPopup.value = true
  }
}

const buildPayload = () => ({
  distance_km: distanceKm.value.toFixed(2),
  steps: Math.round(totalElapsedSeconds.value / 60 * 150),
  elevation: elevationGain.value,
  duration: totalElapsedSeconds.value,
  weather: temperature.value,
  route: JSON.stringify(pathCoordinates.value)
})

const closeAndSave = async () => { await store.completeSession(buildPayload()); router.push('/') }
const closeAndRegister = async () => { await store.completeSession(buildPayload()); router.push('/register') }
const quitSession = () => { pauseTimer(); router.push('/') }

onMounted(async () => {
  window.speechSynthesis.getVoices()
  if (!store.currentProgress) await store.initApp()
  if (currentSession.value) initStep()
  
  // Requête Météo via GPS au chargement
  if ('geolocation' in navigator) {
    navigator.geolocation.getCurrentPosition((pos) => {
      fetchWeather(pos.coords.latitude, pos.coords.longitude)
    })
  }
})

onUnmounted(() => pauseTimer())
</script>

<template>
  <main class="p-5 font-body max-w-[600px] mx-auto">
    
    <div class="flex justify-between items-center mb-4">
      <button @click="quitSession" class="bg-transparent border-none text-red-500 text-sm font-bold cursor-pointer hover:opacity-80 transition-opacity">
        ← Quitter
      </button>
      <div class="text-sm font-bold text-gray-500 flex items-center gap-1 dark:text-gray-300">
        <span v-if="isWeatherLoading">☁️...</span>
        <span v-else-if="temperature !== null">🌤️ {{ temperature }}°C</span>
      </div>
    </div>

    <!-- Pop-up de fin avec stats complètes -->
    <div v-if="showEndPopup" class="fixed inset-0 bg-black/60 flex items-center justify-center z-50 p-5 backdrop-blur-sm">
      <div class="bg-surface p-6 rounded-xl w-full max-w-[350px] shadow-2xl dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
        <h2 class="mt-0 font-heading text-2xl text-accent font-bold mb-4 text-center">Session terminée ! 🏆</h2>
        
        <div class="grid grid-cols-2 gap-3 mb-6">
          <div class="bg-gray-50 p-3 rounded-lg text-center dark:bg-gray-900 border border-gray-100 dark:border-gray-700">
            <strong class="text-xl text-primary font-heading dark:text-gray-100">{{ distanceKm.toFixed(2) }} <span class="text-xs">km</span></strong>
            <div class="text-[0.6rem] text-gray-500 uppercase tracking-widest font-bold">Distance</div>
          </div>
          <div class="bg-gray-50 p-3 rounded-lg text-center dark:bg-gray-900 border border-gray-100 dark:border-gray-700">
            <strong class="text-xl text-primary font-heading dark:text-gray-100">{{ calculatePace(totalElapsedSeconds) }}</strong>
            <div class="text-[0.6rem] text-gray-500 uppercase tracking-widest font-bold">Min/Km</div>
          </div>
          <div class="bg-gray-50 p-3 rounded-lg text-center dark:bg-gray-900 border border-gray-100 dark:border-gray-700">
            <strong class="text-xl text-primary font-heading dark:text-gray-100">{{ avgSpeed }} <span class="text-xs">km/h</span></strong>
            <div class="text-[0.6rem] text-gray-500 uppercase tracking-widest font-bold">Vitesse</div>
          </div>
          <div class="bg-gray-50 p-3 rounded-lg text-center dark:bg-gray-900 border border-gray-100 dark:border-gray-700">
            <strong class="text-xl text-primary font-heading dark:text-gray-100">+{{ elevationGain }} <span class="text-xs">m</span></strong>
            <div class="text-[0.6rem] text-gray-500 uppercase tracking-widest font-bold">Dénivelé</div>
          </div>
        </div>

        <div v-if="!isGuest" class="flex flex-col gap-2">
          <button @click="closeAndSave" class="w-full p-4 bg-accent text-primary rounded-cta font-bold cursor-pointer hover:bg-opacity-90 uppercase tracking-wider font-heading">Valider & Quitter</button>
        </div>
        <div v-else>
          <button @click="closeAndRegister" class="w-full p-4 bg-primary text-accent rounded-cta font-bold cursor-pointer hover:bg-opacity-90 uppercase tracking-wider font-heading shadow-lg">S'inscrire</button>
        </div>
      </div>
    </div>

    <div v-if="!currentSession" class="text-gray-500 animate-pulse text-center mt-10">Chargement...</div>
    
    <div v-else class="flex flex-col items-center">
      <div class="w-full">
        <StepProgressBar :totalSteps="totalSteps" :currentStepIndex="currentStepIndex" />
      </div>

      <TimerDisplay :timeRemaining="timeRemaining" :totalTime="currentExercise.duration_seconds" :exerciseType="currentExercise.type" />
      
      <!-- Grille des statistiques temps réel -->
      <div class="grid grid-cols-3 gap-2 w-full mb-6">
        <div class="text-center">
          <div class="font-bold text-lg text-primary dark:text-gray-100 font-heading">{{ distanceKm.toFixed(2) }}</div>
          <div class="text-[0.6rem] text-gray-500 uppercase font-bold">Km</div>
        </div>
        <div class="text-center border-l border-r border-gray-200 dark:border-gray-700">
          <div class="font-bold text-lg text-primary dark:text-gray-100 font-heading">{{ calculatePace(totalElapsedSeconds) }}</div>
          <div class="text-[0.6rem] text-gray-500 uppercase font-bold">Allure</div>
        </div>
        <div class="text-center">
          <div class="font-bold text-lg text-primary dark:text-gray-100 font-heading">+{{ elevationGain }}m</div>
          <div class="text-[0.6rem] text-gray-500 uppercase font-bold">Dénivelé</div>
        </div>
      </div>

      <!-- Carte Leaflet (Tracé GPS) -->
      <div ref="mapContainer" class="w-full h-32 rounded-xl bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 mb-6 overflow-hidden z-0 shadow-inner"></div>

      <!-- Contrôles -->
      <div class="flex justify-between items-stretch gap-2 w-full max-w-[400px]">
        <button @click="prevStep" :disabled="currentStepIndex === 0" class="flex-1 px-1 py-4 rounded-cta font-bold text-xs sm:text-sm cursor-pointer transition-colors disabled:opacity-50 font-heading uppercase tracking-wide" :class="currentStepIndex === 0 ? 'bg-gray-200 text-gray-400 dark:bg-gray-700 dark:text-gray-500' : 'bg-gray-200 text-gray-700 hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600'">Préc.</button>
        <button v-if="!isRunning" @click="startTimer" class="flex-[2] px-2 py-4 bg-accent text-primary rounded-cta font-bold text-base sm:text-lg cursor-pointer hover:bg-opacity-90 shadow-md font-heading uppercase tracking-widest">Démarrer</button>
        <button v-else @click="pauseTimer" class="flex-[2] px-2 py-4 bg-red-500 text-white rounded-cta font-bold text-base sm:text-lg cursor-pointer hover:bg-red-600 shadow-md font-heading uppercase tracking-widest animate-pulse">Pause</button>
        <button @click="nextStep" class="flex-1 px-1 py-4 bg-gray-200 text-gray-700 rounded-cta font-bold text-xs sm:text-sm cursor-pointer hover:bg-gray-300 transition-colors dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600 font-heading uppercase tracking-wide">Suiv.</button>
      </div>
    </div>
  </main>
</template>