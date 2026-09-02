<script setup>
defineProps({
  totalSteps: { type: Number, required: true },
  currentStepIndex: { type: Number, required: true }
})
</script>

<template>
  <div style="margin-bottom: 20px;">
    <div style="font-weight: bold; margin-bottom: 8px; color: #555;">
      Étape {{ currentStepIndex + 1 }} sur {{ totalSteps }}
    </div>
    <div style="height: 10px; background: #ddd; border-radius: 5px; overflow: hidden;">
      <div 
        :style="{ width: `${((currentStepIndex + 1) / totalSteps) * 100}%`, height: '100%', background: '#4CAF50', transition: 'width 0.3s' }"
      ></div>
    </div>
  </div>
</template>