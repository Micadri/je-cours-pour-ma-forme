<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useProgramStore } from '../stores/program'

const router = useRouter()
const store = useProgramStore()

const firstName = ref(store.userProfile?.first_name || '')
const audioEnabled = ref(store.userProfile?.audio_enabled == 1)
const theme = ref(store.userProfile?.theme || 'light')
const avatarBase64 = ref(store.userProfile?.avatar || '')
const saveMessage = ref('')

// Compresseur d'image intégré (évite de faire planter la Base de Données)
const handleFileUpload = (event) => {
  const file = event.target.files[0]
  if (!file) return

  const reader = new FileReader()
  reader.onload = (e) => {
    const img = new Image()
    img.onload = () => {
      const canvas = document.createElement('canvas')
      const ctx = canvas.getContext('2d')
      
      // Taille max de la miniature : 200x200
      const MAX_SIZE = 200
      let width = img.width
      let height = img.height

      if (width > height) {
        if (width > MAX_SIZE) { height *= MAX_SIZE / width; width = MAX_SIZE; }
      } else {
        if (height > MAX_SIZE) { width *= MAX_SIZE / height; height = MAX_SIZE; }
      }
      
      canvas.width = width
      canvas.height = height
      ctx.drawImage(img, 0, 0, width, height)
      
      // Conversion en Base64 allégé (JPEG qualité 80%)
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
    const res = await fetch('[https://cepegra-frontend.xyz/api/runner/feedback.php?token=](https://cepegra-frontend.xyz/api/runner/feedback.php?token=)' + token, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ subject: feedbackSubject.value, message: feedbackMessage.value })
    })
    const data = await res.json()
    
    if (data.status === 'success') {
      feedbackStatus.value = "Message envoyé, merci !"
      setTimeout(() => { 
        showFeedback.value = false
        feedbackStatus.value = ''
        feedbackMessage.value = ''
      }, 2000)
    } else {
      feedbackStatus.value = data.message
    }
  } catch (e) {
    feedbackStatus.value = "Erreur réseau."
  }
}
</script>

<template>
  <main style="padding: 20px; font-family: sans-serif; max-width: 600px; margin: 0 auto;">
    <button @click="router.push('/')" style="background: none; border: none; color: #4CAF50; font-size: 16px; font-weight: bold; cursor: pointer; margin-bottom: 20px;">
      ← Retour à l'accueil
    </button>
    
    <h1 style="margin-top: 0;">Mon Profil</h1>

    <div style="background: #f4f4f4; padding: 20px; border-radius: 8px;">
      
      <!-- Section Photo -->
      <div style="text-align: center; margin-bottom: 25px;">
        <div style="width: 100px; height: 100px; border-radius: 50%; background: #ccc; margin: 0 auto 10px; overflow: hidden; display: flex; align-items: center; justify-content: center; border: 3px solid #4CAF50;">
          <img v-if="avatarBase64" :src="avatarBase64" style="width: 100%; height: 100%; object-fit: cover;" />
          <span v-else style="color: white; font-size: 40px;">🏃</span>
        </div>
        
        <!-- Le vrai input file est caché, on utilise un label stylisé à la place -->
        <label style="background: #e0e0e0; color: #333; padding: 8px 15px; border-radius: 5px; cursor: pointer; font-size: 14px; display: inline-block; font-weight: bold;">
          Changer la photo
          <input type="file" @change="handleFileUpload" accept="image/*" style="display: none;" />
        </label>
      </div>

      <!-- Formulaire -->
      <div style="margin-bottom: 15px;">
        <label style="display: block; margin-bottom: 5px; font-weight: bold; color: #333;">Prénom</label>
        <input type="text" v-model="firstName" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; font-size: 16px; box-sizing: border-box;" />
      </div>

      <div style="margin-bottom: 15px; display: flex; align-items: center; justify-content: space-between;">
        <label style="font-weight: bold; color: #333;">Annonces vocales (Coach)</label>
        <input type="checkbox" v-model="audioEnabled" style="transform: scale(1.5);" />
      </div>

      <div style="margin-bottom: 25px;">
        <label style="display: block; margin-bottom: 5px; font-weight: bold; color: #333;">Thème de l'application</label>
        <select v-model="theme" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; font-size: 16px; background: white;">
          <option value="light">Mode Clair</option>
          <option value="dark">Mode Sombre</option>
        </select>
      </div>

      <button @click="saveProfile" style="width: 100%; padding: 15px; background: #4CAF50; color: white; border: none; border-radius: 8px; font-size: 18px; font-weight: bold; cursor: pointer;">
        Sauvegarder mon profil
      </button>
      <!-- Bouton d'ouverture -->
      <button @click="showFeedback = true" style="width: 100%; padding: 12px; margin-top: 15px; background: transparent; color: #666; border: 1px solid #ccc; border-radius: 8px; font-size: 16px; font-weight: bold; cursor: pointer;">
        💡 Signaler un bug ou une idée
      </button>

      <!-- Modale de feedback -->
      <div v-if="showFeedback" style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.6); display: flex; align-items: center; justify-content: center; z-index: 1000; padding: 20px;">
        <div style="background: white; padding: 25px; border-radius: 12px; width: 100%; max-width: 350px; position: relative;">
          <button @click="showFeedback = false" style="position: absolute; top: 10px; right: 15px; background: none; border: none; font-size: 20px; cursor: pointer; color: #888;">✕</button>
          
          <h2 style="margin-top: 0; color: #333;">Votre retour</h2>
          
          <select v-model="feedbackSubject" style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 5px;">
            <option value="bug">Signaler un bug 🐛</option>
            <option value="idea">Proposer une idée 💡</option>
            <option value="other">Autre message 💬</option>
          </select>

          <textarea v-model="feedbackMessage" rows="4" placeholder="Expliquez-nous tout..." style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; margin-bottom: 15px; box-sizing: border-box; resize: vertical;"></textarea>
          
          <button @click="sendFeedback" style="width: 100%; padding: 12px; background: #4CAF50; color: white; border: none; border-radius: 8px; font-weight: bold; cursor: pointer;">
            Envoyer
          </button>
          
          <p v-if="feedbackStatus" style="text-align: center; color: #4CAF50; font-weight: bold; margin-bottom: 0;">
            {{ feedbackStatus }}
          </p>
        </div>
      </div>
      <p v-if="saveMessage" style="color: #4CAF50; text-align: center; margin-top: 15px; font-weight: bold;">
        {{ saveMessage }}
      </p>
    </div>
  </main>
</template>