<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useProgramStore } from '../stores/program'

const router = useRouter()
const store = useProgramStore()

const activeTab = ref('runners')
const runners = ref([])
const errorMessage = ref('')
const isLoading = ref(true)

const feedbacks = ref([])
const isFeedbacksLoading = ref(true)

const API_BASE = 'https://cepegra-frontend.xyz/ingrwf13/adrien_ei2/api'

const selectedRunner = ref(null)
const runnerHistory = ref([])
const isHistoryLoading = ref(false)

// CRUD Saisons
const allSeasons = ref([])
const newSeasonTitle = ref('Saison 2 - Objectif 10 km')
const newSeasonWeeks = ref(12)
const newSeasonSessions = ref(3)
const isGenerating = ref(false)
const generateMessage = ref('')

const filterSeason = ref('')
const filterWeek = ref('')
const filterSession = ref('')

const handleLogout = () => {
  store.logout()
  router.push('/welcome')
}

const formatDate = (dateString) => {
  if (!dateString) return 'Date inconnue'
  const safeDate = dateString.replace(' ', 'T')
  return new Date(safeDate).toLocaleDateString('fr-FR', { 
    day: '2-digit', month: '2-digit', year: 'numeric', 
    hour: '2-digit', minute:'2-digit' 
  })
}

const fetchRunners = async () => {
  const token = localStorage.getItem('auth_token')
  try {
    const res = await fetch(`${API_BASE}/admin/users.php?action=list`, {
      headers: { 'Authorization': `Bearer ${token}` }
    })
    const data = await res.json()
    if (data.status === 'success') {
      runners.value = data.data
    } else {
      errorMessage.value = "Accès refusé. Redirection..."
      setTimeout(() => { store.logout(); router.push('/login') }, 2000)
    }
  } catch (e) {
    errorMessage.value = "Erreur réseau."
  } finally {
    isLoading.value = false
  }
}

const fetchFeedbacks = async () => {
  isFeedbacksLoading.value = true
  const token = localStorage.getItem('auth_token')
  try {
    const res = await fetch(`${API_BASE}/admin/users.php?action=feedbacks`, {
      headers: { 'Authorization': `Bearer ${token}` }
    })
    const data = await res.json()
    if (data.status === 'success') {
      feedbacks.value = data.data
    }
  } catch (e) {
    console.error("Erreur", e)
  } finally {
    isFeedbacksLoading.value = false
  }
}

const deleteFeedback = async (id) => {
  if (!confirm("Voulez-vous vraiment supprimer ce signalement ?")) return
  const token = localStorage.getItem('auth_token')
  try {
    const res = await fetch(`${API_BASE}/admin/users.php?action=delete_feedback&id=${id}`, {
      headers: { 'Authorization': `Bearer ${token}` }
    })
    const data = await res.json()
    if (data.status === 'success') {
      feedbacks.value = feedbacks.value.filter(fb => fb.id !== id)
    }
  } catch (e) {
    alert("Erreur réseau.")
  }
}

const fetchAllSeasons = async () => {
  const token = localStorage.getItem('auth_token')
  try {
    const res = await fetch(`${API_BASE}/admin/users.php?action=get_all_seasons`, {
      headers: { 'Authorization': `Bearer ${token}` }
    })
    const data = await res.json()
    if (data.status === 'success') {
      allSeasons.value = data.data
    }
  } catch (e) {
    console.error(e)
  }
}

const editSeason = async (season) => {
  const newTitle = prompt("Nouveau nom pour la saison :", season.title)
  if (!newTitle || newTitle === season.title) return
  
  const token = localStorage.getItem('auth_token')
  try {
    const res = await fetch(`${API_BASE}/admin/users.php?action=update_season`, {
      method: 'POST',
      headers: { 
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json' 
      },
      body: JSON.stringify({ id: season.id, title: newTitle })
    })
    const data = await res.json()
    if (data.status === 'success') {
      season.title = newTitle
      await store.initApp() // Mise à jour globale
    } else {
      alert("Erreur: " + data.message)
    }
  } catch (e) {
    alert("Erreur réseau")
  }
}

