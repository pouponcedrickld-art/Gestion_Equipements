<template>
  <div class="layout-wrapper">
    <!-- 1. SIDEBAR (la barre de menu à gauche) -->
    <aside class="sidebar" :class="{ collapsed: sidebarCollapsed }">
      <!-- 1a. Logo en haut de la sidebar -->
      <div class="logo">
        <h2>GESTPARK</h2>
      </div>
      
      <!-- 1b. Menu de navigation : les liens vers les pages -->
      <nav class="menu">
        <!-- Pour chaque élément du menu (généré selon le rôle de l'utilisateur), on fait un lien -->
        <router-link
          v-for="item in menuItems"
          :key="item.route"
          :to="item.route"
          class="menu-item"
          :class="{ active: $route.path === item.route }"
        >
          <i :class="item.icon"></i> <!-- Icône de l'élément -->
          <span v-if="!sidebarCollapsed">{{ item.label }}</span> <!-- Texte de l'élément (si sidebar pas réduite) -->
        </router-link>
      </nav>
      
      <!-- 1c. Bas de la sidebar : infos utilisateur et bouton déconnexion -->
      <div class="sidebar-footer">
        <!-- Infos de l'utilisateur connecté -->
        <div class="user-info">
          <!-- Badge avec le rôle (couleur selon le rôle) -->
          <span class="role-badge" :class="userRoleClass">
            {{ authStore.userRole }}
          </span>
          <!-- Nom de l'utilisateur -->
          <p>{{ authStore.user?.name }}</p>
          <!-- Nom de l'agence de l'utilisateur -->
          <small>{{ authStore.user?.agence?.nom }}</small>
        </div>
        <!-- Bouton de déconnexion -->
        <button @click="logout" class="logout-btn">
          <i class="pi pi-sign-out"></i>
          <span v-if="!sidebarCollapsed">Déconnexion</span>
        </button>
      </div>
    </aside>

    <!-- 2. CONTENU PRINCIPAL (à droite de la sidebar) -->
    <main class="main-content">
      <!-- 2a. Topbar (barre en haut) -->
      <header class="top-bar">
        <!-- Bouton pour réduire/agrandir la sidebar -->
        <button @click="toggleSidebar" class="toggle-btn">
          <i class="pi pi-bars"></i>
        </button>
        <!-- Titre de la page (ex: "Tableau de bord") -->
        <h1>{{ pageTitle }}</h1>
        <!-- Composant pour les notifications (cloche) -->
        <NotificationCenter />
      </header>
      
      <!-- 2b. Zone où s'affiche le contenu de la page (ce qui est passé via <slot />) -->
      <div class="content">
        <slot />
      </div>
    </main>
  </div>
</template>

<script setup>
// =============================================
// IMPORTS : ce qu'on a besoin pour le layout
// =============================================
// Importe les fonctions de Vue 3 (ref pour variables réactives, computed pour propriétés calculées)
import { ref, computed } from 'vue'
// Importe les fonctions de Vue Router (useRoute pour savoir quelle page on est, useRouter pour naviguer)
import { useRoute, useRouter } from 'vue-router'
// Importe le store d'authentification (pour infos utilisateur, rôle, etc.)
import { useAuthStore } from '@/stores/authStore'
// Importe la fonction qui génère le menu SELON LE RÔLE DE L'UTILISATEUR (dans utils/permissions.js)
import { getMenuItems } from '@/utils/permissions'
// Importe le composant pour les notifications (la cloche en haut à droite)
import NotificationCenter from '@/components/notifications/NotificationCenter.vue'

// =============================================
// INITIALISATIONS
// =============================================
// route : contient les infos de la page actuelle (chemin, paramètres, etc.)
const route = useRoute()
// router : permet de naviguer vers d'autres pages
const router = useRouter()
// authStore : le store d'authentification
const authStore = useAuthStore()

// =============================================
// VARIABLES RÉACTIVES (ref)
// =============================================
// sidebarCollapsed : true si la sidebar est réduite, false sinon (défaut : false)
const sidebarCollapsed = ref(false)

// =============================================
// PROPRIÉTÉS CALCULÉES (computed)
// =============================================
// menuItems : récupère la liste des éléments du menu ADAPTÉE AU RÔLE DE L'UTILISATEUR
// C'est une computed, donc ça se met à jour automatiquement si le rôle change !
const menuItems = computed(() => getMenuItems())

// pageTitle : trouve le titre de la page en fonction de la route actuelle
const pageTitle = computed(() => {
  // Cherche dans le menu l'élément qui a la même route que la page actuelle
  const item = menuItems.value.find(i => i.route === route.path)
  // Si on trouve un élément, on utilise son label, sinon on met "GESTPARK"
  return item?.label || 'GESTPARK'
})

// userRoleClass : donne la classe CSS pour le badge du rôle (couleur différente selon le rôle)
const userRoleClass = computed(() => {
  const classes = {
    super_admin: 'badge-admin',
    gestionnaire_stock_general: 'badge-gestionnaire',
    chef_agence: 'badge-chef',
    gestionnaire_stock: 'badge-gestionnaire',
    technicien_maintenance: 'badge-tech',
    agent: 'badge-agent'
  }
  // Retourne la classe correspondant au rôle, ou une chaîne vide si pas trouvé
  return classes[authStore.userRole] || ''
})

// =============================================
// FONCTIONS
// =============================================
// toggleSidebar : inverse l'état de la sidebar (réduite ↔ agrandie)
const toggleSidebar = () => {
  // Inverse la valeur : si c'était true → false, si false → true
  sidebarCollapsed.value = !sidebarCollapsed.value
}

// logout : déconnecte l'utilisateur et redirige vers la page de login
const logout = async () => {
  // 1. Appelle la fonction logout du store d'authentification (authStore.js)
  await authStore.logout()
  // 2. Redirige l'utilisateur vers la page de login
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

.badge-admin { background: rgba(239, 68, 68, 0.2); color: #ef4444; }
.badge-gestionnaire { background: rgba(245, 158, 11, 0.2); color: #f59e0b; }
.badge-chef { background: rgba(139, 92, 246, 0.2); color: #8b5cf6; }
.badge-tech { background: rgba(6, 182, 212, 0.2); color: #06b6d4; }
.badge-agent { background: rgba(16, 185, 129, 0.2); color: #10b981; }

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
  background: rgba(239, 68, 68, 0.2);
  color: #ef4444;
  border-color: #ef4444;
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