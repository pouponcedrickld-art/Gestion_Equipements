<template>
  <AgenceLayout>
    <div class="p-6 max-w-7xl mx-auto">
      <div class="flex flex-wrap items-center justify-between mb-8 gap-4">
        <div>
          <h2 class="text-2xl font-bold text-neutral-800">Tableau de bord</h2>
          <p class="text-neutral-500 mt-1">Bienvenue, <strong>{{ authStore.user?.name }}</strong></p>
        </div>
        <div class="flex items-center gap-4">
          <!-- Agence filter for global users -->
          <div v-if="authStore.isSuperAdmin || authStore.isGestionnaireGeneral" class="flex items-center gap-2">
            <label class="text-sm text-neutral-600">Agence:</label>
            <select v-model="selectedAgenceId" @change="fetchStats" class="px-3 py-2 border border-neutral-300 rounded-lg bg-white">
              <option value="">Toutes les agences</option>
              <option v-for="agence in agences" :key="agence.id" :value="agence.id">{{ agence.nom }}</option>
            </select>
          </div>
          <span class="text-sm text-neutral-400 italic">Dernière mise à jour: {{ lastUpdate }}</span>
          <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold uppercase" :class="roleBadgeClass">
            {{ roleLabel }}
          </span>
        </div>
      </div>

      <div v-if="loading" class="flex items-center justify-center py-20">
        <div class="flex items-center gap-2">
          <div class="animate-spin h-6 w-6 border-2 border-primary-500 border-t-transparent rounded-full"></div>
          <span class="text-neutral-500">Chargement...</span>
        </div>
      </div>

      <div v-else>
        <!-- Cartes Statistiques Principales -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
          <div class="card stat-card border-l-4 border-primary-500">
            <div class="stat-icon bg-primary-50 text-primary-600">
              <i class="pi pi-box text-2xl"></i>
            </div>
            <div class="stat-info">
              <h3 class="text-2xl font-bold text-neutral-800">{{ stats.total_equipements || 0 }}</h3>
              <p class="text-neutral-500 text-sm mt-1">Équipements</p>
            </div>
          </div>
          
          <div class="card stat-card border-l-4 border-danger-500">
            <div class="stat-icon bg-danger-50 text-danger-600">
              <i class="pi pi-exclamation-triangle text-2xl"></i>
            </div>
            <div class="stat-info">
              <h3 class="text-2xl font-bold text-neutral-800">{{ stats.nombre_pannes || 0 }}</h3>
              <p class="text-neutral-500 text-sm mt-1">Pannes</p>
            </div>
          </div>
          
          <div class="card stat-card border-l-4 border-success-500">
            <div class="stat-icon bg-success-50 text-success-600">
              <i class="pi pi-check-circle text-2xl"></i>
            </div>
            <div class="stat-info">
              <h3 class="text-2xl font-bold text-neutral-800">{{ stats.taux_resolution || 0 }}%</h3>
              <p class="text-neutral-500 text-sm mt-1">Taux résolution</p>
            </div>
          </div>
          
          <div class="card stat-card border-l-4 border-warning-500">
            <div class="stat-icon bg-warning-50 text-warning-600">
              <i class="pi pi-euro text-2xl"></i>
            </div>
            <div class="stat-info">
              <h3 class="text-2xl font-bold text-neutral-800">{{ stats.cout_maintenance || 0 }} €</h3>
              <p class="text-neutral-500 text-sm mt-1">Coût maintenance</p>
            </div>
          </div>
        </div>
        
        <!-- Deuxième rangée de cartes -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
          <div class="card stat-card border-l-4 border-primary-500">
            <div class="stat-icon bg-primary-50 text-primary-600">
              <i class="pi pi-wrench text-2xl"></i>
            </div>
            <div class="stat-info">
              <h3 class="text-2xl font-bold text-neutral-800">{{ stats.equipements_en_maintenance || 0 }}</h3>
              <p class="text-neutral-500 text-sm mt-1">En maintenance</p>
            </div>
          </div>
          
          <div class="card stat-card border-l-4 border-danger-500">
            <div class="stat-icon bg-danger-50 text-danger-600">
              <i class="pi pi-ban text-2xl"></i>
            </div>
            <div class="stat-info">
              <h3 class="text-2xl font-bold text-neutral-800">{{ stats.equipements_irrecuperables || 0 }}</h3>
              <p class="text-neutral-500 text-sm mt-1">Irrecupérables</p>
            </div>
          </div>
          
          <div class="card stat-card border-l-4 border-info-500">
            <div class="stat-icon bg-info-50 text-info-600">
              <i class="pi pi-clock text-2xl"></i>
            </div>
            <div class="stat-info">
              <h3 class="text-2xl font-bold text-neutral-800">{{ stats.temps_moyen_reparation || 0 }}h</h3>
              <p class="text-neutral-500 text-sm mt-1">Tps moy réparation</p>
            </div>
          </div>
          
          <div class="card stat-card border-l-4 border-orange-500">
            <div class="stat-icon bg-orange-50 text-orange-600">
              <i class="pi pi-shield text-2xl"></i>
            </div>
            <div class="stat-info">
              <h3 class="text-2xl font-bold text-neutral-800">{{ stats.garanties_expirant || 0 }}</h3>
              <p class="text-neutral-500 text-sm mt-1">Garanties expirant</p>
            </div>
          </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
          <!-- Pannes par statut -->
          <div class="lg:col-span-1 card p-6">
            <h3 class="text-lg font-semibold text-neutral-800 mb-6 flex items-center gap-2">
              <i class="pi pi-pie-chart text-primary-500"></i> Pannes par statut
            </h3>
            <div class="h-64 relative">
              <Doughnut v-if="pannesStatutData" :data="pannesStatutData" :options="pieOptions" />
              <div v-else class="flex items-center justify-center h-full text-neutral-400">
                Aucune donnée disponible
              </div>
            </div>
          </div>
          
          <!-- Équipements par catégorie -->
          <div class="lg:col-span-1 card p-6">
            <h3 class="text-lg font-semibold text-neutral-800 mb-6 flex items-center gap-2">
              <i class="pi pi-tags text-primary-500"></i> Par Catégorie
            </h3>
            <div class="h-64 relative">
              <Doughnut v-if="categoryChartData" :data="categoryChartData" :options="pieOptions" />
              <div v-else class="flex items-center justify-center h-full text-neutral-400">
                Aucune donnée disponible
              </div>
            </div>
          </div>

          <!-- Activités et Transferts -->
          <div class="lg:col-span-1 space-y-6">
            <div class="grid grid-cols-2 gap-4">
              <div class="card p-5 bg-gradient-to-br from-white to-primary-50">
                <div class="flex items-center justify-between">
                  <div>
                    <p class="text-neutral-500 text-sm font-medium">En panne</p>
                    <h3 class="text-3xl font-bold text-primary-700 mt-1">{{ stats.en_panne || 0 }}</h3>
                  </div>
                  <div class="p-3 bg-primary-100 rounded-xl text-primary-600">
                    <i class="pi pi-exclamation-triangle text-xl"></i>
                  </div>
                </div>
              </div>
              
              <div class="card p-5 bg-gradient-to-br from-white to-warning-50">
                <div class="flex items-center justify-between">
                  <div>
                    <p class="text-neutral-500 text-sm font-medium">Transferts</p>
                    <h3 class="text-3xl font-bold text-warning-700 mt-1">{{ stats.transferts_en_cours || 0 }}</h3>
                  </div>
                  <div class="p-3 bg-warning-100 rounded-xl text-warning-600">
                    <i class="pi pi-send text-xl"></i>
                  </div>
                </div>
              </div>
            </div>

            <div class="card p-6">
              <h3 class="text-lg font-semibold text-neutral-800 mb-4 flex items-center gap-2">
                <i class="pi pi-history text-primary-500"></i> Activité (7 jours)
              </h3>
              <div v-if="stats.activite_recente" class="grid grid-cols-2 gap-4">
                <div class="p-3 bg-neutral-50 rounded-lg hover:bg-primary-50 transition-colors">
                  <div class="text-xl font-bold text-neutral-800">{{ stats.activite_recente.transferts || 0 }}</div>
                  <div class="text-xs text-neutral-500 mt-1">Transferts</div>
                </div>
                <div class="p-3 bg-neutral-50 rounded-lg hover:bg-success-50 transition-colors">
                  <div class="text-xl font-bold text-neutral-800">{{ stats.activite_recente.affectations || 0 }}</div>
                  <div class="text-xs text-neutral-500 mt-1">Affectations</div>
                </div>
                <div class="p-3 bg-neutral-50 rounded-lg hover:bg-danger-50 transition-colors">
                  <div class="text-xl font-bold text-neutral-800">{{ stats.activite_recente.pannes || 0 }}</div>
                  <div class="text-xs text-neutral-500 mt-1">Pannes</div>
                </div>
                <div class="p-3 bg-neutral-50 rounded-lg hover:bg-warning-50 transition-colors">
                  <div class="text-xl font-bold text-neutral-800">{{ stats.activite_recente.maintenances || 0 }}</div>
                  <div class="text-xs text-neutral-500 mt-1">Maintenances</div>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Graphiques de tendances -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
          <div class="card p-6">
            <h3 class="text-lg font-semibold text-neutral-800 mb-6 flex items-center gap-2">
              <i class="pi pi-chart-line text-primary-500"></i> Pannes (14 jours)
            </h3>
            <div class="h-64 relative">
              <Line v-if="pannesTrendData" :data="pannesTrendData" :options="lineOptions" />
              <div v-else class="flex items-center justify-center h-full text-neutral-400">
                Chargement des données...
              </div>
            </div>
          </div>
          
          <div class="card p-6">
            <h3 class="text-lg font-semibold text-neutral-800 mb-6 flex items-center gap-2">
              <i class="pi pi-chart-bar text-success-500"></i> Maintenances (14 jours)
            </h3>
            <div class="h-64 relative">
              <Bar v-if="maintenancesTrendData" :data="maintenancesTrendData" :options="barOptions" />
              <div v-else class="flex items-center justify-center h-full text-neutral-400">
                Chargement des données...
              </div>
            </div>
          </div>
        </div>

        <!-- Vue Président / Global -->
        <div v-if="authStore.isSuperAdmin || authStore.isGestionnaireGeneral" class="space-y-6">
          <div class="card p-6">
            <h3 class="text-lg font-semibold text-neutral-800 mb-6 flex items-center gap-2">
              <i class="pi pi-chart-bar text-primary-500"></i> Répartition par Agence
            </h3>
            <div class="h-80 relative">
              <Bar v-if="agencyChartData" :data="agencyChartData" :options="barOptions" />
              <div v-else class="flex items-center justify-center h-full text-neutral-400">
                Chargement des données...
              </div>
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

