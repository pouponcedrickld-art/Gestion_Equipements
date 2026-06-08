import api from './axiosConfig.js'

export default {
    // CRUD de base
    index: (params = {}) => api.get('/categories', { params }),
    show: (id) => api.get(`/categories/${id}`),
    store: (data) => api.post('/categories', data),
    update: (id, data) => api.put(`/categories/${id}`, data),
    destroy: (id) => api.delete(`/categories/${id}`),

    // Liste simple pour les dropdowns
    list: () => api.get('/categories/list'),

    // Recherche
    search: (term) => api.get('/categories', { 
        params: { search: term } 
    }),

    // Catégories avec équipements seulement
    withEquipements: () => api.get('/categories', { 
        params: { with_equipements_only: true } 
    })
}
