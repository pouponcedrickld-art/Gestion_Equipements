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
  background-color: var(--bg-app);
}

.sidebar {
  width: 260px;
  background: var(--sidebar-bg);
  color: var(--sidebar-text);
  display: flex;
  flex-direction: column;
  transition: width 0.3s;
  border-right: 1px solid var(--sidebar-border);
  box-shadow: var(--shadow-sm);
  z-index: 100;
}

.sidebar.collapsed {
  width: 70px;
}

.logo {
  padding: 24px;
  text-align: center;
  border-bottom: 1px solid var(--sidebar-border);
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
}

.logo h2 {
  margin: 0;
  color: var(--text-dark);
  font-size: 1.5rem;
  font-weight: 800;
  letter-spacing: 0.05em;
}

.menu {
  flex: 1;
  padding: 20px 0;
  overflow-y: auto;
}

.menu-item {
  display: flex;
  align-items: center;
  padding: 12px 24px;
  color: var(--text-muted);
  text-decoration: none;
  transition: all 0.2s;
  font-weight: 600;
  margin: 4px 12px;
  border-radius: var(--radius-md);
}

.menu-item:hover {
  background: var(--bg-input);
  color: var(--text-dark);
}

.menu-item.active {
  background: var(--primary);
  color: var(--text-dark);
  box-shadow: var(--shadow-sm);
}

.menu-item i {
  margin-right: 12px;
  font-size: 1.2rem;
  width: 24px;
  text-align: center;
}

.sidebar.collapsed .menu-item {
  justify-content: center;
  padding: 12px 0;
  margin: 4px 8px;
}

.sidebar.collapsed .menu-item i {
  margin-right: 0;
}

.sidebar-footer {
  padding: 20px;
  border-top: 1px solid var(--sidebar-border);
}

.user-info {
  margin-bottom: 15px;
  padding: 0 4px;
}

.role-badge {
  display: inline-block;
  padding: 4px 10px;
  border-radius: 20px;
  font-size: 0.65rem;
  text-transform: uppercase;
  font-weight: 800;
  margin-bottom: 8px;
}

.badge-admin { background: #fee2e2; color: #ef4444; }
.badge-gestionnaire { background: #fef3c7; color: #f59e0b; }
.badge-chef { background: #ede9fe; color: #8b5cf6; }
.badge-tech { background: #e0f2fe; color: #06b6d4; }
.badge-agent { background: #dcfce7; color: #10b981; }

.user-info p {
  margin: 0;
  font-weight: 700;
  font-size: 0.9rem;
  color: var(--text-dark);
}

.user-info small {
  color: var(--text-muted);
  font-size: 0.8rem;
}

.logout-btn {
  width: 100%;
  padding: 10px;
  background: var(--bg-input);
  border: 1px solid var(--border-color);
  color: var(--text-main);
  border-radius: var(--radius-md);
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  font-weight: 600;
  transition: all 0.2s;
}

.logout-btn:hover {
  background: #fee2e2;
  color: #ef4444;
  border-color: #fca5a5;
}

.main-content {
  flex: 1;
  min-height: 100vh;
  max-height: 100vh;
  display: flex;
  flex-direction: column;
  overflow: hidden;
  background-color: var(--bg-app);
}

.top-bar {
  background-color: var(--bg-card);
  color: var(--text-main);
  padding: 0 30px;
  height: 70px;
  min-height: 70px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  border-bottom: 1px solid var(--border-color);
  z-index: 90;
}

.top-bar h1 {
  margin: 0;
  font-size: 1.25rem;
  font-weight: 700;
}

.content {
  padding: 2rem;
  flex: 1;
  background-color: var(--bg-app);
  overflow-y: auto;
  width: 100%;
  box-sizing: border-box;
}

.toggle-btn {
  background: var(--bg-input);
  border: 1px solid var(--border-color);
  border-radius: var(--radius-sm);
  width: 36px;
  height: 36px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  color: var(--text-muted);
  transition: all 0.2s;
}

.toggle-btn:hover {
  background: var(--primary);
  color: var(--text-dark);
  border-color: var(--primary);
}
</style>