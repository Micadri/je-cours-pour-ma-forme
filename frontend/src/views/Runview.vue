<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { useProgramStore } from '../stores/program'
import StepProgressBar from '../components/StepProgressBar.vue'
import TimerDisplay from '../components/TimerDisplay.vue'

const router = useRouter()
const store = useProgramStore()

const isDataReady = computed(() => store.seasonData !== null && store.currentProgress !== null)

const currentSession = computed(() => {
  if (!isDataReady.value) return null
  const week = store.seasonData.weeks.find(w => w.id === store.currentProgress.current_week_id)
  return week ? week.sessions.find(s => s.id === store.currentProgress.current_session_id) : null
})

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

const nextStep = () => {
  pauseTimer()
  if (currentStepIndex.value < totalSteps.value - 1) {
    currentStepIndex.value++
    initStep()
    startTimer()
  } else {
    alert("Session terminée ! Bien joué.")
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

      <button @click="router.push('/')" style="padding: 15px; background: transparent; border: 1px solid #ccc; border-radius: 8px; width: 100%; cursor: pointer;">
        Quitter la session
      </button>
    </div>

  </main>
</template>