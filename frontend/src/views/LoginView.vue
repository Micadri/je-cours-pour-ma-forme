<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'

const email = ref('')
const password = ref('')
const error = ref('')
const isRegistering = ref(false)
const router = useRouter()

const handleSubmit = async () => {
  const endpoint = isRegistering.value ? 'register.php' : 'login.php'
  try {
    const res = await fetch(`http://localhost:8888/je-cours-pour-ma-forme/api/auth/${endpoint}`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ email: email.value, password: password.value })
    })
    const data = await res.json()
    
    if (data.status === 'success') {
      localStorage.setItem('auth_token', data.token)
      router.push('/')
    } else {
      error.value = data.message
    }
  } catch (err) {
    error.value = 'Erreur de communication avec le serveur'
  }
}
</script>

<template>
  <main style="padding: 20px; font-family: sans-serif; max-width: 400px; margin: 100px auto; text-align: center;">
    <h1>{{ isRegistering ? 'Créer un compte' : 'Connexion' }}</h1>
    <form @submit.prevent="handleSubmit" style="display: flex; flex-direction: column; gap: 15px;">
      <input v-model="email" type="email" placeholder="Email" required style="padding: 10px; font-size: 16px;" />
      <input v-model="password" type="password" placeholder="Mot de passe" required style="padding: 10px; font-size: 16px;" />
      <button type="submit" style="padding: 15px; background: #4CAF50; color: white; border: none; font-size: 16px; cursor: pointer;">
        {{ isRegistering ? "S'inscrire" : 'Se connecter' }}
      </button>
    </form>
    <p v-if="error" style="color: red; margin-top: 15px;">{{ error }}</p>
    
    <button @click="isRegistering = !isRegistering" style="margin-top: 20px; background: none; border: none; color: #007BFF; cursor: pointer; text-decoration: underline;">
      {{ isRegistering ? 'Déjà un compte ? Connecte-toi' : "Pas de compte ? Inscris-toi" }}
    </button>
  </main>
</template>