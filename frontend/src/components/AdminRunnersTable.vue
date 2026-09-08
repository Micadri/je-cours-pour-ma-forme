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
    <div class="flex justify-between items-center mb-4">
      <h2 class="m-0 font-heading text-2xl text-primary font-bold dark:text-gray-100">Liste des inscrits ({{ runners.length }})</h2>
      <div class="flex gap-2">
        <button @click="emit('export', 'csv')" class="bg-blue-500 text-white px-4 py-2 rounded-cta font-bold hover:bg-blue-600 transition-colors text-sm">📥 Export CSV</button>
        <button @click="emit('export', 'json')" class="bg-gray-800 text-white px-4 py-2 rounded-cta font-bold hover:bg-gray-900 transition-colors text-sm dark:bg-gray-600">📥 Export JSON</button>
      </div>
    </div>

    <div v-if="isLoading" class="text-center p-10 text-gray-500 dark:text-gray-400">Chargement des données...</div>

    <div v-else class="overflow-x-auto bg-surface rounded-xl shadow-sm border border-gray-200 dark:bg-gray-800 dark:border-gray-700">
      <table class="w-full border-collapse text-left">
        <thead>
          <tr class="bg-gray-50 text-gray-500 font-heading uppercase text-sm dark:bg-gray-900 dark:text-gray-400">
            <th class="p-4 border-b-2 border-gray-200 dark:border-gray-700">Nom</th>
            <th class="p-4 border-b-2 border-gray-200 dark:border-gray-700">Email</th>
            <th class="p-4 border-b-2 border-gray-200 dark:border-gray-700">Inscription</th>
            <th class="p-4 border-b-2 border-gray-200 dark:border-gray-700">Position (S. / Sem. / Entr.)</th>
            <th class="p-4 border-b-2 border-gray-200 dark:border-gray-700">Km Parcourus</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="runner in runners" :key="runner.id" @click="emit('view-history', runner)" class="border-b border-gray-100 cursor-pointer hover:bg-gray-50 transition-colors dark:border-gray-700 dark:hover:bg-gray-700/50">
            <td class="p-4 font-bold text-text dark:text-gray-200">{{ runner.first_name }}</td>
            <td class="p-4 text-gray-600 dark:text-gray-400">{{ runner.email }}</td>
            <td class="p-4 text-gray-500 text-sm dark:text-gray-400">{{ formatDate(runner.created_at) }}</td>
            <td class="p-4">
              <span :class="runner.total_distance > 0 ? 'bg-primary text-accent' : 'bg-gray-400 text-white dark:bg-gray-600'" class="px-2.5 py-1 rounded-full text-xs font-bold font-heading tracking-wide">
                {{ runner.current_season_id || 1 }} - {{ runner.current_week_id || 1 }} - {{ runner.current_session_id || 1 }}
              </span>
            </td>
            <td class="p-4 text-accent font-bold text-lg font-heading">{{ ((runner.total_distance || 0) / 1000).toFixed(2) }} km</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>