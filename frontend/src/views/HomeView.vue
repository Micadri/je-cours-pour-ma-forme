<script setup>
import { onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useProgramStore } from '../stores/program'

// Import de nos nouveaux composants Atomes
import DashboardHeader from '../components/DashboardHeader.vue'
import SeasonProgress from '../components/SeasonProgress.vue'
import NextSessionCard from '../components/NextSessionCard.vue'
import UpcomingSessions from '../components/UpcomingSessions.vue'
import GuestLockOverlay from '../components/GuestLockOverlay.vue'
import SessionHistory from '../components/SessionHistory.vue'

const router = useRouter()
const store = useProgramStore()

onMounted(() => { store.initApp() })
</script>

<template>
  <main class="dashboard-container">
    <h1 style="text-align: center; margin-bottom: 25px;">Vue d'ensemble</h1>
    
    <section style="scroll-snap-align: start; scroll-margin-top: 20px; margin-bottom: 30px;">
      <DashboardHeader />
      <SeasonProgress />
    </section>

    <div v-if="!store.seasonData || !store.currentProgress">
      <p style="text-align: center;">Synchronisation en cours...</p>
    </div>
    
    <div v-else>
      <section style="scroll-snap-align: start; scroll-margin-top: 20px; margin-bottom: 40px;">
        <NextSessionCard />
      </section>

      <GuestLockOverlay>
        <section style="scroll-snap-align: start; scroll-margin-top: 20px; margin-bottom: 40px;">
          <UpcomingSessions />
        </section>

        <section style="scroll-snap-align: start; scroll-margin-top: 20px; margin-bottom: 40px;">
          <h3 style="color: #333; margin-bottom: 10px; border-bottom: 2px solid #eee; padding-bottom: 5px;">Mes sessions</h3>
          <SessionHistory />
        </section>
      </GuestLockOverlay>
    </div>
    
    <section style="scroll-snap-align: start; scroll-margin-top: 20px; margin-bottom: 25px;">
      <h3 style="color: #333; margin-bottom: 15px; border-bottom: 2px solid #eee; padding-bottom: 5px;">Préparation & Conseils</h3>
      <div @click="router.push('/tips')" style="display: flex; align-items: center; justify-content: space-between; background: #fff; padding: 15px; border-radius: 8px; border: 1px solid #ddd; cursor: pointer; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
        <div style="display: flex; align-items: center; gap: 15px;">
          <div style="font-size: 24px;">💡</div>
          <div>
            <div style="font-weight: bold; color: #4CAF50;">La boîte à outils du coureur</div>
            <div style="color: #666; font-size: 0.85rem; margin-top: 3px;">Échauffement, postures, hydratation...</div>
          </div>
        </div>
        <div style="color: #aaa; font-weight: bold;">></div>
      </div>
    </section>
  </main>
</template>

<style scoped>
.dashboard-container { padding: 20px 20px 50vh 20px; font-family: sans-serif; margin: 0 auto; max-width: 600px; }
@media (min-width: 768px) { .dashboard-container { max-width: 900px; } }
</style>