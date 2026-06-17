<template>
  <AgenceLayout>
    <div class="dashboard">
      <!-- Header -->
      <div class="dashboard-header">
        <div>
          <h1 class="dashboard-title">
            <span class="title-icon"><i class="pi pi-th-large"></i></span>
            Tableau de Bord
          </h1>
          <p class="dashboard-subtitle">
            Bonjour <strong>{{ authStore.user?.name }}</strong>
            <span class="role-badge">{{ roleLabel }}</span>
          </p>
        </div>
        <div class="header-actions">
          <div v-if="authStore.isSuperAdmin || authStore.isGestionnaireGeneral" class="agence-selector">
            <i class="pi pi-building"></i>
            <select v-model="selectedAgenceId" @change="fetchStats">
              <option value="">Toutes les agences</option>
              <option v-for="a in agences" :key="a.id" :value="a.id">{{ a.nom }}</option>
            </select>
          </div>
          <button class="refresh-btn" @click="fetchStats" :disabled="loading">
            <i class="pi pi-refresh" :class="{ 'pi-spin': loading }"></i>
            {{ lastUpdate }}
          </button>
        </div>
      </div>

      <div v-if="loading && !stats.total_equipements" class="loader">
        <div class="spinner"></div>
        <p>Chargement du tableau de bord...</p>
      </div>

      <template v-else>
        <!-- Row 1: KPI Cards -->
        <section class="kpi-grid">
          <div v-for="kpi in kpis" :key="kpi.key" class="kpi-card" :class="kpi.color">
            <div class="kpi-icon" :class="kpi.color">
              <i :class="kpi.icon"></i>
            </div>
            <div class="kpi-body">
              <span class="kpi-label">{{ kpi.label }}</span>
              <div class="kpi-value-row">
                <span class="kpi-value">{{ kpi.value }}</span>
                <span v-if="kpi.suffix" class="kpi-suffix">{{ kpi.suffix }}</span>
              </div>
            </div>
            <div v-if="kpi.trend !== undefined" class="kpi-trend" :class="kpi.trend >= 0 ? 'up' : 'down'">
              <i :class="kpi.trend >= 0 ? 'pi pi-arrow-up' : 'pi pi-arrow-down'"></i>
              {{ Math.abs(kpi.trend) }}%
            </div>
          </div>
        </section>

        <!-- Row 2: Analytics -->
        <section class="analytics-grid">
          <!-- Main chart: Category distribution -->
          <div class="card chart-card chart-main">
            <div class="card-header">
              <h3><i class="pi pi-chart-bar text-primary"></i> Équipements par Catégorie</h3>
            </div>
            <div class="chart-body">
              <Bar v-if="categoryChartData" :data="categoryChartData" :options="barOptions" />
              <div v-else class="chart-empty">Aucune donnée</div>
            </div>
          </div>

          <!-- Donut: Panne status -->
          <div class="card chart-card chart-donut">
            <div class="card-header">
              <h3><i class="pi pi-exclamation-circle text-error"></i> Statut des Pannes</h3>
            </div>
            <div class="chart-body donut-body">
              <Doughnut v-if="pannesStatutData" :data="pannesStatutData" :options="doughnutOptions" />
              <div v-else class="chart-empty">Aucune donnée</div>
            </div>
          </div>
        </section>

        <!-- Row 3: Trend + Metrics -->
        <section class="analytics-grid">
          <!-- Trend chart -->
          <div class="card chart-card chart-trend">
            <div class="card-header">
              <h3><i class="pi pi-chart-line text-warning"></i> Évolution des Pannes (14 jours)</h3>
              <div class="chart-legend-inline">
                <span class="legend-dot" style="background:#facc15"></span> Pannes
              </div>
            </div>
            <div class="chart-body">
              <Line v-if="pannesTrendData" :data="pannesTrendData" :options="lineOptions" />
              <div v-else class="chart-empty">Aucune donnée</div>
            </div>
          </div>

          <!-- Quick metrics -->
          <div class="card metrics-card">
            <div class="card-header">
              <h3><i class="pi pi-info-circle text-info"></i> Indicateurs Clés</h3>
            </div>
            <div class="metrics-body">
              <div class="metric-item">
                <span class="metric-label">Stock général</span>
                <span class="metric-value">{{ stats.en_stock_general || 0 }}</span>
              </div>
              <div class="metric-item">
                <span class="metric-label">Équipements affectés</span>
                <span class="metric-value">{{ stats.affectes || 0 }}</span>
              </div>
              <div class="metric-item">
                <span class="metric-label">En maintenance</span>
                <span class="metric-value">{{ stats.equipements_en_maintenance || 0 }}</span>
              </div>
              <div class="metric-item">
                <span class="metric-label">Temps moy. réparation</span>
                <span class="metric-value">{{ stats.temps_moyen_reparation || 0 }} <small>h</small></span>
              </div>
              <div class="metric-item">
                <span class="metric-label">Garanties à surveiller</span>
                <span class="metric-value text-warning">{{ stats.garanties_expirant || 0 }}</span>
              </div>
              <div class="metric-item">
                <span class="metric-label">Transferts en cours</span>
                <span class="metric-value">{{ stats.transferts_en_cours || 0 }}</span>
              </div>
              <div class="metric-item">
                <span class="metric-label">Demandes en attente</span>
                <span class="metric-value">{{ stats.demandes_en_attente || 0 }}</span>
              </div>
              <div class="metric-item">
                <span class="metric-label">Agents actifs</span>
                <span class="metric-value">{{ stats.agents_actifs || 0 }}</span>
              </div>
            </div>
          </div>
        </section>

        <!-- Row 4: Activity Summary -->
        <section class="card activity-card">
          <div class="card-header">
            <h3><i class="pi pi-clock text-primary"></i> Activité Récente (7 jours)</h3>
          </div>
          <div class="activity-grid">
            <div v-for="act in activityItems" :key="act.label" class="activity-stat" :class="act.class">
              <div class="activity-stat-icon" :class="act.class">
                <i :class="act.icon"></i>
              </div>
              <div class="activity-stat-body">
                <span class="activity-stat-value">{{ act.value }}</span>
                <span class="activity-stat-label">{{ act.label }}</span>
              </div>
              <div class="activity-stat-bar">
                <div class="activity-stat-bar-fill" :class="act.class" :style="{ width: act.pct + '%' }"></div>
              </div>
            </div>
          </div>
        </section>

        <!-- Row 5: Super Admin - Agency Overview -->
        <section v-if="(authStore.isSuperAdmin || authStore.isGestionnaireGeneral) && !selectedAgenceId && agencyChartData" class="card agency-card">
          <div class="card-header">
            <h3><i class="pi pi-map-marker text-primary"></i> Répartition par Agence</h3>
            <router-link to="/agences" class="btn-link">Gérer les agences <i class="pi pi-arrow-right"></i></router-link>
          </div>
          <div class="chart-body agency-chart-body">
            <Bar :data="agencyChartData" :options="barOptions" />
          </div>
        </section>
      </template>
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
  LineElement,
  Filler
} from 'chart.js'
import { Bar, Doughnut, Line } from 'vue-chartjs'

ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale, ArcElement, PointElement, LineElement, Filler)

