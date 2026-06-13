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
          <div class="search-box">
            <i class="pi pi-search" />
            <input
              v-model="filters.search"
              type="text"
              placeholder="Rechercher (titre/message)"
              @keyup.enter="applyFilters"
            />
          </div>

          <div class="select-box">
            <select v-model="filters.lu" @change="applyFilters">
              <option value="">Toutes</option>
              <option :value="false">Non lu</option>
              <option :value="true">Lu</option>
            </select>
          </div>
        </div>

        <div class="pagination-row">
          <div class="pagination-left">
            <button class="page-btn" :disabled="page <= 1" @click="prevPage">
              <i class="pi pi-chevron-left" />
              Précédent
            </button>
            <div class="page-info">{{ page }} / {{ lastPage }}</div>
            <button class="page-btn" :disabled="page >= lastPage" @click="nextPage">
              Suivant
              <i class="pi pi-chevron-right" />
            </button>
          </div>
          <div class="pagination-right">
            <span class="meta-text">Total: {{ meta.total }}</span>
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
          <div
            v-for="n in notifications"
            :key="n.id"
            class="notification-card"
            :class="{ unread: !n.lu }"
            @click="markAsRead(n)"
          >
            <div class="notification-icon">
              <i :class="getIcon(n.type)" />
            </div>

            <div class="notification-content">
              <div class="notification-title">{{ n.titre }}</div>
              <div class="notification-text">{{ n.message }}</div>
              <div class="notification-date">{{ formatDate(n.created_at) }}</div>
            </div>

            <button class="delete-btn" title="Supprimer" @click.stop="deleteNotif(n.id)">
              <i class="pi pi-trash" />
            </button>

            <div v-if="!n.lu" class="unread-dot"></div>
          </div>
        </div>
      </div>
    </div>
  </AgenceLayout>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import AgenceLayout from '@/layouts/AgenceLayout.vue'
import { useNotificationStore } from '@/stores/notificationStore.js'
import { useToast } from 'primevue/usetoast'

const notificationStore = useNotificationStore()
const toast = useToast()

const notifications = ref([])

const loading = computed(() => notificationStore.loading)
const meta = computed(() => notificationStore.meta)
const page = computed(() => meta.value.current_page || 1)
const lastPage = computed(() => meta.value.last_page || 1)

const filters = ref({
  lu: '',
  search: ''
})

const hasUnread = computed(() => (notifications.value || []).some(n => !n.lu))

const buildParams = (overrides = {}) => {
  const lu = filters.value.lu === '' ? undefined : filters.value.lu
  const search = filters.value.search?.trim() || undefined

  return {
    lu,
    search,
    page: overrides.page ?? page.value,
    per_page: overrides.per_page ?? meta.value.per_page
  }
}

const fetchNotifications = async (overrides = {}) => {
  const params = buildParams(overrides)
  await notificationStore.fetchNotifications(params)
  notifications.value = notificationStore.notifications
}

const applyFilters = async () => {
  await fetchNotifications({ page: 1 })
}

const prevPage = async () => {
  if (page.value <= 1) return
  await fetchNotifications({ page: page.value - 1 })
}

const nextPage = async () => {
  if (page.value >= lastPage.value) return
  await fetchNotifications({ page: page.value + 1 })
}

const markAsRead = async (notification) => {
  if (!notification?.lu) {
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
    await fetchNotifications({ page: page.value })
  } catch (err) {
    toast.add({ severity: 'error', summary: 'Erreur', detail: "Échec de l'opération", life: 3000 })
  }
}

const deleteNotif = async (id) => {
  try {
    await notificationStore.deleteNotification(id)
    toast.add({ severity: 'success', summary: 'OK', detail: 'Notification supprimée', life: 2500 })
    await fetchNotifications({ page: page.value })
  } catch (err) {
    toast.add({ severity: 'error', summary: 'Erreur', detail: 'Impossible de supprimer', life: 2500 })
    console.error(err)
  }
}

