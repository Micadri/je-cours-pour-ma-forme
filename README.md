# 🏃‍♂️ Je Cours Pour Ma Forme - PWA (V2)

Une application web progressive (PWA) de tracking sportif, spécialement conçue pour suivre les programmes d'entraînement progressifs (0-5 km, 5-10 km) de course à pied. 

La V2 transforme le simple minuteur d'intervalles en un véritable **tracker d'activité autonome**. L'application est désormais entièrement connectée à une base de données relationnelle MySQL[cite: 8] et introduit une architecture **"Offline-First"** permettant de courir en pleine forêt sans réseau : l'application enregistre le tracé GPS, la météo et les statistiques en local, puis synchronise silencieusement les données avec le serveur distant dès le retour de la connexion.

---

## ✨ Fonctionnalités Principales

* **📶 Architecture Offline-First :** Sauvegarde locale des sessions via Local Storage et une file d'attente (`pwa_sync_queue`)[cite: 7]. Synchronisation automatique en arrière-plan dès la reconnexion au réseau[cite: 7].
* **📍 Tracking GPS & Cartographie :** Enregistrement des coordonnées en temps réel, calcul de la distance (Haversine), du dénivelé (+m) et affichage du tracé interactif avec Leaflet.js et OpenStreetMap[cite: 7].
* **🌤️ Intégration Météo :** Récupération automatique de la température locale via géolocalisation au lancement de l'application avec l'API Open-Meteo[cite: 7].
* **🎧 Coach Vocal & Haptique :** Annonces vocales des étapes via la Web Speech API (Text-to-Speech) et retours vibratoires (Vibration API) pour différencier les phases de marche, de course ou de sprint[cite: 7].
* **📱 Écran Actif (WakeLock) :** Empêche l'écran du smartphone de se verrouiller pendant l'effort, avec une gestion intelligente de l'arrière-plan pour préserver la batterie[cite: 7].
* **📊 Statistiques Détaillées :** Suivi de l'allure (min/km), de la vitesse moyenne (km/h), de la distance parcourue, du dénivelé positif et du temps réel[cite: 7].
* **🎨 UI/UX Moderne :** Design system 100% Tailwind CSS v4, mode Clair/Sombre dynamique[cite: 7], Timer annulaire animé en SVG[cite: 7], et transitions de navigation fluides façon application native[cite: 7].
* **⚙️ Espace Administrateur :** Tableau de bord sécurisé pour gérer les utilisateurs, visualiser l'historique et les tracés GPS des coureurs[cite: 7], traiter les feedbacks, exporter les données en CSV/JSON, et générer de nouvelles saisons d'entraînement de façon procédurale[cite: 7].

---

## 🛠️ Stack Technique

**Frontend (Client)**
* **Framework :** Vue.js 3 (Composition API) + Vite[cite: 7]
* **Routing & State :** Vue Router 4 & Pinia (Gestion de l'état global et persistance)[cite: 7]
* **Styling :** Tailwind CSS v4 (via `@tailwindcss/vite`)[cite: 7]
* **Cartographie :** Leaflet.js[cite: 7]
* **PWA :** `vite-plugin-pwa` (Manifest, Service Workers, Caching)

**Backend (Serveur)**
* **Langage :** PHP (API RESTful pure, sans framework)[cite: 6]
* **Base de données :** MySQL / MariaDB[cite: 6, 8]
* **Authentification :** JWT-like token (Génération et hachage BCrypt)[cite: 6]

---

## 🏗️ Architecture du Code (Frontend)

La logique complexe du tracking a été entièrement modularisée en **Vue Composables** pour garantir un code propre et réutilisable dans l'écran de course (`Runview.vue`)[cite: 7] :
* 📂 `useGPS.js` : Moteur de tracking, calcul de la distance, de l'allure et du dénivelé positif[cite: 7].
* 📂 `useWeather.js` : Fetch API asynchrone pour interroger la météo et la température au moment de la session[cite: 7].
* 📂 `useHaptics.js` : Gestion des patterns vibratoires selon le type d'effort détecté[cite: 7].
* 📂 `program.js` *(Pinia Store)* : Cœur de l'application qui gère la file d'attente hors-ligne (`syncQueue`), l'envoi du payload des sessions à l'API, et l'état de la progression du coureur[cite: 7].

---

## 📦 Installation & Démarrage

### 1. Configuration du Backend (API PHP)
1. **Base de données :** Créez une base de données MySQL et importez le fichier dump `.sql` complet pour initialiser la structure (tables `AD_users`, `AD_session_logs`, `AD_exercises`, etc.) et le programme d'entraînement[cite: 8].
2. **Configuration :** Ouvrez le fichier `api/config.php` et modifiez les identifiants MySQL (`$host`, `$db`, `$user`, `$pass`) pour les connecter à votre base de données locale ou distante[cite: 6].
3. Hébergez le dossier de l'API sur un serveur compatible PHP (Apache/Nginx, MAMP, XAMPP, Hostinger, etc.)[cite: 6].

### 2. Configuration du Frontend (Vue 3)
Assurez-vous d'avoir [Node.js](https://nodejs.org/) installé sur votre machine.

```bash
# 1. Accéder au dossier frontend
cd frontend

# 2. Installer toutes les dépendances (Tailwind v4, Leaflet, Vue Router, Pinia, etc.)
npm install

# 3. Connecter l'API
# Ouvrez src/stores/program.js et vérifiez la constante API_BASE pour cibler votre serveur PHP.
# Exemple local : const API_BASE = 'http://localhost:8888/api'

# 4. Lancer le serveur de développement local avec Hot-Reload
npm run dev