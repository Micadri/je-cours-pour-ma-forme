<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useProgramStore } from '../stores/program'

// Import de nos composants Admin isolés
import AdminRunnersTable from '../components/AdminRunnersTable.vue'
import AdminFeedbacks from '../components/AdminFeedbacks.vue'
import AdminProgramBuilder from '../components/AdminProgramBuilder.vue'
import RunnerHistoryModal from '../components/RunnerHistoryModal.vue'

const router = useRouter()
const store = useProgramStore()
const API_BASE = 'https://cepegra-frontend.xyz/ingrwf13/adrien_ei2/api'

const activeTab = ref('runners')
const runners = ref([])
const feedbacks = ref([])
const allSeasons = ref([])

const isLoading = ref(true)
const isFeedbacksLoading = ref(true)
const errorMessage = ref('')

// États modale Historique
const selectedRunner = ref(null)
const runnerHistory = ref([])
const isHistoryLoading = ref(false)

const handleLogout = () => {
  store.logout()
  router.push('/welcome')
}

const fetchRunners = async () => {
  try {
    const res = await fetch(`${API_BASE}/admin/users.php?action=list`, { headers: { 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` } })
    const data = await res.json()
    if (data.status === 'success') runners.value = data.data
    else throw new Error()
  } catch (e) {
    errorMessage.value = "Erreur. Redirection..."
    setTimeout(() => { store.logout(); router.push('/login') }, 2000)
  } finally { isLoading.value = false }
}

const fetchFeedbacks = async () => {
  isFeedbacksLoading.value = true
  try {
    const res = await fetch(`${API_BASE}/admin/users.php?action=feedbacks`, { headers: { 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` } })
    const data = await res.json()
    if (data.status === 'success') feedbacks.value = data.data
  } catch (e) { console.error(e) } finally { isFeedbacksLoading.value = false }
}

const fetchAllSeasons = async () => {
  try {
    const res = await fetch(`${API_BASE}/admin/users.php?action=get_all_seasons`, { headers: { 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` } })
    const data = await res.json()
    if (data.status === 'success') allSeasons.value = data.data
  } catch (e) { console.error(e) }
}

const exportData = async (format) => {
  try {
    const res = await fetch(`${API_BASE}/admin/users.php?action=export&format=${format}`, { headers: { 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` } })
    const blob = await res.blob()
    const url = window.URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url; a.download = `coureurs.${format}`; a.click(); window.URL.revokeObjectURL(url)
  } catch (e) { alert("Erreur d'export") }
}

const deleteFeedback = async (id) => {
  try {
    await fetch(`${API_BASE}/admin/users.php?action=delete_feedback&id=${id}`, { headers: { 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` } })
    feedbacks.value = feedbacks.value.filter(fb => fb.id !== id)
  } catch (e) { alert("Erreur réseau") }
}

const viewRunnerHistory = async (runner) => {
  selectedRunner.value = runner
  isHistoryLoading.value = true
  try {
    const res = await fetch(`${API_BASE}/admin/users.php?action=history&user_id=${runner.id}`, { headers: { 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` } })
    const data = await res.json()
    if (data.status === 'success') runnerHistory.value = data.data
  } catch (e) { } finally { isHistoryLoading.value = false }
}

const acceptFeedback = async (id) => {
  try {
    const res = await fetch(`${API_BASE}/admin/users.php?action=accept_feedback&id=${id}`, {
      headers: { 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` }
    })
    const data = await res.json()
    if (data.status === 'success') {
      const fb = feedbacks.value.find(f => f.id === id)
      if (fb) fb.status = 'accepted' // Met à jour l'UI instantanément
    }
  } catch (e) {
    alert("Erreur réseau")
  }
}

onMounted(() => {
  fetchRunners(); fetchFeedbacks(); fetchAllSeasons()
})
</script>

<template>
  <main style="padding: 20px; font-family: sans-serif; max-width: 900px; margin: 0 auto; position: relative;">

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
      <h1 style="color: #333; margin: 0;">⚙️ Administration</h1>
      <button @click="handleLogout"
        style="padding: 8px 15px; background: #f44336; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: bold;">Déconnexion</button>
    </div>

    <div v-if="errorMessage"
      style="background: #ffebee; color: #d32f2f; padding: 15px; border-radius: 8px; text-align: center; font-weight: bold; margin-bottom: 20px;">
      {{ errorMessage }}</div>

    <div
      style="display: flex; gap: 10px; margin-bottom: 20px; border-bottom: 2px solid #eee; padding-bottom: 10px; flex-wrap: wrap;">
      <button @click="activeTab = 'runners'"
        :style="{ background: activeTab === 'runners' ? '#4CAF50' : 'transparent', color: activeTab === 'runners' ? 'white' : '#666', border: 'none', padding: '10px 20px', borderRadius: '8px', fontWeight: 'bold', cursor: 'pointer' }">👥
        Les Coureurs</button>
      <button @click="activeTab = 'feedbacks'"
        :style="{ background: activeTab === 'feedbacks' ? '#4CAF50' : 'transparent', color: activeTab === 'feedbacks' ? 'white' : '#666', border: 'none', padding: '10px 20px', borderRadius: '8px', fontWeight: 'bold', cursor: 'pointer' }">📬
        Signalements</button>
      <button @click="activeTab = 'program'"
        :style="{ background: activeTab === 'program' ? '#4CAF50' : 'transparent', color: activeTab === 'program' ? 'white' : '#666', border: 'none', padding: '10px 20px', borderRadius: '8px', fontWeight: 'bold', cursor: 'pointer' }">📁
        Gérer le Programme</button>
    </div>

    <!-- Composants Isolés -->
    <AdminRunnersTable v-if="activeTab === 'runners'" :runners="runners" :isLoading="isLoading" @export="exportData"
      @view-history="viewRunnerHistory" />
    <AdminFeedbacks v-if="activeTab === 'feedbacks'" :feedbacks="feedbacks" :isLoading="isFeedbacksLoading"
      @refresh="fetchFeedbacks" @delete="deleteFeedback" @accept="acceptFeedback" />
    <AdminProgramBuilder v-if="activeTab === 'program'" :allSeasons="allSeasons" @refresh-seasons="fetchAllSeasons" />

    <RunnerHistoryModal v-if="selectedRunner" :runner="selectedRunner" :history="runnerHistory"
      :isLoading="isHistoryLoading" @close="selectedRunner = null" />

  </main>
</template>