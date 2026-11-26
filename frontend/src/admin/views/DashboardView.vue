<script setup>
import { computed, onMounted, ref } from 'vue'
import { RouterLink } from 'vue-router'
import api from '@/services/api'
import PageHeading from '../components/PageHeading.vue'
import StatusPill from '../components/StatusPill.vue'

const dashboard = ref(null)
const loading = ref(true)
const errorMessage = ref('')
const currency = new Intl.NumberFormat('en-KE', { style: 'currency', currency: 'KES', maximumFractionDigits: 0 })

const cards = computed(() => {
  const summary = dashboard.value?.summary || {}
  return [
    { label: "Today's orders", value: summary.orders_today ?? 0, note: 'Orders placed today', icon: '▤', color: 'bg-blue-50 text-blue-700' },
    { label: "Today's sales", value: currency.format(Number(summary.sales_today || 0)), note: 'Excluding cancelled orders', icon: '↗', color: 'bg-emerald-50 text-emerald-700' },
    { label: 'Needs attention', value: summary.pending_orders ?? 0, note: 'Orders waiting to be accepted', icon: '!', color: 'bg-amber-50 text-amber-700' },
    { label: 'Customers', value: summary.customers ?? 0, note: `${summary.menu_items ?? 0} menu items available`, icon: '♙', color: 'bg-violet-50 text-violet-700' },
  ]
})

const maxSales = computed(() => Math.max(1, ...(dashboard.value?.sales_last_7_days || []).map((day) => Number(day.total))))

async function loadDashboard() {
  loading.value = true
  errorMessage.value = ''
  try {
    const { data } = await api.get('/admin/dashboard')
    dashboard.value = data
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Could not load the dashboard.'
  } finally {
    loading.value = false
  }
}

onMounted(loadDashboard)
</script>

<template>
  <PageHeading title="Store overview" description="A quick view of what is happening across your store." />

  <div v-if="errorMessage" role="alert" class="mb-6 rounded-xl bg-red-50 p-4 text-sm font-medium text-red-700">{{ errorMessage }}</div>
  <div v-if="loading" class="rounded-2xl border border-slate-200 bg-white p-8 text-slate-500">Loading store activity…</div>

  <template v-else-if="dashboard">
    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
      <article v-for="card in cards" :key="card.label" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex items-start justify-between">
          <p class="text-sm font-semibold text-slate-500">{{ card.label }}</p>
          <span class="grid h-10 w-10 place-items-center rounded-xl text-xl font-black" :class="card.color">{{ card.icon }}</span>
        </div>
        <p class="mt-5 text-3xl font-black tracking-tight text-slate-950">{{ card.value }}</p>
        <p class="mt-1 text-xs text-slate-500">{{ card.note }}</p>
      </article>
    </section>

    <section class="mt-6 grid gap-6 xl:grid-cols-[1.15fr_.85fr]">
      <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex items-center justify-between">
          <div><h2 class="text-lg font-black text-slate-900">Recent orders</h2><p class="mt-1 text-sm text-slate-500">Latest orders from your customers</p></div>
          <RouterLink :to="{ name: 'admin-orders' }" class="text-sm font-bold text-emerald-700 hover:text-emerald-900">All orders →</RouterLink>
        </div>
        <div v-if="dashboard.recent_orders.length" class="mt-5 divide-y divide-slate-100">
          <RouterLink v-for="order in dashboard.recent_orders" :key="order.id" :to="{ name: 'admin-order-details', params: { id: order.id } }" class="flex flex-wrap items-center justify-between gap-3 py-4 hover:bg-slate-50">
            <div><p class="font-bold text-slate-800">Order #{{ order.id }}</p><p class="mt-1 text-sm text-slate-500">{{ order.user?.name || 'Customer' }} · {{ new Date(order.created_at).toLocaleString() }}</p></div>
            <div class="flex items-center gap-3"><StatusPill :status="order.status" /><span class="min-w-24 text-right font-bold">{{ currency.format(Number(order.total_amount)) }}</span></div>
          </RouterLink>
        </div>
        <p v-else class="mt-6 rounded-xl bg-slate-50 p-5 text-sm text-slate-500">No orders have been placed yet.</p>
      </div>

      <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="text-lg font-black text-slate-900">Sales · last 7 days</h2>
        <p class="mt-1 text-sm text-slate-500">Daily totals, excluding cancelled orders</p>
        <div v-if="dashboard.sales_last_7_days.length" class="mt-6 space-y-4">
          <div v-for="day in dashboard.sales_last_7_days" :key="day.day">
            <div class="mb-1.5 flex justify-between gap-3 text-xs"><span class="font-semibold text-slate-600">{{ new Date(`${day.day}T00:00:00`).toLocaleDateString(undefined, { month: 'short', day: 'numeric' }) }}</span><span class="font-bold text-slate-800">{{ currency.format(Number(day.total)) }}</span></div>
            <div class="h-2 overflow-hidden rounded-full bg-slate-100"><div class="h-full rounded-full bg-emerald-500" :style="{ width: `${Math.max(4, Number(day.total) / maxSales * 100)}%` }" /></div>
          </div>
        </div>
        <p v-else class="mt-6 rounded-xl bg-slate-50 p-5 text-sm text-slate-500">Sales activity will appear here as orders come in.</p>
      </div>
    </section>
  </template>
</template>
