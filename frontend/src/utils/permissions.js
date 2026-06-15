// =============================================
// FICHIER : utils/permissions.js
// RÔLE : Fonctions utilitaires pour les rôles et le menu
// 2 fonctions : hasRole() et getMenuItems()
// =============================================

// Importe le store d'authentification pour connaître le rôle de l'utilisateur
import { useAuthStore } from '@/stores/authStore'

// =============================================
// FONCTION 1 : hasRole()
// Vérifie si l'utilisateur connecté a UN des rôles passés en paramètre
// =============================================
export const hasRole = (roles) => {
    // Récupère le store d'authentification
    const authStore = useAuthStore()
    // Si l'utilisateur n'a pas de rôle, retourne false
    if (!authStore.userRole) return false
    // Si roles est un tableau, vérifie si le rôle de l'utilisateur est dedans
    if (Array.isArray(roles)) return roles.includes(authStore.userRole)
    // Sinon, vérifie si le rôle de l'utilisateur est égal à roles
    return authStore.userRole === roles
}

// =============================================
// FONCTION 2 : getMenuItems()
// Génère la LISTE DES ÉLÉMENTS DU MENU SELON LE RÔLE DE L'UTILISATEUR
// Retourne seulement les éléments visibles pour ce rôle !
// =============================================
export const getMenuItems = () => {
    // Récupère le store d'authentification
    const authStore = useAuthStore()
    // Récupère le rôle de l'utilisateur connecté
    const role = authStore.userRole
    
    // =============================================
    // Étape 1 : Déterminer le label et la route pour le menu "Équipements"
    // (ça change selon le rôle !)
    // =============================================
    let menuEquipementLabel = 'Équipements'
    let menuEquipementRoute = '/equipements'
    
    // Si c'est un Chef d'agence ou Gestionnaire de stock (agence) → "Stocks"
    if (role === 'chef_agence' || role === 'gestionnaire_stock') {
        menuEquipementLabel = 'Stocks'
    } 
    // Si c'est un Technicien maintenance → "Matériels" et route différente
    else if (role === 'technicien_maintenance') {
        menuEquipementLabel = 'Matériels'
        menuEquipementRoute = '/mes-materiels'
    }
    
    // =============================================
    // Étape 2 : Créer la liste complète des éléments du menu
    // Chaque élément a :
    //  - label : le texte affiché
    //  - icon : l'icône PrimeIcons
    //  - route : la page vers laquelle ça mène
    //  - visible : true/false selon si le rôle peut voir ça
    // =============================================
    const items = [
        { label: 'Dashboard', icon: 'pi pi-home', route: '/', visible: true }, // TOUS LES RÔLES
        { label: 'Agences', icon: 'pi pi-building', route: '/agences', visible: role === 'super_admin' }, // Seul Super Admin
        { label: 'Agents', icon: 'pi pi-users', route: '/agents', visible: !['agent', 'technicien_maintenance'].includes(role) }, // Tous sauf Agent & Tech
        { label: 'Catégories', icon: 'pi pi-tags', route: '/categories', visible: ['super_admin', 'gestionnaire_stock_general'].includes(role) }, // Admin & Gestionnaire Général
        { label: menuEquipementLabel, icon: 'pi pi-mobile', route: menuEquipementRoute, visible: true }, // TOUS, mais label change
        { label: 'Consommables', icon: 'pi pi-box', route: '/consommables', visible: ['super_admin', 'gestionnaire_stock_general'].includes(role) }, // Admin & Gestionnaire Général
        { label: 'Réceptions', icon: 'pi pi-download', route: '/receptions', visible: ['chef_agence', 'gestionnaire_stock'].includes(role) }, // Chef Agence & Gestionnaire Stock
        { label: 'Transferts', icon: 'pi pi-send', route: '/transferts', visible: ['super_admin', 'gestionnaire_stock_general', 'gestionnaire_stock'].includes(role) },
        { label: 'Demandes', icon: 'pi pi-shopping-cart', route: '/demandes-materiel', visible: ['super_admin', 'gestionnaire_stock_general', 'chef_agence'].includes(role) },
        { label: 'Affectations', icon: 'pi pi-arrow-right-arrow-left', route: '/affectations', visible: !['agent', 'technicien_maintenance'].includes(role) },
        { label: 'Pannes', icon: 'pi pi-exclamation-triangle', route: '/pannes', visible: true }, // TOUS
        { label: 'Maintenances', icon: 'pi pi-wrench', route: '/maintenances', visible: ['super_admin', 'gestionnaire_stock_general', 'technicien_maintenance', 'gestionnaire_stock'].includes(role) },
        { label: 'Calendrier', icon: 'pi pi-calendar', route: '/maintenances/calendrier', visible: ['super_admin', 'gestionnaire_stock_general', 'technicien_maintenance', 'gestionnaire_stock'].includes(role) },
        { label: 'Pertes', icon: 'pi pi-times-circle', route: '/pertes', visible: true }, // TOUS
        { label: 'Notifications', icon: 'pi pi-bell', route: '/notifications', visible: true }, // TOUS
        { label: 'Rapports', icon: 'pi pi-chart-bar', route: '/rapports', visible: !['agent', 'technicien_maintenance'].includes(role) },
        { label: 'Utilisateurs', icon: 'pi pi-user-edit', route: '/users', visible: ['super_admin', 'gestionnaire_stock_general'].includes(role) },
    ]
    
    // =============================================
    // Étape 3 : Filtrer et retourner seulement les éléments visibles
    // =============================================
    return items.filter(item => item.visible)
}
