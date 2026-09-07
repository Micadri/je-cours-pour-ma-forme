<script setup>
import { watch, onMounted } from 'vue'
import { RouterView } from 'vue-router'
import { useProgramStore } from './stores/program'

const store = useProgramStore()

const updateTheme = () => {
  if (store.userProfile?.theme === 'dark') {
    document.body.classList.add('dark-mode')
  } else {
    document.body.classList.remove('dark-mode')
  }
}

// Appliquer au chargement et surveiller les changements
onMounted(updateTheme)
watch(() => store.userProfile?.theme, updateTheme)
</script>

<template>
  <header style="text-align: center; padding: 20px 0; margin-bottom: 20px;">
    <!-- Ajout de border-radius et d'une légère ombre pour adoucir le logo -->
    <img src="/banner.png" alt="Je Cours Pour Ma Forme" style="max-width: 100%; height: auto; max-height: 120px; width: 300px; object-fit: contain; border-radius: 15px; box-shadow: 0 4px 10px rgba(0,0,0,0.05);" />
  </header>
  <RouterView />
</template>

<style>
/* Style global de base */
body { transition: background 0.3s, color 0.3s; margin: 0; background-color: #f9f9f9; }

/* --- MODE SOMBRE : Fond adouci (Gris Anthracite) --- */
body.dark-mode {
  background-color: #1e1e24 !important; 
  color: #e0e0e0 !important;
}

/* 1. Assombrir les encadrés (Correction du texte blanc sur fond blanc) */
/* On inclut les formats 'rgb' car le navigateur modifie parfois tes styles en direct */
body.dark-mode div[style*="background: #fff"],
body.dark-mode div[style*="background: white"],
body.dark-mode div[style*="background: #ffffff"],
body.dark-mode div[style*="background: #f4f4f4"],
body.dark-mode div[style*="background: #fafafa"],
body.dark-mode div[style*="background-color: rgb(255, 255, 255)"],
body.dark-mode div[style*="background-color: rgb(244, 244, 244)"],
body.dark-mode li {
  background-color: #2b2d35 !important; /* Gris "carte" légèrement plus clair */
  border: 1px solid #3e4149 !important;
  box-shadow: 0 4px 10px rgba(0,0,0,0.2) !important;
}

/* 2. Textes lisibles partout */
body.dark-mode p, body.dark-mode h1, body.dark-mode h2, body.dark-mode h3, 
body.dark-mode label, body.dark-mode span, body.dark-mode strong, body.dark-mode div {
  color: #e0e0e0 !important;
}

/* 3. Sauver les textes verts (pour ne pas qu'ils deviennent blancs) */
body.dark-mode [style*="color: #4CAF50"] {
  color: #66bb6a !important; /* Un vert un peu plus clair, adapté au sombre */
}

/* 4. Le logo : fond gris clair, bords arrondis, moins éblouissant */
body.dark-mode header img {
  background-color: #d1d1d1 !important;
  padding: 10px !important;
  border-radius: 20px !important;
  opacity: 0.9;
}

/* 5. Formulaires */
body.dark-mode input, body.dark-mode select, body.dark-mode textarea {
  background-color: #1e1e24 !important;
  color: #ffffff !important;
  border: 1px solid #555 !important;
}

/* 6. Boutons secondaires */
body.dark-mode button[style*="background: #e0e0e0"],
body.dark-mode button[style*="background: #f0f0f0"] {
  background-color: #3e4149 !important;
  color: #fff !important;
}
body.dark-mode button[style*="background: #ffebee"] {
  background-color: #4a2323 !important;
  color: #ff8a80 !important;
  border-color: #ff8a80 !important;
}
</style>