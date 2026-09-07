<script setup>
import { useRouter } from 'vue-router'
const router = useRouter()

const tools = [
  { 
    id: 'echauffement', 
    title: 'Échauffement', 
    desc: 'Préparer la machine avant l\'effort', 
    icon: '🔥',
    link: 'https://www.asics.com/fr/fr-fr/running-advice/how-to-warm-up-before-running/' // <-- Insère ton lien ici
  },
  { 
    id: 'posture', 
    title: 'Posture de course', 
    desc: 'Courir grand et détendu', 
    icon: '🏃',
    link: 'https://www.decathlon.be/fr/c/exe/comment-adopter-une-bonne-posture-pour-la-course-a-pied_79246bd1-84a6-47d2-92d1-3eba75b15e5a' // <-- Insère ton lien ici
  },
  { 
    id: 'hydratation', 
    title: 'Hydratation', 
    desc: 'L\'eau, votre carburant essentiel', 
    icon: '💧',
    link: 'https://www.nutripure.fr/fr/blog/hydratation-et-sante-n654' // <-- Insère ton lien ici
  },
  { 
    id: 'recuperation', 
    title: 'Récupération', 
    desc: 'S\'étirer et redescendre en douceur', 
    icon: '🧘',
    link: 'https://www.decathlon.be/fr/c/well/running-comment-bien-recuperer-apres-votre-course_875fc080-86b7-4c12-803d-32c0b2abae31' // <-- Insère ton lien ici
  }
]

const openTip = (link) => {
  if (link && link.startsWith('http')) {
    window.open(link, '_blank')
  } else {
    alert("Cet article sera bientôt disponible !")
  }
}
</script>

<template>
  <main style="padding: 20px; font-family: sans-serif; max-width: 600px; margin: 0 auto;">
    <button @click="router.push('/')" style="background: none; border: none; color: #4CAF50; font-size: 16px; font-weight: bold; cursor: pointer; margin-bottom: 20px;">
      ← Retour à l'accueil
    </button>
    
    <h1 style="margin-top: 0; margin-bottom: 25px;">La Boîte à outils</h1>

    <div style="display: flex; flex-direction: column; gap: 15px;">
      <div v-for="tool in tools" :key="tool.id" @click="openTip(tool.link)"
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