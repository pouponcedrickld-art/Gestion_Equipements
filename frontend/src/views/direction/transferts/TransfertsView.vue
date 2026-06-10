<template>
  <DirectionLayout>
    <div class="transferts-view" ref="pageContainer">
      <!-- En-tête Moderne -->
      <div class="page-header animate-in">
        <div class="header-left">
          <div class="title-with-icon">
            <div class="icon-wrapper">
              <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="svg-icon">
                <path d="M8 7L12 3M12 3L16 7M12 3V13M16 17L12 21M12 21L8 17M12 21V11" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </div>
            <div>
              <h1>Flux de Transferts</h1>
              <p class="subtitle">Mouvements inter-agences et affectations</p>
            </div>
          </div>
        </div>
        <div class="header-actions">
          <Button 
            label="Nouveau Transfert" 
            icon="pi pi-plus" 
            class="p-button-success p-button-raised action-btn"
            @click="$router.push('/transferts/nouveau')"
          />
        </div>
      </div>

      <!-- Statistiques Glassmorphism -->
      <div class="stats-container animate-in">
        <div class="stat-glass-card warning">
          <div class="stat-icon-box"><i class="pi pi-clock"></i></div>
          <div class="stat-details">
            <span class="value">{{ transfertsEnAttente.length }}</span>
            <span class="label">En Attente</span>
          </div>
        </div>
        <div class="stat-glass-card primary">
          <div class="stat-icon-box"><i class="pi pi-send"></i></div>
          <div class="stat-details">
            <span class="value">{{ transfertsEnTransit.length }}</span>
            <span class="label">En Transit</span>
          </div>
        </div>
        <div class="stat-glass-card success">
          <div class="stat-icon-box"><i class="pi pi-check"></i></div>
          <div class="stat-details">
            <span class="value">{{ transfertsTermines.length }}</span>
            <span class="label">Terminés</span>
          </div>
        </div>
      </div>

      <!-- Filtres et Recherche -->
      <div class="filters-bar animate-in">
        <div class="search-box">
          <i class="pi pi-search"></i>
          <InputText v-model="searchQuery" placeholder="Rechercher un transfert..." class="search-input" />
        </div>
        <div class="dropdown-filters">
          <Dropdown v-model="selectedStatut" :options="statutOptions" optionLabel="label" optionValue="value" placeholder="Statut" class="modern-dropdown" showClear />
        </div>
      </div>

      <!-- Liste des Transferts (Cards) -->
      <div class="transferts-grid" v-if="!loading">
        <div v-for="(trans, index) in filteredTransferts" :key="trans.id" class="transfert-card animate-card" :style="`--index: ${index}`">
          <div class="card-status-line" :class="trans.statut"></div>
          
          <div class="card-body">
            <div class="card-header-top">
              <span class="trans-no">{{ trans.numero_transfert || '#' + trans.id }}</span>
              <Tag :value="trans.statut" :severity="getStatutSeverity(trans.statut)" />
            </div>

            <div class="route-display">
              <div class="node">
                <i class="pi pi-building"></i>
                <span>{{ trans.agence_origine?.nom || 'Origine' }}</span>
              </div>
              <div class="path">
                <div class="line"></div>
                <i class="pi pi-chevron-right"></i>
              </div>
              <div class="node">
                <i class="pi pi-map-marker"></i>
                <span>{{ trans.agence_destination?.nom || 'Destination' }}</span>
              </div>
            </div>

            <div class="equipement-info-box">
              <div class="equip-icon"><i class="pi pi-desktop"></i></div>
              <div class="equip-details">
                <strong>{{ trans.equipement?.marque }} {{ trans.equipement?.modele }}</strong>
                <span>SN: {{ trans.equipement?.numero_serie }}</span>
              </div>
            </div>

            <div class="card-meta">
              <div class="meta-item">
                <i class="pi pi-calendar"></i>
                <span>{{ formatDate(trans.date_demande) }}</span>
              </div>
            </div>
          </div>

          <div class="card-actions-bar">
            <Button icon="pi pi-eye" class="p-button-text p-button-rounded p-button-info" @click="viewDetails(trans)" />
            <Button v-if="trans.statut === 'demande'" icon="pi pi-check" class="p-button-text p-button-rounded p-button-success" @click="approveTransfert(trans)" />
            <Button v-if="trans.statut === 'approuve'" icon="pi pi-send" class="p-button-text p-button-rounded p-button-warning" @click="shipTransfert(trans)" />
            <Button v-if="trans.statut === 'expedie'" icon="pi pi-inbox" class="p-button-text p-button-rounded p-button-primary" @click="receiveTransfert(trans)" />
          </div>
        </div>
      </div>

      <!-- Skeleton loading -->
      <div class="transferts-grid" v-else>
        <div v-for="n in 6" :key="n" class="transfert-card skeleton">
          <div class="skeleton-body"></div>
        </div>
      </div>
    </div>
  </DirectionLayout>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useToast } from 'primevue/usetoast'
import { useTransfertStore } from '@/stores/transfertStore'
import DirectionLayout from '@/layouts/DirectionLayout.vue'
import gsap from 'gsap'

