<script setup>
import { ref } from 'vue'
import { useRouter, RouterLink, RouterView } from 'vue-router'
import { useAdminAuthStore } from '../stores/adminAuth'

const auth = useAdminAuthStore()
const router = useRouter()
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

async function signOut() {
  await auth.logout()
  router.replace({ name: 'admin-login' })
}
</script>

<template>
  <div class="min-h-screen bg-slate-100 text-slate-900 lg:flex">
    <aside
      class="fixed inset-y-0 left-0 z-40 w-72 bg-slate-950 px-5 py-6 text-white transition-transform lg:static lg:translate-x-0"
      :class="mobileMenuOpen ? 'translate-x-0' : '-translate-x-full'"
    >
      <RouterLink :to="{ name: 'admin-dashboard' }" class="flex items-center gap-3 px-2">
        <span class="grid h-11 w-11 place-items-center rounded-2xl bg-emerald-400 text-xl font-black text-slate-950">F</span>
        <span>
          <span class="block text-lg font-bold tracking-tight">FoodExpress</span>
          <span class="text-xs font-medium uppercase tracking-[.2em] text-slate-400">Admin portal</span>
        </span>
      </RouterLink>

      <p class="mb-3 mt-10 px-3 text-xs font-bold uppercase tracking-[.18em] text-slate-500">Workspace</p>
      <nav class="space-y-1">
        <RouterLink
          v-for="item in navigation"
          :key="item.label"
          :to="item.to"
          class="flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-semibold text-slate-300 transition hover:bg-slate-800 hover:text-white"
          active-class="!bg-emerald-400 !text-slate-950"
          @click="mobileMenuOpen = false"
        >
          <span class="w-6 text-center text-lg" aria-hidden="true">{{ item.icon }}</span>
          {{ item.label }}
        </RouterLink>
      </nav>

      <div class="absolute bottom-6 left-5 right-5 rounded-2xl border border-slate-800 bg-slate-900 p-4">
        <p class="truncate text-sm font-semibold">{{ auth.user?.name || 'Administrator' }}</p>
        <p class="mt-1 truncate text-xs text-slate-400">{{ auth.user?.email }}</p>
        <button class="mt-4 w-full rounded-lg border border-slate-700 px-3 py-2 text-left text-sm text-slate-300 hover:bg-slate-800" @click="signOut">
          Sign out
        </button>
      </div>
    </aside>

    <div v-if="mobileMenuOpen" class="fixed inset-0 z-30 bg-slate-950/50 lg:hidden" @click="mobileMenuOpen = false" />

    <main class="min-w-0 flex-1">
      <header class="sticky top-0 z-20 flex h-16 items-center justify-between border-b border-slate-200 bg-white/90 px-4 backdrop-blur sm:px-8">
        <button class="rounded-lg border border-slate-200 px-3 py-2 text-sm font-semibold lg:hidden" @click="mobileMenuOpen = !mobileMenuOpen">
          Menu
        </button>
        <p class="hidden text-sm text-slate-500 sm:block">Store operations</p>
        <a href="/menu" class="text-sm font-semibold text-emerald-700 hover:text-emerald-900">View customer site ↗</a>
      </header>
      <div class="mx-auto max-w-7xl p-4 sm:p-8">
        <RouterView />
      </div>
    </main>
  </div>
</template>
