<script setup>
defineProps({
  eyebrow: { type: String, default: 'Admin portal' },
  title: { type: String, required: true },
  description: { type: String, default: '' },
})
</script>

<template>
  <div class="mb-7 flex flex-col justify-between gap-5 border-b border-slate-200/80 pb-6 sm:mb-8 sm:flex-row sm:items-end sm:pb-7">
    <div class="min-w-0">
      <p class="inline-flex items-center gap-2 rounded-full bg-emerald-50 px-3 py-1.5 text-[10px] font-extrabold uppercase tracking-[.18em] text-emerald-800 ring-1 ring-emerald-100"><span class="h-1.5 w-1.5 rounded-full bg-emerald-500" />{{ eyebrow }}</p>
      <h1 class="mt-3 text-2xl font-black tracking-tight text-slate-950 sm:text-3xl lg:text-4xl">{{ title }}</h1>
      <p v-if="description" class="mt-2 max-w-2xl text-sm leading-6 text-slate-500 sm:text-[15px]">{{ description }}</p>
    </div>
    <div v-if="$slots.default" class="shrink-0 [&>a]:inline-flex [&>a]:items-center [&>a]:justify-center [&>a]:rounded-xl [&>a]:border-slate-200 [&>a]:bg-white [&>a]:shadow-sm [&>a]:transition [&>a:hover]:border-emerald-200 [&>a:hover]:bg-emerald-50">
      <slot />
    </div>
  </div>
</template>
