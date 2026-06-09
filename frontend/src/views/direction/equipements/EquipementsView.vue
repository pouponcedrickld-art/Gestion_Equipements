<template>
  <DirectionLayout>
    <div class="equipements-view-container">
      <div class="page-header-dashboard">
        <div class="header-title-block">
          <h1>Gestion du Matériel</h1>
          <p>Suivi et administration des équipements</p>
        </div>
        <div class="header-actions">
          <Button label="Importer" icon="pi pi-upload" class="p-button-outlined" />
          <Button label="Ajouter un équipement" icon="pi pi-plus" @click="goToCreate" />
        </div>
      </div>

      <div class="grid stats-grid">
        <div class="col-12 md:col-4">
          <div class="stat-card">
            <div class="stat-icon blue">
              <i class="pi pi-box"></i>
            </div>
            <div class="stat-content">
              <div class="stat-value">{{ stats.total || 0 }}</div>
              <div class="stat-label">Total</div>
            </div>
          </div>
        </div>
        <div class="col-12 md:col-4">
          <div class="stat-card">
            <div class="stat-icon green">
              <i class="pi pi-check-circle"></i>
            </div>
            <div class="stat-content">
              <div class="stat-value">{{ stats.disponible || 0 }}</div>
              <div class="stat-label">Disponible</div>
            </div>
          </div>
        </div>
        <div class="col-12 md:col-4">
          <div class="stat-card">
            <div class="stat-icon red">
              <i class="pi pi-exclamation-triangle"></i>
            </div>
            <div class="stat-content">
              <div class="stat-value">{{ stats.panne || 0 }}</div>
              <div class="stat-label">En panne</div>
            </div>
          </div>
        </div>
      </div>

      <Card class="list-section-card">
        <template #content>
          <div class="grid filters-section">
            <div class="field col-12 md:col-4">
              <span class="p-input-icon-left">
                <i class="pi pi-search"></i>
                <InputText v-model="searchQuery" placeholder="Rechercher (Réf, Nom, Série...)" @input="debouncedSearch" />
              </span>
            </div>
            <div class="field col-12 md:col-3">
              <Dropdown 
                v-model="selectedCategorie" 
                :options="categories" 
                optionLabel="nom" 
                optionValue="id" 
                placeholder="Catégorie"
                @change="loadData"
                clearable
              />
            </div>
            <div class="field col-12 md:col-3">
              <Dropdown 
                v-model="selectedEtat" 
                :options="etatOptions" 
                placeholder="État"
                @change="loadData"
                clearable
              />
            </div>
            <div class="col-12 md:col-2">
              <Button icon="pi pi-filter" class="w-full" @click="loadData" />
            </div>
          </div>

          <DataTable 
            :value="equipements" 
            :loading="loading" 
            responsiveLayout="scroll"
            paginator 
            :rows="20" 
            :totalRecords="totalRecords"
            @page="onPageChange"
          >
            <Column field="reference" header="Référence" sortable>
              <template #body="slotProps">
                <div class="ref-badge">{{ slotProps.data.reference }}</div>
              </template>
            </Column>
            <Column field="marque" header="Equipement" sortable>
              <template #body="slotProps">
                <div class="equipement-info">
                  <div class="equipement-name">{{ slotProps.data.marque }} {{ slotProps.data.modele }}</div>
                  <div class="equipement-details">{{ slotProps.data.categorie?.nom }}</div>
                </div>
              </template>
            </Column>
            <Column field="etat" header="État">
              <template #body="slotProps">
                <Tag :value="getEtatLabel(slotProps.data.etat)" :severity="getEtatSeverity(slotProps.data.etat)" />
              </template>
            </Column>
            <Column field="agence_actuelle" header="Localisation">
              <template #body="slotProps">
                {{ slotProps.data.agence_actuelle?.nom || 'N/A' }}
              </template>
            </Column>
            <Column header="Actions">
              <template #body="slotProps">
                <Button 
                  icon="pi pi-eye" 
                  class="p-button-text p-button-sm" 
                  @click="goToDetail(slotProps.data.id)"
                />
                <Button 
                  icon="pi pi-pencil" 
                  class="p-button-text p-button-sm" 
                  @click="goToEdit(slotProps.data.id)"
                />
              </template>
            </Column>
          </DataTable>
        </template>
      </Card>
    </div>
  </DirectionLayout>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useToast } from 'primevue/usetoast'
import DirectionLayout from '@/layouts/DirectionLayout.vue'
import { useEquipementStore } from '@/stores/equipementStore'
import { useCategorieStore } from '@/stores/categorieStore'

const router = useRouter()
const toast = useToast()
const equipementStore = useEquipementStore()
const categorieStore = useCategorieStore()

