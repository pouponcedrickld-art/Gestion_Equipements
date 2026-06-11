<template>
  <AgenceLayout>
    <div class="stock-agences-view" ref="pageContainer">
      <!-- En-tête -->
      <div class="page-header animate-in">
        <div class="header-left">
          <div class="title-with-icon">
            <div class="icon-wrapper">
              <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="svg-icon">
                <path d="M8 7H5a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M13 10L15 12L13 14" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M16 12H8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </div>
            <div>
              <h1>Stock de l'Agence</h1>
              <p class="subtitle">Inventaire des équipements affectés à votre agence</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Contenu Stock -->
      <div class="tab-content animate-in">
        <!-- Filtres et Recherche -->
        <div class="filters-bar">
          <div class="search-box">
            <i class="pi pi-search"></i>
            <InputText v-model="searchQueryEquipements" placeholder="Rechercher un équipement..." class="search-input" />
          </div>
          <div class="dropdown-filters">
            <Dropdown v-model="selectedStatutEquipement" :options="statutEquipementOptions" optionLabel="label" optionValue="value" placeholder="État" class="modern-dropdown" showClear />
          </div>
        </div>

        <!-- Liste des Équipements (Grille de Cartes) -->
        <div class="equipements-grid" v-if="!loadingEquipements">
          <div v-for="(equipement, index) in filteredEquipements" :key="equipement.id" class="equipement-card animate-card" :style="`--index: ${index}`">
            <div class="card-status-line" :class="`equipement-${equipement.etat}`"></div>
            
            <div class="card-body">
              <div class="card-header-top">
                <span class="code-inventaire">{{ equipement.code_inventaire }}</span>
                <Tag :value="equipement.statut_global" :severity="getEquipementStatutSeverity(equipement.statut_global)" />
              </div>

              <div class="equipement-name">
                <strong>{{ equipement.nom || equipement.marque }} {{ equipement.modele }}</strong>
              </div>

              <div class="equipement-meta">
                <div class="meta-item">
                  <i class="pi pi-tag"></i>
                  <span>{{ equipement.categorie?.nom || '-' }}</span>
                </div>
                <div class="meta-item">
                  <i class="pi pi-barcode"></i>
                  <span>{{ equipement.numero_serie || '-' }}</span>
                </div>
                <div class="meta-item">
                  <i class="pi pi-home"></i>
                  <span>{{ equipement.localisation || '-' }}</span>
                </div>
              </div>
            </div>
          </div>
          
          <div v-if="filteredEquipements.length === 0" class="empty-state">
            <i class="pi pi-box"></i>
            <p>Aucun équipement trouvé dans votre stock.</p>
          </div>
        </div>

        <!-- Skeleton loading -->
        <div class="equipements-grid" v-else>
          <div v-for="n in 8" :key="n" class="equipement-card skeleton">
            <div class="skeleton-body"></div>
          </div>
        </div>
      </div>
    </div>
  </AgenceLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useEquipementStore } from '@/stores/equipementStore'
import { useAuthStore } from '@/stores/authStore'
import AgenceLayout from '@/layouts/AgenceLayout.vue'
import gsap from 'gsap'

// PrimeVue Components
import InputText from 'primevue/inputtext'
import Dropdown from 'primevue/dropdown'
import Tag from 'primevue/tag'

const equipementStore = useEquipementStore()
const authStore = useAuthStore()

// États pour les équipements (stock)
const loadingEquipements = ref(false)
const selectedStatutEquipement = ref(null)
const searchQueryEquipements = ref('')

// Options de statut pour les équipements
const statutEquipementOptions = [
  { label: 'Nouveau', value: 'nouveau' },
  { label: 'En service', value: 'en_service' },
  { label: 'En maintenance', value: 'en_maintenance' },
  { label: 'Hors service', value: 'hors_service' },
  { label: 'Archivé', value: 'archive' }
]

// Computed pour les équipements
const equipementsAgence = computed(() => {
  const list = Array.isArray(equipementStore.equipements) 
    ? equipementStore.equipements 
    : (equipementStore.equipements?.data || [])
    
  return list.filter(e => e.agence_actuelle_id === authStore.userAgence)
})

