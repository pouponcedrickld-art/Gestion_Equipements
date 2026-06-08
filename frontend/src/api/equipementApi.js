import api from './axiosConfig.js'

export default {
    index: () => api.get('/equipements'),
    show: (id) => api.get(`/equipements/${id}`),
    store: (data) => api.post('/equipements', data),
    update: (id, data) => api.put(`/equipements/${id}`, data),
    destroy: (id) => api.delete(`/equipements/${id}`),
    import: (data) => api.post('/equipements/import', data),
    generateQr: (id) => api.post(`/equipements/${id}/qr`)
}
