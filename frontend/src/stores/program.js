import { ref } from 'vue'
import { defineStore } from 'pinia'

export const useProgramStore = defineStore('program', () => {
  const seasonData = ref(null)
  const currentProgress = ref(null)
  
  // Ton token validé
  const token = 'a27eb1adeb239f755e9a3bef07e814af292fd66490a4b30530c0f994d413780e'

  async function initApp() {
    try {
      // 1. Charger l'arborescence du programme
      const progRes = await fetch(`http://localhost:8888/je-cours-pour-ma-forme/api/program/full.php?token=${token}`)
      const progJson = await progRes.json()
      if (progJson.status === 'success') seasonData.value = progJson.data

      // 2. Charger la progression de l'utilisateur
      const progressRes = await fetch(`http://localhost:8888/je-cours-pour-ma-forme/api/runner/progress.php?token=${token}`)
      const progressJson = await progressRes.json()
      if (progressJson.status === 'success') currentProgress.value = progressJson.data
    } catch (error) {
      console.error("Erreur de synchronisation API :", error)
    }
  }

  return { seasonData, currentProgress, initApp }
})