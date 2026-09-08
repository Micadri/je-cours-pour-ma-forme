<script setup>
import { ref } from 'vue'
import { useProgramStore } from '../stores/program'

const props = defineProps(['allSeasons'])
const emit = defineEmits(['refresh-seasons'])
const store = useProgramStore()
const API_BASE = 'https://cepegra-frontend.xyz/ingrwf13/adrien_ei2/api'

const newSeasonTitle = ref('Saison 2 - Objectif 10 km')
const newSeasonWeeks = ref(12); const newSeasonSessions = ref(3)
const isGenerating = ref(false); const generateMessage = ref('')
const selectedSeasonForDetails = ref(null); const seasonDetails = ref([]); const isSeasonDetailsLoading = ref(false)
const editingSessionId = ref(null); const editingExercises = ref([])

const exerciseOptions = [
  { value: 'echauffement', label: 'Échauffement' }, { value: 'marches', label: 'Marche' },
  { value: 'trottes', label: 'Trotte / Course lente' }, { value: 'cours', label: 'Course rapide' },
  { value: 'sprints', label: 'Sprint' }, { value: 'deboules', label: 'Déboulés' }, { value: 'etirements', label: 'Étirements' }
]

const formatDuration = (seconds) => { const m = Math.floor(seconds / 60); const s = seconds % 60; return s > 0 ? `${m}m ${s}s` : `${m} min` }

const generateProgram = async () => {
  if (!confirm(`Générer ${newSeasonWeeks.value * newSeasonSessions.value} entraînements procéduraux ?`)) return
  isGenerating.value = true; generateMessage.value = ''
  try {
    const res = await fetch(`${API_BASE}/admin/users.php?action=generate_season`, {
      method: 'POST', headers: { 'Authorization': `Bearer ${localStorage.getItem('auth_token')}`, 'Content-Type': 'application/json' },
      body: JSON.stringify({ title: newSeasonTitle.value, weeks: newSeasonWeeks.value, sessionsPerWeek: newSeasonSessions.value })
    })
    const data = await res.json()
    if (data.status === 'success') { generateMessage.value = "✅ Programme généré !"; await store.initApp(); emit('refresh-seasons') } 
    else { generateMessage.value = "❌ Erreur : " + data.message }
  } catch (e) { generateMessage.value = "❌ Erreur réseau." } finally { isGenerating.value = false }
}

const editSeason = async (season) => {
  const newTitle = prompt("Nouveau nom :", season.title)
  if (!newTitle || newTitle === season.title) return
  try {
    const res = await fetch(`${API_BASE}/admin/users.php?action=update_season`, {
      method: 'POST', headers: { 'Authorization': `Bearer ${localStorage.getItem('auth_token')}`, 'Content-Type': 'application/json' },
      body: JSON.stringify({ id: season.id, title: newTitle })
    })
    const data = await res.json()
    if (data.status === 'success') { season.title = newTitle; await store.initApp() }
  } catch (e) { alert("Erreur réseau") }
}

const deleteSeason = async (id) => {
  if (!confirm("⚠️ Cela va supprimer définitivement toute la saison. Continuer ?")) return
  try {
    const res = await fetch(`${API_BASE}/admin/users.php?action=delete_season&id=${id}`, { headers: { 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` } })
    const data = await res.json()
    if (data.status === 'success') { if (selectedSeasonForDetails.value?.id === id) selectedSeasonForDetails.value = null; await store.initApp(); emit('refresh-seasons') }
  } catch (e) { alert("Erreur réseau") }
}

const viewSeasonDetails = async (season) => {
  selectedSeasonForDetails.value = season; isSeasonDetailsLoading.value = true
  try {
    const res = await fetch(`${API_BASE}/admin/users.php?action=get_season_details&id=${season.id}`, { headers: { 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` } })
    const data = await res.json()
    if (data.status === 'success') { seasonDetails.value = data.data }
  } catch (e) { alert("Erreur") } finally { isSeasonDetailsLoading.value = false }
}

const openSessionEditor = (session) => {
  editingSessionId.value = session.id
  editingExercises.value = session.exercises.map(exo => ({ type: exo.type, duration_minutes: Number((exo.duration_seconds / 60).toFixed(2)) }))
}

const saveSessionExercises = async (session) => {
  const payload = { session_id: session.id, exercises: editingExercises.value.map(exo => ({ type: exo.type, duration_seconds: Math.round(exo.duration_minutes * 60) })) }
  try {
    const res = await fetch(`${API_BASE}/admin/users.php?action=update_session_exercises`, {
      method: 'POST', headers: { 'Authorization': `Bearer ${localStorage.getItem('auth_token')}`, 'Content-Type': 'application/json' }, body: JSON.stringify(payload)
    })
    const data = await res.json()
    if (data.status === 'success') { session.exercises = payload.exercises.map((exo, i) => ({ ...exo, order_num: i + 1 })); editingSessionId.value = null; await store.initApp(); alert("Entraînement mis à jour !") }
  } catch (e) { alert("Erreur") }
}

