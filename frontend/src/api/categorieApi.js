import api from './axiosConfig.js'

export default {
    index: () => api.get('/categories'),
    show: (id) => api.get(`/categories/${id}`),
    store: (data) => api.post('/categories', data),
    update: (id, data) => api.put(`/categories/${id}`, data),
    destroy: (id) => api.delete(`/categories/${id}`)
}
