import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/authStore.js'
import AuthLayout from '@/layouts/AuthLayout.vue'
import MainLayout from '@/layouts/MainLayout.vue'

const routes = [
  {
    path: '/login',
    component: AuthLayout,
    children: [
      {
        path: '',
        name: 'Login',
        component: () => import('@/views/auth/LoginView.vue'),
        meta: { public: true }
      },
      {
        path: '2fa',
        name: 'TwoFactor',
        component: () => import('@/views/auth/TwoFactorView.vue'),
        meta: { public: true }
      }
    ]
  },
  {
    path: '/',
<<<<<<< HEAD
    component: MainLayout,
    children: [
      {
        path: '',
        name: 'Dashboard',
        component: () => import('@/views/dashboard/DashboardView.vue'),
        meta: { requiresAuth: true }
      },
      {
        path: 'agences',
        name: 'Agences',
        component: () => import('@/views/agences/AgencesView.vue'),
        meta: { requiresAuth: true, roles: ['super_admin'] }
      },
      {
        path: 'agents',
        name: 'Agents',
        component: () => import('@/views/agents/AgentsView.vue'),
        meta: { requiresAuth: true, roles: ['super_admin', 'gestionnaire_stock_general', 'chef_agence', 'gestionnaire_stock'] }
      },
      {
        path: 'equipements',
        name: 'Equipements',
        component: () => import('@/views/equipements/EquipementsView.vue'),
        meta: { requiresAuth: true }
      },
      {
        path: 'transferts',
        name: 'Transferts',
        component: () => import('@/views/transferts/TransfertsView.vue'),
        meta: { requiresAuth: true, roles: ['super_admin', 'gestionnaire_stock_general', 'gestionnaire_stock'] }
      },
      {
        path: 'demandes-materiel',
        name: 'DemandesMateriel',
        component: () => import('@/views/demandes-materiel/DemandesView.vue'),
        meta: { requiresAuth: true, roles: ['super_admin', 'gestionnaire_stock_general', 'chef_agence'] }
      },
      {
        path: 'affectations',
        name: 'Affectations',
        component: () => import('@/views/affectations/AffectationsView.vue'),
        meta: { requiresAuth: true }
      },
      {
        path: 'pannes',
        name: 'Pannes',
        component: () => import('@/views/pannes/PannesView.vue'),
        meta: { requiresAuth: true }
      },
      {
        path: 'maintenances',
        name: 'Maintenances',
        component: () => import('@/views/maintenances/MaintenancesView.vue'),
        meta: { requiresAuth: true, roles: ['super_admin', 'gestionnaire_stock_general', 'technicien_maintenance', 'gestionnaire_stock'] }
      },
      {
        path: 'pertes',
        name: 'Pertes',
        component: () => import('@/views/pertes/PertesView.vue'),
        meta: { requiresAuth: true }
      },
      {
        path: 'notifications',
        name: 'Notifications',
        component: () => import('@/views/notifications/NotificationsView.vue'),
        meta: { requiresAuth: true }
      },
      {
        path: 'rapports',
        name: 'Rapports',
        component: () => import('@/views/rapports/RapportsView.vue'),
        meta: { requiresAuth: true, roles: ['super_admin'] }
      },
      {
        path: 'users',
        name: 'Users',
        component: () => import('@/views/users/UsersView.vue'),
        meta: { requiresAuth: true, roles: ['super_admin', 'gestionnaire_stock_general'] }
      }
    ]
=======
    name: 'Dashboard',
    component: () => import('@/views/dashboard/DashboardView.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/agences',
    name: 'Agences',
    component: () => import('@/views/agences/AgencesView.vue'),
    meta: { requiresAuth: true, roles: ['super_admin'] }
  },
  {
    path: '/agents',
    name: 'Agents',
    component: () => import('@/views/agents/AgentsView.vue'),
    meta: { requiresAuth: true, roles: ['super_admin', 'gestionnaire_stock_general', 'chef_agence', 'gestionnaire_stock'] }
  },
  {
    path: '/categories',
    name: 'Categories',
    component: () => import('@/views/categories/CategoriesView.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/equipements',
    name: 'Equipements',
    component: () => import('@/views/equipements/EquipementsView.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/equipements/nouveau',
    name: 'NouvelEquipement',
    component: () => import('@/views/equipements/EquipementFormView.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/equipements/:id',
    name: 'EquipementDetail',
    component: () => import('@/views/equipements/EquipementDetailView.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/equipements/:id/modifier',
    name: 'ModifierEquipement',
    component: () => import('@/views/equipements/EquipementFormView.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/equipements/scan',
    name: 'ScanQR',
    component: () => import('@/views/equipements/ScanQRView.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/consommables',
    name: 'Consommables',
    component: () => import('@/views/consommables/ConsommablesView.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/transferts',
    name: 'Transferts',
    component: () => import('@/views/transferts/TransfertsView.vue'),
    meta: { requiresAuth: true, roles: ['super_admin', 'gestionnaire_stock_general', 'gestionnaire_stock'] }
  },
  {
    path: '/demandes-materiel',
    name: 'DemandesMateriel',
    component: () => import('@/views/demandes-materiel/DemandesView.vue'),
    meta: { requiresAuth: true, roles: ['super_admin', 'gestionnaire_stock_general', 'chef_agence'] }
  },
  {
    path: '/affectations',
    name: 'Affectations',
    component: () => import('@/views/affectations/AffectationsView.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/pannes',
    name: 'Pannes',
    component: () => import('@/views/pannes/PannesView.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/maintenances',
    name: 'Maintenances',
    component: () => import('@/views/maintenances/MaintenancesView.vue'),
    meta: { requiresAuth: true, roles: ['super_admin', 'gestionnaire_stock_general', 'technicien_maintenance', 'gestionnaire_stock'] }
  },
  {
    path: '/pertes',
    name: 'Pertes',
    component: () => import('@/views/pertes/PertesView.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/notifications',
    name: 'Notifications',
    component: () => import('@/views/notifications/NotificationsView.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/rapports',
    name: 'Rapports',
    component: () => import('@/views/rapports/RapportsView.vue'),
    meta: { requiresAuth: true, roles: ['super_admin', 'gestionnaire_stock_general', 'chef_agence', 'gestionnaire_stock', 'technicien_maintenance'] }
  },
  {
    path: '/users',
    name: 'Users',
    component: () => import('@/views/users/UsersView.vue'),
    meta: { requiresAuth: true, roles: ['super_admin', 'gestionnaire_stock_general'] }
>>>>>>> ffa46c85f50ce66431a07232052669c1f18451a6
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

router.beforeEach((to, from) => {
  const authStore = useAuthStore()

  if (to.meta.public) {
    return
  }

  if (!authStore.isAuthenticated) {
    return '/login'
  }

  if (to.meta.roles && !to.meta.roles.includes(authStore.userRole)) {
    return '/'
  }
})

export default router
