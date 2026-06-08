import api from './axiosConfig.js'

export default {
    index: () => api.get('/consommables'),
    show: (id) => api.get(`/consommables/${id}`),
    store: (data) => api.post('/consommables', data),
    update: (id, data) => api.put(`/consommables/${id}`, data),
    destroy: (id) => api.delete(`/consommables/${id}`)
}