// Options des graphiques
const pieOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: { position: 'bottom', labels: { boxWidth: 12, padding: 15, color: '#64748b' } }
  }
}

const barOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: { display: false }
  },
  scales: {
    y: { beginAtZero: true, grid: { display: true, color: '#f1f5f9' }, ticks: { color: '#64748b' } },
    x: { grid: { display: false }, ticks: { color: '#64748b' } }
  }
}

const lineOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: { display: false }
  },
  scales: {
    y: { beginAtZero: true, grid: { display: true, color: '#f1f5f9' }, ticks: { color: '#64748b' } },
    x: { grid: { display: false }, ticks: { color: '#64748b' } }
  }
}

// Données formatées pour les graphiques
const categoryChartData = computed(() => {
  if (!stats.value.equipements_par_categorie?.length) return null
  
  return {
    labels: stats.value.equipements_par_categorie.map(c => c.nom),
    datasets: [{
      data: stats.value.equipements_par_categorie.map(c => c.equipements_count),
      backgroundColor: [
        '#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#06b6d4', '#64748b'
      ],
      hoverOffset: 4
    }]
  }
})

const pannesStatutData = computed(() => {
  if (!stats.value.pannes_statut?.length) return null
  
  return {
    labels: stats.value.pannes_statut.map(p => p.statut),
    datasets: [{
      data: stats.value.pannes_statut.map(p => p.count),
      backgroundColor: [
        '#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6'
      ],
      hoverOffset: 4
    }]
  }
})

