<script setup>
import { computed, ref } from 'vue'
import { useRoute, useRouter, RouterLink } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const auth = useAuthStore()
const route = useRoute()
const router = useRouter()
const mode = ref('login')
const form = ref({ name: '', email: '', password: '', password_confirmation: '' })
const errorMessage = ref('')
const fieldErrors = ref({})
const isRegister = computed(() => mode.value === 'register')
const nextPath = computed(() => typeof route.query.redirect === 'string' && route.query.redirect.startsWith('/') && !route.query.redirect.startsWith('//') ? route.query.redirect : '/')

function changeMode(next) {
  mode.value = next
  errorMessage.value = ''
  fieldErrors.value = {}
}

async function submit() {
  errorMessage.value = ''
  fieldErrors.value = {}
  try {
    if (isRegister.value) {
      await auth.register({ name: form.value.name, email: form.value.email, password: form.value.password, password_confirmation: form.value.password_confirmation })
    } else {
      await auth.login({ email: form.value.email, password: form.value.password })
    }
    router.replace(nextPath.value)
  } catch (error) {
    errorMessage.value = error.message
    fieldErrors.value = error.validation || {}
  }
}
</script>

<template>
  <main class="auth-page">
    <section class="auth-card">
      <RouterLink class="auth-back" to="/">← Back to the menu</RouterLink>
      <RouterLink class="brand auth-brand" to="/">Soko<span>Kitchen</span></RouterLink>
      <h1>{{ isRegister ? 'Create your account' : 'Welcome back' }}</h1>
      <p>{{ isRegister ? 'Sign up to make ordering your favourites easier.' : 'Log in to continue with your order.' }}</p>
      <form @submit.prevent="submit">
        <label v-if="isRegister">Your name<input v-model.trim="form.name" autocomplete="name" required placeholder="Your name"><small v-if="fieldErrors.name">{{ fieldErrors.name[0] }}</small></label>
        <label>Email address<input v-model.trim="form.email" type="email" autocomplete="email" required placeholder="you@example.com"><small v-if="fieldErrors.email">{{ fieldErrors.email[0] }}</small></label>
        <label>Password<input v-model="form.password" type="password" :autocomplete="isRegister ? 'new-password' : 'current-password'" required minlength="8" placeholder="At least 8 characters"><small v-if="fieldErrors.password">{{ fieldErrors.password[0] }}</small></label>
        <label v-if="isRegister">Confirm password<input v-model="form.password_confirmation" type="password" autocomplete="new-password" required minlength="8" placeholder="Enter your password again"></label>
        <p v-if="errorMessage" class="auth-error" role="alert">{{ errorMessage }}</p>
        <button class="auth-submit" type="submit" :disabled="auth.loading">{{ auth.loading ? 'Please wait…' : isRegister ? 'Create account' : 'Log in' }}</button>
      </form>
      <p class="auth-switch">{{ isRegister ? 'Already have an account?' : 'New to Soko Kitchen?' }} <button @click="changeMode(isRegister ? 'login' : 'register')">{{ isRegister ? 'Log in' : 'Create account' }}</button></p>
    </section>
  </main>
</template>
