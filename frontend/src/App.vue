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
  <header style="text-align: center; background: inherit; padding: 15px 0; border-bottom: 2px solid #f0f0f0; margin-bottom: 20px;">
    <img src="/banner.png" alt="Je Cours Pour Ma Forme" style="max-width: 100%; height: auto; max-height: 152px; width: 351px; object-fit: contain;" />
  </header>
  <RouterView />
</template>

<style>
/* Style global de base */
body { transition: background 0.3s, color 0.3s; margin: 0; }

/* Variables et surcharges pour le mode sombre */
body.dark-mode {
  background-color: #121212 !important;
  color: #ffffff !important;
}
body.dark-mode div, body.dark-mode main, body.dark-mode p, body.dark-mode h1, body.dark-mode h2, body.dark-mode h3 {
  color: #e0e0e0 !important;
}
body.dark-mode input, body.dark-mode select {
  background: #333 !important;
  color: white !important;
  border-color: #555 !important;
}
</style>