<script setup>
import { ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAdminAuthStore } from '../stores/adminAuth'

const auth = useAdminAuthStore()
const route = useRoute()
const router = useRouter()
const email = ref('')
const password = ref('')
const errorMessage = ref('')

async function submitLogin() {
  errorMessage.value = ''
  try {
    await auth.login({ email: email.value, password: password.value })
    const next = route.query.redirect
    const safeNext = typeof next === 'string' && next.startsWith('/admin') ? next : '/admin'
    await router.replace(safeNext)
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Unable to sign in. Please try again.'
  }
}
</script>

<template>
  <main class="grid min-h-screen bg-slate-950 lg:grid-cols-[1.1fr_.9fr]">
    <section class="hidden flex-col justify-between bg-[radial-gradient(ellipse_at_top_left,_var(--tw-gradient-stops))] from-emerald-500 via-slate-900 to-slate-950 p-12 text-white lg:flex">
      <a href="/" class="text-xl font-black tracking-tight">FoodExpress</a>
      <div class="max-w-xl pb-12">
        <p class="mb-5 text-sm font-bold uppercase tracking-[.25em] text-emerald-300">Operations, made clear</p>
        <h1 class="text-5xl font-black leading-tight">Run your store from one place.</h1>
        <p class="mt-6 max-w-lg text-lg leading-8 text-slate-300">Review orders, keep the menu current, and help customers get what they need.</p>
      </div>
      <p class="text-sm text-slate-400">Administrator access is restricted to approved accounts.</p>
    </section>

    <section class="flex items-center justify-center px-5 py-12">
      <div class="w-full max-w-md rounded-3xl bg-white p-8 shadow-2xl sm:p-10">
        <p class="text-sm font-bold uppercase tracking-[.2em] text-emerald-700">FoodExpress admin</p>
        <h2 class="mt-3 text-3xl font-black text-slate-900">Welcome back</h2>
        <p class="mt-2 text-slate-500">Sign in with your administrator account.</p>

        <div v-if="errorMessage" role="alert" class="mt-6 rounded-xl bg-red-50 p-3 text-sm font-medium text-red-700">{{ errorMessage }}</div>

        <form class="mt-8 space-y-5" @submit.prevent="submitLogin">
          <label class="block text-sm font-semibold text-slate-700">
            Email address
            <input v-model="email" type="email" required autocomplete="username" class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100" placeholder="admin@example.com">
          </label>
          <label class="block text-sm font-semibold text-slate-700">
            Password
            <input v-model="password" type="password" required autocomplete="current-password" class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100" placeholder="Your password">
          </label>
          <button :disabled="auth.loading" class="w-full rounded-xl bg-slate-950 px-4 py-3.5 font-bold text-white transition hover:bg-emerald-700 disabled:cursor-wait disabled:opacity-60">
            {{ auth.loading ? 'Signing in…' : 'Sign in to admin' }}
          </button>
        </form>
        <a href="/" class="mt-7 block text-center text-sm font-semibold text-slate-500 hover:text-emerald-700">← Back to the customer site</a>
      </div>
    </section>
  </main>
</template>
