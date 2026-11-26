
<script setup>
import { computed, onMounted, ref } from 'vue'
import { useCartStore } from '@/stores/cart'
import api from '@/services/api'

const cartStore = useCartStore()

const deliveryAddress = ref('')
const loading = ref(false)
const errorMessage = ref('')
const successMessage = ref('')
const storeInfo = ref({ delivery_fee: 0, operating_hours: '', store_open: true })
const orderTotal = computed(() => cartStore.cartTotal + Number(storeInfo.value.delivery_fee || 0))

onMounted(async () => {
  try {
    const { data } = await api.get('/store-info')
    storeInfo.value = data
  } catch {
    // Checkout still works with a zero delivery fee if store info is unavailable.
  }
})

const placeOrder = async () => {
  if (!deliveryAddress.value.trim()) {
    successMessage.value = ''
    errorMessage.value = 'Please enter your delivery address.'
    return
  }

  if (cartStore.cartItems.length === 0) {
    successMessage.value = ''
    errorMessage.value = 'Your cart is empty.'
    return
  }

  if (!storeInfo.value.store_open) {
    successMessage.value = ''
    errorMessage.value = 'The store is currently closed and cannot accept orders.'
    return
  }

  try {
    loading.value = true
    errorMessage.value = ''
    successMessage.value = ''

    const response = await api.post('/orders', {
      delivery_address: deliveryAddress.value,
      items: cartStore.cartItems.map(item => ({
        menu_item_id: item.id,
        quantity: item.quantity
      }))
    })

    successMessage.value =
      response.data.message || 'Your order was placed successfully!'

    cartStore.clearCart()
    errorMessage.value = ''

  } catch (error) {
    console.error('Order error:', error)
    successMessage.value = ''

    errorMessage.value =
      error.response?.data?.message ||
      'Failed to place your order. Please try again.'

  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="max-w-5xl mx-auto px-4 py-10">

    <h1 class="text-3xl font-bold text-gray-800 mb-8">
      Checkout
    </h1>

    <!-- Success Message -->
    <div
      v-if="successMessage"
      class="bg-green-100 text-green-700 p-4 rounded-lg mb-6"
    >
      {{ successMessage }}
    </div>

    <!-- Error Message -->
    <div
      v-if="errorMessage"
      class="bg-red-100 text-red-700 p-4 rounded-lg mb-6"
    >
      {{ errorMessage }}
    </div>

    <div
      v-if="!successMessage"
      class="grid grid-cols-1 md:grid-cols-2 gap-8"
    >

      <!-- Delivery Information -->
      <form
        @submit.prevent="placeOrder"
        class="bg-white shadow-sm border rounded-xl p-6"
      >
        <h2 class="text-xl font-bold mb-5">
          Delivery Information
        </h2>

        <label class="block text-gray-700 font-medium mb-2">
          Delivery Address
        </label>

        <textarea
          v-model="deliveryAddress"
          rows="5"
          placeholder="Enter your full delivery address..."
          class="w-full border border-gray-300 rounded-lg p-3 focus:outline-none focus:ring-2 focus:ring-green-500"
          required
        ></textarea>

        <p class="text-gray-500 text-sm mt-2">
          Include your estate, building, street or any useful delivery directions.
        </p>

        <p v-if="storeInfo.operating_hours" class="mt-3 text-sm text-gray-500">
          Store hours: {{ storeInfo.operating_hours }}
        </p>
        <p v-if="!storeInfo.store_open" class="mt-3 rounded-lg bg-amber-50 p-3 text-sm font-semibold text-amber-800">
          The store is currently closed and cannot accept orders.
        </p>

        <button
          type="submit"
          :disabled="loading || cartStore.cartItems.length === 0 || !storeInfo.store_open"
          class="w-full mt-6 bg-green-600 hover:bg-green-700 disabled:bg-gray-400 text-white py-3 rounded-lg font-semibold"
        >
          {{ loading ? 'Placing Order...' : 'Place Order' }}
        </button>
      </form>

      <!-- Order Summary -->
      <div class="bg-gray-50 rounded-xl p-6 h-fit">

        <h2 class="text-xl font-bold mb-5">
          Your Order
        </h2>

        <div
          v-for="item in cartStore.cartItems"
          :key="item.id"
          class="flex justify-between gap-4 border-b py-3"
        >
          <div>
            <p class="font-medium text-gray-800">
              {{ item.name }}
            </p>

            <p class="text-sm text-gray-500">
              Quantity: {{ item.quantity }}
            </p>
          </div>

          <p class="font-semibold">
            KSh
            {{ (Number(item.price) * item.quantity).toFixed(2) }}
          </p>
        </div>

        <div class="flex justify-between mt-6 text-lg font-bold">
          <span>Items subtotal</span>

          <span class="text-green-600">
            KSh {{ cartStore.cartTotal.toFixed(2) }}
          </span>
        </div>

        <div class="mt-3 flex justify-between text-sm text-gray-600">
          <span>Delivery fee</span>
          <span>KSh {{ Number(storeInfo.delivery_fee || 0).toFixed(2) }}</span>
        </div>

        <div class="mt-3 flex justify-between border-t pt-3 text-lg font-bold">
          <span>Total</span>
          <span class="text-green-600">KSh {{ orderTotal.toFixed(2) }}</span>
        </div>

      </div>

    </div>

    <RouterLink
      v-if="successMessage"
      to="/menu"
      class="inline-block mt-6 text-green-600 font-semibold hover:underline"
    >
      Continue Shopping
    </RouterLink>

  </div>
</template>
