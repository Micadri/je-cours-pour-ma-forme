import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '../views/HomeView.vue'
import RunView from '../views/RunView.vue' // <-- Ajout de l'import

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'home',
      component: HomeView
    },
    {
      path: '/run',
      name: 'run',
      component: RunView // <-- Ajout de la route
    }
  ]
})

export default router