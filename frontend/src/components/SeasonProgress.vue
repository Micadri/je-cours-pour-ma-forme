<script setup>
import { computed } from 'vue'
import { useProgramStore } from '../stores/program'

const store = useProgramStore()

const totalSessionsInSeason = computed(() => {
  if (!store.seasonData) return 0
  return store.seasonData.weeks.reduce((acc, week) => acc + week.sessions.length, 0)
})

const seasonProgressPercent = computed(() => {
  if (totalSessionsInSeason.value === 0) return 0
  return Math.round((store.completedSessions.length / totalSessionsInSeason.value) * 100)
})

const handleSeasonChange = async (event) => {
  const newSeasonId = event.target.value
  if (confirm("Changer de saison réinitialisera votre progression affichée. Continuer ?")) {
    await store.changeSeason(newSeasonId)
  } else {
    event.target.value = store.seasonData.id
  }
}
</script>

<template>
  <div v-if="store.seasonData" style="padding: 0 5px;">
    <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.95rem; color: #555; margin-bottom: 8px; font-weight: bold;">
      <select :value="store.seasonData.id" @change="handleSeasonChange" style="padding: 4px 8px; border-radius: 5px; border: 1px solid #ccc; font-weight: bold; background: #fff; font-size: 0.9rem; color: #333; max-width: 75%;">
        <option v-for="s in store.allSeasons" :key="s.id" :value="s.id">{{ s.title }}</option>
      </select>
      <span style="color: #4CAF50;">{{ seasonProgressPercent }}%</span>
    </div>
    <div style="height: 12px; background: #e0e0e0; border-radius: 6px; overflow: hidden; box-shadow: inset 0 1px 3px rgba(0,0,0,0.1);">
      <div :style="{ width: seasonProgressPercent + '%', height: '100%', background: '#4CAF50', transition: 'width 0.5s ease-in-out' }"></div>
    </div>
  </div>
</template>