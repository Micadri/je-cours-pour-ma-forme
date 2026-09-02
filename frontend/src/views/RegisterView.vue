<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'

const firstName = ref('')
const router = useRouter()
const email = ref('')
const password = ref('')
const errorMessage = ref('')

const handleRegister = async () => {
  errorMessage.value = ''
  try {
    const res = await fetch('http://localhost:8888/je-cours-pour-ma-forme/api/auth/register.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ email: email.value, password: password.value, first_name: firstName.value })
    })
    
    const data = await res.json()
    
    if (data.status === 'success') {
      // Sauvegarde du token et redirection immédiate vers le tableau de bord
      localStorage.setItem('auth_token', data.token)
      router.push('/')
    } else {
      errorMessage.value = data.message || 'Erreur lors de l\'inscription'
    }
  } catch (error) {
    errorMessage.value = 'Erreur réseau.'
  }
}
</script>

<template>
  <main style="display: flex; justify-content: center; align-items: center; min-height: 80vh; padding: 20px; font-family: sans-serif;">
    <div style="background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); width: 100%; max-width: 400px;">
      <h1 style="text-align: center; color: #333; margin-top: 0;">Inscription</h1>
      
      <p v-if="errorMessage" style="color: #d32f2f; background: #ffebee; padding: 10px; border-radius: 5px; text-align: center; font-size: 14px;">
        {{ errorMessage }}
      </p>
<!-- Dans ton template, juste avant le champ Email -->
<div>
  <label style="display: block; margin-bottom: 5px; color: #555; font-weight: bold;">Prénom</label>
  <input type="text" v-model="firstName" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; font-size: 16px; box-sizing: border-box;" />
</div>
      <form @submit.prevent="handleRegister" style="display: flex; flex-direction: column; gap: 15px; margin-top: 20px;">
        <div>
          <label style="display: block; margin-bottom: 5px; color: #555; font-weight: bold;">Email</label>
          <input type="email" v-model="email" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; font-size: 16px; box-sizing: border-box;" />
        </div>
        
        <div>
          <label style="display: block; margin-bottom: 5px; color: #555; font-weight: bold;">Mot de passe</label>
          <input type="password" v-model="password" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; font-size: 16px; box-sizing: border-box;" />
        </div>

        <button type="submit" style="padding: 12px; background: #4CAF50; color: white; border: none; border-radius: 5px; font-size: 16px; font-weight: bold; cursor: pointer; margin-top: 10px;">
          Créer mon compte
        </button>

        <p style="text-align: center; font-size: 14px; color: #666; margin-top: 10px;">
          Déjà un compte ? 
          <router-link to="/login" style="color: #4CAF50; text-decoration: none; font-weight: bold;">Se connecter</router-link>
        </p>
      </form>
    </div>
  </main>
</template>