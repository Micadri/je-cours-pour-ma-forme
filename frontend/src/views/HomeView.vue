<script setup>
import { onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useProgramStore } from '../stores/program'

import DashboardHeader from '../components/DashboardHeader.vue'
import SeasonProgress from '../components/SeasonProgress.vue'
import NextSessionCard from '../components/NextSessionCard.vue'
import UpcomingSessions from '../components/UpcomingSessions.vue'
import GuestLockOverlay from '../components/GuestLockOverlay.vue'
import SessionHistory from '../components/SessionHistory.vue'

const router = useRouter()
const store = useProgramStore()

const goToTips = () => router.push('/tips')

onMounted(() => { store.initApp() })
</script>

<template>
  <main class="mx-auto max-w-[600px] md:max-w-[900px] px-5 pb-[50vh]">
    <h1 class="font-heading text-3xl font-bold text-center mb-6 text-primary dark:text-gray-100">Vue d'ensemble</h1>
    
    <section class="mb-8">
      <DashboardHeader />
      <SeasonProgress />
    </section>

    <div v-if="!store.seasonData || !store.currentProgress">
      <p class="text-center text-gray-500 animate-pulse">Synchronisation en cours...</p>
    </div>
    
    <div v-else>
      <section class="mb-10">
        <NextSessionCard />
      </section>

      <GuestLockOverlay>
        <section class="mb-10">
          <UpcomingSessions />
        </section>

        <section class="mb-10">
          <h3 class="font-heading text-xl font-bold mb-3 border-b-2 border-gray-200 pb-2 text-primary dark:text-gray-100">Mes sessions</h3>
          <SessionHistory />
        </section>
      </GuestLockOverlay>
    </div>
    
    <section class="mb-6">
      <h3 class="font-heading text-xl font-bold mb-3 border-b-2 border-gray-200 pb-2 text-primary dark:text-gray-100">Préparation & Conseils</h3>
      <div @click="goToTips" class="flex items-center justify-between bg-surface p-4 rounded-xl border border-gray-200 cursor-pointer shadow-sm hover:shadow-md transition-shadow dark:bg-gray-800 dark:border-gray-700">
        <div class="flex items-center gap-4">
          <div class="text-2xl">💡</div>
          <div>
            <div class="font-bold text-accent">La boîte à outils du coureur</div>
            <div class="text-gray-500 text-sm mt-1 dark:text-gray-400">Échauffement, postures, hydratation...</div>
          </div>
        </div>
        <div class="text-gray-400 font-bold text-lg">&gt;</div>
      </div>
    </section>
  </main>
</template>