const authStore = useAuthStore()
const stats = ref({})
const agences = ref([])
const loading = ref(false)
const selectedAgenceId = ref('')
const lastUpdate = ref('—')

const colors = {
  primary: '#facc15',
  primaryHover: '#eab308',
  success: '#10b981',
  error: '#ef4444',
  warning: '#f59e0b',
  info: '#3b82f6',
  purple: '#8b5cf6',
  cyan: '#06b6d4',
  bg: '#fbf9f4',
  card: '#ffffff',
  text: '#111827',
  muted: '#78716c',
  border: '#e2e8f0'
}

const chartDefaults = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      labels: { boxWidth: 10, padding: 16, font: { weight: '700', size: 11 }, color: colors.muted }
    },
    tooltip: {
      backgroundColor: '#1e293b',
      titleColor: '#fff',
      bodyColor: '#fff',
      cornerRadius: 8,
      padding: 10,
      bodyFont: { weight: '700' }
    }
  }
}

const barOptions = {
  ...chartDefaults,
  plugins: { ...chartDefaults.plugins, legend: { display: false } },
  scales: {
    y: {
      beginAtZero: true,
      grid: { color: '#f1f5f9', drawBorder: false },
      ticks: { color: colors.muted, font: { size: 11 } }
    },
    x: {
      grid: { display: false },
      ticks: { color: colors.muted, font: { size: 11, weight: '700' }, maxRotation: 0 }
    }
  }
}

