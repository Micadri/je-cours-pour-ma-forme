<script setup>
defineProps({
  feedbacks: Array,
  isLoading: Boolean
})

const emit = defineEmits(['refresh', 'delete', 'accept'])

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
      <h2 style="margin: 0; color: #4CAF50;">Retours utilisateurs ({{ feedbacks.length }})</h2>
      <button @click="emit('refresh')" style="padding: 8px 15px; background: #e0e0e0; color: #333; border: none; border-radius: 5px; cursor: pointer; font-weight: bold;">🔄 Rafraîchir</button>
    </div>
    
    <div v-if="isLoading" style="text-align: center; padding: 40px; color: #888;">Chargement des signalements...</div>
    <div v-else-if="feedbacks.length === 0" style="text-align: center; padding: 40px; color: #888; font-style: italic; background: white; border-radius: 12px;">Aucun signalement pour le moment.</div>
    
    <div v-else style="display: flex; flex-direction: column; gap: 15px;">
      <div v-for="fb in feedbacks" :key="fb.id" :style="{ background: 'white', borderRadius: '12px', padding: '20px', boxShadow: '0 2px 8px rgba(0,0,0,0.05)', borderLeft: fb.subject === 'bug' ? '5px solid #f44336' : fb.subject === 'idea' ? '5px solid #2196F3' : '5px solid #9e9e9e', opacity: fb.status === 'accepted' ? 0.6 : 1 }">
        
        <div style="display: flex; justify-content: space-between; margin-bottom: 10px; align-items: flex-start;">
          <div>
            <h3 style="margin: 0 0 5px 0; color: #333; display: flex; align-items: center; gap: 10px;">
              {{ fb.subject === 'bug' ? '🐛 Bug rapporté' : fb.subject === 'idea' ? '💡 Idée proposée' : '✉️ Autre message' }}
              <!-- Badge affiché si le signalement est accepté -->
              <span v-if="fb.status === 'accepted'" style="font-size: 0.75rem; background: #e8f5e9; color: #4CAF50; padding: 4px 8px; border-radius: 12px;">✔ Traité</span>
            </h3>
            <div style="font-size: 0.85rem; color: #666;">De <strong>{{ fb.first_name || 'Inconnu' }}</strong> ({{ fb.email }})</div>
          </div>
          
          <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 8px;">
            <div style="font-size: 0.8rem; color: #aaa; text-align: right;">{{ formatDate(fb.created_at) }}</div>
            
            <div style="display: flex; gap: 8px;">
              <!-- Masqué si déjà accepté -->
              <button v-if="fb.status !== 'accepted'" @click="emit('accept', fb.id)" style="background: #e8f5e9; border: 1px solid #c8e6c9; color: #4CAF50; padding: 4px 10px; border-radius: 5px; cursor: pointer; font-size: 0.8rem; font-weight: bold; transition: background 0.2s;">
                Accepter
              </button>
              <button @click="emit('delete', fb.id)" style="background: #ffebee; border: 1px solid #ffcdd2; color: #f44336; padding: 4px 10px; border-radius: 5px; cursor: pointer; font-size: 0.8rem; font-weight: bold; transition: background 0.2s;">
                Supprimer
              </button>
            </div>
          </div>
        </div>
        
        <p style="margin: 0; color: #444; line-height: 1.5; background: #fafafa; padding: 15px; border-radius: 8px; border: 1px solid #eee; white-space: pre-wrap;">{{ fb.message }}</p>
      </div>
    </div>
  </div>
</template>