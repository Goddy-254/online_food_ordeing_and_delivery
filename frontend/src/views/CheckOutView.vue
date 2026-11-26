<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue'
import { onBeforeRouteLeave } from 'vue-router'
import { useCartStore } from '@/stores/cart'
import api from '@/services/api'

const cartStore = useCartStore()
const deliveryAddress = ref('')
const mpesaPhone = ref('')
const loading = ref(false)
const errorMessage = ref('')
const successMessage = ref('')
const storeInfo = ref({ delivery_fee: 0, operating_hours: '', store_open: true })
const orderId = ref(null)
const paymentStatus = ref('')
const mpesaReceipt = ref('')
const paymentAmount = ref(null)
const paidAt = ref('')
const pendingMessage = ref('')
const pollTimer = ref(null)
const checkoutOrderStorageKey = 'foodexpress_active_checkout_order'
const orderTotal = computed(() => cartStore.cartTotal + Number(storeInfo.value.delivery_fee || 0))
const displayedOrderTotal = computed(() => paymentAmount.value ?? orderTotal.value)

onMounted(async () => {
  const storedOrderId = sessionStorage.getItem(checkoutOrderStorageKey)
  if (storedOrderId && /^\d+$/.test(storedOrderId)) {
    // Restore the order after a refresh so the customer can still see the final payment result.
    orderId.value = storedOrderId

    try {
      const status = await refreshPaymentStatus()
      if (status === 'paid') cartStore.clearCart()
      if (['starting', 'pending'].includes(status)) {
        pendingMessage.value = 'Your M-Pesa request is still being confirmed. Keep this order; do not start another payment.'
        pollPayment()
      }
    } catch {
      paymentStatus.value = 'checking'
      errorMessage.value = 'We saved your order, but could not check its payment status. Check again before retrying.'
    }
  }

  try {
    const { data } = await api.get('/store-info')
    storeInfo.value = data
  } catch {
    // The backend remains authoritative if store information cannot be loaded.
  }
})

onUnmounted(() => {
  if (pollTimer.value) clearTimeout(pollTimer.value)
})

onBeforeRouteLeave(() => {
  // Keep unfinished attempts resumable, but don't reopen an old paid checkout later.
  if (paymentStatus.value === 'paid') sessionStorage.removeItem(checkoutOrderStorageKey)
})

async function refreshPaymentStatus() {
  if (!orderId.value) return ''

  const { data } = await api.get(`/orders/${orderId.value}/payment-status`)
  const payment = data.payment

  if (!payment) return ''

  paymentStatus.value = payment.status
  mpesaReceipt.value = payment.mpesa_receipt || ''
  paymentAmount.value = payment.amount === null || payment.amount === undefined ? null : Number(payment.amount)
  paidAt.value = payment.paid_at || ''

  if (payment.status === 'paid') {
    successMessage.value = 'Payment successful. Your order is confirmed.'
    errorMessage.value = ''
    pendingMessage.value = ''
  } else if (payment.status === 'failed') {
    successMessage.value = ''
    errorMessage.value = payment.result_description || 'The M-Pesa payment was not completed. You can try again.'
    pendingMessage.value = ''
  } else if (payment.status === 'review') {
    successMessage.value = ''
    errorMessage.value = 'We could not confirm the payment yet. Please contact the store before trying again.'
    pendingMessage.value = ''
  } else if (['starting', 'pending'].includes(payment.status)) {
    errorMessage.value = ''
  }

  return payment.status
}

function pollPayment(attempt = 0) {
  if (pollTimer.value) clearTimeout(pollTimer.value)

  // Check frequently at first, then slow down while waiting for a delayed callback.
  const delay = attempt < 24 ? 5000 : 15000

  pollTimer.value = setTimeout(async () => {
    try {
      const status = await refreshPaymentStatus()

      // Poll our own order status, not Safaricom, for up to about 18 minutes.
      if (['starting', 'pending'].includes(status) && attempt < 90) {
        pollPayment(attempt + 1)
      } else if (['starting', 'pending'].includes(status)) {
        pendingMessage.value = 'We are still waiting for M-Pesa confirmation. Your order is saved; check again before trying another payment.'
      }
    } catch {
      if (attempt < 90) pollPayment(attempt + 1)
    }
  }, delay)
}

