<script setup>
import { useRouter } from 'vue-router'
import { useCartStore } from '@/stores/cart'
import { useAuthStore } from '@/stores/auth'

const cartStore = useCartStore()
const authStore = useAuthStore()
const router = useRouter()

const handleLogout = async () => {
  await authStore.logout()
  router.push('/login')
}
</script>

<template>
  <nav class="bg-white shadow-md px-6 py-4">
    <div class="max-w-7xl mx-auto flex justify-between items-center">

      <!-- Application Logo -->
      <RouterLink
        to="/"
        class="text-2xl font-bold text-green-600"
      >
        FoodExpress
      </RouterLink>

      <!-- Navigation Links -->
      <div class="flex items-center gap-6">

        <RouterLink
          to="/"
          class="text-gray-700 hover:text-green-600"
        >
          Home
        </RouterLink>

        <RouterLink
          to="/menu"
          class="text-gray-700 hover:text-green-600"
        >
          Menu
        </RouterLink>

        <RouterLink
          to="/cart"
          class="relative text-gray-700 hover:text-green-600 font-medium"
        >
          Cart
          <span
            class="ml-1 bg-green-600 text-white text-xs rounded-full px-2 py-1"
          >
            {{ cartStore.cartCount }}
          </span>
        </RouterLink>

        <!-- Authenticated State -->
        <template v-if="authStore.isAuthenticated()">
          <span class="text-gray-700 font-medium">
            Hi, {{ authStore.user?.name || 'Customer' }}
          </span>

          <button
            @click="handleLogout"
            class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg transition cursor-pointer"
          >
            Logout
          </button>
        </template>

        <!-- Guest State -->
        <template v-else>
          <RouterLink
            to="/login"
            class="text-green-600 hover:text-green-700 font-semibold"
          >
            Login
          </RouterLink>

          <RouterLink
            to="/register"
            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition"
          >
            Register
          </RouterLink>
        </template>

      </div>
    </div>
  </nav>
</template>