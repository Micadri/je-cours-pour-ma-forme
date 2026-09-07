<script setup>
import { ref, computed } from 'vue'
import { useProgramStore } from '../stores/program'

const store = useProgramStore()
const expandedSessionId = ref(null)

const toggleSessionDetails = (id) => {
  expandedSessionId.value = expandedSessionId.value === id ? null : id
}

const currentPage = ref(1)
const itemsPerPage = 3
const totalPages = computed(() => Math.ceil(store.completedSessions.length / itemsPerPage))

const paginatedSessions = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage
  return store.completedSessions.slice(start, start + itemsPerPage)
})

const nextPage = () => { if (currentPage.value < totalPages.value) currentPage.value++ }
const prevPage = () => { if (currentPage.value > 1) currentPage.value-- }
</script>

<template>
  <div style="margin-bottom: 25px;">
    <h3 style="color: #333; margin-bottom: 10px; border-bottom: 2px solid #eee; padding-bottom: 5px;">Mes sessions</h3>
    
    <p v-if="store.completedSessions.length === 0" style="color: #888; text-align: center; font-style: italic; margin-top: 15px;">
      Aucune course terminée pour le moment.
    </p>
    
    <div v-else>
      <!-- Conteneur unique fusionné -->
      <div style="background: #fff; border-radius: 12px; border: 1px solid #ddd; overflow: hidden; box-shadow: 0 2px 5px rgba(0,0,0,0.02);">
        <div v-for="(session, index) in paginatedSessions" :key="session.id" 
             :style="{ borderBottom: index < paginatedSessions.length - 1 ? '1px solid #eee' : 'none' }">
          
          <div @click="toggleSessionDetails(session.id)" style="display: flex; justify-content: space-between; align-items: center; padding: 12px 15px; cursor: pointer;">
            <div>
              <div style="font-weight: bold; color: #4CAF50; font-size: 0.9rem;">{{ session.weekTitle }}</div>
              <div style="color: #555; font-size: 0.85rem; margin-top: 2px;">{{ session.title }}</div>
            </div>
            <div style="display: flex; align-items: center; gap: 15px;">
              <span style="color: #aaa; font-size: 12px; font-weight: bold;">{{ expandedSessionId === session.id ? '▲' : '▼' }}</span>
              <button @click.stop="store.deleteSession(session.id)" style="background: none; border: none; font-size: 16px; cursor: pointer; color: #ff5252; padding: 0;" title="Annuler">✖</button>
            </div>
          </div>

          <div v-if="expandedSessionId === session.id" style="display: flex; justify-content: space-around; padding: 12px 15px; background: #fafafa; border-top: 1px solid #eee;">
            <div style="text-align: center;">
              <strong style="color: #333; font-size: 1rem;">{{ session.distance }} km</strong>
              <div style="font-size: 0.75rem; color: #888; text-transform: uppercase;">Distance</div>
            </div>
            <div style="text-align: center;">
              <strong style="color: #333; font-size: 1rem;">{{ session.steps }}</strong>
              <div style="font-size: 0.75rem; color: #888; text-transform: uppercase;">Pas</div>
            </div>
          </div>

        </div>
      </div>

      <!-- Pagination Textuelle Allégée -->
      <div v-if="totalPages > 1" style="display: flex; justify-content: space-between; align-items: center; margin-top: 12px; padding: 0 5px;">
        <button @click="prevPage" :disabled="currentPage === 1" style="background: none; border: none; color: #4CAF50; font-weight: bold; font-size: 0.9rem; cursor: pointer; padding: 5px 0;" :style="{ opacity: currentPage === 1 ? 0.3 : 1 }">
          ← Précédent
        </button>
        <span style="font-size: 0.85rem; color: #666;">Page {{ currentPage }} / {{ totalPages }}</span>
        <button @click="nextPage" :disabled="currentPage === totalPages" style="background: none; border: none; color: #4CAF50; font-weight: bold; font-size: 0.9rem; cursor: pointer; padding: 5px 0;" :style="{ opacity: currentPage === totalPages ? 0.3 : 1 }">
          Suivant →
        </button>
      </div>
    </div>
  </div>
</template>