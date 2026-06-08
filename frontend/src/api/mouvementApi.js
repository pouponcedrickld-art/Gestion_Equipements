import api from './axiosConfig.js'

export default {
    index: () => api.get('/mouvements')
}
