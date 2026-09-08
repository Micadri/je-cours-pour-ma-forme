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
        upcoming.push({
          ...session,
          weekTitle: week.title,
          sessionIndex,
          durationMin: Math.round(durationSeconds / 60)
        })
      }
      sessionIndex++
    }
  }
  return upcoming.slice(0, 3) 
})

const toggleUpcoming = (id) => {
  expandedUpcomingId.value = expandedUpcomingId.value === id ? null : id
}

const formatDuration = (seconds) => {
  const m = Math.floor(seconds / 60)
  const s = seconds % 60
  return s > 0 ? `${m}min ${s}s` : `${m} min`
}
</script>

<template>
  <div v-if="upcomingSessions.length > 0">
    <h3 style="color: #333; margin-bottom: 10px; border-bottom: 2px solid #eee; padding-bottom: 5px;">À venir...</h3>
    
    <div style="background: #fff; border-radius: 12px; border: 1px solid #ddd; overflow: hidden; box-shadow: 0 2px 5px rgba(0,0,0,0.02);">
      <div v-for="(session, index) in upcomingSessions" :key="'up-' + session.id" :style="{ borderBottom: index < upcomingSessions.length - 1 ? '1px solid #eee' : 'none' }">
        <div @click="toggleUpcoming(session.id)" style="display: flex; justify-content: space-between; align-items: center; padding: 12px 15px; cursor: pointer;">
          <div>
            <div style="font-weight: bold; color: #e38734; font-size: 0.95rem;">{{ session.weekTitle }}</div>
            <div style="color: #666; font-size: 0.85rem; margin-top: 2px;">{{ session.sessionIndex === 1 ? '1ère' : session.sessionIndex + 'ème' }} session • {{ session.durationMin }} min</div>
          </div>
          <div style="font-size: 0.7rem; color: #888; text-transform: uppercase; font-weight: bold; background: #f4f4f4; padding: 4px 8px; border-radius: 12px; border: 1px solid #ddd; display: flex; align-items: center; gap: 4px;">
            Détails <span>{{ expandedUpcomingId === session.id ? '▲' : '▼' }}</span>
          </div>
        </div>
        
        <div v-if="expandedUpcomingId === session.id" style="background: #fafafa; padding: 12px 15px; border-top: 1px solid #eee;">
          <div v-for="(exo, i) in session.exercises" :key="i" style="display: flex; justify-content: space-between; padding: 4px 0; border-bottom: 1px dashed #ddd; font-size: 0.85rem; color: #555;">
            <span style="text-transform: capitalize;">{{ exo.type }}</span>
            <span style="font-weight: bold; color: #e38734;">{{ formatDuration(exo.duration_seconds) }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>