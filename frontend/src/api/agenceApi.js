import api from './axiosConfig.js'

export default {
    index: () => api.get('/agences'),
    show: (id) => api.get(`/agences/${id}`),
    store: (data) => api.post('/agences', data),
    update: (id, data) => api.put(`/agences/${id}`, data),
    destroy: (id) => api.delete(`/agences/${id}`),
    stats: (id) => api.get(`/agences/${id}/stats`)
}