const moveExercise = (idx, dir) => {
  if (idx + dir >= 0 && idx + dir < editingExercises.value.length) {
    const temp = editingExercises.value[idx]; editingExercises.value[idx] = editingExercises.value[idx + dir]; editingExercises.value[idx + dir] = temp
  }
}
</script>

<template>
  <div>
    <!-- Vue Générale -->
    <div v-if="!selectedSeasonForDetails">
      
      <!-- Bloc Saisons Existantes -->
      <div class="bg-surface p-6 rounded-xl shadow-sm border border-gray-200 mb-6 dark:bg-gray-800 dark:border-gray-700">
        <h2 class="font-heading text-2xl text-primary font-bold mt-0 border-b border-gray-200 pb-3 mb-4 dark:text-gray-100 dark:border-gray-700">Saisons Existantes</h2>
        <div v-if="allSeasons.length === 0" class="text-center text-gray-500 py-5 dark:text-gray-400">Aucune saison.</div>
        <div v-else class="flex flex-col gap-3">
          <div v-for="season in allSeasons" :key="season.id" class="flex justify-between items-center p-4 bg-gray-50 border border-gray-200 rounded-lg dark:bg-gray-900 dark:border-gray-700">
            <div>
              <strong class="text-lg font-bold text-text dark:text-gray-200">{{ season.title }}</strong>
              <div class="text-sm text-gray-500 mt-1 dark:text-gray-400">{{ season.weeks_count }} semaines paramétrées</div>
            </div>
            <div class="flex gap-2 flex-wrap">
              <button @click="viewSeasonDetails(season)" class="px-3 py-1.5 bg-accent text-primary rounded-cta cursor-pointer font-bold text-sm hover:bg-opacity-90 font-heading uppercase tracking-wide">👁️ Gérer les blocs</button>
              <button @click="editSeason(season)" class="px-3 py-1.5 bg-blue-500 text-white rounded-cta cursor-pointer font-bold text-sm hover:bg-blue-600 transition-colors">✏️ Renommer</button>
              <button @click="deleteSeason(season.id)" class="px-3 py-1.5 bg-red-500 text-white rounded-cta cursor-pointer font-bold text-sm hover:bg-red-600 transition-colors">🗑️ Supprimer</button>
            </div>
          </div>
        </div>
      </div>

      <!-- Bloc Générateur -->
      <div class="bg-surface p-6 rounded-xl shadow-sm border border-gray-200 dark:bg-gray-800 dark:border-gray-700">
        <div class="text-center border-b border-gray-200 pb-5 mb-6 dark:border-gray-700">
          <h2 class="font-heading text-2xl text-primary font-bold mt-0 mb-2 dark:text-gray-100">Générateur Automatique</h2>
          <p class="text-gray-600 text-sm m-0 dark:text-gray-400">Générez une base de travail procédurale avant de l'éditer bloc par bloc.</p>
        </div>
        <div class="flex flex-col gap-4 max-w-[500px] mx-auto">
          <div><label class="block mb-1 font-bold text-gray-700 text-left dark:text-gray-300">Titre</label> <input type="text" v-model="newSeasonTitle" class="w-full p-2.5 border border-gray-300 rounded-md bg-surface focus:ring-2 focus:ring-accent outline-none dark:bg-gray-900 dark:border-gray-600 dark:text-white" /></div>
          <div class="flex gap-4">
            <div class="flex-1"><label class="block mb-1 font-bold text-gray-700 text-left dark:text-gray-300">Semaines</label> <input type="number" v-model="newSeasonWeeks" min="1" max="52" class="w-full p-2.5 border border-gray-300 rounded-md bg-surface focus:ring-2 focus:ring-accent outline-none dark:bg-gray-900 dark:border-gray-600 dark:text-white" /></div>
            <div class="flex-1"><label class="block mb-1 font-bold text-gray-700 text-left dark:text-gray-300">Séances</label> <input type="number" v-model="newSeasonSessions" min="1" max="7" class="w-full p-2.5 border border-gray-300 rounded-md bg-surface focus:ring-2 focus:ring-accent outline-none dark:bg-gray-900 dark:border-gray-600 dark:text-white" /></div>
          </div>
          <button @click="generateProgram" :disabled="isGenerating" class="w-full p-4 mt-2 bg-primary text-accent rounded-cta font-bold text-lg cursor-pointer hover:bg-opacity-90 font-heading uppercase tracking-widest transition-colors disabled:opacity-50">
            {{ isGenerating ? 'Génération en cours...' : '⚡ Générer la Saison' }}
          </button>
          <p v-if="generateMessage" class="text-center font-bold mt-2 mb-0" :class="generateMessage.includes('❌') ? 'text-red-500' : 'text-accent'">{{ generateMessage }}</p>
        </div>
      </div>
    </div>

    <!-- Vue Constructeur de Blocs -->
    <div v-else>
      <button @click="selectedSeasonForDetails = null" class="mb-4 bg-transparent border-none text-accent text-base font-bold cursor-pointer p-0 hover:opacity-80 transition-opacity">← Retour aux saisons</button>
      <h2 class="text-text font-heading text-2xl mt-0 mb-6 dark:text-gray-100">Paramétrage : <span class="text-accent">{{ selectedSeasonForDetails.title }}</span></h2>
      
      <div v-if="isSeasonDetailsLoading" class="text-center p-10 text-gray-500 dark:text-gray-400">Chargement de l'architecture...</div>
      
      <div v-else class="flex flex-col gap-6">
        <div v-for="week in seasonDetails" :key="week.id" class="bg-surface rounded-xl shadow-sm border border-gray-200 overflow-hidden dark:bg-gray-800 dark:border-gray-700">
          <div class="bg-gray-600 text-white px-4 py-3 font-bold text-lg font-heading dark:bg-gray-700">{{ week.title }}</div>
          
          <div class="p-4 flex flex-col gap-4">
            <div v-for="session in week.sessions" :key="session.id" class="border border-gray-200 rounded-lg bg-gray-50 dark:bg-gray-900 dark:border-gray-700">
              
              <div class="flex justify-between items-center p-3 bg-surface rounded-t-lg border-b border-gray-200 dark:bg-gray-800 dark:border-gray-700">
                <strong class="text-primary font-bold text-lg font-heading dark:text-gray-200">{{ session.title }}</strong>
                <button v-if="editingSessionId !== session.id" @click="openSessionEditor(session)" class="px-4 py-1.5 bg-accent text-primary rounded-cta font-bold cursor-pointer text-sm font-heading uppercase tracking-wide hover:bg-opacity-90 transition-colors">⚙️ Configurer</button>
              </div>

              <!-- Zone d'édition active -->
              <div v-if="editingSessionId === session.id" class="p-4 border-t border-gray-200 dark:border-gray-700">
                <div v-for="(exo, index) in editingExercises" :key="index" class="flex gap-2.5 items-center mb-3 bg-surface p-2.5 rounded-md border border-gray-300 dark:bg-gray-800 dark:border-gray-600">
                  <div class="flex flex-col gap-1">
                    <button @click="moveExercise(index, -1)" :disabled="index === 0" class="bg-transparent border-none cursor-pointer text-gray-500 text-lg disabled:opacity-30 hover:text-primary transition-colors p-0 leading-none dark:text-gray-400">▲</button>
                    <button @click="moveExercise(index, 1)" :disabled="index === editingExercises.length - 1" class="bg-transparent border-none cursor-pointer text-gray-500 text-lg disabled:opacity-30 hover:text-primary transition-colors p-0 leading-none dark:text-gray-400">▼</button>
                  </div>
                  <select v-model="exo.type" class="flex-2 p-2 rounded-md border border-gray-300 bg-surface outline-none focus:ring-2 focus:ring-accent dark:bg-gray-900 dark:border-gray-600 dark:text-white">
                    <option v-for="opt in exerciseOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
                  </select>
                  <div class="flex-1 flex items-center gap-1.5">
                    <input type="number" step="0.5" v-model="exo.duration_minutes" class="w-full p-2 rounded-md border border-gray-300 bg-surface outline-none focus:ring-2 focus:ring-accent dark:bg-gray-900 dark:border-gray-600 dark:text-white" />
                    <span class="text-gray-600 font-bold dark:text-gray-400">min</span>
                  </div>
                  <button @click="editingExercises.splice(index, 1)" class="bg-red-50 text-red-500 px-3 py-2 rounded-md font-bold cursor-pointer hover:bg-red-100 transition-colors dark:bg-red-900/30 dark:text-red-400 dark:hover:bg-red-900/50">✖</button>
                </div>
                
                <button @click="editingExercises.push({ type: 'trottes', duration_minutes: 1 })" class="w-full p-2.5 mt-1 bg-gray-100 text-gray-700 border border-dashed border-gray-400 rounded-md font-bold cursor-pointer hover:bg-gray-200 transition-colors dark:bg-gray-800 dark:text-gray-300 dark:border-gray-500 dark:hover:bg-gray-700">+ Ajouter une étape</button>
                
                <div class="flex gap-3 mt-5">
                  <button @click="saveSessionExercises(session)" class="flex-1 p-3 bg-primary text-accent rounded-cta font-bold cursor-pointer font-heading uppercase hover:bg-opacity-90 transition-colors">💾 Sauvegarder</button>
                  <button @click="editingSessionId = null" class="flex-1 p-3 bg-gray-400 text-white rounded-cta font-bold cursor-pointer font-heading uppercase hover:bg-gray-500 transition-colors dark:bg-gray-600 dark:hover:bg-gray-500">Annuler</button>
                </div>
              </div>
              
              <!-- Vue lecture seule -->
              <div v-else class="flex flex-wrap gap-2 p-3">
                <span v-for="(exo, i) in session.exercises" :key="i" class="bg-gray-200 text-gray-800 text-xs px-2.5 py-1 rounded-full font-bold capitalize dark:bg-gray-700 dark:text-gray-200">
                  {{ exo.type }} ({{ formatDuration(exo.duration_seconds) }})
                </span>
              </div>
              
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>