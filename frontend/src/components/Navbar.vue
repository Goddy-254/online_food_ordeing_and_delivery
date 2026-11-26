<script setup>
import { ref } from 'vue'
import { useRouter, RouterLink } from 'vue-router'
import { useCartStore } from '@/stores/cart'
import { useAuthStore } from '@/stores/auth'

const cartStore = useCartStore()
const authStore = useAuthStore()
const router = useRouter()
const mobileMenuOpen = ref(false)

async function handleLogout() {
  await authStore.logout()
  mobileMenuOpen.value = false
  router.push('/login')
}
</script>

<template>
  <nav class="relative z-20 border-b border-slate-100 bg-white">
    <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
      <RouterLink to="/" class="flex items-center gap-2.5" @click="mobileMenuOpen = false">
        <span class="grid h-10 w-10 place-items-center rounded-xl bg-emerald-700 text-lg font-black text-white">F</span>
        <span class="text-xl font-black tracking-tight text-slate-900">Food<span class="text-emerald-700">Express</span></span>
      </RouterLink>

      <div class="hidden items-center gap-7 md:flex">
        <RouterLink to="/" class="text-sm font-semibold text-slate-600 transition hover:text-emerald-700">Home</RouterLink>
        <RouterLink to="/menu" class="text-sm font-semibold text-slate-600 transition hover:text-emerald-700">Menu</RouterLink>
        <RouterLink to="/cart" class="relative text-sm font-semibold text-slate-600 transition hover:text-emerald-700">
          Cart
          <span class="ml-1 rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-bold text-emerald-800">{{ cartStore.cartCount }}</span>
        </RouterLink>

        <template v-if="authStore.isAuthenticated()">
          <span class="text-sm font-semibold text-slate-600">Hi, {{ authStore.user?.name || 'Customer' }}</span>
          <button class="rounded-xl bg-slate-100 px-4 py-2 text-sm font-bold text-slate-700 transition hover:bg-red-50 hover:text-red-700" @click="handleLogout">Sign out</button>
        </template>
        <template v-else>
          <RouterLink to="/login" class="text-sm font-bold text-slate-600 hover:text-emerald-700">Sign in</RouterLink>
          <RouterLink to="/register" class="rounded-xl bg-emerald-700 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-emerald-800">Create account</RouterLink>
        </template>
      </div>

      <button
        class="rounded-xl border border-slate-200 px-3 py-2 text-sm font-bold text-slate-700 md:hidden"
        :aria-expanded="mobileMenuOpen"
        aria-controls="mobile-navigation"
        @click="mobileMenuOpen = !mobileMenuOpen"
      >
        {{ mobileMenuOpen ? 'Close' : 'Menu' }}
      </button>
    </div>

    <!-- Small-screen navigation keeps the customer actions reachable on mobile. -->
    <div v-if="mobileMenuOpen" id="mobile-navigation" class="border-t border-slate-100 bg-white px-4 py-3 md:hidden">
      <div class="mx-auto max-w-7xl space-y-1">
        <RouterLink to="/" class="block rounded-lg px-3 py-3 font-semibold text-slate-700 hover:bg-emerald-50" @click="mobileMenuOpen = false">Home</RouterLink>
        <RouterLink to="/menu" class="block rounded-lg px-3 py-3 font-semibold text-slate-700 hover:bg-emerald-50" @click="mobileMenuOpen = false">Menu</RouterLink>
        <RouterLink to="/cart" class="block rounded-lg px-3 py-3 font-semibold text-slate-700 hover:bg-emerald-50" @click="mobileMenuOpen = false">Cart <span class="ml-1 rounded-full bg-emerald-100 px-2 py-0.5 text-xs text-emerald-800">{{ cartStore.cartCount }}</span></RouterLink>
        <template v-if="authStore.isAuthenticated()">
          <p class="px-3 py-2 text-sm text-slate-500">Signed in as {{ authStore.user?.name || 'Customer' }}</p>
          <button class="w-full rounded-lg px-3 py-3 text-left font-semibold text-red-700 hover:bg-red-50" @click="handleLogout">Sign out</button>
        </template>
        <template v-else>
          <RouterLink to="/login" class="block rounded-lg px-3 py-3 font-semibold text-slate-700 hover:bg-emerald-50" @click="mobileMenuOpen = false">Sign in</RouterLink>
          <RouterLink to="/register" class="block rounded-lg bg-emerald-700 px-3 py-3 text-center font-bold text-white" @click="mobileMenuOpen = false">Create account</RouterLink>
        </template>
      </div>
    </div>
  </nav>
</template>
