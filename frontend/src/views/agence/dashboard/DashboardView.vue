<template>
  <AgenceLayout>
    <div class="dashboard-bulletin-board animate-fade-in">
      <!-- Header Section -->
      <div class="dashboard-header mb-8">
        <div class="welcome-section">
          <h1 class="text-3xl font-extrabold text-dark tracking-tight flex items-center gap-3">
            <span class="p-2 bg-primary rounded-xl shadow-sm"><i class="pi pi-th-large text-dark"></i></span>
            Tableau de Bord Professionnel
          </h1>
          <p class="text-muted mt-2 font-medium">
            Bonjour, <span class="text-primary-hover font-bold">{{ authStore.user?.name }}</span> • 
            <span class="text-xs uppercase tracking-wider ml-1 opacity-75">{{ roleLabel }}</span>
          </p>
        </div>

        <div class="header-actions">
          <!-- Agence filter for global users -->
          <div v-if="authStore.isSuperAdmin || authStore.isGestionnaireGeneral" class="agence-selector">
            <i class="pi pi-building mr-2 text-primary"></i>
            <select v-model="selectedAgenceId" @change="fetchStats" class="select-clean">
              <option value="">Toutes les agences</option>
              <option v-for="agence in agences" :key="agence.id" :value="agence.id">{{ agence.nom }}</option>
            </select>
          </div>
          <div class="refresh-indicator" @click="fetchStats">
            <i class="pi pi-refresh" :class="{ 'pi-spin': loading }"></i>
            <span>Dernière MAJ: {{ lastUpdate }}</span>
          </div>
        </div>
      </div>

      <div v-if="loading && !stats.total_equipements" class="loader-overlay">
        <div class="loader-content">
          <div class="spinner"></div>
          <p>Initialisation des données...</p>
        </div>
      </div>

      <div v-else class="dashboard-grid">
        <!-- Top KPIs - Operational Health -->
        <div class="kpi-row grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
          <div class="kpi-card group" v-for="(kpi, index) in kpis" :key="index" :class="kpi.color">
            <div class="kpi-icon-box">
              <i :class="kpi.icon"></i>
            </div>
            <div class="kpi-content">
              <span class="kpi-label">{{ kpi.label }}</span>
              <div class="flex items-baseline gap-2">
                <span class="kpi-value">{{ kpi.value }}</span>
                <span class="kpi-suffix" v-if="kpi.suffix">{{ kpi.suffix }}</span>
              </div>
            </div>
            <div class="kpi-trend" v-if="kpi.trend">
              <i class="pi pi-arrow-up text-xs"></i> {{ kpi.trend }}%
            </div>
          </div>
        </div>

        <!-- Main Content Area - Bento Layout -->
        <div class="bento-grid grid grid-cols-12 gap-6">
          
          <!-- Column 1: Primary Metrics & Charts -->
          <div class="col-span-12 lg:col-span-8 space-y-6">
            
            <!-- Tendances Globales -->
            <div class="bento-card p-6 min-h-[350px]">
              <div class="card-header border-b pb-4 mb-6 flex justify-between items-center">
                <h3 class="flex items-center gap-2 font-extrabold text-dark">
                  <i class="pi pi-chart-line text-primary"></i> Analyse des Tendances
                </h3>
                <div class="flex gap-2">
                  <button class="btn-tab active">Pannes</button>
                  <button class="btn-tab">Maintenances</button>
                </div>
              </div>
              <div class="chart-container h-[250px]">
                <Line v-if="pannesTrendData" :data="pannesTrendData" :options="lineOptions" />
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <!-- Répartition par Catégorie -->
              <div class="bento-card p-6">
                <div class="card-header mb-4">
                  <h3 class="font-extrabold text-dark">Inventaire par Catégorie</h3>
                </div>
                <div class="chart-container h-[220px] flex justify-center">
                  <Doughnut v-if="categoryChartData" :data="categoryChartData" :options="pieOptions" />
                </div>
              </div>

              <!-- Répartition par Statut -->
              <div class="bento-card p-6">
                <div class="card-header mb-4">
                  <h3 class="font-extrabold text-dark">État de Santé Technique</h3>
                </div>
                <div class="chart-container h-[220px] flex justify-center">
                  <Doughnut v-if="pannesStatutData" :data="pannesStatutData" :options="pieOptions" />
                </div>
              </div>
            </div>
          </div>

          <!-- Column 2: Side Bulletin & Quick Info -->
          <div class="col-span-12 lg:col-span-4 space-y-6">
            
            <!-- Quick Action Board -->
            <div class="bento-card bulletin-card p-6 bg-dark text-white border-0">
              <h3 class="text-xl font-bold mb-4 flex items-center gap-2">
                <i class="pi pi-bolt text-primary"></i> Actions Rapides
              </h3>
              <div class="grid grid-cols-2 gap-3">
                <router-link to="/equipements/nouveau" class="action-btn">
                  <i class="pi pi-plus-circle text-xl mb-2 text-primary"></i>
                  <span>Nouveau Matériel</span>
                </router-link>
                <router-link to="/pannes" class="action-btn">
                  <i class="pi pi-exclamation-circle text-xl mb-2 text-error"></i>
                  <span>Déclarer Panne</span>
                </router-link>
              </div>
            </div>

            <!-- Activity Bulletin Board -->
            <div class="bento-card p-6 overflow-hidden">
              <div class="card-header mb-6">
                <h3 class="font-extrabold text-dark flex items-center gap-2">
                  <i class="pi pi-megaphone text-primary"></i> Journal d'Activité
                </h3>
              </div>
              <div class="activity-list space-y-4">
                <div class="activity-item" v-for="(item, key) in activityItems" :key="key">
                  <div class="activity-icon" :class="item.class">
                    <i :class="item.icon"></i>
                  </div>
                  <div class="activity-info">
                    <div class="flex justify-between items-start">
                      <span class="activity-label">{{ item.label }}</span>
                      <span class="activity-value">{{ item.value }}</span>
                    </div>
                    <div class="activity-progress-bg">
                      <div class="activity-progress-bar" :class="item.class" :style="{ width: '75%' }"></div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="mt-8 pt-4 border-t border-dashed">
                <router-link to="/rapports" class="text-primary-hover font-bold text-sm flex items-center justify-center gap-2">
                  Voir rapports complets <i class="pi pi-arrow-right text-xs"></i>
                </router-link>
              </div>
            </div>

            <!-- Secondary Metrics Card -->
            <div class="bento-card p-6">
              <h3 class="font-bold text-dark mb-4">Indicateurs de Performance</h3>
              <div class="space-y-4">
                <div class="flex items-center justify-between p-3 bg-app rounded-xl">
                  <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-info-light flex items-center justify-center text-info">
                      <i class="pi pi-clock"></i>
                    </div>
                    <span class="text-sm font-medium">Temps moy. réparation</span>
                  </div>
                  <span class="font-extrabold text-dark">{{ stats.temps_moyen_reparation || 0 }}h</span>
                </div>
                <div class="flex items-center justify-between p-3 bg-app rounded-xl">
                  <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-orange-light flex items-center justify-center text-orange-500">
                      <i class="pi pi-shield"></i>
                    </div>
                    <span class="text-sm font-medium">Garanties à surveiller</span>
                  </div>
                  <span class="font-extrabold text-dark">{{ stats.garanties_expirant || 0 }}</span>
                </div>
              </div>
            </div>

          </div>
        </div>

        <!-- Global View for Super Admin -->
        <div v-if="(authStore.isSuperAdmin || authStore.isGestionnaireGeneral) && !selectedAgenceId" class="mt-8">
          <div class="bento-card p-8">
            <div class="flex justify-between items-center mb-8">
              <h3 class="text-xl font-extrabold text-dark flex items-center gap-3">
                <i class="pi pi-map-marker text-primary"></i> Aperçu de l'Architecture Réseau
              </h3>
              <router-link to="/agences" class="btn btn-outline btn-sm">Gérer les agences</router-link>
            </div>
            <div class="h-80 w-full">
              <Bar v-if="agencyChartData" :data="agencyChartData" :options="barOptions" />
            </div>
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
import {
  Chart as ChartJS,
  Title,
  Tooltip,
  Legend,
  BarElement,
  CategoryScale,
  LinearScale,
  ArcElement,
  PointElement,
  LineElement
} from 'chart.js'
import { Bar, Doughnut, Line } from 'vue-chartjs'

