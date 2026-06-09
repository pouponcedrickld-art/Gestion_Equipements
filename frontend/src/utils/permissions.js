import { useAuthStore } from '@/stores/authStore'

export const hasRole = (roles) => {
    const authStore = useAuthStore()
    if (!authStore.userRole) return false
    if (Array.isArray(roles)) return roles.includes(authStore.userRole)
    return authStore.userRole === roles
}

export const getMenuItems = () => {
    const authStore = useAuthStore()
    const role = authStore.userRole
    
    const items = [
        { label: 'Dashboard', icon: 'pi pi-home', route: '/', visible: true },
        { label: 'Agences', icon: 'pi pi-building', route: '/agences', visible: role === 'super_admin' },
        { label: 'Agents', icon: 'pi pi-users', route: '/agents', visible: role !== 'agent' },
        { label: 'Catégories', icon: 'pi pi-tags', route: '/categories', visible: true },
        { label: 'Équipements', icon: 'pi pi-mobile', route: '/equipements', visible: true },
        { label: 'Consommables', icon: 'pi pi-box', route: '/consommables', visible: true },
        { label: 'Transferts', icon: 'pi pi-send', route: '/transferts', visible: ['super_admin', 'gestionnaire_stock_general', 'gestionnaire_stock'].includes(role) },
        { label: 'Demandes', icon: 'pi pi-shopping-cart', route: '/demandes-materiel', visible: ['super_admin', 'gestionnaire_stock_general', 'chef_agence'].includes(role) },
        { label: 'Affectations', icon: 'pi pi-arrow-right-arrow-left', route: '/affectations', visible: role !== 'agent' },
        { label: 'Pannes', icon: 'pi pi-exclamation-triangle', route: '/pannes', visible: true },
        { label: 'Maintenances', icon: 'pi pi-wrench', route: '/maintenances', visible: ['super_admin', 'gestionnaire_stock_general', 'technicien_maintenance', 'gestionnaire_stock'].includes(role) },
        { label: 'Pertes', icon: 'pi pi-times-circle', route: '/pertes', visible: true },
        { label: 'Notifications', icon: 'pi pi-bell', route: '/notifications', visible: true },
        { label: 'Rapports', icon: 'pi pi-chart-bar', route: '/rapports', visible: role !== 'agent' },
        { label: 'Utilisateurs', icon: 'pi pi-user-edit', route: '/users', visible: ['super_admin', 'gestionnaire_stock_general'].includes(role) },
    ]
    return items.filter(item => item.visible)
}
