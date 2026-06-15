<template>
  <MainLayout>
    <div class="retours-direction-view" ref="pageContainer">
      <!-- En-tête -->
      <div class="page-header animate-in">
        <div class="header-left">
          <div class="title-with-icon">
            <div class="icon-wrapper">
              <i class="pi pi-undo"></i>
            </div>
            <div>
              <h1>Retours de Matériel (Siège)</h1>
              <p class="subtitle">Liste des équipements refusés ou retournés par les agences</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Filtres et Recherche -->
      <div class="filters-bar animate-in">
        <div class="search-box">
          <i class="pi pi-search"></i>
          <InputText v-model="searchQuery" placeholder="Rechercher par équipement, agence..." class="search-input" />
        </div>
        <div class="dropdown-filters">
          <Dropdown v-model="selectedStatut" :options="statutOptions" optionLabel="label" optionValue="value" placeholder="Statut" class="modern-dropdown" showClear />
        </div>
      </div>

      <!-- Liste des Retours (Tableau) -->
      <div class="table-container animate-in" v-if="!loading">
        <DataTable 
          :value="filteredTransferts" 
          responsiveLayout="stack" 
          breakpoint="960px"
          stripedRows
          class="professional-table"
          :paginator="true" 
          :rows="10"
          emptyMessage="Aucun retour de matériel"
        >
          <Column field="id" header="ID" sortable>
            <template #body="slotProps">
              <span class="trans-id">#{{ slotProps.data.id }}</span>
            </template>
          </Column>

          <Column header="Équipement" sortable sortField="equipement.nom">
            <template #body="slotProps">
              <div class="equipement-cell">
                <span class="equip-name">{{ slotProps.data.equipement?.nom || slotProps.data.equipement?.marque + ' ' + slotProps.data.equipement?.modele }}</span>
                <small class="equip-sn">SN: {{ slotProps.data.equipement?.numero_serie || 'N/A' }}</small>
              </div>
            </template>
          </Column>

          <Column header="Provenance (Agence)" sortable sortField="agence_source.nom">
            <template #body="slotProps">
              <span class="agence-cell">{{ slotProps.data.agence_source?.nom || 'Inconnue' }}</span>
            </template>
          </Column>

          <Column field="date_demande" header="Date" sortable>
            <template #body="slotProps">
              {{ formatDate(slotProps.data.date_demande) }}
            </template>
          </Column>

          <Column field="statut" header="Statut" sortable>
            <template #body="slotProps">
              <Tag :value="slotProps.data.statut" :severity="getStatutSeverity(slotProps.data.statut)" />
            </template>
          </Column>

          <Column header="Motif / Observations">
            <template #body="slotProps">
              <div class="text-sm text-gray-600 truncate w-48" v-tooltip="slotProps.data.observations">
                {{ slotProps.data.observations || 'Aucun motif' }}
              </div>
            </template>
          </Column>

          <Column header="Actions">
            <template #body="slotProps">
              <div class="actions-cell">
                <Button icon="pi pi-check" v-if="['demande', 'expedie'].includes(slotProps.data.statut)" class="p-button-rounded p-button-success p-button-sm" v-tooltip="'Confirmer la réception'" @click="recevoirTransfert(slotProps.data)" />
              </div>
            </template>
          </Column>
        </DataTable>
      </div>

      <!-- Skeleton loading -->
      <div class="table-container skeleton" v-else>
        <div v-for="n in 5" :key="n" class="skeleton-row"></div>
      </div>

    </div>
  </MainLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useToast } from 'primevue/usetoast'
import { useTransfertStore } from '@/stores/transfertStore'
import MainLayout from '@/layouts/MainLayout.vue'
import gsap from 'gsap'

// PrimeVue Components
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import Dropdown from 'primevue/dropdown'
import Tag from 'primevue/tag'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'

const toast = useToast()
const transfertStore = useTransfertStore()

const loading = ref(false)
const searchQuery = ref('')
const selectedStatut = ref('')

const statutOptions = [
  { label: 'Demande / En attente', value: 'demande' },
  { label: 'En transit', value: 'expedie' },
  { label: 'Reçu par le Siège', value: 'recu' },
]

