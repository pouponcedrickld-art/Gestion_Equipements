import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import consommableApi from '@/api/consommableApi'

export const useConsommableStore = defineStore('consommable', () => {
  // États réactifs
  const consommables = ref([])
  const currentConsommable = ref(null)
  const loading = ref(false)
  const error = ref(null)
  const types = ref([])
  const statistiques = ref(null)
  
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
    type: '',
    equipement_id: null,
    stock_faible_only: false,
    seuil_stock: 1,
    sort_by: 'nom',
    sort_order: 'asc'
  })

  // Getters calculés
  const consommablesStockFaible = computed(() => 
    consommables.value.filter(c => c.is_stock_faible || c.quantite <= 1)
  )

  const consommablesRupture = computed(() => 
    consommables.value.filter(c => c.quantite === 0)
  )

  const consommablesParType = computed(() => {
    const stats = {}
    consommables.value.forEach(c => {
      const type = c.type || 'autre'
      stats[type] = (stats[type] || 0) + 1
    })
    return stats
  })

  const getConsommableById = computed(() => 
    (id) => consommables.value.find(c => c.id === id)
  )

  const typesOptions = computed(() => 
    Object.entries(types.value || {}).map(([key, label]) => ({
      label,
      value: key
    }))
  )

  // Actions

  /**
   * Charger les consommables avec filtres et pagination
   */
  async function fetchConsommables(params = {}) {
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

      const response = await consommableApi.index(queryParams)
      
      if (response.data.success) {
        consommables.value = response.data.data.data || response.data.data
        
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
        throw new Error(response.data.message || 'Erreur lors du chargement des consommables')
      }
    } catch (err) {
      error.value = err.response?.data?.message || err.message || 'Erreur réseau'
      console.error('Erreur fetchConsommables:', err)
    } finally {
      loading.value = false
    }
  }

  /**
   * Charger un consommable spécifique
   */
  async function fetchConsommable(id) {
    loading.value = true
    error.value = null
    
    try {
      const response = await consommableApi.show(id)
      
      if (response.data.success) {
        currentConsommable.value = response.data.data
        return currentConsommable.value
      } else {
        throw new Error(response.data.message)
      }
    } catch (err) {
      error.value = err.response?.data?.message || err.message
      console.error('Erreur fetchConsommable:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Créer un nouveau consommable
   */
  async function createConsommable(data) {
    loading.value = true
    error.value = null
    
    try {
      const response = await consommableApi.store(data)
      
      if (response.data.success) {
        const newConsommable = response.data.data
        
        // Ajouter à la liste locale
        consommables.value.unshift(newConsommable)
        
        return newConsommable
      } else {
        throw new Error(response.data.message)
      }
    } catch (err) {
      error.value = err.response?.data?.message || err.message
      console.error('Erreur createConsommable:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Mettre à jour un consommable
   */
  async function updateConsommable(id, data) {
    loading.value = true
    error.value = null
    
    try {
      const response = await consommableApi.update(id, data)
      
      if (response.data.success) {
        const updatedConsommable = response.data.data
        
        // Mettre à jour dans la liste locale
        const index = consommables.value.findIndex(c => c.id === id)
        if (index !== -1) {
          consommables.value[index] = updatedConsommable
        }
        
        if (currentConsommable.value && currentConsommable.value.id === id) {
          currentConsommable.value = updatedConsommable
        }
        
        return updatedConsommable
      } else {
        throw new Error(response.data.message)
      }
    } catch (err) {
      error.value = err.response?.data?.message || err.message
      console.error('Erreur updateConsommable:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Supprimer un consommable
   */
  async function deleteConsommable(id) {
    loading.value = true
    error.value = null
    
    try {
      const response = await consommableApi.destroy(id)
      
      if (response.data.success) {
        // Retirer de la liste locale
        consommables.value = consommables.value.filter(c => c.id !== id)
        
        if (currentConsommable.value && currentConsommable.value.id === id) {
          currentConsommable.value = null
        }
        
        return true
      } else {
        throw new Error(response.data.message)
      }
    } catch (err) {
      error.value = err.response?.data?.message || err.message
      console.error('Erreur deleteConsommable:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Ajuster le stock d'un consommable
   */
  async function ajusterStock(id, dataOrAction, quantite = null, description = '') {
    loading.value = true
    error.value = null
    
    try {
      let data;
      if (typeof dataOrAction === 'object') {
        data = dataOrAction;
      } else {
        data = {
          action: dataOrAction, // 'ajouter' ou 'retirer'
          quantite,
          description
        }
      }
      
      const response = await consommableApi.ajusterStock(id, data)
      
      if (response.data.success) {
        const updatedConsommable = response.data.data.consommable
        
        // Mettre à jour dans la liste locale
        const index = consommables.value.findIndex(c => c.id === id)
        if (index !== -1) {
          consommables.value[index] = updatedConsommable
        }
        
        if (currentConsommable.value && currentConsommable.value.id === id) {
          currentConsommable.value = updatedConsommable
        }
        
        return response.data.data
      } else {
        throw new Error(response.data.message)
      }
    } catch (err) {
      error.value = err.response?.data?.message || err.message
      console.error('Erreur ajusterStock:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Charger les types de consommables disponibles
   */
  async function fetchTypes() {
    try {
      const response = await consommableApi.getTypes()
      
      if (response.data.success) {
        types.value = response.data.data
        return types.value
      } else {
        throw new Error(response.data.message)
      }
    } catch (err) {
      error.value = err.response?.data?.message || err.message
      console.error('Erreur fetchTypes:', err)
    }
  }

  /**
   * Charger les statistiques des consommables
   */
  async function fetchStatistiques() {
    try {
      const response = await consommableApi.getStatistiques()
      
      if (response.data.success) {
        statistiques.value = response.data.data
        return statistiques.value
      } else {
        throw new Error(response.data.message)
      }
    } catch (err) {
      error.value = err.response?.data?.message || err.message
      console.error('Erreur fetchStatistiques:', err)
    }
  }

  /**
   * Rechercher des consommables
   */
  async function searchConsommables(term) {
    if (!term || term.length < 2) {
      return fetchConsommables()
    }
    
    loading.value = true
    error.value = null
    
    try {
      const response = await consommableApi.search(term)
      
      if (response.data.success) {
        consommables.value = response.data.data.data || response.data.data
        return consommables.value
      } else {
        throw new Error(response.data.message)
      }
    } catch (err) {
      error.value = err.response?.data?.message || err.message
      console.error('Erreur searchConsommables:', err)
    } finally {
      loading.value = false
    }
  }

  /**
   * Filtrer par type
   */
  async function fetchByType(type) {
    loading.value = true
    error.value = null
    
    try {
      const response = await consommableApi.getByType(type)
      
      if (response.data.success) {
        consommables.value = response.data.data.data || response.data.data
        return consommables.value
      } else {
        throw new Error(response.data.message)
      }
    } catch (err) {
      error.value = err.response?.data?.message || err.message
      console.error('Erreur fetchByType:', err)
    } finally {
      loading.value = false
    }
  }

  /**
   * Filtrer par équipement
   */
  async function fetchByEquipement(equipementId) {
    loading.value = true
    error.value = null
    
    try {
      const response = await consommableApi.getByEquipement(equipementId)
      
      if (response.data.success) {
        consommables.value = response.data.data.data || response.data.data
        return consommables.value
      } else {
        throw new Error(response.data.message)
      }
    } catch (err) {
      error.value = err.response?.data?.message || err.message
      console.error('Erreur fetchByEquipement:', err)
    } finally {
      loading.value = false
    }
  }

  /**
   * Charger les consommables en stock faible
   */
  async function fetchStockFaible(seuil = 1) {
    loading.value = true
    error.value = null
    
    try {
      const response = await consommableApi.getStockFaible(seuil)
      
      if (response.data.success) {
        consommables.value = response.data.data.data || response.data.data
        return consommables.value
      } else {
        throw new Error(response.data.message)
      }
    } catch (err) {
      error.value = err.response?.data?.message || err.message
      console.error('Erreur fetchStockFaible:', err)
    } finally {
      loading.value = false
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
      type: '',
      equipement_id: null,
      stock_faible_only: false,
      seuil_stock: 1,
      sort_by: 'nom',
      sort_order: 'asc'
    }
  }

  /**
   * Changer de page
   */
  function changePage(page) {
    pagination.value.current_page = page
    fetchConsommables({ page })
  }

  /**
   * Réinitialiser le store
   */
  function resetStore() {
    consommables.value = []
    currentConsommable.value = null
    error.value = null
    types.value = []
    statistiques.value = null
    resetFilters()
    pagination.value = {
      current_page: 1,
      last_page: 1,
      per_page: 15,
      total: 0
    }
  }

  /**
   * Vider les erreurs
   */
  function clearError() {
    error.value = null
  }

  return {
    // État
    consommables,
    currentConsommable,
    loading,
    error,
    types,
    statistiques,
    pagination,
    filters,

    // Getters
    consommablesStockFaible,
    consommablesRupture,
    consommablesParType,
    getConsommableById,
    typesOptions,

    // Actions
    fetchConsommables,
    fetchConsommable,
    createConsommable,
    updateConsommable,
    deleteConsommable,
    ajusterStock,
    fetchTypes,
    fetchStatistiques,
    searchConsommables,
    fetchByType,
    fetchByEquipement,
    fetchStockFaible,
    updateFilters,
    resetFilters,
    changePage,
    resetStore,
    clearError
  }
})