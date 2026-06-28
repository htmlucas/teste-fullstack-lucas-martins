const headers = {
  Accept: 'application/json',
  'Content-Type': 'application/json',
}

async function request(url, options = {}) {
  const response = await fetch(url, {
    headers,
    ...options,
  })

  const payload = await response.json().catch(() => ({
    data: null,
    meta: {},
    errors: ['Invalid JSON response'],
  }))

  if (!response.ok) {
    const error = new Error('API request failed')
    error.status = response.status
    error.payload = payload
    throw error
  }

  return payload
}

export const api = {
  getOrders(query = '') {
    return request(`/api/orders${query}`)
  },

  getOrder(id) {
    return request(`/api/orders/${id}`)
  },

  getMetrics() {
    return request('/api/orders/metrics')
  },
  
  getAffiliateSummary(id) {
    return request(`/api/affiliates/${id}/summary`)
  },

  updateOrderStatus(id, status) {
    return request(`/api/orders/${id}/status`, {
      method: 'POST',
      body: JSON.stringify({ status }),
    })
  },
}