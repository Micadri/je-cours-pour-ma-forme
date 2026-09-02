<script setup>
import { RouterView } from 'vue-router'
</script>

<template>
  <header style="text-align: center; background: #fff; padding: 15px 0; border-bottom: 2px solid #f0f0f0; margin-bottom: 20px;">
    <!-- L'image garde ses proportions max (351x152) mais s'adapte aux petits écrans -->
    <img src="/banner.png" alt="Je Cours Pour Ma Forme" style="max-width: 100%; height: auto; max-height: 152px; width: 351px; object-fit: contain;" />
  </header>
  
  <RouterView />
</template>