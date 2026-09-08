<script setup>
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useProgramStore } from '../stores/program'

const router = useRouter()
const store = useProgramStore()

const isGuest = computed(() => !localStorage.getItem('auth_token'))
const showPreview = ref(false)
const showWeekSelector = ref(false)
const selectedWeekId = ref(1)

const totalDistance = computed(() => (store.completedSessions.reduce((acc, session) => acc + (parseFloat(session.distance) || 0), 0)).toFixed(2))
const totalSteps = computed(() => store.completedSessions.reduce((acc, session) => acc + (parseInt(session.steps) || 0), 0))

const nextSessionIndex = computed(() => {
  if (!store.currentSessionDetails) return 1
  return store.currentSessionDetails.week.sessions.findIndex(s => s.id === store.currentSessionDetails.session.id) + 1
})

const nextSessionDuration = computed(() => {
  if (!store.currentSessionDetails) return 0
  const totalSeconds = store.currentSessionDetails.session.exercises.reduce((acc, exo) => acc + parseInt(exo.duration_seconds), 0)
  return Math.round(totalSeconds / 60)
})

const formatDuration = (seconds) => {
  const m = Math.floor(seconds / 60)
  const s = seconds % 60
  if (m === 0) return `${s}s`
  return s > 0 ? `${m}min ${s}s` : `${m} min`
}

const availableWeeks = computed(() => {
  if (!store.seasonData || !store.currentSessionDetails || !store.currentProgress) return []
  const currentWeekId = store.currentSessionDetails.week.id
  const currentSessionId = store.currentProgress.current_session_id
  return store.seasonData.weeks.filter(w => (w.id < currentWeekId) || (w.id === currentWeekId && currentSessionId > w.sessions[0].id))
})

const openWeekSelector = () => {
  if (availableWeeks.value.length > 0) {
    selectedWeekId.value = availableWeeks.value[availableWeeks.value.length - 1].id
    showWeekSelector.value = true
  }
}

const startSession = () => router.push('/run')
const goToRegister = () => router.push('/register')
</script>

