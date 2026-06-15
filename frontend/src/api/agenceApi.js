// =============================================
// FICHIER : agenceApi.js
// RÔLE : API Client pour les requêtes liées aux agences
// Utilise l'instance Axios configurée dans axiosConfig.js
// =============================================

// Importe l'instance Axios pré-configurée
import api from './axiosConfig.js'

export default {
    // Récupère la liste de toutes les agences
    index: () => api.get('/agences'),
    
    // Récupère les détails d'une agence par son ID
    show: (id) => api.get(`/agences/${id}`),
    
    // Crée une nouvelle agence
    store: (data) => api.post('/agences', data),
    
    // Met à jour une agence existante
    update: (id, data) => api.put(`/agences/${id}`, data),
    
    // Supprime une agence
    destroy: (id) => api.delete(`/agences/${id}`),
    
    // Récupère les statistiques d'une agence
    stats: (id) => api.get(`/agences/${id}/stats`)
}
