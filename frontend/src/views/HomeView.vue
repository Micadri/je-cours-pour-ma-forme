<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useProgramStore } from '../stores/program'
import SessionHistory from '../components/SessionHistory.vue'

const handleLogout = () => {
  store.logout()
  router.push('/login')
}

const router = useRouter()
const store = useProgramStore()

const showWeekSelector = ref(false)
const selectedWeekId = ref(1)

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

// Calcul des totaux
const totalDistance = computed(() => {
  const sum = store.completedSessions.reduce((acc, session) => acc + (parseFloat(session.distance) || 0), 0)
  return sum.toFixed(2)
})

const totalSteps = computed(() => {
  return store.completedSessions.reduce((acc, session) => acc + (parseInt(session.steps) || 0), 0)
})

const startSession = () => router.push('/run')

onMounted(() => { store.initApp() })
</script>

<template>
  <main style="padding: 20px; font-family: sans-serif; max-width: 600px; margin: 0 auto;">
    <h1 style="text-align: center;">Vue d'ensemble</h1>
    
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
  <div style="display: flex; align-items: center; gap: 15px;">
    <div style="width: 50px; height: 50px; border-radius: 50%; background: #ccc; overflow: hidden; display: flex; align-items: center; justify-content: center;">
      <img v-if="store.userProfile?.avatar" :src="store.userProfile.avatar" style="width: 100%; height: 100%; object-fit: cover;" />
      <span v-else style="color: white; font-size: 20px;">🏃</span>
    </div>
    <h2 style="margin: 0; font-size: 1.2rem; color: #333;">
      Bonjour, {{ store.userProfile?.first_name || 'Coureur' }} !
    </h2>
  </div>
  <div>
    <button @click="router.push('/profile')" style="padding: 8px 12px; background: #e0e0e0; color: #333; border: none; border-radius: 5px; cursor: pointer; font-size: 14px; margin-right: 10px;">
      Profil
    </button>
    <button @click="handleLogout" style="padding: 8px 12px; background: transparent; color: #f44336; border: 1px solid #f44336; border-radius: 5px; cursor: pointer; font-size: 14px;">
      Déconnexion
    </button>
  </div>
</div>

    <div v-if="!store.seasonData || !store.currentProgress">
      <p style="text-align: center;">Synchronisation en cours...</p>
    </div>
    
    <div v-else>
      <div v-if="store.currentSessionDetails" style="background: #f4f4f4; padding: 15px; border-radius: 8px; margin-bottom: 15px;">
        <h2 style="margin-top: 0; font-size: 1.2rem;">Ta progression actuelle</h2>
        <p style="margin: 5px 0;"><strong>Programme :</strong> {{ store.seasonData.title }}</p>
        <p style="margin: 5px 0;"><strong>Semaine :</strong> {{ store.currentSessionDetails.week.title }}</p>
        <p style="margin: 5px 0;"><strong>Prochaine course :</strong> {{ store.currentSessionDetails.session.title }}</p>
        
        <!-- Affichage des totaux -->
        <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #ddd; display: flex; justify-content: space-around;">
          <div style="text-align: center;">
            <div style="font-size: 1.2rem; color: #4CAF50; font-weight: bold;">{{ totalDistance }} km</div>
            <div style="font-size: 0.85rem; color: #666;">Distance totale</div>
          </div>
          <div style="text-align: center;">
            <div style="font-size: 1.2rem; color: #4CAF50; font-weight: bold;">{{ totalSteps }}</div>
            <div style="font-size: 0.85rem; color: #666;">Pas cumulés</div>
          </div>
        </div>
      </div>

      <button 
        @click="startSession" 
        style="width: 100%; padding: 15px; background: #4CAF50; color: white; border: none; border-radius: 8px; font-size: 18px; font-weight: bold; cursor: pointer; margin-bottom: 15px;"
      >
        Démarrer la session
      </button>

      <div style="display: flex; gap: 10px; margin-bottom: 30px; align-items: stretch; height: 40px;">
        <button 
          v-if="!showWeekSelector"
          @click="openWeekSelector" 
          :disabled="availableWeeks.length === 0"
          :style="{
            flex: 1, 
            background: availableWeeks.length === 0 ? '#f0f0f0' : '#e0e0e0', 
            border: 'none', 
            borderRadius: '5px', 
            cursor: availableWeeks.length === 0 ? 'not-allowed' : 'pointer', 
            color: availableWeeks.length === 0 ? '#aaa' : '#333', 
            fontWeight: 'bold'
          }"
        >
          Reset Semaine
        </button>

        <div v-else style="flex: 2; display: flex; gap: 5px;">
          <select v-model="selectedWeekId" style="flex: 1; padding: 0 5px; border-radius: 5px; border: 1px solid #ccc; background: #fff; font-size: 14px;">
            <option v-for="week in availableWeeks" :key="week.id" :value="week.id">{{ week.title }}</option>
          </select>
          <button @click="store.resetToWeek(selectedWeekId); showWeekSelector = false" style="padding: 0 15px; background: #4CAF50; border: none; border-radius: 5px; cursor: pointer; color: white; font-weight: bold;">✔</button>
          <button @click="showWeekSelector = false" style="padding: 0 15px; background: #9e9e9e; border: none; border-radius: 5px; cursor: pointer; color: white; font-weight: bold;">✖</button>
        </div>

        <button v-if="!showWeekSelector" @click="store.resetSeason" style="flex: 1; background: #ffebee; border: none; border-radius: 5px; cursor: pointer; color: #d32f2f; font-weight: bold;">
          Reset Saison
        </button>
      </div>

      <SessionHistory />

    </div>
  </main>
</template>