<template>
  <div v-if="store.currentSessionDetails">
    <!-- CARTE PRINCIPALE -->
    <div class="bg-surface rounded-xl overflow-hidden shadow-md mb-6 border border-gray-100 dark:bg-gray-800 dark:border-gray-700">
      
      <!-- En-tête de la semaine -->
      <div class="bg-primary text-white py-3 px-4 font-bold text-lg text-center font-heading tracking-wide uppercase">
        {{ store.currentSessionDetails.week.title }} <span class="opacity-75 text-sm">(sur {{ store.seasonData.weeks.length }})</span>
      </div>
      
      <!-- Bannière Entraînement Suivant -->
      <div @click="showPreview = !showPreview" class="bg-primary text-white py-5 px-5 flex justify-between items-center cursor-pointer transition-all hover:brightness-110 shadow-inner">
        <div>
          <div class="text-[0.65rem] opacity-70 uppercase tracking-widest font-black mb-1 font-body">Prochaine course</div>
          <div class="font-bold text-xl font-heading tracking-wide text-white">
            {{ nextSessionIndex === 1 ? '1ère' : nextSessionIndex + 'ème' }} session
          </div>
        </div>
        
        <div class="text-right flex flex-col items-end">
          <div class="font-bold text-3xl font-heading tracking-wider text-accent drop-shadow-sm">
            {{ nextSessionDuration }} min
          </div>
          
          <div class="text-[0.7rem] mt-2 uppercase font-black bg-accent text-primary px-4 py-1.5 rounded-full flex items-center gap-1.5 transition-transform hover:scale-105 shadow-md">
            Détails <span class="text-[0.55rem]">{{ showPreview ? '▲' : '▼' }}</span>
          </div>
        </div>
      </div>

      <!-- Aperçu des blocs -->
      <div v-if="showPreview" class="bg-gray-50 p-4 border-b border-gray-200 dark:bg-gray-900 dark:border-gray-700">
          <div v-for="(exo, i) in store.currentSessionDetails.session.exercises" :key="i" class="flex justify-between py-1.5 border-b border-dashed border-gray-300 text-[0.95rem] text-gray-700 last:border-0 dark:border-gray-600 dark:text-gray-300">
            <span class="capitalize font-medium">{{ exo.type }}</span>
            <span class="font-bold text-accent">{{ formatDuration(exo.duration_seconds) }}</span>
          </div>
      </div>
      
      <!-- Statistiques -->
      <div class="p-5 bg-surface dark:bg-gray-800">
        <div class="text-center text-gray-400 text-xs font-bold uppercase tracking-widest mb-4">Statistiques de la saison</div>
        <div class="flex justify-around items-center">
          <div class="text-center w-[45%]">
            <div class="text-3xl text-primary font-heading font-bold dark:text-gray-100">{{ totalDistance }} <span class="text-lg text-accent">km</span></div>
            <div class="text-[0.65rem] text-gray-400 uppercase tracking-widest mt-1 font-bold">Distance</div>
          </div>
          <div class="w-px h-10 bg-gray-200 dark:bg-gray-700"></div>
          <div class="text-center w-[45%]">
            <div class="text-3xl text-primary font-heading font-bold dark:text-gray-100">{{ totalSteps }}</div>
            <div class="text-[0.65rem] text-gray-400 uppercase tracking-widest mt-1 font-bold">Pas Cumulés</div>
          </div>
        </div>
      </div>
    </div>

    <!-- BOUTON DÉMARRER -->
    <button v-if="!isGuest || store.completedSessions.length === 0" @click="startSession" class="w-full py-4 bg-primary text-accent rounded-cta text-xl font-bold cursor-pointer mb-4 shadow-lg hover:bg-opacity-90 transition-all uppercase tracking-widest font-heading active:scale-[0.98]">
      Démarrer la session
    </button>
    <button v-else @click="goToRegister" class="w-full py-4 bg-accent text-primary rounded-cta text-lg font-bold cursor-pointer mb-4 shadow-lg hover:bg-opacity-90 transition-all uppercase tracking-wide font-heading">
      🔒 S'inscrire pour continuer
    </button>

    <!-- Contrôles Secondaires -->
    <div class="flex gap-3 mb-4 items-stretch h-10">
      <button v-if="!showWeekSelector" @click="openWeekSelector" :disabled="availableWeeks.length === 0" class="flex-1 rounded-cta font-bold text-sm transition-colors" :class="availableWeeks.length === 0 ? 'bg-gray-100 text-gray-400 cursor-not-allowed dark:bg-gray-800 dark:text-gray-600' : 'bg-gray-200 text-gray-700 hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600'">Reset Semaine</button>
      
      <div v-else class="flex-2 flex gap-2 w-full">
        <select v-model="selectedWeekId" class="flex-1 px-2 rounded-cta border border-gray-300 bg-surface text-sm focus:ring-2 focus:ring-accent outline-none dark:bg-gray-700 dark:border-gray-600 dark:text-white">
          <option v-for="week in availableWeeks" :key="week.id" :value="week.id">{{ week.title }}</option>
        </select>
        <button @click="store.resetToWeek(selectedWeekId); showWeekSelector = false" class="px-4 bg-accent text-primary rounded-cta font-bold hover:bg-opacity-90">✔</button>
        <button @click="showWeekSelector = false" class="px-4 bg-gray-400 text-white rounded-cta font-bold hover:bg-gray-500">✖</button>
      </div>
      
      <button v-if="!showWeekSelector" @click="store.resetSeason" class="flex-1 bg-red-50 text-red-600 rounded-cta font-bold text-sm hover:bg-red-100 transition-colors border border-red-100 dark:bg-red-900/20 dark:border-red-900/30 dark:text-red-400">Reset Saison</button>
    </div>
  </div>
</template>