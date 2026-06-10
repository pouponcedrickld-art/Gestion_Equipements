import api from './axiosConfig.js'

export default {
    // CRUD de base
    index: (params = {}) => api.get('/consommables', { params }),
    show: (id) => api.get(`/consommables/${id}`),
    store: (data) => api.post('/consommables', data),
    update: (id, data) => api.put(`/consommables/${id}`, data),
    destroy: (id) => api.delete(`/consommables/${id}`),

    // Gestion du stock
    ajusterStock: (id, data) => api.post(`/consommables/${id}/ajuster-stock`, data),

    // Filtres spécialisés
    getByType: (type, params = {}) => 
        api.get('/consommables', { params: { type, ...params } }),
    
    getByEquipement: (equipementId, params = {}) => 
        api.get('/consommables', { params: { equipement_id: equipementId, ...params } }),
    
    getStockFaible: (seuil = 1, params = {}) => 
        api.get('/consommables', { params: { stock_faible_only: true, seuil_stock: seuil, ...params } }),

    // Utilitaires
    getTypes: () => api.get('/consommables-types'),
    getStatistiques: () => api.get('/consommables/statistiques'),

    // Recherche
    search: (term, params = {}) => 
        api.get('/consommables', { params: { search: term, ...params } })
}