const pannesTrendData = computed(() => {
  if (!stats.value.pannes_trend?.length) return null
  
  return {
    labels: stats.value.pannes_trend.map(t => t.date),
    datasets: [{
      label: 'Pannes',
      data: stats.value.pannes_trend.map(t => t.count),
      borderColor: '#3b82f6',
      backgroundColor: 'rgba(59, 130, 246, 0.1)',
      tension: 0.3,
      fill: true
    }]
  }
})

const maintenancesTrendData = computed(() => {
  if (!stats.value.maintenances_trend?.length) return null
  
  return {
    labels: stats.value.maintenances_trend.map(t => t.date),
    datasets: [{
      label: 'Maintenances',
      data: stats.value.maintenances_trend.map(t => t.count),
      backgroundColor: '#10b981',
      borderRadius: 6
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
      backgroundColor: '#3b82f6',
      borderRadius: 6
    }]
  }
})

const roleLabel = computed(() => ({
  super_admin: 'Super Admin',
  gestionnaire_stock_general: 'G. Stock Général',
  chef_agence: 'Chef d\'Agence',
  gestionnaire_stock: 'G. Stock Local',
  technicien_maintenance: 'Technicien',
  agent: 'Agent'
}[authStore.userRole] || authStore.userRole))

const roleBadgeClass = computed(() => ({
  super_admin: 'bg-danger-100 text-danger-700',
  gestionnaire_stock_general: 'bg-warning-100 text-warning-700',
  chef_agence: 'bg-primary-100 text-primary-700',
  gestionnaire_stock: 'bg-success-100 text-success-700',
  technicien_maintenance: 'bg-primary-100 text-primary-700',
  agent: 'bg-neutral-100 text-neutral-700'
}[authStore.userRole] || 'bg-neutral-100 text-neutral-700'))

const fetchStats = async () => {
  loading.value = true
  try {
    const params = {}
    if (selectedAgenceId.value) {
      params.agence_id = selectedAgenceId.value
    }
    const { data } = await api.get('/dashboard', { params })
    stats.value = data.stats || {}
    agences.value = data.agences || []
    if (data.user) authStore.user = { ...authStore.user, ...data.user }
    lastUpdate.value = new Date().toLocaleTimeString()
  } catch (e) {
    console.error('Erreur dashboard', e)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchStats()
})
</script>

<style scoped>
.card {
  background: white;
  border-radius: 12px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  transition: all 0.2s;
}

.stat-card {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 20px;
}

.stat-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
}

.stat-icon {
  width: 56px;
  height: 56px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 12px;
}
</style>
