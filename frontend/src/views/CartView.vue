<script setup>
import { useCartStore } from "@/stores/cart";

const cartStore = useCartStore();
</script>

<template>
  <div class="max-w-6xl mx-auto px-4 py-10">
    <h1 class="text-3xl font-bold text-gray-800 mb-8">Your Shopping Cart</h1>

    <!-- Empty Cart -->
    <div
      v-if="cartStore.cartItems.length === 0"
      class="text-center py-16 bg-gray-50 rounded-xl"
    >
      <h2 class="text-xl font-semibold text-gray-700 mb-3">
        Your cart is empty
      </h2>

      <p class="text-gray-500 mb-6">You haven't added any food yet.</p>

      <RouterLink
        to="/menu"
        class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg"
      >
        Browse Menu
      </RouterLink>
    </div>

    <!-- Cart Items -->
    <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      <!-- Items List -->
      <div class="lg:col-span-2 space-y-4">
        <div
          v-for="item in cartStore.cartItems"
          :key="item.id"
          class="bg-white shadow-sm border rounded-xl p-4 flex flex-col sm:flex-row gap-4 items-center"
        >
          <!-- Food Image -->
          <img
            :src="item.image"
            :alt="item.name"
            class="w-24 h-24 object-cover rounded-lg"
          />

          <!-- Food Details -->
          <div class="flex-1 text-center sm:text-left">
            <h3 class="font-bold text-lg text-gray-800">
              {{ item.name }}
            </h3>

            <p class="text-green-600 font-semibold">
              KSh {{ Number(item.price).toFixed(2) }}
            </p>

            <p class="text-gray-500 text-sm mt-1">
              Subtotal: KSh
              {{ (Number(item.price) * item.quantity).toFixed(2) }}
            </p>
          </div>

          <!-- Quantity Controls -->
          <div class="flex items-center gap-3">
            <button
              @click="cartStore.decreaseQuantity(item.id)"
              class="w-8 h-8 bg-gray-200 rounded-full hover:bg-gray-300"
            >
              -
            </button>

            <span class="font-bold">
              {{ item.quantity }}
            </span>

            <button
              @click="cartStore.increaseQuantity(item.id)"
              class="w-8 h-8 bg-green-600 text-white rounded-full hover:bg-green-700"
            >
              +
            </button>
          </div>

          <!-- Remove Button -->
          <button
            @click="cartStore.removeFromCart(item.id)"
            class="text-red-500 hover:text-red-700 text-sm font-semibold"
          >
            Remove
          </button>
        </div>
      </div>

      <!-- Order Summary -->
      <div class="bg-gray-50 rounded-xl p-6 h-fit">
        <h2 class="text-xl font-bold mb-6">Order Summary</h2>

        <div class="flex justify-between mb-4">
          <span class="text-gray-600">Total Items</span>
          <span class="font-semibold">
            {{ cartStore.cartCount }}
          </span>
        </div>

        <div class="flex justify-between mb-6">
          <span class="text-gray-600">Subtotal</span>
          <span class="font-semibold">
            KSh {{ cartStore.cartTotal.toFixed(2) }}
          </span>
        </div>

        <div class="border-t pt-4 flex justify-between text-lg font-bold">
          <span>Total</span>
          <span class="text-green-600">
            KSh {{ cartStore.cartTotal.toFixed(2) }}
          </span>
        </div>

        <button
          v-if="cartStore.cartItems.length === 0"
          disabled
          class="w-full mt-6 bg-green-600 opacity-50 cursor-not-allowed text-white py-3 rounded-lg font-semibold"
        >
          Proceed to Checkout
        </button>

        <RouterLink
          v-else
          to="/checkout"
          class="block w-full mt-6 bg-green-600 hover:bg-green-700 text-white py-3 rounded-lg font-semibold text-center"
        >
          Proceed to Checkout
        </RouterLink>

        <RouterLink
          to="/menu"
          class="block text-center mt-4 text-green-600 hover:underline"
        >
          Continue Shopping
        </RouterLink>
      </div>
    </div>
  </div>
</template>
