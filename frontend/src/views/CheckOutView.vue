<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue'
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
const pollTimer = ref(null)
const orderTotal = computed(() => cartStore.cartTotal + Number(storeInfo.value.delivery_fee || 0))

onMounted(async () => {
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

async function refreshPaymentStatus() {
  if (!orderId.value) return ''

  const { data } = await api.get(`/orders/${orderId.value}/payment-status`)
  const payment = data.payment

  if (!payment) return ''

  paymentStatus.value = payment.status
  mpesaReceipt.value = payment.mpesa_receipt || ''

  if (payment.status === 'paid') {
    successMessage.value = 'Payment received. Your order is confirmed.'
    errorMessage.value = ''
  } else if (payment.status === 'failed') {
    successMessage.value = ''
    errorMessage.value = payment.result_description || 'The M-Pesa payment was not completed. You can try again.'
  } else if (payment.status === 'review') {
    successMessage.value = ''
    errorMessage.value = 'We could not confirm the payment yet. Please contact the store before trying again.'
  }

  return payment.status
}

function pollPayment(attempt = 0) {
  if (pollTimer.value) clearTimeout(pollTimer.value)

  pollTimer.value = setTimeout(async () => {
    try {
      const status = await refreshPaymentStatus()

      // Poll our own order status, not Safaricom, and stop after about two minutes.
      if (['starting', 'pending'].includes(status) && attempt < 23) {
        pollPayment(attempt + 1)
      } else if (['starting', 'pending'].includes(status)) {
        errorMessage.value = 'Payment is still pending. Keep this order and check again shortly.'
      }
    } catch {
      if (attempt < 23) pollPayment(attempt + 1)
    }
  }, 5000)
}

async function checkPaymentNow() {
  loading.value = true
  try {
    const status = await refreshPaymentStatus()
    if (['starting', 'pending'].includes(status)) pollPayment()
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

  try {
    const { data } = await api.post(`/orders/${orderId.value}/mpesa`, {
      phone: mpesaPhone.value,
    })

    paymentStatus.value = data.payment.status
    successMessage.value = data.message || 'Check your phone and enter your M-Pesa PIN.'
    cartStore.clearCart()
    pollPayment()
  } catch (error) {
    const knownPayment = error.response?.data?.payment
    if (knownPayment?.status) paymentStatus.value = knownPayment.status

    if (['starting', 'pending'].includes(knownPayment?.status)) {
      errorMessage.value = 'A payment request is already in progress. We will wait for its confirmation.'
      pollPayment()
      return
    }

    // A response can be lost after Safaricom accepted a push; inspect the saved attempt first.
    if (orderId.value && !knownPayment) {
      try {
        const savedStatus = await refreshPaymentStatus()
    if (['starting', 'pending'].includes(savedStatus)) {
          errorMessage.value = 'Check your phone for the M-Pesa prompt. We will wait for confirmation.'
          pollPayment()
          return
        }
      } catch {
        // Use the request error below if the status endpoint is also unavailable.
      }
    }

    errorMessage.value = error.response?.data?.message || 'Could not start M-Pesa payment.'
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
    }

    await startPayment()
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Could not create your order.'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <main class="min-h-screen bg-gray-50">
    <div class="mx-auto max-w-5xl px-4 py-10">
      <h1 class="mb-2 text-3xl font-black text-gray-900">Checkout</h1>
      <p class="mb-8 text-gray-500">Enter delivery details and approve the M-Pesa prompt on your phone.</p>

      <div v-if="errorMessage" role="alert" class="mb-6 rounded-xl bg-red-100 p-4 text-red-800">{{ errorMessage }}</div>
      <div v-if="['starting', 'pending'].includes(paymentStatus)" class="mb-6 rounded-xl border border-blue-200 bg-blue-50 p-4 text-blue-900">
        <p class="font-bold">{{ paymentStatus === 'starting' ? 'Starting your M-Pesa request' : 'Waiting for M-Pesa confirmation' }}</p>
        <p class="mt-1 text-sm">{{ successMessage || `Check ${mpesaPhone} and enter your PIN. Keep this page open while we check for confirmation.` }}</p>
        <button class="mt-3 text-sm font-bold underline" :disabled="loading" @click="checkPaymentNow">{{ loading ? 'Checking…' : 'Check payment status now' }}</button>
      </div>
      <div v-if="paymentStatus === 'review'" class="mb-6 rounded-xl border border-amber-200 bg-amber-50 p-4 text-amber-900">
        <p class="font-bold">Payment needs a check</p>
        <p class="mt-1 text-sm">Order #{{ orderId }} has not been marked paid. Contact the store before retrying.</p>
      </div>
      <div v-if="paymentStatus === 'paid'" class="mb-6 rounded-xl bg-green-100 p-4 text-green-800">
        <p class="font-bold">{{ successMessage }}</p>
        <p class="mt-1 text-sm">Order #{{ orderId }}<span v-if="mpesaReceipt"> · M-Pesa receipt {{ mpesaReceipt }}</span></p>
      </div>

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
            :disabled="loading || paymentStatus === 'starting' || paymentStatus === 'pending' || paymentStatus === 'review' || !storeInfo.store_open || (!orderId && cartStore.cartItems.length === 0)"
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
          <p v-else-if="orderId" class="rounded-xl bg-blue-50 p-4 text-sm text-blue-800">Your items are saved in order #{{ orderId }} while payment is being processed.</p>
          <p v-else class="text-sm text-gray-500">Your cart is empty.</p>

          <div class="mt-5 flex justify-between text-sm text-gray-600"><span>Items subtotal</span><span>KSh {{ cartStore.cartTotal.toFixed(2) }}</span></div>
          <div class="mt-2 flex justify-between text-sm text-gray-600"><span>Delivery fee</span><span>KSh {{ Number(storeInfo.delivery_fee || 0).toFixed(2) }}</span></div>
          <div class="mt-4 flex justify-between border-t pt-4 text-lg font-black"><span>Total</span><span class="text-green-700">KSh {{ orderTotal.toFixed(2) }}</span></div>
        </section>
      </div>

      <RouterLink v-if="paymentStatus === 'paid'" to="/menu" class="mt-6 inline-flex rounded-xl bg-green-700 px-5 py-3 font-bold text-white hover:bg-green-800">Continue shopping</RouterLink>
    </div>
  </main>
</template>
