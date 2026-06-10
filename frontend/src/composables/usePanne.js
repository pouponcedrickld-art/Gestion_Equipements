import { computed } from 'vue'
import { usePanneStore } from '@/stores/panneStore'

export function usePanne() {
  const store = usePanneStore()

  const pannes = computed(() => store.pannes)
  const loading = computed(() => store.loading)
  const error = computed(() => store.error)

  async function loadPannes(filters = {}) {
    await store.fetchPannes(filters)
    return store.pannes
  }

  async function createPanne(data) {
    return await store.createPanne(data)
  }

  async function updatePanne(id, data) {
    return await store.updatePanne(id, data)
  }

  async function deletePanne(id) {
    await store.deletePanne(id)
  }

  async function transmettreMaintenance(id) {
    await store.transmettreMaintenance(id, {})
  }

  async function diagnostiquer(id, data) {
    await store.diagnostiquer(id, data)
  }

  function resetError() {
    store.error = null
  }

  return {
    pannes,
    loading,
    error,
    loadPannes,
    createPanne,
    updatePanne,
    deletePanne,
    transmettreMaintenance,
    diagnostiquer,
    resetError
  }
}