const getIcon = (type) => {
  const icons = {
    info: 'pi-info-circle',
    success: 'pi-check-circle',
    warning: 'pi-exclamation-triangle',
    error: 'pi-times-circle',
    panne: 'pi-exclamation-circle',
    panne_declaree: 'pi-exclamation-triangle',
    panne_resolue: 'pi-check-circle',
    maintenance: 'pi-wrench',
    maintenance_programmee: 'pi-wrench',
    maintenance_terminee: 'pi-check-circle',
    transfert: 'pi-arrow-right',
    transfert_valide: 'pi-check',
    transfert_recu: 'pi-inbox',
    retour_en_retard: 'pi-clock',
    garantie_proche_expiration: 'pi-shield',
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

onMounted(() => {
  fetchNotifications({ page: 1 })
})
</script>

<style scoped>
.notifications-container { padding: 24px; color: #f8fafc; }

.header-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
}

.header-bar h2 { margin: 0; font-size: 1.5rem; }
.header-bar p { color: #94a3b8; margin: 4px 0 0 0; }

.mark-all-btn {
  background: #3b82f6;
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 8px;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 8px;
  font-weight: 600;
}
.mark-all-btn:hover { background: #2563eb; }

.filters-card {
  background: #1e293b;
  border: 1px solid #334155;
  padding: 16px;
  border-radius: 12px;
  margin-bottom: 20px;
}

.filters-row {
  display: flex;
  gap: 16px;
  flex-wrap: wrap;
  align-items: center;
}

.search-box {
  display: flex;
  align-items: center;
  gap: 10px;
  background: #0f172a;
  border: 1px solid #334155;
  border-radius: 8px;
  padding: 10px 12px;
}
.search-box input {
  background: transparent;
  border: none;
  outline: none;
  color: #f8fafc;
  width: 260px;
}

.select-box select {
  background: #0f172a;
  border: 1px solid #334155;
  color: #f8fafc;
  padding: 10px 12px;
  border-radius: 8px;
  width: 180px;
}

.pagination-row {
  margin-top: 14px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 16px;
}

.page-btn {
  background: #0f172a;
  border: 1px solid #334155;
  color: #f8fafc;
  padding: 10px 12px;
  border-radius: 8px;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 8px;
}
.page-btn:disabled { opacity: .5; cursor: not-allowed; }
.page-info { color: #94a3b8; font-size: 0.9rem; min-width: 90px; text-align: center; }
.meta-text { color: #64748b; font-size: 0.9rem; }

.notifications-list { background: transparent; border-radius: 12px; }
.notifications-cards { display: grid; gap: 16px; }

.notification-card {
  background: #1e293b;
  border: 1px solid #334155;
  border-radius: 12px;
  padding: 20px;
  display: flex;
  gap: 16px;
  align-items: flex-start;
  cursor: pointer;
  transition: all 0.2s;
  position: relative;
}

.notification-card:hover { border-color: #3b82f6; background: #26364c; }
.notification-card.unread { background: #26364c; border-color: #3b82f6; }

.notification-icon {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  background: rgba(59, 130, 246, 0.15);
  color: #3b82f6;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  flex-shrink: 0;
}

.notification-content { flex: 1; }
.notification-title { font-weight: 600; color: #e2e8f0; margin-bottom: 4px; }
.notification-text { color: #94a3b8; line-height: 1.5; margin-bottom: 8px; }
.notification-date { color: #64748b; font-size: 0.8rem; }

.unread-dot {
  width: 10px;
  height: 10px;
  border-radius: 50%;
  background: #3b82f6;
  flex-shrink: 0;
  margin-top: 8px;
  align-self: flex-start;
}

.delete-btn {
  background: transparent;
  border: 1px solid #334155;
  color: #94a3b8;
  padding: 8px;
  border-radius: 8px;
  cursor: pointer;
}
.delete-btn:hover { border-color: #ef4444; color: #ef4444; }

.loading-state, .empty-state {
  padding: 80px;
  text-align: center;
  color: #94a3b8;
}
.loading-state i { font-size: 2rem; margin-bottom: 12px; color: #3b82f6; }
.empty-state i { font-size: 3rem; margin-bottom: 12px; opacity: 0.2; }
</style>

