<script setup>
import SkeletonCard from './SkeletonCard.vue'

const props = defineProps({
  metrics: {
    type: Object,
    default: null,
  },
  loading: {
    type: Boolean,
    default: false,
  },
  error: {
    type: Array,
    default: null,
  },
  updatedAgo: {
    type: String,
    default: '',
  },
})

defineEmits(['refresh'])

function money(value) {
  return new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL',
  }).format(Number(value ?? 0))
}
</script>

<template>
  <section>
    <div class="mb-3 flex items-center justify-between">
      <div>
        <h2 class="text-lg font-semibold text-slate-900">
          Métricas
        </h2>
        <p class="text-sm text-slate-500">
          Dados em cache por 5 minutos · {{ updatedAgo }}
        </p>
      </div>

      <button
        type="button"
        class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-white focus:outline focus:outline-2 focus:outline-offset-2 focus:outline-slate-900"
        aria-label="Atualizar métricas"
        @click="$emit('refresh')"
      >
        Refresh
      </button>
    </div>

    <div v-if="loading && !metrics" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
      <SkeletonCard />
      <SkeletonCard />
      <SkeletonCard />
      <SkeletonCard />
    </div>

    <div
      v-else-if="error"
      class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700"
    >
      {{ error[0] }}
    </div>

    <div v-else class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
      <article class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
        <p class="text-sm font-medium text-slate-500">Total de pedidos</p>
        <strong class="mt-2 block text-2xl font-bold text-slate-900">
          {{ metrics?.total_orders ?? 0 }}
        </strong>
      </article>

      <article class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
        <p class="text-sm font-medium text-slate-500">Receita total</p>
        <strong class="mt-2 block text-2xl font-bold text-slate-900">
          {{ money(metrics?.total_revenue) }}
        </strong>
      </article>

      <article class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
        <p class="text-sm font-medium text-slate-500">Ticket médio</p>
        <strong class="mt-2 block text-2xl font-bold text-slate-900">
          {{ money(metrics?.average_ticket) }}
        </strong>
      </article>

      <article class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
        <p class="text-sm font-medium text-slate-500">Cancelados</p>
        <strong class="mt-2 block text-2xl font-bold text-slate-900">
          {{ metrics?.cancelled_orders ?? 0 }}
        </strong>
      </article>
    </div>
  </section>
</template>