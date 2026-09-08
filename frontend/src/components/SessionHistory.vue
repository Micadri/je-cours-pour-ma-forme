<script setup>
import { ref, computed } from 'vue'
import { useProgramStore } from '../stores/program'
import RouteMap from './RouteMap.vue' // Import de la carte

const store = useProgramStore()
const expandedSessionId = ref(null)
const currentPage = ref(1)
const itemsPerPage = 3

const totalPages = computed(() => Math.ceil(store.completedSessions.length / itemsPerPage))
const paginatedSessions = computed(() => store.completedSessions.slice((currentPage.value - 1) * itemsPerPage, currentPage.value * itemsPerPage))

const prevPage = () => { if (currentPage.value > 1) currentPage.value-- }
const nextPage = () => { if (currentPage.value < totalPages.value) currentPage.value++ }

const getSessionIndex = (sessionId) => {
  if (!store.seasonData) return 1
  for (const week of store.seasonData.weeks) {
    const index = week.sessions.findIndex(s => s.id === sessionId)
    if (index !== -1) return index + 1
  }
  return 1
}

const getPace = (distance, duration) => {
  if (!distance || !duration || distance <= 0) return "-:--"
  const paceSec = Math.round(duration / parseFloat(distance))
  return `${Math.floor(paceSec / 60)}:${(paceSec % 60).toString().padStart(2, '0')}`
}
</script>

<template>
  <div>
    <p v-if="store.completedSessions.length === 0" class="text-gray-500 text-center italic mt-2 dark:text-gray-400">Aucune course terminée pour le moment.</p>
    
    <div v-else>
      <div class="bg-surface rounded-xl border border-gray-200 overflow-hidden shadow-sm dark:bg-gray-800 dark:border-gray-700">
        <div v-for="session in paginatedSessions" :key="session.id" class="border-b border-gray-100 last:border-none dark:border-gray-700">
          
          <div @click="expandedSessionId = expandedSessionId === session.id ? null : session.id" class="flex justify-between items-center p-4 cursor-pointer hover:bg-gray-50 transition-colors dark:hover:bg-gray-700/50">
            <div>
              <div class="font-bold text-primary font-heading tracking-wide dark:text-gray-200">{{ session.weekTitle }}</div>
              <div class="text-gray-500 text-xs mt-1 dark:text-gray-400 font-medium flex gap-2">
                <span>Session {{ getSessionIndex(session.id) }}</span>
                <span v-if="session.weather" class="text-accent font-bold">🌤️ {{ session.weather }}°C</span>
              </div>
            </div>
            <div class="flex items-center gap-4">
              <span class="text-gray-400 text-xs font-bold">{{ expandedSessionId === session.id ? '▲' : '▼' }}</span>
              <button @click.stop="store.deleteSession(session.id)" class="text-red-500 hover:bg-red-50 p-1.5 rounded-md transition-colors dark:hover:bg-red-900/30" title="Annuler">🗑️</button>
            </div>
          </div>

          <div v-if="expandedSessionId === session.id" class="p-4 bg-gray-50 border-t border-gray-100 dark:bg-gray-900 dark:border-gray-700">
            <div class="grid grid-cols-3 gap-2 mb-4">
              <div class="text-center">
                <strong class="text-primary font-heading text-lg dark:text-gray-100">{{ session.distance }} <span class="text-xs">km</span></strong>
                <div class="text-[0.6rem] text-gray-400 uppercase font-bold">Distance</div>
              </div>
              <div class="text-center border-l border-r border-gray-200 dark:border-gray-700">
                <strong class="text-primary font-heading text-lg dark:text-gray-100">{{ getPace(session.distance, session.duration) }}</strong>
                <div class="text-[0.6rem] text-gray-400 uppercase font-bold">Min/Km</div>
              </div>
              <div class="text-center">
                <strong class="text-primary font-heading text-lg dark:text-gray-100">+{{ session.elevation || 0 }}m</strong>
                <div class="text-[0.6rem] text-gray-400 uppercase font-bold">Dénivelé</div>
              </div>
            </div>
            
            <RouteMap v-if="session.route && session.route !== '[]'" :routeString="session.route" />
          </div>
        </div>
      </div>

      <div v-if="totalPages > 1" class="flex justify-between items-center mt-4 px-2">
        <button @click="prevPage" :disabled="currentPage === 1" class="text-primary font-bold text-sm hover:opacity-80 disabled:opacity-30 transition-opacity">Précédent</button>
        <span class="text-xs text-gray-500 font-bold">{{ currentPage }} / {{ totalPages }}</span>
        <button @click="nextPage" :disabled="currentPage === totalPages" class="text-primary font-bold text-sm hover:opacity-80 disabled:opacity-30 transition-opacity">Suivant</button>
      </div>
    </div>
  </div>
</template>