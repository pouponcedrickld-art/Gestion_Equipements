import api from './axiosConfig.js'

export default {
  index: (filters = {}) => api.get('/notifications', { params: filters }),
  markAsRead: (id) => api.patch(`/notifications/${id}/lu`),
  markAllAsRead: () => api.patch('/notifications/mark-all-read'),
  destroy: (id) => api.delete(`/notifications/${id}`)
}

