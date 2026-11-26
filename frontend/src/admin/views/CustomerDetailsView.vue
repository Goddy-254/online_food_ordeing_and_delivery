<script setup>
import { onMounted, ref, watch } from 'vue'
import { useRoute, RouterLink } from 'vue-router'
import api from '@/services/api'
import PageHeading from '../components/PageHeading.vue'
import StatusPill from '../components/StatusPill.vue'

const route = useRoute()
const customer = ref(null)
const orders = ref({ data: [], current_page: 1, last_page: 1, total: 0 })
const loading = ref(true)
const errorMessage = ref('')
const currency = new Intl.NumberFormat('en-KE', { style: 'currency', currency: 'KES', maximumFractionDigits: 0 })

async function loadCustomer(page = 1) {
  loading.value = true
  errorMessage.value = ''
  try {
    const { data } = await api.get(`/admin/customers/${route.params.id}`, { params: { page } })
    customer.value = data.customer
    orders.value = data.orders
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Could not load customer details.'
  } finally {
    loading.value = false
  }
}

onMounted(() => loadCustomer())
watch(() => route.params.id, () => loadCustomer())
</script>

<template>
  <PageHeading title="Customer history" description="Customer details and past orders.">
    <RouterLink :to="{ name: 'admin-customers' }" class="rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-bold">← All customers</RouterLink>
  </PageHeading>
  <div v-if="errorMessage" role="alert" class="mb-5 rounded-xl bg-red-50 p-4 text-sm text-red-700">{{ errorMessage }}</div>
  <div v-if="loading" class="rounded-2xl bg-white p-8 text-slate-500">Loading customer…</div>
  <template v-else-if="customer">
    <section class="mb-6 grid gap-4 rounded-2xl border border-slate-200 bg-white p-6 sm:grid-cols-3">
      <div><p class="text-xs font-bold uppercase tracking-wide text-slate-400">Customer</p><p class="mt-2 text-lg font-black">{{ customer.name }}</p><p class="mt-1 text-sm text-slate-500">{{ customer.email }}</p></div>
      <div><p class="text-xs font-bold uppercase tracking-wide text-slate-400">Orders placed</p><p class="mt-2 text-2xl font-black">{{ customer.orders_count }}</p></div>
      <div><p class="text-xs font-bold uppercase tracking-wide text-slate-400">Lifetime order value</p><p class="mt-2 text-2xl font-black">{{ currency.format(Number(customer.orders_sum_total_amount || 0)) }}</p></div>
    </section>
    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
      <div class="border-b border-slate-200 px-5 py-4"><h2 class="font-black">Order history</h2></div>
      <div v-if="!orders.data.length" class="p-10 text-center text-slate-500">This customer has not placed an order yet.</div>
      <div v-else class="divide-y divide-slate-100">
        <RouterLink v-for="order in orders.data" :key="order.id" :to="{ name: 'admin-order-details', params: { id: order.id } }" class="flex flex-wrap items-center justify-between gap-3 px-5 py-4 hover:bg-slate-50">
          <div><p class="font-bold">Order #{{ order.id }}</p><p class="mt-1 text-xs text-slate-500">{{ new Date(order.created_at).toLocaleString() }}</p></div>
          <div class="flex items-center gap-3"><StatusPill :status="order.status" /><span class="font-black">{{ currency.format(Number(order.total_amount)) }}</span></div>
        </RouterLink>
      </div>
      <footer v-if="orders.last_page > 1" class="flex justify-between border-t border-slate-200 px-5 py-4 text-sm"><button :disabled="orders.current_page <= 1" class="font-bold disabled:opacity-40" @click="loadCustomer(orders.current_page - 1)">Previous</button><span>Page {{ orders.current_page }} of {{ orders.last_page }}</span><button :disabled="orders.current_page >= orders.last_page" class="font-bold disabled:opacity-40" @click="loadCustomer(orders.current_page + 1)">Next</button></footer>
    </section>
  </template>
</template>
