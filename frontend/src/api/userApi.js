import api from './axiosConfig.js'

export default {
    index: () => api.get('/users'),
    show: (id) => api.get(`/users/${id}`),
    store: (data) => api.post('/users', data),
    update: (id, data) => api.put(`/users/${id}`, data),
    destroy: (id) => api.delete(`/users/${id}`),
    toggleActif: (id) => api.post(`/users/${id}/toggle-actif`)
}
