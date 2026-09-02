<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useProgramStore } from '../stores/program'

const router = useRouter()
const store = useProgramStore()

// Pagination
const currentPage = ref(1)
const itemsPerPage = 3

const totalPages = computed(() => Math.ceil(store.completedSessions.length / itemsPerPage))

const paginatedSessions = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage
  return store.completedSessions.slice(start, start + itemsPerPage)
})

const nextPage = () => { if (currentPage.value < totalPages.value) currentPage.value++ }
const prevPage = () => { if (currentPage.value > 1) currentPage.value-- }

const startSession = () => router.push('/run')

const logout = () => {
  localStorage.removeItem('auth_token')
  store.seasonData = null
  store.currentProgress = null
  router.push('/login')
}

onMounted(() => {
  store.initApp()
})
</script>

<template>
  <main style="padding: 20px; font-family: sans-serif; max-width: 600px; margin: 0 auto;">
    <h1 style="text-align: center;">Vue d'ensemble</h1>
    
    <div v-if="!store.seasonData || !store.currentProgress">
      <p style="text-align: center;">Synchronisation en cours...</p>
    </div>
    
    <div v-else>
      <!-- Progression actuelle -->
      <div v-if="store.currentSessionDetails" style="background: #f4f4f4; padding: 15px; border-radius: 8px; margin-bottom: 15px;">
        <h2 style="margin-top: 0; font-size: 1.2rem;">Ta progression actuelle</h2>
        <p><strong>Programme :</strong> {{ store.seasonData.title }}</p>
        <p><strong>Semaine :</strong> {{ store.currentSessionDetails.week.title }}</p>
        <p><strong>Prochaine course :</strong> {{ store.currentSessionDetails.session.title }}</p>
      </div>

      <button 
        @click="startSession" 
        style="width: 100%; padding: 15px; background: #4CAF50; color: white; border: none; border-radius: 8px; font-size: 18px; font-weight: bold; cursor: pointer; margin-bottom: 15px;"
      >
        Démarrer la session
      </button>

      <!-- Actions rapides -->
      <div style="display: flex; gap: 10px; margin-bottom: 30px;">
        <button @click="store.resetWeek" style="flex: 1; padding: 10px; background: #e0e0e0; border: none; border-radius: 5px; cursor: pointer; color: #333;">
          Reset Semaine
        </button>
        <button @click="store.resetSeason" style="flex: 1; padding: 10px; background: #ffebee; border: none; border-radius: 5px; cursor: pointer; color: #d32f2f;">
          Reset Saison
        </button>
      </div>

      <!-- Historique : Mes sessions -->
      <div style="margin-bottom: 25px;">
        <h3 style="color: #333; margin-bottom: 15px; border-bottom: 2px solid #eee; padding-bottom: 5px;">Mes sessions</h3>
        
        <p v-if="store.completedSessions.length === 0" style="color: #888; text-align: center; font-style: italic;">
          Aucune course terminée pour le moment.
        </p>

        <ul v-else style="list-style: none; padding: 0; margin: 0;">
          <li 
            v-for="session in paginatedSessions" 
            :key="session.id"
            style="display: flex; justify-content: space-between; align-items: center; padding: 12px 15px; border: 1px solid #ddd; border-radius: 8px; margin-bottom: 10px; background: #fff;"
          >
            <div>
              <div style="font-weight: bold; color: #4CAF50; font-size: 0.9rem;">{{ session.weekTitle }}</div>
              <div style="color: #555;">{{ session.title }}</div>
            </div>
            <button 
              @click="store.deleteSession(session.id)" 
              style="background: none; border: none; font-size: 20px; cursor: pointer; color: #ff5252; padding: 5px;"
              title="Annuler cette session"
            >
              ✖
            </button>
          </li>
        </ul>

        <!-- Contrôles de pagination -->
        <div v-if="totalPages > 1" style="display: flex; justify-content: space-between; align-items: center; margin-top: 15px;">
          <button @click="prevPage" :disabled="currentPage === 1" style="padding: 8px 12px; border: 1px solid #ccc; border-radius: 5px; background: #fff; cursor: pointer;" :style="{ opacity: currentPage === 1 ? 0.5 : 1 }">
            Précédent
          </button>
          <span style="font-size: 0.9rem; color: #666;">Page {{ currentPage }} sur {{ totalPages }}</span>
          <button @click="nextPage" :disabled="currentPage === totalPages" style="padding: 8px 12px; border: 1px solid #ccc; border-radius: 5px; background: #fff; cursor: pointer;" :style="{ opacity: currentPage === totalPages ? 0.5 : 1 }">
            Suivant
          </button>
        </div>
      </div>

      <div style="margin-top: 50px; text-align: center;">
        <button 
          @click="logout" 
          style="padding: 10px 20px; background: transparent; color: #f44336; border: 1px solid #f44336; border-radius: 8px; cursor: pointer;"
        >
          Se déconnecter
        </button>
      </div>
    </div>
  </main>
</template>