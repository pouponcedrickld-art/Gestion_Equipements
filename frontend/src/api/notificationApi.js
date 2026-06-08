import api from './axiosConfig.js'

export default {
    index: () => api.get('/notifications'),
    markAsRead: (id) => api.patch(`/notifications/${id}/lu`)
}
