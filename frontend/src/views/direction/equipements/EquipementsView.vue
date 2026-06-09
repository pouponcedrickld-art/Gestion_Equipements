<template>
  <DirectionLayout>
    <div class="equipements-page" ref="pageContainer">
      <!-- En-tête -->
      <div class="page-header animate-in">
        <div class="header-left">
          <div class="title-with-icon">
            <div class="icon-wrapper">
              <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="svg-icon">
                <path d="M9 17H5C3.89543 17 3 16.1046 3 15V5C3 3.89543 3.89543 3 5 3H19C20.1046 3 21 3.89543 21 5V15C21 16.1046 20.1046 17 19 17H15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M7 21H17" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M12 17V21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </div>
            <div>
              <h1>Gestion des Équipements</h1>
              <p class="subtitle">Suivi en temps réel de votre parc informatique</p>
            </div>
          </div>
        </div>
        <div class="header-actions">
          <Button 
            label="Importer CSV" 
            icon="pi pi-upload" 
            class="p-button-outlined p-button-secondary action-btn mr-2"
            @click="showImportDialog = true"
          />
          <Button 
            label="Scanner QR" 
            icon="pi pi-qrcode" 
            class="p-button-outlined p-button-info action-btn mr-2"
            @click="$router.push('/equipements/scan')"
          />
          <Button 
            label="Nouvel Équipement" 
            icon="pi pi-plus" 
            class="p-button-success p-button-raised action-btn"
            @click="$router.push('/equipements/nouveau')"
          />
        </div>
      </div>

      <!-- Statistiques Modernes -->
      <div class="stats-container animate-in">
        <div class="stat-glass-card total">
          <div class="stat-icon-box">
            <i class="pi pi-desktop"></i>
          </div>
          <div class="stat-details">
            <span class="value">{{ equipementStore.equipements.length }}</span>
            <span class="label">Total Parc</span>
          </div>
        </div>
        
        <div class="stat-glass-card success">
          <div class="stat-icon-box">
            <i class="pi pi-check-circle"></i>
          </div>
          <div class="stat-details">
            <span class="value">{{ statsDispo }}</span>
            <span class="label">En Stock</span>
          </div>
        </div>

        <div class="stat-glass-card warning">
          <div class="stat-icon-box">
            <i class="pi pi-user"></i>
          </div>
          <div class="stat-details">
            <span class="value">{{ statsAffectes }}</span>
            <span class="label">Affectés</span>
          </div>
        </div>

        <div class="stat-glass-card danger">
          <div class="stat-icon-box">
            <i class="pi pi-wrench"></i>
          </div>
          <div class="stat-details">
            <span class="value">{{ statsMaintenance }}</span>
            <span class="label">Maintenance</span>
          </div>
        </div>
      </div>

      <!-- Filtres Modernes -->
      <div class="filters-bar animate-in">
        <div class="search-box">
          <i class="pi pi-search"></i>
          <InputText
            v-model="searchQuery"
            placeholder="Rechercher par N° Série, Nom, Code Inventaire..."
            class="search-input"
            @input="handleSearch"
          />
        </div>
        <div class="dropdown-filters">
          <Dropdown
            v-model="selectedCategorie"
            :options="categories"
            optionLabel="nom"
            optionValue="id"
            placeholder="Filtrer par Catégorie"
            class="modern-dropdown"
            showClear
            @change="handleSearch"
          />
          <Dropdown
            v-model="selectedStatut"
            :options="statutOptions"
            optionLabel="label"
            optionValue="value"
            placeholder="Filtrer par Statut"
            class="modern-dropdown"
            showClear
            @change="handleSearch"
          />
          <SelectButton v-model="viewMode" :options="viewOptions" optionLabel="icon" optionValue="value" class="view-toggle">
            <template #option="slotProps">
              <i :class="slotProps.option.icon"></i>
            </template>
          </SelectButton>
          <Button 
            icon="pi pi-filter-slash" 
            label="Réinitialiser"
            class="p-button-text p-button-secondary"
            @click="resetFilters"
          />
        </div>
      </div>

      <!-- Liste des Équipements -->
      <div class="equipements-container" v-if="!loading">
        <!-- Vue Table -->
        <DataTable 
          v-if="viewMode === 'table' && equipementStore.equipements.length > 0"
          :value="equipementStore.equipements" 
          responsiveLayout="scroll" 
          class="modern-table"
          :rows="10" 
          paginator
          :rowsPerPageOptions="[10, 20, 50]"
        >
          <!-- ... colonnes existantes ... -->
          <Column field="code_inventaire" header="Code Inventaire" sortable>
            <template #body="{ data }">
              <code class="code-badge">{{ data.code_inventaire }}</code>
            </template>
          </Column>
          
          <Column field="nom" header="Désignation" sortable>
            <template #body="{ data }">
              <div class="flex flex-column">
                <div class="flex align-items-center gap-2">
                  <span class="font-bold text-gray-800">{{ data.nom }}</span>
                  <Tag v-if="data.is_lot" :value="`Lot x${data.quantite}`" severity="info" class="text-xs" />
                </div>
                <small class="text-gray-500">{{ data.marque }} {{ data.modele }}</small>
              </div>
            </template>
          </Column>

          <Column field="categorie.nom" header="Catégorie" sortable>
            <template #body="{ data }">
              <span class="category-pill">{{ data.categorie?.nom }}</span>
            </template>
          </Column>

          <Column field="numero_serie" header="N° Série Fabricant" sortable></Column>

          <Column field="localisation" header="Emplacement" sortable>
            <template #body="{ data }">
              <div class="flex align-items-center gap-2">
                <i class="pi pi-map-marker text-gray-400"></i>
                <span>{{ data.localisation || 'Non défini' }}</span>
              </div>
            </template>
          </Column>

          <Column field="etat" header="Statut" sortable>
            <template #body="{ data }">
              <div class="flex align-items-center gap-2">
                <span class="status-dot" :class="`bg-${getStatutColor(data.etat)}`"></span>
                <span :class="[`text-${getStatutColor(data.etat)}`, 'font-medium', 'text-sm']">
                  {{ getStatutLabel(data.etat) }}
                </span>
              </div>
            </template>
          </Column>

          <Column header="Actions" class="text-right">
            <template #body="{ data }">
              <div class="flex justify-content-end gap-1">
                <Button icon="pi pi-eye" class="p-button-text p-button-rounded p-button-info" v-tooltip.top="'Voir la fiche'" @click="viewDetails(data)" />
                <Button icon="pi pi-pencil" class="p-button-text p-button-rounded" v-tooltip.top="'Modifier'" @click="editEquipement(data)" />
                <Button icon="pi pi-qrcode" class="p-button-text p-button-rounded p-button-secondary" v-tooltip.top="'Imprimer QR'" @click="showQRCode(data)" />
                <Button icon="pi pi-trash" class="p-button-text p-button-rounded p-button-danger" v-tooltip.top="'Mettre au rebut'" @click="handleDelete(data)" />
              </div>
            </template>
          </Column>
        </DataTable>

        <!-- Vue Cartes (Grille) -->
        <div v-else-if="viewMode === 'grid' && equipementStore.equipements.length > 0" class="equipements-grid">
          <div v-for="eq in equipementStore.equipements" :key="eq.id" class="equipement-card animate-card" :class="{ 'is-lot-card': eq.is_lot }">
            <div class="card-badge-lot" v-if="eq.is_lot">LOT x{{ eq.quantite }}</div>
            <div class="card-image">
              <img v-if="eq.photo" :src="`${apiBaseUrl}/storage/${eq.photo}`" alt="Photo" />
              <div v-else class="image-placeholder">
                <i class="pi pi-desktop"></i>
              </div>
              <div class="card-status" :class="`bg-${getStatutColor(eq.etat)}`">
                {{ getStatutLabel(eq.etat) }}
              </div>
            </div>
            <div class="card-content">
              <div class="card-category">{{ eq.categorie?.nom }}</div>
              <h3 class="card-title">{{ eq.nom }}</h3>
              <p class="card-subtitle">{{ eq.marque }} {{ eq.modele }}</p>
              
              <div class="card-info">
                <div class="info-row">
                  <i class="pi pi-barcode"></i>
                  <span>{{ eq.code_inventaire }}</span>
                </div>
                <div class="info-row" v-if="eq.numero_serie">
                  <i class="pi pi-tag"></i>
                  <span>{{ eq.numero_serie }}</span>
                </div>
                <div class="info-row">
                  <i class="pi pi-map-marker"></i>
                  <span>{{ eq.localisation || 'Non défini' }}</span>
                </div>
              </div>
            </div>
            <div class="card-footer">
              <Button icon="pi pi-eye" class="p-button-text p-button-rounded" @click="viewDetails(eq)" v-tooltip.top="'Détails'" />
              <Button icon="pi pi-pencil" class="p-button-text p-button-rounded" @click="editEquipement(eq)" v-tooltip.top="'Modifier'" />
              <Button icon="pi pi-trash" class="p-button-text p-button-rounded p-button-danger" @click="handleDelete(eq)" v-tooltip.top="'Supprimer'" />
            </div>
          </div>
        </div>

        <!-- État vide -->
        <div v-else-if="equipementStore.equipements.length === 0" class="empty-state-grid">
          <i class="pi pi-box"></i>
          <p>Aucun équipement trouvé</p>
          <Button label="Ajouter un équipement" icon="pi pi-plus" class="p-button-outlined" @click="$router.push('/equipements/ajouter')" />
        </div>
      </div>

      <!-- Skeleton loading -->
      <div class="equipements-grid" v-else>
        <div v-for="n in 8" :key="n" class="equipement-card skeleton">
          <div class="card-image skeleton-animate"></div>
          <div class="card-content">
            <div class="skeleton-line w-40"></div>
            <div class="skeleton-line w-full h-2"></div>
            <div class="skeleton-line w-60"></div>
          </div>
        </div>
      </div>
    </div>
  </DirectionLayout>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useToast } from 'primevue/usetoast'
