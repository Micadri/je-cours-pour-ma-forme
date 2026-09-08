import { ref } from 'vue'

export function useWeather() {
  const temperature = ref(null)
  const isWeatherLoading = ref(false)

  const fetchWeather = async (lat, lon) => {
    isWeatherLoading.value = true
    try {
      // API gratuite ne nécessitant aucune clé
      const res = await fetch(`https://api.open-meteo.com/v1/forecast?latitude=${lat}&longitude=${lon}&current_weather=true`)
      const data = await res.json()
      temperature.value = Math.round(data.current_weather.temperature)
    } catch (e) {
      console.warn("Météo indisponible hors-ligne.")
    } finally {
      isWeatherLoading.value = false
    }
  }

  return { temperature, isWeatherLoading, fetchWeather }
}