import api from './axiosConfig.js'

export default {
    index: () => api.get('/demandes-materiel'),
    show: (id) => api.get(`/demandes-materiel/${id}`),
    store: (data) => api.post('/demandes-materiel', data),
    update: (id, data) => api.put(`/demandes-materiel/${id}`, data),
    destroy: (id) => api.delete(`/demandes-materiel/${id}`),
    traiter: (id, data) => api.post(`/demandes-materiel/${id}/traiter`, data)
}
