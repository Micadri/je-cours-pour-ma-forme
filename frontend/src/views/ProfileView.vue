<script setup>
import { ref, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useProgramStore } from '../stores/program'

const router = useRouter()
const store = useProgramStore()

const firstName = ref('')
const audioEnabled = ref(true)
const theme = ref('light')
const avatarBase64 = ref('')
const saveMessage = ref('')

watch(() => store.userProfile, (newProfile) => {
  if (newProfile) {
    firstName.value = newProfile.first_name || ''
    audioEnabled.value = newProfile.audio_enabled == 1
    theme.value = newProfile.theme || 'light'
    avatarBase64.value = newProfile.avatar || ''
  }
}, { immediate: true })

onMounted(async () => {
  if (!store.userProfile) await store.initApp()
})

const handleFileUpload = (event) => {
  const file = event.target.files[0]
  if (!file) return
  const reader = new FileReader()
  reader.onload = (e) => {
    const img = new Image()
    img.onload = () => {
      const canvas = document.createElement('canvas')
      const ctx = canvas.getContext('2d')
      const MAX_SIZE = 200
      let width = img.width
      let height = img.height
      if (width > height) { if (width > MAX_SIZE) { height *= MAX_SIZE / width; width = MAX_SIZE; } } 
      else { if (height > MAX_SIZE) { width *= MAX_SIZE / height; height = MAX_SIZE; } }
      canvas.width = width
      canvas.height = height
      ctx.drawImage(img, 0, 0, width, height)
      avatarBase64.value = canvas.toDataURL('image/jpeg', 0.8)
    }
    img.src = e.target.result
  }
  reader.readAsDataURL(file)
}

const saveProfile = async () => {
  await store.updateProfile({
    first_name: firstName.value,
    audio_enabled: audioEnabled.value ? 1 : 0,
    theme: theme.value,
    avatar: avatarBase64.value
  })
  saveMessage.value = 'Profil mis à jour !'
  setTimeout(() => saveMessage.value = '', 3000)
}

const showFeedback = ref(false)
const feedbackSubject = ref('bug')
const feedbackMessage = ref('')
const feedbackStatus = ref('')

const sendFeedback = async () => {
  const token = localStorage.getItem('auth_token')
  if (!token) return
  try {
    const res = await fetch('https://cepegra-frontend.xyz/ingrwf13/adrien_ei2/api/runner/feedback.php?token=' + token, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ subject: feedbackSubject.value, message: feedbackMessage.value })
    })
    const data = await res.json()
    if (data.status === 'success') {
      feedbackStatus.value = "Message envoyé, merci !"
      setTimeout(() => { showFeedback.value = false; feedbackStatus.value = ''; feedbackMessage.value = '' }, 2000)
    } else { feedbackStatus.value = data.message }
  } catch (e) { feedbackStatus.value = "Erreur réseau." }
}
</script>

