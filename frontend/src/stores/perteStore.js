import { defineStore } from 'pinia'
import perteApi from '@/api/perteApi.js'

export const usePerteStore = defineStore('perte', {
  state: () => ({
    pertes: [],
    loading: false,
    error: null
  }),
  actions: {
    async fetchPertes(filters = {}) {
      this.loading = true
      this.error = null
      try {
        const response = await perteApi.index(filters)
        this.pertes = response.data?.data || response.data || []
      } catch (error) {
        this.error = error.response?.data?.message || 'Erreur lors du chargement des pertes'
      } finally {
        this.loading = false
      }
    },
    async createPerte(data) {
      this.loading = true
      this.error = null
      try {
        const response = await perteApi.store(data)
        await this.fetchPertes()
        return response.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Erreur lors de la création de la perte'
        throw error
      } finally {
        this.loading = false
      }
    },
    async updatePerte(id, data) {
      this.loading = true
      this.error = null
      try {
        const response = await perteApi.update(id, data)
        await this.fetchPertes()
        return response.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Erreur lors de la mise à jour de la perte'
        throw error
      } finally {
        this.loading = false
      }
    },
    async deletePerte(id) {
      this.loading = true
      this.error = null
      try {
        await perteApi.destroy(id)
        await this.fetchPertes()
      } catch (error) {
        this.error = error.response?.data?.message || 'Erreur lors de la suppression de la perte'
        throw error
      } finally {
        this.loading = false
      }
    }
  }
})
