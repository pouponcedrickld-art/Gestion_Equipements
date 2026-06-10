import { computed } from 'vue'
import { useNotificationStore } from '@/stores/notificationStore'

export function useNotification() {
  const store = useNotificationStore()

  const notifications = computed(() => store.notifications)
  const loading = computed(() => store.loading)
  const error = computed(() => store.error)
  const unreadCount = computed(() => store.notifications.filter(n => !n.lu).length)

  async function loadNotifications(filters = {}) {
    await store.fetchNotifications(filters)
    return store.notifications
  }

  async function markAsRead(id) {
    await store.markAsRead(id)
    const index = store.notifications.findIndex(n => n.id === id)
    if (index !== -1) {
      store.notifications[index].lu = true
    }
  }

  async function markAllAsRead() {
    store.notifications.forEach(n => n.lu = true)
  }

  function resetError() {
    store.error = null
  }

  return {
    notifications,
    loading,
    error,
    unreadCount,
    loadNotifications,
    markAsRead,
    markAllAsRead,
    resetError
  }
}
