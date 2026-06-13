<template>
  <div class="notification-center">
    <!-- Icône cloche avec badge -->
    <div class="notification-bell" @click="togglePanel">
      <i class="pi pi-bell"></i>
      <span v-if="unreadCount > 0" class="notification-badge">{{ unreadCount }}</span>
    </div>

    <!-- Panneau slide-over -->
    <div v-if="isOpen" class="notification-overlay" @click="closePanel">
      <div ref="panelRef" class="notification-panel glass-card" @click.stop>
        <div class="panel-header">
          <h3>Notifications</h3>
          <button @click="closePanel" class="close-btn">
            <i class="pi pi-times"></i>
          </button>
        </div>

        <!-- Recherche + actions -->
        <div class="panel-actions">
          <div class="search-box">
            <i class="pi pi-search" />
            <input
              v-model="search.query"
              type="text"
              placeholder="Rechercher (titre/message)"
              @keyup.enter="applySearch"
            />
          </div>

          <div class="action-row">
            <button class="action-btn" @click="markAllRead">
              <i class="pi pi-check-circle" />
              Tout marquer lu
            </button>
          </div>
        </div>

        <div class="notifications-list">
          <div v-if="loading" class="empty-state">
            <i class="pi pi-spin pi-spinner"></i>
            <p>Chargement...</p>
          </div>

          <div v-else-if="notifications.length === 0" class="empty-state">
            <i class="pi pi-inbox"></i>
            <p>Aucune notification</p>
          </div>

          <div
            v-for="notif in notifications"
            :key="notif.id"
            class="notification-item"
            :class="{ unread: !notif.lu }"
            @click="markAsRead(notif)"
          >
            <div class="notif-icon">
              <i :class="getNotificationIcon(notif.type)" />
            </div>
            <div class="notif-content">
              <h4>{{ notif.titre }}</h4>
              <p>{{ notif.message }}</p>
              <span class="notif-time">{{ formatTime(notif.created_at || notif.date) }}</span>
            </div>

            <button
              class="delete-btn"
              title="Supprimer"
              @click.stop="deleteNotif(notif.id)"
            >
              <i class="pi pi-trash" />
            </button>
          </div>
        </div>

        <!-- Pagination -->
        <div class="panel-footer">
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
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, nextTick, onMounted } from 'vue'
import { animateSlideInPanel, animateSlideOutPanel } from '@/utils/gsapAnimations'

import { useNotificationStore } from '@/stores/notificationStore'

// État local
const isOpen = ref(false)
const panelRef = ref(null)

const notificationStore = useNotificationStore()

// On garde un ref locale pour éviter de dépendre directement de la forme du store
const notifications = computed(() => notificationStore.notifications || [])

// Etat UI
const loading = computed(() => notificationStore.loading)
const search = ref({ query: '' })

const page = computed(() => notificationStore.meta?.current_page || 1)
const lastPage = computed(() => notificationStore.meta?.last_page || 1)
const perPage = computed(() => notificationStore.meta?.per_page || 10)

const fetchNotifications = async (opts = {}) => {
  await notificationStore.fetchNotifications({
    page: opts.page ?? (notificationStore.meta?.current_page || 1),
    per_page: opts.per_page ?? (notificationStore.meta?.per_page || 10),
    lu: opts.lu ?? null,
    search: opts.search ?? (search.value?.query ? search.value.query : undefined),
    titre: opts.titre,
    message: opts.message,
    type: opts.type,
    canal: opts.canal
  })
}







// Computed
const unreadCount = computed(() => {
  return (notifications.value || []).filter(n => !n.lu).length
})



// Méthodes
const togglePanel = async () => {
  // Charge à l’ouverture (1ère fois)
  if (isOpen.value === false && (!notificationStore.notifications || notificationStore.notifications.length === 0)) {
    await fetchNotifications()
  }
  isOpen.value = !isOpen.value
}


const closePanel = () => {
  if (panelRef.value) {
    animateSlideOutPanel(panelRef.value, () => {
      isOpen.value = false
    })
  } else {
    isOpen.value = false
  }
}

// Animation à l'ouverture
watch(isOpen, async (newVal) => {
  if (newVal) {
    await nextTick()
    if (panelRef.value) {
      animateSlideInPanel(panelRef.value)
    }
  }
})

onMounted(async () => {
  // chargement léger au montage si déjà demandé ailleurs
  if (!notificationStore.notifications || notificationStore.notifications.length === 0) {
    await fetchNotifications()
  }
})


