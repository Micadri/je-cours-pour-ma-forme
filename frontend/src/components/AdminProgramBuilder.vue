<script setup>
import { ref } from 'vue'
import { useProgramStore } from '../stores/program'

const props = defineProps(['allSeasons'])
const emit = defineEmits(['refresh-seasons'])
const store = useProgramStore()

const API_BASE = 'https://cepegra-frontend.xyz/ingrwf13/adrien_ei2/api'

// UI States
const newSeasonTitle = ref('Saison 2 - Objectif 10 km')
const newSeasonWeeks = ref(12)
const newSeasonSessions = ref(3)
const isGenerating = ref(false)
const generateMessage = ref('')
const selectedSeasonForDetails = ref(null)
const seasonDetails = ref([])
const isSeasonDetailsLoading = ref(false)
const editingSessionId = ref(null)
const editingExercises = ref([])

const exerciseOptions = [
  { value: 'echauffement', label: 'Échauffement' },
  { value: 'marches', label: 'Marche' },
  { value: 'trottes', label: 'Trotte / Course lente' },
  { value: 'cours', label: 'Course rapide' },
  { value: 'sprints', label: 'Sprint' },
  { value: 'deboules', label: 'Déboulés' },
  { value: 'etirements', label: 'Étirements' }
]

const formatDuration = (seconds) => {
  const m = Math.floor(seconds / 60)
  const s = seconds % 60
  return s > 0 ? `${m}m ${s}s` : `${m} min`
}

const generateProgram = async () => {
  if (!confirm(`Générer ${newSeasonWeeks.value * newSeasonSessions.value} entraînements procéduraux ?`)) return
  isGenerating.value = true
  generateMessage.value = ''
  
  const token = localStorage.getItem('auth_token')
  try {
    const res = await fetch(`${API_BASE}/admin/users.php?action=generate_season`, {
      method: 'POST',
      headers: { 'Authorization': `Bearer ${token}`, 'Content-Type': 'application/json' },
      body: JSON.stringify({ title: newSeasonTitle.value, weeks: newSeasonWeeks.value, sessionsPerWeek: newSeasonSessions.value })
    })
    const data = await res.json()
    if (data.status === 'success') {
      generateMessage.value = "✅ Programme généré !"
      await store.initApp()
      emit('refresh-seasons')
    } else {
      generateMessage.value = "❌ Erreur : " + data.message
    }
  } catch (e) {
    generateMessage.value = "❌ Erreur réseau."
  } finally {
    isGenerating.value = false
  }
}

const editSeason = async (season) => {
  const newTitle = prompt("Nouveau nom :", season.title)
  if (!newTitle || newTitle === season.title) return
  const token = localStorage.getItem('auth_token')
  try {
    const res = await fetch(`${API_BASE}/admin/users.php?action=update_season`, {
      method: 'POST',
      headers: { 'Authorization': `Bearer ${token}`, 'Content-Type': 'application/json' },
      body: JSON.stringify({ id: season.id, title: newTitle })
    })
    const data = await res.json()
    if (data.status === 'success') {
      season.title = newTitle
      await store.initApp()
    }
  } catch (e) { alert("Erreur réseau") }
}

const deleteSeason = async (id) => {
  if (!confirm("⚠️ Cela va supprimer définitivement toute la saison. Continuer ?")) return
  const token = localStorage.getItem('auth_token')
  try {
    const res = await fetch(`${API_BASE}/admin/users.php?action=delete_season&id=${id}`, {
      headers: { 'Authorization': `Bearer ${token}` }
    })
    const data = await res.json()
    if (data.status === 'success') {
      if (selectedSeasonForDetails.value?.id === id) selectedSeasonForDetails.value = null
      await store.initApp()
      emit('refresh-seasons')
    }
  } catch (e) { alert("Erreur réseau") }
}

const viewSeasonDetails = async (season) => {
  selectedSeasonForDetails.value = season
  isSeasonDetailsLoading.value = true
  const token = localStorage.getItem('auth_token')
  try {
    const res = await fetch(`${API_BASE}/admin/users.php?action=get_season_details&id=${season.id}`, {
      headers: { 'Authorization': `Bearer ${token}` }
    })
    const data = await res.json()
    if (data.status === 'success') { seasonDetails.value = data.data }
  } catch (e) { alert("Erreur") } finally {
    isSeasonDetailsLoading.value = false
  }
}

