<script setup>
import { ref, computed } from 'vue'
import { useProgramStore } from '../stores/program'

const store = useProgramStore()
const expandedSessionId = ref(null)
const toggleSessionDetails = (id) => { expandedSessionId.value = expandedSessionId.value === id ? null : id }

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
    <h3 style="color: #333; margin-bottom: 15px; border-bottom: 2px solid #eee; padding-bottom: 5px;">Mes sessions</h3>
    
    <p v-if="store.completedSessions.length === 0" style="color: #888; text-align: center; font-style: italic;">
      Aucune course terminée pour le moment.
    </p>

    <ul v-else style="list-style: none; padding: 0; margin: 0;">
      <li v-for="session in paginatedSessions" :key="session.id" style="border: 1px solid #ddd; border-radius: 8px; margin-bottom: 10px; background: #fff; overflow: hidden;">
        <div @click="toggleSessionDetails(session.id)" style="display: flex; justify-content: space-between; align-items: center; padding: 12px 15px; cursor: pointer;">
          <div>
            <div style="font-weight: bold; color: #4CAF50; font-size: 0.9rem;">{{ session.weekTitle }}</div>
            <div style="color: #555;">{{ session.title }}</div>
          </div>
          <div style="display: flex; align-items: center; gap: 15px;">
            <span style="color: #aaa; font-size: 14px;">{{ expandedSessionId === session.id ? '▲' : '▼' }}</span>
            <button @click.stop="store.deleteSession(session.id)" style="background: none; border: none; font-size: 20px; cursor: pointer; color: #ff5252; padding: 0;" title="Annuler">✖</button>
          </div>
        </div>
        <div v-if="expandedSessionId === session.id" style="display: flex; justify-content: space-around; padding: 15px; background: #fafafa; border-top: 1px solid #eee;">
          <div style="text-align: center;"><div style="font-size: 20px; margin-bottom: 5px;">🏃</div><strong style="color: #333;">{{ session.distance }} km</strong></div>
          <div style="text-align: center;"><div style="font-size: 20px; margin-bottom: 5px;">👟</div><strong style="color: #333;">{{ session.steps }} pas</strong></div>
        </div>
      </li>
    </ul>

    <div v-if="totalPages > 1" style="display: flex; justify-content: space-between; align-items: center; margin-top: 15px;">
      <button @click="prevPage" :disabled="currentPage === 1" style="padding: 8px 12px; border: 1px solid #ccc; border-radius: 5px; background: #fff; cursor: pointer;" :style="{ opacity: currentPage === 1 ? 0.5 : 1 }">Précédent</button>
      <span style="font-size: 0.9rem; color: #666;">Page {{ currentPage }} sur {{ totalPages }}</span>
      <button @click="nextPage" :disabled="currentPage === totalPages" style="padding: 8px 12px; border: 1px solid #ccc; border-radius: 5px; background: #fff; cursor: pointer;" :style="{ opacity: currentPage === totalPages ? 0.5 : 1 }">Suivant</button>
    </div>
  </div>
</template>