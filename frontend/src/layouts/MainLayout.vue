<template>
  <div class="layout-wrapper">
    <!-- Sidebar -->
    <aside class="sidebar" :class="{ collapsed: sidebarCollapsed }">
      <div class="logo">
        <h2>GESTPARK</h2>
      </div>
      
      <nav class="menu">
        <router-link
          v-for="item in menuItems"
          :key="item.route"
          :to="item.route"
          class="menu-item"
          :class="{ active: $route.path === item.route }"
        >
          <i :class="item.icon"></i>
          <span v-if="!sidebarCollapsed">{{ item.label }}</span>
        </router-link>
      </nav>
      
      <div class="sidebar-footer">
        <div class="user-info">
          <span class="role-badge" :class="userRoleClass">
            {{ authStore.userRole }}
          </span>
          <p>{{ authStore.user?.name }}</p>
          <small>{{ authStore.user?.agence?.nom }}</small>
        </div>
        <button @click="logout" class="logout-btn">
          <i class="pi pi-sign-out"></i>
          <span v-if="!sidebarCollapsed">Déconnexion</span>
        </button>
      </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
      <header class="top-bar">
        <button @click="toggleSidebar" class="toggle-btn">
          <i class="pi pi-bars"></i>
        </button>
        <h1>{{ pageTitle }}</h1>
        <NotificationCenter />
      </header>
      
      <div class="content">
        <slot />
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/authStore'
import { getMenuItems } from '@/utils/permissions'
import NotificationCenter from '@/components/notifications/NotificationCenter.vue'

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()

const sidebarCollapsed = ref(false)

const menuItems = computed(() => getMenuItems())

const pageTitle = computed(() => {
  const item = menuItems.value.find(i => i.route === route.path)
  return item?.label || 'GESTPARK'
})

const userRoleClass = computed(() => {
  const classes = {
    super_admin: 'badge-admin',
    gestionnaire_stock_general: 'badge-gestionnaire',
    chef_agence: 'badge-chef',
    gestionnaire_stock: 'badge-gestionnaire',
    technicien_maintenance: 'badge-tech',
    agent: 'badge-agent'
  }
  return classes[authStore.userRole] || ''
})

const toggleSidebar = () => {
  sidebarCollapsed.value = !sidebarCollapsed.value
}

const logout = async () => {
  await authStore.logout()
  router.push('/login')
}
</script>

<style scoped>
.layout-wrapper {
  display: flex;
  min-height: 100vh;
}

.sidebar {
  width: 260px;
  background: #1e293b;
  color: white;
  display: flex;
  flex-direction: column;
  transition: width 0.3s;
}

.sidebar.collapsed {
  width: 60px;
}

.logo {
  padding: 20px;
  text-align: center;
  border-bottom: 1px solid #334155;
}

.logo h2 {
  margin: 0;
  color: #3b82f6;
}

.menu {
  flex: 1;
  padding: 10px 0;
}

.menu-item {
  display: flex;
  align-items: center;
  padding: 12px 20px;
  color: #cbd5e1;
  text-decoration: none;
  transition: all 0.2s;
}

.menu-item:hover, .menu-item.active {
  background: #334155;
  color: white;
}

.menu-item i {
  margin-right: 12px;
  font-size: 1.2rem;
}

.sidebar-footer {
  padding: 15px;
  border-top: 1px solid #334155;
}

.user-info {
  margin-bottom: 10px;
}

.role-badge {
  display: inline-block;
  padding: 2px 8px;
  border-radius: 4px;
  font-size: 0.7rem;
  text-transform: uppercase;
  font-weight: bold;
}

.badge-admin { background: #ef4444; }
.badge-gestionnaire { background: #f59e0b; }
.badge-chef { background: #8b5cf6; }
.badge-tech { background: #06b6d4; }
.badge-agent { background: #10b981; }

.logout-btn {
  width: 100%;
  padding: 8px;
  background: #334155;
  border: none;
  color: white;
  border-radius: 4px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
}

.main-content {
  flex: 1;
  background-color: #f8fafc !important;
  color: #1e293b !important;
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

.top-bar {
  background-color: white !important;
  color: #1e293b !important;
  padding: 15px 25px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.content {
  padding: 25px;
  flex: 1;
  background-color: #f8fafc !important;
}

.toggle-btn {
  background: none;
  border: none;
  font-size: 1.2rem;
  cursor: pointer;
}

.notifications {
  position: relative;
  cursor: pointer;
}

.badge {
  position: absolute;
  top: -5px;
  right: -5px;
  background: #ef4444;
  color: white;
  border-radius: 50%;
  width: 18px;
  height: 18px;
  font-size: 0.7rem;
  display: flex;
  align-items: center;
  justify-content: center;
}

.content {
  padding: 25px;
  flex: 1;
}
</style>