// PrimeVue Components
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import Dropdown from 'primevue/dropdown'
import Tag from 'primevue/tag'

const router = useRouter()
const toast = useToast()
const transfertStore = useTransfertStore()

const loading = ref(false)
const selectedStatut = ref(null)
const searchQuery = ref('')

const transfertsEnAttente = computed(() => transfertStore.transfertsEnAttente)
const transfertsEnTransit = computed(() => transfertStore.transfertsEnTransit)
const transfertsTermines = computed(() => transfertStore.transfertsTermines)

const filteredTransferts = computed(() => {
  let list = transfertStore.transferts
  if (searchQuery.value) {
    const q = searchQuery.value.toLowerCase()
    list = list.filter(t => t.numero_transfert?.toLowerCase().includes(q) || t.equipement?.marque?.toLowerCase().includes(q))
  }
  if (selectedStatut.value) {
    list = list.filter(t => t.statut === selectedStatut.value)
  }
  return list
})

const statutOptions = [
  { label: 'Demande', value: 'demande' },
  { label: 'Approuvé', value: 'approuve' },
  { label: 'Expédié', value: 'expedie' },
  { label: 'Reçu', value: 'recu' },
  { label: 'Refusé', value: 'refuse' }
]

const getStatutSeverity = (s) => {
  switch(s) {
    case 'demande': return 'warning'
    case 'approuve': return 'info'
    case 'expedie': return 'primary'
    case 'recu': return 'success'
    case 'refuse': return 'danger'
    default: return 'secondary'
  }
}

const formatDate = (date) => date ? new Date(date).toLocaleDateString() : 'N/A'

const viewDetails = (trans) => router.push(`/transferts/${trans.id}`)
const approveTransfert = async (trans) => {
  try {
    await transfertStore.approuverTransfert(trans.id)
    toast.add({ severity: 'success', summary: 'Succès', detail: 'Transfert approuvé' })
  } catch (err) {
    toast.add({ severity: 'error', summary: 'Erreur', detail: 'Échec de l\'approbation' })
  }
}

onMounted(async () => {
  loading.value = true
  await transfertStore.fetchTransferts()
  loading.value = false
  
  gsap.from('.animate-in', { opacity: 0, y: 20, duration: 0.8, stagger: 0.2, ease: 'power3.out' })
  gsap.from('.animate-card', { opacity: 0, y: 20, duration: 0.6, stagger: 0.05, ease: 'power2.out' })
})
</script>

<style scoped lang="scss">
.transferts-view { padding: 2rem; }
.title-with-icon {
  display: flex; align-items: center; gap: 1.5rem;
  .icon-wrapper {
    width: 60px; height: 60px;
    background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
    border-radius: 16px; display: flex; align-items: center; justify-content: center;
    color: white; box-shadow: 0 8px 16px rgba(79, 70, 229, 0.2);
    .svg-icon { width: 32px; height: 32px; }
  }
  h1 { font-size: 2rem; font-weight: 800; color: #1e293b; margin: 0; }
  .subtitle { color: #64748b; }
}
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }

.stats-container { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 3rem; }
.stat-glass-card {
  background: white; padding: 1.5rem; border-radius: 20px; box-shadow: 0 4px 12px rgba(0,0,0,0.03);
  display: flex; align-items: center; gap: 1rem;
  .stat-icon-box { font-size: 1.5rem; color: #6366f1; }
  .value { display: block; font-size: 1.5rem; font-weight: 800; }
  .label { color: #64748b; font-size: 0.8rem; }
}

.route-display {
  display: flex; align-items: center; justify-content: space-between; margin: 1.5rem 0; padding: 1rem; background: #f8fafc; border-radius: 12px;
  .node { display: flex; flex-direction: column; align-items: center; gap: 0.3rem; i { color: #6366f1; } span { font-size: 0.8rem; font-weight: 600; } }
  .path { flex: 1; display: flex; align-items: center; padding: 0 1rem; .line { height: 2px; flex: 1; background: #e2e8f0; } i { color: #cbd5e1; margin-left: -5px; } }
}

.equipement-info-box {
  display: flex; align-items: center; gap: 1rem; padding: 1rem; border: 1px solid #f1f5f9; border-radius: 12px; margin-bottom: 1rem;
  .equip-icon { width: 40px; height: 40px; background: #eef2ff; border-radius: 10px; display: flex; align-items: center; justify-content: center; color: #6366f1; }
  .equip-details { display: flex; flex-direction: column; strong { font-size: 0.9rem; } span { font-size: 0.75rem; color: #64748b; } }
}

.transferts-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 1.5rem; }
.transfert-card {
  background: white; border-radius: 20px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.05); position: relative;
  .card-status-line { height: 4px; width: 100%; &.demande { background: #f59e0b; } &.expedie { background: #3b82f6; } &.recu { background: #10b981; } }
  .card-body { padding: 1.5rem; .card-header-top { display: flex; justify-content: space-between; .trans-no { font-family: monospace; font-weight: bold; color: #64748b; } } }
  .card-actions-bar { display: flex; justify-content: space-around; padding: 1rem; border-top: 1px solid #f1f5f9; }
}
</style>
