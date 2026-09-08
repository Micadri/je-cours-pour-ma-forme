<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useProgramStore } from '../stores/program'

const firstName = ref('')
const router = useRouter()
const store = useProgramStore()

const email = ref('')
const password = ref('')
const errorMessage = ref('')

const handleRegister = async () => {
  errorMessage.value = ''
  try {
    const res = await fetch('https://cepegra-frontend.xyz/ingrwf13/adrien_ei2/api/auth/register.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ email: email.value, password: password.value, first_name: firstName.value })
    })
    
    const data = await res.json()
    
    if (data.status === 'success') {
      localStorage.setItem('auth_token', data.token)
      await store.initApp()
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
  <main class="flex flex-col justify-center items-center min-h-[80vh] p-5 font-body">
    
    <div class="w-full max-w-[400px] mb-4">
      <button @click="router.push('/welcome')" class="bg-transparent border-none text-accent text-base font-bold cursor-pointer p-0 hover:opacity-80 transition-opacity">
        ← Retour à la présentation
      </button>
    </div>

    <div class="bg-surface p-8 rounded-xl shadow-md w-full max-w-[400px] border border-gray-200 dark:bg-gray-800 dark:border-gray-700">
      <h1 class="text-center text-3xl text-primary font-heading font-bold mt-0 mb-6 dark:text-gray-100">Inscription</h1>
      
      <p v-if="errorMessage" class="text-red-600 bg-red-50 p-3 rounded-md text-center text-sm font-bold mb-4 dark:bg-red-900/30 dark:text-red-400 border border-red-200 dark:border-red-900/50">
        {{ errorMessage }}
      </p>

      <form @submit.prevent="handleRegister" class="flex flex-col gap-4">
        <div>
          <label class="block mb-1.5 text-gray-700 font-bold dark:text-gray-300">Prénom</label>
          <input type="text" v-model="firstName" required class="w-full p-2.5 border border-gray-300 rounded-md bg-surface text-text focus:ring-2 focus:ring-accent outline-none dark:bg-gray-900 dark:border-gray-600 dark:text-gray-100" />
        </div>
        
        <div>
          <label class="block mb-1.5 text-gray-700 font-bold dark:text-gray-300">Email</label>
          <input type="email" v-model="email" required class="w-full p-2.5 border border-gray-300 rounded-md bg-surface text-text focus:ring-2 focus:ring-accent outline-none dark:bg-gray-900 dark:border-gray-600 dark:text-gray-100" />
        </div>
        
        <div>
          <label class="block mb-1.5 text-gray-700 font-bold dark:text-gray-300">Mot de passe</label>
          <input type="password" v-model="password" required class="w-full p-2.5 border border-gray-300 rounded-md bg-surface text-text focus:ring-2 focus:ring-accent outline-none dark:bg-gray-900 dark:border-gray-600 dark:text-gray-100" />
        </div>

        <button type="submit" class="w-full p-3 mt-2 bg-primary text-accent rounded-cta text-lg font-bold cursor-pointer hover:bg-opacity-90 transition-colors uppercase tracking-wider font-heading">
          Créer mon compte
        </button>

        <p class="text-center text-sm text-gray-600 mt-4 mb-0 dark:text-gray-400">
          Déjà un compte ? 
          <router-link to="/login" class="text-accent font-bold no-underline hover:opacity-80">Se connecter</router-link>
        </p>
      </form>
    </div>
  </main>
</template>