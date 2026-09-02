import { ref, computed } from 'vue'
import { defineStore } from 'pinia'

const API_BASE = 'http://localhost:8888/je-cours-pour-ma-forme/api' 

export const useProgramStore = defineStore('program', () => {
  const seasonData = ref(null)
  const currentProgress = ref(null)
  const sessionHistory = ref([])

  const currentSessionDetails = computed(() => {
    if (!seasonData.value || !currentProgress.value) return null
    const targetId = currentProgress.value.current_session_id
    for (const week of seasonData.value.weeks) {
      const session = week.sessions.find(s => s.id === targetId)
      if (session) return { week, session }
    }
    return null
  })

  const completedSessions = computed(() => {
    if (!seasonData.value || !currentProgress.value) return []
    const targetId = currentProgress.value.current_session_id
    const completed = []
    
    for (const week of seasonData.value.weeks) {
      for (const session of week.sessions) {
        if (session.id < targetId) {
          // On cherche les stats, avec les bons noms de colonnes
          const stats = sessionHistory.value.find(h => h.session_id === session.id) || { distance_meters: 0, steps_count: 0 }
          
          completed.push({ 
            ...session, 
            weekTitle: week.title, 
            distance: (stats.distance_meters / 1000).toFixed(2), // On reconvertit en km pour l'affichage Vue
            steps: stats.steps_count 
          })
        }
      }
    }
    return completed.reverse()
  })

  // --- MOTEUR DE SYNCHRONISATION ---
  async function syncQueue() {
    const token = localStorage.getItem('auth_token')
    if (!token) return
    const queue = JSON.parse(localStorage.getItem('pwa_sync_queue') || '[]')
    if (queue.length === 0) return

    try {
      for (const action of queue) {
        if (action.type === 'log') {
          await fetch(`${API_BASE}/runner/log.php?token=${token}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ 
              session_id: action.session_id, 
              distance_meters: action.distance_meters, 
              steps_count: action.steps_count 
            })
          })
        } else if (action.type === 'reset') {
          await fetch(`${API_BASE}/runner/reset.php?token=${token}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ target_session_id: action.target_session_id })
          })
        }
      }
      localStorage.removeItem('pwa_sync_queue')
      console.log("Synchronisation hors-ligne réussie !")
    } catch (e) {
      console.warn("Toujours hors-ligne, la synchronisation attendra.")
    }
  }

  function addToSyncQueue(action) {
    const queue = JSON.parse(localStorage.getItem('pwa_sync_queue') || '[]')
    queue.push(action)
    localStorage.setItem('pwa_sync_queue', JSON.stringify(queue))
  }
  // ---------------------------------

  async function initApp() {
    const token = localStorage.getItem('auth_token')
    if (!token) return

    await syncQueue()

    try {
      const res = await fetch('/program_5k.json')
      const rawData = await res.json()
      
      const sourceProgram = Array.isArray(rawData) ? rawData[0] : rawData
      const formattedData = { title: sourceProgram.label || "Programme 5K", weeks: [] }
      const weeksMap = {}
      let sessionIdCounter = 1

      sourceProgram.etapes.forEach(etape => {
        const match = etape.label.match(/Semaine\s*(\d+)/i)
        const weekId = match ? parseInt(match[1]) : 1
        if (!weeksMap[weekId]) weeksMap[weekId] = { id: weekId, title: `Semaine ${weekId}`, sessions: [] }
        const mappedExercises = etape.steps.map(step => ({ type: step.type, duration_seconds: Math.round(step.time * 60) }))
        weeksMap[weekId].sessions.push({ id: sessionIdCounter++, title: etape.label, exercises: mappedExercises })
      })

      formattedData.weeks = Object.values(weeksMap)
      seasonData.value = formattedData
      localStorage.setItem('pwa_cache_program', JSON.stringify(formattedData))

      const progressRes = await fetch(`${API_BASE}/runner/progress.php?token=${token}`)
      const progressJson = await progressRes.json()
      
      if (progressJson.status === 'success') {
         currentProgress.value = progressJson.data.progress
         // Si ton API renvoie l'historique de la DB, on le met à jour ici
         if (progressJson.data.history) {
             sessionHistory.value = progressJson.data.history
             localStorage.setItem('pwa_history', JSON.stringify(sessionHistory.value))
         }
      }

    } catch (e) {
      console.warn("Mode Hors-ligne activé. Chargement du cache.")
      const cachedProgram = localStorage.getItem('pwa_cache_program')
      if (cachedProgram) seasonData.value = JSON.parse(cachedProgram)

      const savedProgress = localStorage.getItem('pwa_progress')
      currentProgress.value = savedProgress ? JSON.parse(savedProgress) : { current_week_id: 1, current_session_id: 1 }

      const savedHistory = localStorage.getItem('pwa_history')
      sessionHistory.value = savedHistory ? JSON.parse(savedHistory) : []
    }
  }

  // On reçoit la distance en KM depuis la vue
  async function completeSession(distanceKm = 0, stepsCount = 0) {
    if (!seasonData.value || !currentProgress.value) return

    // Conversion en mètres pour la DB
    const distanceMeters = Math.round(distanceKm * 1000)

    const allSessions = seasonData.value.weeks.flatMap(w => w.sessions)
    const currentIndex = allSessions.findIndex(s => s.id === currentProgress.value.current_session_id)
    
    // Sauvegarde avec les noms de colonnes de la DB
    sessionHistory.value.push({ 
      session_id: currentProgress.value.current_session_id, 
      distance_meters: distanceMeters, 
      steps_count: stepsCount 
    })
    localStorage.setItem('pwa_history', JSON.stringify(sessionHistory.value))

    let nextSessionId = currentProgress.value.current_session_id
    if (currentIndex !== -1 && currentIndex + 1 < allSessions.length) {
      nextSessionId = allSessions[currentIndex + 1].id
    }

    currentProgress.value.current_session_id = nextSessionId
    localStorage.setItem('pwa_progress', JSON.stringify(currentProgress.value))

    const token = localStorage.getItem('auth_token')
    try {
      await fetch(`${API_BASE}/runner/log.php?token=${token}`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ 
          session_id: currentProgress.value.current_session_id, 
          next_session_id: nextSessionId, 
          distance_meters: distanceMeters, 
          steps_count: stepsCount 
        })
      })
    } catch (e) {
      addToSyncQueue({ 
        type: 'log', 
        session_id: currentProgress.value.current_session_id, 
        distance_meters: distanceMeters, 
        steps_count: stepsCount 
      })
    }

    await initApp()
  }

  async function executeReset(targetSessionId) {
    currentProgress.value.current_session_id = targetSessionId
    localStorage.setItem('pwa_progress', JSON.stringify(currentProgress.value))
    sessionHistory.value = sessionHistory.value.filter(h => h.session_id < targetSessionId)
    localStorage.setItem('pwa_history', JSON.stringify(sessionHistory.value))
    
    const token = localStorage.getItem('auth_token')
    try {
      await fetch(`${API_BASE}/runner/reset.php?token=${token}`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ target_session_id: targetSessionId })
      })
    } catch (e) {
      addToSyncQueue({ type: 'reset', target_session_id: targetSessionId })
    }

    initApp()
  }

  const deleteSession = (id) => executeReset(id)
  
  const resetToWeek = (weekId) => {
    if (!seasonData.value) return
    const targetWeek = seasonData.value.weeks.find(w => w.id === weekId)
    if (targetWeek && targetWeek.sessions.length > 0) executeReset(targetWeek.sessions[0].id)
  }

  const resetSeason = () => executeReset(1)

  function logout() {
    // Suppression des accès et de tous les caches hors-ligne
    localStorage.removeItem('auth_token')
    localStorage.removeItem('pwa_progress')
    localStorage.removeItem('pwa_history')
    localStorage.removeItem('pwa_cache_program')
    localStorage.removeItem('pwa_sync_queue')
    
    // Réinitialisation de l'état
    seasonData.value = null
    currentProgress.value = null
    sessionHistory.value = []
  }

  // N'oublie pas d'ajouter `logout` dans le return final :
  return { seasonData, currentProgress, currentSessionDetails, completedSessions, initApp, completeSession, deleteSession, resetToWeek, resetSeason, logout }
})