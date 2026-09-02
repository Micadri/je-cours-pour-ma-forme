# Je Cours Pour Ma Forme - PWA (V2)

Une application web progressive (PWA) conçue pour suivre des programmes d'entraînement de course à pied. La V2 introduit une architecture "Offline-First", permettant de courir sans réseau et de synchroniser les statistiques ultérieurement.

## 🚀 Technologies

* **Frontend :** Vue.js 3, Vite, Vue Router
* **State Management :** Pinia (Optimistic UI & File d'attente de synchronisation)
* **Backend :** PHP 8, MySQL (API RESTful)
* **Architecture :** PWA Offline-First avec LocalStorage

## 📦 Installation & Démarrage

### 1. Backend (API PHP)
1. Importez la base de données via phpMyAdmin (exécutez `seed.php` pour charger le programme 5K).
2. Configurez vos identifiants MySQL dans `api/config.php`.
3. Lancez votre serveur local (ex: MAMP sur le port `8888`).

### 2. Frontend (Vue 3)
```sh
cd frontend
npm install