import api from './axiosConfig.js'

export default {
    index: () => api.get('/pannes'),
    show: (id) => api.get(`/pannes/${id}`),
    store: (data) => api.post('/pannes', data),
    update: (id, data) => api.put(`/pannes/${id}`, data),
    destroy: (id) => api.delete(`/pannes/${id}`),
    transmettreMaintenance: (id) => api.post(`/pannes/${id}/transmettre-maintenance`),
    diagnostiquer: (id, data) => api.post(`/pannes/${id}/diagnostiquer`, data)
}
