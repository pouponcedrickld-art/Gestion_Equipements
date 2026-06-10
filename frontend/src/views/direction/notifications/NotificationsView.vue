<template>
  <AgenceLayout>
    <div class="notifications-container">
      <div class="header-bar">
        <div class="header-left">
          <h2>Notifications</h2>
          <p>Consultez toutes vos notifications</p>
        </div>
        <div class="header-right">
          <button v-if="hasUnread" class="mark-all-btn" @click="markAllRead">
            <i class="pi pi-check"></i> Tout marquer lu
          </button>
        </div>
      </div>

      <div class="filters-card">
        <div class="filters-row">
          <div class="select-box">
            <select v-model="filters.lu">
              <option value="">Toutes</option>
              <option :value="false">Non lu</option>
              <option :value="true">Lu</option>
            </select>
          </div>
        </div>
      </div>

      <div class="notifications-list">
        <div v-if="loading" class="loading-state">
          <i class="pi pi-spin pi-spinner"></i> Chargement...
        </div>
        <div v-else-if="notifications.length === 0" class="empty-state">
          <i class="pi pi-bell"></i>
          <p>Aucune notification</p>
        </div>
        <div v-else class="notifications-cards">
          <div v-for="n in filteredNotifications" :key="n.id" class="notification-card" :class="{ unread: !n.lu }" @click="markAsRead(n)">
            <div class="notification-icon">
              <i :class="getIcon(n.type)"></i>
            </div>
            <div class="notification-content">
              <div class="notification-title">{{ n.titre }}</div>
              <div class="notification-text">{{ n.message }}</div>
              <div class="notification-date">{{ formatDate(n.created_at) }}</div>
            </div>
            <div v-if="!n.lu" class="unread-dot"></div>
          </div>
        </div>
      </div>
    </div>
  </AgenceLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import AgenceLayout from '@/layouts/AgenceLayout.vue'
import { useNotificationStore } from '@/stores/notificationStore.js'
import { useToast } from 'primevue/usetoast'

const notificationStore = useNotificationStore()
const toast = useToast()

const notifications = ref([])
const loading = ref(false)
const filters = ref({ lu: null })

const filteredNotifications = computed(() => {
  return notifications.value.filter(n => filters.value.lu === null || n.lu === filters.value.lu)
})

const hasUnread = computed(() => notifications.value.some(n => !n.lu))

const fetchNotifications = async () => {
  loading.value = true
  await notificationStore.fetchNotifications(filters.value)
  notifications.value = notificationStore.notifications
  loading.value = false
}

const markAsRead = async (notification) => {
  if (!notification.lu) {
    try {
      await notificationStore.markAsRead(notification.id)
      notification.lu = true
    } catch (err) {
      console.error(err)
    }
  }
}

const markAllRead = async () => {
  try {
    await notificationStore.markAllAsRead()
    toast.add({ severity: 'success', summary: 'Succès', detail: 'Toutes les notifications marquées comme lues', life: 3000 })
  } catch (err) {
    toast.add({ severity: 'error', summary: 'Erreur', detail: 'Échec de l\'opération', life: 3000 })
  }
}

const getIcon = (type) => {
  const icons = {
    info: 'pi-info-circle',
    success: 'pi-check-circle',
    warning: 'pi-exclamation-triangle',
    error: 'pi-times-circle',
    panne: 'pi-exclamation-circle',
    maintenance: 'pi-wrench',
    transfert: 'pi-arrow-right',
    demande: 'pi-file'
  }
  return `pi ${icons[type] || icons.info}`
}

const formatDate = (date) => {
  if (!date) return ''
  const d = new Date(date)
  return d.toLocaleString('fr-FR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

onMounted(fetchNotifications)
</script>

<style scoped>
.notifications-container { padding: 24px; color: #f8fafc; }
.header-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; }
.header-bar h2 { margin: 0; font-size: 1.5rem; }
.header-bar p { color: #94a3b8; margin: 4px 0 0 0; }
.mark-all-btn { background: #3b82f6; color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; display: flex; align-items: center; gap: 8px; font-weight: 600; }
.mark-all-btn:hover { background: #2563eb; }
.filters-card { background: #1e293b; border: 1px solid #334155; padding: 16px; border-radius: 12px; margin-bottom: 20px; }
.filters-row { display: flex; gap: 16px; flex-wrap: wrap; }
.select-box select { background: #0f172a; border: 1px solid #334155; color: #f8fafc; padding: 10px 12px; border-radius: 8px; width: 180px; }
.notifications-list { background: transparent; border-radius: 12px; }
.notifications-cards { display: grid; gap: 16px; }
.notification-card { background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 20px; display: flex; gap: 16px; align-items: flex-start; cursor: pointer; transition: all 0.2s; }
.notification-card:hover { border-color: #3b82f6; background: #26364c; }
.notification-card.unread { background: #26364c; border-color: #3b82f6; }
.notification-icon { width: 48px; height: 48px; border-radius: 50%; background: rgba(59, 130, 246, 0.15); color: #3b82f6; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; flex-shrink: 0; }
.notification-content { flex: 1; }
.notification-title { font-weight: 600; color: #e2e8f0; margin-bottom: 4px; }
.notification-text { color: #94a3b8; line-height: 1.5; margin-bottom: 8px; }
.notification-date { color: #64748b; font-size: 0.8rem; }
.unread-dot { width: 10px; height: 10px; border-radius: 50%; background: #3b82f6; flex-shrink: 0; margin-top: 8px; }
.loading-state, .empty-state { padding: 80px; text-align: center; color: #94a3b8; }
.loading-state i { font-size: 2rem; margin-bottom: 12px; color: #3b82f6; }
.empty-state i { font-size: 3rem; margin-bottom: 12px; opacity: 0.2; }
</style>
