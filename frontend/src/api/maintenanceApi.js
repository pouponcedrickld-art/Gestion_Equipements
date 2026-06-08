import api from './axiosConfig.js'

export default {
    index: () => api.get('/maintenances'),
    show: (id) => api.get(`/maintenances/${id}`),
    store: (data) => api.post('/maintenances', data),
    update: (id, data) => api.put(`/maintenances/${id}`, data),
    destroy: (id) => api.delete(`/maintenances/${id}`)
}
