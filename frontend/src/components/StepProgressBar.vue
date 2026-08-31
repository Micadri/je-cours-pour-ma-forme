<script setup>
defineProps({
  currentIndex: { type: Number, required: true },
  totalSteps: { type: Number, required: true }
})
</script>

<template>
  <div>
    <div style="background: #e0e0e0; height: 10px; border-radius: 5px; margin: 20px 0; overflow: hidden;">
      <div 
        :style="{ width: ((currentIndex + 1) / totalSteps * 100) + '%', background: '#4CAF50', height: '100%', transition: 'width 0.3s' }"
      ></div>
    </div>
    <p style="font-weight: bold; text-align: center;">Étape {{ currentIndex + 1 }} sur {{ totalSteps }}</p>
  </div>
</template>