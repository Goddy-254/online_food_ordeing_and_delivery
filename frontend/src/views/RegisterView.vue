
<script setup>
import { ref } from 'vue'
import { useRouter, RouterLink } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const authStore = useAuthStore()
const router = useRouter()

const name = ref('')
const email = ref('')
const password = ref('')
const passwordConfirmation = ref('')
const errorMessage = ref('')

const handleRegister = async () => {
  try {
    errorMessage.value = ''

    await authStore.register({
      name: name.value,
      email: email.value,
      password: password.value,
      password_confirmation: passwordConfirmation.value,
    })

    await router.push('/menu')

  } catch (error) {
    const errors = error.response?.data?.errors

    errorMessage.value = errors
      ? Object.values(errors).flat().join(' ')
      : error.response?.data?.message || 'Registration failed'
  }
}
</script>

<template>
  <div class="min-h-screen bg-gray-50 flex items-center justify-center px-4">

    <div class="bg-white shadow-lg rounded-xl p-8 w-full max-w-md">

      <h1 class="text-3xl font-bold text-center text-green-600 mb-2">
        Create Account
      </h1>

      <p class="text-center text-gray-500 mb-8">
        Register to order your favourite food
      </p>

      <div
        v-if="errorMessage"
        class="bg-red-100 text-red-700 p-3 rounded-lg mb-4"
      >
        {{ errorMessage }}
      </div>

      <form @submit.prevent="handleRegister" class="space-y-5">

        <div>
          <label class="block font-medium mb-2">Full Name</label>

          <input
            v-model="name"
            type="text"
            required
            placeholder="Enter your name"
            class="w-full border rounded-lg p-3 focus:ring-2 focus:ring-green-500 outline-none"
          />
        </div>

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
            minlength="8"
            placeholder="Create a password"
            class="w-full border rounded-lg p-3 focus:ring-2 focus:ring-green-500 outline-none"
          />
        </div>

        <div>
          <label class="block font-medium mb-2">
            Confirm Password
          </label>

          <input
            v-model="passwordConfirmation"
            type="password"
            required
            placeholder="Confirm your password"
            class="w-full border rounded-lg p-3 focus:ring-2 focus:ring-green-500 outline-none"
          />
        </div>

        <button
          type="submit"
          :disabled="authStore.loading"
          class="w-full bg-green-600 hover:bg-green-700 disabled:bg-gray-400 text-white py-3 rounded-lg font-semibold"
        >
          {{ authStore.loading ? 'Creating Account...' : 'Register' }}
        </button>

      </form>

      <p class="text-center text-gray-600 mt-6">
        Already have an account?

        <RouterLink
          to="/login"
          class="text-green-600 font-semibold hover:underline"
        >
          Login
        </RouterLink>
      </p>

    </div>
  </div>
</template>
