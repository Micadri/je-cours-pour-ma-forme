import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '../views/HomeView.vue'
import RunView from '../views/RunView.vue'
// Tu peux supprimer l'import de LoginView pour ce test

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    { path: '/', name: 'home', component: HomeView },
    { path: '/run', name: 'run', component: RunView }
  ]
})
export default router