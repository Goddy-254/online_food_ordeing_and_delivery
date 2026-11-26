<script setup>
import { onMounted, ref } from 'vue'
import { RouterLink } from 'vue-router'
import api from '@/services/api'
import PageHeading from '../components/PageHeading.vue'
import StatusPill from '../components/StatusPill.vue'

const results = ref({ data: [], current_page: 1, last_page: 1, total: 0 })
const search = ref('')
const status = ref('')
const loading = ref(false)
const errorMessage = ref('')
const currency = new Intl.NumberFormat('en-KE', { style: 'currency', currency: 'KES', maximumFractionDigits: 0 })
const statuses = ['pending', 'preparing', 'out_for_delivery', 'delivered', 'cancelled']

// The API returns payment attempts newest first; show the current attempt in the table.
function currentPayment(order) {
  return order.payments?.[0] || null
}

function paymentStatus(payment) {
  return payment?.status === 'failed' && Number(payment.result_code) === 1032
    ? 'cancelled'
    : payment?.status
}

async function loadOrders(page = 1) {
  loading.value = true
  errorMessage.value = ''
  try {
    const { data } = await api.get('/admin/orders', { params: { page, search: search.value || undefined, status: status.value || undefined } })
    results.value = data
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Could not load orders.'
  } finally {
    loading.value = false
  }
}

onMounted(() => loadOrders())
</script>

<template>
  <PageHeading title="Orders" description="Review incoming orders and follow each one through delivery." />
  <div v-if="errorMessage" role="alert" class="mb-5 rounded-xl bg-red-50 p-4 text-sm text-red-700">{{ errorMessage }}</div>

  <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
    <form class="flex flex-col gap-3 border-b border-slate-200 p-4 sm:flex-row" @submit.prevent="loadOrders(1)">
      <input v-model="search" class="min-w-0 flex-1 rounded-xl border border-slate-300 px-4 py-2.5 text-sm outline-none focus:border-emerald-500" placeholder="Search order, customer or email">
      <select v-model="status" class="rounded-xl border border-slate-300 px-4 py-2.5 text-sm capitalize outline-none focus:border-emerald-500" @change="loadOrders(1)">
        <option value="">All statuses</option>
        <option v-for="item in statuses" :key="item" :value="item">{{ item.replaceAll('_', ' ') }}</option>
      </select>
      <button class="rounded-xl bg-slate-950 px-5 py-2.5 text-sm font-bold text-white hover:bg-emerald-700">Search</button>
    </form>

    <div class="overflow-x-auto">
      <table class="w-full min-w-[900px] text-left text-sm">
        <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500"><tr><th class="px-5 py-4">Order</th><th class="px-5 py-4">Customer</th><th class="px-5 py-4">Items</th><th class="px-5 py-4">Total</th><th class="px-5 py-4">Payment</th><th class="px-5 py-4">Order status</th><th class="px-5 py-4">Placed</th><th class="px-5 py-4"></th></tr></thead>
        <tbody class="divide-y divide-slate-100">
          <tr v-for="order in results.data" :key="order.id" class="hover:bg-slate-50">
            <td class="px-5 py-4 font-bold">#{{ order.id }}</td>
            <td class="px-5 py-4"><p class="font-semibold text-slate-800">{{ order.user?.name || 'Customer' }}</p><p class="mt-1 text-xs text-slate-500">{{ order.user?.email }}</p></td>
            <td class="px-5 py-4">{{ order.order_items_count }}</td>
            <td class="px-5 py-4 font-bold">{{ currency.format(Number(order.total_amount)) }}</td>
            <td class="px-5 py-4">
              <div v-if="currentPayment(order)" class="space-y-1.5">
                <p class="text-xs font-semibold capitalize text-slate-500">{{ currentPayment(order).payment_method.replaceAll('_', ' ') }}</p>
                <StatusPill :status="paymentStatus(currentPayment(order))" />
                <p v-if="paymentStatus(currentPayment(order)) === 'cancelled'" class="text-xs text-red-700">Cancelled by customer</p>
              </div>
              <span v-else class="text-xs text-slate-400">No payment record</span>
            </td>
            <td class="px-5 py-4"><StatusPill :status="order.status" /></td>
            <td class="px-5 py-4 text-slate-500">{{ new Date(order.created_at).toLocaleDateString() }}</td>
            <td class="px-5 py-4 text-right"><RouterLink :to="{ name: 'admin-order-details', params: { id: order.id } }" class="font-bold text-emerald-700 hover:underline">Details</RouterLink></td>
          </tr>
          <tr v-if="!loading && !results.data.length"><td colspan="8" class="px-5 py-12 text-center text-slate-500">No matching orders found.</td></tr>
          <tr v-if="loading"><td colspan="8" class="px-5 py-12 text-center text-slate-500">Loading orders…</td></tr>
        </tbody>
      </table>
    </div>

    <footer class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-200 px-5 py-4 text-sm">
      <p class="text-slate-500">{{ results.total }} orders · Page {{ results.current_page }} of {{ results.last_page }}</p>
      <div class="flex gap-2">
        <button :disabled="loading || results.current_page <= 1" class="rounded-lg border border-slate-300 px-3 py-2 font-semibold disabled:opacity-40" @click="loadOrders(results.current_page - 1)">Previous</button>
        <button :disabled="loading || results.current_page >= results.last_page" class="rounded-lg border border-slate-300 px-3 py-2 font-semibold disabled:opacity-40" @click="loadOrders(results.current_page + 1)">Next</button>
      </div>
    </footer>
  </section>
</template>
