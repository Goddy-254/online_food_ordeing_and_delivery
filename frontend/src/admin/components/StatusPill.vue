<script setup>
defineProps({ status: { type: String, required: true } })

const styles = {
  starting: 'bg-blue-100 text-blue-800',
  uninitiated: 'bg-slate-100 text-slate-600',
  pending: 'bg-amber-100 text-amber-800',
  preparing: 'bg-blue-100 text-blue-800',
  out_for_delivery: 'bg-violet-100 text-violet-800',
  delivered: 'bg-emerald-100 text-emerald-800',
  cancelled: 'bg-red-100 text-red-800',
  paid: 'bg-emerald-100 text-emerald-800',
  failed: 'bg-red-100 text-red-800',
  review: 'bg-violet-100 text-violet-800',
}

const label = (value) => value.replaceAll('_', ' ')
</script>

<template>
  <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-bold capitalize" :class="styles[status] || 'bg-slate-100 text-slate-700'">
    {{ label(status) }}
  </span>
</template>
