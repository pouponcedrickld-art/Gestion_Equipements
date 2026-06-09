import { computed } from 'vue'
import { useMaintenanceStore } from '@/stores/maintenanceStore.js'
import { getMonthBounds } from '@/utils/dateFormatter.js'

export function useMaintenance() {
  const store = useMaintenanceStore()

  const maintenances = computed(() => store.maintenances)
  const loading = computed(() => store.loading)
  const error = computed(() => store.error)

  async function loadMaintenances(month, filters = {}) {
    const bounds = getMonthBounds(month)
    await store.fetchMaintenancesByPeriod(bounds.start, bounds.end, filters)
  }

  async function loadMaintenanceDetails(id) {
    await store.fetchMaintenanceById(id)
  }

  async function createMaintenance(data) {
    return await store.createMaintenance(data)
  }

  function getMaintenancesForDay(date) {
    const dateStr = new Date(date).toISOString().split('T')[0]
    return store.maintenancesByDate[dateStr] || []
  }

  function filterByType(type) {
    return store.maintenancesByType[type] || []
  }

  function filterByStatus(status) {
    return store.maintenancesByStatus[status] || []
  }

  return {
    maintenances,
    loading,
    error,
    loadMaintenances,
    loadMaintenanceDetails,
    createMaintenance,
    getMaintenancesForDay,
    filterByType,
    filterByStatus
  }
}
