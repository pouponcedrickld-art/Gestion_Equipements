import api from './axiosConfig.js'

export default {
    index: (params = {}) => api.get('/pertes', { params }),
    show: (id) => api.get(`/pertes/${id}`),
    store: (data) => api.post('/pertes', data),
    update: (id, data) => api.put(`/pertes/${id}`, data),
    destroy: (id) => api.delete(`/pertes/${id}`)
}
