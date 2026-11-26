import { defineStore } from 'pinia'
import { computed, ref } from 'vue'
import api from '@/services/api'

// Keep admin credentials separate from the customer account in local storage.
export const useAdminAuthStore = defineStore('adminAuth', () => {
  const token = ref(localStorage.getItem('admin_token'))
  const user = ref(readSavedUser())
  const loading = ref(false)

  function readSavedUser() {
    try {
      return JSON.parse(localStorage.getItem('admin_user') || 'null')
    } catch {
      return null
    }
  }

  const isAuthenticated = computed(() => Boolean(token.value && user.value?.role === 'admin'))

  async function login(credentials) {
    loading.value = true
    try {
      const { data } = await api.post('/admin/login', credentials)
      token.value = data.token
      user.value = data.user
      localStorage.setItem('admin_token', data.token)
      localStorage.setItem('admin_user', JSON.stringify(data.user))
      return data
    } finally {
      loading.value = false
    }
  }

  async function logout() {
    try {
      if (token.value) await api.post('/admin/logout')
    } finally {
      token.value = null
      user.value = null
      localStorage.removeItem('admin_token')
      localStorage.removeItem('admin_user')
    }
  }

  return { token, user, loading, isAuthenticated, login, logout }
})
