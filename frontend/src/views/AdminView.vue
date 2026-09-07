<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const activeTab = ref('runners')
const runners = ref([])
const errorMessage = ref('')
const isLoading = ref(true)

const API_BASE = 'https://cepegra-frontend.xyz/ingrwf13/adrien_ei2/api'

// Fetch des coureurs
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
      setTimeout(() => router.push('/'), 2000)
    }
  } catch (e) {
    errorMessage.value = "Erreur réseau."
  } finally {
    isLoading.value = false
  }
}

// Fonction de téléchargement sécurisée (gère le token Bearer)
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

onMounted(() => {
  fetchRunners()
})
</script>

<template>
  <main style="padding: 20px; font-family: sans-serif; max-width: 900px; margin: 0 auto;">
    
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
      <h1 style="color: #333; margin: 0;">⚙️ Administration</h1>
      <button @click="router.push('/')" style="padding: 8px 15px; background: #e0e0e0; color: #333; border: none; border-radius: 8px; cursor: pointer; font-weight: bold;">
        Retour à l'app
      </button>
    </div>

    <div v-if="errorMessage" style="background: #ffebee; color: #d32f2f; padding: 15px; border-radius: 8px; text-align: center; font-weight: bold; margin-bottom: 20px;">
      {{ errorMessage }}
    </div>

    <!-- Onglets -->
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
              <th style="padding: 15px; border-bottom: 2px solid #ddd;">Position (S. / W. / E.)</th>
              <th style="padding: 15px; border-bottom: 2px solid #ddd;">Km Parcourus</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="runner in runners" :key="runner.id" style="border-bottom: 1px solid #eee;">
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