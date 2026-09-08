<script setup>
import { ref, computed } from 'vue'
import { useProgramStore } from '../stores/program'

const store = useProgramStore()
const expandedUpcomingId = ref(null)

const upcomingSessions = computed(() => {
  if (!store.seasonData || !store.currentProgress) return []
  const currentId = Number(store.currentProgress.current_session_id)
  const upcoming = []

  for (const week of store.seasonData.weeks) {
    let sessionIndex = 1
    for (const session of week.sessions) {
      if (session.id > currentId) {
        const durationSeconds = session.exercises.reduce((acc, exo) => acc + parseInt(exo.duration_seconds), 0)
        upcoming.push({ ...session, weekTitle: week.title, sessionIndex, durationMin: Math.round(durationSeconds / 60) })
      }
      sessionIndex++
    }
  }
  return upcoming.slice(0, 3) 
})

const formatDuration = (seconds) => {
  const m = Math.floor(seconds / 60); const s = seconds % 60
  return s > 0 ? `${m}m ${s}s` : `${m} min`
}
</script>

<template>
  <div v-if="upcomingSessions.length > 0">
    <h3 class="font-heading text-xl font-bold mb-3 border-b-2 border-gray-200 pb-2 text-primary dark:text-gray-100 dark:border-gray-700">À venir...</h3>
    
    <div class="bg-surface rounded-xl border border-gray-200 overflow-hidden shadow-sm dark:bg-gray-800 dark:border-gray-700">
      <div v-for="(session, index) in upcomingSessions" :key="'up-' + session.id" class="border-b border-gray-100 last:border-none dark:border-gray-700">
        <div @click="expandedUpcomingId = expandedUpcomingId === session.id ? null : session.id" class="flex justify-between items-center p-4 cursor-pointer hover:bg-gray-50 transition-colors dark:hover:bg-gray-700/50 group">
          <div>
            <div class="font-bold text-primary font-heading tracking-wide text-lg dark:text-gray-200 group-hover:text-accent transition-colors">{{ session.weekTitle }}</div>
            <div class="text-gray-500 text-xs mt-1 dark:text-gray-400 font-medium">Session {{ session.sessionIndex }} • {{ session.durationMin }} min</div>
          </div>
          
          <!-- Badge Détails redessiné -->
          <div :class="expandedUpcomingId === session.id ? 'bg-primary text-accent' : 'bg-primary/10 text-primary'" class="text-[0.65rem] uppercase font-bold px-3 py-1.5 rounded-full flex items-center gap-1.5 transition-colors dark:bg-gray-700 dark:text-gray-300">
            Détails <span class="text-[0.5rem]">{{ expandedUpcomingId === session.id ? '▲' : '▼' }}</span>
          </div>
        </div>
        
        <div v-if="expandedUpcomingId === session.id" class="bg-gray-50 p-4 border-t border-gray-100 dark:bg-gray-900 dark:border-gray-700">
          <div v-for="(exo, i) in session.exercises" :key="i" class="flex justify-between py-1 border-b border-dashed border-gray-300 text-sm text-gray-600 last:border-0 dark:border-gray-700 dark:text-gray-400">
            <span class="capitalize">{{ exo.type }}</span>
            <span class="font-bold text-accent">{{ formatDuration(exo.duration_seconds) }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>