const openSessionEditor = (session) => {
  editingSessionId.value = session.id
  editingExercises.value = session.exercises.map(exo => ({ type: exo.type, duration_minutes: Number((exo.duration_seconds / 60).toFixed(2)) }))
}

const saveSessionExercises = async (session) => {
  const token = localStorage.getItem('auth_token')
  const payload = {
    session_id: session.id,
    exercises: editingExercises.value.map(exo => ({ type: exo.type, duration_seconds: Math.round(exo.duration_minutes * 60) }))
  }
  try {
    const res = await fetch(`${API_BASE}/admin/users.php?action=update_session_exercises`, {
      method: 'POST',
      headers: { 'Authorization': `Bearer ${token}`, 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    })
    const data = await res.json()
    if (data.status === 'success') {
      session.exercises = payload.exercises.map((exo, i) => ({ ...exo, order_num: i + 1 }))
      editingSessionId.value = null
      await store.initApp()
      alert("Entraînement mis à jour !")
    }
  } catch (e) { alert("Erreur") }
}

const moveExercise = (idx, dir) => {
  if (idx + dir >= 0 && idx + dir < editingExercises.value.length) {
    const temp = editingExercises.value[idx]
    editingExercises.value[idx] = editingExercises.value[idx + dir]
    editingExercises.value[idx + dir] = temp
  }
}
</script>

<template>
  <div>
    <!-- Vue Générale -->
    <div v-if="!selectedSeasonForDetails">
      <div style="background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); margin-bottom: 25px;">
        <h2 style="color: #4CAF50; margin-top: 0; border-bottom: 1px solid #eee; padding-bottom: 10px;">Saisons Existantes</h2>
        <div v-if="allSeasons.length === 0" style="text-align: center; color: #888; padding: 20px;">Aucune saison.</div>
        <div v-else style="display: flex; flex-direction: column; gap: 10px;">
          <div v-for="season in allSeasons" :key="season.id" style="display: flex; justify-content: space-between; align-items: center; padding: 15px; background: #fafafa; border: 1px solid #eee; border-radius: 8px;">
            <div>
              <strong style="font-size: 1.1rem; color: #333;">{{ season.title }}</strong>
              <div style="font-size: 0.85rem; color: #666; margin-top: 4px;">{{ season.weeks_count }} semaines paramétrées</div>
            </div>
            <div style="display: flex; gap: 10px; flex-wrap: wrap;">
              <button @click="viewSeasonDetails(season)" style="padding: 6px 12px; background: #e38734; color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: bold;">👁️ Gérer les blocs</button>
              <button @click="editSeason(season)" style="padding: 6px 12px; background: #2196F3; color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: bold;">✏️ Renommer</button>
              <button @click="deleteSeason(season.id)" style="padding: 6px 12px; background: #f44336; color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: bold;">🗑️ Supprimer</button>
            </div>
          </div>
        </div>
      </div>

      <div style="background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.05);">
        <div style="text-align: center; border-bottom: 1px solid #eee; padding-bottom: 20px; margin-bottom: 25px;">
          <h2 style="color: #4CAF50; margin-top: 0; margin-bottom: 10px;">Générateur Automatique</h2>
          <p style="color: #666; font-size: 0.95rem; margin: 0;">Générez une base de travail procédurale avant de l'éditer bloc par bloc.</p>
        </div>
        <div style="display: flex; flex-direction: column; gap: 15px; max-width: 500px; margin: 0 auto;">
          <div><label style="display: block; margin-bottom: 5px; font-weight: bold; color: #333;">Titre</label> <input type="text" v-model="newSeasonTitle" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px;" /></div>
          <div style="display: flex; gap: 15px;">
            <div style="flex: 1;"><label style="display: block; margin-bottom: 5px; font-weight: bold; color: #333;">Semaines</label> <input type="number" v-model="newSeasonWeeks" min="1" max="52" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px;" /></div>
            <div style="flex: 1;"><label style="display: block; margin-bottom: 5px; font-weight: bold; color: #333;">Séances</label> <input type="number" v-model="newSeasonSessions" min="1" max="7" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px;" /></div>
          </div>
          <button @click="generateProgram" :disabled="isGenerating" style="width: 100%; padding: 15px; margin-top: 10px; background: #e38734; color: white; border: none; border-radius: 8px; font-weight: bold; font-size: 16px; cursor: pointer;">{{ isGenerating ? 'Génération en cours...' : '⚡ Générer la Saison' }}</button>
          <p v-if="generateMessage" style="text-align: center; font-weight: bold; margin-top: 10px;" :style="{ color: generateMessage.includes('❌') ? '#d32f2f' : '#4CAF50' }">{{ generateMessage }}</p>
        </div>
      </div>
    </div>

    <!-- Vue Constructeur de Blocs -->
    <div v-else>
      <button @click="selectedSeasonForDetails = null" style="margin-bottom: 15px; background: none; border: none; color: #4CAF50; font-size: 16px; font-weight: bold; cursor: pointer; padding: 0;">← Retour aux saisons</button>
      <h2 style="color: #333; margin-top: 0;">Paramétrage : <span style="color: #4CAF50;">{{ selectedSeasonForDetails.title }}</span></h2>
      
      <div v-if="isSeasonDetailsLoading" style="text-align: center; padding: 40px; color: #888;">Chargement de l'architecture...</div>
      <div v-else style="display: flex; flex-direction: column; gap: 20px;">
        <div v-for="week in seasonDetails" :key="week.id" style="background: white; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); overflow: hidden;">
          <div style="background: #6e757b; color: white; padding: 10px 15px; font-weight: bold; font-size: 1.1rem;">{{ week.title }}</div>
          <div style="padding: 15px; display: flex; flex-direction: column; gap: 15px;">
            <div v-for="session in week.sessions" :key="session.id" style="border: 1px solid #eee; border-radius: 8px; background: #fafafa;">
              <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px 15px; background: #fff; border-radius: 8px;">
                <strong style="color: #e38734; font-size: 1.05rem;">{{ session.title }}</strong>
                <button v-if="editingSessionId !== session.id" @click="openSessionEditor(session)" style="padding: 6px 15px; background: #4CAF50; color: white; border: none; border-radius: 5px; font-weight: bold; cursor: pointer;">⚙️ Configurer</button>
              </div>

              <div v-if="editingSessionId === session.id" style="padding: 15px; border-top: 1px solid #eee;">
                <div v-for="(exo, index) in editingExercises" :key="index" style="display: flex; gap: 10px; align-items: center; margin-bottom: 10px; background: white; padding: 10px; border-radius: 5px; border: 1px solid #ddd;">
                  <div style="display: flex; flex-direction: column; gap: 2px;">
                    <button @click="moveExercise(index, -1)" :disabled="index === 0" style="background: none; border: none; cursor: pointer; color: #888; font-size: 16px;">▲</button>
                    <button @click="moveExercise(index, 1)" :disabled="index === editingExercises.length - 1" style="background: none; border: none; cursor: pointer; color: #888; font-size: 16px;">▼</button>
                  </div>
                  <select v-model="exo.type" style="flex: 2; padding: 8px; border-radius: 5px; border: 1px solid #ccc;">
                    <option v-for="opt in exerciseOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
                  </select>
                  <div style="flex: 1; display: flex; align-items: center; gap: 5px;">
                    <input type="number" step="0.5" v-model="exo.duration_minutes" style="width: 100%; padding: 8px; border-radius: 5px; border: 1px solid #ccc;" />
                    <span style="color: #666; font-weight: bold;">min</span>
                  </div>
                  <button @click="editingExercises.splice(index, 1)" style="background: #ffebee; border: none; color: #f44336; padding: 8px 12px; border-radius: 5px; cursor: pointer; font-weight: bold;">✖</button>
                </div>
                <button @click="editingExercises.push({ type: 'trottes', duration_minutes: 1 })" style="width: 100%; padding: 10px; margin-top: 5px; background: #e0e0e0; border: 1px dashed #aaa; border-radius: 5px; font-weight: bold; cursor: pointer;">+ Ajouter une étape</button>
                <div style="display: flex; gap: 10px; margin-top: 20px;">
                  <button @click="saveSessionExercises(session)" style="flex: 1; padding: 12px; background: #4CAF50; color: white; border: none; border-radius: 5px; font-weight: bold; cursor: pointer;">💾 Sauvegarder</button>
                  <button @click="editingSessionId = null" style="flex: 1; padding: 12px; background: #9e9e9e; color: white; border: none; border-radius: 5px; font-weight: bold; cursor: pointer;">Annuler</button>
                </div>
              </div>
              
              <div v-else style="padding: 10px 15px;">
                <div style="display: flex; flex-wrap: wrap; gap: 5px;">
                  <span v-for="(exo, i) in session.exercises" :key="i" style="background: #e0e0e0; color: #333; font-size: 0.8rem; padding: 4px 8px; border-radius: 12px; font-weight: bold;">{{ exo.type }} ({{ formatDuration(exo.duration_seconds) }})</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>