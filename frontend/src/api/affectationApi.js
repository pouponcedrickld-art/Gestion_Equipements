import api from './axiosConfig.js'

export default {
    index: (params) => api.get('/affectations', { params }),
    store: (data) => api.post('/affectations', data),
    retour: (id, data) => api.post(`/affectations/${id}/retour`, data),
    getAgents: () => api.get('/agents'),
    getEquipementsDisponibles: () => api.get('/equipements', { params: { statut_global: 'en_stock_agence', all_equipements: true } })
}