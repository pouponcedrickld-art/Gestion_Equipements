import api from './axiosConfig'

export default {
<<<<<<< HEAD
    // CRUD de base
    index: (params = {}) => api.get('/transferts', { params }),
    show: (id) => api.get(`/transferts/${id}`),
    store: (data) => api.post('/transferts', data),
    update: (id, data) => api.put(`/transferts/${id}`, data),
    destroy: (id) => api.delete(`/transferts/${id}`),

    // Workflow transfert
    approuver: (id) => api.post(`/transferts/${id}/approuver`),
    refuser: (id, data) => api.post(`/transferts/${id}/refuser`, data),
    expedier: (id) => api.post(`/transferts/${id}/expedier`),
    recevoir: (id) => api.post(`/transferts/${id}/recevoir`),

    // Filtres par statut
    getByStatut: (statut, params = {}) => 
        api.get('/transferts', { params: { statut, ...params } }),
    
    getEnAttente: (params = {}) => 
        api.get('/transferts', { params: { statut: 'demande', ...params } }),
    
    getEnTransit: (params = {}) => 
        api.get('/transferts', { params: { statut: 'expedie', ...params } }),

    // Filtres par direction (pour une agence)
    getEntrants: (params = {}) => 
        api.get('/transferts', { params: { direction: 'entrants', ...params } }),
    
    getSortants: (params = {}) => 
        api.get('/transferts', { params: { direction: 'sortants', ...params } }),

    // Filtres par type
    getByType: (type, params = {}) => 
        api.get('/transferts', { params: { type_transfert: type, ...params } }),

    // Filtres par agence
    getByAgenceSource: (agenceId, params = {}) => 
        api.get('/transferts', { params: { agence_source_id: agenceId, ...params } }),
    
    getByAgenceDestination: (agenceId, params = {}) => 
        api.get('/transferts', { params: { agence_destination_id: agenceId, ...params } }),

    // Filtres par dates
    getByPeriode: (dateFrom, dateTo, params = {}) => 
        api.get('/transferts', { params: { date_from: dateFrom, date_to: dateTo, ...params } }),

    // Utilitaires
    getStatistiques: () => api.get('/transferts/statistiques'),
    getOptions: () => api.get('/transferts/options')
=======
  getKanban() {
    return api.get('/transferts-kanban')
  },
  updateStatus(id, newStatus) {
    return api.post('/transferts-kanban/update-status', { id, newStatus })
  }
>>>>>>> 2c965af4f2361eccbef055db409105b763f2340d
}
