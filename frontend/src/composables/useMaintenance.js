/**
 * Composable pour la gestion des maintenances
 * Encapsule la logique d'interaction avec le store Pinia
 */
import { computed } from 'vue'
import { useMaintenanceStore } from '@/stores/maintenanceStore'
import { getMonthBounds } from '@/utils/dateFormatter'

export function useMaintenance() {
  const store = useMaintenanceStore()

  // États exposés (computed pour réactivité)
  const maintenances = computed(() => store.maintenances)
  const loading = computed(() => store.loading)
  const error = computed(() => store.error)
  const selectedMaintenance = computed(() => store.selectedMaintenance)

  /**
   * Charge les maintenances pour un mois donné
   * @param {Date|string} month - Mois à charger
   * @returns {Promise<Array>}
   */
  async function loadMaintenances(month) {
    try {
      const date = typeof month === 'string' ? new Date(month) : month
      const { start, end } = getMonthBounds(date)
      
      // Format ISO pour l'API: YYYY-MM-DD
      const startDate = start.toISOString().split('T')[0]
      const endDate = end.toISOString().split('T')[0]
      
      await store.fetchMaintenancesByPeriod(startDate, endDate)
      return store.maintenances
    } catch (err) {
      console.error('Error loading maintenances:', err)
      throw new Error('Impossible de charger les maintenances. Veuillez réessayer.')
    }
  }

  /**
   * Charge les détails complets d'une maintenance
   * @param {number} id - ID de la maintenance
   * @returns {Promise<Object>}
   */
  async function loadMaintenanceDetails(id) {
    try {
      await store.fetchMaintenanceById(id)
      return store.selectedMaintenance
    } catch (err) {
      console.error('Error loading maintenance details:', err)
      throw new Error('Impossible de charger les détails de la maintenance.')
    }
  }

  /**
   * Crée une nouvelle maintenance
   * @param {Object} data - Données de la maintenance
   * @returns {Promise<Object>}
   */
  async function createMaintenance(data) {
    try {
      // Validation basique
      if (!data.equipement_id) {
        throw new Error('L\'équipement est requis')
      }
      if (!data.date_prevue) {
        throw new Error('La date prévue est requise')
      }
      if (!data.responsable) {
        throw new Error('Le responsable est requis')
      }

      const maintenance = await store.createMaintenance(data)
      return maintenance
    } catch (err) {
      console.error('Error creating maintenance:', err)
      
      // Messages d'erreur utilisateur
      if (err.response?.status === 422) {
        const errors = err.response.data.errors
        const firstError = Object.values(errors)[0][0]
        throw new Error(firstError || 'Données invalides')
      }
      
      throw new Error(err.message || 'Impossible de créer la maintenance.')
    }
  }

  /**
   * Obtient les maintenances pour un jour spécifique
   * @param {Date|string} date - Date à filtrer
   * @returns {Array}
   */
  function getMaintenancesForDay(date) {
    const targetDate = typeof date === 'string' ? new Date(date) : date
    
    return store.maintenances.filter(maintenance => {
      const maintenanceDate = new Date(maintenance.date_prevue)
      return (
        maintenanceDate.getDate() === targetDate.getDate() &&
        maintenanceDate.getMonth() === targetDate.getMonth() &&
        maintenanceDate.getFullYear() === targetDate.getFullYear()
      )
    })
  }

  /**
   * Filtre les maintenances par type
   * @param {string} type - Type de maintenance ('preventive', 'corrective', ou 'all')
   * @returns {Array}
   */
  function filterByType(type) {
    if (!type || type === 'all') {
      return store.maintenances
    }
    
    return store.maintenances.filter(m => m.type_maintenance === type)
  }

  /**
   * Met à jour une maintenance
   * @param {number} id - ID de la maintenance
   * @param {Object} data - Données à mettre à jour
   * @returns {Promise<Object>}
   */
  async function updateMaintenance(id, data) {
    try {
      const maintenance = await store.updateMaintenance(id, data)
      return maintenance
    } catch (err) {
      console.error('Error updating maintenance:', err)
      throw new Error(err.message || 'Impossible de mettre à jour la maintenance.')
    }
  }

  /**
   * Supprime une maintenance
   * @param {number} id - ID de la maintenance
   * @returns {Promise<void>}
   */
  async function deleteMaintenance(id) {
    try {
      await store.deleteMaintenance(id)
    } catch (err) {
      console.error('Error deleting maintenance:', err)
      throw new Error(err.message || 'Impossible de supprimer la maintenance.')
    }
  }

  /**
   * Démarre une maintenance
   * @param {number} id - ID de la maintenance
   * @param {Object} data - Données additionnelles (technicien_id, diagnostic)
   * @returns {Promise<Object>}
   */
  async function startMaintenance(id, data = {}) {
    try {
      const maintenance = await store.startMaintenance(id, data)
      return maintenance
    } catch (err) {
      console.error('Error starting maintenance:', err)
      throw new Error(err.message || 'Impossible de démarrer la maintenance.')
    }
  }

  /**
   * Termine une maintenance
   * @param {number} id - ID de la maintenance
   * @param {Object} data - Données additionnelles (diagnostic, cout, observations, action_realisee)
   * @returns {Promise<Object>}
   */
  async function completeMaintenance(id, data = {}) {
    try {
      const maintenance = await store.completeMaintenance(id, data)
      return maintenance
    } catch (err) {
      console.error('Error completing maintenance:', err)
      throw new Error(err.message || 'Impossible de terminer la maintenance.')
    }
  }

  /**
   * Filtre les maintenances par statut
   * @param {string} statut - Statut ('planifiee', 'en_cours', 'terminee', ou 'all')
   * @returns {Array}
   */
  function filterByStatus(statut) {
    if (!statut || statut === 'all') {
      return store.maintenances
    }
    
    return store.maintenances.filter(m => m.statut === statut)
  }

  /**
   * Filtre les maintenances par type ET statut
   * @param {string} type - Type de maintenance
   * @param {string} statut - Statut
   * @returns {Array}
   */
  function filterByTypeAndStatus(type, statut) {
    let filtered = store.maintenances

    if (type && type !== 'all') {
      filtered = filtered.filter(m => m.type_maintenance === type)
    }

    if (statut && statut !== 'all') {
      filtered = filtered.filter(m => m.statut === statut)
    }

    return filtered
  }

  /**
   * Réinitialise l'erreur
   */
  function clearError() {
    store.resetError()
  }

  /**
   * Vide le cache du store
   */
  function clearCache() {
    store.clearCache()
  }

  /**
   * Obtient les statistiques des maintenances actuelles
   * @returns {Object} { total, planifiee, en_cours, terminee, preventive, corrective }
   */
  function getStatistics() {
    const stats = {
      total: store.maintenances.length,
      planifiee: 0,
      en_cours: 0,
      terminee: 0,
      preventive: 0,
      corrective: 0
    }

    store.maintenances.forEach(maintenance => {
      // Par statut
      if (stats.hasOwnProperty(maintenance.statut)) {
        stats[maintenance.statut]++
      }
      
      // Par type
      if (stats.hasOwnProperty(maintenance.type_maintenance)) {
        stats[maintenance.type_maintenance]++
      }
    })

    return stats
  }

  /**
   * Vérifie si les données sont en cache pour une période
   * @param {string} startDate - Date de début (YYYY-MM-DD)
   * @param {string} endDate - Date de fin (YYYY-MM-DD)
   * @returns {boolean}
   */
  function isCached(startDate, endDate) {
    const cacheKey = `${startDate}_${endDate}`
    const cached = store.cache[cacheKey]
    
    if (!cached) return false
    
    const now = Date.now()
    const isExpired = now - cached.timestamp > store.CACHE_TTL
    
    return !isExpired
  }

  return {
    // États
    maintenances,
    loading,
    error,
    selectedMaintenance,
    
    // Actions
    loadMaintenances,
    loadMaintenanceDetails,
    createMaintenance,
    updateMaintenance,
    deleteMaintenance,
    startMaintenance,
    completeMaintenance,
    
    // Utilitaires de filtrage
    getMaintenancesForDay,
    filterByType,
    filterByStatus,
    filterByTypeAndStatus,
    
    // Gestion du cache et erreurs
    clearError,
    clearCache,
    isCached,
    
    // Statistiques
    getStatistics
  }
}
