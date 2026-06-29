<script setup>
defineProps({
  status: {
    type: String,
    required: true,
  },
})

const classes = {
  pending: 'bg-yellow-50 text-yellow-700 ring-yellow-600/20',
  approved: 'bg-green-50 text-green-700 ring-green-600/20',
  cancelled: 'bg-red-50 text-red-700 ring-red-600/20',
  refunded: 'bg-blue-50 text-blue-700 ring-blue-600/20',
}

const labels = {
  pending: 'Pendente',
  approved: 'Aprovado',
  cancelled: 'Cancelado',
  refunded: 'Reembolsado',
}
</script>

<template>
  <span
    class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium ring-1 ring-inset"
    :class="classes[status] ?? 'bg-slate-50 text-slate-700 ring-slate-600/20'"
  >
    {{ labels[status] ?? status }}
  </span>
</template>