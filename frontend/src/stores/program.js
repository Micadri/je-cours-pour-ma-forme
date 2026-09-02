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
    try {
      const res = await fetch('/program_5k.json')
      const rawData = await res.json()

      const sourceProgram = Array.isArray(rawData) ? rawData[0] : rawData

      const formattedData = {
        title: sourceProgram.label || "Programme d'entraînement",
        weeks: []
      }

      const weeksMap = {}
      let sessionIdCounter = 1

      sourceProgram.etapes.forEach(etape => {
        const match = etape.label.match(/Semaine\s*(\d+)/i)
        const weekId = match ? parseInt(match[1]) : 1

        if (!weeksMap[weekId]) {
          weeksMap[weekId] = { id: weekId, title: `Semaine ${weekId}`, sessions: [] }
        }

        const mappedExercises = etape.steps.map(step => ({
          type: step.type,
          duration_seconds: Math.round(step.time * 60)
        }))

        weeksMap[weekId].sessions.push({
          id: sessionIdCounter++,
          title: etape.label,
          exercises: mappedExercises
        })
      })

      formattedData.weeks = Object.values(weeksMap)
      seasonData.value = formattedData

    } catch (e) {
      console.error("Erreur chargement JSON", e)
    }

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
  
  const resetToWeek = (weekId) => {
    if (!seasonData.value) return
    const targetWeek = seasonData.value.weeks.find(w => w.id === weekId)
    if (targetWeek && targetWeek.sessions.length > 0) {
      executeReset(targetWeek.sessions[0].id)
    }
  }

  const resetSeason = () => executeReset(1)

  // On s'assure que tout est bien exporté ici
  return { seasonData, currentProgress, currentSessionDetails, completedSessions, initApp, completeSession, deleteSession, resetToWeek, resetSeason }
})