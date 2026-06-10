import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import categorieApi from '@/api/categorieApi'

export const useCategorieStore = defineStore('categorie', () => {
  // État réactif
  const categories = ref([])
  const categoriesList = ref([]) // Liste simple pour les dropdowns
  const currentCategorie = ref(null)
  const loading = ref(false)
  const error = ref(null)
  const pagination = ref({
    current_page: 1,
    last_page: 1,
    per_page: 15,
    total: 0
  })

  // Cache local pour éviter trop d'appels API
  const lastFetch = ref(null)
  const cacheTimeout = 5 * 60 * 1000 // 5 minutes

  // Getters calculés
  const categoriesOptions = computed(() => 
    categoriesList.value.map(cat => ({
      label: cat.nom,
      value: cat.id
    }))
  )

  const getCategorieById = computed(() => 
    (id) => categories.value.find(cat => cat.id === id)
  )

  const hasCategories = computed(() => categories.value.length > 0)

  // Actions

  /**
   * Charger toutes les catégories avec pagination
   */
  async function fetchCategories(params = {}) {
    loading.value = true
    error.value = null
    
    try {
      const response = await categorieApi.index(params)
      
      if (response.data.success) {
        categories.value = response.data.data.data || response.data.data
        
        // Mettre à jour la pagination si disponible
        if (response.data.data.current_page) {
          pagination.value = {
            current_page: response.data.data.current_page,
            last_page: response.data.data.last_page,
            per_page: response.data.data.per_page,
            total: response.data.data.total
          }
        }
        
        lastFetch.value = Date.now()
      } else {
        throw new Error(response.data.message || 'Erreur lors du chargement des catégories')
      }
    } catch (err) {
      error.value = err.response?.data?.message || err.message || 'Erreur réseau'
      console.error('Erreur fetchCategories:', err)
    } finally {
      loading.value = false
    }
  }

  /**
   * Charger la liste simple des catégories pour les dropdowns
   */
  async function fetchCategoriesList() {
    // Utiliser le cache si encore valide
    if (categoriesList.value.length > 0 && lastFetch.value && 
        (Date.now() - lastFetch.value) < cacheTimeout) {
      return categoriesList.value
    }

    try {
      const response = await categorieApi.list()
      
      if (response.data.success) {
        categoriesList.value = response.data.data
        lastFetch.value = Date.now()
      } else {
        throw new Error(response.data.message)
      }
    } catch (err) {
      error.value = err.response?.data?.message || err.message
      console.error('Erreur fetchCategoriesList:', err)
    }
    
    return categoriesList.value
  }

  /**
   * Charger une catégorie spécifique
   */
  async function fetchCategorie(id) {
    loading.value = true
    error.value = null
    
    try {
      const response = await categorieApi.show(id)
      
      if (response.data.success) {
        currentCategorie.value = response.data.data
        return currentCategorie.value
      } else {
        throw new Error(response.data.message)
      }
    } catch (err) {
      error.value = err.response?.data?.message || err.message
      console.error('Erreur fetchCategorie:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Créer une nouvelle catégorie
   */
  async function createCategorie(data) {
    loading.value = true
    error.value = null
    
    try {
      const response = await categorieApi.store(data)
      
      if (response.data.success) {
        const newCategorie = response.data.data
        
        // Ajouter à la liste locale
        categories.value.unshift(newCategorie)
        categoriesList.value.unshift(newCategorie)
        
        return newCategorie
      } else {
        throw new Error(response.data.message)
      }
    } catch (err) {
      error.value = err.response?.data?.message || err.message
      console.error('Erreur createCategorie:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Mettre à jour une catégorie
   */
  async function updateCategorie(id, data) {
    loading.value = true
    error.value = null
    
    try {
      const response = await categorieApi.update(id, data)
      
      if (response.data.success) {
        const updatedCategorie = response.data.data
        
        // Mettre à jour dans la liste locale
        const index = categories.value.findIndex(cat => cat.id === id)
        if (index !== -1) {
          categories.value[index] = updatedCategorie
        }
        
        const listIndex = categoriesList.value.findIndex(cat => cat.id === id)
        if (listIndex !== -1) {
          categoriesList.value[listIndex] = updatedCategorie
        }
        
        if (currentCategorie.value && currentCategorie.value.id === id) {
          currentCategorie.value = updatedCategorie
        }
        
        return updatedCategorie
      } else {
        throw new Error(response.data.message)
      }
    } catch (err) {
      error.value = err.response?.data?.message || err.message
      console.error('Erreur updateCategorie:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Supprimer une catégorie
   */
  async function deleteCategorie(id) {
    loading.value = true
    error.value = null
    
    try {
      const response = await categorieApi.destroy(id)
      
      if (response.data.success) {
        // Retirer de la liste locale
        categories.value = categories.value.filter(cat => cat.id !== id)
        categoriesList.value = categoriesList.value.filter(cat => cat.id !== id)
        
        if (currentCategorie.value && currentCategorie.value.id === id) {
          currentCategorie.value = null
        }
        
        return true
      } else {
        throw new Error(response.data.message)
      }
    } catch (err) {
      error.value = err.response?.data?.message || err.message
      console.error('Erreur deleteCategorie:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Rechercher des catégories
   */
  async function searchCategories(term) {
    if (!term || term.length < 2) {
      return fetchCategories()
    }
    
    loading.value = true
    error.value = null
    
    try {
      const response = await categorieApi.search(term)
      
      if (response.data.success) {
        categories.value = response.data.data.data || response.data.data
        return categories.value
      } else {
        throw new Error(response.data.message)
      }
    } catch (err) {
      error.value = err.response?.data?.message || err.message
      console.error('Erreur searchCategories:', err)
    } finally {
      loading.value = false
    }
  }

  /**
   * Réinitialiser le store
   */
  function resetStore() {
    categories.value = []
    categoriesList.value = []
    currentCategorie.value = null
    error.value = null
    lastFetch.value = null
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

  /**
   * Invalider le cache
   */
  function invalidateCache() {
    lastFetch.value = null
  }

  return {
    // État
    categories,
    categoriesList,
    currentCategorie,
    loading,
    error,
    pagination,

    // Getters
    categoriesOptions,
    getCategorieById,
    hasCategories,

    // Actions
    fetchCategories,
    fetchCategoriesList,
    fetchCategorie,
    createCategorie,
    updateCategorie,
    deleteCategorie,
    searchCategories,
    resetStore,
    clearError,
    invalidateCache
  }
})