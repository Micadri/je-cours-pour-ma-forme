<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { useProgramStore } from '../stores/program'
import StepProgressBar from '../components/StepProgressBar.vue'
import TimerDisplay from '../components/TimerDisplay.vue'

const router = useRouter()
const store = useProgramStore()

const isGuest = computed(() => !localStorage.getItem('auth_token'))
const isDataReady = computed(() => store.seasonData !== null && store.currentProgress !== null)
const currentSession = computed(() => store.currentSessionDetails?.session || null)
const exercises = computed(() => currentSession.value?.exercises || [])
const totalSteps = computed(() => exercises.value.length)
const currentStepIndex = ref(0)
const timeRemaining = ref(0)
const isRunning = ref(false)
const currentExercise = computed(() => exercises.value[currentStepIndex.value] || {})

const showEndPopup = ref(false)
const finalStats = ref({ distance: 0, steps: 0 })
let timerInterval = null; let stepTargetTime = 0; let wakeLock = null
const hasPlayedAudio = ref(false)

// Synthèse Vocale (TTS) & Retour Haptique (Vibrations)
const playStepAudio = (type) => {
  if (!type) return
  
  // 1. Synthèse Vocale (Text-to-Speech)
  if (store.userProfile?.audio_enabled == 1) {
    const textToSpeak = type.replace(/_/g, ' ')
    const utterance = new SpeechSynthesisUtterance(textToSpeak)
    utterance.lang = 'fr-FR'; utterance.rate = 1.1; utterance.pitch = 1.2
    
    const voices = window.speechSynthesis.getVoices()
    // Forcer une voix féminine française si disponible (Audrey, Hortense, Thomas, etc.)
    const femaleVoice = voices.find(v => v.lang.startsWith('fr') && (v.name.toLowerCase().includes('female') || v.name.toLowerCase().includes('hortense') || v.name.toLowerCase().includes('aurelie')))
    if (femaleVoice) utterance.voice = femaleVoice
    
    window.speechSynthesis.cancel() // Coupe la voix précédente
    window.speechSynthesis.speak(utterance)
  }

  // 2. Retour Haptique (Vibrations)
  if ('vibrate' in navigator) {
    const isRun = type.toLowerCase().includes('trotte') || type.toLowerCase().includes('cours') || type.toLowerCase().includes('sprint')
    if (isRun) navigator.vibrate([100, 100, 100]) // 3 petites impulsions = Courir
    else navigator.vibrate([300]) // 1 longue impulsion = Marcher/Echauffement
  }
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

  // Déclenche l'audio et la vibration
  if (!hasPlayedAudio.value && currentExercise.value.type) {
    playStepAudio(currentExercise.value.type)
    hasPlayedAudio.value = true
  }

  try { if ('wakeLock' in navigator) wakeLock = await navigator.wakeLock.request('screen') } catch (err) {}
  
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
    pauseTimer(); currentStepIndex.value--; initStep()
    if (wasRunning) startTimer()
  }
}

const nextStep = async () => {
  pauseTimer()
  if (currentStepIndex.value < totalSteps.value - 1) {
    currentStepIndex.value++; initStep(); startTimer()
  } else {
    const totalSeconds = exercises.value.reduce((acc, curr) => acc + curr.duration_seconds, 0)
    finalStats.value.distance = (totalSeconds / 60 * 0.15).toFixed(2)
    finalStats.value.steps = Math.round(totalSeconds / 60 * 150)
    showEndPopup.value = true
  }
}

const closeAndSave = async () => { await store.completeSession(finalStats.value.distance, finalStats.value.steps); router.push('/') }
const closeAndRegister = async () => { await store.completeSession(finalStats.value.distance, finalStats.value.steps); router.push('/register') }
const quitSession = () => { pauseTimer(); router.push('/') }

const shareSession = async () => {
  const text = `Je viens de terminer "${currentSession.value.title}" en parcourant ${finalStats.value.distance} km !\nRejoins-moi sur Je Cours Pour Ma Forme !`
  if (navigator.share) { try { await navigator.share({ title: 'Exploit sportif !', text: text }) } catch (err) {} } 
  else { navigator.clipboard.writeText(text); alert('Texte copié dans le presse-papier !') }
}

onMounted(async () => {
  // Préchauffer les voix (Nécessaire sur certains navigateurs)
  window.speechSynthesis.getVoices()
  if (!isDataReady.value) await store.initApp()
  if (currentSession.value) initStep()
})

onUnmounted(() => pauseTimer())
</script>

