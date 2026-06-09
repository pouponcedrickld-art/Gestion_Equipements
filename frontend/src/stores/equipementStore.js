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

          if (value instanceof Date) {
            formData.append(key, value.toISOString().split('T')[0])
          } else if (key === 'specifications' && typeof value === 'object') {
            formData.append(key, JSON.stringify(value))
          } else {
            formData.append(key, value)
          }
        }
      })

      const response = await equipementApi.store(formData)
      
      if (response.data.success) {
        const newEquipement = response.data.data
        if (Array.isArray(newEquipement)) {
          // Si création multiple, on rafraîchit la liste
          await fetchEquipements()
        } else {
          equipements.value.unshift(newEquipement)
        }
        return newEquipement
      } else {
        throw new Error(response.data.message)
      }
    } catch (err) {
      error.value = err.response?.data?.message || err.message
      console.error('Erreur createEquipement:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Mettre à jour un équipement
   */
  async function updateEquipement(id, data) {
    loading.value = true
    error.value = null
    
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
            // Ne pas ajouter la photo si ce n'est pas un nouveau fichier
          } else {
            formData.append(key, value)
          }
        }
      })

      const response = await equipementApi.update(id, formData)
      
      if (response.data.success) {
        const updatedEquipement = response.data.data
        const index = equipements.value.findIndex(eq => eq.id === id)
        if (index !== -1) {
          equipements.value[index] = updatedEquipement
        }
        if (currentEquipement.value && currentEquipement.value.id === id) {
          currentEquipement.value = updatedEquipement
        }
        return updatedEquipement
      } else {
        throw new Error(response.data.message)
      }
    } catch (err) {
      error.value = err.response?.data?.message || err.message
      console.error('Erreur updateEquipement:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Supprimer un équipement
   */
  async function deleteEquipement(id) {
    loading.value = true
    error.value = null
    
    try {
      const response = await equipementApi.destroy(id)
      
      if (response.data.success) {
        // Retirer de la liste locale
        equipements.value = equipements.value.filter(eq => eq.id !== id)
        
        if (currentEquipement.value && currentEquipement.value.id === id) {
          currentEquipement.value = null
        }
        
        return true
      } else {
        throw new Error(response.data.message)
      }
    } catch (err) {
      error.value = err.response?.data?.message || err.message
      console.error('Erreur deleteEquipement:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Recherche avancée d'équipements
   */
  async function searchEquipements(searchParams) {
    loading.value = true
    error.value = null
    
    try {
      const response = await equipementApi.search(searchParams)
      
      if (response.data.success) {
        return response.data.data
      } else {
        throw new Error(response.data.message)
      }
    } catch (err) {
      error.value = err.response?.data?.message || err.message
      console.error('Erreur searchEquipements:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Générer un QR code pour un équipement
   */
  async function generateQRCode(id) {
    loading.value = true
    error.value = null
    
    try {
      const response = await equipementApi.generateQr(id)
      
      if (response.data.success) {
        // Mettre à jour l'équipement avec le QR code
        const index = equipements.value.findIndex(eq => eq.id === id)
        if (index !== -1) {
          equipements.value[index].qr_code = response.data.data.qr_code
        }
        
        if (currentEquipement.value && currentEquipement.value.id === id) {
          currentEquipement.value.qr_code = response.data.data.qr_code
        }
        
        return response.data.data
      } else {
        throw new Error(response.data.message)
      }
    } catch (err) {
      error.value = err.response?.data?.message || err.message
      console.error('Erreur generateQRCode:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Prévisualiser un import
   */
  async function previewImport(file) {
    importState.value.previewing = true
    importState.value.error = null
    
    try {
      const formData = new FormData()
      formData.append('file', file)
      
      const response = await equipementApi.previewImport(formData)
      
      if (response.data.success) {
        importState.value.preview = response.data.data
        return response.data.data
      } else {
        throw new Error(response.data.message)
      }
    } catch (err) {
      importState.value.error = err.response?.data?.message || err.message
      console.error('Erreur previewImport:', err)
      throw err
    } finally {
      importState.value.previewing = false
    }
  }

  /**
   * Importer des équipements
   */
  async function importEquipements(file, agenceId = null) {
    importState.value.uploading = true
    importState.value.error = null
    
    try {
      const formData = new FormData()
      formData.append('file', file)
      if (agenceId) {
        formData.append('agence_id', agenceId)
      }
      
      const response = await equipementApi.import(formData)
      
      if (response.data.success) {
        // Recharger la liste après import
        await fetchEquipements()
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
