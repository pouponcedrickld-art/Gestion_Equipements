import { defineStore } from 'pinia'
import panneApi from '@/api/panneApi.js'

export const usePanneStore = defineStore('panne', {
  state: () => ({
    pannes: [],
    loading: false,
    error: null
  }),
  actions: {
    async fetchPannes(filters = {}) {
      this.loading = true
      this.error = null
      try {
        const response = await panneApi.index(filters)
        this.pannes = response.data?.data || response.data || []
      } catch (error) {
        this.error = error.response?.data?.message || 'Erreur lors du chargement des pannes'
      } finally {
        this.loading = false
      }
    },
    async createPanne(data) {
      this.loading = true
      this.error = null
      try {
        const response = await panneApi.store(data)
        await this.fetchPannes()
        return response.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Erreur lors de la création de la panne'
        throw error
      } finally {
        this.loading = false
      }
    },
    async updatePanne(id, data) {
      this.loading = true
      this.error = null
      try {
        const response = await panneApi.update(id, data)
        await this.fetchPannes()
        return response.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Erreur lors de la mise à jour de la panne'
        throw error
      } finally {
        this.loading = false
      }
    },
    async deletePanne(id) {
      this.loading = true
      this.error = null
      try {
        await panneApi.destroy(id)
        await this.fetchPannes()
      } catch (error) {
        this.error = error.response?.data?.message || 'Erreur lors de la suppression de la panne'
        throw error
      } finally {
        this.loading = false
      }
    },
    async transmettreMaintenance(id, data) {
      this.loading = true
      this.error = null
      try {
        const response = await panneApi.transmettreMaintenance(id, data)
        await this.fetchPannes()
        return response.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Erreur lors de la transmission'
        throw error
      } finally {
        this.loading = false
      }
    },
    async diagnostiquer(id, data) {
      this.loading = true
      this.error = null
      try {
        const response = await panneApi.diagnostiquer(id, data)
        await this.fetchPannes()
        return response.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Erreur lors du diagnostic'
        throw error
      } finally {
        this.loading = false
      }
    }
  }
})
