<script setup>
import { computed, ref, watch } from 'vue'
import StatusBadge from './StatusBadge.vue'

const props = defineProps({
  open: {
    type: Boolean,
    default: false,
  },
  order: {
    type: Object,
    default: null,
  },
  onUpdateStatus: {
    type: Function,
    required: true,
  },
  affiliateSummary: {
    type: Object,
    default: null,
  },
})

defineEmits(['close'])

const selectedStatus = ref('')
const loading = ref(false)
const error = ref(null)

const transitions = {
  pending: ['approved', 'cancelled'],
  approved: ['refunded'],
  cancelled: [],
  refunded: [],
}

const availableStatuses = computed(() => {
  if (!props.order?.status) {
    return []
  }

  return transitions[props.order.status] ?? []
})

watch(
  () => props.order?.status,
  () => {
    selectedStatus.value = ''
    error.value = null
  }
)

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

  return new Intl.DateTimeFormat('pt-BR', {
    dateStyle: 'short',
    timeStyle: 'short',
  }).format(new Date(value))
}

async function submitStatus() {
  if (!selectedStatus.value || !props.order?.id) {
    return
  }

  loading.value = true
  error.value = null

  try {
    await props.onUpdateStatus(props.order.id, selectedStatus.value)
    selectedStatus.value = ''
  } catch (err) {
    error.value = err.payload?.errors?.status?.[0]
      ?? err.payload?.message
      ?? 'Não foi possível atualizar o status.'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div
    v-if="open"
    class="fixed inset-0 z-50"
    role="dialog"
    aria-modal="true"
    aria-label="Detalhes do pedido"
  >
    <div
      class="absolute inset-0 bg-slate-900/40"
      @click="$emit('close')"
    />

    <aside
      class="absolute right-0 top-0 h-full w-full max-w-xl overflow-y-auto bg-white p-5 shadow-xl sm:p-6"
    >
      <div class="flex items-start justify-between gap-4">
        <div>
          <h2 class="text-xl font-bold text-slate-900">
            Pedido #{{ order?.id ?? '-' }}
          </h2>
          <p class="text-sm text-slate-500">
            Detalhes, itens e histórico de status.
          </p>
        </div>

        <button
          type="button"
          class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-full border border-slate-300 bg-white text-slate-700 shadow-sm transition hover:bg-slate-50 hover:text-slate-900 focus:outline focus:outline-2 focus:outline-offset-2 focus:outline-slate-900"
          aria-label="Fechar painel de detalhes"
          title="Fechar"
          @click="$emit('close')"
        >
          <span aria-hidden="true" class="text-2xl leading-none">×</span>
        </button>
      </div>

      <div v-if="!order" class="mt-8 text-sm text-slate-500">
        Carregando detalhes...
      </div>

      <div v-else class="mt-6 space-y-6">
        <section class="rounded-xl border border-slate-200 p-4">
          <div class="flex items-center justify-between">
            <h3 class="font-semibold text-slate-900">Resumo</h3>
            <StatusBadge :status="order.status" />
          </div>

          <dl class="mt-4 grid grid-cols-2 gap-4 text-sm">
            <div>
              <dt class="text-slate-500">Afiliado</dt>
              <dd class="font-medium text-slate-900">
                {{ order.affiliate?.username ?? order.affiliate_name ?? '-' }}
              </dd>
            </div>

            <div>
              <dt class="text-slate-500">Valor</dt>
              <dd class="font-medium text-slate-900">
                {{ money(order.total) }}
              </dd>
            </div>

            <div>
              <dt class="text-slate-500">Data do pedido</dt>
              <dd class="font-medium text-slate-900">
                {{ formatDate(order.ordered_at ?? order.created_at) }}
              </dd>
            </div>

            <div>
              <dt class="text-slate-500">Status atual</dt>
              <dd class="font-medium text-slate-900">
                {{ order.status }}
              </dd>
            </div>
          </dl>
        </section>

        <section class="rounded-xl border border-slate-200 p-4">
          <h3 class="font-semibold text-slate-900">Resumo do afiliado</h3>

          <div v-if="!affiliateSummary" class="mt-3 text-sm text-slate-500">
            Carregando resumo do afiliado...
          </div>

          <dl v-else class="mt-4 grid grid-cols-2 gap-4 text-sm">
            <div>
              <dt class="text-slate-500">Total de pedidos</dt>
              <dd class="font-medium text-slate-900">
                {{ affiliateSummary.total_orders ?? 0 }}
              </dd>
            </div>

            <div>
              <dt class="text-slate-500">Receita</dt>
              <dd class="font-medium text-slate-900">
                {{ money(affiliateSummary.total_revenue) }}
              </dd>
            </div>

            <div>
              <dt class="text-slate-500">Ticket médio</dt>
              <dd class="font-medium text-slate-900">
                {{ money(affiliateSummary.average_ticket) }}
              </dd>
            </div>

            <div>
              <dt class="text-slate-500">Taxa de cancelamento</dt>
              <dd class="font-medium text-slate-900">
                {{ affiliateSummary.cancellation_rate ?? 0 }}%
              </dd>
            </div>
          </dl>
        </section>

        <section class="rounded-xl border border-slate-200 p-4">
          <h3 class="font-semibold text-slate-900">Itens</h3>

          <div class="mt-4 space-y-3">
            <div
              v-for="item in order.items ?? []"
              :key="item.id"
              class="flex items-center justify-between rounded-lg bg-slate-50 p-3 text-sm"
            >
              <div>
                <p class="font-medium text-slate-900">
                  {{ item.product?.title ?? item.product_name ?? `Produto #${item.product_id}` }}
                </p>
                <p class="text-slate-500">
                  Qtd: {{ item.quantity }} · Unitário: {{ money(item.unit_price) }}
                </p>
              </div>

              <strong class="text-slate-900">
                {{ money(item.quantity * item.unit_price) }}
              </strong>
            </div>

            <p
              v-if="!order.items || order.items.length === 0"
              class="text-sm text-slate-500"
            >
              Nenhum item encontrado para este pedido.
            </p>
          </div>
        </section>

        <section class="rounded-xl border border-slate-200 p-4">
          <h3 class="font-semibold text-slate-900">Alterar status</h3>

          <div v-if="availableStatuses.length === 0" class="mt-3 text-sm text-slate-500">
            Não existem transições disponíveis para este status.
          </div>

          <div v-else class="mt-4 flex gap-2">
            <select
              v-model="selectedStatus"
              class="min-w-0 flex-1 rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline focus:outline-2 focus:outline-offset-2 focus:outline-slate-900 bg-white text-slate-900"
              aria-label="Selecionar novo status"
            >
              <option value="">Selecione</option>
              <option
                v-for="status in availableStatuses"
                :key="status"
                :value="status"
              >
                {{ status }}
              </option>
            </select>

            <button
              type="button"
              class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white disabled:opacity-50 focus:outline focus:outline-2 focus:outline-offset-2 focus:outline-slate-900"
              :disabled="!selectedStatus || loading"
              @click="submitStatus"
            >
              Salvar
            </button>
          </div>

          <p
            v-if="error"
            class="mt-3 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700"
          >
            {{ error }}
          </p>
        </section>

        <section class="rounded-xl border border-slate-200 p-4">
          <h3 class="font-semibold text-slate-900">Histórico</h3>

          <ol class="mt-4 space-y-4">
            <li
              v-for="log in order.status_logs ?? order.logs ?? []"
              :key="log.id"
              class="relative border-l border-slate-200 pl-4"
            >
              <span class="absolute -left-1.5 top-1 h-3 w-3 rounded-full bg-slate-900" />

              <p class="text-sm font-medium text-slate-900">
                {{ log.from_status ?? 'criado' }} → {{ log.to_status }}
              </p>

              <p class="text-xs text-slate-500">
                {{ formatDate(log.changed_at ?? log.created_at) }}
                <span v-if="log.user">
                  · por {{ log.user.name }}
                </span>
              </p>
            </li>

            <li
              v-if="!(order.status_logs?.length || order.logs?.length)"
              class="text-sm text-slate-500"
            >
              Nenhuma mudança de status registrada.
            </li>
          </ol>
        </section>
      </div>
    </aside>
  </div>
</template>
