
import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/services/api'

export const useAuthStore = defineStore('auth', () => {
  const user = ref(null)
  const token = ref(localStorage.getItem('token'))
  const loading = ref(false)
  const error = ref(null)

  // Save token after login or registration
  const saveAuthentication = (data) => {
    token.value = data.token
    user.value = data.user

    localStorage.setItem('token', data.token)
  }

  // Register
  const register = async (formData) => {
    try {
      loading.value = true
      error.value = null

      const response = await api.post('/register', formData)

      saveAuthentication(response.data)

      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Registration failed'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Login
  const login = async (credentials) => {
    try {
      loading.value = true
      error.value = null

      const response = await api.post('/login', credentials)

      saveAuthentication(response.data)

      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Login failed'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Get authenticated user
  const fetchUser = async () => {
    if (!token.value) return

    try {
      const response = await api.get('/user')
      user.value = response.data.user
    } catch (err) {
      user.value = null
      token.value = null
      localStorage.removeItem('token')
    }
  }

  // Logout
  const logout = async () => {
    try {
      await api.post('/logout')
    } finally {
      user.value = null
      token.value = null
      localStorage.removeItem('token')
    }
  }

  const isAuthenticated = () => {
    return !!token.value
  }

  return {
    user,
    token,
    loading,
    error,
    register,
    login,
    logout,
    fetchUser,
    isAuthenticated,
  }
})