ChartJS.register(
  Title, Tooltip, Legend, 
  BarElement, CategoryScale, LinearScale, 
  ArcElement, PointElement, LineElement
)

const authStore = useAuthStore()
const stats = ref({})
const agences = ref([])
const loading = ref(false)
const selectedAgenceId = ref('')
const lastUpdate = ref(new Date().toLocaleTimeString())

const kpis = computed(() => [
  { label: 'Matériels', value: stats.value.total_equipements || 0, icon: 'pi pi-box', color: 'primary', trend: 4 },
  { label: 'Pannes Actives', value: stats.value.nombre_pannes || 0, icon: 'pi pi-exclamation-triangle', color: 'error', trend: -2 },
  { label: 'Taux Résolution', value: stats.value.taux_resolution || 0, icon: 'pi pi-check-circle', color: 'success', suffix: '%', trend: 1.5 },
  { label: 'Budget Maintenance', value: stats.value.cout_maintenance || 0, icon: 'pi pi-euro', color: 'warning', suffix: '€' }
])

const activityItems = computed(() => [
  { label: 'Transferts', value: stats.value.activite_recente?.transferts || 0, icon: 'pi pi-send', class: 'warning' },
  { label: 'Affectations', value: stats.value.activite_recente?.affectations || 0, icon: 'pi pi-user', class: 'success' },
  { label: 'Pannes Signalées', value: stats.value.activite_recente?.pannes || 0, icon: 'pi pi-exclamation-circle', class: 'error' },
  { label: 'Maintenances', value: stats.value.activite_recente?.maintenances || 0, icon: 'pi pi-wrench', class: 'info' }
])

