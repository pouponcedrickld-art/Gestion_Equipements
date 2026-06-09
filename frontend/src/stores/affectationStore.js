import { defineStore } from 'pinia'
import { ref } from 'vue'
import affectationApi from '@/api/affectationApi'

export const useAffectationStore = defineStore('affectation', () => {
  const affectations = ref([])
  const loading = ref(false)
  const error = ref(null)

  const fetchAffectations = async () => {
    loading.value = true
    try {
      const response = await affectationApi.index()
      affectations.value = response.data.data || response.data
    } catch (err) {
      error.value = err.message
      throw err
    } finally {
      loading.value = false
    }
  }

  const createAffectation = async (data) => {
    loading.value = true
    try {
      const response = await affectationApi.store(data)
      await fetchAffectations()
      return response.data
    } finally {
      loading.value = false
    }
  }

  const enregistrerRetour = async (id) => {
    loading.value = true
    try {
      const response = await affectationApi.retour(id)
      await fetchAffectations()
      return response.data
    } finally {
      loading.value = false
    }
  }

  return {
    affectations, loading, error,
    fetchAffectations, createAffectation, enregistrerRetour
  }
})