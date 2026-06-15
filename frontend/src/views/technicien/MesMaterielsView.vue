<template>
  <AgenceLayout>
    <div class="dashboard-bulletin-board animate-fade-in">
      <!-- Header Section -->
      <div class="dashboard-header mb-8">
        <div class="welcome-section">
          <h1 class="text-3xl font-extrabold text-dark tracking-tight flex items-center gap-3">
            <span class="p-2 bg-primary rounded-xl shadow-sm"><i class="pi pi-desktop text-dark"></i></span>
            Mon Espace Matériel
          </h1>
          <p class="text-muted mt-2 font-medium">
            Consultez et gérez les équipements qui vous sont <span class="text-primary-hover font-bold">affectés</span>
          </p>
        </div>
      </div>

      <div v-if="loading" class="loader-overlay">
        <div class="loader-content">
          <div class="spinner"></div>
          <p>Chargement de vos équipements...</p>
        </div>
      </div>

      <div v-else>
        <!-- Top KPIs -->
        <div class="kpi-row grid grid-cols-1 md:grid-cols-3 gap-5 mb-8">
          <div class="kpi-card primary">
            <div class="kpi-icon-box"><i class="pi pi-box"></i></div>
            <div class="kpi-content">
              <span class="kpi-label">Total Affectations</span>
              <span class="kpi-value">{{ mesAffectations.length }}</span>
            </div>
          </div>
          <div class="kpi-card success">
            <div class="kpi-icon-box"><i class="pi pi-check-circle"></i></div>
            <div class="kpi-content">
              <span class="kpi-label">En Bon État</span>
              <span class="kpi-value">{{ countByStatus('actif') }}</span>
            </div>
          </div>
          <div class="kpi-card error">
            <div class="kpi-icon-box"><i class="pi pi-exclamation-triangle"></i></div>
            <div class="kpi-content">
              <span class="kpi-label">À Signaler</span>
              <span class="kpi-value">{{ countByStatus('en_panne') + countByStatus('en_maintenance') }}</span>
            </div>
          </div>
        </div>

        <!-- Main Content Area -->
        <div class="bento-card p-8">
          <div class="flex justify-between items-center mb-8">
            <h2 class="text-xl font-extrabold text-dark flex items-center gap-2">
              <i class="pi pi-list text-primary"></i> Liste de mes Matériels
            </h2>
            <div class="search-box relative">
              <i class="pi pi-search absolute left-3 top-1/2 -translate-y-1/2 text-muted"></i>
              <input v-model="search" placeholder="Rechercher..." class="pl-10 select-clean bg-app px-4 py-2 rounded-lg border border-color" />
            </div>
          </div>

          <div v-if="filteredAffectations.length > 0" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
            <div v-for="affectation in filteredAffectations" :key="affectation.id" class="item-bulletin-card">
              <div class="item-image">
                <img v-if="affectation.equipement?.photo" :src="`${apiBaseUrl}/storage/${affectation.equipement.photo}`" alt="Photo" />
                <div v-else class="placeholder-icon">
                  <i class="pi pi-desktop text-4xl opacity-20"></i>
                </div>
                <div class="item-status" :class="getStatutColor(affectation.equipement?.etat)">
                  {{ getStatutLabel(affectation.equipement?.etat) }}
                </div>
              </div>
              <div class="item-body">
                <div class="flex justify-between items-start mb-2">
                  <span class="item-cat">{{ affectation.equipement?.categorie?.nom }}</span>
                  <span class="item-sn">SN: {{ affectation.equipement?.numero_serie || 'N/A' }}</span>
                </div>
                <h3 class="item-name">{{ affectation.equipement?.nom }}</h3>
                <p class="item-desc">{{ affectation.equipement?.marque }} {{ affectation.equipement?.modele }}</p>
                
                <div class="item-footer mt-4 pt-4 border-t border-dashed border-color flex justify-between items-center">
                  <div class="flex items-center gap-2 text-xs text-muted">
                    <i class="pi pi-calendar"></i>
                    <span>Affecté le {{ formatDate(affectation.date_affectation) }}</span>
                  </div>
                  <button class="btn-action-small" @click="reportPanne(affectation.equipement)">
                    <i class="pi pi-megaphone"></i>
                  </button>
                </div>
              </div>
            </div>
          </div>

          <div v-else class="empty-bulletin py-20 text-center">
            <div class="empty-icon mb-4"><i class="pi pi-inbox"></i></div>
            <h3 class="text-lg font-bold text-dark">Aucun matériel trouvé</h3>
            <p class="text-muted">Vous n'avez aucun matériel affecté correspondant à votre recherche.</p>
          </div>
        </div>
      </div>
    </div>
  </AgenceLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAffectationStore } from '@/stores/affectationStore'