const doughnutOptions = {
  ...chartDefaults,
  cutout: '70%',
  plugins: {
    ...chartDefaults.plugins,
    legend: { position: 'bottom', labels: { boxWidth: 10, padding: 12, font: { weight: '700', size: 10 }, color: colors.muted } }
  }
}

const lineOptions = {
  ...chartDefaults,
  plugins: { ...chartDefaults.plugins, legend: { display: false } },
  scales: {
    y: {
      beginAtZero: true,
      grid: { color: '#f1f5f9', drawBorder: false },
      ticks: { color: colors.muted, font: { size: 11 }, stepSize: 1 }
    },
    x: {
      grid: { display: false },
      ticks: { color: colors.muted, font: { size: 10 }, maxTicksLimit: 14 }
    }
  },
  interaction: { intersect: false, mode: 'index' }
}

const kpis = computed(() => [
  { key: 'equipements', label: 'Matériels', value: stats.value.total_equipements || 0, icon: 'pi pi-box', color: 'primary', trend: null },
  { key: 'pannes', label: 'Pannes Actives', value: stats.value.nombre_pannes || 0, icon: 'pi pi-exclamation-triangle', color: 'error', trend: null },
  { key: 'resolution', label: 'Taux Résolution', value: stats.value.taux_resolution || 0, icon: 'pi pi-check-circle', color: 'success', suffix: '%', trend: 2.1 },
  { key: 'budget', label: 'Budget Maintenance', value: stats.value.cout_maintenance || 0, icon: 'pi pi-euro', color: 'warning', suffix: '€', trend: null },
  { key: 'maintenance', label: 'En Maintenance', value: stats.value.equipements_en_maintenance || 0, icon: 'pi pi-wrench', color: 'info', trend: null },
  { key: 'garanties', label: 'Garanties Expirantes', value: stats.value.garanties_expirant || 0, icon: 'pi pi-shield', color: 'purple', trend: null }
])

const activityItems = computed(() => {
  const items = [
    { label: 'Transferts', value: stats.value.activite_recente?.transferts || 0, icon: 'pi pi-send', class: 'info' },
    { label: 'Affectations', value: stats.value.activite_recente?.affectations || 0, icon: 'pi pi-user', class: 'success' },
    { label: 'Pannes Signalées', value: stats.value.activite_recente?.pannes || 0, icon: 'pi pi-exclamation-circle', class: 'error' },
    { label: 'Maintenances', value: stats.value.activite_recente?.maintenances || 0, icon: 'pi pi-wrench', class: 'warning' }
  ]
  const max = Math.max(...items.map(i => i.value), 1)
  return items.map(i => ({ ...i, pct: Math.round((i.value / max) * 100) }))
})

const categoryChartData = computed(() => {
  if (!stats.value.equipements_par_categorie?.length) return null
  const palette = [colors.primary, colors.success, colors.info, colors.error, colors.purple, colors.cyan, colors.warning]
  return {
    labels: stats.value.equipements_par_categorie.map(c => c.nom),
    datasets: [{
      data: stats.value.equipements_par_categorie.map(c => c.equipements_count),
      backgroundColor: stats.value.equipements_par_categorie.map((_, i) => palette[i % palette.length]),
      borderRadius: 8,
      barThickness: 40,
      maxBarThickness: 50
    }]
  }
})

