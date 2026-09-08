<script setup>
import { useRouter } from 'vue-router'
const router = useRouter()

const tools = [
  { id: 'echauffement', title: 'Échauffement', desc: 'Préparer la machine avant l\'effort', icon: '🔥', link: 'https://www.asics.com/fr/fr-fr/running-advice/how-to-warm-up-before-running/' },
  { id: 'posture', title: 'Posture de course', desc: 'Courir grand et détendu', icon: '🏃', link: 'https://www.decathlon.be/fr/c/exe/comment-adopter-une-bonne-posture-pour-la-course-a-pied_79246bd1-84a6-47d2-92d1-3eba75b15e5a' },
  { id: 'hydratation', title: 'Hydratation', desc: 'L\'eau, votre carburant essentiel', icon: '💧', link: 'https://www.nutripure.fr/fr/blog/hydratation-et-sante-n654' },
  { id: 'recuperation', title: 'Récupération', desc: 'S\'étirer et redescendre en douceur', icon: '🧘', link: 'https://www.decathlon.be/fr/c/well/running-comment-bien-recuperer-apres-votre-course_875fc080-86b7-4c12-803d-32c0b2abae31' }
]

const openTip = (link) => {
  if (link && link.startsWith('http')) window.open(link, '_blank')
  else alert("Cet article sera bientôt disponible !")
}
</script>

<template>
  <main class="p-5 font-body max-w-[600px] mx-auto">
    <button @click="router.push('/')" class="bg-transparent border-none text-accent text-base font-bold cursor-pointer mb-5">
      ← Retour à l'accueil
    </button>
    
    <h1 class="mt-0 mb-6 font-heading text-3xl text-primary font-bold dark:text-gray-100">La Boîte à outils</h1>
    
    <div class="flex flex-col gap-4">
      <div v-for="tool in tools" :key="tool.id" @click="openTip(tool.link)" class="bg-surface border border-gray-200 rounded-xl p-4 flex items-center justify-between cursor-pointer shadow-sm hover:shadow-md transition-shadow dark:bg-gray-800 dark:border-gray-700">
        <div class="flex items-center gap-4">
          <div class="text-3xl">{{ tool.icon }}</div>
          <div>
            <div class="font-bold text-lg text-text font-heading dark:text-gray-200">{{ tool.title }}</div>
            <div class="text-gray-500 text-sm mt-1 dark:text-gray-400">{{ tool.desc }}</div>
          </div>
        </div>
        <div class="text-accent font-bold text-2xl">&gt;</div>
      </div>
    </div>
  </main>
</template>