const getNotificationIcon = (type) => {
  const icons = {
    maintenance: 'pi pi-wrench',
    maintenance_programmee: 'pi pi-wrench',
    maintenance_terminee: 'pi pi-check-circle',
    panne: 'pi pi-exclamation-triangle',
    panne_declaree: 'pi pi-exclamation-triangle',
    panne_resolue: 'pi pi-check',
    transfert: 'pi pi-send',
    transfert_valide: 'pi pi-check',
    transfert_recu: 'pi pi-inbox',
    retard: 'pi pi-clock',
    retour_en_retard: 'pi pi-clock',
    garantie: 'pi pi-shield',
    garantie_proche_expiration: 'pi pi-shield',
    default: 'pi pi-info-circle'
  };
  return icons[type] || icons.default;
};

const formatTime = (date) => {
  if (!date) return ''
  const d = typeof date === 'string' ? new Date(date) : date
  const now = new Date()
  const diff = now - d
  const minutes = Math.floor(diff / 60000)
  const hours = Math.floor(diff / 3600000)
  const days = Math.floor(diff / 86400000)

  if (minutes < 60) return `Il y a ${minutes} min`
  if (hours < 24) return `Il y a ${hours}h`
  return `Il y a ${days}j`
}

// Actions panel
const applySearch = async () => {
  await fetchNotifications({
    page: 1,
    lu: null,
    search: search.value?.query
  })
}

const markAsRead = async (notif) => {
  if (!notif || notif.lu) return
  await notificationStore.markAsRead(notif.id)
}

const markAllRead = async () => {
  await notificationStore.markAllAsRead()
}

const deleteNotif = async (id) => {
  try {
    await notificationStore.deleteNotification?.(id)
  } finally {
    // refresh current page
    await fetchNotifications({
      page: page.value,
      lu: null,
      search: search.value?.query
    })
  }
}

const prevPage = async () => {
  if (page.value <= 1) return
  await fetchNotifications({
    page: page.value - 1,
    lu: null,
    search: search.value?.query
  })
}

const nextPage = async () => {
  if (page.value >= lastPage.value) return
  await fetchNotifications({
    page: page.value + 1,
    lu: null,
    search: search.value?.query
  })
}

</script>


<style scoped>
.notification-center {
  position: relative;
}

.notification-bell {
  position: relative;
  cursor: pointer;
  padding: 8px;
  border-radius: 50%;
  transition: background 0.2s;
}

.notification-bell:hover {
  background: rgba(0, 0, 0, 0.05);
}

.notification-bell i {
  font-size: 1.3rem;
  color: #475569;
}

.notification-badge {
  position: absolute;
  top: 2px;
  right: 2px;
  background: #ef4444;
  color: white;
  border-radius: 50%;
  width: 20px;
  height: 20px;
  font-size: 0.7rem;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
}

.notification-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.3);
  z-index: 999;
  backdrop-filter: blur(2px);
}

.notification-panel {
  position: fixed;
  top: 0;
  right: 0;
  height: 100vh;
  width: 400px;
  background: white;
  box-shadow: -4px 0 20px rgba(0, 0, 0, 0.1);
  display: flex;
  flex-direction: column;
  /* Animation gérée par GSAP, suppression de l'animation CSS */
}

.panel-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px;
  border-bottom: 1px solid #e2e8f0;
}

.panel-header h3 {
  margin: 0;
  color: #1e293b;
}

.close-btn {
  background: none;
  border: none;
  font-size: 1.3rem;
  cursor: pointer;
  color: #64748b;
  transition: color 0.2s;
}

.close-btn:hover {
  color: #1e293b;
}

.notifications-list {
  flex: 1;
  overflow-y: auto;
  padding: 10px;
}

.empty-state {
  text-align: center;
  padding: 60px 20px;
  color: #94a3b8;
}

.empty-state i {
  font-size: 3rem;
  margin-bottom: 15px;
}

.notification-item {
  display: flex;
  gap: 12px;
  padding: 15px;
  margin-bottom: 8px;
  border-radius: 8px;
  background: #f8fafc;
  cursor: pointer;
  transition: all 0.2s;
}

.notification-item:hover {
  background: #f1f5f9;
  transform: translateX(-2px);
}

.notification-item.unread {
  background: #eff6ff;
  border-left: 3px solid #3b82f6;
}

.notif-icon {
  flex-shrink: 0;
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: #3b82f6;
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
}

.notif-content {
  flex: 1;
}

.notif-content h4 {
  margin: 0 0 4px 0;
  font-size: 0.95rem;
  color: #1e293b;
}

.notif-content p {
  margin: 0 0 6px 0;
  font-size: 0.85rem;
  color: #64748b;
  line-height: 1.4;
}

.notif-time {
  font-size: 0.75rem;
  color: #94a3b8;
}

.glass-card {
  backdrop-filter: blur(10px);
  background: rgba(255, 255, 255, 0.95);
}

@media (max-width: 480px) {
  .notification-panel {
    width: 100%;
  }
}
</style>