// Chart Config
const pieOptions = {
  responsive: true,
  maintainAspectRatio: false,
  cutout: '70%',
  plugins: {
    legend: { position: 'bottom', labels: { boxWidth: 10, padding: 20, font: { weight: '700', size: 11 } } }
  }
}

const barOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: { legend: { display: false } },
  scales: {
    y: { beginAtZero: true, grid: { color: '#f1f5f9', borderDash: [5, 5] } },
    x: { grid: { display: false } }
  }
}

const lineOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: { legend: { display: false } },
  scales: {
    y: { beginAtZero: true, grid: { color: '#f1f5f9' } },
    x: { grid: { display: false } }
  }
}

// Data formatters
const categoryChartData = computed(() => {
  if (!stats.value.equipements_par_categorie?.length) return null
  return {
    labels: stats.value.equipements_par_categorie.map(c => c.nom),
    datasets: [{
      data: stats.value.equipements_par_categorie.map(c => c.equipements_count),
      backgroundColor: ['#facc15', '#10b981', '#3b82f6', '#ef4444', '#8b5cf6', '#06b6d4'],
      borderWidth: 0
    }]
  }
})

const pannesStatutData = computed(() => {
  if (!stats.value.pannes_statut?.length) return null
  return {
    labels: stats.value.pannes_statut.map(p => p.statut),
    datasets: [{
      data: stats.value.pannes_statut.map(p => p.count),
      backgroundColor: ['#facc15', '#10b981', '#3b82f6', '#ef4444'],
      borderWidth: 0
    }]
  }
})

const pannesTrendData = computed(() => {
  if (!stats.value.pannes_trend?.length) return null
  return {
    labels: stats.value.pannes_trend.map(t => t.date),
    datasets: [{
      data: stats.value.pannes_trend.map(t => t.count),
      borderColor: '#facc15',
      borderWidth: 4,
      pointBackgroundColor: '#fff',
      pointBorderColor: '#facc15',
      pointBorderWidth: 2,
      pointRadius: 4,
      tension: 0.4,
      fill: true,
      backgroundColor: 'rgba(250, 204, 21, 0.05)'
    }]
  }
})

const agencyChartData = computed(() => {
  if (!stats.value.equipements_par_agence?.length) return null
  return {
    labels: stats.value.equipements_par_agence.map(a => a.nom),
    datasets: [{
      label: 'Équipements',
      data: stats.value.equipements_par_agence.map(a => a.total),
      backgroundColor: '#facc15',
      borderRadius: 12,
      barThickness: 30
    }]
  }
})

const roleLabel = computed(() => ({
  super_admin: 'Administrateur Système',
  gestionnaire_stock_general: 'Gestionnaire Stock Central',
  chef_agence: 'Responsable d\'Agence',
  gestionnaire_stock: 'Gestionnaire Stock Local',
  technicien_maintenance: 'Expert Maintenance',
  agent: 'Utilisateur Final'
}[authStore.userRole] || authStore.userRole))

const fetchStats = async () => {
  loading.value = true
  try {
    const params = selectedAgenceId.value ? { agence_id: selectedAgenceId.value } : {}
    const { data } = await api.get('/dashboard', { params })
    stats.value = data.stats || {}
    agences.value = data.agences || []
    lastUpdate.value = new Date().toLocaleTimeString()
  } catch (e) {
    console.error('Erreur dashboard', e)
  } finally {
    loading.value = false
  }
}

onMounted(fetchStats)
</script>

<style scoped>
.dashboard-bulletin-board {
  padding: 1rem 1.5rem;
  background-color: var(--bg-app);
  min-height: 100vh;
}

.dashboard-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
}

.agence-selector {
  display: flex;
  align-items: center;
  background: white;
  padding: 8px 16px;
  border-radius: var(--radius-md);
  box-shadow: var(--shadow-sm);
  border: 1px solid var(--border-color);
}

