import { useAuthStore } from '@/stores/authStore'

// Vérifier si l'utilisateur a un rôle
export const hasRole = (roles) => {
    const authStore = useAuthStore()
    return authStore.hasRole(roles)
}

// Vérifier si l'utilisateur peut voir une agence
export const canViewAgence = (agenceId) => {
    const authStore = useAuthStore()
    return authStore.canViewAgence(agenceId)
}

// Permissions spécifiques par module
export const canCreateAgence = () => hasRole('super_admin')
export const canManageUsers = () => hasRole(['super_admin', 'gestionnaire_stock_general'])
export const canManageStock = () => hasRole(['super_admin', 'gestionnaire_stock_general', 'gestionnaire_stock'])
export const canCreateTransfert = () => hasRole(['super_admin', 'gestionnaire_stock_general'])
export const canReceiveTransfert = () => hasRole(['super_admin', 'gestionnaire_stock_general', 'gestionnaire_stock'])
export const canCreateDemande = () => hasRole(['super_admin', 'chef_agence'])
export const canTraiterDemande = () => hasRole(['super_admin', 'gestionnaire_stock_general'])
export const canDiagnostiquerPanne = () => hasRole(['super_admin', 'technicien_maintenance'])
export const canPlanifierMaintenance = () => hasRole(['super_admin', 'technicien_maintenance'])

// Menu items visibles selon le rôle
export const getMenuItems = () => {
    const authStore = useAuthStore()
    const items = [
        { label: 'Dashboard', icon: 'pi pi-home', route: '/', visible: true },
        { label: 'Agences', icon: 'pi pi-building', route: '/agences', visible: authStore.isSuperAdmin },
        { label: 'Agents', icon: 'pi pi-users', route: '/agents', visible: !authStore.isAgent },
        { label: 'Équipements', icon: 'pi pi-mobile', route: '/equipements', visible: true },
        { label: 'Transferts', icon: 'pi pi-send', route: '/transferts', visible: authStore.isSuperAdmin || authStore.isGestionnaireGeneral || authStore.isGestionnaireStock },
        { label: 'Demandes', icon: 'pi pi-shopping-cart', route: '/demandes-materiel', visible: authStore.isSuperAdmin || authStore.isGestionnaireGeneral || authStore.isChefAgence },
        { label: 'Affectations', icon: 'pi pi-arrow-right-arrow-left', route: '/affectations', visible: !authStore.isAgent },
        { label: 'Pannes', icon: 'pi pi-exclamation-triangle', route: '/pannes', visible: true },
        { label: 'Maintenances', icon: 'pi pi-wrench', route: '/maintenances', visible: authStore.isSuperAdmin || authStore.isGestionnaireGeneral || authStore.isTechnicien || authStore.isGestionnaireStock },
        { label: 'Pertes', icon: 'pi pi-times-circle', route: '/pertes', visible: true },
        { label: 'Notifications', icon: 'pi pi-bell', route: '/notifications', visible: true },
        { label: 'Rapports', icon: 'pi pi-chart-bar', route: '/rapports', visible: !authStore.isAgent },
        { label: 'Utilisateurs', icon: 'pi pi-user-edit', route: '/users', visible: authStore.isSuperAdmin || authStore.isGestionnaireGeneral },
    ]
    return items.filter(item => item.visible)
}