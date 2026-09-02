import { ref, computed } from 'vue'
import { defineStore } from 'pinia'

export const useProgramStore = defineStore('program', () => {
  const seasonData = ref(null)
  const currentProgress = ref(null)

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
          completed.push({ ...session, weekTitle: week.title })
        }
      }
    }
    return completed.reverse()
  })

  async function initApp() {
    // 1. Charger le JSON statique
    try {
      const res = await fetch('/program.json')
      seasonData.value = await res.json()
    } catch (e) {
      console.error("Erreur chargement JSON", e)
    }

    // 2. Charger la progression depuis le LocalStorage (ou initialiser à 1)
    const savedProgress = localStorage.getItem('pwa_progress')
    if (savedProgress) {
      currentProgress.value = JSON.parse(savedProgress)
    } else {
      currentProgress.value = { current_week_id: 1, current_session_id: 1 }
    }
  }

  async function completeSession() {
    if (!seasonData.value || !currentProgress.value) return

    const allSessions = seasonData.value.weeks.flatMap(w => w.sessions)
    const currentIndex = allSessions.findIndex(s => s.id === currentProgress.value.current_session_id)
    
    let nextSessionId = currentProgress.value.current_session_id
    if (currentIndex !== -1 && currentIndex + 1 < allSessions.length) {
      nextSessionId = allSessions[currentIndex + 1].id
    }

    // Sauvegarde en LocalStorage au lieu de la BDD
    currentProgress.value.current_session_id = nextSessionId
    localStorage.setItem('pwa_progress', JSON.stringify(currentProgress.value))
    await initApp()
  }

  function executeReset(targetSessionId) {
    currentProgress.value.current_session_id = targetSessionId
    localStorage.setItem('pwa_progress', JSON.stringify(currentProgress.value))
    initApp()
  }

  const deleteSession = (id) => executeReset(id)
  const resetWeek = () => {
    if (currentSessionDetails.value) executeReset(currentSessionDetails.value.week.sessions[0].id)
  }
  const resetSeason = () => executeReset(1)

  return { seasonData, currentProgress, currentSessionDetails, completedSessions, initApp, completeSession, deleteSession, resetWeek, resetSeason }
})