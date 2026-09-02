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

// NOUVEAU : Variables pour la Pop-up de fin
const showEndPopup = ref(false)
const finalStats = ref({ distance: 0, steps: 0 })

let timerInterval = null
let stepTargetTime = 0
let wakeLock = null
const hasPlayedAudio = ref(false)

const playStepAudio = (type) => {
  if (!type) return
  const fileName = type.normalize("NFD").replace(/[\u0300-\u036f]/g, "").toLowerCase()
  const audio = new Audio(`/audio/${fileName}.mp3`)
  audio.play().catch(err => console.warn("Audio bloqué :", err))
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
  
if (!hasPlayedAudio.value && currentExercise.value.type && store.userProfile?.audio_enabled == 1) {
    playStepAudio(currentExercise.value.type)
    hasPlayedAudio.value = true
  }
  
  try { if ('wakeLock' in navigator) wakeLock = await navigator.wakeLock.request('screen') } 
  catch (err) { console.warn("WakeLock bloqué") }

  stepTargetTime = Date.now() + (timeRemaining.value * 1000)

  timerInterval = setInterval(() => {
    const remaining = Math.max(0, Math.round((stepTargetTime - Date.now()) / 1000))
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
    const wasRunning = isRunning.value
    pauseTimer()
    currentStepIndex.value--
    initStep()
    if (wasRunning) startTimer()
  }
}

const nextStep = async () => {
  pauseTimer()
  if (currentStepIndex.value < totalSteps.value - 1) {
    currentStepIndex.value++
    initStep()
    startTimer()
  } else {
    // Calcul de statistiques fictives (pour le prototype offline)
    const totalSeconds = exercises.value.reduce((acc, curr) => acc + curr.duration_seconds, 0)
    finalStats.value.distance = (totalSeconds / 60 * 0.15).toFixed(2) // Moyenne 9km/h
    finalStats.value.steps = Math.round(totalSeconds / 60 * 150) // Moyenne 150 pas/min
    
    showEndPopup.value = true // Ouvre la popup au lieu de quitter
  }
}

const closeAndSave = async () => {
  await store.completeSession(finalStats.value.distance, finalStats.value.steps)
  router.push('/')
}

onMounted(async () => {
  if (!isDataReady.value) await store.initApp()
  if (currentSession.value) initStep()
})
onUnmounted(() => pauseTimer())

const quitSession = () => {
  pauseTimer()
  router.push('/')
}
</script>

<template>
  <main style="padding: 20px; font-family: sans-serif; max-width: 600px; margin: 0 auto; text-align: center;">
    <div style="text-align: left; margin-bottom: 20px;">
  <button @click="quitSession" style="background: none; border: none; color: #f44336; font-size: 16px; font-weight: bold; cursor: pointer;">
    ← Quitter la course
  </button>
</div>
    <!-- Pop-up de fin de session -->
    <div v-if="showEndPopup" style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.6); display: flex; align-items: center; justify-content: center; z-index: 1000;">
      <div style="background: white; padding: 30px; border-radius: 12px; max-width: 320px; width: 90%; position: relative;">
        <button @click="closeAndSave" style="position: absolute; top: 10px; right: 15px; background: none; border: none; font-size: 24px; cursor: pointer; color: #888;">✖</button>
        <h2 style="margin-top: 0; color: #4CAF50;">Session terminée ! 🎉</h2>
        <div style="margin: 20px 0; font-size: 1.2rem; color: #333;">
          <p><strong>Distance :</strong> {{ finalStats.distance }} km</p>
          <p><strong>Pas :</strong> {{ finalStats.steps }}</p>
        </div>
        <button @click="closeAndSave" style="width: 100%; padding: 15px; background: #4CAF50; color: white; border: none; border-radius: 8px; font-weight: bold; font-size: 16px; cursor: pointer;">
          Valider et Quitter
        </button>
      </div>
    </div>

    <!-- Le reste de ton interface RunView (Timer, Barre de progression, Contrôles) -->
    <div v-if="!currentSession">
      <p>Chargement de la session...</p>
    </div>
    
    <div v-else>
      <StepProgressBar :totalSteps="totalSteps" :currentStepIndex="currentStepIndex" />
      <h2 style="text-transform: capitalize; margin: 20px 0; color: #4CAF50; font-size: 2rem;">
        {{ currentExercise.type }}
      </h2>
      <TimerDisplay :timeRemaining="timeRemaining" />
      <div style="display: flex; justify-content: center; gap: 20px; margin-top: 30px;">
        <button @click="prevStep" :disabled="currentStepIndex === 0" style="padding: 10px 20px; border: none; border-radius: 5px; font-size: 18px; cursor: pointer;" :style="{ background: currentStepIndex === 0 ? '#ccc' : '#e0e0e0' }">
          ⏮ Précédent
        </button>
        <button v-if="!isRunning" @click="startTimer" style="padding: 10px 30px; background: #4CAF50; color: white; border: none; border-radius: 5px; font-size: 18px; font-weight: bold; cursor: pointer;">
          ▶ Démarrer
        </button>
        <button v-else @click="pauseTimer" style="padding: 10px 30px; background: #ff9800; color: white; border: none; border-radius: 5px; font-size: 18px; font-weight: bold; cursor: pointer;">
          ⏸ Pause
        </button>
        <button @click="nextStep" style="padding: 10px 20px; background: #e0e0e0; border: none; border-radius: 5px; font-size: 18px; cursor: pointer;">
          Suivant ⏭
        </button>
      </div>
    </div>
  </main>
</template>