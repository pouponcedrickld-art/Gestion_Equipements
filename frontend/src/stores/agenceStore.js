import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/api/axiosConfig.js'

export const useAgenceStore = defineStore('agence', () => {
  const agences = ref([])
  const loading = ref(false)
  const error = ref(null)

  const agenceGenerale = computed(() => agences.value.find(a => a.type === 'generale'))
  const sousAgences = computed(() => agences.value.filter(a => a.type === 'sous_agence'))

  const fetchAgences = async () => {
    loading.value = true
    try {
      const { data } = await api.get('/agences')
      agences.value = data
    } catch (e) {
      error.value = e.response?.data?.message || 'Erreur'
    } finally {
      loading.value = false
    }
  }

  const createAgence = async (payload) => {
    const { data } = await api.post('/agences', payload)
    agences.value.push(data)
    return data
  }

  const updateAgence = async (id, payload) => {
    const { data } = await api.put(`/agences/${id}`, payload)
    const idx = agences.value.findIndex(a => a.id === id)
    if (idx !== -1) agences.value[idx] = data
    return data
  }

  const deleteAgence = async (id) => {
    await api.delete(`/agences/${id}`)
    agences.value = agences.value.filter(a => a.id !== id)
  }

  return {
    agences, agenceGenerale, sousAgences, loading, error,
    fetchAgences, createAgence, updateAgence, deleteAgence
  }
})
