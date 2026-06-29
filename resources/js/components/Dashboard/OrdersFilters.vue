<script setup>
defineProps({
  filters: {
    type: Object,
    required: true,
  },
})

const emit = defineEmits(['update-filter'])

function updateFilter(key, event) {
  emit('update-filter', key, event.target.value)
}
</script>

<template>
  <form class="grid gap-3 md:grid-cols-3 lg:grid-cols-6" @submit.prevent>
    <label class="space-y-1">
      <span class="text-xs font-medium text-slate-600">Afiliado ID</span>
      <input
        :value="filters.affiliate_id"
        type="number"
        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:outline focus:outline-2 focus:outline-offset-2 focus:outline-slate-900"
        placeholder="Ex: 1"
        @input="updateFilter('affiliate_id', $event)"
      />
    </label>

    <label class="space-y-1">
      <span class="text-xs font-medium text-slate-600">Status</span>
      <select
        :value="filters.status"
        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:outline focus:outline-2 focus:outline-offset-2 focus:outline-slate-900"
        @change="updateFilter('status', $event)"
      >
        <option value="">Todos</option>
        <option value="pending">Pendente</option>
        <option value="approved">Aprovado</option>
        <option value="cancelled">Cancelado</option>
        <option value="refunded">Reembolsado</option>
      </select>
    </label>

    <label class="space-y-1">
      <span class="text-xs font-medium text-slate-600">De</span>
      <input
        :value="filters.date_from"
        type="date"
        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:outline focus:outline-2 focus:outline-offset-2 focus:outline-slate-900"
        @input="updateFilter('date_from', $event)"
      />
    </label>

    <label class="space-y-1">
      <span class="text-xs font-medium text-slate-600">Até</span>
      <input
        :value="filters.date_to"
        type="date"
        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:outline focus:outline-2 focus:outline-offset-2 focus:outline-slate-900"
        @input="updateFilter('date_to', $event)"
      />
    </label>

    <label class="space-y-1">
      <span class="text-xs font-medium text-slate-600">Valor mín.</span>
      <input
        :value="filters.min_value"
        type="number"
        step="0.01"
        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:outline focus:outline-2 focus:outline-offset-2 focus:outline-slate-900"
        placeholder="0.00"
        @input="updateFilter('min_value', $event)"
      />
    </label>

    <label class="space-y-1">
      <span class="text-xs font-medium text-slate-600">Valor máx.</span>
      <input
        :value="filters.max_value"
        type="number"
        step="0.01"
        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:outline focus:outline-2 focus:outline-offset-2 focus:outline-slate-900"
        placeholder="999.00"
        :min="filters.min_value || 0"
        @input="updateFilter('max_value', $event)"
      />
    </label>
  </form>
</template>