const deleteSeason = async (id) => {
  if (!confirm("⚠️ ATTENTION : Cela va supprimer définitivement toute la saison, y compris les semaines et entraînements associés. Continuer ?")) return
  
  const token = localStorage.getItem('auth_token')
  try {
    const res = await fetch(`${API_BASE}/admin/users.php?action=delete_season&id=${id}`, {
      headers: { 'Authorization': `Bearer ${token}` }
    })
    const data = await res.json()
    if (data.status === 'success') {
      allSeasons.value = allSeasons.value.filter(s => s.id !== id)
      await store.initApp() // Mise à jour globale
    } else {
      alert("Erreur lors de la suppression")
    }
  } catch (e) {
    alert("Erreur réseau")
  }
}

const generateProgram = async () => {
  if (!confirm(`Générer ${newSeasonWeeks.value * newSeasonSessions.value} entraînements procéduraux ?`)) return
  isGenerating.value = true
  generateMessage.value = ''
  
  const token = localStorage.getItem('auth_token')
  try {
    const res = await fetch(`${API_BASE}/admin/users.php?action=generate_season`, {
      method: 'POST',
      headers: { 
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json' 
      },
      body: JSON.stringify({
        title: newSeasonTitle.value,
        weeks: newSeasonWeeks.value,
        sessionsPerWeek: newSeasonSessions.value
      })
    })
    const data = await res.json()
    if (data.status === 'success') {
      generateMessage.value = "✅ Programme généré avec succès en base de données !"
      await store.initApp()
      await fetchAllSeasons() // Rafraîchit la liste en direct
    } else {
      generateMessage.value = "❌ Erreur : " + data.message
    }
  } catch (e) {
    generateMessage.value = "❌ Erreur réseau."
  } finally {
    isGenerating.value = false
  }
}

const viewRunnerHistory = async (runner) => {
  selectedRunner.value = runner
  isHistoryLoading.value = true
  runnerHistory.value = []
  filterSeason.value = ''
  filterWeek.value = ''
  filterSession.value = ''

  const token = localStorage.getItem('auth_token')
  try {
    const res = await fetch(`${API_BASE}/admin/users.php?action=history&user_id=${runner.id}`, {
      headers: { 'Authorization': `Bearer ${token}` }
    })
    const data = await res.json()
    if (data.status === 'success') {
      runnerHistory.value = data.data
    }
  } catch (e) {
    console.error(e)
  } finally {
    isHistoryLoading.value = false
  }
}

const closeHistory = () => {
  selectedRunner.value = null
}

const availableSeasons = computed(() => [...new Set(runnerHistory.value.map(h => h.season_title))])
const availableWeeks = computed(() => {
  return [...new Set(runnerHistory.value
    .filter(h => !filterSeason.value || h.season_title === filterSeason.value)
    .map(h => h.week_title))]
})
const availableSessions = computed(() => {
  return [...new Set(runnerHistory.value
    .filter(h => (!filterSeason.value || h.season_title === filterSeason.value) && 
                 (!filterWeek.value || h.week_title === filterWeek.value))
    .map(h => h.session_index))].sort((a, b) => a - b)
})

const filteredHistory = computed(() => {
  return runnerHistory.value.filter(log => {
    return (!filterSeason.value || log.season_title === filterSeason.value) &&
           (!filterWeek.value || log.week_title === filterWeek.value) &&
           (!filterSession.value || log.session_index === filterSession.value)
  })
})

const exportData = async (format) => {
  const token = localStorage.getItem('auth_token')
  try {
    const res = await fetch(`${API_BASE}/admin/users.php?action=export&format=${format}`, {
      headers: { 'Authorization': `Bearer ${token}` }
    })
    if (!res.ok) throw new Error("Erreur export")
    const blob = await res.blob()
    const url = window.URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url
    a.download = `coureurs.${format}`
    a.click()
    window.URL.revokeObjectURL(url)
  } catch (e) {
    alert("Erreur téléchargement.")
  }
}

