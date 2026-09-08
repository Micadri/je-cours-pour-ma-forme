<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
  runner: Object,
  history: Array,
  isLoading: Boolean
})

const emit = defineEmits(['close'])

const filterSeason = ref('')
const filterWeek = ref('')
const filterSession = ref('')

const availableSeasons = computed(() => [...new Set(props.history.map(h => h.season_title))])
const availableWeeks = computed(() => [...new Set(props.history.filter(h => !filterSeason.value || h.season_title === filterSeason.value).map(h => h.week_title))])
const availableSessions = computed(() => [...new Set(props.history.filter(h => (!filterSeason.value || h.season_title === filterSeason.value) && (!filterWeek.value || h.week_title === filterWeek.value)).map(h => h.session_index))].sort((a, b) => a - b))

const filteredHistory = computed(() => {
  return props.history.filter(log => {
    return (!filterSeason.value || log.season_title === filterSeason.value) &&
           (!filterWeek.value || log.week_title === filterWeek.value) &&
           (!filterSession.value || log.session_index === filterSession.value)
  })
})

const formatDate = (dateString) => {
  if (!dateString) return 'Date inconnue'
  return new Date(dateString.replace(' ', 'T')).toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute:'2-digit' })
}
</script>

<template>
  <div style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.6); display: flex; align-items: center; justify-content: center; z-index: 1000; padding: 20px;">
    <div style="background: white; padding: 25px; border-radius: 12px; width: 100%; max-width: 800px; max-height: 85vh; overflow-y: auto; position: relative;">
      <button @click="emit('close')" style="position: absolute; top: 15px; right: 20px; background: none; border: none; font-size: 24px; cursor: pointer; color: #888;">✖</button>
      <h2 style="margin-top: 0; color: #333;">Historique de <span style="color: #4CAF50;">{{ runner.first_name }}</span></h2>
      
      <div style="display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap;">
        <select v-model="filterSeason" @change="filterWeek = ''; filterSession = ''" style="padding: 8px; border-radius: 5px; border: 1px solid #ccc; flex: 1; min-width: 150px;">
          <option value="">Toutes les Saisons</option>
          <option v-for="s in availableSeasons" :key="s" :value="s">{{ s }}</option>
        </select>
        <select v-model="filterWeek" @change="filterSession = ''" style="padding: 8px; border-radius: 5px; border: 1px solid #ccc; flex: 1; min-width: 150px;">
          <option value="">Toutes les Semaines</option>
          <option v-for="w in availableWeeks" :key="w" :value="w">{{ w }}</option>
        </select>
        <select v-model="filterSession" style="padding: 8px; border-radius: 5px; border: 1px solid #ccc; flex: 1; min-width: 150px;">
          <option value="">Tous les Entraînements</option>
          <option v-for="idx in availableSessions" :key="idx" :value="idx">{{ idx === 1 ? '1ère' : idx + 'ème' }} session</option>
        </select>
      </div>

      <div v-if="isLoading" style="text-align: center; padding: 20px; color: #888;">Chargement de l'historique...</div>
      <div v-else-if="filteredHistory.length === 0" style="text-align: center; padding: 20px; color: #888; font-style: italic;">Aucune session trouvée.</div>
      
      <table v-else style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.9rem;">
        <thead>
          <tr style="background: #f4f4f4; color: #555;">
            <th style="padding: 10px; border-bottom: 2px solid #ddd;">Date</th>
            <th style="padding: 10px; border-bottom: 2px solid #ddd;">Saison</th>
            <th style="padding: 10px; border-bottom: 2px solid #ddd;">Semaine</th>
            <th style="padding: 10px; border-bottom: 2px solid #ddd;">Entraînement</th>
            <th style="padding: 10px; border-bottom: 2px solid #ddd; text-align: right;">Distance</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(log, i) in filteredHistory" :key="i" style="border-bottom: 1px solid #eee;">
            <td style="padding: 10px; color: #666;">{{ formatDate(log.completed_at || log.created_at) }}</td>
            <td style="padding: 10px; font-weight: bold; color: #333;">{{ log.season_title }}</td>
            <td style="padding: 10px; color: #555;">{{ log.week_title }}</td>
            <td style="padding: 10px; color: #e38734; font-weight: bold;">{{ log.session_index === 1 ? '1ère' : log.session_index + 'ème' }} session</td>
            <td style="padding: 10px; font-weight: bold; color: #4CAF50; text-align: right;">{{ (log.distance_meters / 1000).toFixed(2) }} km</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>