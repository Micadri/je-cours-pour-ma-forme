<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { useProgramStore } from '../stores/program'
import StepProgressBar from '../components/StepProgressBar.vue'
import TimerDisplay from '../components/TimerDisplay.vue'

const router = useRouter()
const store = useProgramStore()

const isDataReady = computed(() => store.seasonData !== null && store.currentProgress !== null)

const currentSession = computed(() => store.currentSessionDetails?.session || null)

const exercises = computed(() => currentSession.value?.exercises || [])
const totalSteps = computed(() => exercises.value.length)

const currentStepIndex = ref(0)
const timeRemaining = ref(0)
const isRunning = ref(false)
const currentExercise = computed(() => exercises.value[currentStepIndex.value] || {})

let timerInterval = null
let stepTargetTime = 0
let wakeLock = null

const initStep = () => {
  if (currentExercise.value.duration_seconds) {
    timeRemaining.value = currentExercise.value.duration_seconds
  }
}

const startTimer = async () => {
  if (isRunning.value) return
  isRunning.value = true
  
  try {
    if ('wakeLock' in navigator) wakeLock = await navigator.wakeLock.request('screen')
  } catch (err) {
    console.warn("WakeLock bloqué")
  }

  stepTargetTime = Date.now() + (timeRemaining.value * 1000)

  timerInterval = setInterval(() => {
    const now = Date.now()
    const remaining = Math.max(0, Math.round((stepTargetTime - now) / 1000))
    timeRemaining.value = remaining

    if (remaining <= 0) nextStep()
  }, 200)
}

const pauseTimer = () => {
  isRunning.value = false
  clearInterval(timerInterval)
  if (wakeLock) wakeLock.release()
}

const prevStep = () => {
  if (currentStepIndex.value > 0) {
    const wasRunning = isRunning.value // On mémorise si le chrono tournait
    pauseTimer()
    currentStepIndex.value--
    initStep()
    if (wasRunning) startTimer() // On relance automatiquement si on était en pleine course
  }
}

const nextStep = async () => {
  pauseTimer()
  if (currentStepIndex.value < totalSteps.value - 1) {
    currentStepIndex.value++
    initStep()
    startTimer()
  } else {
    // Session terminée : sauvegarde en DB puis retour au tableau de bord
    await store.completeSession(0) // On transmet 0 pour la distance provisoirement
    router.push('/')
  }
}

onMounted(async () => {
  if (!isDataReady.value) {
    await store.initApp()
  }
  if (currentSession.value) initStep()
})

onUnmounted(() => pauseTimer())
</script>

<template>
  <main style="padding: 20px; font-family: sans-serif; max-width: 600px; margin: 0 auto;">
    
    <div v-if="!isDataReady">
      <p>Chargement de la course...</p>
    </div>

    <div v-else-if="currentSession">
      <h2 style="color: #666; text-align: center;">{{ currentSession.title }}</h2>
      
      <StepProgressBar :currentIndex="currentStepIndex" :totalSteps="totalSteps" />
      <TimerDisplay :timeRemaining="timeRemaining" :exerciseType="currentExercise.type" />

      <!-- Bouton Démarrer / Pause -->
      <button 
        v-if="!isRunning" 
        @click="startTimer" 
        style="width: 100%; padding: 20px; background: #4CAF50; color: white; border: none; border-radius: 8px; font-size: 24px; font-weight: bold; cursor: pointer; margin-bottom: 15px;"
      >
        Démarrer
      </button>
      
      <button 
        v-else 
        @click="pauseTimer" 
        style="width: 100%; padding: 20px; background: #FF9800; color: white; border: none; border-radius: 8px; font-size: 24px; font-weight: bold; cursor: pointer; margin-bottom: 15px;"
      >
        Pause
      </button>

      <!-- NOUVEAU : Boutons de navigation manuelle -->
      <div style="display: flex; gap: 10px; margin-bottom: 15px;">
        <button 
          @click="prevStep" 
          :disabled="currentStepIndex === 0"
          :style="{ opacity: currentStepIndex === 0 ? 0.5 : 1 }"
          style="flex: 1; padding: 15px; background: #E0E0E0; color: #333; border: none; border-radius: 8px; font-size: 16px; font-weight: bold; cursor: pointer;"
        >
          ⏪ Précédent
        </button>
        
        <button 
          @click="nextStep" 
          style="flex: 1; padding: 15px; background: #E0E0E0; color: #333; border: none; border-radius: 8px; font-size: 16px; font-weight: bold; cursor: pointer;"
        >
          Suivant ⏩
        </button>
      </div>

      <button @click="router.push('/')" style="padding: 15px; background: transparent; border: 1px solid #ccc; border-radius: 8px; width: 100%; cursor: pointer;">
        Quitter la session
      </button>
    </div>

  </main>
</template>