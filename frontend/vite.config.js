import { fileURLToPath, URL } from 'node:url'
import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import tailwindcss from '@tailwindcss/vite'
import { VitePWA } from 'vite-plugin-pwa' // Le retour du plugin PWA !

export default defineConfig({
  plugins: [
    vue(),
    tailwindcss(),
    VitePWA({
      registerType: 'autoUpdate',
      devOptions: {
        enabled: true // Permet de tester l'installation PWA même en local sur ton PC
      },
      includeAssets: ['favicon.ico', 'banner.png'],
      manifest: {
        name: 'Je Cours Pour Ma Forme',
        short_name: 'JCPMF',
        description: 'Programme de progression à la course à pied',
        theme_color: '#003355', // Ton Bleu Marine en couleur de barre de statut
        background_color: '#f4f4f4',
        display: 'standalone',
        icons: [
          {
            src: 'pwa-192x192.png', // Assure-toi que ces images existent dans ton dossier /public
            sizes: '192x192',
            type: 'image/png'
          },
          {
            src: 'pwa-512x512.png',
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