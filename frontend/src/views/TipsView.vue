<script setup>
import { useRouter } from 'vue-router'
const router = useRouter()

const tools = [
  { id: 'echauffement', title: 'Échauffement', desc: 'Préparer la machine avant l\'effort', icon: '🔥' },
  { id: 'posture', title: 'Posture de course', desc: 'Courir grand et détendu', icon: '🏃' },
  { id: 'hydratation', title: 'Hydratation', desc: 'L\'eau, votre carburant essentiel', icon: '💧' },
  { id: 'recuperation', title: 'Récupération', desc: 'S\'étirer et redescendre en douceur', icon: '🧘' }
]

const openTip = (id) => {
  // Tu pourras remplacer cette alerte par un router.push(`/tips/${id}`) plus tard
  alert(`La page détaillée pour "${id}" sera bientôt disponible !`)
}
</script>

<template>
  <main style="padding: 20px; font-family: sans-serif; max-width: 600px; margin: 0 auto;">
    <button @click="router.push('/')" style="background: none; border: none; color: #4CAF50; font-size: 16px; font-weight: bold; cursor: pointer; margin-bottom: 20px;">
      ← Retour à l'accueil
    </button>
    
    <h1 style="margin-top: 0; margin-bottom: 25px;">La Boîte à outils</h1>

    <div style="display: flex; flex-direction: column; gap: 15px;">
      <div v-for="tool in tools" :key="tool.id" @click="openTip(tool.id)"
           style="background: #ffffff; border: 1px solid #eee; border-radius: 12px; padding: 15px 20px; display: flex; align-items: center; justify-content: space-between; cursor: pointer; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
        <div style="display: flex; align-items: center; gap: 15px;">
          <div style="font-size: 28px;">{{ tool.icon }}</div>
          <div>
            <div style="font-weight: bold; font-size: 1.1rem; color: #333;">{{ tool.title }}</div>
            <div style="color: #777; font-size: 0.9rem; margin-top: 4px;">{{ tool.desc }}</div>
          </div>
        </div>
        <div style="color: #4CAF50; font-weight: bold; font-size: 1.5rem;">›</div>
      </div>
    </div>
  </main>
</template>