const pannesStatutData = computed(() => {
  if (!stats.value.pannes_statut?.length) return null
  const palette = [colors.error, colors.warning, colors.success, colors.info]
  return {
    labels: stats.value.pannes_statut.map(p => p.statut),
    datasets: [{
      data: stats.value.pannes_statut.map(p => p.count),
      backgroundColor: stats.value.pannes_statut.map((_, i) => palette[i % palette.length]),
      borderWidth: 0,
      hoverOffset: 8
    }]
  }
})

const pannesTrendData = computed(() => {
  if (!stats.value.pannes_trend?.length) return null
  return {
    labels: stats.value.pannes_trend.map(t => {
      const d = new Date(t.date + 'T00:00:00')
      return d.toLocaleDateString('fr-FR', { day: '2-digit', month: 'short' })
    }),
    datasets: [{
      data: stats.value.pannes_trend.map(t => t.count),
      borderColor: colors.primary,
      backgroundColor: 'rgba(250, 204, 21, 0.12)',
      borderWidth: 3,
      pointBackgroundColor: colors.card,
      pointBorderColor: colors.primary,
      pointBorderWidth: 2,
      pointRadius: 4,
      pointHoverRadius: 7,
      tension: 0.35,
      fill: true
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
      backgroundColor: stats.value.equipements_par_agence.map(() => colors.primary),
      borderRadius: 8,
      barThickness: 32
    }]
  }
})

const roleLabel = computed(() => ({
  super_admin: 'Super Admin',
  gestionnaire_stock_general: 'Gestionnaire Central',
  chef_agence: "Chef d'Agence",
  gestionnaire_stock: 'Gestionnaire Stock',
  technicien_maintenance: 'Technicien',
  agent: 'Agent'
}[authStore.userRole] || authStore.userRole))

const fetchStats = async () => {
  loading.value = true
  try {
    const params = selectedAgenceId.value ? { agence_id: selectedAgenceId.value } : {}
    const { data } = await api.get('/dashboard', { params })
    stats.value = data.stats || {}
    agences.value = data.agences || []
    lastUpdate.value = new Date().toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' })
  } catch (e) {
    console.error('Erreur dashboard', e)
  } finally {
    loading.value = false
  }
}

onMounted(fetchStats)
</script>

<style scoped>
.dashboard {
  padding: 1.5rem 2rem;
  min-height: 100vh;
  max-width: 1440px;
  margin: 0 auto;
}

/* Header */
.dashboard-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1.75rem;
  gap: 1rem;
  flex-wrap: wrap;
}

.dashboard-title {
  font-size: 1.5rem;
  font-weight: 900;
  color: var(--text-dark);
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin: 0;
}

.title-icon {
  width: 40px;
  height: 40px;
  background: var(--primary);
  border-radius: var(--radius-md);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.1rem;
  color: var(--text-dark);
  box-shadow: 0 2px 8px rgba(250, 204, 21, 0.3);
}

.dashboard-subtitle {
  color: var(--text-muted);
  margin: 0.25rem 0 0 3.25rem;
  font-size: 0.875rem;
}

.role-badge {
  display: inline-block;
  background: var(--primary-light);
  color: var(--primary-hover);
  font-size: 0.65rem;
  font-weight: 800;
  padding: 2px 8px;
  border-radius: 20px;
  margin-left: 0.5rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.header-actions {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  flex-shrink: 0;
}

.agence-selector {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  background: var(--bg-card);
  padding: 0.5rem 1rem;
  border-radius: var(--radius-md);
  border: 1px solid var(--border-color);
  color: var(--text-muted);
  font-size: 0.85rem;
}

.agence-selector select {
  border: none;
  background: transparent;
  font-weight: 700;
  color: var(--text-dark);
  cursor: pointer;
  outline: none;
  font-size: 0.85rem;
  padding-right: 0.5rem;
}

.refresh-btn {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  background: var(--bg-card);
  border: 1px solid var(--border-color);
  border-radius: var(--radius-md);
  padding: 0.5rem 1rem;
  font-size: 0.75rem;
  font-weight: 600;
  color: var(--text-muted);
  cursor: pointer;
  transition: all 0.2s;
  white-space: nowrap;
}

.refresh-btn:hover:not(:disabled) {
  border-color: var(--primary);
  color: var(--text-dark);
}

.refresh-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

/* Cards */
.card {
  background: var(--bg-card);
  border: 1px solid var(--border-color);
  border-radius: var(--radius-lg);
  box-shadow: var(--shadow-sm);
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.25rem 1.5rem 0;
}

.card-header h3 {
  font-size: 0.95rem;
  font-weight: 800;
  color: var(--text-dark);
  margin: 0;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.card-header h3 i {
  font-size: 1.1rem;
}

.chart-body {
  padding: 1.25rem 1.5rem 1.5rem;
  height: 320px;
  position: relative;
}

.chart-empty {
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--text-muted);
  font-weight: 600;
  font-size: 0.9rem;
}

.chart-legend-inline {
  display: flex;
  align-items: center;
  gap: 0.4rem;
  font-size: 0.75rem;
  font-weight: 700;
  color: var(--text-muted);
}

.legend-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  display: inline-block;
}

