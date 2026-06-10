import api from './axiosConfig.js'

export default {
    index: () => api.get('/affectations'),
    show: (id) => api.get(`/affectations/${id}`),
    store: (data) => api.post('/affectations', data),
    update: (id, data) => api.put(`/affectations/${id}`, data),
    destroy: (id) => api.delete(`/affectations/${id}`),
    retour: (id) => api.post(`/affectations/${id}/retour`)
}
