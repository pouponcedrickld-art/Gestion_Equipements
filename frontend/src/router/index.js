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
    name: 'Dashboard',
    component: () => import('@/views/agence/dashboard/DashboardView.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/agences',
    name: 'Agences',
    component: () => import('@/views/direction/agences/AgencesView.vue'),
    meta: { requiresAuth: true, roles: ['super_admin'] }
  },
  {
    path: '/agents',
    name: 'Agents',
    component: () => import('@/views/agence/agents/AgentsView.vue'),
    meta: { requiresAuth: true, roles: ['super_admin', 'gestionnaire_stock_general', 'chef_agence', 'gestionnaire_stock'] }
  },
  {
    path: '/categories',
    name: 'Categories',
    component: () => import('@/views/direction/categories/CategoriesView.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/categories/:id',
    name: 'CategorieDetail',
    component: () => import('@/views/direction/categories/CategorieDetailView.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/equipements',
    name: 'Equipements',
    component: () => import('@/views/direction/equipements/EquipementsView.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/equipements/nouveau',
    name: 'NouvelEquipement',
    component: () => import('@/views/direction/equipements/EquipementFormView.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/equipements/:id',
    name: 'EquipementDetail',
    component: () => import('@/views/direction/equipements/EquipementDetailView.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/equipements/:id/modifier',
    name: 'ModifierEquipement',
    component: () => import('@/views/direction/equipements/EquipementFormView.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/equipements/scan',
    name: 'ScanQR',
    component: () => import('@/views/direction/equipements/ScanQRView.vue'),
    meta: { requiresAuth: true }
  },
  {
        path: '/consommables',
        name: 'Consommables',
        component: () => import('@/views/direction/consommables/ConsommablesView.vue'),
        meta: { requiresAuth: true }
      },
      {
        path: '/consommables/:id',
        name: 'ConsommableDetail',
        component: () => import('@/views/direction/consommables/ConsommableDetailView.vue'),
        meta: { requiresAuth: true }
      },
  {
    path: '/transferts',
    name: 'Transferts',
    component: () => import('@/views/direction/transferts/TransfertsView.vue'),
    meta: { requiresAuth: true, roles: ['super_admin', 'gestionnaire_stock_general', 'gestionnaire_stock'] }
  },
  {
    path: '/demandes-materiel',
    name: 'DemandesMateriel',
    component: () => import('@/views/agence/demandes-materiel/DemandesView.vue'),
    meta: { requiresAuth: true, roles: ['super_admin', 'gestionnaire_stock_general', 'chef_agence'] }
  },
  {
    path: '/demandes-materiel/nouveau',
    name: 'NouvelleDemande',
    component: () => import('@/views/agence/demandes-materiel/DemandeFormView.vue'),
    meta: { requiresAuth: true, roles: ['chef_agence'] }
  },
  {
    path: '/affectations',
    name: 'Affectations',
    component: () => import('@/views/agence/affectations/AffectationsView.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/pannes',
    name: 'Pannes',
    component: () => import('@/views/agence/pannes/PannesView.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/maintenances',
    name: 'Maintenances',
    component: () => import('@/views/agence/maintenances/MaintenancesView.vue'),
    meta: { requiresAuth: true, roles: ['super_admin', 'gestionnaire_stock_general', 'technicien_maintenance', 'gestionnaire_stock'] }
  },
  {
    path: '/mouvements',
    name: 'Mouvements',
    component: () => import('@/views/agence/mouvements/MouvementsView.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/pertes',
    name: 'Pertes',
    component: () => import('@/views/agence/pertes/PertesView.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/notifications',
    name: 'Notifications',
    component: () => import('@/views/direction/notifications/NotificationsView.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/rapports',
    name: 'Rapports',
    component: () => import('@/views/agence/rapports/RapportsView.vue'),
    meta: { requiresAuth: true, roles: ['super_admin', 'gestionnaire_stock_general', 'chef_agence', 'gestionnaire_stock', 'technicien_maintenance'] }
  },
  {
    path: '/users',
    name: 'Users',
    component: () => import('@/views/direction/utilisateurs/UsersView.vue'),
    meta: { requiresAuth: true, roles: ['super_admin', 'gestionnaire_stock_general'] }
  },
  {
    path: '/maintenances/calendrier',
    name: 'MaintenanceCalendar',
    component: () => import('@/views/agence/maintenances/MaintenanceCalendarView.vue'),
    meta: { requiresAuth: true, roles: ['super_admin', 'gestionnaire_stock_general', 'technicien_maintenance', 'gestionnaire_stock'] }
  },
// {
//   path: '/maintenances/details/:id',
//   name: 'MaintenanceDetails',
//   component: () => import('@/views/maintenances/MaintenanceDetailsView.vue'),
//   meta: { requiresAuth: true, roles: ['super_admin', 'technicien_maintenance', 'gestionnaire_stock'] }
// }
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
