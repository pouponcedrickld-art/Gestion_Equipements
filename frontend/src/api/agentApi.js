import api from './axiosConfig.js'

export default {
    index: () => api.get('/agents'),
    show: (id) => api.get(`/agents/${id}`),
    store: (data) => api.post('/agents', data),
    update: (id, data) => api.put(`/agents/${id}`, data),
    destroy: (id) => api.delete(`/agents/${id}`)
}
