import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/authStore'

const routes = [
    // Auth (public)
    {
        path: '/login',
        name: 'Login',
        component: () => import('@/views/auth/LoginView.vue'),
        meta: { public: true }
    },
    {
        path: '/2fa',
        name: 'TwoFactor',
        component: () => import('@/views/auth/TwoFactorView.vue'),
        meta: { public: true, requires2FA: true }
    },

    // Dashboard
    {
        path: '/',
        name: 'Dashboard',
        component: () => import('@/views/dashboard/DashboardView.vue'),
        meta: { requiresAuth: true }
    },

    // Agences (Super Admin uniquement)
    {
        path: '/agences',
        name: 'Agences',
        component: () => import('@/views/agences/AgencesView.vue'),
        meta: { requiresAuth: true, roles: ['super_admin'] }
    },

    // Agents
    {
        path: '/agents',
        name: 'Agents',
        component: () => import('@/views/agents/AgentsView.vue'),
        meta: { requiresAuth: true, roles: ['super_admin', 'gestionnaire_stock_general', 'chef_agence', 'gestionnaire_stock'] }
    },

    // Équipements
    {
        path: '/equipements',
        name: 'Equipements',
        component: () => import('@/views/equipements/EquipementsView.vue'),
        meta: { requiresAuth: true }
    },

    // Transferts (Gestionnaire stock général + sous-agence)
    {
        path: '/transferts',
        name: 'Transferts',
        component: () => import('@/views/transferts/TransfertsView.vue'),
        meta: { requiresAuth: true, roles: ['super_admin', 'gestionnaire_stock_general', 'gestionnaire_stock'] }
    },

    // Demandes matériel (Chef d'agence + Gestionnaire stock général)
    {
        path: '/demandes-materiel',
        name: 'DemandesMateriel',
        component: () => import('@/views/demandes-materiel/DemandesView.vue'),
        meta: { requiresAuth: true, roles: ['super_admin', 'gestionnaire_stock_general', 'chef_agence'] }
    },

    // Affectations
    {
        path: '/affectations',
        name: 'Affectations',
        component: () => import('@/views/affectations/AffectationsView.vue'),
        meta: { requiresAuth: true }
    },

    // Pannes
    {
        path: '/pannes',
        name: 'Pannes',
        component: () => import('@/views/pannes/PannesView.vue'),
        meta: { requiresAuth: true }
    },

    // Maintenances (Technicien + Gestionnaires)
    {
        path: '/maintenances',
        name: 'Maintenances',
        component: () => import('@/views/maintenances/MaintenancesView.vue'),
        meta: { requiresAuth: true, roles: ['super_admin', 'gestionnaire_stock_general', 'technicien_maintenance', 'gestionnaire_stock'] }
    },

    // Pertes
    {
        path: '/pertes',
        name: 'Pertes',
        component: () => import('@/views/pertes/PertesView.vue'),
        meta: { requiresAuth: true }
    },

    // Notifications
    {
        path: '/notifications',
        name: 'Notifications',
        component: () => import('@/views/notifications/NotificationsView.vue'),
        meta: { requiresAuth: true }
    },

    // Rapports
    {
        path: '/rapports',
        name: 'Rapports',
        component: () => import('@/views/rapports/RapportsView.vue'),
        meta: { requiresAuth: true }
    },

    // Utilisateurs (Admin + Gestionnaire stock général)
    {
        path: '/users',
        name: 'Users',
        component: () => import('@/views/users/UsersView.vue'),
        meta: { requiresAuth: true, roles: ['super_admin', 'gestionnaire_stock_general'] }
    }
]

const router = createRouter({
    history: createWebHistory(),
    routes
})

// Guard global
router.beforeEach((to, from, next) => {
    const authStore = useAuthStore()

    // Route publique
    if (to.meta.public) {
        next()
        return
    }

    // Pas connecté → login
    if (!authStore.isAuthenticated) {
        next('/login')
        return
    }

    // Vérifier les rôles
    if (to.meta.roles && !to.meta.roles.includes(authStore.userRole)) {
        next('/') // Rediriger vers dashboard si pas autorisé
        return
    }

    next()
})

export default router