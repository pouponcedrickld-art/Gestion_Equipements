import { defineStore } from 'pinia'
import mouvementApi from '@/api/mouvementApi.js'

export const useMouvementStore = defineStore('mouvement', {
  state: () => ({
    mouvements: [],
    loading: false,
    error: null
  }),
  actions: {
    async fetchMouvements(filters = {}) {
      this.loading = true
      this.error = null
      try {
        const response = await mouvementApi.index(filters)
        this.mouvements = response.data?.data || response.data || []
      } catch (error) {
        this.error = error.response?.data?.message || 'Erreur lors du chargement des mouvements'
      } finally {
        this.loading = false
      }
    }
  }
})
