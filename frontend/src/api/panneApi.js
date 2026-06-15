import api from './axiosConfig.js'

export default {
    index: (filters = {}) => api.get('/pannes', { params: filters }),
    show: (id) => api.get(`/pannes/${id}`),
    store: (data) => api.post('/pannes', data),
    update: (id, data) => api.put(`/pannes/${id}`, data),
    destroy: (id) => api.delete(`/pannes/${id}`),
    transmettreMaintenance: (id, data) => api.post(`/pannes/${id}/transmettre-maintenance`, data),
    diagnostiquer: (id, data) => api.post(`/pannes/${id}/diagnostiquer`, data),
    decider: (id, data) => api.post(`/pannes/${id}/decider`, data),
    updateResultat: (id, data) => api.post(`/pannes/${id}/update-resultat`, data),
    cloturer: (id, data) => api.post(`/pannes/${id}/cloturer`, data)
}
