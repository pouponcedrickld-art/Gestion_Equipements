import api from './axiosConfig.js'

export default {
    index: () => api.get('/transferts'),
    show: (id) => api.get(`/transferts/${id}`),
    store: (data) => api.post('/transferts', data),
    update: (id, data) => api.put(`/transferts/${id}`, data),
    destroy: (id) => api.delete(`/transferts/${id}`),
    approuver: (id) => api.post(`/transferts/${id}/approuver`),
    expedier: (id) => api.post(`/transferts/${id}/expedier`),
    recevoir: (id) => api.post(`/transferts/${id}/recevoir`)
}
