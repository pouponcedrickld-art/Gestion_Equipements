import api from './axiosConfig.js'

export default {
    inventaire: () => api.get('/rapports/inventaire'),
    pannes: () => api.get('/rapports/pannes'),
    export: (type) => api.get(`/rapports/export/${type}`)
}
