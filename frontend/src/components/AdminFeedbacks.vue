<script setup>
defineProps({ feedbacks: Array, isLoading: Boolean })
const emit = defineEmits(['refresh', 'delete', 'accept'])

const formatDate = (dateString) => {
  if (!dateString) return 'Date inconnue'
  const safeDate = dateString.replace(' ', 'T')
  return new Date(safeDate).toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute:'2-digit' })
}
</script>

<template>
  <div>
    <div class="flex justify-between items-center mb-4">
      <h2 class="m-0 font-heading text-2xl text-primary font-bold dark:text-gray-100">Retours utilisateurs ({{ feedbacks.length }})</h2>
      <button @click="emit('refresh')" class="bg-gray-200 text-gray-800 px-4 py-2 rounded-cta font-bold hover:bg-gray-300 transition-colors text-sm dark:bg-gray-700 dark:text-gray-200">🔄 Rafraîchir</button>
    </div>
    
    <div v-if="isLoading" class="text-center p-10 text-gray-500 dark:text-gray-400">Chargement des signalements...</div>
    <div v-else-if="feedbacks.length === 0" class="text-center p-10 text-gray-500 italic bg-surface rounded-xl dark:bg-gray-800 dark:text-gray-400">Aucun signalement pour le moment.</div>
    
    <div v-else class="flex flex-col gap-4">
      <div v-for="fb in feedbacks" :key="fb.id" 
           class="bg-surface rounded-xl p-5 shadow-sm border border-gray-200 transition-opacity border-l-4 dark:bg-gray-800 dark:border-gray-700"
           :class="[ fb.status === 'accepted' ? 'opacity-60' : 'opacity-100', fb.subject === 'bug' ? 'border-l-red-500' : fb.subject === 'idea' ? 'border-l-blue-500' : 'border-l-gray-400' ]">
        
        <div class="flex justify-between mb-3 items-start">
          <div>
            <h3 class="m-0 mb-1 text-text font-heading text-lg font-bold flex items-center gap-2 dark:text-gray-100">
              {{ fb.subject === 'bug' ? '🐛 Bug rapporté' : fb.subject === 'idea' ? '💡 Idée proposée' : '✉️ Autre message' }}
              <span v-if="fb.status === 'accepted'" class="text-[0.65rem] uppercase tracking-wide bg-green-50 text-green-600 px-2 py-0.5 rounded-full border border-green-200 dark:bg-green-900/30 dark:border-green-800 dark:text-green-400">✔ Traité</span>
            </h3>
            <div class="text-sm text-gray-600 dark:text-gray-400">De <strong class="font-bold text-text dark:text-gray-200">{{ fb.first_name || 'Inconnu' }}</strong> ({{ fb.email }})</div>
          </div>
          
          <div class="flex flex-col items-end gap-2">
            <div class="text-xs text-gray-400 text-right font-medium">{{ formatDate(fb.created_at) }}</div>
            <div class="flex gap-2">
              <button v-if="fb.status !== 'accepted'" @click="emit('accept', fb.id)" class="bg-green-50 border border-green-200 text-green-600 px-3 py-1.5 rounded-cta cursor-pointer text-xs font-bold uppercase tracking-wide transition-colors hover:bg-green-100 dark:bg-green-900/30 dark:border-green-800 dark:text-green-400 dark:hover:bg-green-900/50">Accepter</button>
              <button @click="emit('delete', fb.id)" class="bg-red-50 border border-red-200 text-red-600 px-3 py-1.5 rounded-cta cursor-pointer text-xs font-bold uppercase tracking-wide transition-colors hover:bg-red-100 dark:bg-red-900/30 dark:border-red-800 dark:text-red-400 dark:hover:bg-red-900/50">Supprimer</button>
            </div>
          </div>
        </div>
        
        <p class="m-0 text-gray-700 text-sm leading-relaxed bg-gray-50 p-4 rounded-lg border border-gray-100 whitespace-pre-wrap dark:bg-gray-900 dark:border-gray-700 dark:text-gray-300">{{ fb.message }}</p>
      </div>
    </div>
  </div>
</template>