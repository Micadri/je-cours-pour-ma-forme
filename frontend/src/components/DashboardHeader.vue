<script setup>
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import { useProgramStore } from '../stores/program'

const router = useRouter()
const store = useProgramStore()
const isGuest = computed(() => !localStorage.getItem('auth_token'))

const handleLogout = () => {
  store.logout()
  window.location.href = '/welcome'
}
</script>

<template>
  <div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 15px; margin-bottom: 25px; padding: 15px; background: #fff; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
    <div style="display: flex; align-items: center; gap: 15px; flex: 1; min-width: 200px;">
      <div style="width: 50px; height: 50px; border-radius: 50%; background: #ccc; overflow: hidden; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
        <img v-if="store.userProfile?.avatar" :src="store.userProfile.avatar" style="width: 100%; height: 100%; object-fit: cover;" />
        <span v-else style="color: white; font-size: 20px;">👤</span>
      </div>
      <h2 style="margin: 0; font-size: 1.2rem; color: #333; line-height: 1.2;">
        Bonjour, <br/><span style="color: #4CAF50;">{{ store.userProfile?.first_name || 'Coureur' }}</span> 
        <span v-if="isGuest" style="font-size: 0.8rem; color: #888; font-weight: normal; margin-left: 5px;">(Invité)</span>
      </h2>
    </div>
    
    <div v-if="!isGuest" style="display: flex; gap: 10px; flex-shrink: 0;">
      <button @click="router.push('/profile')" style="padding: 8px 15px; background: #e0e0e0; color: #333; border: none; border-radius: 8px; cursor: pointer; font-size: 14px; font-weight: bold;">Profil</button>
      <button @click="handleLogout" style="padding: 8px 15px; background: transparent; color: #f44336; border: 1px solid #f44336; border-radius: 8px; cursor: pointer; font-size: 14px; font-weight: bold;">Déconnexion</button>
    </div>
    <div v-else style="display: flex; gap: 10px; flex-shrink: 0;">
      <button @click="router.push('/login')" style="padding: 8px 15px; background: transparent; color: #4CAF50; border: 1px solid #4CAF50; border-radius: 8px; cursor: pointer; font-size: 14px; font-weight: bold;">Se connecter</button>
      <button @click="router.push('/register')" style="padding: 8px 15px; background: #4CAF50; color: white; border: none; border-radius: 8px; cursor: pointer; font-size: 14px; font-weight: bold;">S'inscrire</button>
    </div>
  </div>
</template>