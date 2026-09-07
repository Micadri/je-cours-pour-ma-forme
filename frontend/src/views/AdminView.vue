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

const API_BASE = 'https://cepegra-frontend.xyz/ingrwf13/adrien_ei2/api'

// États pour la modale d'historique
const selectedRunner = ref(null)
const runnerHistory = ref([])
const isHistoryLoading = ref(false)

// Filtres
const filterSeason = ref('')
const filterWeek = ref('')
const filterSession = ref('')

const handleLogout = () => {
  store.logout()
  router.push('/welcome')
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

// Logique des listes déroulantes de filtrage
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
    .map(h => h.session_title))]
})

// Application des filtres sur le tableau
const filteredHistory = computed(() => {
  return runnerHistory.value.filter(log => {
    return (!filterSeason.value || log.season_title === filterSeason.value) &&
           (!filterWeek.value || log.week_title === filterWeek.value) &&
           (!filterSession.value || log.session_title === filterSession.value)
  })
})

const exportData = async (format) => {
  const token = localStorage.getItem('auth_token')
  try {
    const res = await fetch(`${API_BASE}/admin/users.php?action=export&format=${format}`, {
      headers: { 'Authorization': `Bearer ${token}` }
    })
    if (!res.ok) throw new Error("Erreur lors de l'export")
    
    const blob = await res.blob()
    const url = window.URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url
    a.download = `coureurs.${format}`
    a.click()
    window.URL.revokeObjectURL(url)
  } catch (e) {
    alert("Erreur lors du téléchargement.")
  }
}

onMounted(() => { fetchRunners() })
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

    <div style="display: flex; gap: 10px; margin-bottom: 20px; border-bottom: 2px solid #eee; padding-bottom: 10px;">
      <button @click="activeTab = 'runners'" :style="{ background: activeTab === 'runners' ? '#4CAF50' : 'transparent', color: activeTab === 'runners' ? 'white' : '#666', border: 'none', padding: '10px 20px', borderRadius: '8px', fontWeight: 'bold', cursor: 'pointer' }">
        👥 Les Coureurs
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
              <th style="padding: 15px; border-bottom: 2px solid #ddd;">Position (Saison / Semaine / Entraînement)</th>
              <th style="padding: 15px; border-bottom: 2px solid #ddd;">Km Parcourus</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="runner in runners" :key="runner.id" @click="viewRunnerHistory(runner)" class="runner-row">
              <td style="padding: 15px; font-weight: bold; color: #333;">{{ runner.first_name }}</td>
              <td style="padding: 15px; color: #666;">{{ runner.email }}</td>
              <td style="padding: 15px; color: #888; font-size: 0.9rem;">{{ new Date(runner.created_at).toLocaleDateString() }}</td>
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

    <!-- MODALE HISTORIQUE D'UN COUREUR -->
    <div v-if="selectedRunner" style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.6); display: flex; align-items: center; justify-content: center; z-index: 1000; padding: 20px;">
      <div style="background: white; padding: 25px; border-radius: 12px; width: 100%; max-width: 800px; max-height: 85vh; overflow-y: auto; position: relative;">
        <button @click="closeHistory" style="position: absolute; top: 15px; right: 20px; background: none; border: none; font-size: 24px; cursor: pointer; color: #888;">✖</button>
        
        <h2 style="margin-top: 0; color: #333;">Historique de <span style="color: #4CAF50;">{{ selectedRunner.first_name }}</span></h2>
        
        <!-- Filtres -->
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
            <option v-for="se in availableSessions" :key="se" :value="se">{{ se.split(' - ')[1] || se }}</option>
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
                <!-- Gestion robuste de l'affichage de la date -->
                {{ log.created_at ? new Date(log.created_at).toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute:'2-digit' }) : 'Date inconnue' }}
              </td>
              <td style="padding: 10px; font-weight: bold; color: #333;">{{ log.season_title }}</td>
              <td style="padding: 10px; color: #555;">{{ log.week_title }}</td>
              <td style="padding: 10px; color: #e38734;">{{ log.session_title.split(' - ')[1] || log.session_title }}</td>
              <td style="padding: 10px; font-weight: bold; color: #4CAF50; text-align: right;">{{ (log.distance_meters / 1000).toFixed(2) }} km</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- TAB : PROGRAMME CRUD -->
    <div v-if="activeTab === 'program'">
      <div style="background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); text-align: center;">
        <h2 style="color: #4CAF50; margin-top: 0;">Éditeur de programme</h2>
        <p style="color: #666;">L'interface de gestion (Créer/Modifier/Supprimer) pour les saisons, semaines, et entraînements s'affichera ici.</p>
        <button style="padding: 10px 20px; background: #e38734; color: white; border: none; border-radius: 8px; font-weight: bold; cursor: not-allowed; opacity: 0.5;">
          + Ajouter une saison (Bientôt)
        </button>
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
  background-color: #f1f8e9; /* Vert très clair au survol */
}
</style>