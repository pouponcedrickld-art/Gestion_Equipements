<template>
  <AgenceLayout>
    <div class="p-6 max-w-7xl mx-auto">
      <div class="flex items-center justify-between mb-8">
        <div>
          <h2 class="text-2xl font-bold text-neutral-800">Tableau de bord</h2>
          <p class="text-neutral-500 mt-1">Bienvenue, <strong>{{ authStore.user?.name }}</strong></p>
        </div>
        <div class="flex items-center gap-4">
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
        <div class="stats-grid mb-8">
          <div class="stat-card">
            <div class="stat-icon">
              <i class="pi pi-box"></i>
            </div>
            <div>
              <h3 class="text-2xl font-bold">{{ stats.total_equipements || 0 }}</h3>
              <p class="text-muted text-sm">Équipements</p>
            </div>
          </div>
          
          <div class="stat-card" style="border-left-color: var(--success)">
            <div class="stat-icon" style="color: var(--success); background: #dcfce7">
              <i class="pi pi-check-circle"></i>
            </div>
            <div>
              <h3 class="text-2xl font-bold">{{ stats.en_stock_general || stats.en_stock_local || 0 }}</h3>
              <p class="text-muted text-sm">En stock</p>
            </div>
          </div>
          
          <div class="stat-card" style="border-left-color: var(--warning)">
            <div class="stat-icon" style="color: var(--warning); background: #fef3c7">
              <i class="pi pi-user"></i>
            </div>
            <div>
              <h3 class="text-2xl font-bold">{{ stats.affectes || 0 }}</h3>
              <p class="text-muted text-sm">Affectés</p>
            </div>
          </div>
          
          <div class="stat-card" style="border-left-color: var(--error)">
            <div class="stat-icon" style="color: var(--error); background: #fee2e2">
              <i class="pi pi-exclamation-triangle"></i>
            </div>
            <div>
              <h3 class="text-2xl font-bold">{{ stats.en_panne || 0 }}</h3>
              <p class="text-muted text-sm">En panne</p>
            </div>
          </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
          <!-- Graphique par Catégorie -->
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
          <div class="lg:col-span-2 space-y-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
              <div class="card p-5 bg-gradient-to-br from-white to-primary-50">
                <div class="flex items-center justify-between">
                  <div>
                    <p class="text-neutral-500 text-sm font-medium">Transferts en cours</p>
                    <h3 class="text-3xl font-bold text-primary-700 mt-1">{{ stats.transferts_en_cours || 0 }}</h3>
                  </div>
                  <div class="p-3 bg-primary-100 rounded-xl text-primary-600">
                    <i class="pi pi-send text-xl"></i>
                  </div>
                </div>
              </div>
              
              <div class="card p-5 bg-gradient-to-br from-white to-orange-50">
                <div class="flex items-center justify-between">
                  <div>
                    <p class="text-neutral-500 text-sm font-medium">Demandes en attente</p>
                    <h3 class="text-3xl font-bold text-orange-700 mt-1">{{ stats.demandes_en_attente || 0 }}</h3>
                  </div>
                  <div class="p-3 bg-orange-100 rounded-xl text-orange-600">
                    <i class="pi pi-clock text-xl"></i>
                  </div>
                </div>
              </div>
            </div>

            <div class="card p-6">
              <h3 class="text-lg font-semibold text-neutral-800 mb-4 flex items-center gap-2">
                <i class="pi pi-history text-primary-500"></i> Activité (7 derniers jours)
              </h3>
              <div v-if="stats.activite_recente" class="grid grid-cols-2 md:grid-cols-4 gap-4">
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

          <div class="card p-6 bg-neutral-900 text-white shadow-xl overflow-hidden relative">
            <div class="absolute top-0 right-0 p-8 opacity-10">
              <i class="pi pi-shield text-9xl"></i>
            </div>
            <h3 class="text-lg font-semibold mb-6 flex items-center gap-2 relative z-10">
              <i class="pi pi-shield text-primary-400"></i> Indicateurs Stratégiques
            </h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6 relative z-10">
              <div class="space-y-1">
                <span class="text-neutral-400 text-sm">Sous-agences</span>
                <p class="text-3xl font-bold">{{ stats.agences_count || 0 }}</p>
              </div>
              <div class="space-y-1">
                <span class="text-neutral-400 text-sm">Agents Actifs</span>
                <p class="text-3xl font-bold text-success-400">{{ stats.agents_actifs || 0 }}</p>
              </div>
              <div class="space-y-1">
                <span class="text-neutral-400 text-sm">Pannes en cours</span>
                <p class="text-3xl font-bold text-danger-400">{{ stats.pannes_non_resolues || 0 }}</p>
              </div>
              <div class="space-y-1">
                <span class="text-neutral-400 text-sm">Maintenances</span>
                <p class="text-3xl font-bold text-primary-400">{{ stats.maintenances_planifiees || 0 }}</p>
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
import { Bar, Doughnut } from 'vue-chartjs'

ChartJS.register(
  Title, Tooltip, Legend, 
  BarElement, CategoryScale, LinearScale, 
  ArcElement, PointElement, LineElement
)

const authStore = useAuthStore()
const stats = ref({})
const loading = ref(false)
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

onMounted(async () => {
  loading.value = true
  try {
    const { data } = await api.get('/dashboard')
    stats.value = data.stats || {}
    if (data.user) authStore.user = { ...authStore.user, ...data.user }
    lastUpdate.value = new Date().toLocaleTimeString()
  } catch (e) {
    console.error('Erreur dashboard', e)
  } finally {
    loading.value = false
  }
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
