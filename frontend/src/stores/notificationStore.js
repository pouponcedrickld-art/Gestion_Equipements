import { defineStore } from 'pinia'
import notificationApi from '@/api/notificationApi.js'

export const useNotificationStore = defineStore('notification', {
  state: () => ({
    notifications: [],
    meta: {
      current_page: 1,
      per_page: 10,
      total: 0,
      last_page: 1
    },
    loading: false,
    error: null
  }),
  actions: {
    async fetchNotifications(filters = {}) {
      this.loading = true
      this.error = null
      try {
        const response = await notificationApi.index(filters)

        const payload = response.data
        // Laravel paginator : { data: [...], current_page, last_page, total... }
        this.notifications = payload?.data || payload?.data?.data || payload?.items || payload || []

        if (payload?.current_page) {
          this.meta.current_page = payload.current_page
          this.meta.per_page = payload.per_page
          this.meta.total = payload.total
          this.meta.last_page = payload.last_page
        }
      } catch (error) {
        this.error = error.response?.data?.message || 'Erreur lors du chargement des notifications'
      } finally {
        this.loading = false
      }
    },
    async markAsRead(id) {
      this.loading = true
      this.error = null
      try {
        await notificationApi.markAsRead(id)
        const n = this.notifications.find(x => x.id === id)
        if (n) n.lu = true
      } catch (error) {
        this.error = error.response?.data?.message || 'Erreur lors du marquage comme lu'
        throw error
      } finally {
        this.loading = false
      }
    },
    async markAllAsRead() {
      this.loading = true
      this.error = null
      try {
        await notificationApi.markAllAsRead()
        this.notifications.forEach(n => (n.lu = true))
      } catch (error) {
        this.error = error.response?.data?.message || 'Erreur lors du marquage de toutes les notifications'
        throw error
      } finally {
        this.loading = false
      }
    },
    async deleteNotification(id) {
      this.loading = true
      this.error = null
      try {
        await notificationApi.destroy(id)
        this.notifications = this.notifications.filter(n => n.id !== id)
      } catch (error) {
        this.error = error.response?.data?.message || 'Erreur lors de la suppression de la notification'
        throw error
      } finally {
        this.loading = false
      }
    }
  }
})
