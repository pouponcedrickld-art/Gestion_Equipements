// =============================================
// FICHIER : equipementStore.js
// RÔLE : Store Pinia pour gérer l'état des équipements dans l'application
// =============================================

import { defineStore } from 'pinia' // Importe Pinia pour créer le store
import equipementApi from '@/api/equipementApi' // Importe l'API client pour les équipements

// Crée et exporte le store d'équipements
export const useEquipementStore = defineStore('equipement', {
  // =============================================
  // STATE : État réactif initial du store
  // =============================================
  state: () => ({
    equipements: [], // Liste des équipements
    loading: false, // Indique si une requête est en cours
    error: null, // Stocke les erreurs éventuelles
    cache: new Map() // Cache pour éviter les requêtes répétées
  }),

  // =============================================
  // GETTERS : Propriétés calculées à partir du state
  // =============================================
  getters: {
    // Récupère la liste des équipements disponibles
    equipementsDisponibles: (state) => {
      return state.equipements.filter(e =>
        e.statut_global !== 'hors_service' &&
        e.etat === 'en_service'
      )
    },

    // Récupère un équipement par son ID
    getEquipementById: (state) => (id) => {
      return state.equipements.find(e => e.id === id)
    },

    // Vérifie si on est en cours de chargement
    isLoading: (state) => state.loading,

    // Vérifie si il y a une erreur
    hasError: (state) => !!state.error,

    // =============================================
    // STATISTIQUES POUR LE DASHBOARD
    // =============================================
    // Nombre total d'équipements
    totalEquipements: (state) => state.equipements.length,
    // Nombre d'équipements en stock
    equipementsEnStock: (state) =>
      state.equipements.filter(e => e.etat === 'nouveau' || e.etat === 'actif').length,
    // Nombre d'équipements affectés
    equipementsAffectes: (state) =>
      state.equipements.filter(e => e.statut_global === 'affecte').length,
    // Nombre d'équipements en maintenance
    equipementsEnMaintenance: (state) =>
      state.equipements.filter(e => e.etat === 'en_maintenance').length,
    // Nombre d'équipements en panne
    equipementsEnPanne: (state) =>
      state.equipements.filter(e => e.etat === 'hors_service').length,

    // Répartition des équipements par catégorie
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

  // =============================================
  // ACTIONS : Fonctions pour modifier l'état et interroger l'API
  // =============================================
  actions: {
    // Récupère la liste des équipements
    async fetchEquipements(filters = {}) {
      // Crée une clé de cache avec les filtres
      const cacheKey = `all_${JSON.stringify(filters)}`

      // Vérifie si on a des données en cache valides (moins de 5 minutes)
      const cached = this.cache.get(cacheKey)
      if (cached && Date.now() - cached.timestamp < 5 * 60 * 1000) {
        this.equipements = cached.data
        return
      }

      // Active l'état de chargement et réinitialise l'erreur
      this.loading = true
      this.error = null

      try {
        // Appelle l'API pour récupérer les équipements
        const response = await equipementApi.index(filters)
        // Traite la réponse (Laravel renvoie souvent les données dans data.data)
        const result = response.data?.data
        this.equipements = result?.data || result || []

        // Enregistre les données en cache
        this.cache.set(cacheKey, {
          data: this.equipements,
          timestamp: Date.now()
        })
      } catch (error) {
        // Gère les erreurs
        this.error = error.response?.data?.message || 'Erreur lors du chargement des équipements'
        console.error('Erreur fetchEquipements:', error)
      } finally {
        // Désactive l'état de chargement, quoi qu'il arrive
        this.loading = false
      }
    },

    // Récupère un équipement par son ID
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

    // Crée un nouvel équipement
    async createEquipement(data) {
      this.loading = true
      this.error = null

      try {
        // Convertit les données en FormData pour gérer les uploads de fichiers
        const formData = new FormData()
        Object.keys(data).forEach(key => {
          const value = data[key]
          if (value !== null && value !== undefined) {
            if (value instanceof Date) {
              // Si c'est une date, on formatte en YYYY-MM-DD
              formData.append(key, value.toISOString().split('T')[0])
            } else if (key === 'specifications' && typeof value === 'object') {
              // Si c'est un objet, on convertit en JSON
              formData.append(key, JSON.stringify(value))
            } else {
              formData.append(key, value)
            }
          }
        })

        // Log pour debug
        console.log('Données envoyées au serveur:', data)
        console.log('FormData:', [...formData.entries()])

        const response = await equipementApi.store(formData)

        if (response.data.success) {
          this.clearCache() // Vide le cache
          const newEquipement = response.data.data
          if (Array.isArray(newEquipement)) {
            // Si c'est un lot, on recharge toute la liste
            await this.fetchEquipements()
          } else {
            // Sinon, on ajoute le nouvel équipement au début de la liste
            this.equipements.unshift(newEquipement)
          }
          return newEquipement
        } else {
          throw new Error(response.data.message)
        }
      } catch (err) {
        // Log détaillé des erreurs pour le debug
        console.error('Erreur createEquipement complète:', err)
        console.error('Réponse du serveur:', err.response?.data)
        this.error = err.response?.data?.message || err.message
        throw err
      } finally {
        this.loading = false
      }
    },

    // Met à jour un équipement existant
    async updateEquipement(id, data) {
      this.loading = true
      this.error = null

      try {
        // Convertit en FormData
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
              // Si c'est la photo mais ce n'est pas un fichier, on ne l'ajoute pas (pour ne pas écraser)
            } else {
              formData.append(key, value)
            }
          }
        })

        const response = await equipementApi.update(id, formData)

        if (response.data.success) {
          this.clearCache()
          const updatedEquipement = response.data.data
          // Met à jour l'équipement dans la liste
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

    // Supprime un équipement
    async deleteEquipement(id) {
      this.loading = true
      this.error = null

      try {
        const response = await equipementApi.destroy(id)

        if (response.data.success) {
          this.clearCache()
          // Retire l'équipement de la liste
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

    // Génère un QR code pour un équipement
    async generateQRCode(id) {
      this.loading = true
      this.error = null

      try {
        const response = await equipementApi.generateQr(id)

        if (response.data.success) {
          this.clearCache()
          // Met à jour le QR code dans la liste
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

    // Vide le cache
    clearCache() {
      this.cache.clear()
    },

    // Réinitialise l'erreur
    resetError() {
      this.error = null
    }
  }
})
