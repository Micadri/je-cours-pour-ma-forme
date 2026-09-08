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

const totalDistance = computed(() => {
  const sum = store.completedSessions.reduce((acc, session) => acc + (parseFloat(session.distance) || 0), 0)
  return sum.toFixed(2)
})

const totalSteps = computed(() => store.completedSessions.reduce((acc, session) => acc + (parseInt(session.steps) || 0), 0))

const nextSessionIndex = computed(() => {
  if (!store.currentSessionDetails) return 1
  const weekSessions = store.currentSessionDetails.week.sessions
  const currentId = store.currentSessionDetails.session.id
  return weekSessions.findIndex(s => s.id === currentId) + 1
})

const nextSessionDuration = computed(() => {
  if (!store.currentSessionDetails) return 0
  const exercises = store.currentSessionDetails.session.exercises
  const totalSeconds = exercises.reduce((acc, exo) => acc + parseInt(exo.duration_seconds), 0)
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
  return store.seasonData.weeks.filter(w => {
    if (w.id < currentWeekId) return true
    if (w.id === currentWeekId) return currentSessionId > w.sessions[0].id
    return false
  })
})

const openWeekSelector = () => {
  if (availableWeeks.value.length > 0) {
    selectedWeekId.value = availableWeeks.value[availableWeeks.value.length - 1].id
    showWeekSelector.value = true
  }
}

const startSession = () => router.push('/run')
</script>

<template>
  <div v-if="store.currentSessionDetails">
    <div style="background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.08); margin-bottom: 20px; border: 1px solid #eee;">
      <div style="background: #6e757b; color: white; padding: 12px 15px; font-weight: bold; font-size: 1.15rem; text-align: center;">
        <span style="color: white !important;">{{ store.currentSessionDetails.week.title }} (sur {{ store.seasonData.weeks.length }})</span>
      </div>
      
      <div @click="showPreview = !showPreview" style="background: #e38734; color: white; padding: 12px 15px; display: flex; justify-content: space-between; align-items: center; cursor: pointer; border-bottom: 1px solid rgba(255,255,255,0.2);">
        <div>
          <div style="font-size: 0.75rem; opacity: 0.9; text-transform: uppercase; letter-spacing: 1px; font-weight: 800; color: white !important; margin-bottom: 3px;">Prochaine course</div>
          <div style="font-weight: bold; font-size: 1.1rem; color: white !important;">
            {{ nextSessionIndex === 1 ? '1ère' : nextSessionIndex + 'ème' }} session de la semaine
          </div>
        </div>
        <div style="text-align: right; display: flex; flex-direction: column; align-items: flex-end;">
          <div style="font-weight: bold; font-size: 1.3rem; color: white !important;">{{ nextSessionDuration }} min</div>
          <div style="font-size: 0.75rem; color: white !important; margin-top: 6px; text-transform: uppercase; font-weight: bold; background: rgba(255, 255, 255, 0.25); padding: 4px 10px; border-radius: 20px; border: 1px solid rgba(255, 255, 255, 0.4); display: flex; align-items: center; gap: 5px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            Détails <span style="font-size: 0.65rem;">{{ showPreview ? '▲' : '▼' }}</span>
          </div>
        </div>
      </div>

      <div v-if="showPreview" style="background: #fafafa; padding: 15px; border-bottom: 1px solid #eee;">
          <div v-for="(exo, i) in store.currentSessionDetails.session.exercises" :key="i" style="display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px dashed #ddd; font-size: 0.95rem; color: #444;">
            <span style="text-transform: capitalize;">{{ exo.type }}</span>
            <span style="font-weight: bold; color: #e38734;">{{ formatDuration(exo.duration_seconds) }}</span>
          </div>
      </div>
      
      <div style="padding: 15px; background: #fff;">
        <div style="text-align: center; color: #666; font-size: 0.85rem; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 15px;">Statistiques de la saison</div>
        <div style="display: flex; justify-content: space-around;">
          <div style="text-align: center; width: 45%;">
            <div style="font-size: 1.6rem; color: #4CAF50; font-weight: bold;">{{ totalDistance }} km</div>
            <div style="font-size: 0.75rem; color: #888; text-transform: uppercase; letter-spacing: 0.8px; margin-top: 4px; font-weight: bold;">Distance Cumulée</div>
          </div>
          <div style="width: 1px; background: #eee;"></div>
          <div style="text-align: center; width: 45%;">
            <div style="font-size: 1.6rem; color: #4CAF50; font-weight: bold;">{{ totalSteps }}</div>
            <div style="font-size: 0.75rem; color: #888; text-transform: uppercase; letter-spacing: 0.8px; margin-top: 4px; font-weight: bold;">Pas Cumulés</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Contrôles -->
    <button v-if="!isGuest || store.completedSessions.length === 0" @click="startSession" style="width: 100%; padding: 15px; background: #4CAF50; color: white; border: none; border-radius: 8px; font-size: 18px; font-weight: bold; cursor: pointer; margin-bottom: 15px;">
      Démarrer la session
    </button>
    <button v-else @click="router.push('/register')" style="width: 100%; padding: 15px; background: #e38734; color: white; border: none; border-radius: 8px; font-size: 18px; font-weight: bold; cursor: pointer; margin-bottom: 15px; box-shadow: 0 4px 10px rgba(227, 135, 52, 0.3);">
      🔒 S'inscrire pour continuer
    </button>

    <div style="display: flex; gap: 10px; margin-bottom: 15px; align-items: stretch; height: 40px;">
      <button v-if="!showWeekSelector" @click="openWeekSelector" :disabled="availableWeeks.length === 0" :style="{ flex: 1, background: availableWeeks.length === 0 ? '#f0f0f0' : '#e0e0e0', border: 'none', borderRadius: '5px', cursor: availableWeeks.length === 0 ? 'not-allowed' : 'pointer', color: availableWeeks.length === 0 ? '#aaa' : '#333', fontWeight: 'bold' }">Reset Semaine</button>
      <div v-else style="flex: 2; display: flex; gap: 5px;">
        <select v-model="selectedWeekId" style="flex: 1; padding: 0 5px; border-radius: 5px; border: 1px solid #ccc; background: #fff; font-size: 14px;">
          <option v-for="week in availableWeeks" :key="week.id" :value="week.id">{{ week.title }}</option>
        </select>
        <button @click="store.resetToWeek(selectedWeekId); showWeekSelector = false" style="padding: 0 15px; background: #4CAF50; border: none; border-radius: 5px; cursor: pointer; color: white; font-weight: bold;">✔</button>
        <button @click="showWeekSelector = false" style="padding: 0 15px; background: #9e9e9e; border: none; border-radius: 5px; cursor: pointer; color: white; font-weight: bold;">✖</button>
      </div>
      <button v-if="!showWeekSelector" @click="store.resetSeason" style="flex: 1; background: #ffebee; border: none; border-radius: 5px; cursor: pointer; color: #d32f2f; font-weight: bold;">Reset Saison</button>
    </div>
  </div>
</template>