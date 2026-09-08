<script setup>
import { ref, computed } from 'vue'

const props = defineProps({ runner: Object, history: Array, isLoading: Boolean })
const emit = defineEmits(['close'])

const filterSeason = ref(''); const filterWeek = ref(''); const filterSession = ref('')

const availableSeasons = computed(() => [...new Set(props.history.map(h => h.season_title))])
const availableWeeks = computed(() => [...new Set(props.history.filter(h => !filterSeason.value || h.season_title === filterSeason.value).map(h => h.week_title))])
const availableSessions = computed(() => [...new Set(props.history.filter(h => (!filterSeason.value || h.season_title === filterSeason.value) && (!filterWeek.value || h.week_title === filterWeek.value)).map(h => h.session_index))].sort((a, b) => a - b))
const filteredHistory = computed(() => props.history.filter(log => (!filterSeason.value || log.season_title === filterSeason.value) && (!filterWeek.value || log.week_title === filterWeek.value) && (!filterSession.value || log.session_index === filterSession.value)))

const formatDate = (dateString) => {
  if (!dateString) return 'Date inconnue'
  return new Date(dateString.replace(' ', 'T')).toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute:'2-digit' })
}
</script>

<template>
  <div class="fixed inset-0 bg-black/60 flex items-center justify-center z-[1000] p-5 backdrop-blur-sm">
    <div class="bg-surface p-6 rounded-xl w-full max-w-[800px] max-h-[85vh] overflow-y-auto relative shadow-2xl dark:bg-gray-800 border border-gray-100 dark:border-gray-700">
      <button @click="emit('close')" class="absolute top-4 right-5 bg-transparent border-none text-2xl cursor-pointer text-gray-400 hover:text-gray-800 transition-colors dark:hover:text-gray-200">✖</button>
      <h2 class="mt-0 font-heading text-2xl text-text font-bold mb-5 dark:text-gray-100">Historique de <span class="text-primary dark:text-accent">{{ runner.first_name }}</span></h2>
      
      <div class="flex gap-2.5 mb-5 flex-wrap">
        <select v-model="filterSeason" @change="filterWeek = ''; filterSession = ''" class="p-2 rounded-md border border-gray-300 flex-1 min-w-[150px] bg-surface focus:ring-2 focus:ring-accent outline-none dark:bg-gray-900 dark:border-gray-600 dark:text-gray-100 text-sm">
          <option value="">Toutes les Saisons</option><option v-for="s in availableSeasons" :key="s" :value="s">{{ s }}</option>
        </select>
        <select v-model="filterWeek" @change="filterSession = ''" class="p-2 rounded-md border border-gray-300 flex-1 min-w-[150px] bg-surface focus:ring-2 focus:ring-accent outline-none dark:bg-gray-900 dark:border-gray-600 dark:text-gray-100 text-sm">
          <option value="">Toutes les Semaines</option><option v-for="w in availableWeeks" :key="w" :value="w">{{ w }}</option>
        </select>
        <select v-model="filterSession" class="p-2 rounded-md border border-gray-300 flex-1 min-w-[150px] bg-surface focus:ring-2 focus:ring-accent outline-none dark:bg-gray-900 dark:border-gray-600 dark:text-gray-100 text-sm">
          <option value="">Tous les Entraînements</option><option v-for="idx in availableSessions" :key="idx" :value="idx">{{ idx === 1 ? '1ère' : idx + 'ème' }} session</option>
        </select>
      </div>

      <div v-if="isLoading" class="text-center p-10 text-gray-500 dark:text-gray-400">Chargement de l'historique...</div>
      <div v-else-if="filteredHistory.length === 0" class="text-center p-10 text-gray-500 italic bg-gray-50 rounded-lg dark:bg-gray-900 dark:text-gray-400">Aucune session trouvée.</div>
      
      <table v-else class="w-full border-collapse text-left text-sm rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700">
        <thead>
          <tr class="bg-gray-100 text-gray-500 font-heading uppercase text-xs tracking-wide dark:bg-gray-900 dark:text-gray-400">
            <th class="p-3 border-b border-gray-200 dark:border-gray-700 font-medium">Date</th>
            <th class="p-3 border-b border-gray-200 dark:border-gray-700 font-medium">Saison</th>
            <th class="p-3 border-b border-gray-200 dark:border-gray-700 font-medium">Semaine</th>
            <th class="p-3 border-b border-gray-200 dark:border-gray-700 font-medium">Entraînement</th>
            <th class="p-3 border-b border-gray-200 dark:border-gray-700 font-medium text-right">Distance</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(log, i) in filteredHistory" :key="i" class="border-b border-gray-100 last:border-0 hover:bg-gray-50 transition-colors dark:border-gray-700 dark:hover:bg-gray-800">
            <td class="p-3 text-gray-600 dark:text-gray-400 whitespace-nowrap">{{ formatDate(log.completed_at || log.created_at) }}</td>
            <td class="p-3 font-bold text-text dark:text-gray-200">{{ log.season_title }}</td>
            <td class="p-3 text-gray-500 dark:text-gray-400">{{ log.week_title }}</td>
            <td class="p-3 text-primary font-bold dark:text-gray-200">{{ log.session_index === 1 ? '1ère' : log.session_index + 'ème' }} session</td>
            <td class="p-3 font-bold text-accent text-right whitespace-nowrap">{{ (log.distance_meters / 1000).toFixed(2) }} km</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>