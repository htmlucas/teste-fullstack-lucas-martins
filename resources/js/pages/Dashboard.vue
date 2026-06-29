<script setup>
import MetricsCards from '@/components/Dashboard/MetricsCards.vue'
import OrdersFilters from '@/components/Dashboard/OrdersFilters.vue'
import OrdersTable from '@/components/Dashboard/OrdersTable.vue'
import OrdersMobileList from '@/components/Dashboard/OrdersMobileList.vue'
import OrderDrawer from '@/components/Dashboard/OrderDrawer.vue'

import { useMetrics } from '@/composables/useMetrics'
import { useOrders } from '@/composables/useOrders'

const metrics = useMetrics()
const orders = useOrders()
</script>

<template>
  <main class="min-h-screen bg-slate-50 p-4 sm:p-6 lg:p-8">
    <div class="mx-auto max-w-7xl space-y-6">
      <header class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
        <div>
          <h1 class="text-2xl font-bold text-slate-900">
            Gestão de Pedidos
          </h1>
          <p class="text-sm text-slate-500">
            Dashboard de pedidos, afiliados e status da operação.
          </p>
        </div>

        <span class="text-xs text-slate-500">
          {{ metrics.updatedAgo() }}
        </span>
      </header>

      <MetricsCards
        :metrics="metrics.metrics.value"
        :loading="metrics.loading.value"
        :error="metrics.error.value"
        :updated-ago="metrics.updatedAgo()"
        @refresh="metrics.fetchMetrics"
      />

      <section class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
        <div class="mb-4 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
          <div>
            <h2 class="text-lg font-semibold text-slate-900">
              Pedidos
            </h2>
            <p class="text-sm text-slate-500">
              Filtre, ordene e acompanhe os pedidos importados.
            </p>
          </div>

          <div class="flex flex-wrap items-center justify-start gap-2 sm:justify-end">
            <span
              v-if="orders.selectedIds.value.length > 0"
              class="rounded-lg bg-slate-100 px-3 py-2 text-sm text-slate-700"
            >
              {{ orders.selectedIds.value.length }} selecionado(s)
            </span>

            <button
              v-if="orders.selectedIds.value.length > 0"
              type="button"
              class="rounded-lg border border-red-300 px-3 py-2 text-sm font-medium text-red-700 hover:bg-red-50 disabled:opacity-50 focus:outline focus:outline-2 focus:outline-offset-2 focus:outline-red-700"
              aria-label="Cancelar pedidos selecionados"
              :disabled="orders.bulkLoading.value"
              @click="orders.cancelSelectedOrders"
            >
              {{ orders.bulkLoading.value ? 'Cancelando...' : 'Cancelar selecionados' }}
            </button>

            <button
              v-if="orders.selectedIds.value.length > 0"
              type="button"
              class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 focus:outline focus:outline-2 focus:outline-offset-2 focus:outline-slate-900"
              aria-label="Limpar seleção de pedidos"
              @click="orders.clearSelection"
            >
              Limpar seleção
            </button>

            <button
              type="button"
              class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 focus:outline focus:outline-2 focus:outline-offset-2 focus:outline-slate-900"
              aria-label="Atualizar lista de pedidos"
              @click="orders.fetchOrders"
            >
              Atualizar
            </button>
          </div>
        </div>

        <OrdersFilters :filters="orders.filters" />

        <div
          v-if="orders.error.value"
          class="mt-4 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700"
        >
          {{ orders.error.value[0] }}
        </div>

        <div
          v-if="orders.bulkError.value"
          class="mt-4 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700"
        >
          {{ orders.bulkError.value[0] }}
        </div>

        <div class="mt-4 hidden md:block">
          <OrdersTable
            :orders="orders.orders.value"
            :meta="orders.meta.value"
            :loading="orders.loading.value"
            :filters="orders.filters"
            :selected-ids="orders.selectedIds.value"
            @sort="orders.sortBy"
            @change-page="orders.changePage"
            @toggle-selection="orders.toggleSelection"
            @open-order="orders.openOrder"
          />
        </div>

        <div class="mt-4 md:hidden">
          <OrdersMobileList
            :orders="orders.orders.value"
            :loading="orders.loading.value"
            @open-order="orders.openOrder"
          />
        </div>
      </section>

      <OrderDrawer
        :open="orders.drawerOpen.value"
        :order="orders.selectedOrderDetails.value"
        :affiliate-summary="orders.affiliateSummary.value"
        @close="orders.closeDrawer"
        :on-update-status="orders.updateStatus"
      />
    </div>
  </main>
</template>