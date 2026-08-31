# 🏃‍♂️ Je Cours Pour Ma Forme (PWA)

![Version](https://img.shields.io/badge/version-1.0%20MVP-blue)
![Stack](https://img.shields.io/badge/Stack-Vue.js%20%7C%20PHP%20%7C%20MySQL-success)

Une Progressive Web App (PWA) mobile-first développée lors d'un sprint intensif de 4 jours. Conçue pour accompagner les coureurs dans leur programme d'entraînement avec une architecture Headless légère, pensée pour le mode hors-ligne et les performances pures.

## 🎯 Objectif du MVP (Sprint de 4 jours)
Fournir une interface fluide permettant à un coureur de suivre sa progression, de lancer une session chronométrée intelligente (passage automatique de la marche à la course) et d'empêcher la mise en veille de son téléphone pendant l'effort, même lors de pertes de réseau.

## 🛠️ Stack Technique & Architecture
L'application repose sur une architecture découplée (Jamstack) pour séparer strictement l'interface utilisateur de la logique métier.

* **Front-end :** Vue.js 3 (Composition API) + Vite
* **Gestion d'état :** Pinia (stockage du programme et de la progression en mémoire pour le Offline-First)
* **Back-end API :** PHP 8 Natif (API REST sécurisée via PDO)
* **Base de Données :** MySQL (Modèle relationnel strict pour préparer la scalabilité)
* **Style :** SCSS (Design System sur-mesure)

## ✨ Fonctionnalités Principales (V1)
* **Chronomètre intelligent :** Décompte basé sur des timestamps absolus (`Date.now()`) pour éviter la dérive de la boucle JavaScript.
* **Maintien de l'écran (WakeLock) :** Exploitation de l'API `navigator.wakeLock` couplée à un écouteur `visibilitychange` pour garder l'écran allumé pendant la course.
* **Offline-First :** Pas d'appels API superflus. Le programme est préchargé pour pallier les zones blanches (forêts, campagnes).
* **Sécurité :** API PHP protégée contre les injections SQL (requêtes préparées) et configurations CORS strictes.

## 📂 Structure du Dépôt
* `/docs/` : Dossiers d'architecture, charte graphique et analyse des risques.
* `/frontend/` : Code source de l'application Vue.js (PWA).
* `/api/` : Code source des endpoints PHP (REST).

## 🚀 Déploiement
* Le Front-end statique est compilé via `npm run build` et déployé sur plateforme Serverless (ex: Vercel).
* Le Back-end et la base de données MySQL sont hébergés sur un serveur mutualisé (Hostinger), permettant un stockage centralisé et sécurisé avec un minimum d'infrastructures.