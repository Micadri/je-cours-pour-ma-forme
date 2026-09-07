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
}

/* 1. TOUTES LES CARTES (Profil, Progression, Outils, Sessions) */
/* On inclut les traductions "rgb" de Vue.js pour forcer l'application du style */
body.dark-mode div[style*="background: #fff"],
body.dark-mode div[style*="background: white"],
body.dark-mode div[style*="background: #ffffff"],
body.dark-mode div[style*="background: #f4f4f4"],
body.dark-mode div[style*="background-color: rgb(255, 255, 255)"],
body.dark-mode div[style*="background: rgb(255, 255, 255)"],
body.dark-mode div[style*="background-color: rgb(244, 244, 244)"],
body.dark-mode div[style*="background: rgb(244, 244, 244)"],
body.dark-mode li {
  background-color: #2b2d35 !important;
  border: 1px solid #3e4149 !important;
  box-shadow: 0 4px 10px rgba(0,0,0,0.2) !important;
}

/* 2. L'INTÉRIEUR DE LA SESSION DÉPLIÉE (Un chouia plus clair pour la profondeur) */
body.dark-mode div[style*="background: #fafafa"],
body.dark-mode div[style*="background-color: rgb(250, 250, 250)"],
body.dark-mode div[style*="background: rgb(250, 250, 250)"] {
  background-color: #353842 !important; 
  border-top: 1px solid #4a4d59 !important;
}

/* 3. FORCER LES TEXTES À ÊTRE LISIBLES PARTOUT */
body.dark-mode,
body.dark-mode p, 
body.dark-mode h1, 
body.dark-mode h2, 
body.dark-mode h3, 
body.dark-mode label, 
body.dark-mode span, 
body.dark-mode strong, 
body.dark-mode div {
  color: #e0e0e0 !important;
}

/* 4. SAUVER LES COULEURS SPÉCIFIQUES (Vert et Rouge) */
body.dark-mode [style*="color: #4CAF50"] {
  color: #66bb6a !important; 
}
body.dark-mode [style*="color: #f44336"],
body.dark-mode [style*="color: #ff5252"] {
  color: #ff8a80 !important;
}

/* 5. BOUTONS SECONDAIRES (Reset Semaine / Saison) */
body.dark-mode button[style*="background: #e0e0e0"],
body.dark-mode button[style*="background: rgb(224, 224, 224)"],
body.dark-mode button[style*="background-color: rgb(224, 224, 224)"] {
  background-color: #3e4149 !important;
  color: #e0e0e0 !important;
  border: 1px solid #555 !important;
}

body.dark-mode button[style*="background: #ffebee"],
body.dark-mode button[style*="background: rgb(255, 235, 238)"],
body.dark-mode button[style*="background-color: rgb(255, 235, 238)"] {
  background-color: #4a2323 !important;
  color: #ff8a80 !important;
  border: 1px solid #ff8a80 !important;
}

/* 6. LOGO & FORMULAIRES */
body.dark-mode header img {
  background-color: #d1d1d1 !important;
  padding: 10px !important;
  border-radius: 20px !important;
  opacity: 0.9;
}
body.dark-mode input, body.dark-mode select, body.dark-mode textarea {
  background-color: #1e1e24 !important;
  color: #ffffff !important;
  border: 1px solid #555 !important;
}
</style>