const filteredEquipements = computed(() => {
  const list = equipementsAgence.value || []
  if (searchQueryEquipements.value) {
    const q = searchQueryEquipements.value.toLowerCase()
    list = list.filter(e => 
      e.nom?.toLowerCase().includes(q) || 
      e.marque?.toLowerCase().includes(q) ||
      e.modele?.toLowerCase().includes(q) ||
      e.code_inventaire?.toLowerCase().includes(q) ||
      e.categorie?.nom?.toLowerCase().includes(q)
    )
  }
  if (selectedStatutEquipement.value) {
    list = list.filter(e => e.etat === selectedStatutEquipement.value)
  }
  return list
})

// Récupération de la sévérité pour le tag statut équipement
const getEquipementStatutSeverity = (s) => {
  switch(s) {
    case 'en_stock_general': return 'success'
    case 'en_service': return 'primary'
    case 'en_maintenance': return 'warning'
    case 'hors_service': return 'danger'
    case 'affecte': return 'info'
    default: return 'secondary'
  }
}

const fetchEquipements = async () => {
  loadingEquipements.value = true
  try {
    await equipementStore.fetchEquipements()
  } catch (err) {
    console.error('Erreur lors du chargement des équipements:', err)
  } finally {
    loadingEquipements.value = false
  }
}

// Chargement initial
onMounted(async () => {
  await fetchEquipements()
  gsap.from('.animate-in', { opacity: 0, y: 20, duration: 0.8, stagger: 0.2, ease: 'power3.out' })
  gsap.from('.animate-card', { opacity: 0, y: 20, duration: 0.6, stagger: 0.05, ease: 'power2.out' })
})
</script>

<style scoped lang="scss">
.stock-agences-view { padding: 2rem; }
.title-with-icon {
  display: flex; align-items: center; gap: 1.5rem;
  .icon-wrapper {
    width: 60px; height: 60px;
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    border-radius: 16px; display: flex; align-items: center; justify-content: center;
    color: white; box-shadow: 0 8px 16px rgba(37, 99, 235, 0.2);
    .svg-icon { width: 32px; height: 32px; }
  }
  h1 { font-size: 2rem; font-weight: 800; color: #1e293b; margin: 0; }
  .subtitle { color: #64748b; }
}
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }

.filters-bar {
  display: flex; justify-content: space-between; gap: 1.5rem; margin-bottom: 2rem;
  .search-box { flex: 1; position: relative; i { position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: #94a3b8; } .search-input { width: 100%; padding-left: 2.5rem; border-radius: 12px; border: 1px solid #e2e8f0; } }
  .dropdown-filters { :deep(.modern-dropdown) { border-radius: 12px; border: 1px solid #e2e8f0; min-width: 200px; } }
}

.equipements-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 1.5rem; }
.equipement-card {
  background: white; border-radius: 20px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.05); position: relative;
  .card-status-line { height: 4px; width: 100%; 
    &.equipement-nouveau { background: #3b82f6; } &.equipement-en_service { background: #10b981; } &.equipement-en_maintenance { background: #f59e0b; } &.equipement-hors_service { background: #ef4444; } &.equipement-archive { background: #64748b; }
  }
  .card-body { padding: 1.5rem; .card-header-top { display: flex; justify-content: space-between; .code-inventaire { font-family: monospace; font-weight: bold; color: #64748b; } } }
}

.equipement-name { margin: 1rem 0; strong { font-size: 1.1rem; color: #1e293b; } }
.equipement-meta { display: flex; flex-direction: column; gap: 0.75rem;
  .meta-item { display: flex; align-items: center; gap: 0.75rem; color: #64748b; i { color: #94a3b8; width: 20px; } }
}

.empty-state { grid-column: 1 / -1; text-align: center; padding: 4rem; color: #94a3b8; i { font-size: 3rem; margin-bottom: 1rem; } }

.skeleton { .skeleton-body { padding: 5rem; border-radius: 20px; background: linear-gradient(90deg, #f1f5f9 25%, #f8fafc 50%, #f1f5f9 75%); background-size: 200% 100%; animation: loading 1.5s infinite; } }
@keyframes loading { 0% { background-position: 200% 0; } 100% { background-position: -200% 0; } }
</style>
