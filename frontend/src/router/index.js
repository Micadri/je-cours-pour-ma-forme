import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '../views/HomeView.vue'
import RunView from '../views/Runview.vue'
import LoginView from '../views/LoginView.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    { path: '/', name: 'home', component: HomeView, meta: { requiresAuth: true } },
    { path: '/run', name: 'run', component: RunView, meta: { requiresAuth: true } },
    { path: '/profile', name: 'profile', component: () => import('../views/ProfileView.vue'), meta: { requiresAuth: true } },
    { path: '/tips', name: 'tips', component: () => import('../views/TipsView.vue'), meta: { requiresAuth: true } },
    { path: '/welcome', name: 'welcome', component: () => import('../views/OnboardingView.vue') },
    { path: '/login', name: 'login', component: LoginView },
    { path: '/register', name: 'register', component: () => import('../views/RegisterView.vue') }
  ]
})

router.beforeEach((to, from, next) => {
  const isAuthenticated = !!localStorage.getItem('auth_token')
  
  if (to.meta.requiresAuth && !isAuthenticated) {
    next('/welcome') // Redirection vers l'onboarding
  } else if ((to.name === 'login' || to.name === 'register' || to.name === 'welcome') && isAuthenticated) {
    next('/')
  } else {
    next()
  }
})

export default router