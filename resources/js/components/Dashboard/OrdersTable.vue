<script setup>
import StatusBadge from './StatusBadge.vue'

defineProps({
  orders: {
    type: Array,
    default: () => [],
  },
  meta: {
    type: Object,
    default: () => ({}),
  },
  loading: {
    type: Boolean,
    default: false,
  },
  filters: {
    type: Object,
    required: true,
  },
  selectedIds: {
    type: Array,
    default: () => [],
  },
})

defineEmits([
  'sort',
  'change-page',
  'toggle-selection',
  'open-order',
])

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
  <div class="overflow-hidden rounded-xl border border-slate-200">
    <table class="min-w-full divide-y divide-slate-200">
      <thead class="bg-slate-50">
        <tr>
          <th class="px-4 py-3 text-left">
            <span class="sr-only">Selecionar</span>
          </th>

          <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">
            <button
              type="button"
              class="focus:outline focus:outline-2 focus:outline-offset-2 focus:outline-slate-900"
              @click="$emit('sort', 'id')"
            >
              ID
            </button>
          </th>

          <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">
            Afiliado
          </th>

          <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">
            <button
              type="button"
              class="focus:outline focus:outline-2 focus:outline-offset-2 focus:outline-slate-900"
              @click="$emit('sort', 'total')"
            >
              Valor
            </button>
          </th>

          <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">
            Status
          </th>

          <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">
            <button
              type="button"
              class="focus:outline focus:outline-2 focus:outline-offset-2 focus:outline-slate-900"
              @click="$emit('sort', 'created_at')"
            >
              Data
            </button>
          </th>

          <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-slate-500">
            Ações
          </th>
        </tr>
      </thead>

      <tbody class="divide-y divide-slate-200 bg-white">
        <tr v-if="loading">
          <td colspan="7" class="px-4 py-8 text-center text-sm text-slate-500">
            Carregando pedidos...
          </td>
        </tr>

        <tr v-else-if="orders.length === 0">
          <td colspan="7" class="px-4 py-8 text-center text-sm text-slate-500">
            Nenhum pedido encontrado para os filtros aplicados.
          </td>
        </tr>

        <tr
          v-for="order in orders"
          v-else
          :key="order.id"
          class="hover:bg-slate-50"
        >
          <td class="px-4 py-3">
            <input
              type="checkbox"
              class="rounded border-slate-300 focus:ring-slate-900"
              :checked="selectedIds.includes(order.id)"
              :aria-label="`Selecionar pedido ${order.id}`"
              @change="$emit('toggle-selection', order.id)"
            />
          </td>

          <td class="px-4 py-3 text-sm font-medium text-slate-900">
            #{{ order.id }}
          </td>

          <td class="px-4 py-3 text-sm text-slate-600">
            {{ order.affiliate?.username ?? order.affiliate_name ?? '-' }}
          </td>

          <td class="px-4 py-3 text-sm text-slate-600">
            {{ money(order.total) }}
          </td>

          <td class="px-4 py-3">
            <StatusBadge :status="order.status" />
          </td>

          <td class="px-4 py-3 text-sm text-slate-600">
            {{ formatDate(order.ordered_at ?? order.created_at) }}
          </td>

          <td class="px-4 py-3 text-right">
            <button
              type="button"
              class="text-sm font-medium text-slate-900 underline-offset-4 hover:underline focus:outline focus:outline-2 focus:outline-offset-2 focus:outline-slate-900"
              :aria-label="`Ver detalhes do pedido ${order.id}`"
              @click="$emit('open-order', order)"
            >
              Ver detalhes
            </button>
          </td>
        </tr>
      </tbody>
    </table>

    <div class="flex items-center justify-between border-t border-slate-200 bg-white px-4 py-3">
      <p class="text-sm text-slate-500">
        Página {{ meta.current_page ?? 1 }} de {{ meta.last_page ?? 1 }}
      </p>

      <div class="flex gap-2">
        <button
          type="button"
          class="rounded-lg border border-slate-300 px-3 py-2 text-sm disabled:opacity-50"
          :disabled="(meta.current_page ?? 1) <= 1"
          @click="$emit('change-page', (meta.current_page ?? 1) - 1)"
        >
          Anterior
        </button>

        <button
          type="button"
          class="rounded-lg border border-slate-300 px-3 py-2 text-sm disabled:opacity-50"
          :disabled="(meta.current_page ?? 1) >= (meta.last_page ?? 1)"
          @click="$emit('change-page', (meta.current_page ?? 1) + 1)"
        >
          Próxima
        </button>
      </div>
    </div>
  </div>
</template>