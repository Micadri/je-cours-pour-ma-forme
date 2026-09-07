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
const totalSessionsInSeason = computed(() => {
  if (!store.seasonData) return 0
  return store.seasonData.weeks.reduce((acc, week) => acc + week.sessions.length, 0)
})

const seasonProgressPercent = computed(() => {
  if (totalSessionsInSeason.value === 0) return 0
  return Math.round((store.completedSessions.length / totalSessionsInSeason.value) * 100)
})
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
    
<div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 15px; margin-bottom: 25px; padding: 15px; background: #fff; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
  <!-- Avatar + Nom -->
  <div style="display: flex; align-items: center; gap: 15px; flex: 1; min-width: 200px;">
    <div style="width: 50px; height: 50px; border-radius: 50%; background: #ccc; overflow: hidden; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
      <img v-if="store.userProfile?.avatar" :src="store.userProfile.avatar" style="width: 100%; height: 100%; object-fit: cover;" />
      <span v-else style="color: white; font-size: 20px;">👤</span>
    </div>
    <h2 style="margin: 0; font-size: 1.2rem; color: #333; line-height: 1.2;">
      Bonjour, <br/><span style="color: #4CAF50;">{{ store.userProfile?.first_name || 'Coureur' }}</span> !
    </h2>
  </div>
  
  <!-- Boutons -->
  <div style="display: flex; gap: 10px; flex-shrink: 0;">
    <button @click="router.push('/profile')" style="padding: 8px 15px; background: #e0e0e0; color: #333; border: none; border-radius: 8px; cursor: pointer; font-size: 14px; font-weight: bold;">
      Profil
    </button>
    <button @click="handleLogout" style="padding: 8px 15px; background: transparent; color: #f44336; border: 1px solid #f44336; border-radius: 8px; cursor: pointer; font-size: 14px; font-weight: bold;">
      Déconnexion
    </button>
  </div>
</div>

    <div v-if="!store.seasonData || !store.currentProgress">
      <p style="text-align: center;">Synchronisation en cours...</p>
    </div>
    
    <div v-else>
     <!-- Barre de progression globale de la saison -->
      <div v-if="store.seasonData" style="margin-bottom: 25px; padding: 0 5px;">
        <div style="display: flex; justify-content: space-between; font-size: 0.95rem; color: #555; margin-bottom: 8px; font-weight: bold;">
          <span style="color: inherit;">Progression : {{ store.seasonData.title }} ({{ store.seasonData.weeks.length }} semaines)</span>
          <span style="color: #4CAF50;">{{ seasonProgressPercent }}%</span>
        </div>
        <div style="height: 12px; background: #e0e0e0; border-radius: 6px; overflow: hidden; box-shadow: inset 0 1px 3px rgba(0,0,0,0.1);">
          <div :style="{ width: seasonProgressPercent + '%', height: '100%', background: '#4CAF50', transition: 'width 0.5s ease-in-out' }"></div>
        </div>
      </div>

      <!-- Carte de la prochaine session (Style JCPMF) -->
      <div v-if="store.currentSessionDetails" style="background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.08); margin-bottom: 20px; border: 1px solid #eee;">
        
        <!-- En-tête Gris : Semaine -->
        <div style="background: #6e757b; color: white; padding: 12px 15px; font-weight: bold; font-size: 1.2rem; display: flex; justify-content: space-between; align-items: center;">
          <span style="color: white !important;">{{ store.currentSessionDetails.week.title }}</span>
          <span style="font-size: 0.85rem; opacity: 0.9; text-transform: uppercase; letter-spacing: 0.5px; color: white !important;">Prochaine course</span>
        </div>
        
        <!-- Sous-titre Orange : Jour -->
        <div style="background: #e38734; color: white; padding: 8px 15px; font-weight: bold; font-size: 1.05rem;">
          <span style="color: white !important;">{{ store.currentSessionDetails.session.title.split(' - ')[1] || store.currentSessionDetails.session.title }}</span>
        </div>
        
        <!-- Statistiques cumulées -->
        <div style="padding: 20px 15px; display: flex; justify-content: space-around;">
          <div style="text-align: center;">
            <div style="font-size: 1.5rem; color: #4CAF50; font-weight: bold;">{{ totalDistance }} km</div>
            <div style="font-size: 0.8rem; color: #888; text-transform: uppercase; letter-spacing: 1px; margin-top: 4px;">Distance totale</div>
          </div>
          <div style="width: 1px; background: #eee;"></div>
          <div style="text-align: center;">
            <div style="font-size: 1.5rem; color: #4CAF50; font-weight: bold;">{{ totalSteps }}</div>
            <div style="font-size: 0.8rem; color: #888; text-transform: uppercase; letter-spacing: 1px; margin-top: 4px;">Pas cumulés</div>
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
    <!-- Nouvelle section : La boîte à outils -->
    <div style="margin-top: 30px; margin-bottom: 25px;">
      <h3 style="color: #333; margin-bottom: 15px; border-bottom: 2px solid #eee; padding-bottom: 5px;">Préparation & Conseils</h3>
      <div @click="router.push('/tips')" style="display: flex; align-items: center; justify-content: space-between; background: #fff; padding: 15px; border-radius: 8px; border: 1px solid #ddd; cursor: pointer; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
        <div style="display: flex; align-items: center; gap: 15px;">
          <div style="font-size: 24px;">💡</div>
          <div>
            <div style="font-weight: bold; color: #4CAF50;">La boîte à outils du coureur</div>
            <div style="color: #666; font-size: 0.85rem; margin-top: 3px;">Échauffement, postures, hydratation...</div>
          </div>
        </div>
        <div style="color: #aaa; font-weight: bold;">></div>
      </div>
    </div>
  </main>
</template>