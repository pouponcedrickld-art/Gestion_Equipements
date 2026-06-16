import { defineStore } from 'pinia'
import { ref } from 'vue'
import demandeAgenceApi from '@/api/demandeAgenceApi'

export const useDemandeAgenceStore = defineStore('demandeAgence', () => {
  const demandes = ref([])
  const currentDemande = ref(null)
  const loading = ref(false)
  const error = ref(null)

  async function fetchDemandes() {
    loading.value = true
    error.value = null
    try {
      const response = await demandeAgenceApi.index()
      if (response.data.success) {
        demandes.value = response.data.data
      }
    } catch (err) {
      error.value = err.response?.data?.message || err.message
    } finally {
      loading.value = false
    }
  }

  async function fetchDemande(id) {
    loading.value = true
    error.value = null
    try {
      const response = await demandeAgenceApi.show(id)
      if (response.data.success) {
        currentDemande.value = response.data.data
        return currentDemande.value
      }
    } catch (err) {
      error.value = err.response?.data?.message || err.message
    } finally {
      loading.value = false
    }
  }

  async function createDemande(data) {
    loading.value = true
    error.value = null
    try {
      const response = await demandeAgenceApi.store(data)
      if (response.data.success) {
        demandes.value.unshift(response.data.data)
        return response.data.data
      }
    } catch (err) {
      error.value = err.response?.data?.message || err.message
      throw err
    } finally {
      loading.value = false
    }
  }

  async function updateDemande(id, data) {
    loading.value = true
    error.value = null
    try {
      const response = await demandeAgenceApi.update(id, data)
      if (response.data.success) {
        const index = demandes.value.findIndex(d => d.id === id)
        if (index !== -1) demandes.value[index] = response.data.data
        return response.data.data
      }
    } catch (err) {
      error.value = err.response?.data?.message || err.message
      throw err
    } finally {
      loading.value = false
    }
  }

  async function deleteDemande(id) {
    loading.value = true
    error.value = null
    try {
      await demandeAgenceApi.destroy(id)
      demandes.value = demandes.value.filter(d => d.id !== id)
    } catch (err) {
      error.value = err.response?.data?.message || err.message
      throw err
    } finally {
      loading.value = false
    }
  }

  async function traiterDemande(id, data) {
    loading.value = true
    error.value = null
    try {
      const response = await demandeAgenceApi.traiter(id, data)
      if (response.data.success) {
        const index = demandes.value.findIndex(d => d.id === id)
        if (index !== -1) demandes.value[index] = response.data.data
        return response.data.data
      }
    } catch (err) {
      error.value = err.response?.data?.message || err.message
      throw err
    } finally {
      loading.value = false
    }
  }

  function resetStore() {
    demandes.value = []
    currentDemande.value = null
    error.value = null
  }

  return {
    demandes,
    currentDemande,
    loading,
    error,
    fetchDemandes,
    fetchDemande,
    createDemande,
    updateDemande,
    deleteDemande,
    traiterDemande,
    resetStore
  }
})
