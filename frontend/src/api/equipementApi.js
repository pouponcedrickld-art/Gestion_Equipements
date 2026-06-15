// =============================================
// FICHIER : equipementApi.js
// RÔLE : API Client pour les requêtes liées aux équipements
// Utilise l'instance Axios configurée dans axiosConfig.js
// =============================================

// Importe l'instance Axios pré-configurée
import api from './axiosConfig.js'

export default {
    // =============================================
    // CRUD DE BASE
    // =============================================
    // Récupère la liste des équipements (avec paramètres optionnels de filtrage/pagination)
    index: (params = {}) => api.get('/equipements', { params }),
    
    // Récupère les détails d'un équipement par son ID
    show: (id) => api.get(`/equipements/${id}`),
    
    // Crée un nouvel équipement
    store: (data) => api.post('/equipements', data),
    
    // Met à jour un équipement existant
    update: (id, data) => {
        // Si c'est un FormData (pour uploader des fichiers), on utilise POST avec _method=PATCH (spécificité Laravel)
        if (data instanceof FormData) {
            if (!data.has('_method')) {
                data.append('_method', 'PATCH');
            }
            return api.post(`/equipements/${id}`, data, {
                headers: { 'Content-Type': 'multipart/form-data' }
            });
        }
        // Sinon, on utilise PUT normalement
        return api.put(`/equipements/${id}`, data);
    },
    
    // Supprime un équipement
    destroy: (id) => api.delete(`/equipements/${id}`),

    // =============================================
    // FONCTIONNALITÉS SPÉCIALES
    // =============================================
    // Recherche avancée d'équipements
    search: (params) => api.get('/equipements/search/advanced', { params }),
    
    // Génère un QR code pour un équipement
    generateQr: (id) => api.post(`/equipements/${id}/qr`),
    
    // =============================================
    // IMPORT/EXPORT
    // =============================================
    // Importe un fichier (Excel/CSV) d'équipements
    import: (formData) => api.post('/equipements/import', formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
    }),
    
    // Prévisualise l'import (sans enregistrer)
    previewImport: (formData) => {
        formData.append('preview_only', 'true')
        return api.post('/equipements/import', formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        })
    },
    
    // Télécharge le modèle d'import Excel/CSV
    downloadTemplate: () => api.get('/equipements/import/template', {
        responseType: 'blob' // Important : pour télécharger un fichier
    }),

    // =============================================
    // FILTRES ET RECHERCHES SPÉCIALISÉES
    // =============================================
    // Récupère les équipements d'une agence spécifique
    getByAgence: (agenceId, params = {}) => 
        api.get('/equipements', { params: { agence_id: agenceId, ...params } }),
    
    // Récupère les équipements par statut global
    getByStatut: (statut, params = {}) => 
        api.get('/equipements', { params: { statut_global: statut, ...params } }),
    
    // Récupère les équipements par catégorie
    getByCategorie: (categorieId, params = {}) => 
        api.get('/equipements', { params: { categorie_id: categorieId, ...params } }),
    
    // Récupère les équipements disponibles pour un transfert
    getDisponiblesTransfert: (params = {}) => 
        api.get('/equipements', { params: { disponibles_transfert: true, ...params } }),
    
    // Récupère les équipements dont la garantie expire bientôt
    getGarantieExpire: (jours = 30, params = {}) => 
        api.get('/equipements', { params: { garantie_expire_bientot: true, jours_garantie: jours, ...params } }),

    // =============================================
    // SCAN QR
    // =============================================
    // Recherche un équipement par son QR code
    searchByQR: (qrData) => 
        api.get('/equipements/search/advanced', { params: { qr_search: qrData } })
}
