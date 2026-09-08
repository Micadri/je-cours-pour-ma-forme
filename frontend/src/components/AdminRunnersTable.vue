<script setup>
defineProps({
  runners: Array,
  isLoading: Boolean
})

const emit = defineEmits(['export', 'view-history'])

const formatDate = (dateString) => {
  if (!dateString) return 'Date inconnue'
  const safeDate = dateString.replace(' ', 'T')
  return new Date(safeDate).toLocaleDateString('fr-FR', { 
    day: '2-digit', month: '2-digit', year: 'numeric', 
    hour: '2-digit', minute:'2-digit' 
  })
}
</script>

<template>
  <div>
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
      <h2 style="margin: 0; color: #4CAF50;">Liste des inscrits ({{ runners.length }})</h2>
      <div style="display: flex; gap: 10px;">
        <button @click="emit('export', 'csv')" style="padding: 8px 15px; background: #2196F3; color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: bold;">📥 Export CSV</button>
        <button @click="emit('export', 'json')" style="padding: 8px 15px; background: #333; color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: bold;">📥 Export JSON</button>
      </div>
    </div>

    <div v-if="isLoading" style="text-align: center; padding: 40px; color: #888;">Chargement des données...</div>

    <div v-else style="overflow-x: auto; background: white; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.05);">
      <table style="width: 100%; border-collapse: collapse; text-align: left;">
        <thead>
          <tr style="background: #f4f4f4; color: #555;">
            <th style="padding: 15px; border-bottom: 2px solid #ddd;">Nom</th>
            <th style="padding: 15px; border-bottom: 2px solid #ddd;">Email</th>
            <th style="padding: 15px; border-bottom: 2px solid #ddd;">Inscription</th>
            <th style="padding: 15px; border-bottom: 2px solid #ddd;">Position (S. / Sem. / Entr.)</th>
            <th style="padding: 15px; border-bottom: 2px solid #ddd;">Km Parcourus</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="runner in runners" :key="runner.id" @click="emit('view-history', runner)" class="runner-row" style="border-bottom: 1px solid #eee; cursor: pointer;">
            <td style="padding: 15px; font-weight: bold; color: #333;">{{ runner.first_name }}</td>
            <td style="padding: 15px; color: #666;">{{ runner.email }}</td>
            <td style="padding: 15px; color: #888; font-size: 0.9rem;">{{ formatDate(runner.created_at) }}</td>
            <td style="padding: 15px;">
              <!-- Le badge passe au gris (#9e9e9e) si la distance est de 0 -->
              <span :style="{ background: runner.total_distance > 0 ? '#e38734' : '#9e9e9e', color: 'white', padding: '4px 8px', borderRadius: '12px', fontSize: '0.8rem', fontWeight: 'bold' }">
                {{ runner.current_season_id || 1 }} - {{ runner.current_week_id || 1 }} - {{ runner.current_session_id || 1 }}
              </span>
            </td>
            <td style="padding: 15px; color: #4CAF50; font-weight: bold;">{{ ((runner.total_distance || 0) / 1000).toFixed(2) }} km</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<style scoped>
.runner-row:hover {
  background-color: #f1f8e9 !important;
}
</style>