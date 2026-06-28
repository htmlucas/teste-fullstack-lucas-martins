import { ref, onMounted, onUnmounted } from 'vue'
import { api } from '@/services/api'

export function useMetrics() {
  const metrics = ref(null)
  const loading = ref(false)
  const error = ref(null)
  const lastUpdatedAt = ref(null)

  let interval = null

  async function fetchMetrics() {
    loading.value = true
    error.value = null

    try {
      const response = await api.getMetrics()
      metrics.value = response.data
      lastUpdatedAt.value = new Date()
    } catch (err) {
      error.value = err.payload?.errors ?? ['Erro ao carregar métricas.']
    } finally {
      loading.value = false
    }
  }

  function updatedAgo() {
    if (!lastUpdatedAt.value) return 'não atualizado'

    const diffInSeconds = Math.floor((new Date() - lastUpdatedAt.value) / 1000)

    if (diffInSeconds < 60) {
      return `atualizado há ${diffInSeconds}s`
    }

    return `atualizado há ${Math.floor(diffInSeconds / 60)}min`
  }

  onMounted(() => {
    fetchMetrics()
    interval = setInterval(fetchMetrics, 60000)
  })

  onUnmounted(() => {
    clearInterval(interval)
  })

  return {
    metrics,
    loading,
    error,
    fetchMetrics,
    updatedAgo,
  }
}