import { ref } from 'vue'
import { defineStore } from 'pinia'

export const useProgramStore = defineStore('program', () => {
  const seasonData = ref(null)
  const currentProgress = ref(null)

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

  return { seasonData, currentProgress, initApp }
})