<template>
  <main class="p-5 font-body max-w-[600px] mx-auto text-center">
    
    <div class="text-left mb-5">
      <button @click="quitSession" class="bg-transparent border-none text-red-500 text-base font-bold cursor-pointer hover:opacity-80 transition-opacity">
        ← Quitter la course
      </button>
    </div>

    <!-- Pop-up de fin -->
    <div v-if="showEndPopup" class="fixed inset-0 bg-black/60 flex items-center justify-center z-50 p-5 backdrop-blur-sm">
      <div class="bg-surface p-8 rounded-xl w-full max-w-[320px] relative shadow-2xl dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
        <button @click="closeAndSave" class="absolute top-3 right-4 bg-transparent border-none text-2xl text-gray-400 cursor-pointer hover:text-gray-800 dark:hover:text-gray-200">×</button>
        <h2 class="mt-0 font-heading text-2xl text-accent font-bold mb-6">Session terminée ! 🏆</h2>
        
        <div class="my-6 bg-gray-50 p-4 rounded-lg flex justify-around dark:bg-gray-900 border border-gray-100 dark:border-gray-700">
          <div>
            <strong class="text-2xl text-primary font-heading dark:text-gray-100">{{ finalStats.distance }} <span class="text-sm">km</span></strong>
            <div class="text-[0.65rem] text-gray-500 uppercase tracking-widest font-bold">Distance</div>
          </div>
          <div class="w-px bg-gray-200 dark:bg-gray-700"></div>
          <div>
            <strong class="text-2xl text-primary font-heading dark:text-gray-100">{{ finalStats.steps }}</strong>
            <div class="text-[0.65rem] text-gray-500 uppercase tracking-widest font-bold">Pas</div>
          </div>
        </div>

        <div v-if="!isGuest">
          <button @click="shareSession" class="w-full p-4 mb-3 bg-blue-500 text-white rounded-cta font-bold cursor-pointer hover:bg-blue-600 transition-colors uppercase tracking-wider font-heading">Partager l'exploit</button>
          <button @click="closeAndSave" class="w-full p-4 bg-accent text-primary rounded-cta font-bold cursor-pointer hover:bg-opacity-90 transition-colors uppercase tracking-wider font-heading">Valider & Quitter</button>
        </div>
        <div v-else>
          <p class="text-gray-500 text-sm mb-5 italic dark:text-gray-400">Créez un compte pour sauvegarder cette course et débloquer la suite du programme !</p>
          <button @click="closeAndRegister" class="w-full p-4 bg-accent text-primary rounded-cta font-bold cursor-pointer hover:bg-opacity-90 transition-colors uppercase tracking-wider font-heading shadow-lg">S'inscrire</button>
        </div>
      </div>
    </div>

    <!-- Interface d'entraînement -->
    <div v-if="!currentSession" class="text-gray-500 animate-pulse mt-10">Chargement de la session...</div>
    
    <div v-else>
      <StepProgressBar :totalSteps="totalSteps" :currentStepIndex="currentStepIndex" />
      
      <!-- L'ancien h2 du texte est supprimé car intégré dans le cercle SVG -->
      <TimerDisplay 
        :timeRemaining="timeRemaining" 
        :totalTime="currentExercise.duration_seconds" 
        :exerciseType="currentExercise.type" 
      />

      <div class="flex justify-center gap-4 mt-8">
        <button @click="prevStep" :disabled="currentStepIndex === 0" class="px-6 py-3 rounded-cta font-bold text-lg cursor-pointer transition-colors disabled:opacity-50 font-heading uppercase tracking-wide" :class="currentStepIndex === 0 ? 'bg-gray-200 text-gray-400 dark:bg-gray-700 dark:text-gray-500' : 'bg-gray-200 text-gray-700 hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600'">Précédent</button>
        <button v-if="!isRunning" @click="startTimer" class="px-8 py-3 bg-accent text-primary rounded-cta font-bold text-lg cursor-pointer hover:bg-opacity-90 transition-transform hover:scale-105 shadow-md font-heading uppercase tracking-widest">Démarrer</button>
        <button v-else @click="pauseTimer" class="px-8 py-3 bg-red-500 text-white rounded-cta font-bold text-lg cursor-pointer hover:bg-red-600 transition-colors shadow-md font-heading uppercase tracking-widest animate-pulse">Pause</button>
        <button @click="nextStep" class="px-6 py-3 bg-gray-200 text-gray-700 rounded-cta font-bold text-lg cursor-pointer hover:bg-gray-300 transition-colors dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600 font-heading uppercase tracking-wide">Suivant</button>
      </div>
    </div>
  </main>
</template>