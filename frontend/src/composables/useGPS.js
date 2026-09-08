import { ref } from 'vue'

export function useGPS() {
  const pathCoordinates = ref([]) // Pour dessiner la carte Leaflet
  const distanceKm = ref(0)
  const elevationGain = ref(0)
  
  let watcherId = null
  let lastPos = null

  // Formule mathématique pour calculer la distance entre deux points GPS
  const getHaversineDistance = (lat1, lon1, lat2, lon2) => {
    const R = 6371 // Rayon de la Terre en km
    const dLat = (lat2 - lat1) * Math.PI / 180
    const dLon = (lon2 - lon1) * Math.PI / 180
    const a = Math.sin(dLat/2) * Math.sin(dLat/2) + Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) * Math.sin(dLon/2) * Math.sin(dLon/2)
    return R * (2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a)))
  }

  const startTracking = () => {
    if (!('geolocation' in navigator)) return
    
    watcherId = navigator.geolocation.watchPosition((pos) => {
      const { latitude, longitude, altitude } = pos.coords
      
      // Stockage pour le tracé de la carte
      pathCoordinates.value.push([latitude, longitude])

      // Calcul des statistiques
      if (lastPos) {
        distanceKm.value += getHaversineDistance(lastPos.lat, lastPos.lon, latitude, longitude)
        if (altitude && lastPos.alt && altitude > lastPos.alt) {
          elevationGain.value += Math.round(altitude - lastPos.alt)
        }
      }
      lastPos = { lat: latitude, lon: longitude, alt: altitude }
      
    }, (err) => console.warn("Erreur GPS :", err), { enableHighAccuracy: true })
  }

  const stopTracking = () => {
    if (watcherId) navigator.geolocation.clearWatch(watcherId)
  }

  // Calcul du temps moyen pour parcourir 1 km (Allure)
  const calculatePace = (totalSeconds) => {
    if (distanceKm.value === 0) return "0:00"
    const paceSeconds = Math.round(totalSeconds / distanceKm.value)
    const m = Math.floor(paceSeconds / 60)
    const s = (paceSeconds % 60).toString().padStart(2, '0')
    return `${m}:${s}`
  }

  return { pathCoordinates, distanceKm, elevationGain, startTracking, stopTracking, calculatePace }
}