import { computed, ref } from 'vue'
import { defineStore } from 'pinia'

const API_BASE = import.meta.env.VITE_API_BASE_URL || '/api'

async function request(path, options = {}, token = null) {
  const response = await fetch(`${API_BASE}${path}`, {
    ...options,
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      ...(token ? { Authorization: `Bearer ${token}` } : {}),
      ...options.headers,
    },
  })
  const data = await response.json().catch(() => ({}))
  if (!response.ok) {
    const error = new Error(data.message || 'Something went wrong. Please try again.')
    error.validation = data.errors || {}
    throw error
  }
  return data
}

function readSession() {
  try {
    return JSON.parse(localStorage.getItem('soko-session') || '{}')
  } catch {
    return {}
  }
}

export const useAuthStore = defineStore('auth', () => {
  const session = readSession()
  const token = ref(session.token || '')
  const user = ref(session.user || null)
  const loading = ref(false)
  const isAuthenticated = computed(() => Boolean(token.value && user.value))

  function saveSession(data) {
    token.value = data.token
    user.value = data.user
    localStorage.setItem('soko-session', JSON.stringify({ token: data.token, user: data.user }))
  }

  function clearSession() {
    token.value = ''
    user.value = null
    localStorage.removeItem('soko-session')
  }

  async function login(credentials) {
    loading.value = true
    try {
      const data = await request('/login', { method: 'POST', body: JSON.stringify(credentials) })
      saveSession(data)
      return data.user
    } finally {
      loading.value = false
    }
  }

  async function register(details) {
    loading.value = true
    try {
      const data = await request('/register', { method: 'POST', body: JSON.stringify(details) })
      saveSession(data)
      return data.user
    } finally {
      loading.value = false
    }
  }

  async function logout() {
    loading.value = true
    try {
      if (token.value) await request('/logout', { method: 'POST' }, token.value)
    } catch {
      // Clear the local session even if the API is temporarily unavailable.
    } finally {
      clearSession()
      loading.value = false
    }
  }

  async function refreshUser() {
    if (!token.value) return
    try {
      const data = await request('/profile', {}, token.value)
      user.value = data.user
      localStorage.setItem('soko-session', JSON.stringify({ token: token.value, user: data.user }))
    } catch {
      clearSession()
    }
  }

  return { token, user, loading, isAuthenticated, login, register, logout, refreshUser }
})
