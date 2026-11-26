<script setup>
import { onMounted, ref, watch } from 'vue'
import { useRoute, RouterLink } from 'vue-router'
import api from '@/services/api'
import PageHeading from '../components/PageHeading.vue'
import StatusPill from '../components/StatusPill.vue'

const route = useRoute()
const order = ref(null)
const selectedStatus = ref('')
const loading = ref(true)
const saving = ref(false)
const message = ref('')
const errorMessage = ref('')
const statuses = ['pending', 'preparing', 'out_for_delivery', 'delivered', 'cancelled']
const currency = new Intl.NumberFormat('en-KE', { style: 'currency', currency: 'KES', maximumFractionDigits: 2 })

async function loadOrder() {
  loading.value = true
  errorMessage.value = ''
  try {
    const { data } = await api.get(`/admin/orders/${route.params.id}`)
    order.value = data.order
    selectedStatus.value = data.order.status
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Could not load this order.'
  } finally {
    loading.value = false
  }
}

async function saveStatus() {
  saving.value = true
  errorMessage.value = ''
  message.value = ''
  try {
    const { data } = await api.patch(`/admin/orders/${order.value.id}/status`, { status: selectedStatus.value })
    order.value = { ...order.value, ...data.order }
    message.value = data.message
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Could not update order status.'
  } finally {
    saving.value = false
  }
}

onMounted(loadOrder)
watch(() => route.params.id, loadOrder)
</script>

<template>
  <PageHeading title="Order details" description="Review the customer's order and update its fulfillment status.">
    <RouterLink :to="{ name: 'admin-orders' }" class="rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-bold text-slate-700 hover:bg-white">← All orders</RouterLink>
  </PageHeading>
  <div v-if="message" class="mb-5 rounded-xl bg-emerald-50 p-4 text-sm font-semibold text-emerald-800">{{ message }}</div>
  <div v-if="errorMessage" role="alert" class="mb-5 rounded-xl bg-red-50 p-4 text-sm text-red-700">{{ errorMessage }}</div>
  <div v-if="loading" class="rounded-2xl bg-white p-8 text-slate-500">Loading order…</div>

  <div v-else-if="order" class="grid gap-6 xl:grid-cols-[1.3fr_.7fr]">
    <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
      <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 pb-5">
        <div><p class="text-sm text-slate-500">Order reference</p><h2 class="mt-1 text-2xl font-black">#{{ order.id }}</h2></div>
        <StatusPill :status="order.status" />
      </div>
      <div class="divide-y divide-slate-100">
        <div v-for="item in order.order_items" :key="item.id" class="flex items-center justify-between gap-4 py-5">
          <div><p class="font-bold text-slate-900">{{ item.menu_item?.name || 'Menu item' }}</p><p class="mt-1 text-sm text-slate-500">{{ item.quantity }} × {{ currency.format(Number(item.price)) }}</p></div>
          <p class="font-bold">{{ currency.format(Number(item.price) * item.quantity) }}</p>
        </div>
      </div>
      <div class="space-y-3 border-t border-slate-100 pt-5 text-sm">
        <div class="flex justify-between text-slate-600"><span>Items subtotal</span><span>{{ currency.format(order.order_items.reduce((sum, item) => sum + Number(item.price) * item.quantity, 0)) }}</span></div>
        <div class="flex justify-between text-slate-600"><span>Delivery fee</span><span>{{ currency.format(Number(order.delivery_fee || 0)) }}</span></div>
        <div class="flex justify-between border-t border-slate-100 pt-4 text-lg font-black"><span>Total</span><span>{{ currency.format(Number(order.total_amount)) }}</span></div>
      </div>
    </section>

    <aside class="space-y-6">
      <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="font-black text-slate-900">Fulfillment</h2>
        <label class="mt-5 block text-sm font-semibold text-slate-700">Order status
          <select v-model="selectedStatus" class="mt-2 w-full rounded-xl border border-slate-300 px-3 py-3 capitalize">
            <option v-for="item in statuses" :key="item" :value="item">{{ item.replaceAll('_', ' ') }}</option>
          </select>
        </label>
        <button :disabled="saving || selectedStatus === order.status" class="mt-4 w-full rounded-xl bg-slate-950 px-4 py-3 font-bold text-white hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-40" @click="saveStatus">{{ saving ? 'Saving…' : 'Update status' }}</button>
      </section>
      <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="font-black text-slate-900">Customer</h2>
        <p class="mt-4 font-bold">{{ order.user?.name }}</p>
        <p class="mt-1 text-sm text-slate-500">{{ order.user?.email }}</p>
        <p v-if="order.user?.phone_number" class="mt-1 text-sm text-slate-500">{{ order.user.phone_number }}</p>
        <h3 class="mb-1 mt-5 text-xs font-bold uppercase tracking-wide text-slate-400">Delivery address</h3>
        <p class="text-sm leading-6 text-slate-700">{{ order.delivery_address }}</p>
        <p class="mt-4 text-xs text-slate-400">Placed {{ new Date(order.created_at).toLocaleString() }}</p>
      </section>
      <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="font-black text-slate-900">Payments</h2>
        <div v-if="order.payments?.length" class="mt-4 space-y-3">
          <div v-for="payment in order.payments" :key="payment.id" class="flex items-center justify-between gap-3 rounded-xl bg-slate-50 p-3">
            <div><p class="font-semibold capitalize">{{ payment.payment_method.replaceAll('_', ' ') }}</p><p v-if="payment.mpesa_receipt" class="mt-1 text-xs text-slate-500">Receipt {{ payment.mpesa_receipt }}</p></div>
            <div class="text-right"><p class="font-bold">{{ currency.format(Number(payment.amount)) }}</p><StatusPill :status="payment.status" /></div>
          </div>
        </div>
        <p v-else class="mt-3 text-sm text-slate-500">No payment record has been added.</p>
      </section>
    </aside>
  </div>
</template>
