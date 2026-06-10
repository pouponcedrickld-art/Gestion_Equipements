import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/api/axiosConfig.js'

export const useUserStore = defineStore('user', () => {
  const users = ref([])
  const loading = ref(false)
  const error = ref(null)

  const agents = computed(() => users.value.filter(u => u.roles?.some(r => r.name === 'agent')))
  const chefsAgence = computed(() => users.value.filter(u => u.roles?.some(r => r.name === 'chef_agence')))

  const fetchUsers = async () => {
    loading.value = true
    try {
      const { data } = await api.get('/users')
      users.value = data.data || data
    } catch (e) {
      error.value = e.response?.data?.message || 'Erreur'
    } finally {
      loading.value = false
    }
  }

  const createUser = async (payload) => {
    const { data } = await api.post('/users', payload)
    users.value.push(data)
    return data
  }

  const updateUser = async (id, payload) => {
    const { data } = await api.put(`/users/${id}`, payload)
    const idx = users.value.findIndex(u => u.id === id)
    if (idx !== -1) users.value[idx] = data
    return data
  }

  const deleteUser = async (id) => {
    await api.delete(`/users/${id}`)
    users.value = users.value.filter(u => u.id !== id)
  }

  const toggleActif = async (id) => {
    const { data } = await api.post(`/users/${id}/toggle-actif`)
    const idx = users.value.findIndex(u => u.id === id)
    if (idx !== -1) users.value[idx] = data
    return data
  }

  return {
    users, agents, chefsAgence, loading, error,
    fetchUsers, createUser, updateUser, deleteUser, toggleActif
  }
})
