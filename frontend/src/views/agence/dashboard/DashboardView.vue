<template>
  <AgenceLayout>
    <div class="p-6 max-w-7xl mx-auto">
      <div class="flex items-center justify-between mb-8">
        <div>
          <h2 class="text-2xl font-bold text-neutral-800">Tableau de bord</h2>
          <p class="text-neutral-500 mt-1">Bienvenue, <strong>{{ authStore.user?.name }}</strong></p>
        </div>
        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold uppercase" :class="roleBadgeClass">
          {{ roleLabel }}
        </span>
      </div>

      <div v-if="loading" class="flex items-center justify-center py-20">
        <div class="flex items-center gap-2">
          <div class="animate-spin h-6 w-6 border-2 border-primary-500 border-t-transparent rounded-full"></div>
          <span class="text-neutral-500">Chargement...</span>
        </div>
      </div>

      <div v-else>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
          <div class="card stat-card">
            <div class="stat-icon bg-primary-100 text-primary-600">
              <i class="pi pi-box text-2xl"></i>
            </div>
            <div class="stat-info">
              <h3 class="text-2xl font-bold text-neutral-800">{{ stats.total_equipements || 0 }}</h3>
              <p class="text-neutral-500 text-sm mt-1">Équipements</p>
            </div>
          </div>
          
          <div class="card stat-card">
            <div class="stat-icon bg-success-100 text-success-600">
              <i class="pi pi-check-circle text-2xl"></i>
            </div>
            <div class="stat-info">
              <h3 class="text-2xl font-bold text-neutral-800">{{ stats.en_stock_general || stats.en_stock_local || 0 }}</h3>
              <p class="text-neutral-500 text-sm mt-1">En stock</p>
            </div>
          </div>
          
          <div class="card stat-card">
            <div class="stat-icon bg-warning-100 text-warning-600">
              <i class="pi pi-user text-2xl"></i>
            </div>
            <div class="stat-info">
              <h3 class="text-2xl font-bold text-neutral-800">{{ stats.affectes || 0 }}</h3>
              <p class="text-neutral-500 text-sm mt-1">Affectés</p>
            </div>
          </div>
          
          <div class="card stat-card">
            <div class="stat-icon bg-danger-100 text-danger-600">
              <i class="pi pi-exclamation-triangle text-2xl"></i>
            </div>
            <div class="stat-info">
              <h3 class="text-2xl font-bold text-neutral-800">{{ stats.en_panne || 0 }}</h3>
              <p class="text-neutral-500 text-sm mt-1">En panne</p>
            </div>
          </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
          <div class="card">
            <div class="stat-icon bg-primary-100 text-primary-600">
              <i class="pi pi-send text-2xl"></i>
            </div>
            <div class="stat-info">
              <h3 class="text-2xl font-bold text-neutral-800">{{ stats.transferts_en_cours || stats.transferts_recus || 0 }}</h3>
              <p class="text-neutral-500 text-sm mt-1">Transferts en cours</p>
            </div>
          </div>
          
          <div class="card">
            <div class="stat-icon bg-neutral-100 text-neutral-600">
              <i class="pi pi-clock text-2xl"></i>
            </div>
            <div class="stat-info">
              <h3 class="text-2xl font-bold text-neutral-800">{{ stats.demandes_en_attente || 0 }}</h3>
              <p class="text-neutral-500 text-sm mt-1">Demandes en attente</p>
            </div>
          </div>
        </div>

        <div v-if="authStore.isSuperAdmin || authStore.isGestionnaireGeneral" class="card p-6">
          <h3 class="text-lg font-semibold text-neutral-800 mb-4 flex items-center gap-2">
            <i class="pi pi-chart-bar text-primary-500"></i> Vue Président
          </h3>
          <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="p-4 bg-neutral-50 rounded-lg text-center">
              <div class="text-2xl font-bold text-neutral-800">{{ stats.agences_count || 0 }}</div>
              <div class="text-sm text-neutral-500 mt-1">Sous-agences</div>
            </div>
            <div class="p-4 bg-neutral-50 rounded-lg text-center">
              <div class="text-2xl font-bold text-neutral-800">{{ stats.agents_actifs || 0 }}</div>
              <div class="text-sm text-neutral-500 mt-1">Agents</div>
            </div>
            <div class="p-4 bg-neutral-50 rounded-lg text-center">
              <div class="text-2xl font-bold text-neutral-800">{{ stats.pannes_non_resolues || 0 }}</div>
              <div class="text-sm text-neutral-500 mt-1">Pannes</div>
            </div>
            <div class="p-4 bg-neutral-50 rounded-lg text-center">
              <div class="text-2xl font-bold text-neutral-800">{{ stats.maintenances_planifiees || 0 }}</div>
              <div class="text-sm text-neutral-500 mt-1">Maintenances</div>
            </div>
          </div>
        </div>

        <div v-if="stats.activite_recente" class="card p-6">
          <h3 class="text-lg font-semibold text-neutral-800 mb-4 flex items-center gap-2">
            <i class="pi pi-history text-primary-500"></i> Activité récente (7 jours)
          </h3>
          <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="p-4 bg-neutral-50 rounded-lg text-center">
              <div class="text-2xl font-bold text-neutral-800">{{ stats.activite_recente.transferts || 0 }}</div>
              <div class="text-sm text-neutral-500 mt-1">Transferts</div>
            </div>
            <div class="p-4 bg-neutral-50 rounded-lg text-center">
              <div class="text-2xl font-bold text-neutral-800">{{ stats.activite_recente.affectations || 0 }}</div>
              <div class="text-sm text-neutral-500 mt-1">Affectations</div>
            </div>
            <div class="p-4 bg-neutral-50 rounded-lg text-center">
              <div class="text-2xl font-bold text-neutral-800">{{ stats.activite_recente.pannes || 0 }}</div>
              <div class="text-sm text-neutral-500 mt-1">Pannes</div>
            </div>
            <div class="p-4 bg-neutral-50 rounded-lg text-center">
              <div class="text-2xl font-bold text-neutral-800">{{ stats.activite_recente.maintenances || 0 }}</div>
              <div class="text-sm text-neutral-500 mt-1">Maintenances</div>
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
  } catch (e) {
    console.error('Erreur dashboard', e)
  } finally {
    loading.value = false
  }
})
</script>

<style scoped>
.stat-card {
  display: flex;
  align-items: center;
  gap: 16px;
}
</style>
