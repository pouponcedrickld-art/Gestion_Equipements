import axiosInstance from './axiosConfig';

/**
 * API pour les maintenances
 */
const maintenanceApi = {
  /**
   * Récupère les maintenances par période
   * @param {string} startDate - Date de début (YYYY-MM-DD)
   * @param {string} endDate - Date de fin (YYYY-MM-DD)
   * @param {object} filters - Filtres optionnels (type_maintenance, statut)
   * @returns {Promise}
   */
  async getByPeriod(startDate, endDate, filters = {}) {
    try {
      const params = { start_date: startDate, end_date: endDate, ...filters };
      const response = await axiosInstance.get('/maintenances', { params });
      return response.data;
    } catch (error) {
      console.error('Erreur lors de la récupération des maintenances:', error);
      throw error;
    }
  },

  /**
   * Récupère les maintenances d'un mois
   * @param {string} month - Mois au format YYYY-MM
   * @param {object} filters - Filtres optionnels
   * @returns {Promise}
   */
  async getByMonth(month, filters = {}) {
    try {
      const params = { month, ...filters };
      const response = await axiosInstance.get('/maintenances', { params });
      return response.data;
    } catch (error) {
      console.error('Erreur lors de la récupération des maintenances du mois:', error);
      throw error;
    }
  },

  /**
   * Récupère une maintenance par ID
   * @param {number} id - ID de la maintenance
   * @returns {Promise}
   */
  async getById(id) {
    try {
      const response = await axiosInstance.get(`/maintenances/${id}`);
      return response.data;
    } catch (error) {
      console.error('Erreur lors de la récupération de la maintenance:', error);
      throw error;
    }
  },

  /**
   * Crée une nouvelle maintenance
   * @param {object} data - Données de la maintenance
   * @returns {Promise}
   */
  async create(data) {
    try {
      const response = await axiosInstance.post('/maintenances', data);
      return response.data;
    } catch (error) {
      console.error('Erreur lors de la création de la maintenance:', error);
      throw error;
    }
  },

  /**
   * Met à jour une maintenance
   * @param {number} id - ID de la maintenance
   * @param {object} data - Données à mettre à jour
   * @returns {Promise}
   */
  async update(id, data) {
    try {
      const response = await axiosInstance.put(`/maintenances/${id}`, data);
      return response.data;
    } catch (error) {
      console.error('Erreur lors de la mise à jour de la maintenance:', error);
      throw error;
    }
  },

  /**
   * Supprime une maintenance
   * @param {number} id - ID de la maintenance
   * @returns {Promise}
   */
  async delete(id) {
    try {
      const response = await axiosInstance.delete(`/maintenances/${id}`);
      return response.data;
    } catch (error) {
      console.error('Erreur lors de la suppression de la maintenance:', error);
      throw error;
    }
  },

  /**
   * Démarre une maintenance
   * @param {number} id - ID de la maintenance
   * @param {object} data - Données additionnelles (technicien_id, diagnostic)
   * @returns {Promise}
   */
  async start(id, data = {}) {
    try {
      const response = await axiosInstance.post(`/maintenances/${id}/start`, data);
      return response.data;
    } catch (error) {
      console.error('Erreur lors du démarrage de la maintenance:', error);
      throw error;
    }
  },

  /**
   * Termine une maintenance
   * @param {number} id - ID de la maintenance
   * @param {object} data - Données additionnelles (diagnostic, cout, observations, action_realisee)
   * @returns {Promise}
   */
  async complete(id, data = {}) {
    try {
      const response = await axiosInstance.post(`/maintenances/${id}/complete`, data);
      return response.data;
    } catch (error) {
      console.error('Erreur lors de la fin de la maintenance:', error);
      throw error;
    }
  }
};

export default maintenanceApi;