import { useEquipementStore } from '@/stores/equipementStore'
import { useCategorieStore } from '@/stores/categorieStore'
import DirectionLayout from '@/layouts/DirectionLayout.vue'
import gsap from 'gsap'

// PrimeVue Components
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import Dropdown from 'primevue/dropdown'
import SelectButton from 'primevue/selectbutton'
import Tag from 'primevue/tag'
import Dialog from 'primevue/dialog'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Tooltip from 'primevue/tooltip'

const router = useRouter()
const toast = useToast()
const equipementStore = useEquipementStore()
const categorieStore = useCategorieStore()

const loading = ref(false)
const searchQuery = ref('')
const selectedCategorie = ref(null)
const selectedStatut = ref(null)
const viewMode = ref('grid')
const viewOptions = ref([
  { icon: 'pi pi-list', value: 'table' },
  { icon: 'pi pi-th-large', value: 'grid' }
])
const apiBaseUrl = import.meta.env.VITE_API_URL?.split('/api')[0] || 'http://localhost:8000'

const statutOptions = [
  { label: 'Nouveau', value: 'nouveau' },
  { label: 'Actif', value: 'actif' },
  { label: 'En maintenance', value: 'en_maintenance' },
  { label: 'Hors service', value: 'hors_service' },
  { label: 'Archivé', value: 'archive' }
]

