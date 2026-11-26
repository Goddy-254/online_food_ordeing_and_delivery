<script setup>
import { onMounted, ref } from 'vue'
import { RouterLink } from 'vue-router'
import api from '@/services/api'
import PageHeading from '../components/PageHeading.vue'

const results = ref({ data: [], current_page: 1, last_page: 1, total: 0 })
const search = ref('')
const loading = ref(false)
const errorMessage = ref('')
const currency = new Intl.NumberFormat('en-KE', { style: 'currency', currency: 'KES', maximumFractionDigits: 0 })

async function loadCustomers(page = 1) {
  loading.value = true
  errorMessage.value = ''
  try {
    const { data } = await api.get('/admin/customers', { params: { page, search: search.value || undefined } })
    results.value = data
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Could not load customers.'
  } finally {
    loading.value = false
  }
}

onMounted(() => loadCustomers())
</script>

<template>
  <PageHeading title="Customers" description="See customer accounts, their order activity, and lifetime order value." />
  <div v-if="errorMessage" role="alert" class="mb-5 rounded-xl bg-red-50 p-4 text-sm text-red-700">{{ errorMessage }}</div>
  <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
    <form class="flex gap-3 border-b border-slate-200 p-4" @submit.prevent="loadCustomers(1)">
      <input v-model="search" class="min-w-0 flex-1 rounded-xl border border-slate-300 px-4 py-2.5 text-sm" placeholder="Search name or email">
      <button class="rounded-xl bg-slate-950 px-5 py-2.5 text-sm font-bold text-white hover:bg-emerald-700">Search</button>
    </form>
    <div class="overflow-x-auto">
      <table class="w-full min-w-[650px] text-left text-sm">
        <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500"><tr><th class="px-5 py-4">Customer</th><th class="px-5 py-4">Orders</th><th class="px-5 py-4">Lifetime order value</th><th class="px-5 py-4">Joined</th><th></th></tr></thead>
        <tbody class="divide-y divide-slate-100">
          <tr v-for="customer in results.data" :key="customer.id" class="hover:bg-slate-50">
            <td class="px-5 py-4"><p class="font-bold text-slate-900">{{ customer.name }}</p><p class="mt-1 text-xs text-slate-500">{{ customer.email }}</p></td>
            <td class="px-5 py-4">{{ customer.orders_count }}</td>
            <td class="px-5 py-4 font-bold">{{ currency.format(Number(customer.orders_sum_total_amount || 0)) }}</td>
            <td class="px-5 py-4 text-slate-500">{{ new Date(customer.created_at).toLocaleDateString() }}</td>
            <td class="px-5 py-4 text-right"><RouterLink :to="{ name: 'admin-customer-details', params: { id: customer.id } }" class="font-bold text-emerald-700 hover:underline">View history</RouterLink></td>
          </tr>
          <tr v-if="loading"><td colspan="5" class="p-10 text-center text-slate-500">Loading customers…</td></tr>
          <tr v-else-if="!results.data.length"><td colspan="5" class="p-10 text-center text-slate-500">No customers found.</td></tr>
        </tbody>
      </table>
    </div>
    <footer class="flex items-center justify-between border-t border-slate-200 px-5 py-4 text-sm"><span class="text-slate-500">{{ results.total }} customers · Page {{ results.current_page }} of {{ results.last_page }}</span><div class="flex gap-2"><button :disabled="loading || results.current_page <= 1" class="rounded-lg border px-3 py-2 font-semibold disabled:opacity-40" @click="loadCustomers(results.current_page - 1)">Previous</button><button :disabled="loading || results.current_page >= results.last_page" class="rounded-lg border px-3 py-2 font-semibold disabled:opacity-40" @click="loadCustomers(results.current_page + 1)">Next</button></div></footer>
  </section>
</template>