const transfertsEntrants = computed(() => {
  const list = Array.isArray(transfertStore.transferts) 
    ? transfertStore.transferts 
    : (transfertStore.transferts?.data || [])

  // Filtrer pour n'afficher que les retours (transferts vers le siège)
  return list.filter(t => t.type_transfert === 'retour_generale')
})

const filteredTransferts = computed(() => {
  let list = transfertsEntrants.value || []
  if (searchQuery.value) {
    const q = searchQuery.value.toLowerCase()
    list = list.filter(t => 
      t.id.toString().includes(q) || 
      t.equipement?.nom?.toLowerCase().includes(q) ||
      t.equipement?.marque?.toLowerCase().includes(q) ||
      t.agence_source?.nom?.toLowerCase().includes(q)
    )
  }
  if (selectedStatut.value) {
    list = list.filter(t => t.statut === selectedStatut.value)
  }
  return list
})

const getStatutSeverity = (s) => {
  switch(s) {
    case 'demande': return 'warning'
    case 'expedie': return 'info'
    case 'recu': return 'success'
    default: return 'secondary'
  }
}

const formatDate = (date) => date ? new Date(date).toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : 'N/A'

const recevoirTransfert = async (trans) => {
  try {
    await transfertStore.recevoirTransfert(trans.id)
    toast.add({ severity: 'success', summary: 'Succès', detail: 'Équipement retourné bien reçu au Siège', life: 3000 })
    await transfertStore.fetchTransferts({ type_transfert: 'retour_generale' })
  } catch (err) {
    toast.add({ severity: 'error', summary: 'Erreur', detail: 'Impossible de recevoir l\'équipement', life: 3000 })
  }
}

onMounted(async () => {
  loading.value = true
  await transfertStore.fetchTransferts({ 
    type_transfert: 'retour_generale' 
  })
  loading.value = false
  
  gsap.from('.animate-in', { opacity: 0, y: 20, duration: 0.8, stagger: 0.2, ease: 'power3.out' })
})
</script>

<style scoped lang="scss">
.retours-direction-view { padding: 2rem; }
.title-with-icon {
  display: flex; align-items: center; gap: 1.5rem;
  .icon-wrapper {
    width: 60px; height: 60px;
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    border-radius: 16px; display: flex; align-items: center; justify-content: center;
    color: white; box-shadow: 0 8px 16px rgba(16, 185, 129, 0.2);
    i { font-size: 1.8rem; }
  }
  h1 { font-size: 2rem; font-weight: 800; color: #1e293b; margin: 0; }
  .subtitle { color: #64748b; }
}
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }

.filters-bar {
  display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; gap: 1.5rem;
  .search-box {
    position: relative; flex: 1;
    i { position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: #94a3b8; z-index: 1; }
    :deep(.search-input) { width: 100%; padding-left: 2.5rem; border-radius: 12px; border: 1px solid #e2e8f0; }
  }
  .dropdown-filters { :deep(.modern-dropdown) { border-radius: 12px; border: 1px solid #e2e8f0; min-width: 220px; } }
}

.table-container {
  background: white; border-radius: 20px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); overflow: hidden; border: 1px solid #f1f5f9;
  &.skeleton { padding: 1rem; .skeleton-row { height: 60px; background: #f8fafc; margin-bottom: 0.5rem; border-radius: 8px; animation: pulse 1.5s infinite; } }
}

@keyframes pulse { 0% { opacity: 0.6; } 50% { opacity: 1; } 100% { opacity: 0.6; } }

.modern-table {
  :deep(.p-datatable-thead > tr > th) { background: #f8fafc; color: #475569; font-weight: 700; padding: 1.2rem 1rem; border-bottom: 2px solid #f1f5f9; }
  :deep(.p-datatable-tbody > tr) { transition: background 0.2s; &:hover { background: #f8fafc; } > td { padding: 1.2rem 1rem; border-bottom: 1px solid #f1f5f9; } }
}

.trans-id { font-family: monospace; font-weight: 700; color: #64748b; }
.equipement-cell { display: flex; flex-direction: column; .equip-name { font-weight: 600; color: #1e293b; } .equip-sn { color: #94a3b8; font-size: 0.75rem; } }
.agence-cell { font-weight: 600; color: #10b981; }
.actions-cell { display: flex; gap: 0.75rem; align-items: center; }

.text-sm { font-size: 0.875rem; }
.text-gray-600 { color: #4b5563; }
.truncate { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.w-48 { width: 12rem; }
</style>