// Stats
const statsDispo = computed(() => equipementStore.equipements.filter(e => e.etat === 'nouveau' || e.etat === 'actif').length)
const statsAffectes = computed(() => equipementStore.equipements.filter(e => e.statut_global === 'affecte').length)
const statsMaintenance = computed(() => equipementStore.equipements.filter(e => e.etat === 'en_maintenance').length)

const categories = computed(() => categorieStore.categories)

const handleSearch = () => {
  equipementStore.fetchEquipements({
    search: searchQuery.value,
    categorie_id: selectedCategorie.value,
    etat: selectedStatut.value
  })
}

const getStatutLabel = (s) => {
  const found = statutOptions.find(opt => opt.value === s)
  return found ? found.label : s
}

const getStatutColor = (s) => {
  switch(s) {
    case 'actif': return 'green-500'
    case 'nouveau': return 'blue-500'
    case 'en_maintenance': return 'orange-500'
    case 'hors_service': return 'red-500'
    case 'archive': return 'gray-500'
    default: return 'gray-400'
  }
}

const viewDetails = (equip) => router.push(`/equipements/${equip.id}`)
const editEquipement = (equip) => router.push(`/equipements/${equip.id}/modifier`)
const showQRCode = (equip) => toast.add({ severity: 'info', summary: 'QR Code', detail: `Génération pour ${equip.code_inventaire}` })

