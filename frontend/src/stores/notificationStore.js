import { defineStore } from 'pinia'
import notificationApi from '@/api/notificationApi.js'

export const useNotificationStore = defineStore('notification', {
  state: () => ({
    notifications: [],
    loading: false,
    error: null
  }),
  actions: {
    async fetchNotifications(filters = {}) {
      this.loading = true
      this.error = null
      try {
        const response = await notificationApi.index(filters)
        this.notifications = response.data?.data || response.data || []
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
        this.notifications.forEach(n => n.lu = true)
      } catch (error) {
        this.error = error.response?.data?.message || 'Erreur lors du marquage de toutes les notifications'
        throw error
      } finally {
        this.loading = false
      }
    }
  }
})