/* KPI Grid */
.kpi-grid {
  display: grid;
  grid-template-columns: repeat(6, 1fr);
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.kpi-card {
  background: var(--bg-card);
  border: 1px solid var(--border-color);
  border-radius: var(--radius-lg);
  padding: 1.25rem;
  box-shadow: var(--shadow-sm);
  transition: all 0.25s ease;
  position: relative;
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.kpi-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.06);
}

.kpi-icon {
  width: 40px;
  height: 40px;
  border-radius: var(--radius-md);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.15rem;
}

.kpi-icon.primary { background: var(--primary-light); color: var(--primary-hover); }
.kpi-icon.error { background: rgba(239, 68, 68, 0.12); color: var(--error); }
.kpi-icon.success { background: rgba(16, 185, 129, 0.12); color: var(--success); }
.kpi-icon.warning { background: rgba(245, 158, 11, 0.12); color: var(--warning); }
.kpi-icon.info { background: rgba(59, 130, 246, 0.12); color: var(--info); }
.kpi-icon.purple { background: rgba(139, 92, 246, 0.12); color: #8b5cf6; }

.kpi-body {
  flex: 1;
}

.kpi-label {
  font-size: 0.7rem;
  font-weight: 700;
  color: var(--text-muted);
  text-transform: uppercase;
  letter-spacing: 0.5px;
  display: block;
  margin-bottom: 0.25rem;
}

.kpi-value-row {
  display: flex;
  align-items: baseline;
  gap: 0.25rem;
}

.kpi-value {
  font-size: 1.75rem;
  font-weight: 900;
  color: var(--text-dark);
  line-height: 1;
}

.kpi-suffix {
  font-size: 0.85rem;
  font-weight: 700;
  color: var(--text-muted);
}

.kpi-trend {
  position: absolute;
  top: 1rem;
  right: 1rem;
  font-size: 0.65rem;
  font-weight: 800;
  padding: 2px 8px;
  border-radius: 20px;
  display: flex;
  align-items: center;
  gap: 2px;
}

.kpi-trend.up {
  background: rgba(16, 185, 129, 0.12);
  color: var(--success);
}

.kpi-trend.down {
  background: rgba(239, 68, 68, 0.12);
  color: var(--error);
}

/* Analytics Grid */
.analytics-grid {
  display: grid;
  grid-template-columns: 2fr 1fr;
  gap: 1.25rem;
  margin-bottom: 1.5rem;
}

.chart-main .chart-body {
  height: 350px;
}

.chart-donut .chart-body {
  height: 320px;
}

.donut-body {
  display: flex;
  align-items: center;
  justify-content: center;
}

/* Trend chart */
.chart-trend {
  grid-column: 1 / -1;
}

.chart-trend .chart-body {
  height: 280px;
}

/* Metrics Card */
.metrics-card {
  display: flex;
  flex-direction: column;
}

.metrics-body {
  padding: 1rem 1.5rem 1.5rem;
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0.25rem;
  flex: 1;
}

.metric-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.6rem 0.75rem;
  border-radius: var(--radius-md);
  transition: background 0.2s;
}

