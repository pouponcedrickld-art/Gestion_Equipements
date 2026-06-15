// =============================================
// FICHIER : panneApi.js
// RÔLE : API Client pour les requêtes liées aux pannes
// Utilise l'instance Axios configurée dans axiosConfig.js
// =============================================

// Importe l'instance Axios pré-configurée
import api from './axiosConfig.js'

export default {
    // =============================================
    // CRUD DE BASE
    // =============================================
    // Récupère la liste des pannes (avec filtres optionnels)
    index: (filters = {}) => api.get('/pannes', { params: filters }),
    
    // Récupère les détails d'une panne par son ID
    show: (id) => api.get(`/pannes/${id}`),
    
    // Crée une nouvelle panne (déclaration)
    store: (data) => api.post('/pannes', data),
    
    // Met à jour une panne existante
    update: (id, data) => api.put(`/pannes/${id}`, data),
    
    // Supprime une panne
    destroy: (id) => api.delete(`/pannes/${id}`),
    
    // =============================================
    // ACTIONS SPÉCIFIQUES AU WORKFLOW DES PANNES
    // =============================================
    // Transmet la panne à la maintenance
    transmettreMaintenance: (id, data) => api.post(`/pannes/${id}/transmettre-maintenance`, data),
    
    // Ajoute un diagnostic de technicien à la panne
    diagnostiquer: (id, data) => api.post(`/pannes/${id}/diagnostiquer`, data),
    
    // Prend une décision sur la panne (ex: réparer, remplacer, etc.)
    decider: (id, data) => api.post(`/pannes/${id}/decider`, data),
    
    // Met à jour le résultat de la panne
    updateResultat: (id, data) => api.post(`/pannes/${id}/update-resultat`, data),
    
    // Clôture la panne
    cloturer: (id, data) => api.post(`/pannes/${id}/cloturer`, data)
}
