<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useProgramStore } from '../stores/program'

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

const selectedRunner = ref(null)
const runnerHistory = ref([])
const isHistoryLoading = ref(false)

const handleLogout = () => { store.logout(); router.push('/welcome') }

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
    const res = await fetch(`${API_BASE}/admin/users.php?action=accept_feedback&id=${id}`, { headers: { 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` } })
    const data = await res.json()
    if (data.status === 'success') {
      const fb = feedbacks.value.find(f => f.id === id)
      if (fb) fb.status = 'accepted'
    }
  } catch (e) { alert("Erreur réseau") }
}

onMounted(() => { fetchRunners(); fetchFeedbacks(); fetchAllSeasons() })
</script>

<template>
  <main class="p-5 font-body max-w-[900px] mx-auto relative">
    
    <div class="flex justify-between items-center mb-8">
      <h1 class="m-0 font-heading text-3xl text-primary font-bold dark:text-gray-100">⚙️ Administration</h1>
      <button @click="handleLogout" class="px-4 py-2 bg-red-500 text-white border-none rounded-cta cursor-pointer font-bold hover:bg-red-600 transition-colors">Déconnexion</button>
    </div>

    <div v-if="errorMessage" class="bg-red-50 text-red-600 p-4 rounded-xl text-center font-bold mb-5 dark:bg-red-900/20 dark:text-red-400 border border-red-200 dark:border-red-900/30">
      {{ errorMessage }}
    </div>

    <!-- Navigation par Onglets -->
    <div class="flex gap-2 mb-6 border-b-2 border-gray-200 pb-3 flex-wrap dark:border-gray-700">
      <button @click="activeTab = 'runners'" class="px-5 py-2 rounded-cta font-bold cursor-pointer transition-colors font-heading tracking-wide uppercase text-sm" :class="activeTab === 'runners' ? 'bg-primary text-accent' : 'bg-transparent text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800'">👥 Les Coureurs</button>
      <button @click="activeTab = 'feedbacks'" class="px-5 py-2 rounded-cta font-bold cursor-pointer transition-colors font-heading tracking-wide uppercase text-sm" :class="activeTab === 'feedbacks' ? 'bg-primary text-accent' : 'bg-transparent text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800'">📬 Signalements</button>
      <button @click="activeTab = 'program'" class="px-5 py-2 rounded-cta font-bold cursor-pointer transition-colors font-heading tracking-wide uppercase text-sm" :class="activeTab === 'program' ? 'bg-primary text-accent' : 'bg-transparent text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800'">📁 Gérer le Programme</button>
    </div>

    <AdminRunnersTable v-if="activeTab === 'runners'" :runners="runners" :isLoading="isLoading" @export="exportData" @view-history="viewRunnerHistory" />
    <AdminFeedbacks v-if="activeTab === 'feedbacks'" :feedbacks="feedbacks" :isLoading="isFeedbacksLoading" @refresh="fetchFeedbacks" @delete="deleteFeedback" @accept="acceptFeedback" />
    <AdminProgramBuilder v-if="activeTab === 'program'" :allSeasons="allSeasons" @refresh-seasons="fetchAllSeasons" />

    <RunnerHistoryModal v-if="selectedRunner" :runner="selectedRunner" :history="runnerHistory" :isLoading="isHistoryLoading" @close="selectedRunner = null" />
  </main>
</template>