onMounted(() => { 
  fetchRunners()
  fetchFeedbacks()
  fetchAllSeasons()
})
</script>

<template>
  <main style="padding: 20px; font-family: sans-serif; max-width: 900px; margin: 0 auto; position: relative;">
    
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
      <h1 style="color: #333; margin: 0;">⚙️ Administration</h1>
      <button @click="handleLogout" style="padding: 8px 15px; background: #f44336; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: bold;">
        Déconnexion
      </button>
    </div>

    <div v-if="errorMessage" style="background: #ffebee; color: #d32f2f; padding: 15px; border-radius: 8px; text-align: center; font-weight: bold; margin-bottom: 20px;">
      {{ errorMessage }}
    </div>

    <div style="display: flex; gap: 10px; margin-bottom: 20px; border-bottom: 2px solid #eee; padding-bottom: 10px; flex-wrap: wrap;">
      <button @click="activeTab = 'runners'" :style="{ background: activeTab === 'runners' ? '#4CAF50' : 'transparent', color: activeTab === 'runners' ? 'white' : '#666', border: 'none', padding: '10px 20px', borderRadius: '8px', fontWeight: 'bold', cursor: 'pointer' }">
        👥 Les Coureurs
      </button>
      <button @click="activeTab = 'feedbacks'" :style="{ background: activeTab === 'feedbacks' ? '#4CAF50' : 'transparent', color: activeTab === 'feedbacks' ? 'white' : '#666', border: 'none', padding: '10px 20px', borderRadius: '8px', fontWeight: 'bold', cursor: 'pointer' }">
        📬 Signalements
      </button>
      <button @click="activeTab = 'program'" :style="{ background: activeTab === 'program' ? '#4CAF50' : 'transparent', color: activeTab === 'program' ? 'white' : '#666', border: 'none', padding: '10px 20px', borderRadius: '8px', fontWeight: 'bold', cursor: 'pointer' }">
        📁 Gérer le Programme
      </button>
    </div>

    <!-- TAB : COUREURS -->
    <div v-if="activeTab === 'runners'">
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
        <h2 style="margin: 0; color: #4CAF50;">Liste des inscrits ({{ runners.length }})</h2>
        <div style="display: flex; gap: 10px;">
          <button @click="exportData('csv')" style="padding: 8px 15px; background: #2196F3; color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: bold;">📥 Export CSV</button>
          <button @click="exportData('json')" style="padding: 8px 15px; background: #333; color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: bold;">📥 Export JSON</button>
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
            <tr v-for="runner in runners" :key="runner.id" @click="viewRunnerHistory(runner)" class="runner-row">
              <td style="padding: 15px; font-weight: bold; color: #333;">{{ runner.first_name }}</td>
              <td style="padding: 15px; color: #666;">{{ runner.email }}</td>
              <td style="padding: 15px; color: #888; font-size: 0.9rem;">{{ formatDate(runner.created_at) }}</td>
              <td style="padding: 15px;">
                <span style="background: #e38734; color: white; padding: 4px 8px; border-radius: 12px; font-size: 0.8rem; font-weight: bold;">
                  {{ runner.current_season_id || 1 }} - {{ runner.current_week_id || 1 }} - {{ runner.current_session_id || 1 }}
                </span>
              </td>
              <td style="padding: 15px; color: #4CAF50; font-weight: bold;">
                {{ ((runner.total_distance || 0) / 1000).toFixed(2) }} km
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- TAB : SIGNALEMENTS -->
    <div v-if="activeTab === 'feedbacks'">
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
        <h2 style="margin: 0; color: #4CAF50;">Retours utilisateurs ({{ feedbacks.length }})</h2>
        <button @click="fetchFeedbacks" style="padding: 8px 15px; background: #e0e0e0; color: #333; border: none; border-radius: 5px; cursor: pointer; font-weight: bold;">
          🔄 Rafraîchir
        </button>
      </div>

      <div v-if="isFeedbacksLoading" style="text-align: center; padding: 40px; color: #888;">Chargement des signalements...</div>

      <div v-else-if="feedbacks.length === 0" style="text-align: center; padding: 40px; color: #888; font-style: italic; background: white; border-radius: 12px;">
        Aucun signalement pour le moment.
      </div>

      <div v-else style="display: flex; flex-direction: column; gap: 15px;">
        <div v-for="fb in feedbacks" :key="fb.id" 
             :style="{ background: 'white', borderRadius: '12px', padding: '20px', boxShadow: '0 2px 8px rgba(0,0,0,0.05)', borderLeft: fb.subject === 'bug' ? '5px solid #f44336' : fb.subject === 'idea' ? '5px solid #2196F3' : '5px solid #9e9e9e' }">
          <div style="display: flex; justify-content: space-between; margin-bottom: 10px; align-items: flex-start;">
            <div>
              <h3 style="margin: 0 0 5px 0; color: #333;">
                {{ fb.subject === 'bug' ? '🐛 Bug rapporté' : fb.subject === 'idea' ? '💡 Idée proposée' : '✉️ Autre message' }}
              </h3>
              <div style="font-size: 0.85rem; color: #666;">De <strong>{{ fb.first_name || 'Inconnu' }}</strong> ({{ fb.email }})</div>
            </div>
            
            <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 8px;">
              <div style="font-size: 0.8rem; color: #aaa; text-align: right;">{{ formatDate(fb.created_at) }}</div>
              <button @click="deleteFeedback(fb.id)" style="background: #ffebee; border: 1px solid #ffcdd2; color: #f44336; padding: 4px 10px; border-radius: 5px; cursor: pointer; font-size: 0.8rem; font-weight: bold;">
                Supprimer
              </button>
            </div>
          </div>
          <p style="margin: 0; color: #444; line-height: 1.5; background: #fafafa; padding: 15px; border-radius: 8px; border: 1px solid #eee; white-space: pre-wrap;">{{ fb.message }}</p>
        </div>
      </div>
    </div>

    <!-- TAB : PROGRAMME CRUD -->
    <div v-if="activeTab === 'program'">
      
      <!-- Générateur -->
      <div style="background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); margin-bottom: 25px;">
        <div style="text-align: center; border-bottom: 1px solid #eee; padding-bottom: 20px; margin-bottom: 25px;">
          <h2 style="color: #4CAF50; margin-top: 0; margin-bottom: 10px;">Générateur de Programme</h2>
          <p style="color: #666; font-size: 0.95rem; margin: 0;">Générez automatiquement une nouvelle saison crescendo (Échauffement + Course/Marche + Étirements).</p>
        </div>

        <div style="display: flex; flex-direction: column; gap: 15px; max-width: 500px; margin: 0 auto;">
          <div>
            <label style="display: block; margin-bottom: 5px; font-weight: bold; color: #333;">Titre de la nouvelle saison</label>
            <input type="text" v-model="newSeasonTitle" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box; font-size: 16px;" />
          </div>
          <div style="display: flex; gap: 15px;">
            <div style="flex: 1;">
              <label style="display: block; margin-bottom: 5px; font-weight: bold; color: #333;">Nombre de Semaines</label>
              <input type="number" v-model="newSeasonWeeks" min="1" max="52" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box; font-size: 16px;" />
            </div>
            <div style="flex: 1;">
              <label style="display: block; margin-bottom: 5px; font-weight: bold; color: #333;">Séances par Semaine</label>
              <input type="number" v-model="newSeasonSessions" min="1" max="7" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box; font-size: 16px;" />
            </div>
          </div>
          
          <button @click="generateProgram" :disabled="isGenerating" style="width: 100%; padding: 15px; margin-top: 10px; background: #e38734; color: white; border: none; border-radius: 8px; font-weight: bold; font-size: 16px; cursor: pointer; box-shadow: 0 4px 10px rgba(227, 135, 52, 0.3);">
            {{ isGenerating ? 'Génération en cours...' : '⚡ Générer la Saison' }}
          </button>

          <p v-if="generateMessage" style="text-align: center; font-weight: bold; margin-top: 10px;" :style="{ color: generateMessage.includes('❌') ? '#d32f2f' : '#4CAF50' }">{{ generateMessage }}</p>
        </div>
      </div>

      <!-- Liste des Saisons pour Édition -->
      <div style="background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.05);">
        <h2 style="color: #4CAF50; margin-top: 0; border-bottom: 1px solid #eee; padding-bottom: 10px;">Saisons Existantes</h2>
        
        <div v-if="allSeasons.length === 0" style="text-align: center; color: #888; padding: 20px;">
          Aucune saison pour le moment.
        </div>

        <div v-else style="display: flex; flex-direction: column; gap: 10px;">
          <div v-for="season in allSeasons" :key="season.id" style="display: flex; justify-content: space-between; align-items: center; padding: 15px; background: #fafafa; border: 1px solid #eee; border-radius: 8px;">
            <div>
              <strong style="font-size: 1.1rem; color: #333;">{{ season.title }}</strong>
              <div style="font-size: 0.85rem; color: #666; margin-top: 4px;">{{ season.weeks_count }} semaines</div>
            </div>
            <div style="display: flex; gap: 10px;">
              <button @click="editSeason(season)" style="padding: 6px 12px; background: #2196F3; color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: bold;">
                ✏️ Éditer
              </button>
              <button @click="deleteSeason(season.id)" style="padding: 6px 12px; background: #f44336; color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: bold;">
                🗑️ Supprimer
              </button>
            </div>
          </div>
        </div>
      </div>

    </div>

    <!-- MODALE HISTORIQUE D'UN COUREUR -->
    <div v-if="selectedRunner" style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.6); display: flex; align-items: center; justify-content: center; z-index: 1000; padding: 20px;">
      <div style="background: white; padding: 25px; border-radius: 12px; width: 100%; max-width: 800px; max-height: 85vh; overflow-y: auto; position: relative;">
        <button @click="closeHistory" style="position: absolute; top: 15px; right: 20px; background: none; border: none; font-size: 24px; cursor: pointer; color: #888;">✖</button>
        
        <h2 style="margin-top: 0; color: #333;">Historique de <span style="color: #4CAF50;">{{ selectedRunner.first_name }}</span></h2>
        
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
            <option v-for="idx in availableSessions" :key="idx" :value="idx">
              {{ idx === 1 ? '1ère' : idx + 'ème' }} session
            </option>
          </select>
        </div>

        <div v-if="isHistoryLoading" style="text-align: center; padding: 20px; color: #888;">Chargement de l'historique...</div>
        
        <div v-else-if="filteredHistory.length === 0" style="text-align: center; padding: 20px; color: #888; font-style: italic;">
          Aucune session trouvée pour ces filtres.
        </div>

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
              <td style="padding: 10px; color: #666;">
                {{ formatDate(log.completed_at || log.created_at) }}
              </td>
              <td style="padding: 10px; font-weight: bold; color: #333;">{{ log.season_title }}</td>
              <td style="padding: 10px; color: #555;">{{ log.week_title }}</td>
              <td style="padding: 10px; color: #e38734; font-weight: bold;">
                {{ log.session_index === 1 ? '1ère' : log.session_index + 'ème' }} session
              </td>
              <td style="padding: 10px; font-weight: bold; color: #4CAF50; text-align: right;">{{ (log.distance_meters / 1000).toFixed(2) }} km</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

  </main>
</template>

<style scoped>
.runner-row {
  border-bottom: 1px solid #eee;
  cursor: pointer;
  transition: background 0.2s;
}
.runner-row:hover {
  background-color: #f1f8e9;
}
</style>