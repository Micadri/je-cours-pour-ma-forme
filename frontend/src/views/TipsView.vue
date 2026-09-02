<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const activeTab = ref('echauffement')

const tabs = [
  { id: 'echauffement', title: 'Échauffement', icon: '🔥' },
  { id: 'course', title: 'Course', icon: '🏃' },
  { id: 'hydratation', title: 'Hydratation', icon: '💧' },
  { id: 'recuperation', title: 'Récupération', icon: '🧘' }
]
</script>

<template>
  <main style="padding: 20px; font-family: sans-serif; max-width: 600px; margin: 0 auto;">
    <button @click="router.push('/')" style="background: none; border: none; color: #4CAF50; font-size: 16px; font-weight: bold; cursor: pointer; margin-bottom: 20px;">
      ← Retour
    </button>
    
    <h1 style="margin-top: 0; color: #333;">La Boîte à outils</h1>

    <!-- Menu des catégories -->
    <div style="display: flex; overflow-x: auto; gap: 10px; padding-bottom: 10px; margin-bottom: 20px; scrollbar-width: none;">
      <button v-for="tab in tabs" :key="tab.id" @click="activeTab = tab.id"
              :style="{ flex: '0 0 auto', padding: '10px 15px', borderRadius: '20px', border: 'none', background: activeTab === tab.id ? '#4CAF50' : '#e0e0e0', color: activeTab === tab.id ? 'white' : '#333', fontWeight: 'bold', cursor: 'pointer' }">
        {{ tab.icon }} {{ tab.title }}
      </button>
    </div>

    <!-- Contenu dynamique -->
    <div style="background: #f4f4f4; padding: 20px; border-radius: 8px; line-height: 1.6; color: #444;">
      <div v-if="activeTab === 'echauffement'">
        <h2 style="margin-top: 0; color: #4CAF50;">Préparer la machine</h2>
        <p>Ne partez jamais à froid ! Prenez 5 à 10 minutes avant chaque session.</p>
        <ul style="padding-left: 20px;">
          <li>Marche active pendant 2 minutes.</li>
          <li>Rotations des articulations (chevilles, genoux, bassin).</li>
          <li>Talons-fesses légers sur place.</li>
        </ul>
      </div>

      <div v-else-if="activeTab === 'course'">
        <h2 style="margin-top: 0; color: #4CAF50;">La bonne posture</h2>
        <p>Courez grand et détendu.</p>
        <ul style="padding-left: 20px;">
          <li>Regardez loin devant vous, pas vos pieds.</li>
          <li>Gardez les épaules basses et relâchées.</li>
          <li>Vos bras doivent faire un angle de 90° et balancer d'avant en arrière sans croiser l'axe du corps.</li>
        </ul>
      </div>

      <div v-else-if="activeTab === 'hydratation'">
        <h2 style="margin-top: 0; color: #4CAF50;">L'eau, votre carburant</h2>
        <p>L'hydratation se gère avant, pendant et après l'effort.</p>
        <ul style="padding-left: 20px;">
          <li>Buvez 2 à 3 verres d'eau 2 heures avant de courir.</li>
          <li>Ne buvez pas une grande quantité juste avant le départ (point de côté assuré !).</li>
          <li>Réhydratez-vous doucement après la séance.</li>
        </ul>
      </div>

      <div v-else-if="activeTab === 'recuperation'">
        <h2 style="margin-top: 0; color: #4CAF50;">L'art de redescendre</h2>
        <p>La récupération est aussi importante que l'entraînement.</p>
        <ul style="padding-left: 20px;">
          <li>Finissez toujours par 3 à 5 minutes de marche lente.</li>
          <li>Étirez-vous doucement, sans à-coups ni douleur (mollets, cuisses).</li>
          <li>Prenez une douche tiède pour détendre les muscles.</li>
        </ul>
      </div>
    </div>
  </main>
</template>