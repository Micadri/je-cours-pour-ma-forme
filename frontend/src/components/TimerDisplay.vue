<script setup>
import { computed } from 'vue'

const props = defineProps({
  timeRemaining: { type: Number, required: true },
  exerciseType: { type: String, required: true }
})

const formattedTime = computed(() => {
  const m = Math.floor(props.timeRemaining / 60).toString().padStart(2, '0')
  const s = (props.timeRemaining % 60).toString().padStart(2, '0')
  return `${m}:${s}`
})
</script>

<template>
  <div style="margin: 40px 0; text-align: center;">
    <h1 style="font-size: 3rem; text-transform: uppercase; margin: 0; color: #333;">
      {{ exerciseType }}
    </h1>
    <div style="font-size: 5rem; font-weight: bold; font-variant-numeric: tabular-nums;">
      {{ formattedTime }}
    </div>
  </div>
</template>