<script setup>
import { computed, ref, watch } from 'vue'
import { useRoute, useRouter, RouterLink, RouterView } from 'vue-router'
import { useAdminAuthStore } from '../stores/adminAuth'

const auth = useAdminAuthStore()
const router = useRouter()
const route = useRoute()
const mobileMenuOpen = ref(false)

const navigation = [
  { label: 'Overview', to: { name: 'admin-dashboard' }, icon: '⌂' },
  { label: 'Orders', to: { name: 'admin-orders' }, icon: '▤' },
  { label: 'Menu items', to: { name: 'admin-menu' }, icon: '◈' },
  { label: 'Categories', to: { name: 'admin-categories' }, icon: '▦' },
  { label: 'Customers', to: { name: 'admin-customers' }, icon: '♙' },
  { label: 'Users & roles', to: { name: 'admin-users' }, icon: '♧' },
  { label: 'Store settings', to: { name: 'admin-settings' }, icon: '⚙' },
]

// Use the active route to keep the shared workspace header in sync with its page.
const currentSection = computed(() => route.name === 'admin-order-details'
  ? 'Order details'
  : navigation.find((item) => item.to.name === route.name)?.label || 'Overview')

watch(() => route.path, () => { mobileMenuOpen.value = false })

async function signOut() {
  await auth.logout()
  router.replace({ name: 'admin-login' })
}
</script>

<template>
  <div class="min-h-screen bg-[#f4f7f5] text-slate-900 lg:flex">
    <aside
      id="admin-sidebar"
      class="fixed inset-y-0 left-0 z-40 flex w-[286px] flex-col border-r border-white/5 bg-[#10271f] px-4 py-5 text-white shadow-2xl shadow-slate-950/15 transition-transform duration-300 lg:sticky lg:top-0 lg:h-screen lg:shrink-0 lg:translate-x-0 lg:shadow-none"
      :class="mobileMenuOpen ? 'translate-x-0' : '-translate-x-full'"
    >
      <RouterLink :to="{ name: 'admin-dashboard' }" class="flex items-center gap-3 rounded-2xl px-2 py-2 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-300">
        <span class="grid h-12 w-12 place-items-center rounded-2xl bg-emerald-300 text-xl font-black text-[#10271f] shadow-lg shadow-emerald-950/20">F</span>
        <span>
          <span class="block text-lg font-bold tracking-tight">FoodExpress</span>
          <span class="text-[10px] font-bold uppercase tracking-[.22em] text-emerald-200/65">Admin workspace</span>
        </span>
      </RouterLink>

      <div class="mb-3 mt-9 flex items-center justify-between px-3"><p class="text-[10px] font-extrabold uppercase tracking-[.2em] text-slate-400">Workspace</p><span class="rounded-full border border-emerald-300/15 bg-emerald-300/10 px-2 py-1 text-[9px] font-bold uppercase tracking-wider text-emerald-200">Portal</span></div>
      <nav class="flex-1 space-y-1.5" aria-label="Admin navigation">
        <RouterLink
          v-for="item in navigation"
          :key="item.label"
          :to="item.to"
          class="group flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-semibold text-slate-300/85 transition duration-200 hover:bg-white/[.07] hover:text-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-300"
          active-class="!bg-emerald-300 !text-[#10271f] shadow-md shadow-black/10"
        >
          <span class="grid h-8 w-8 place-items-center rounded-lg bg-white/[.06] text-base transition group-hover:bg-white/10" aria-hidden="true">{{ item.icon }}</span>
          {{ item.label }}
          <span v-if="route.name === item.to.name" class="ml-auto h-1.5 w-1.5 rounded-full bg-current" aria-hidden="true" />
        </RouterLink>
      </nav>

      <div class="mt-6 rounded-2xl border border-white/10 bg-white/[.05] p-3.5">
        <div class="flex items-center gap-3">
          <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-emerald-200 text-sm font-black uppercase text-[#10271f]">{{ (auth.user?.name || 'A').slice(0, 1) }}</span>
          <div class="min-w-0"><p class="truncate text-sm font-bold">{{ auth.user?.name || 'Administrator' }}</p><p class="mt-0.5 truncate text-xs text-slate-400">{{ auth.user?.email }}</p></div>
        </div>
        <button class="mt-3 flex w-full items-center justify-between rounded-xl border border-white/10 px-3 py-2.5 text-left text-xs font-bold text-slate-300 transition hover:border-red-300/20 hover:bg-red-400/10 hover:text-red-200 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-red-200" @click="signOut">
          <span>Sign out</span><span aria-hidden="true">↗</span>
        </button>
      </div>
    </aside>

    <div v-if="mobileMenuOpen" class="fixed inset-0 z-30 bg-slate-950/55 backdrop-blur-[2px] lg:hidden" aria-hidden="true" @click="mobileMenuOpen = false" />

    <main class="min-h-screen min-w-0 flex-1">
      <header class="sticky top-0 z-20 border-b border-slate-200/80 bg-white/90 px-4 backdrop-blur-xl sm:px-7 lg:px-10">
        <div class="flex h-[4.5rem] items-center justify-between gap-4">
          <div class="flex min-w-0 items-center gap-3">
            <button class="grid h-10 w-10 shrink-0 place-items-center rounded-xl border border-slate-200 bg-white text-slate-700 shadow-sm transition hover:border-emerald-300 hover:text-emerald-800 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-emerald-100 lg:hidden" :aria-expanded="mobileMenuOpen" aria-controls="admin-sidebar" aria-label="Toggle admin menu" @click="mobileMenuOpen = !mobileMenuOpen">
              <span class="text-lg" aria-hidden="true">{{ mobileMenuOpen ? '×' : '☰' }}</span>
            </button>
            <div class="min-w-0"><p class="hidden text-[10px] font-extrabold uppercase tracking-[.18em] text-slate-400 sm:block">FoodExpress <span class="px-1 text-slate-300">/</span> Admin</p><h2 class="truncate text-sm font-black text-slate-900 sm:mt-0.5 sm:text-base">{{ currentSection }}</h2></div>
          </div>
          <div class="flex shrink-0 items-center gap-3">
            <span class="hidden items-center gap-2 rounded-full border border-emerald-100 bg-emerald-50 px-3 py-2 text-xs font-bold text-emerald-800 sm:inline-flex"><span class="h-2 w-2 rounded-full bg-emerald-500" />Admin session</span>
            <span class="grid h-9 w-9 place-items-center rounded-full bg-slate-900 text-xs font-black uppercase text-white sm:hidden">{{ (auth.user?.name || 'A').slice(0, 1) }}</span>
          </div>
        </div>
      </header>
      <div class="admin-content mx-auto max-w-[1500px] p-4 sm:p-6 lg:p-9">
        <RouterView />
      </div>
    </main>
  </div>
</template>
