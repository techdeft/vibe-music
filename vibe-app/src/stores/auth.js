import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { api } from '@/services/api'

export const useAuthStore = defineStore('auth', () => {
  const user = ref(null)
  const loading = ref(false)
  const error = ref('')

  const isLoggedIn = computed(() => !!user.value)
  const isAdmin = computed(() => user.value?.is_admin || false)

  // Fetch current user from WP session on app boot
  async function fetchMe() {
    try {
      const res = await api.authMe()
      user.value = res.user || null
    } catch {
      user.value = null
    }
  }

  async function login(username, password) {
    loading.value = true
    error.value = ''
    try {
      const res = await api.authLogin(username, password)
      user.value = res.user
      // Update nonce for subsequent authenticated requests
      if (res.nonce) api.updateNonce(res.nonce)
      return true
    } catch (e) {
      error.value = e.message || 'Invalid username or password.'
      return false
    } finally {
      loading.value = false
    }
  }

  async function register(username, email, password, displayName) {
    loading.value = true
    error.value = ''
    try {
      const res = await api.authRegister(username, email, password, displayName)
      user.value = res.user
      if (res.nonce) api.updateNonce(res.nonce)
      return true
    } catch (e) {
      error.value = e.message || 'Registration failed. Please try again.'
      return false
    } finally {
      loading.value = false
    }
  }

  async function logout() {
    try {
      await api.authLogout()
    } catch {}
    user.value = null
  }

  async function forgotPassword(email) {
    loading.value = true
    error.value = ''
    try {
      await api.authForgotPassword(email)
      return true
    } catch (e) {
      error.value = e.message || 'Something went wrong.'
      return false
    } finally {
      loading.value = false
    }
  }

  function clearError() {
    error.value = ''
  }

  return {
    user, loading, error, isLoggedIn, isAdmin,
    fetchMe, login, register, logout, forgotPassword, clearError,
  }
})
