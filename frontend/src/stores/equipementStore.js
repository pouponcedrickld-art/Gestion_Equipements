import { defineStore } from 'pinia'
import equipementApi from '@/api/equipementApi'

export const useEquipementStore = defineStore('equipement', {
  state: () => ({
    equipements: [],
    loading: false,
    error: null,
    cache: new Map()
  }),

  getters: {
    equipementsDisponibles: (state) => {
      return state.equipements.filter(e =>
        e.statut_global !== 'hors_service' &&
        e.etat === 'en_service'
      )
    },

    getEquipementById: (state) => (id) => {
      return state.equipements.find(e => e.id === id)
    },

    isLoading: (state) => state.loading,

    hasError: (state) => !!state.error,

    // Stats for dashboard and equipements view
    totalEquipements: (state) => state.equipements.length,
    equipementsEnStock: (state) =>
      state.equipements.filter(e => e.etat === 'nouveau' || e.etat === 'actif').length,
    equipementsAffectes: (state) =>
      state.equipements.filter(e => e.statut_global === 'affecte').length,
    equipementsEnMaintenance: (state) =>
      state.equipements.filter(e => e.etat === 'en_maintenance').length,
    equipementsEnPanne: (state) =>
      state.equipements.filter(e => e.etat === 'hors_service').length,

    equipementsParCategorie: (state) => {
      const categories = {}
      state.equipements.forEach(e => {
        if (e.categorie) {
          const catId = e.categorie.id
          if (!categories[catId]) {
            categories[catId] = {
              id: catId,
              nom: e.categorie.nom,
              equipements_count: 0
            }
          }
          categories[catId].equipements_count++
        }
      })
      return Object.values(categories)
    }
  },

  actions: {
    async fetchEquipements(filters = {}) {
      const cacheKey = `all_${JSON.stringify(filters)}`

      const cached = this.cache.get(cacheKey)
      if (cached && Date.now() - cached.timestamp < 5 * 60 * 1000) {
        this.equipements = cached.data
        return
      }

      this.loading = true
      this.error = null

      try {
        const response = await equipementApi.index(filters)
        const result = response.data?.data
        this.equipements = result?.data || result || []

        this.cache.set(cacheKey, {
          data: this.equipements,
          timestamp: Date.now()
        })
      } catch (error) {
        this.error = error.response?.data?.message || 'Erreur lors du chargement des équipements'
        console.error('Erreur fetchEquipements:', error)
      } finally {
        this.loading = false
      }
    },

    async fetchEquipementById(id) {
      this.loading = true
      this.error = null

      try {
        const response = await equipementApi.show(id)
        return response.data
      } catch (error) {
        this.error = error.response?.data?.message || "Erreur lors du chargement de l'équipement "
        console.error('Erreur fetchEquipementById:', error)
        throw error
      } finally {
        this.loading = false
      }
    },

    async createEquipement(data) {
      this.loading = true
      this.error = null

      try {
        const formData = new FormData()
        Object.keys(data).forEach(key => {
          const value = data[key]
          if (value !== null && value !== undefined) {
            if (value instanceof Date) {
              formData.append(key, value.toISOString().split('T')[0])
            } else if (key === 'specifications' && typeof value === 'object') {
              formData.append(key, JSON.stringify(value))
            } else {
              formData.append(key, value)
            }
          }
        })

        // Log des données envoyées
        console.log('Données envoyées au serveur:', data)
        console.log('FormData:', [...formData.entries()])

        const response = await equipementApi.store(formData)

        if (response.data.success) {
          this.clearCache()
          const newEquipement = response.data.data
          if (Array.isArray(newEquipement)) {
            await this.fetchEquipements()
          } else {
            this.equipements.unshift(newEquipement)
          }
          return newEquipement
        } else {
          throw new Error(response.data.message)
        }
      } catch (err) {
        // Log détaillé des erreurs
        console.error('Erreur createEquipement complète:', err)
        console.error('Réponse du serveur:', err.response?.data)
        this.error = err.response?.data?.message || err.message
        throw err
      } finally {
        this.loading = false
      }
    },

    async updateEquipement(id, data) {
      this.loading = true
      this.error = null

      try {
        const formData = new FormData()
        formData.append('_method', 'PUT')

        Object.keys(data).forEach(key => {
          const value = data[key]
          if (value !== null && value !== undefined) {
            if (value instanceof Date) {
              formData.append(key, value.toISOString().split('T')[0])
            } else if (key === 'specifications' && typeof value === 'object') {
              formData.append(key, JSON.stringify(value))
            } else if (key === 'photo' && !(value instanceof File)) {
            } else {
              formData.append(key, value)
            }
          }
        })

        const response = await equipementApi.update(id, formData)

        if (response.data.success) {
          this.clearCache()
          const updatedEquipement = response.data.data
          const index = this.equipements.findIndex(eq => eq.id === id)
          if (index !== -1) {
            this.equipements[index] = updatedEquipement
          }
          return updatedEquipement
        } else {
          throw new Error(response.data.message)
        }
      } catch (err) {
        this.error = err.response?.data?.message || err.message
        console.error('Erreur updateEquipement:', err)
        throw err
      } finally {
        this.loading = false
      }
    },

    async deleteEquipement(id) {
      this.loading = true
      this.error = null

      try {
        const response = await equipementApi.destroy(id)

        if (response.data.success) {
          this.clearCache()
          this.equipements = this.equipements.filter(eq => eq.id !== id)
          return true
        } else {
          throw new Error(response.data.message)
        }
      } catch (err) {
        this.error = err.response?.data?.message || err.message
        console.error('Erreur deleteEquipement:', err)
        throw err
      } finally {
        this.loading = false
      }
    },

    async generateQRCode(id) {
      this.loading = true
      this.error = null

      try {
        const response = await equipementApi.generateQr(id)

        if (response.data.success) {
          this.clearCache()
          const index = this.equipements.findIndex(eq => eq.id === id)
          if (index !== -1) {
            this.equipements[index].qr_code = response.data.data.qr_code
          }
          return response.data.data
        } else {
          throw new Error(response.data.message)
        }
      } catch (err) {
        this.error = err.response?.data?.message || err.message
        console.error('Erreur generateQRCode:', err)
        throw err
      } finally {
        this.loading = false
      }
    },

    clearCache() {
      this.cache.clear()
    },

    resetError() {
      this.error = null
    }
  }
})
