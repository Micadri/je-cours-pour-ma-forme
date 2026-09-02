<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const email = ref('')
const password = ref('')
const errorMessage = ref('')

const handleLogin = async () => {
  errorMessage.value = ''
  try {
    const res = await fetch('[https://cepegra-frontend.xyz/ingrwf13/adrien_ei2/api/auth/login.php](https://cepegra-frontend.xyz/ingrwf13/adrien_ei2/api/auth/login.php)', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ email: email.value, password: password.value })
    })
    
    const data = await res.json()
    
    if (data.status === 'success') {
      localStorage.setItem('auth_token', data.token)
      router.push('/')
    } else {
      errorMessage.value = data.message || 'Erreur de connexion'
    }
  } catch (error) {
    errorMessage.value = 'Erreur réseau. Impossible de joindre le serveur.'
  }
}
</script>

<template>
  <main style="display: flex; justify-content: center; align-items: center; min-height: 80vh; padding: 20px; font-family: sans-serif;">
    <div style="background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); width: 100%; max-width: 400px;">
      <h1 style="text-align: center; color: #333; margin-top: 0;">Connexion</h1>
      
      <p v-if="errorMessage" style="color: #d32f2f; background: #ffebee; padding: 10px; border-radius: 5px; text-align: center; font-size: 14px;">
        {{ errorMessage }}
      </p>
<p style="text-align: center; font-size: 14px; color: #666; margin-top: 15px;">
          Pas encore de compte ? 
          <router-link to="/register" style="color: #4CAF50; text-decoration: none; font-weight: bold;">S'inscrire</router-link>
        </p>
      <form @submit.prevent="handleLogin" style="display: flex; flex-direction: column; gap: 15px; margin-top: 20px;">
        <div>
          <label style="display: block; margin-bottom: 5px; color: #555; font-weight: bold;">Email</label>
          <input type="email" v-model="email" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; font-size: 16px; box-sizing: border-box;" />
        </div>
        
        <div>
          <label style="display: block; margin-bottom: 5px; color: #555; font-weight: bold;">Mot de passe</label>
          <input type="password" v-model="password" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; font-size: 16px; box-sizing: border-box;" />
        </div>

        <button type="submit" style="padding: 12px; background: #4CAF50; color: white; border: none; border-radius: 5px; font-size: 16px; font-weight: bold; cursor: pointer; margin-top: 10px;">
          Se connecter
        </button>
      </form>
    </div>
  </main>
</template>