.select-clean {
  border: none;
  background: transparent;
  font-weight: 700;
  padding: 0;
  cursor: pointer;
}

.refresh-indicator {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-top: 10px;
  font-size: 0.75rem;
  color: var(--text-muted);
  cursor: pointer;
  justify-content: flex-end;
}

/* KPI Cards */
.kpi-card {
  background: white;
  padding: 1.5rem;
  border-radius: var(--radius-xl);
  display: flex;
  align-items: center;
  gap: 1.25rem;
  position: relative;
  overflow: hidden;
  box-shadow: var(--shadow-sm);
  transition: all 0.3s ease;
  border: 1px solid var(--border-color);
}

.kpi-card:hover {
  transform: translateY(-5px);
  box-shadow: var(--shadow-md);
}

.kpi-icon-box {
  width: 54px;
  height: 54px;
  border-radius: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
}

.kpi-card.primary .kpi-icon-box { background: var(--primary-light); color: var(--primary-hover); }
.kpi-card.error .kpi-icon-box { background: #fee2e2; color: #ef4444; }
.kpi-card.success .kpi-icon-box { background: #dcfce7; color: #10b981; }
.kpi-card.warning .kpi-icon-box { background: #fef3c7; color: #f59e0b; }

.kpi-label { font-size: 0.85rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; }
.kpi-value { font-size: 1.875rem; font-weight: 900; color: var(--text-dark); line-height: 1; }
.kpi-suffix { font-weight: 700; color: var(--text-muted); font-size: 1rem; }

.kpi-trend {
  position: absolute;
  top: 1rem;
  right: 1.25rem;
  font-size: 0.7rem;
  font-weight: 800;
  padding: 2px 8px;
  border-radius: 20px;
  background: #f1f5f9;
}

/* Bento Cards */
.bento-card {
  background: white;
  border-radius: var(--radius-xl);
  border: 1px solid var(--border-color);
  box-shadow: var(--shadow-sm);
  transition: box-shadow 0.3s ease;
}

.bento-card:hover {
  box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
}

.btn-tab {
  padding: 6px 16px;
  border-radius: 8px;
  font-size: 0.8rem;
  font-weight: 700;
  color: var(--text-muted);
  transition: all 0.2s;
}

.btn-tab.active {
  background: var(--primary);
  color: var(--text-dark);
}

/* Bulletin Board Elements */
.bulletin-card {
  background: #111827;
  color: white;
}

.action-btn {
  background: rgba(255, 255, 255, 0.05);
  padding: 1.25rem;
  border-radius: 16px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  border: 1px solid rgba(255, 255, 255, 0.1);
  transition: all 0.2s;
  text-align: center;
}

.action-btn:hover {
  background: rgba(255, 255, 255, 0.1);
  border-color: var(--primary);
  transform: scale(1.02);
}

.action-btn span { font-size: 0.75rem; font-weight: 700; opacity: 0.9; }

.activity-item {
  display: flex;
  gap: 1rem;
  align-items: center;
}

.activity-icon {
  width: 40px;
  height: 40px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.activity-icon.warning { background: #fffbeb; color: #d97706; }
.activity-icon.success { background: #f0fdf4; color: #16a34a; }
.activity-icon.error { background: #fef2f2; color: #dc2626; }
.activity-icon.info { background: #eff6ff; color: #2563eb; }

.activity-info { flex: 1; }
.activity-label { font-size: 0.85rem; font-weight: 700; color: var(--text-dark); }
.activity-value { font-size: 0.95rem; font-weight: 900; color: var(--text-dark); }

.activity-progress-bg {
  height: 6px;
  background: #f1f5f9;
  border-radius: 3px;
  margin-top: 6px;
  overflow: hidden;
}

.activity-progress-bar { height: 100%; border-radius: 3px; }
.activity-progress-bar.warning { background: var(--warning); }
.activity-progress-bar.success { background: var(--success); }
.activity-progress-bar.error { background: var(--error); }
.activity-progress-bar.info { background: var(--info); }

/* Loader */
.loader-overlay {
  height: 400px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.loader-content { text-align: center; }
.spinner {
  width: 50px;
  height: 50px;
  border: 4px solid var(--primary-light);
  border-top-color: var(--primary);
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin: 0 auto 1rem;
}

@keyframes spin { to { transform: rotate(360deg); } }
@keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

.animate-fade-in {
  animation: fadeIn 0.6s cubic-bezier(0.16, 1, 0.3, 1);
}

.bg-info-light { background: #e0f2fe; }
.bg-orange-light { background: #fff7ed; }
</style>
