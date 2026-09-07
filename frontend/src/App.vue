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

/* --- MODE SOMBRE --- */
body.dark-mode {
  background-color: #121212 !important;
  color: #e0e0e0 !important;
}

/* 1. Assombrir les encadrés blancs/gris de toutes les pages */
body.dark-mode div[style*="background: white"],
body.dark-mode div[style*="background: #fff"],
body.dark-mode div[style*="background: #ffffff"],
body.dark-mode div[style*="background: #f4f4f4"] {
  background-color: #1e1e1e !important;
  border: 1px solid #333 !important;
  box-shadow: none !important;
}

/* 2. Empêcher les textes de disparaître sur fond clair */
body.dark-mode p, body.dark-mode h1, body.dark-mode h2, body.dark-mode h3, 
body.dark-mode label, body.dark-mode span {
  color: #e0e0e0 !important;
}

/* 3. Intégrer le logo en douceur */
body.dark-mode header img {
  background-color: #e0e0e0;
  padding: 10px;
  border-radius: 15px;
  opacity: 0.85; /* Réduit l'éblouissement */
}

/* 4. Adapter les formulaires */
body.dark-mode input, body.dark-mode select, body.dark-mode textarea {
  background: #2c2c2c !important;
  color: #ffffff !important;
  border: 1px solid #444 !important;
}

/* 5. Adapter les boutons secondaires (Profil, Reset) */
body.dark-mode button[style*="background: #e0e0e0"],
body.dark-mode button[style*="background: #f0f0f0"] {
  background-color: #333 !important;
  color: #fff !important;
}
body.dark-mode button[style*="background: #ffebee"] {
  background-color: #3a1c1c !important;
  color: #ff8a80 !important;
  border: 1px solid #ff8a80 !important;
}
</style>