<template>
  <main class="p-5 font-body max-w-[600px] mx-auto">
    <button @click="router.push('/')" class="bg-transparent border-none text-accent text-base font-bold cursor-pointer mb-5">
      ← Retour à l'accueil
    </button>
    
    <h1 class="mt-0 font-heading text-3xl text-primary font-bold mb-6 dark:text-gray-100">Mon Profil</h1>
    
    <div class="bg-surface p-5 rounded-xl border border-gray-200 shadow-sm dark:bg-gray-800 dark:border-gray-700">
      
      <!-- Section Photo -->
      <div class="text-center mb-6">
        <div class="w-[100px] h-[100px] rounded-full bg-gray-200 mx-auto mb-3 overflow-hidden flex items-center justify-center border-4 border-accent dark:bg-gray-700">
          <img v-if="avatarBase64" :src="avatarBase64" class="w-full h-full object-cover" />
          <span v-else class="text-white text-4xl">👤</span>
        </div>
        <label class="bg-gray-100 text-text px-4 py-2 rounded-cta cursor-pointer text-sm inline-block font-bold hover:bg-gray-200 transition-colors dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">
          Changer la photo
          <input type="file" @change="handleFileUpload" accept="image/*" class="hidden" />
        </label>
      </div>

      <!-- Formulaire -->
      <div class="mb-4">
        <label class="block mb-1 font-bold text-gray-700 dark:text-gray-300">Prénom</label>
        <input type="text" v-model="firstName" class="w-full p-2.5 border border-gray-300 rounded-md text-base box-border bg-surface focus:ring-2 focus:ring-accent outline-none dark:bg-gray-900 dark:border-gray-600 dark:text-gray-100" />
      </div>
      
      <div class="mb-4 flex items-center justify-between">
        <label class="font-bold text-gray-700 dark:text-gray-300">Annonces vocales (Coach)</label>
        <input type="checkbox" v-model="audioEnabled" class="scale-150 accent-accent" />
      </div>
      
      <div class="mb-6">
        <label class="block mb-1 font-bold text-gray-700 dark:text-gray-300">Thème de l'application</label>
        <select v-model="theme" class="w-full p-2.5 border border-gray-300 rounded-md text-base bg-surface focus:ring-2 focus:ring-accent outline-none dark:bg-gray-900 dark:border-gray-600 dark:text-gray-100">
          <option value="light">Mode Clair</option>
          <option value="dark">Mode Sombre</option>
        </select>
      </div>
      
      <button @click="saveProfile" class="w-full p-4 bg-primary text-white rounded-cta text-lg font-bold cursor-pointer hover:bg-opacity-90 transition-colors uppercase tracking-wider font-heading">
        Sauvegarder mon profil
      </button>

      <!-- Bouton d'ouverture Feedback -->
      <button @click="showFeedback = true" class="w-full p-3 mt-4 bg-transparent text-gray-600 border border-gray-300 rounded-cta text-base font-bold cursor-pointer hover:bg-gray-50 transition-colors dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700">
        Signaler un bug ou une idée
      </button>

      <!-- Modale de feedback -->
      <div v-if="showFeedback" class="fixed inset-0 bg-black/60 flex items-center justify-center z-50 p-5">
        <div class="bg-surface p-6 rounded-xl w-full max-w-[350px] relative dark:bg-gray-800">
          <button @click="showFeedback = false" class="absolute top-2 right-4 bg-transparent border-none text-2xl cursor-pointer text-gray-500 hover:text-gray-800 dark:hover:text-gray-200">×</button>
          
          <h2 class="mt-0 font-heading text-2xl text-primary font-bold mb-4 dark:text-gray-100">Votre retour</h2>
          
          <select v-model="feedbackSubject" class="w-full p-2.5 mb-4 border border-gray-300 rounded-md bg-surface focus:ring-2 focus:ring-accent outline-none dark:bg-gray-900 dark:border-gray-600 dark:text-gray-100">
            <option value="bug">Signaler un bug 🐛</option>
            <option value="idea">Proposer une idée 💡</option>
            <option value="other">Autre message ✉️</option>
          </select>
          
          <textarea v-model="feedbackMessage" rows="4" placeholder="Expliquez-nous tout..." class="w-full p-2.5 border border-gray-300 rounded-md mb-4 box-border resize-y bg-surface focus:ring-2 focus:ring-accent outline-none dark:bg-gray-900 dark:border-gray-600 dark:text-gray-100"></textarea>
          
          <button @click="sendFeedback" class="w-full p-3 bg-accent text-primary rounded-cta font-bold cursor-pointer hover:bg-opacity-90 transition-colors uppercase tracking-wider font-heading">
            Envoyer
          </button>
          
          <p v-if="feedbackStatus" class="text-center text-accent font-bold mt-3 mb-0">{{ feedbackStatus }}</p>
        </div>
      </div>

      <p v-if="saveMessage" class="text-accent text-center mt-4 font-bold">{{ saveMessage }}</p>
    </div>
  </main>
</template>