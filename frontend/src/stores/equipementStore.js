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
    /**
     * Équipements disponibles pour maintenance
     */
    equipementsDisponibles: (state) => {
      return state.equipements.filter(e => 
        e.statut_global !== 'hors_service' && 
        e.etat === 'en_service'
      )
    },

    /**
     * Récupère un équipement par ID
     */
    getEquipementById: (state) => (id) => {
      return state.equipements.find(e => e.id === id)
    },

    /**
     * Indique si les données sont en cours de chargement
     */
    isLoading: (state) => state.loading,

    /**
     * Indique s'il y a une erreur
     */
    hasError: (state) => !!state.error
  },

  actions: {
    /**
     * Récupère tous les équipements
     */
    async fetchEquipements(filters = {}) {
      const cacheKey = `all_${JSON.stringify(filters)}`
      
      // Vérifier le cache (TTL 5 minutes)
      const cached = this.cache.get(cacheKey)
      if (cached && Date.now() - cached.timestamp < 5 * 60 * 1000) {
        this.equipements = cached.data
        return
      }

      this.loading = true
      this.error = null

      try {
        const response = await equipementApi.getAll(filters)
        this.equipements = response.data || []
        
        // Mettre en cache
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

    /**
     * Récupère un équipement par ID
     */
    async fetchEquipementById(id) {
      this.loading = true
      this.error = null

      try {
        const response = await equipementApi.getById(id)
        
        // Mettre à jour dans la liste si existant
        const index = this.equipements.findIndex(e => e.id === id)
        if (index > -1) {
          this.equipements[index] = response.data
        } else {
          this.equipements.push(response.data)
        }
        
        return response.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Erreur lors du chargement de l\'équipement'
        console.error('Erreur fetchEquipementById:', error)
        throw error
      } finally {
        this.loading = false
      }
    },

    /**
     * Efface le cache
     */
    clearCache() {
      this.cache.clear()
    },

    /**
     * Réinitialise l'erreur
     */
    resetError() {
      this.error = null
    }
  }
})
