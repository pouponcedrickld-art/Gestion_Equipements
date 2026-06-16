import api from './axiosConfig.js'

export default {
    // CRUD de base
    index: (params = {}) => api.get('/equipements', { params }),
    show: (id) => api.get(`/equipements/${id}`),
    store: (data) => {
        if (data instanceof FormData) {
            return api.post('/equipements', data);
        }
        return api.post('/equipements', data);
    },
    update: (id, data) => {
        if (data instanceof FormData) {
            if (!data.has('_method')) {
                data.append('_method', 'PATCH');
            }
            return api.post(`/equipements/${id}`, data);
        }
        return api.put(`/equipements/${id}`, data);
    },
    destroy: (id) => api.delete(`/equipements/${id}`),

    // Fonctionnalités spéciales
    search: (params) => api.get('/equipements/search/advanced', { params }),
    generateQr: (id) => api.post(`/equipements/${id}/qr`),
    
    // Import/Export
    import: (formData) => api.post('/equipements/import', formData),
    previewImport: (formData) => {
        formData.append('preview_only', 'true')
        return api.post('/equipements/import', formData)
    },
    downloadTemplate: () => api.get('/equipements/import/template', {
        responseType: 'blob'
    }),

    // Filtres et recherches spécialisées
    getByAgence: (agenceId, params = {}) => 
        api.get('/equipements', { params: { agence_id: agenceId, ...params } }),
    
    getByStatut: (statut, params = {}) => 
        api.get('/equipements', { params: { statut_global: statut, ...params } }),
    
    getByCategorie: (categorieId, params = {}) => 
        api.get('/equipements', { params: { categorie_id: categorieId, ...params } }),
    
    getDisponiblesTransfert: (params = {}) => 
        api.get('/equipements', { params: { disponibles_transfert: true, ...params } }),
    
    getGarantieExpire: (jours = 30, params = {}) => 
        api.get('/equipements', { params: { garantie_expire_bientot: true, jours_garantie: jours, ...params } }),

    // Scan QR
    searchByQR: (qrData) => 
        api.get('/equipements/search/advanced', { params: { qr_search: qrData } })
}
