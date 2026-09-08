<script setup>
import { watch, onMounted } from 'vue'
import { RouterView } from 'vue-router'
import { useProgramStore } from './stores/program'

const store = useProgramStore()

const updateTheme = () => {
  if (store.userProfile?.theme === 'dark') {
    document.documentElement.classList.add('dark')
  } else {
    document.documentElement.classList.remove('dark')
  }
}

onMounted(updateTheme)
watch(() => store.userProfile?.theme, updateTheme)
</script>

<template>
  <div class="min-h-screen bg-background text-text transition-colors duration-300 font-body dark:bg-gray-900 dark:text-gray-100">
    <header class="text-center py-6">
      <img src="/banner.png" alt="Je Cours Pour Ma Forme" class="mx-auto max-w-full h-auto max-h-[120px] w-[300px] object-contain rounded-xl shadow-sm dark:bg-gray-200 dark:p-2" />
    </header>
    <RouterView />
  </div>
</template>