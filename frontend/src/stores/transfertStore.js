import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import transfertApi from '@/api/transfertApi'
import { useAuthStore } from '@/stores/authStore'

export const useTransfertStore = defineStore('transfert', () => {
  // États réactifs
  const transferts = ref([])
  const currentTransfert = ref(null)
  const loading = ref(false)
  const error = ref(null)
  const statistiques = ref(null)
  const options = ref({
    statuts: {},
    types: {}
  })
  
  // État de pagination
  const pagination = ref({
    current_page: 1,
    last_page: 1,
    per_page: 15,
    total: 0
  })

  // Filtres actifs
  const filters = ref({
    statut: '',
    type_transfert: '',
    agence_source_id: null,
    agence_destination_id: null,
    direction: '', // 'entrants', 'sortants'
    date_from: null,
    date_to: null,
    sort_by: 'date_demande',
    sort_order: 'desc'
  })

  // Getters calculés
  const transfertsEnAttente = computed(() => 
    transferts.value.filter(t => t.statut === 'demande')
  )

  const transfertsEnTransit = computed(() => 
    transferts.value.filter(t => t.statut === 'expedie')
  )

  const transfertsTermines = computed(() => 
    transferts.value.filter(t => ['recu', 'refuse'].includes(t.statut))
  )

  const transfertsParStatut = computed(() => {
    const stats = {}
    transferts.value.forEach(t => {
      const statut = t.statut || 'inconnu'
      stats[statut] = (stats[statut] || 0) + 1
    })
    return stats
  })

  const transfertsParType = computed(() => {
    const stats = {}
    transferts.value.forEach(t => {
      const type = t.type_transfert || 'inconnu'
      stats[type] = (stats[type] || 0) + 1
    })
    return stats
  })

  const getTransfertById = computed(() => 
    (id) => transferts.value.find(t => t.id === id)
  )

  // Actions

  /**
   * Charger les transferts avec filtres et pagination
   */
  async function fetchTransferts(params = {}) {
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

      const response = await transfertApi.index(queryParams)
      
      if (response.data.success) {
        transferts.value = response.data.data.data || response.data.data
        
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
        throw new Error(response.data.message || 'Erreur lors du chargement des transferts')
      }
    } catch (err) {
      error.value = err.response?.data?.message || err.message || 'Erreur réseau'
      console.error('Erreur fetchTransferts:', err)
    } finally {
      loading.value = false
    }
  }

  /**
   * Charger un transfert spécifique
   */
  async function fetchTransfert(id) {
    loading.value = true
    error.value = null
    
    try {
      const response = await transfertApi.show(id)
      
      if (response.data.success) {
        currentTransfert.value = response.data.data
        return currentTransfert.value
      } else {
        throw new Error(response.data.message)
      }
    } catch (err) {
      error.value = err.response?.data?.message || err.message
      console.error('Erreur fetchTransfert:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Créer une nouvelle demande de transfert
   */
  async function createTransfert(data) {
    loading.value = true
    error.value = null
    
    try {
      const response = await transfertApi.store(data)
      
      if (response.data.success) {
        const newTransfert = response.data.data
        
        // Ajouter à la liste locale
        transferts.value.unshift(newTransfert)
        
        return newTransfert
      } else {
        throw new Error(response.data.message)
      }
    } catch (err) {
      error.value = err.response?.data?.message || err.message
      console.error('Erreur createTransfert:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Approuver un transfert
   */
  async function approuverTransfert(id) {
    loading.value = true
    error.value = null
    
    try {
      const response = await transfertApi.approuver(id)
      
      if (response.data.success) {
        const updatedTransfert = response.data.data
        
        // Mettre à jour dans la liste locale
        const index = transferts.value.findIndex(t => t.id === id)
        if (index !== -1) {
          transferts.value[index] = updatedTransfert
        }
        
        if (currentTransfert.value && currentTransfert.value.id === id) {
          currentTransfert.value = updatedTransfert
        }
        
        return updatedTransfert
      } else {
        throw new Error(response.data.message)
      }
    } catch (err) {
      error.value = err.response?.data?.message || err.message
      console.error('Erreur approuverTransfert:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Refuser un transfert
   */
  async function refuserTransfert(id, observations) {
    loading.value = true
    error.value = null
    
    try {
      const response = await transfertApi.refuser(id, { observations })
      
      if (response.data.success) {
        const updatedTransfert = response.data.data
        
        // Mettre à jour dans la liste locale
        const index = transferts.value.findIndex(t => t.id === id)
        if (index !== -1) {
          transferts.value[index] = updatedTransfert
        }
        
        if (currentTransfert.value && currentTransfert.value.id === id) {
          currentTransfert.value = updatedTransfert
        }
        
        return updatedTransfert
      } else {
        throw new Error(response.data.message)
      }
    } catch (err) {
      error.value = err.response?.data?.message || err.message
      console.error('Erreur refuserTransfert:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Expédier un transfert
   */
  async function expedierTransfert(id) {
    loading.value = true
    error.value = null
    
    try {
      const response = await transfertApi.expedier(id)
      
      if (response.data.success) {
        const updatedTransfert = response.data.data
        
        // Mettre à jour dans la liste locale
        const index = transferts.value.findIndex(t => t.id === id)
        if (index !== -1) {
          transferts.value[index] = updatedTransfert
        }
        
        if (currentTransfert.value && currentTransfert.value.id === id) {
          currentTransfert.value = updatedTransfert
        }
        
        return updatedTransfert
      } else {
        throw new Error(response.data.message)
      }
    } catch (err) {
      error.value = err.response?.data?.message || err.message
      console.error('Erreur expedierTransfert:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Recevoir un transfert
   */
  async function recevoirTransfert(id) {
    loading.value = true
    error.value = null
    
    try {
      const response = await transfertApi.recevoir(id)
      
      if (response.data.success) {
        const updatedTransfert = response.data.data
        
        // Mettre à jour dans la liste locale
        const index = transferts.value.findIndex(t => t.id === id)
        if (index !== -1) {
          transferts.value[index] = updatedTransfert
        }
        
        if (currentTransfert.value && currentTransfert.value.id === id) {
          currentTransfert.value = updatedTransfert
        }
        
        return updatedTransfert
      } else {
        throw new Error(response.data.message)
      }
    } catch (err) {
      error.value = err.response?.data?.message || err.message
      console.error('Erreur recevoirTransfert:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Charger les transferts par statut
   */
  async function fetchByStatut(statut) {
    return fetchTransferts({ statut })
  }

  /**
   * Charger les transferts entrants (pour l'agence courante)
   */
  async function fetchTransfertsEntrants() {
    return fetchTransferts({ direction: 'entrants' })
  }

  /**
   * Charger les transferts sortants (de l'agence courante)
   */
  async function fetchTransfertsSortants() {
    return fetchTransferts({ direction: 'sortants' })
  }

  /**
   * Charger les transferts par période
   */
  async function fetchByPeriode(dateFrom, dateTo) {
    return fetchTransferts({ date_from: dateFrom, date_to: dateTo })
  }

  /**
   * Charger les statistiques des transferts
   */
  async function fetchStatistiques() {
    try {
      const response = await transfertApi.getStatistiques()
      
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
   * Charger les options (statuts et types disponibles)
   */
  async function fetchOptions() {
    try {
      const response = await transfertApi.getOptions()
      
      if (response.data.success) {
        options.value = response.data.data
        return options.value
      } else {
        throw new Error(response.data.message)
      }
    } catch (err) {
      error.value = err.response?.data?.message || err.message
      console.error('Erreur fetchOptions:', err)
    }
  }

  /**
   * Vérifier si un transfert peut être modifié par l'utilisateur courant
   */
  function canModifyTransfert(transfert) {
    const authStore = useAuthStore()
    
    // Super admin peut tout faire
    if (authStore.isSuperAdmin) return true
    
    // Gestionnaire général peut approuver/refuser/expédier
    if (authStore.isGestionnaireGeneral) {
      return ['demande', 'approuve'].includes(transfert.statut)
    }
    
    // Gestionnaire stock peut recevoir les transferts pour son agence
    if (authStore.isGestionnaireStock) {
      return transfert.statut === 'expedie' && 
             transfert.agence_destination_id === authStore.userAgence
    }
    
    // Chef d'agence peut créer des demandes
    if (authStore.isChefAgence) {
      return transfert.statut === 'demande' && 
             transfert.demande_par_id === authStore.user.id
    }
    
    return false
  }

  /**
   * Obtenir les actions possibles pour un transfert
   */
  function getAvailableActions(transfert) {
    const authStore = useAuthStore()
    const actions = []
    
    if (!transfert || !authStore.user) return actions
    
    // Actions selon le statut et le rôle
    if (transfert.statut === 'demande') {
      if (authStore.isGestionnaireGeneral || authStore.isSuperAdmin) {
        actions.push('approuver', 'refuser')
      }
    }
    
    if (transfert.statut === 'approuve') {
      if (authStore.isGestionnaireGeneral || authStore.isSuperAdmin) {
        actions.push('expedier')
      }
    }
    
    if (transfert.statut === 'expedie') {
      if ((authStore.isGestionnaireStock && transfert.agence_destination_id === authStore.userAgence) ||
          authStore.isSuperAdmin) {
        actions.push('recevoir')
      }
    }
    
    return actions
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
      statut: '',
      type_transfert: '',
      agence_source_id: null,
      agence_destination_id: null,
      direction: '',
      date_from: null,
      date_to: null,
      sort_by: 'date_demande',
      sort_order: 'desc'
    }
  }

  /**
   * Changer de page
   */
  function changePage(page) {
    pagination.value.current_page = page
    fetchTransferts({ page })
  }

  /**
   * Réinitialiser le store
   */
  function resetStore() {
    transferts.value = []
    currentTransfert.value = null
    error.value = null
    statistiques.value = null
    options.value = { statuts: {}, types: {} }
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
    transferts,
    currentTransfert,
    loading,
    error,
    statistiques,
    options,
    pagination,
    filters,

    // Getters
    transfertsEnAttente,
    transfertsEnTransit,
    transfertsTermines,
    transfertsParStatut,
    transfertsParType,
    getTransfertById,

    // Actions
    fetchTransferts,
    fetchTransfert,
    createTransfert,
    approuverTransfert,
    refuserTransfert,
    expedierTransfert,
    recevoirTransfert,
    fetchByStatut,
    fetchTransfertsEntrants,
    fetchTransfertsSortants,
    fetchByPeriode,
    fetchStatistiques,
    fetchOptions,
    canModifyTransfert,
    getAvailableActions,
    updateFilters,
    resetFilters,
    changePage,
    resetStore,
    clearError
  }
})