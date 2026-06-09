<template>
  <AgenceLayout>
    <div class="dashboard">
      <div class="welcome-bar">
        <div>
          <h2>Tableau de bord</h2>
          <p>Bienvenue, <strong>{{ authStore.user?.name }}</strong></p>
        </div>
        <span class="role-badge" :class="authStore.userRole">{{ roleLabel }}</span>
      </div>

    <div v-if="loading" class="loading">
      <i class="pi pi-spin pi-spinner"></i> Chargement...
    </div>

    <div v-else>
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-icon"><i class="pi pi-box"></i></div>
          <div class="stat-info">
            <h3>{{ stats.total_equipements || 0 }}</h3>
            <p>Total</p>
          </div>
        </div>
        <div class="stat-card success">
          <div class="stat-icon"><i class="pi pi-check-circle"></i></div>
          <div class="stat-info">
            <h3>{{ stats.en_stock_general || stats.en_stock_local || 0 }}</h3>
            <p>En stock</p>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon"><i class="pi pi-user"></i></div>
          <div class="stat-info">
            <h3>{{ stats.affectes || 0 }}</h3>
            <p>Affectés</p>
          </div>
        </div>
        <div class="stat-card warning">
          <div class="stat-icon"><i class="pi pi-exclamation-triangle"></i></div>
          <div class="stat-info">
            <h3>{{ stats.en_panne || 0 }}</h3>
            <p>En panne</p>
          </div>
        </div>
        <div class="stat-card info">
          <div class="stat-icon"><i class="pi pi-send"></i></div>
          <div class="stat-info">
            <h3>{{ stats.transferts_en_cours || stats.transferts_recus || 0 }}</h3>
            <p>Transferts</p>
          </div>
        </div>
        <div class="stat-card info">
          <div class="stat-icon"><i class="pi pi-clock"></i></div>
          <div class="stat-info">
            <h3>{{ stats.demandes_en_attente || 0 }}</h3>
            <p>Demandes</p>
          </div>
        </div>
      </div>

      <div v-if="authStore.isSuperAdmin || authStore.isGestionnaireGeneral" class="president-section">
        <h3><i class="pi pi-chart-bar"></i> Vue Président</h3>
        <div class="agences-stats">
          <div class="mini-card"><span class="big">{{ stats.agences_count || 0 }}</span><span>Sous-agences</span></div>
          <div class="mini-card"><span class="big">{{ stats.agents_actifs || 0 }}</span><span>Agents</span></div>
          <div class="mini-card"><span class="big">{{ stats.pannes_non_resolues || 0 }}</span><span>Pannes</span></div>
          <div class="mini-card"><span class="big">{{ stats.maintenances_planifiees || 0 }}</span><span>Maintenances</span></div>
        </div>
      </div>
    </div>
    </div>
  </AgenceLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAuthStore } from '@/stores/authStore'
import AgenceLayout from '@/layouts/AgenceLayout.vue'
import api from '@/api/axiosConfig'

const authStore = useAuthStore()
const stats = ref({})
const loading = ref(false)

const roleLabel = computed(() => ({
  super_admin: 'Super Admin',
  gestionnaire_stock_general: 'G. Stock Général',
  chef_agence: 'Chef d\'Agence',
  gestionnaire_stock: 'G. Stock Local',
  technicien_maintenance: 'Technicien',
  agent: 'Agent'
}[authStore.userRole] || authStore.userRole))

onMounted(async () => {
  loading.value = true
  try {
    const { data } = await api.get('/dashboard')
    stats.value = data.stats || {}
    if (data.user) authStore.user = { ...authStore.user, ...data.user }
  } catch (e) {
    console.error('Erreur dashboard', e)
  } finally {
    loading.value = false
  }
})
</script>

<style scoped>
.dashboard {
  padding: 20px;
}

.welcome-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 25px;
}

.welcome-bar h2 {
  margin: 0;
  color: #e2e8f0;
}

.welcome-bar p {
  margin: 5px 0 0 0;
  color: #94a3b8;
}

.role-badge {
  padding: 6px 14px;
  border-radius: 6px;
  font-size: 0.8rem;
  font-weight: bold;
  text-transform: uppercase;
  color: white;
}

.role-badge.super_admin {
  background: #ef4444;
}

.role-badge.gestionnaire_stock_general {
  background: #f59e0b;
}

.role-badge.chef_agence {
  background: #8b5cf6;
}

.role-badge.gestionnaire_stock {
  background: #06b6d4;
}

.role-badge.technicien_maintenance {
  background: #10b981;
}

.role-badge.agent {
  background: #64748b;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
  gap: 15px;
  margin-bottom: 25px;
}

.stat-card {
  background: #1e293b;
  border: 1px solid #334155;
  border-radius: 10px;
  padding: 20px;
  display: flex;
  align-items: center;
  gap: 15px;
}

.stat-card.success {
  border-color: rgba(16, 185, 129, 0.3);
}

.stat-card.warning {
  border-color: rgba(245, 158, 11, 0.3);
}

.stat-card.info {
  border-color: rgba(6, 182, 212, 0.3);
}

.stat-icon {
  width: 48px;
  height: 48px;
  background: #334155;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.4rem;
  color: #3b82f6;
}

.stat-card.success .stat-icon {
  color: #10b981;
}

.stat-card.warning .stat-icon {
  color: #f59e0b;
}

.stat-card.info .stat-icon {
  color: #06b6d4;
}

.stat-info h3 {
  margin: 0;
  font-size: 1.6rem;
  color: #e2e8f0;
}

.stat-info p {
  margin: 4px 0 0 0;
  color: #94a3b8;
  font-size: 0.85rem;
}

.president-section {
  background: #1e293b;
  border: 1px solid #334155;
  border-radius: 10px;
  padding: 20px;
}

.president-section h3 {
  margin: 0 0 15px 0;
  color: #e2e8f0;
  font-size: 1rem;
}

.president-section h3 i {
  color: #3b82f6;
  margin-right: 8px;
}

.agences-stats {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
  gap: 15px;
}

.mini-card {
  background: #0f172a;
  padding: 15px;
  border-radius: 8px;
  text-align: center;
}

.mini-card .big {
  display: block;
  font-size: 1.8rem;
  font-weight: bold;
  color: #e2e8f0;
  margin-bottom: 5px;
}

.mini-card span {
  color: #94a3b8;
  font-size: 0.85rem;
}

.loading {
  text-align: center;
  color: #94a3b8;
  padding: 40px;
}
</style>
