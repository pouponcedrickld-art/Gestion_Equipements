import { defineStore } from 'pinia'
import { ref } from 'vue'
import rapportApi from '@/api/rapportApi'

export const useRapportStore = defineStore('rapport', () => {
  const inventaire = ref(null)
  const pannes = ref(null)
  const loading = ref(false)
  const error = ref(null)

  async function fetchInventaire() {
    loading.value = true
    error.value = null
    try {
      const response = await rapportApi.inventaire()
      if (response.data.success) {
        inventaire.value = response.data.data
        return inventaire.value
      }
    } catch (err) {
      error.value = err.response?.data?.message || err.message
    } finally {
      loading.value = false
    }
  }

  async function fetchPannes() {
    loading.value = true
    error.value = null
    try {
      const response = await rapportApi.pannes()
      if (response.data.success) {
        pannes.value = response.data.data
        return pannes.value
      }
    } catch (err) {
      error.value = err.response?.data?.message || err.message
    } finally {
      loading.value = false
    }
  }

  async function exporter(type) {
    try {
      const response = await rapportApi.export(type)
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || err.message
      throw err
    }
  }

  function resetStore() {
    inventaire.value = null
    pannes.value = null
    error.value = null
  }

  return {
    inventaire,
    pannes,
    loading,
    error,
    fetchInventaire,
    fetchPannes,
    exporter,
    resetStore
  }
})
