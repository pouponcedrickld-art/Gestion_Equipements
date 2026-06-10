import api from './axiosConfig.js'

export default {
    index: () => api.get('/demandes-materiel'),
    show: (id) => api.get(`/demandes-materiel/${id}`),
    store(data) {
        return api.post('/demandes-materiel', data)
    },

    update(id, data) {
        return api.put(`/demandes-materiel/${id}`, data)
    },

    destroy(id) {
        return api.delete(`/demandes-materiel/${id}`)
    },

    traiter(id, data) {
        return api.post(`/demandes-materiel/${id}/traiter`, data)
    }
}
