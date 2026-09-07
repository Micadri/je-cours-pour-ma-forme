<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const step = ref(0)

const slides = [
  {
    icon: '🏃',
    title: 'De 0 à 5 km',
    text: 'Un programme progressif conçu pour vous amener à courir 5 kilomètres sans vous arrêter, à votre rythme.'
  },
  {
    icon: '🌲',
    title: 'Courez hors-ligne',
    text: 'Pas de réseau en forêt ? Aucun problème. L\'application synchronisera vos exploits dès votre retour.'
  },
  {
    icon: '🎧',
    title: 'Coach vocal intégré',
    text: 'Laissez-vous guider par les annonces sonores pendant l\'effort. Prêt à relever le défi ?'
  }
]

const nextStep = () => {
  if (step.value < slides.length - 1) step.value++
}
</script>

<template>
  <main style="display: flex; flex-direction: column; justify-content: center; align-items: center; min-height: 70vh; padding: 20px; font-family: sans-serif; text-align: center; max-width: 600px; margin: 0 auto;">

    <!-- Contenu principal -->
    <div style="display: flex; flex-direction: column; align-items: center; margin-bottom: 30px;">
      <div style="font-size: 80px; margin-bottom: 15px;">{{ slides[step].icon }}</div>
      <h1 style="color: #4CAF50; margin: 0 0 15px 0; font-size: 2rem;">{{ slides[step].title }}</h1>
      <p style="color: #555; font-size: 1.1rem; line-height: 1.5; max-width: 350px; margin: 0;">
        {{ slides[step].text }}
      </p>
    </div>

    <!-- Indicateurs de progression -->
    <div style="display: flex; gap: 10px; margin-bottom: 35px;">
      <div v-for="(slide, index) in slides" :key="index"
           :style="{ width: '12px', height: '12px', borderRadius: '50%', background: step === index ? '#4CAF50' : '#ddd', transition: 'background 0.3s' }">
      </div>
    </div>

    <!-- Zone des boutons -->
    <div style="width: 100%; max-width: 320px;">
      <button v-if="step < slides.length - 1" @click="nextStep" style="width: 100%; padding: 15px; background: #4CAF50; color: white; border: none; border-radius: 8px; font-size: 18px; font-weight: bold; cursor: pointer;">
        Suivant
      </button>
      <div v-else style="display: flex; flex-direction: column; gap: 15px;">
        <button @click="router.push('/register')" style="width: 100%; padding: 15px; background: #4CAF50; color: white; border: none; border-radius: 8px; font-size: 18px; font-weight: bold; cursor: pointer;">
          Commencer l'aventure
        </button>
        <button @click="router.push('/login')" style="width: 100%; padding: 15px; background: transparent; color: #4CAF50; border: 2px solid #4CAF50; border-radius: 8px; font-size: 18px; font-weight: bold; cursor: pointer;">
          J'ai déjà un compte
        </button>
      </div>
    </div>

  </main>
</template>