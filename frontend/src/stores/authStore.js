import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import authApi from '@/api/authApi'

export const useAuthStore = defineStore('auth', () => {
  const user = ref(null)
  const token = ref(localStorage.getItem('token') || null)
  const loading = ref(false)

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
    try {
      const response = await authApi.login(credentials)
      token.value = response.data.token
      localStorage.setItem('token', token.value)
      user.value = response.data.user
      return response.data
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
    user, token, loading,
    isAuthenticated, userRole, userAgence,
    isSuperAdmin, isGestionnaireGeneral, isChefAgence,
    isGestionnaireStock, isTechnicien, isAgent,
    login, logout, fetchUser, hasRole, canViewAgence
  }
})
