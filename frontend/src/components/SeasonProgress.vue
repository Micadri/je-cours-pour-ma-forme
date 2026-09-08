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
  <div v-if="store.seasonData" class="px-1">
    <div class="flex justify-between items-center text-sm text-gray-600 mb-2 font-bold dark:text-gray-300">
      <select :value="store.seasonData.id" @change="handleSeasonChange" class="px-2 py-1 rounded-md border border-gray-300 font-bold bg-surface text-sm text-text max-w-[75%] focus:ring-2 focus:ring-accent outline-none dark:bg-gray-800 dark:border-gray-600 dark:text-gray-100">
        <option v-for="s in store.allSeasons" :key="s.id" :value="s.id">{{ s.title }}</option>
      </select>
      <span class="text-accent font-bold text-lg font-heading">{{ seasonProgressPercent }}%</span>
    </div>
    <div class="h-3 bg-gray-200 rounded-full overflow-hidden shadow-inner dark:bg-gray-700">
      <div :style="{ width: seasonProgressPercent + '%' }" class="h-full bg-accent transition-all duration-500 ease-in-out"></div>
    </div>
  </div>
</template>