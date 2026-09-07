<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useProgramStore } from '../stores/program'
import SessionHistory from '../components/SessionHistory.vue'

const handleLogout = () => {
  store.logout()
  router.push('/welcome')
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

// Variable pour ouvrir/fermer la preview de la course actuelle
const showPreview = ref(false)

// Calcul du numéro de l'entraînement dans la semaine
const nextSessionIndex = computed(() => {
  if (!store.currentSessionDetails) return 1
  const weekSessions = store.currentSessionDetails.week.sessions
  const currentId = store.currentSessionDetails.session.id
  return weekSessions.findIndex(s => s.id === currentId) + 1
})

// Calcul de la durée totale de la prochaine course
const nextSessionDuration = computed(() => {
  if (!store.currentSessionDetails) return 0
  const exercises = store.currentSessionDetails.session.exercises
  const totalSeconds = exercises.reduce((acc, exo) => acc + parseInt(exo.duration_seconds), 0)
  return Math.round(totalSeconds / 60)
})

// Formateur de temps pour la preview
const formatDuration = (seconds) => {
  const m = Math.floor(seconds / 60)
  const s = seconds % 60
  if (m === 0) return `${s}s`
  return s > 0 ? `${m}min ${s}s` : `${m} min`
}

// Calcul des prochaines sessions à venir
const upcomingSessions = computed(() => {
  if (!store.seasonData || !store.currentProgress) return []
  const currentId = Number(store.currentProgress.current_session_id)
  const upcoming = []

  for (const week of store.seasonData.weeks) {
    let sessionIndex = 1
    for (const session of week.sessions) {
      if (session.id > currentId) {
        const durationSeconds = session.exercises.reduce((acc, exo) => acc + parseInt(exo.duration_seconds), 0)
        upcoming.push({
          ...session,
          weekTitle: week.title,
          sessionIndex,
          durationMin: Math.round(durationSeconds / 60)
        })
      }
      sessionIndex++
    }
  }
  return upcoming.slice(0, 3) 
})

const expandedUpcomingId = ref(null)
const toggleUpcoming = (id) => {
  expandedUpcomingId.value = expandedUpcomingId.value === id ? null : id
}

const startSession = () => router.push('/run')

onMounted(() => { store.initApp() })

// Vérification du mode invité
const isGuest = computed(() => !localStorage.getItem('auth_token'))
</script>

<template>
  <main class="dashboard-container">
    <h1 style="text-align: center; margin-bottom: 25px;">Vue d'ensemble</h1>
    
    <!-- SECTION 1 : EN-TÊTE ET PROGRESSION GLOBALE -->
    <section style="scroll-snap-align: start; scroll-margin-top: 20px; margin-bottom: 30px;">
      <div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 15px; margin-bottom: 25px; padding: 15px; background: #fff; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
        <div style="display: flex; align-items: center; gap: 15px; flex: 1; min-width: 200px;">
          <div style="width: 50px; height: 50px; border-radius: 50%; background: #ccc; overflow: hidden; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <img v-if="store.userProfile?.avatar" :src="store.userProfile.avatar" style="width: 100%; height: 100%; object-fit: cover;" />
            <span v-else style="color: white; font-size: 20px;">👤</span>
          </div>
         <h2 style="margin: 0; font-size: 1.2rem; color: #333; line-height: 1.2;">
            Bonjour, <br/><span style="color: #4CAF50;">{{ store.userProfile?.first_name || 'Coureur' }}</span> 
            <span v-if="isGuest" style="font-size: 0.8rem; color: #888; font-weight: normal; margin-left: 5px;">(Invité)</span>
          </h2>
        </div>
        
        <div v-if="!isGuest" style="display: flex; gap: 10px; flex-shrink: 0;">
          <button @click="router.push('/profile')" style="padding: 8px 15px; background: #e0e0e0; color: #333; border: none; border-radius: 8px; cursor: pointer; font-size: 14px; font-weight: bold;">Profil</button>
          <button @click="handleLogout" style="padding: 8px 15px; background: transparent; color: #f44336; border: 1px solid #f44336; border-radius: 8px; cursor: pointer; font-size: 14px; font-weight: bold;">Déconnexion</button>
        </div>
        <div v-else style="display: flex; gap: 10px; flex-shrink: 0;">
          <button @click="router.push('/login')" style="padding: 8px 15px; background: transparent; color: #4CAF50; border: 1px solid #4CAF50; border-radius: 8px; cursor: pointer; font-size: 14px; font-weight: bold;">Se connecter</button>
          <button @click="router.push('/register')" style="padding: 8px 15px; background: #4CAF50; color: white; border: none; border-radius: 8px; cursor: pointer; font-size: 14px; font-weight: bold;">S'inscrire</button>
        </div>
      </div>

      <div v-if="store.seasonData" style="padding: 0 5px;">
        <div style="display: flex; justify-content: space-between; font-size: 0.95rem; color: #555; margin-bottom: 8px; font-weight: bold;">
          <span style="color: inherit;">Progression : {{ store.seasonData.title }} ({{ store.seasonData.weeks.length }} semaines)</span>
          <span style="color: #4CAF50;">{{ seasonProgressPercent }}%</span>
        </div>
        <div style="height: 12px; background: #e0e0e0; border-radius: 6px; overflow: hidden; box-shadow: inset 0 1px 3px rgba(0,0,0,0.1);">
          <div :style="{ width: seasonProgressPercent + '%', height: '100%', background: '#4CAF50', transition: 'width 0.5s ease-in-out' }"></div>
        </div>
      </div>
    </section>

    <!-- Chargement -->
    <div v-if="!store.seasonData || !store.currentProgress">
      <p style="text-align: center;">Synchronisation en cours...</p>
    </div>
    
    <!-- Contenu Principal -->
    <div v-else>
      <!-- SECTION 2 : COURSE ACTUELLE -->
      <section style="scroll-snap-align: start; scroll-margin-top: 20px; margin-bottom: 40px;">
        <div v-if="store.currentSessionDetails" style="background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.08); margin-bottom: 20px; border: 1px solid #eee;">
          
          <div style="background: #6e757b; color: white; padding: 12px 15px; font-weight: bold; font-size: 1.15rem; text-align: center;">
            <span style="color: white !important;">
              {{ store.currentSessionDetails.week.title }} (sur {{ store.seasonData.weeks.length }})
            </span>
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
              <div style="font-size: 0.75rem; color: white !important; margin-top: 6px; text-transform: uppercase; font-weight: bold; background: rgba(255, 255, 255, 0.25); padding: 4px 10px; border-radius: 20px; border: 1px solid rgba(255, 255, 255, 0.4); display: flex; align-items: center; gap: 5px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); transition: background 0.2s;">
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

      <!-- Bouton de lancement conditionnel -->
      <button 
        v-if="!isGuest || store.completedSessions.length === 0"
        @click="startSession" 
        style="width: 100%; padding: 15px; background: #4CAF50; color: white; border: none; border-radius: 8px; font-size: 18px; font-weight: bold; cursor: pointer; margin-bottom: 15px;"
      >
        Démarrer la session
      </button>
      
      <button 
        v-else
        @click="router.push('/register')" 
        style="width: 100%; padding: 15px; background: #e38734; color: white; border: none; border-radius: 8px; font-size: 18px; font-weight: bold; cursor: pointer; margin-bottom: 15px; box-shadow: 0 4px 10px rgba(227, 135, 52, 0.3);"
      >
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
      </section>

      <!-- ZONE VERROUILLÉE POUR LES INVITÉS -->
      <div style="position: relative;">
        
        <div v-if="isGuest" style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; z-index: 10; display: flex; align-items: center; justify-content: center;">
          <button @click="router.push('/register')" style="padding: 15px 25px; background: #4CAF50; color: white; border: none; border-radius: 8px; font-size: 16px; font-weight: bold; cursor: pointer; box-shadow: 0 6px 20px rgba(0,0,0,0.15);">
            🔒 Créer un compte pour débloquer
          </button>
        </div>

        <div :style="{ pointerEvents: isGuest ? 'none' : 'auto' }">
          
          <!-- SECTION 3 : À VENIR -->
          <section v-if="upcomingSessions.length > 0" style="scroll-snap-align: start; scroll-margin-top: 20px; margin-bottom: 40px;">
            <h3 style="color: #333; margin-bottom: 10px; border-bottom: 2px solid #eee; padding-bottom: 5px;">À venir...</h3>
            
            <div :style="{ filter: isGuest ? 'blur(5px)' : 'none', opacity: isGuest ? 0.6 : 1, transition: 'all 0.3s' }">
              <div style="background: #fff; border-radius: 12px; border: 1px solid #ddd; overflow: hidden; box-shadow: 0 2px 5px rgba(0,0,0,0.02);">
                <div v-for="(session, index) in upcomingSessions" :key="'up-' + session.id" :style="{ borderBottom: index < upcomingSessions.length - 1 ? '1px solid #eee' : 'none' }">
                  <div @click="toggleUpcoming(session.id)" style="display: flex; justify-content: space-between; align-items: center; padding: 12px 15px; cursor: pointer;">
                    <div>
                      <div style="font-weight: bold; color: #e38734; font-size: 0.95rem;">{{ session.weekTitle }}</div>
                      <div style="color: #666; font-size: 0.85rem; margin-top: 2px;">{{ session.sessionIndex === 1 ? '1ère' : session.sessionIndex + 'ème' }} session de la semaine • {{ session.durationMin }} min</div>
                    </div>
                    <div style="font-size: 0.7rem; color: #888; text-transform: uppercase; font-weight: bold; background: #f4f4f4; padding: 4px 8px; border-radius: 12px; border: 1px solid #ddd; display: flex; align-items: center; gap: 4px;">
                      Détails <span>{{ expandedUpcomingId === session.id ? '▲' : '▼' }}</span>
                    </div>
                  </div>
                  
                  <div v-if="expandedUpcomingId === session.id" style="background: #fafafa; padding: 12px 15px; border-top: 1px solid #eee;">
                    <div v-for="(exo, i) in session.exercises" :key="i" style="display: flex; justify-content: space-between; padding: 4px 0; border-bottom: 1px dashed #ddd; font-size: 0.85rem; color: #555;">
                      <span style="text-transform: capitalize;">{{ exo.type }}</span>
                      <span style="font-weight: bold; color: #e38734;">{{ formatDuration(exo.duration_seconds) }}</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </section>

          <!-- SECTION 4 : HISTORIQUE -->
          <section style="scroll-snap-align: start; scroll-margin-top: 20px; margin-bottom: 40px;">
            <h3 style="color: #333; margin-bottom: 10px; border-bottom: 2px solid #eee; padding-bottom: 5px;">Mes sessions</h3>
            <div :style="{ filter: isGuest ? 'blur(5px)' : 'none', opacity: isGuest ? 0.6 : 1, transition: 'all 0.3s' }">
              <SessionHistory />
            </div>
          </section>

        </div>
      </div>
    </div>
    
    <!-- SECTION 5 : BOÎTE À OUTILS -->
    <section style="scroll-snap-align: start; scroll-margin-top: 20px; margin-bottom: 25px;">
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
    </section>
  </main>
</template>

<style scoped>
/* Conteneur principal gérant la largeur selon l'écran */
.dashboard-container {
  padding: 20px 20px 50vh 20px;
  font-family: sans-serif;
  margin: 0 auto;
  max-width: 600px; /* Largeur pour les téléphones */
}

/* Sur les ordinateurs et tablettes, on étire la vue à 900px */
@media (min-width: 768px) {
  .dashboard-container {
    max-width: 900px;
  }
}
</style>