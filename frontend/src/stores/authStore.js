import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import authApi from '@/api/authApi.js'

export const useAuthStore = defineStore('auth', () => {
  const user = ref(null)
  const token = ref(localStorage.getItem('token') || null)
  const loading = ref(false)
  const requires2FA = ref(false)
  const tempUserId = ref(null)

  const isAuthenticated = computed(() => !!token.value)
  const userRole = computed(() => user.value?.role || null)
  const userAgence = computed(() => user.value?.agence_id || null)
  const isSuperAdmin = computed(() => userRole.value === 'super_admin')
  const isGestionnaireGeneral = computed(() => userRole.value === 'gestionnaire_stock_general')
  const isChefAgence = computed(() => userRole.value === 'chef_agence')
  const isGestionnaireStock = computed(() => userRole.value === 'gestionnaire_stock')
  const isTechnicien = computed(() => userRole.value === 'technicien_maintenance')
  const isAgent = computed(() => userRole.value === 'agent')

  const login = async (credentials) => {
    loading.value = true
    requires2FA.value = false
    tempUserId.value = null
    try {
      const response = await authApi.login(credentials)
      if (response.data.requires_2fa) {
        requires2FA.value = true
        tempUserId.value = response.data.user_id
        return { requires2FA: true }
      }
      token.value = response.data.token
      localStorage.setItem('token', token.value)
      user.value = response.data.user
      return { requires2FA: false }
    } finally {
      loading.value = false
    }
  }

  const verify2FA = async (code) => {
    loading.value = true
    try {
      const response = await authApi.verify2FA({
        user_id: tempUserId.value,
        code: code
      })
      token.value = response.data.token
      localStorage.setItem('token', token.value)
      user.value = response.data.user
      requires2FA.value = false
      tempUserId.value = null
    } finally {
      loading.value = false
    }
  }

  const logout = async () => {
    try {
      await authApi.logout()
    } catch (e) {
      console.log('Logout error', e)
    }
    token.value = null
    user.value = null
    requires2FA.value = false
    tempUserId.value = null
    localStorage.removeItem('token')
  }

  const fetchUser = async () => {
    try {
      const response = await authApi.me()
      user.value = response.data.user
    } catch {
      logout()
    }
  }

  const hasRole = (roles) => {
    if (!Array.isArray(roles)) roles = [roles]
    return roles.includes(userRole.value)
  }

  const canViewAgence = (agenceId) => {
    if (isSuperAdmin.value) return true
    return userAgence.value === agenceId
  }

  return {
    user, token, loading, requires2FA, tempUserId,
    isAuthenticated, userRole, userAgence,
    isSuperAdmin, isGestionnaireGeneral, isChefAgence,
    isGestionnaireStock, isTechnicien, isAgent,
    login, verify2FA, logout, fetchUser, hasRole, canViewAgence
  }
})
