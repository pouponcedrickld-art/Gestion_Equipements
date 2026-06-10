<template>
  <AgenceLayout>
    <div class="maintenances-hub p-6">
      <!-- Header -->
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-black mb-2 flex items-center gap-3">
          <i class="pi pi-wrench text-blue-500"></i>
          Gestion des Maintenances
        </h1>
        <p class="text-gray-600 dark:text-gray-400">
          Planifiez, suivez et gérez toutes vos maintenances préventives et correctives
        </p>
      </div>

      <!-- Quick Actions Cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        <!-- Calendrier Card -->
        <router-link
          to="/maintenances/calendrier"
          class="feature-card glass-card hover:scale-105 transition-all duration-300"
        >
          <div class="card-icon bg-blue-500">
            <i class="pi pi-calendar text-3xl text-white"></i>
          </div>
          <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">
            📅 Calendrier
          </h3>
          <p class="text-gray-600 dark:text-gray-400 text-sm mb-4">
            Vue mensuelle de toutes les maintenances planifiées avec filtres avancés
          </p>
          <div class="card-footer">
            <span class="text-blue-500 font-semibold flex items-center gap-2">
              Ouvrir le calendrier
              <i class="pi pi-arrow-right"></i>
            </span>
          </div>
        </router-link>

        <!-- Liste des Maintenances Card -->
        <div class="feature-card glass-card opacity-60 cursor-not-allowed">
          <div class="card-icon bg-gray-400">
            <i class="pi pi-list text-3xl text-white"></i>
          </div>
          <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">
            📋 Liste
          </h3>
          <p class="text-gray-600 dark:text-gray-400 text-sm mb-4">
            Tableau détaillé avec recherche, tri et export (à venir)
          </p>
          <div class="card-footer">
            <span class="text-gray-400 font-semibold">
              Prochainement
            </span>
          </div>
        </div>

        <!-- Nouvelle Maintenance Card -->
        <div class="feature-card glass-card opacity-60 cursor-not-allowed">
          <div class="card-icon bg-gray-400">
            <i class="pi pi-plus-circle text-3xl text-white"></i>
          </div>
          <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">
            ➕ Nouvelle
          </h3>
          <p class="text-gray-600 dark:text-gray-400 text-sm mb-4">
            Planifier une nouvelle maintenance préventive ou corrective (à venir)
          </p>
          <div class="card-footer">
            <span class="text-gray-400 font-semibold">
              Prochainement
            </span>
          </div>
        </div>
      </div>

      <!-- Stats Section -->
      <div class="stats-section mb-8">
        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">
          📊 Statistiques Rapides
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div class="stat-card">
            <div class="stat-value text-blue-600">-</div>
            <div class="stat-label">Planifiées</div>
          </div>
          <div class="stat-card">
            <div class="stat-value text-orange-600">-</div>
            <div class="stat-label">En cours</div>
          </div>
          <div class="stat-card">
            <div class="stat-value text-green-600">-</div>
            <div class="stat-label">Terminées ce mois</div>
          </div>
          <div class="stat-card">
            <div class="stat-value text-purple-600">-</div>
            <div class="stat-label">Total</div>
          </div>
        </div>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-3 text-center">
          💡 Astuce : Utilisez le calendrier pour visualiser toutes vos maintenances
        </p>
      </div>

      <!-- Quick Access -->
      <div class="quick-access bg-blue-50 dark:bg-blue-900/20 rounded-lg p-6">
        <h3 class="text-lg font-bold text-blue-900 dark:text-blue-100 mb-3 flex items-center gap-2">
          <i class="pi pi-bolt"></i>
          Accès Rapide
        </h3>
        <div class="flex flex-wrap gap-3">
          <router-link
            to="/maintenances/calendrier"
            class="quick-btn bg-blue-500 hover:bg-blue-600 text-white"
          >
            <i class="pi pi-calendar"></i>
            Voir le Calendrier
          </router-link>
          <button class="quick-btn bg-gray-300 text-gray-500 cursor-not-allowed" disabled>
            <i class="pi pi-file-excel"></i>
            Export Excel
          </button>
          <button class="quick-btn bg-gray-300 text-gray-500 cursor-not-allowed" disabled>
            <i class="pi pi-chart-line"></i>
            Rapports
          </button>
        </div>
      </div>
    </div>
  </AgenceLayout>
</template>

<script setup>
import AgenceLayout from '@/layouts/AgenceLayout.vue'
</script>

<style scoped>
.maintenances-hub {
  min-height: 100vh;
}

/* Feature Cards */
.feature-card {
  background: rgba(255, 255, 255, 0.9);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.3);
  border-radius: 16px;
  padding: 24px;
  display: flex;
  flex-direction: column;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  text-decoration: none;
  cursor: pointer;
}

.feature-card:hover:not(.cursor-not-allowed) {
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
}

.card-icon {
  width: 64px;
  height: 64px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 16px;
}

.card-footer {
  margin-top: auto;
  padding-top: 16px;
}

/* Stats Cards */
.stat-card {
  background: white;
  border-radius: 12px;
  padding: 20px;
  text-align: center;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.stat-value {
  font-size: 2rem;
  font-weight: bold;
  margin-bottom: 8px;
}

.stat-label {
  color: #64748b;
  font-size: 0.875rem;
}

/* Quick Access Buttons */
.quick-btn {
  padding: 10px 20px;
  border-radius: 8px;
  font-weight: 500;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  transition: all 0.2s;
  border: none;
  cursor: pointer;
}

.quick-btn:not(:disabled):hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

/* Dark mode */
@media (prefers-color-scheme: dark) {
  .feature-card {
    background: rgba(31, 41, 55, 0.9);
    border-color: rgba(75, 85, 99, 0.3);
  }
  
  .stat-card {
    background: rgba(31, 41, 55, 0.9);
  }
}
</style>