.metric-item:hover {
  background: var(--bg-app);
}

.metric-label {
  font-size: 0.75rem;
  font-weight: 600;
  color: var(--text-muted);
}

.metric-value {
  font-size: 0.9rem;
  font-weight: 800;
  color: var(--text-dark);
}

.metric-value small {
  font-weight: 600;
  color: var(--text-muted);
}

.metric-value.text-warning { color: var(--warning); }

/* Activity Card */
.activity-card {
  margin-bottom: 1.5rem;
}

.activity-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 1rem;
  padding: 1.25rem 1.5rem 1.5rem;
}

.activity-stat {
  padding: 1rem;
  border-radius: var(--radius-md);
  background: var(--bg-app);
  transition: all 0.2s;
}

.activity-stat:hover {
  transform: translateY(-2px);
}

.activity-stat-icon {
  width: 36px;
  height: 36px;
  border-radius: var(--radius-md);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1rem;
  margin-bottom: 0.75rem;
}

.activity-stat-icon.info { background: rgba(59, 130, 246, 0.12); color: var(--info); }
.activity-stat-icon.success { background: rgba(16, 185, 129, 0.12); color: var(--success); }
.activity-stat-icon.error { background: rgba(239, 68, 68, 0.12); color: var(--error); }
.activity-stat-icon.warning { background: rgba(245, 158, 11, 0.12); color: var(--warning); }

.activity-stat-body {
  display: flex;
  align-items: baseline;
  gap: 0.5rem;
  margin-bottom: 0.5rem;
}

.activity-stat-value {
  font-size: 1.5rem;
  font-weight: 900;
  color: var(--text-dark);
}

.activity-stat-label {
  font-size: 0.75rem;
  font-weight: 600;
  color: var(--text-muted);
}

.activity-stat-bar {
  height: 4px;
  background: var(--border-color);
  border-radius: 4px;
  overflow: hidden;
}

.activity-stat-bar-fill {
  height: 100%;
  border-radius: 4px;
  transition: width 0.6s ease;
}

.activity-stat-bar-fill.info { background: var(--info); }
.activity-stat-bar-fill.success { background: var(--success); }
.activity-stat-bar-fill.error { background: var(--error); }
.activity-stat-bar-fill.warning { background: var(--warning); }

/* Agency card */
.agency-card {
  margin-bottom: 0;
}

.agency-chart-body {
  height: 300px;
}

.btn-link {
  display: flex;
  align-items: center;
  gap: 0.4rem;
  font-size: 0.8rem;
  font-weight: 700;
  color: var(--text-muted);
  text-decoration: none;
  transition: color 0.2s;
}

.btn-link:hover {
  color: var(--text-dark);
}

/* Loader */
.loader {
  height: 400px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 1rem;
  color: var(--text-muted);
  font-weight: 600;
}

.spinner {
  width: 44px;
  height: 44px;
  border: 4px solid rgba(250, 204, 21, 0.2);
  border-top-color: var(--primary);
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin { to { transform: rotate(360deg); } }

/* Responsive */
@media (max-width: 1200px) {
  .kpi-grid {
    grid-template-columns: repeat(3, 1fr);
  }
}

@media (max-width: 992px) {
  .analytics-grid {
    grid-template-columns: 1fr;
  }

  .activity-grid {
    grid-template-columns: repeat(2, 1fr);
  }

  .metrics-body {
    grid-template-columns: 1fr;
  }

  .dashboard {
    padding: 1rem;
  }
}

@media (max-width: 640px) {
  .kpi-grid {
    grid-template-columns: repeat(2, 1fr);
  }

  .kpi-card {
    padding: 1rem;
  }

  .kpi-value {
    font-size: 1.35rem;
  }

  .activity-grid {
    grid-template-columns: 1fr;
  }

  .dashboard-header {
    flex-direction: column;
  }

  .header-actions {
    width: 100%;
    flex-wrap: wrap;
  }

  .agence-selector {
    flex: 1;
  }
}
</style>