const searchQuery = ref('')
const selectedCategorie = ref(null)
const selectedEtat = ref(null)
const currentPage = ref(1)

const equipements = computed(() => equipementStore.equipements)
const loading = computed(() => equipementStore.loading)
const totalRecords = computed(() => equipementStore.total)
const categories = computed(() => categorieStore.categories)

const stats = computed(() => {
  const total = totalRecords.value
  let disponible = 0
  let panne = 0
  equipements.value.forEach(e => {
    if (e.etat === 'en_panne') panne++
    if (e.etat === 'neuf' || e.etat === 'en_service' && !e.affectation_active) disponible++
  })
  return { total, disponible, panne }
})

const etatOptions = [
  { label: 'Neuf', value: 'neuf' },
  { label: 'En service', value: 'en_service' },
  { label: 'En panne', value: 'en_panne' },
  { label: 'En maintenance', value: 'en_maintenance' },
  { label: 'Réformé', value: 'reforme' }
]

let searchTimeout = null
const debouncedSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    currentPage.value = 1
    loadData()
  }, 300)
}

const loadData = async () => {
  const params = {
    page: currentPage.value,
    per_page: 20,
    search: searchQuery.value,
    categorie_id: selectedCategorie.value,
    etat: selectedEtat.value
  }
  await equipementStore.fetchEquipements(params)
}

const onPageChange = (event) => {
  currentPage.value = event.page + 1
  loadData()
}

const goToCreate = () => {
  router.push('/equipements/create')
}
const goToEdit = (id) => {
  router.push(`/equipements/${id}/edit`)
}
const goToDetail = (id) => {
  router.push(`/equipements/${id}`)
}

const getEtatLabel = (etat) => {
  const labels = {
    neuf: 'Neuf',
    en_service: 'En Service',
    en_panne: 'En Panne',
    en_maintenance: 'Maintenance',
    reforme: 'Réformé',
    perdu: 'Perdu'
  }
  return labels[etat] || etat
}

const getEtatSeverity = (etat) => {
  const colors = {
    neuf: 'success',
    en_service: 'info',
    en_panne: 'danger',
    en_maintenance: 'warning',
    reforme: 'secondary',
    perdu: 'danger'
  }
  return colors[etat] || 'secondary'
}

onMounted(async () => {
  await categorieStore.fetchCategories({ per_page: 100 })
  loadData()
})
</script>

<style scoped>
.equipements-view-container {
  padding: 1.5rem;
}

.page-header-dashboard {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}

.page-header-dashboard .header-title-block h1 {
  margin: 0;
  font-size: 2rem;
  font-weight: 800;
  color: #e2e8f0;
}
.page-header-dashboard .header-title-block p {
  margin: 0.25rem 0 0 0;
  color: #94a3b8;
}

.stats-grid {
  margin-bottom: 1.5rem;
}

.stat-card {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1.5rem;
  background: #1e293b;
  border-radius: 20px;
  border: 1px solid #334155;
}

.stat-card .stat-icon {
  width: 60px;
  height: 60px;
  border-radius: 18px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
}

.stat-card .stat-icon.blue { background: rgba(59, 130, 246, 0.2); color: #3b82f6; }
.stat-card .stat-icon.green { background: rgba(16, 185, 129, 0.2); color: #10b981; }
.stat-card .stat-icon.red { background: rgba(239, 68, 68, 0.2); color: #ef4444; }

.stat-card .stat-content .stat-value {
  font-size: 1.75rem;
  font-weight: 800;
  color: #e2e8f0;
}
.stat-card .stat-content .stat-label {
  font-size: 0.9rem;
  color: #94a3b8;
}

.list-section-card {
  background: #1e293b;
  border-radius: 30px;
  border: 1px solid #334155;
}

.filters-section {
  margin-bottom: 1.5rem;
}

.ref-badge {
  display: inline-block;
  padding: 0.25rem 0.75rem;
  background: #0f172a;
  border-radius: 20px;
  font-weight: 700;
  color: #3b82f6;
  font-family: monospace;
}

.equipement-info .equipement-name {
  font-weight: 700;
  color: #e2e8f0;
}
.equipement-info .equipement-details {
  font-size: 0.85rem;
  color: #94a3b8;
}

:deep(.p-datatable) {
  background: transparent;
}
:deep(.p-datatable .p-datatable-tbody > tr) {
  background: #0f172a;
  border-bottom: 1px solid #1e293b;
}
:deep(.p-datatable .p-datatable-header), 
:deep(.p-paginator) {
  background: #1e293b;
  border: none;
  color: #e2e8f0;
}
:deep(.p-dropdown) {
  background: #0f172a;
  border-color: #334155;
  color: #e2e8f0;
}
</style>
