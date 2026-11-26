<script setup>
defineProps({
  eyebrow: { type: String, default: 'Admin portal' },
  title: { type: String, required: true },
  description: { type: String, default: '' },
})
</script>

<template>
  <div class="mb-8 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
    <div>
      <p class="text-xs font-bold uppercase tracking-[.2em] text-emerald-700">{{ eyebrow }}</p>
      <h1 class="mt-2 text-3xl font-black tracking-tight text-slate-950 sm:text-4xl">{{ title }}</h1>
      <p v-if="description" class="mt-2 max-w-2xl text-sm leading-6 text-slate-500">{{ description }}</p>
    </div>
    <slot />
  </div>
</template>
