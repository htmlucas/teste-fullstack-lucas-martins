<script setup>
import StatusBadge from './StatusBadge.vue'

defineProps({
  orders: {
    type: Array,
    default: () => [],
  },
  loading: {
    type: Boolean,
    default: false,
  },
})

defineEmits(['open-order'])

function money(value) {
  return new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL',
  }).format(Number(value ?? 0))
}

function formatDate(value) {
  if (!value) {
return '-'
}

  return new Intl.DateTimeFormat('pt-BR').format(new Date(value))
}
</script>

<template>
  <div class="space-y-3">
    <div
      v-if="loading"
      class="rounded-xl border border-slate-200 bg-white p-4 text-center text-sm text-slate-500"
    >
      Carregando pedidos...
    </div>

    <div
      v-else-if="orders.length === 0"
      class="rounded-xl border border-slate-200 bg-white p-4 text-center text-sm text-slate-500"
    >
      Nenhum pedido encontrado para os filtros aplicados.
    </div>

    <article
      v-for="order in orders"
      v-else
      :key="order.id"
      class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm"
    >
      <div class="flex items-start justify-between gap-3">
        <div>
          <h3 class="font-semibold text-slate-900">
            Pedido #{{ order.id }}
          </h3>
          <p class="text-sm text-slate-500">
            {{ order.affiliate?.username ?? order.affiliate_name ?? 'Afiliado não informado' }}
          </p>
        </div>

        <StatusBadge :status="order.status" />
      </div>

      <dl class="mt-4 grid grid-cols-2 gap-3 text-sm">
        <div>
          <dt class="text-slate-500">Valor</dt>
          <dd class="font-medium text-slate-900">
            {{ money(order.total) }}
          </dd>
        </div>

        <div>
          <dt class="text-slate-500">Data</dt>
          <dd class="font-medium text-slate-900">
            {{ formatDate(order.ordered_at ?? order.created_at) }}
          </dd>
        </div>
      </dl>

      <button
        type="button"
        class="mt-4 w-full rounded-lg bg-slate-900 px-3 py-2 text-sm font-medium text-white focus:outline focus:outline-2 focus:outline-offset-2 focus:outline-slate-900"
        :aria-label="`Abrir detalhes do pedido ${order.id}`"
        @click="$emit('open-order', order)"
      >
        Ver detalhes
      </button>
    </article>
  </div>
</template>