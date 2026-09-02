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
    const token = localStorage.getItem('auth_token')
    if (!token) return
    try {
      const progRes = await fetch(`http://localhost:8888/je-cours-pour-ma-forme/api/program/full.php?token=${token}`)
      const progJson = await progRes.json()
      if (progJson.status === 'success') seasonData.value = progJson.data

      const progressRes = await fetch(`http://localhost:8888/je-cours-pour-ma-forme/api/runner/progress.php?token=${token}`)
      const progressJson = await progressRes.json()
      if (progressJson.status === 'success') currentProgress.value = progressJson.data
    } catch (error) {
      console.error("Erreur de synchronisation", error)
    }
  }

  async function completeSession(distance = 0) {
    const token = localStorage.getItem('auth_token')
    if (!token || !seasonData.value || !currentProgress.value) return

    const allSessions = seasonData.value.weeks.flatMap(w => w.sessions)
    const currentIndex = allSessions.findIndex(s => s.id === currentProgress.value.current_session_id)
    
    let nextSessionId = currentProgress.value.current_session_id
    if (currentIndex !== -1 && currentIndex + 1 < allSessions.length) {
      nextSessionId = allSessions[currentIndex + 1].id
    }

    try {
      const res = await fetch(`http://localhost:8888/je-cours-pour-ma-forme/api/runner/log.php?token=${token}`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ session_id: currentProgress.value.current_session_id, next_session_id: nextSessionId, distance: distance })
      })
      const data = await res.json()
      if (data.status === 'success') await initApp()
    } catch (error) {
      console.error("Erreur", error)
    }
  }

  // Suppression des messages d'alerte dans l'exécution
  async function executeReset(targetSessionId) {
    const token = localStorage.getItem('auth_token')
    try {
      const res = await fetch(`http://localhost:8888/je-cours-pour-ma-forme/api/runner/reset.php?token=${token}`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ target_session_id: targetSessionId })
      })
      const data = await res.json()
      if (data.status === 'success') await initApp()
    } catch (error) {
      console.error("Erreur de reset", error)
    }
  }

  const deleteSession = (id) => executeReset(id)
  
  const resetWeek = () => {
    if (!currentSessionDetails.value) return
    executeReset(currentSessionDetails.value.week.sessions[0].id)
  }
  
  const resetSeason = () => executeReset(1)

  return { seasonData, currentProgress, currentSessionDetails, completedSessions, initApp, completeSession, deleteSession, resetWeek, resetSeason }
})