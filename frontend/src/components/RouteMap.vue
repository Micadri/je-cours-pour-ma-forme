<script setup>
import { ref, onMounted, nextTick } from 'vue'
import L from 'leaflet'
import 'leaflet/dist/leaflet.css'

const props = defineProps({ routeString: { type: [String, Array], required: true } })
const mapContainer = ref(null)

onMounted(async () => {
  await nextTick()
  try {
    const coords = typeof props.routeString === 'string' ? JSON.parse(props.routeString) : props.routeString
    if (!coords || coords.length === 0) return
    
    const map = L.map(mapContainer.value, { zoomControl: false, dragging: false, scrollWheelZoom: false }).setView(coords[0], 14)
    L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', { maxZoom: 19 }).addTo(map)
    const polyline = L.polyline(coords, { color: '#8cc63f', weight: 4 }).addTo(map)
    
    // Centre la carte sur l'ensemble du tracé
    map.fitBounds(polyline.getBounds())
    setTimeout(() => map.invalidateSize(), 100) // Sécurité d'affichage
  } catch(e) { console.warn("Tracé indisponible") }
})
</script>

<template>
  <div ref="mapContainer" class="w-full h-40 bg-gray-100 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 z-0"></div>
</template>