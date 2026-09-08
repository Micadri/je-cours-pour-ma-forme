<script setup>
import { computed } from 'vue'

const props = defineProps({
  timeRemaining: { type: Number, required: true },
  totalTime: { type: Number, required: true },
  exerciseType: { type: String, required: true }
})

const formattedTime = computed(() => {
  const m = Math.floor(props.timeRemaining / 60).toString().padStart(2, '0')
  const s = (props.timeRemaining % 60).toString().padStart(2, '0')
  return `${m}:${s}`
})

// Calcul SVG pour l'anneau (Rayon de 120)
const circumference = 753.98
const progressOffset = computed(() => {
  const percent = props.totalTime > 0 ? (props.timeRemaining / props.totalTime) : 0
  return circumference - (percent * circumference)
})

// Code couleur intelligent : Vert Anis pour courir, Bleu pour marcher, Orange pour s'échauffer
const ringColor = computed(() => {
  const type = props.exerciseType.toLowerCase()
  if (type.includes('echauffement') || type.includes('etirement')) return 'text-orange-400'
  if (type.includes('marche')) return 'text-blue-500'
  if (type.includes('cours') || type.includes('trotte') || type.includes('sprint')) return 'text-accent'
  return 'text-primary'
})
</script>

<template>
  <div class="my-10 flex flex-col items-center">
    <div class="relative flex items-center justify-center w-[280px] h-[280px]">
      <svg class="absolute inset-0 w-full h-full transform -rotate-90 drop-shadow-md" viewBox="0 0 260 260">
        <circle cx="130" cy="130" r="120" stroke="currentColor" stroke-width="12" fill="transparent" class="text-gray-200 dark:text-gray-700" />
        <circle cx="130" cy="130" r="120" stroke="currentColor" stroke-width="12" fill="transparent" stroke-linecap="round"
          :stroke-dasharray="circumference"
          :stroke-dashoffset="progressOffset"
          class="transition-all duration-1000 ease-linear" :class="ringColor" />
      </svg>
      <div class="text-6xl font-bold font-heading tabular-nums text-text dark:text-gray-100 z-10 flex flex-col items-center">
        {{ formattedTime }}
        <span class="text-sm uppercase tracking-widest mt-2 font-body text-gray-500 font-black" :class="ringColor">{{ exerciseType }}</span>
      </div>
    </div>
  </div>
</template>