async function checkPaymentNow() {
  loading.value = true
  try {
    const status = await refreshPaymentStatus()
    if (['starting', 'pending'].includes(status)) {
      pendingMessage.value = 'Your M-Pesa request is still being confirmed. Do not start another payment.'
      pollPayment()
    }
  } catch {
    errorMessage.value = 'Could not check payment status. Please try again shortly.'
  } finally {
    loading.value = false
  }
}

async function startPayment() {
  errorMessage.value = ''
  successMessage.value = ''
  paymentStatus.value = 'starting'
  pendingMessage.value = 'Sending the payment prompt to your phone…'

  try {
    const { data } = await api.post(`/orders/${orderId.value}/mpesa`, {
      phone: mpesaPhone.value,
    })

    paymentStatus.value = data.payment.status
    paymentAmount.value = Number(data.payment.amount)
    successMessage.value = ''
    pendingMessage.value = data.message || `Check ${mpesaPhone.value} and approve the M-Pesa prompt.`
    cartStore.clearCart()
    pollPayment()
  } catch (error) {
    const knownPayment = error.response?.data?.payment
    if (knownPayment?.status) paymentStatus.value = knownPayment.status

    if (['starting', 'pending'].includes(knownPayment?.status)) {
      errorMessage.value = ''
      pendingMessage.value = 'A payment prompt is already in progress. We will keep checking for its result.'
      pollPayment()
      return
    }

    // A response can be lost after Safaricom accepted a push; inspect the saved attempt first.
    if (orderId.value && !knownPayment) {
      try {
        const savedStatus = await refreshPaymentStatus()
        if (['starting', 'pending'].includes(savedStatus)) {
          pendingMessage.value = 'Check your phone for the M-Pesa prompt. We will keep checking for confirmation.'
          pollPayment()
          return
        }
      } catch {
        // Use the request error below if the status endpoint is also unavailable.
      }
    }

    if (paymentStatus.value !== 'paid') {
      errorMessage.value = error.response?.data?.message || 'Could not start M-Pesa payment.'
    }
  }
}

async function placeOrder() {
  if (!deliveryAddress.value.trim()) {
    errorMessage.value = 'Please enter your delivery address.'
    return
  }

  if (!mpesaPhone.value.trim()) {
    errorMessage.value = 'Enter the Kenyan phone number that should receive the M-Pesa prompt.'
    return
  }

  if (!orderId.value && cartStore.cartItems.length === 0) {
    errorMessage.value = 'Your cart is empty.'
    return
  }

  if (!storeInfo.value.store_open) {
    errorMessage.value = 'The store is currently closed and cannot accept orders.'
    return
  }

  loading.value = true
  errorMessage.value = ''

  try {
    // Save the order ID before starting payment so a retry never creates another order.
    if (!orderId.value) {
      const { data } = await api.post('/orders', {
        delivery_address: deliveryAddress.value,
        items: cartStore.cartItems.map((item) => ({
          menu_item_id: item.id,
          quantity: item.quantity,
        })),
      })
      orderId.value = data.order.id
      sessionStorage.setItem(checkoutOrderStorageKey, String(orderId.value))
    }

    await startPayment()
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Could not create your order.'
  } finally {
    loading.value = false
  }
}

function leaveCheckout() {
  sessionStorage.removeItem(checkoutOrderStorageKey)
}
</script>