import AgenceLayout from '@/layouts/AgenceLayout.vue'
import { useRouter } from 'vue-router'

const affectationStore = useAffectationStore()
const router = useRouter()
const search = ref('')
const apiBaseUrl = import.meta.env.VITE_API_URL?.split('/api')[0] || 'http://localhost:8000'

const loading = computed(() => affectationStore.loading)
const mesAffectations = computed(() => affectationStore.mesAffectations)

const filteredAffectations = computed(() => {
  if (!search.value) return mesAffectations.value
  const s = search.value.toLowerCase()
  return mesAffectations.value.filter(a => 
    a.equipement?.nom?.toLowerCase().includes(s) || 
    a.equipement?.marque?.toLowerCase().includes(s) ||
    a.equipement?.numero_serie?.toLowerCase().includes(s)
  )
})

const countByStatus = (status) => {
  return mesAffectations.value.filter(a => a.equipement?.etat === status).length
}

const getStatutLabel = (etat) => {
  const options = {
    nouveau: 'Nouveau',
    actif: 'Actif',
    en_maintenance: 'Maintenance',
    en_panne: 'En Panne',
    hors_service: 'HS',
    archive: 'Archivé'
  }
  return options[etat] || etat || 'N/A'
}

const getStatutColor = (etat) => {
  const colors = {
    nouveau: 'bg-info',
    actif: 'bg-success',
    en_maintenance: 'bg-warning',
    en_panne: 'bg-error',
    hors_service: 'bg-dark',
    archive: 'bg-muted'
  }
  return colors[etat] || 'bg-muted'
}

const formatDate = (dateStr) => {
  if (!dateStr) return 'N/A'
  return new Date(dateStr).toLocaleDateString('fr-FR')
}

const reportPanne = (equipement) => {
  router.push({ name: 'Pannes', query: { equipement_id: equipement.id } })
}

onMounted(async () => {
  await affectationStore.fetchMesAffectations()
})
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

/* KPI Cards */
.kpi-card {
  background: white;
  padding: 1.5rem;
  border-radius: var(--radius-xl);
  display: flex;
  align-items: center;
  gap: 1.25rem;
  box-shadow: var(--shadow-sm);
  border: 1px solid var(--border-color);
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

.kpi-label { font-size: 0.85rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; }
.kpi-value { font-size: 1.875rem; font-weight: 900; color: var(--text-dark); line-height: 1; display: block; }

/* Bento Card / Main List Container */
.bento-card {
  background: white;
  border-radius: var(--radius-xl);
  border: 1px solid var(--border-color);
  box-shadow: var(--shadow-sm);
}

.item-bulletin-card {
  background: var(--bg-app);
  border-radius: 20px;
  overflow: hidden;
  border: 1px solid var(--border-color);
  transition: all 0.3s ease;
}

.item-bulletin-card:hover {
  transform: translateY(-5px);
  box-shadow: var(--shadow-md);
  border-color: var(--primary);
}

.item-image {
  height: 160px;
  background: white;
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
}

.item-image img {
  width: 100%;
  height: 100%;
  object-fit: contain;
  padding: 1rem;
}

.item-status {
  position: absolute;
  top: 1rem;
  right: 1rem;
  padding: 4px 12px;
  border-radius: 20px;
  color: white;
  font-size: 0.65rem;
  font-weight: 800;
  text-transform: uppercase;
}

.bg-muted { background: var(--text-muted); }

.item-body { padding: 1.5rem; }
.item-cat { font-size: 0.65rem; font-weight: 800; color: var(--primary-hover); text-transform: uppercase; }
.item-sn { font-size: 0.65rem; font-weight: 700; color: var(--text-muted); }
.item-name { font-size: 1.125rem; font-weight: 800; color: var(--text-dark); margin: 0.25rem 0; }
.item-desc { font-size: 0.85rem; color: var(--text-muted); }

.btn-action-small {
  width: 32px;
  height: 32px;
  border-radius: 8px;
  background: white;
  border: 1px solid var(--border-color);
  color: var(--error);
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-action-small:hover {
  background: #fef2f2;
  border-color: #ef4444;
  transform: scale(1.1);
}

/* Loader & States */
.loader-overlay {
  height: 400px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.spinner {
  width: 40px;
  height: 40px;
  border: 3px solid var(--primary-light);
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

.empty-icon { font-size: 3rem; color: var(--border-color); }
</style>
