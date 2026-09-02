import { fileURLToPath, URL } from 'node:url'
import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import { VitePWA } from 'vite-plugin-pwa'

export default defineConfig({
  plugins: [
    vue(),
    VitePWA({
      registerType: 'autoUpdate',
      injectRegister: 'auto',
     workbox: {
  // Ajout de 'json' à la liste des fichiers à stocker hors-ligne
  globPatterns: ['**/*.{js,css,html,ico,png,svg,mp3,json}'] 
},
      manifest: {
        name: 'Je Cours Pour Ma Forme',
        short_name: 'JCPMF',
        description: 'Programme d\'entraînement 5 kms',
        theme_color: '#4CAF50',
        background_color: '#ffffff',
        display: 'standalone',
    icons: [
  {
    src: '/logo-192-192.png',
    sizes: '192x192',
    type: 'image/png',
    purpose: 'any maskable'
  },
  {
    src: '/logo-512.png',
    sizes: '512x512',
    type: 'image/png',
    purpose: 'any maskable'
  }
]
      }
    })
  ],
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url))
    }
  }
})