import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import equipementApi from '@/api/equipementApi'
import { useAuthStore } from '@/stores/authStore'

export const useEquipementStore = defineStore('equipement', () => {
  // États réactifs
  const equipements = ref([])
  const currentEquipement = ref(null)
  const loading = ref(false)
  const error = ref(null)
  
  // État de pagination
  const pagination = ref({
    current_page: 1,
    last_page: 1,
    per_page: 15,
    total: 0
  })

  // Filtres actifs
  const filters = ref({
    search: '',
    agence_id: null,
    statut_global: '',
    etat: '',
    categorie_id: null,
    garantie_expire_bientot: false,
    disponibles_transfert: false,
    sort_by: 'reference',
    sort_order: 'asc'
  })

  // État de l'import
  const importState = ref({
    uploading: false,
    previewing: false,
    preview: null,
    error: null
  })

  // État du scan QR
  const scanState = ref({
    scanning: false,
    result: null,
    error: null
  })

  // Getters calculés
  const filteredEquipements = computed(() => {
    let result = equipements.value

    if (filters.value.search) {
      const term = filters.value.search.toLowerCase()
      result = result.filter(eq => 
        eq.reference?.toLowerCase().includes(term) ||
        eq.numero_serie?.toLowerCase().includes(term) ||
        eq.marque?.toLowerCase().includes(term) ||
        eq.modele?.toLowerCase().includes(term) ||
        eq.code_inventaire?.toLowerCase().includes(term)
      )
    }

    return result
  })

  const equipementsParStatut = computed(() => {
    const stats = {}
    equipements.value.forEach(eq => {
      const statut = eq.statut_global || 'inconnu'
      stats[statut] = (stats[statut] || 0) + 1
    })
    return stats
  })

  const equipementsDisponibles = computed(() => 
    equipements.value.filter(eq => 
      ['en_stock_general', 'en_stock_local'].includes(eq.statut_global) &&
      eq.etat === 'en_service'
    )
  )

  const getEquipementById = computed(() => 
    (id) => equipements.value.find(eq => eq.id === id)
  )

  // Actions

  /**
   * Charger les équipements avec filtres et pagination
   */
  async function fetchEquipements(params = {}) {
    loading.value = true
    error.value = null
    
    try {
      const queryParams = {
        ...filters.value,
        ...params,
        page: params.page || pagination.value.current_page
      }

      // Nettoyer les paramètres vides
      Object.keys(queryParams).forEach(key => {
        if (queryParams[key] === null || queryParams[key] === '' || queryParams[key] === false) {
          delete queryParams[key]
        }
      })

      const response = await equipementApi.index(queryParams)
      
      if (response.data.success) {
        equipements.value = response.data.data.data || response.data.data
        
        // Mettre à jour la pagination
        if (response.data.data.current_page) {
          pagination.value = {
            current_page: response.data.data.current_page,
            last_page: response.data.data.last_page,
            per_page: response.data.data.per_page,
            total: response.data.data.total
          }
        }
      } else {
        throw new Error(response.data.message || 'Erreur lors du chargement des équipements')
      }
    } catch (err) {
      error.value = err.response?.data?.message || err.message || 'Erreur réseau'
      console.error('Erreur fetchEquipements:', err)
    } finally {
      loading.value = false
    }
  }

  /**
   * Charger un équipement spécifique avec ses détails complets
   */
  async function fetchEquipement(id) {
    loading.value = true
    error.value = null
    
    try {
      const response = await equipementApi.show(id)
      
      if (response.data.success) {
        currentEquipement.value = response.data.data.equipement
        return currentEquipement.value
      } else {
        throw new Error(response.data.message)
      }
    } catch (err) {
      error.value = err.response?.data?.message || err.message
      console.error('Erreur fetchEquipement:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Créer un nouvel équipement
   */
  async function createEquipement(data) {
    loading.value = true
    error.value = null
    
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
      } else {
        throw new Error(response.data.message)
      }
    } catch (err) {
      importState.value.error = err.response?.data?.message || err.message
      console.error('Erreur importEquipements:', err)
      throw err
    } finally {
      importState.value.uploading = false
    }
  }

  /**
   * Télécharger le template d'import
   */
  async function downloadImportTemplate() {
    try {
      const response = await equipementApi.downloadTemplate()
      
      // Créer un lien de téléchargement
      const url = window.URL.createObjectURL(new Blob([response.data]))
      const link = document.createElement('a')
      link.href = url
      link.setAttribute('download', 'template_import_equipements.csv')
      document.body.appendChild(link)
      link.click()
      link.remove()
      window.URL.revokeObjectURL(url)
      
      return true
    } catch (err) {
      error.value = err.response?.data?.message || err.message
      console.error('Erreur downloadImportTemplate:', err)
      throw err
    }
  }

  /**
   * Rechercher par QR code
   */
  async function searchByQR(qrData) {
    scanState.value.scanning = true
    scanState.value.error = null
    
    try {
      const response = await equipementApi.searchByQR(qrData)
      
      if (response.data.success) {
        scanState.value.result = response.data.data
        return response.data.data
      } else {
        throw new Error(response.data.message)
      }
    } catch (err) {
      scanState.value.error = err.response?.data?.message || err.message
      console.error('Erreur searchByQR:', err)
      throw err
    } finally {
      scanState.value.scanning = false
    }
  }

  /**
   * Mettre à jour les filtres
   */
  function updateFilters(newFilters) {
    filters.value = { ...filters.value, ...newFilters }
  }

  /**
   * Réinitialiser les filtres
   */
  function resetFilters() {
    filters.value = {
      search: '',
      agence_id: null,
      statut_global: '',
      etat: '',
      categorie_id: null,
      garantie_expire_bientot: false,
      disponibles_transfert: false,
      sort_by: 'reference',
      sort_order: 'asc'
    }
  }

  /**
   * Changer de page
   */
  function changePage(page) {
    pagination.value.current_page = page
    fetchEquipements({ page })
  }

  /**
   * Réinitialiser le store
   */
  function resetStore() {
    equipements.value = []
    currentEquipement.value = null
    error.value = null
    resetFilters()
    pagination.value = {
      current_page: 1,
      last_page: 1,
      per_page: 15,
      total: 0
    }
    importState.value = {
      uploading: false,
      previewing: false,
      preview: null,
      error: null
    }
    scanState.value = {
      scanning: false,
      result: null,
      error: null
    }
  }

  /**
   * Vider les erreurs
   */
  function clearError() {
    error.value = null
    importState.value.error = null
    scanState.value.error = null
  }

  return {
    // État
    equipements,
    currentEquipement,
    loading,
    error,
    pagination,
    filters,
    importState,
    scanState,

    // Getters
    filteredEquipements,
    equipementsParStatut,
    equipementsDisponibles,
    getEquipementById,

    // Actions
    fetchEquipements,
    fetchEquipement,
    createEquipement,
    updateEquipement,
    deleteEquipement,
    searchEquipements,
    generateQRCode,
    previewImport,
    importEquipements,
    downloadImportTemplate,
    searchByQR,
    updateFilters,
    resetFilters,
    changePage,
    resetStore,
    clearError
  }
})