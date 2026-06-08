import api from './axiosConfig'

export default {
  getKanban() {
    return api.get('/transferts-kanban')
  },
  updateStatus(id, newStatus) {
    return api.post('/transferts-kanban/update-status', { id, newStatus })
  }
}
