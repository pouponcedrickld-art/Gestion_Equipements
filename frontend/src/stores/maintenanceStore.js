import { defineStore } from 'pinia';
import maintenanceApi from '@/api/maintenanceApi';

export const useMaintenanceStore = defineStore('maintenance', {
  state: () => ({
    maintenances: [],
    loading: false,
    error: null,
    cache: new Map(),
    selectedMaintenance: null
  }),

  getters: {
    /**
     * Récupère les maintenances pour une date donnée
     */
    maintenancesByDate: (state) => (date) => {
      return state.maintenances.filter(m => {
        const maintenanceDate = new Date(m.date_prevue).toISOString().split('T')[0];
        return maintenanceDate === date;
      });
    },

    /**
     * Récupère les maintenances par statut
     */
    maintenancesByStatus: (state) => (status) => {
      return state.maintenances.filter(m => m.statut === status);
    },

    /**
     * Récupère les maintenances par type
     */
    maintenancesByType: (state) => (type) => {
      return state.maintenances.filter(m => m.type_maintenance === type);
    },

    /**
     * Indique si les données sont en cours de chargement
     */
    isLoading: (state) => state.loading,

    /**
     * Indique s'il y a une erreur
     */
    hasError: (state) => !!state.error
  },

  actions: {
    /**
     * Récupère les maintenances par période
     */
    async fetchMaintenancesByPeriod(startDate, endDate, filters = {}) {
      // Clé de cache
      const cacheKey = `${startDate}_${endDate}_${JSON.stringify(filters)}`;
      
      // Vérifier le cache (TTL 5 minutes)
      const cached = this.cache.get(cacheKey);
      if (cached && Date.now() - cached.timestamp < 5 * 60 * 1000) {
        this.maintenances = cached.data;
        return;
      }

      this.loading = true;
      this.error = null;

      try {
        const response = await maintenanceApi.getByPeriod(startDate, endDate, filters);
        this.maintenances = response.data || [];
        
        // Mettre en cache
        this.cache.set(cacheKey, {
          data: this.maintenances,
          timestamp: Date.now()
        });
      } catch (error) {
        this.error = error.response?.data?.message || 'Erreur lors du chargement des maintenances';
        console.error('Erreur fetchMaintenancesByPeriod:', error);
      } finally {
        this.loading = false;
      }
    },

    /**
     * Récupère les maintenances d'un mois
     */
    async fetchMaintenancesByMonth(month, filters = {}) {
      const cacheKey = `month_${month}_${JSON.stringify(filters)}`;
      
      const cached = this.cache.get(cacheKey);
      if (cached && Date.now() - cached.timestamp < 5 * 60 * 1000) {
        this.maintenances = cached.data;
        return;
      }

      this.loading = true;
      this.error = null;

      try {
        const response = await maintenanceApi.getByMonth(month, filters);
        this.maintenances = response.data || [];
        
        this.cache.set(cacheKey, {
          data: this.maintenances,
          timestamp: Date.now()
        });
      } catch (error) {
        this.error = error.response?.data?.message || 'Erreur lors du chargement des maintenances';
        console.error('Erreur fetchMaintenancesByMonth:', error);
      } finally {
        this.loading = false;
      }
    },

    /**
     * Récupère une maintenance par ID
     */
    async fetchMaintenanceById(id) {
      this.loading = true;
      this.error = null;

      try {
        const response = await maintenanceApi.getById(id);
        this.selectedMaintenance = response.data;
        return response.data;
      } catch (error) {
        this.error = error.response?.data?.message || 'Erreur lors du chargement de la maintenance';
        console.error('Erreur fetchMaintenanceById:', error);
        throw error;
      } finally {
        this.loading = false;
      }
    },

    /**
     * Crée une nouvelle maintenance
     */
    async createMaintenance(data) {
      this.loading = true;
      this.error = null;

      try {
        const response = await maintenanceApi.create(data);
        
        // Invalider le cache
        this.clearCache();
        
        return response.data;
      } catch (error) {
        this.error = error.response?.data?.message || 'Erreur lors de la création de la maintenance';
        console.error('Erreur createMaintenance:', error);
        throw error;
      } finally {
        this.loading = false;
      }
    },

    /**
     * Met à jour une maintenance
     */
    async updateMaintenance(id, data) {
      this.loading = true;
      this.error = null;

      try {
        const response = await maintenanceApi.update(id, data);
        
        // Invalider le cache
        this.clearCache();
        
        return response.data;
      } catch (error) {
        this.error = error.response?.data?.message || 'Erreur lors de la mise à jour de la maintenance';
        console.error('Erreur updateMaintenance:', error);
        throw error;
      } finally {
        this.loading = false;
      }
    },

    /**
     * Supprime une maintenance
     */
    async deleteMaintenance(id) {
      this.loading = true;
      this.error = null;

      try {
        const response = await maintenanceApi.delete(id);
        
        // Invalider le cache
        this.clearCache();
        
        return response.data;
      } catch (error) {
        this.error = error.response?.data?.message || 'Erreur lors de la suppression de la maintenance';
        console.error('Erreur deleteMaintenance:', error);
        throw error;
      } finally {
        this.loading = false;
      }
    },

    /**
     * Démarre une maintenance
     */
    async startMaintenance(id, data = {}) {
      this.loading = true;
      this.error = null;

      try {
        const response = await maintenanceApi.start(id, data);
        
        // Invalider le cache
        this.clearCache();
        
        return response.data;
      } catch (error) {
        this.error = error.response?.data?.message || 'Erreur lors du démarrage de la maintenance';
        console.error('Erreur startMaintenance:', error);
        throw error;
      } finally {
        this.loading = false;
      }
    },

    /**
     * Termine une maintenance
     */
    async completeMaintenance(id, data = {}) {
      this.loading = true;
      this.error = null;

      try {
        const response = await maintenanceApi.complete(id, data);
        
        // Invalider le cache
        this.clearCache();
        
        return response.data;
      } catch (error) {
        this.error = error.response?.data?.message || 'Erreur lors de la fin de la maintenance';
        console.error('Erreur completeMaintenance:', error);
        throw error;
      } finally {
        this.loading = false;
      }
    },

    /**
     * Efface le cache
     */
    clearCache() {
      this.cache.clear();
    },

    /**
     * Réinitialise l'erreur
     */
    resetError() {
      this.error = null;
    }
  }
});
