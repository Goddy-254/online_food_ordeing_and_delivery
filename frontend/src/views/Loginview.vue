
<script setup>
import { ref } from 'vue'
import { useRouter, RouterLink } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const authStore = useAuthStore()
const router = useRouter()

const email = ref('')
const password = ref('')
const errorMessage = ref('')

const handleLogin = async () => {
  try {
    errorMessage.value = ''

    await authStore.login({
      email: email.value,
      password: password.value,
    })

    await router.push('/menu')

  } catch (error) {
    errorMessage.value =
      error.response?.data?.message || 'Unable to login'
  }
}
</script>

<template>
  <div class="min-h-screen bg-gray-50 flex items-center justify-center px-4">

    <div class="bg-white shadow-lg rounded-xl p-8 w-full max-w-md">

      <h1 class="text-3xl font-bold text-center text-green-600 mb-2">
        Welcome Back
      </h1>

      <p class="text-center text-gray-500 mb-8">
        Login to your FoodExpress account
      </p>

      <div
        v-if="errorMessage"
        class="bg-red-100 text-red-700 p-3 rounded-lg mb-4"
      >
        {{ errorMessage }}
      </div>

      <form @submit.prevent="handleLogin" class="space-y-5">

        <div>
          <label class="block font-medium mb-2">Email</label>

          <input
            v-model="email"
            type="email"
            required
            placeholder="Enter your email"
            class="w-full border rounded-lg p-3 focus:ring-2 focus:ring-green-500 outline-none"
          />
        </div>

        <div>
          <label class="block font-medium mb-2">Password</label>

          <input
            v-model="password"
            type="password"
            required
            placeholder="Enter your password"
            class="w-full border rounded-lg p-3 focus:ring-2 focus:ring-green-500 outline-none"
          />
        </div>

        <button
          type="submit"
          :disabled="authStore.loading"
          class="w-full bg-green-600 hover:bg-green-700 disabled:bg-gray-400 text-white py-3 rounded-lg font-semibold"
        >
          {{ authStore.loading ? 'Logging in...' : 'Login' }}
        </button>

      </form>

      <p class="text-center text-gray-600 mt-6">
        Don't have an account?

        <RouterLink
          to="/register"
          class="text-green-600 font-semibold hover:underline"
        >
          Register
        </RouterLink>
      </p>

    </div>
  </div>
</template>
