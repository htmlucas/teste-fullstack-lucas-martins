import { ref, reactive, watch, onMounted } from 'vue'
import { api } from '@/services/api'

export function useOrders() {
  const orders = ref([])
  const meta = ref({})
  const loading = ref(false)
  const error = ref(null)

  const selectedOrder = ref(null)
  const selectedOrderDetails = ref(null)
  const drawerOpen = ref(false)
  const selectedIds = ref([])

  const filters = reactive({
    affiliate_id: '',
    status: '',
    date_from: '',
    date_to: '',
    min_value: '',
    max_value: '',
    sort_by: 'created_at',
    sort_dir: 'desc',
    page: 1,
  })

  const affiliateSummary = ref(null)

  let debounceTimer = null

  function loadFiltersFromUrl() {
    const params = new URLSearchParams(window.location.search)

    Object.keys(filters).forEach((key) => {
      if (params.has(key)) {
        const value = params.get(key)

        if (key === 'affiliate_id' && Number(value) <= 0) {
          filters[key] = ''
          return
        }

        filters[key] = value
      }
    })
  }

  function buildQuery() {
    const params = new URLSearchParams()

    Object.entries(filters).forEach(([key, value]) => {
      if (value === null || value === '') {
        return
      }

      if (key === 'affiliate_id' && Number(value) <= 0) {
        return
      }

      params.set(key, value)
    })

    return params.toString() ? `?${params.toString()}` : ''
  }

  function syncUrl() {
    const query = buildQuery()
    window.history.replaceState({}, '', `${window.location.pathname}${query}`)
  }

  async function fetchOrders() {
    loading.value = true
    error.value = null

    try {
      syncUrl()

      const response = await api.getOrders(buildQuery())

      orders.value = response.data ?? []
      meta.value = response.meta ?? {}
    } catch (err) {
      error.value = err.payload?.errors ?? ['Erro ao carregar pedidos.']
    } finally {
      loading.value = false
    }
  }

  function debouncedFetch() {
    clearTimeout(debounceTimer)

    debounceTimer = setTimeout(() => {
      filters.page = 1
      fetchOrders()
    }, 400)
  }

  function changePage(page) {
    filters.page = page
    fetchOrders()
  }

  function sortBy(column) {
    if (filters.sort_by === column) {
      filters.sort_dir = filters.sort_dir === 'asc' ? 'desc' : 'asc'
    } else {
      filters.sort_by = column
      filters.sort_dir = 'asc'
    }

    fetchOrders()
  }

  function toggleSelection(id) {
    if (selectedIds.value.includes(id)) {
      selectedIds.value = selectedIds.value.filter((item) => item !== id)
      return
    }

    selectedIds.value.push(id)
  }

  function clearSelection() {
    selectedIds.value = []
  }

  async function openOrder(order) {
    selectedOrder.value = order
    drawerOpen.value = true
    selectedOrderDetails.value = null
    affiliateSummary.value = null

    try {
      const response = await api.getOrder(order.id)
      selectedOrderDetails.value = response.data

      const affiliateId = response.data?.affiliate?.id

      if (affiliateId) {
        const summary = await api.getAffiliateSummary(affiliateId)
        affiliateSummary.value = summary.data
      }
    } catch (err) {
      error.value = err.payload?.errors ?? ['Erro ao carregar detalhes do pedido.']
    }
  }

  function closeDrawer() {
    drawerOpen.value = false
    selectedOrder.value = null
    selectedOrderDetails.value = null
    affiliateSummary.value = null
  }

  async function updateStatus(orderId, status) {
    const response = await api.updateOrderStatus(orderId, status)

    await fetchOrders()

    if (selectedOrder.value?.id === orderId) {
      const detail = await api.getOrder(orderId)
      selectedOrderDetails.value = detail.data
    }

    return response
  }

  watch(
    () => ({
      affiliate_id: filters.affiliate_id,
      status: filters.status,
      date_from: filters.date_from,
      date_to: filters.date_to,
      min_value: filters.min_value,
      max_value: filters.max_value,
    }),
    debouncedFetch,
    { deep: true }
  )

  onMounted(() => {
    loadFiltersFromUrl()
    fetchOrders()
  })

  return {
    orders,
    meta,
    loading,
    error,
    filters,
    selectedIds,
    selectedOrder,
    selectedOrderDetails,
    affiliateSummary,
    drawerOpen,
    fetchOrders,
    changePage,
    sortBy,
    toggleSelection,
    clearSelection,
    openOrder,
    closeDrawer,
    updateStatus,
    
  }
}