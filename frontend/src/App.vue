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
body { transition: background 0.3s, color 0.3s; margin: 0; }

body.dark-mode {
  background-color: #121212 !important;
  color: #f5f5f5 !important;
}

/* Force les textes à être blancs au lieu de gris illisible */
body.dark-mode p, body.dark-mode h1, body.dark-mode h2, body.dark-mode h3, 
body.dark-mode label, body.dark-mode span, body.dark-mode div {
  color: #f5f5f5 !important;
}

body.dark-mode input, body.dark-mode select, body.dark-mode textarea {
  background: #2c2c2c !important;
  color: #ffffff !important;
  border: 1px solid #444 !important;
}

/* Assombrit les encadrés blancs du mode jour */
body.dark-mode div[style*="background: #f4f4f4"],
body.dark-mode div[style*="background: white"],
body.dark-mode div[style*="background: #ffffff"] {
  background: #1e1e1e !important;
  border-color: #333 !important;
}
</style>