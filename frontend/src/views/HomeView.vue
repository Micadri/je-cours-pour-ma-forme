<script setup>
import { onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useProgramStore } from '../stores/program'

// 1. Initialisation
const router = useRouter()
const store = useProgramStore()

// 2. Fonctions d'action
const startSession = () => {
  router.push('/run')
}

const logout = () => {
  localStorage.removeItem('auth_token')
  store.seasonData = null
  store.currentProgress = null
  router.push('/login')
}

// 3. Cycle de vie
onMounted(() => {
  store.initApp()
})
</script>

<template>
  <main style="padding: 20px; font-family: sans-serif; max-width: 600px; margin: 0 auto;">
    <h1>Vue d'ensemble</h1>
    
    <div v-if="!store.seasonData || !store.currentProgress">
      <p>Synchronisation en cours...</p>
    </div>
    
    <div v-else>
      <div style="background: #f4f4f4; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
        <h2>Ta progression</h2>
        <p><strong>Programme :</strong> {{ store.seasonData.title }}</p>
        <p><strong>Semaine en cours :</strong> {{ store.currentProgress.current_week_id }}</p>
        <p><strong>Prochaine étape :</strong> Session {{ store.currentProgress.current_session_id }}</p>
      </div>
      
     <button @click="startSession" style="width: 100%; padding: 15px; background: #4CAF50; color: white; border: none; border-radius: 8px; font-size: 18px; font-weight: bold; cursor: pointer;">
  Démarrer la session
</button>
      
      <div style="margin-top: 20px; display: flex; gap: 10px;">
        <button style="flex: 1; padding: 10px;">Reset Session</button>
        <button style="flex: 1; padding: 10px;">Reset Semaine</button>
        <button style="flex: 1; padding: 10px;">Reset Saison</button>
      </div>
      <div style="margin-top: 40px; text-align: center;">
  <button @click="logout" style="padding: 10px 20px; background: #f44336; color: white; border: none; border-radius: 8px; cursor: pointer;">
    Se déconnecter
  </button>
</div>
    </div>
  </main>
</template>