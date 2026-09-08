<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const step = ref(0)
const slides = [
  { icon: '🎯', title: 'De 0 à 5 km', text: 'Un programme progressif conçu pour vous amener à courir 5 kilomètres sans vous arrêter, à votre rythme.' },
  { icon: '🌲', title: 'Courez hors-ligne', text: 'Pas de réseau en forêt ? Aucun problème. L\'application synchronisera vos exploits dès votre retour.' },
  { icon: '🎧', title: 'Coach vocal intégré', text: 'Laissez-vous guider par les annonces sonores pendant l\'effort. Prêt à relever le défi ?' }
]

const nextStep = () => { if (step.value < slides.length - 1) step.value++ }
const continueAsGuest = () => { localStorage.setItem('guest_mode', 'true'); router.push('/') }
</script>

<template>
  <main class="flex flex-col justify-center items-center min-h-[70vh] p-5 font-body text-center max-w-[600px] mx-auto">
    <!-- Contenu principal -->
    <div class="flex flex-col items-center mb-8">
      <div class="text-7xl mb-4">{{ slides[step].icon }}</div>
      <h1 class="text-accent m-0 mb-4 text-3xl font-heading font-bold">{{ slides[step].title }}</h1>
      <p class="text-gray-600 text-lg leading-relaxed max-w-[350px] m-0 dark:text-gray-300">
        {{ slides[step].text }}
      </p>
    </div>

    <!-- Indicateurs de progression -->
    <div class="flex gap-2.5 mb-8">
      <div v-for="(slide, index) in slides" :key="index" class="w-3 h-3 rounded-full transition-colors duration-300" :class="step === index ? 'bg-accent' : 'bg-gray-300 dark:bg-gray-600'"></div>
    </div>

    <!-- Zone des boutons -->
    <div class="w-full max-w-[320px]">
      <button v-if="step < slides.length - 1" @click="nextStep" class="w-full p-4 bg-primary text-white rounded-cta text-lg font-bold cursor-pointer uppercase tracking-wider font-heading hover:bg-opacity-90 transition-colors">
        Suivant
      </button>
      <div v-else class="flex flex-col gap-4">
        <button @click="router.push('/register')" class="w-full p-4 bg-primary text-accent rounded-cta text-lg font-bold cursor-pointer uppercase tracking-wider font-heading hover:bg-opacity-90 transition-colors">
          Commencer l'aventure
        </button>
        <button @click="router.push('/login')" class="w-full p-4 bg-transparent text-primary border-2 border-primary rounded-cta text-lg font-bold cursor-pointer uppercase tracking-wider font-heading hover:bg-primary/5 transition-colors dark:text-gray-300 dark:border-gray-300 dark:hover:bg-gray-800">
          J'ai déjà un compte
        </button>
        <button @click="continueAsGuest" class="bg-none border-none text-gray-500 text-base underline cursor-pointer pt-1 hover:text-gray-700 dark:hover:text-gray-300 transition-colors">
          Continuer sans compte
        </button>
      </div>
    </div>
  </main>
</template>