const handleDelete = async (equip) => {
  if (confirm(`Voulez-vous vraiment mettre au rebut l'équipement ${equip.nom} ?`)) {
    try {
      await equipementStore.deleteEquipement(equip.id)
      toast.add({ severity: 'success', summary: 'Succès', detail: 'Équipement mis au rebut' })
    } catch (err) {
      toast.add({ severity: 'error', summary: 'Erreur', detail: 'Action impossible' })
    }
  }
}

const resetFilters = () => {
  searchQuery.value = ''
  selectedCategorie.value = null
  selectedStatut.value = null
  handleSearch()
}

onMounted(async () => {
  loading.value = true
  console.log('Fetching equipments...')
  try {
    await Promise.all([
      equipementStore.fetchEquipements(),
      categorieStore.fetchCategories()
    ])
    console.log('Equipments loaded:', equipementStore.equipements)
  } catch (err) {
    console.error('Error loading data:', err)
  } finally {
    loading.value = false
  }
  
  gsap.from('.animate-in', { opacity: 0, y: 30, duration: 0.8, stagger: 0.2, ease: 'power3.out' })
  gsap.from('.animate-card', { opacity: 0, scale: 0.9, duration: 0.5, stagger: 0.05, delay: 0.4 })
})
</script>

<style scoped lang="scss">
.equipements-page { padding: 1rem; }
.title-with-icon {
  display: flex; align-items: center; gap: 1rem;
  .icon-wrapper {
    width: 40px; height: 40px;
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    border-radius: 12px; display: flex; align-items: center; justify-content: center;
    color: white; box-shadow: 0 4px 8px rgba(37, 99, 235, 0.2);
    .svg-icon { width: 20px; height: 20px; }
  }
  h1 { font-size: 1.5rem; font-weight: 800; color: #1e293b; margin: 0; }
  .subtitle { color: #64748b; font-size: 0.85rem; }
}
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; }

.stats-container { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem; margin-bottom: 1.5rem; }
.stat-glass-card {
  background: white; padding: 0.75rem 1rem; border-radius: 16px; box-shadow: 0 2px 8px rgba(0,0,0,0.03);
  display: flex; align-items: center; gap: 0.75rem;
  .stat-icon-box { font-size: 1.2rem; color: #3b82f6; }
  .value { display: block; font-size: 1.2rem; font-weight: 800; }
  .label { color: #64748b; font-size: 0.75rem; }
}

.filters-bar {
  display: flex; justify-content: space-between; gap: 0.75rem; margin-bottom: 1rem;
  .search-box { flex: 1; position: relative; i { position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%); color: #64748b; font-size: 0.85rem; } .search-input { width: 100%; padding-left: 2.25rem; border-radius: 10px; border: none; box-shadow: 0 2px 6px rgba(0,0,0,0.05); font-size: 0.85rem; height: 36px; } }
  .dropdown-filters { display: flex; gap: 0.75rem; align-items: center; .modern-dropdown { border-radius: 10px; border: none; box-shadow: 0 2px 6px rgba(0,0,0,0.05); font-size: 0.85rem; height: 36px; } }
  :deep(.p-selectbutton) { .p-button { padding: 0.5rem; } }
  .action-btn { font-size: 0.85rem; padding: 0.5rem 0.75rem; }
}

.header-actions {
  display: flex; gap: 0.5rem;
  .action-btn { font-size: 0.85rem; padding: 0.5rem 0.75rem; }
}

.equipements-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
  gap: 1rem;
  margin-top: 0.5rem;
}

.equipement-card {
  background: white;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 2px 12px rgba(0,0,0,0.05);
  transition: transform 0.2s ease;
  position: relative;
  display: flex;
  flex-direction: column;

  &:hover {
    transform: translateY(-3px);
  }

  &.is-lot-card {
    border: 1.5px solid #3b82f6;
  }
}

.card-badge-lot {
  position: absolute;
  top: 0.5rem;
  left: 0.5rem;
  background: #3b82f6;
  color: white;
  padding: 0.15rem 0.5rem;
  border-radius: 12px;
  font-size: 0.65rem;
  font-weight: 800;
  z-index: 2;
}

.card-image {
  height: 120px;
  position: relative;
  background: #f8fafc;
  
  img { width: 100%; height: 100%; object-fit: contain; }
  
  .image-placeholder {
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    color: #cbd5e1;
  }

  .card-status {
    position: absolute;
    bottom: 0.5rem;
    right: 0.5rem;
    padding: 0.15rem 0.5rem;
    border-radius: 6px;
    color: white;
    font-size: 0.6rem;
    font-weight: 600;
  }
}

.card-content {
  padding: 0.75rem;
  flex: 1;

  .card-category {
    font-size: 0.6rem;
    color: #3b82f6;
    font-weight: 700;
    text-transform: uppercase;
    margin-bottom: 0.25rem;
  }

  .card-title {
    font-size: 0.95rem;
    font-weight: 700;
    color: #1e293b;
    margin: 0 0 0.15rem 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  .card-subtitle {
    font-size: 0.75rem;
    color: #64748b;
    margin-bottom: 0.5rem;
  }

  .card-info {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;

    .info-row {
      display: flex;
      align-items: center;
      gap: 0.4rem;
      font-size: 0.7rem;
      color: #475569;
      
      i { color: #94a3b8; width: 12px; font-size: 0.7rem; }
    }
  }
}

.card-footer {
  padding: 0.5rem 0.75rem;
  border-top: 1px solid #f1f5f9;
  display: flex;
  justify-content: flex-end;
  gap: 0.25rem;
  .p-button { width: 28px; height: 28px; }
}

/* Skeleton */
.skeleton-animate {
  background: linear-gradient(90deg, #f1f5f9 25%, #e2e8f0 50%, #f1f5f9 75%);
  background-size: 200% 100%;
  animation: loading 1.5s infinite;
}

.skeleton-line {
  height: 1rem;
  background: #f1f5f9;
  margin-bottom: 0.5rem;
  border-radius: 4px;
  &.w-40 { width: 40%; }
  &.w-60 { width: 60%; }
  &.w-full { width: 100%; }
  &.h-2 { height: 1.5rem; }
}

@keyframes loading {
  0% { background-position: 200% 0; }
  100% { background-position: -200% 0; }
}

.empty-state-grid {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 4rem 2rem;
  background: white;
  border-radius: 16px;
  box-shadow: 0 4px 20px rgba(0,0,0,0.05);
  text-align: center;
  
  i { font-size: 4rem; color: #cbd5e1; margin-bottom: 1rem; }
  p { font-size: 1.1rem; color: #64748b; margin-bottom: 1.5rem; }
}

.modern-table {
  :deep(.p-datatable-thead > tr > th) {
    background: #f8fafc;
    color: #475569;
    font-weight: 700;
    text-transform: uppercase;
    font-size: 0.75rem;
    letter-spacing: 0.05em;
    padding: 1.25rem 1rem;
    border: none;
  }
  
  :deep(.p-datatable-tbody > tr) {
    transition: background 0.2s;
    &:hover { background: #f1f5f9; }
  }
  
  :deep(.p-datatable-tbody > tr > td) {
    padding: 1rem;
    border-bottom: 1px solid #f1f5f9;
  }
}

.code-badge {
  background: #f1f5f9;
  padding: 0.25rem 0.5rem;
  border-radius: 6px;
  font-family: monospace;
  color: #475569;
  font-weight: 600;
}

.category-pill {
  background: #eff6ff;
  color: #3b82f6;
  padding: 0.25rem 0.75rem;
  border-radius: 20px;
  font-size: 0.85rem;
  font-weight: 600;
}

.status-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  display: inline-block;
}

.empty-state {
  text-align: center;
  padding: 5rem 2rem;
  color: #64748b;
  
  i { font-size: 4rem; margin-bottom: 1.5rem; color: #cbd5e1; }
  h3 { font-size: 1.5rem; font-weight: 700; margin-bottom: 0.5rem; color: #1e293b; }
}
</style>