<template>
  <main class="min-h-screen bg-gray-50">
    <div class="mx-auto max-w-5xl px-4 py-10">
      <h1 class="mb-2 text-3xl font-black text-gray-900">Checkout</h1>
      <p class="mb-8 text-gray-500">Enter delivery details and approve the M-Pesa prompt on your phone.</p>

      <div v-if="errorMessage" role="alert" class="mb-6 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm font-medium leading-6 text-red-800">{{ errorMessage }}</div>
      <section v-if="paymentStatus === 'checking'" class="mb-6 flex flex-col gap-4 rounded-2xl border border-sky-200 bg-sky-50 p-5 text-sky-950 sm:flex-row sm:items-center sm:justify-between">
        <div><p class="font-black">Checking your saved order</p><p class="mt-1 text-sm leading-6">Order #{{ orderId }} is saved. We need to reconnect to confirm its payment status.</p></div>
        <button class="shrink-0 rounded-xl border border-sky-300 bg-white px-4 py-2.5 text-sm font-bold text-sky-900 transition hover:bg-sky-100" :disabled="loading" @click="checkPaymentNow">{{ loading ? 'Checking…' : 'Check payment status' }}</button>
      </section>
      <section v-if="['starting', 'pending'].includes(paymentStatus)" class="mb-6 overflow-hidden rounded-2xl border border-amber-200 bg-white shadow-sm">
        <div class="flex items-start gap-4 bg-amber-50 p-5 sm:p-6">
          <span class="mt-0.5 grid h-11 w-11 shrink-0 place-items-center rounded-full bg-amber-100 text-xl text-amber-800" aria-hidden="true"><span class="animate-pulse">◷</span></span>
          <div class="min-w-0 flex-1">
            <p class="font-black text-amber-950">{{ paymentStatus === 'starting' ? 'Sending your M-Pesa prompt' : 'Waiting for M-Pesa confirmation' }}</p>
            <p class="mt-1 text-sm leading-6 text-amber-900">{{ pendingMessage || `Check ${mpesaPhone} and approve the prompt on your phone.` }}</p>
            <p class="mt-2 text-xs font-semibold text-amber-800">Order #{{ orderId }} · Do not start another payment for this order.</p>
          </div>
        </div>
        <div class="flex flex-wrap items-center justify-between gap-3 px-5 py-4 sm:px-6">
          <p class="text-sm text-slate-500">The confirmation will appear here automatically.</p>
          <button class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-bold text-slate-700 transition hover:border-emerald-300 hover:bg-emerald-50" :disabled="loading" @click="checkPaymentNow">{{ loading ? 'Checking…' : 'Check again' }}</button>
        </div>
      </section>
      <div v-if="paymentStatus === 'review'" class="mb-6 rounded-xl border border-amber-200 bg-amber-50 p-4 text-amber-900">
        <p class="font-bold">Payment needs a check</p>
        <p class="mt-1 text-sm">Order #{{ orderId }} has not been marked paid. Contact the store before retrying.</p>
      </div>
      <section v-if="paymentStatus === 'paid'" class="mb-7 overflow-hidden rounded-3xl border border-emerald-200 bg-white shadow-lg shadow-emerald-950/5">
        <div class="bg-gradient-to-r from-emerald-800 to-emerald-600 px-6 py-7 text-white sm:px-8">
          <div class="flex items-center gap-4">
            <span class="grid h-14 w-14 shrink-0 place-items-center rounded-full bg-white text-3xl font-black text-emerald-700 shadow-lg" aria-hidden="true">✓</span>
            <div><p class="text-xs font-extrabold uppercase tracking-[.18em] text-emerald-100">Payment confirmed</p><h2 class="mt-1 text-2xl font-black sm:text-3xl">Payment successful</h2><p class="mt-1 text-sm text-emerald-50">{{ successMessage || 'Your order is confirmed.' }}</p></div>
          </div>
        </div>
        <div class="grid gap-4 p-6 sm:grid-cols-2 sm:p-8">
          <div class="rounded-2xl bg-slate-50 p-4"><p class="text-xs font-bold uppercase tracking-wide text-slate-500">Order number</p><p class="mt-1 text-lg font-black text-slate-900">#{{ orderId }}</p></div>
          <div class="rounded-2xl bg-emerald-50 p-4"><p class="text-xs font-bold uppercase tracking-wide text-emerald-700">Amount paid</p><p class="mt-1 text-lg font-black text-emerald-900">KSh {{ Number(displayedOrderTotal).toFixed(2) }}</p></div>
          <div v-if="mpesaReceipt" class="rounded-2xl bg-slate-50 p-4"><p class="text-xs font-bold uppercase tracking-wide text-slate-500">M-Pesa receipt</p><p class="mt-1 break-all font-black text-slate-900">{{ mpesaReceipt }}</p></div>
          <div v-if="paidAt" class="rounded-2xl bg-slate-50 p-4"><p class="text-xs font-bold uppercase tracking-wide text-slate-500">Confirmed at</p><p class="mt-1 font-semibold text-slate-900">{{ new Date(paidAt).toLocaleString() }}</p></div>
          <p class="text-sm leading-6 text-slate-600 sm:col-span-2">We’ve confirmed your payment and saved your order. You can safely continue shopping.</p>
        </div>
      </section>

      <div v-if="paymentStatus !== 'paid'" class="grid grid-cols-1 gap-8 md:grid-cols-2">
        <form class="rounded-2xl border bg-white p-6 shadow-sm" @submit.prevent="placeOrder">
          <h2 class="mb-5 text-xl font-black">Delivery and payment</h2>

          <label class="mb-5 block font-semibold text-gray-700">
            Delivery address
            <textarea v-model="deliveryAddress" :disabled="Boolean(orderId) && paymentStatus !== 'failed'" rows="4" maxlength="255" required placeholder="Estate, building, street and delivery directions" class="mt-2 w-full rounded-xl border border-gray-300 p-3 outline-none focus:border-green-600 focus:ring-2 focus:ring-green-100 disabled:bg-gray-100" />
          </label>

          <label class="block font-semibold text-gray-700">
            M-Pesa phone number
            <input v-model.trim="mpesaPhone" :disabled="Boolean(orderId) && paymentStatus !== 'failed'" type="tel" inputmode="tel" autocomplete="tel" required placeholder="0712345678 or 254712345678" class="mt-2 w-full rounded-xl border border-gray-300 p-3 outline-none focus:border-green-600 focus:ring-2 focus:ring-green-100 disabled:bg-gray-100" />
          </label>
          <p class="mt-2 text-xs leading-5 text-gray-500">The payment prompt will be sent to this number. Never share your M-Pesa PIN.</p>

          <p v-if="storeInfo.operating_hours" class="mt-4 text-sm text-gray-500">Store hours: {{ storeInfo.operating_hours }}</p>
          <p v-if="!storeInfo.store_open" class="mt-4 rounded-lg bg-amber-50 p-3 text-sm font-semibold text-amber-800">The store is currently closed and cannot accept orders.</p>

          <button
            type="submit"
            :disabled="loading || ['checking', 'starting', 'pending', 'review'].includes(paymentStatus) || !storeInfo.store_open || (!orderId && cartStore.cartItems.length === 0)"
            class="mt-6 w-full rounded-xl bg-green-700 py-3 font-bold text-white transition hover:bg-green-800 disabled:cursor-not-allowed disabled:bg-gray-400"
          >
            {{ loading ? 'Starting payment…' : paymentStatus === 'failed' ? 'Try M-Pesa again' : orderId ? 'Retry payment' : 'Place order and pay' }}
          </button>
          <p v-if="orderId && paymentStatus === 'failed'" class="mt-3 text-xs text-gray-500">Retrying uses order #{{ orderId }}; it will not create another order.</p>
        </form>

        <section class="h-fit rounded-2xl bg-white p-6 shadow-sm">
          <h2 class="mb-5 text-xl font-black">Your order</h2>
          <div v-if="cartStore.cartItems.length" class="divide-y">
            <div v-for="item in cartStore.cartItems" :key="item.id" class="flex justify-between gap-4 py-3">
              <div><p class="font-semibold text-gray-800">{{ item.name }}</p><p class="text-sm text-gray-500">Quantity: {{ item.quantity }}</p></div>
              <p class="font-bold">KSh {{ (Number(item.price) * item.quantity).toFixed(2) }}</p>
            </div>
          </div>
          <p v-else-if="orderId" class="rounded-xl bg-blue-50 p-4 text-sm leading-6 text-blue-800">Your items are saved in order #{{ orderId }}. The amount below is the total for this saved order.</p>
          <p v-else class="text-sm text-gray-500">Your cart is empty.</p>

          <template v-if="cartStore.cartItems.length">
            <div class="mt-5 flex justify-between text-sm text-gray-600"><span>Items subtotal</span><span>KSh {{ cartStore.cartTotal.toFixed(2) }}</span></div>
            <div class="mt-2 flex justify-between text-sm text-gray-600"><span>Delivery fee</span><span>KSh {{ Number(storeInfo.delivery_fee || 0).toFixed(2) }}</span></div>
          </template>
          <div class="mt-4 flex justify-between border-t pt-4 text-lg font-black"><span>{{ orderId ? 'Order total' : 'Total' }}</span><span class="text-green-700">KSh {{ Number(displayedOrderTotal).toFixed(2) }}</span></div>
        </section>
      </div>

      <RouterLink v-if="paymentStatus === 'paid'" to="/menu" class="inline-flex rounded-xl bg-green-700 px-5 py-3 font-bold text-white hover:bg-green-800" @click="leaveCheckout">Continue shopping</RouterLink>
    </div>
  </main>
</template>
