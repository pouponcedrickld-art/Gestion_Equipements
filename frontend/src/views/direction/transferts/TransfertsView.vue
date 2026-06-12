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

      <!-- Section des demandes approuvées à transférer -->
      <div class="approved-demandes-section animate-in" v-if="approvedDemandes.length > 0">
        <div class="section-header">
          <i class="pi pi-verified"></i>
          <h3>Demandes de matériel approuvées à transférer</h3>
          <span class="count-badge">{{ approvedDemandes.length }}</span>
        </div>
        
        <div class="demandes-horizontal-scroll">
          <div v-for="demande in approvedDemandes" :key="demande.id" class="approved-demande-card">
            <div class="demande-header">
              <span class="agence-name">{{ demande.agence?.nom }}</span>
              <span class="date">{{ formatDate(demande.created_at) }}</span>
            </div>
            <div class="demande-body">
              <strong>{{ demande.equipement?.nom }}</strong>
              <div class="demande-meta">
                <span>Qté: {{ demande.quantite }}</span>
                <span class="urgency" :class="demande.urgence.toLowerCase()">{{ demande.urgence }}</span>
              </div>
            </div>
            <div class="demande-footer">
              <Button 
                label="Lancer le transfert" 
                icon="pi pi-external-link" 
                class="p-button-sm p-button-outlined"
                @click="createTransfertFromDemande(demande)"
              />
            </div>
          </div>
        </div>
      </div>

      <!-- Statistiques Glassmorphism (commenté sur demande) -->
      <!-- <div class="stats-container animate-in">
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
      </div> -->

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

      <!-- Liste des Transferts (Tableau) -->
      <div class="table-container animate-in" v-if="!loading">
        <DataTable 
          :value="filteredTransferts" 
          responsiveLayout="stack" 
          breakpoint="960px"
          stripedRows
          class="professional-table"
          :paginator="true" 
          :rows="10"
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

          <Column header="Source" sortable sortField="agence_source.nom">
            <template #body="slotProps">
              <span class="agence-cell source">{{ slotProps.data.agence_source?.nom || 'Siège' }}</span>
            </template>
          </Column>

          <Column header="Destination" sortable sortField="agence_destination.nom">
            <template #body="slotProps">
              <span class="agence-cell destination">{{ slotProps.data.agence_destination?.nom }}</span>
            </template>
          </Column>

          <Column field="date_demande" header="Date Demande" sortable>
            <template #body="slotProps">
              {{ formatDate(slotProps.data.date_demande) }}
            </template>
          </Column>

          <Column field="statut" header="Statut" sortable>
            <template #body="slotProps">
              <Tag :value="slotProps.data.statut" :severity="getStatutSeverity(slotProps.data.statut)" />
            </template>
          </Column>

          <Column header="Actions">
            <template #body="slotProps">
              <div class="actions-cell">
                <Button icon="pi pi-eye" class="p-button-text p-button-rounded p-button-info" @click="viewDetails(slotProps.data)" v-tooltip.top="'Voir détails'" />
                <Button v-if="slotProps.data.statut === 'demande'" icon="pi pi-check" class="p-button-text p-button-rounded p-button-success" @click="approveTransfert(slotProps.data)" v-tooltip.top="'Approuver'" />
                <Button v-if="slotProps.data.statut === 'approuve'" icon="pi pi-send" class="p-button-text p-button-rounded p-button-warning" @click="shipTransfert(slotProps.data)" v-tooltip.top="'Expédier'" />
                <Button v-if="slotProps.data.statut === 'expedie'" icon="pi pi-inbox" class="p-button-text p-button-rounded p-button-primary" @click="receiveTransfert(slotProps.data)" v-tooltip.top="'Recevoir'" />
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
  </DirectionLayout>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useToast } from 'primevue/usetoast'
import { useTransfertStore } from '@/stores/transfertStore'
import DirectionLayout from '@/layouts/DirectionLayout.vue'
import transfertApi from '@/api/transfertApi' 
import gsap from 'gsap'

// PrimeVue Components
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import Dropdown from 'primevue/dropdown'
import Tag from 'primevue/tag'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'

const router = useRouter()
const toast = useToast()
const transfertStore = useTransfertStore()

const loading = ref(false)
const loadingApprovedDemandes = ref(false) // État chargement demandes
const approvedDemandes = ref([]) // Liste des demandes approuvées
const selectedStatut = ref(null)
const searchQuery = ref('')

// Récupérer les demandes approuvées prêtes pour transfert
const fetchApprovedDemandes = async () => {
  loadingApprovedDemandes.value = true
  try {
    const res = await transfertApi.getDemandesApprouvees()
    if (res.data && res.data.success) {
      approvedDemandes.value = res.data.data
    }
  } catch (err) {
    console.error('Erreur lors de la récupération des demandes approuvées', err)
  } finally {
    loadingApprovedDemandes.value = false
  }
}

// Créer un transfert depuis une demande
const createTransfertFromDemande = async (demande) => {
  try {
    const res = await transfertApi.creerDepuisDemande(demande.id)
    if (res.data && res.data.success) {
      toast.add({ severity: 'success', summary: 'Succès', detail: 'Transfert généré avec succès' })
      // Rafraîchir les données
      await Promise.all([
        fetchApprovedDemandes(),
        transfertStore.fetchTransferts()
      ])
    }
  } catch (err) {
    toast.add({ severity: 'error', summary: 'Erreur', detail: err.response?.data?.message || 'Échec de la création du transfert' })
  }
}

const transfertsEnAttente = computed(() => transfertStore.transfertsEnAttente)
const transfertsEnTransit = computed(() => transfertStore.transfertsEnTransit)
const transfertsTermines = computed(() => transfertStore.transfertsTermines)

const filteredTransferts = computed(() => {
  let list = transfertStore.transferts
  if (searchQuery.value) {
    const q = searchQuery.value.toLowerCase()
    list = list.filter(t => 
      t.numero_transfert?.toLowerCase().includes(q) || 
      t.equipement?.nom?.toLowerCase().includes(q) ||
      t.equipement?.marque?.toLowerCase().includes(q) ||
      t.agence_source?.nom?.toLowerCase().includes(q) ||
      t.agence_destination?.nom?.toLowerCase().includes(q)
    )
  }
  if (selectedStatut.value) {
    list = list.filter(t => t.statut === selectedStatut.value)
  }
  return list
})

const statutOptions = [
  { label: 'En attente', value: 'demande' },
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

const shipTransfert = async (trans) => {
  try {
    await transfertStore.expedierTransfert(trans.id)
    toast.add({ severity: 'success', summary: 'Succès', detail: 'Équipement expédié' })
  } catch (err) {
    toast.add({ severity: 'error', summary: 'Erreur', detail: 'Échec de l\'expédition' })
  }
}

const receiveTransfert = async (trans) => {
  try {
    await transfertStore.recevoirTransfert(trans.id)
    toast.add({ severity: 'success', summary: 'Succès', detail: 'Équipement reçu' })
  } catch (err) {
    toast.add({ severity: 'error', summary: 'Erreur', detail: 'Échec de la réception' })
  }
}

onMounted(async () => {
  loading.value = true
  await Promise.all([
    transfertStore.fetchTransferts(),
    fetchApprovedDemandes()
  ])
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

.approved-demandes-section {
  background: rgba(99, 102, 241, 0.05);
  border: 1px solid rgba(99, 102, 241, 0.1);
  border-radius: 20px;
  padding: 1.5rem;
  margin-bottom: 2rem;
  
  .section-header {
    display: flex;
    align-items: center;
    gap: 0.8rem;
    margin-bottom: 1.2rem;
    color: #6366f1;
    
    i { font-size: 1.2rem; }
    h3 { margin: 0; font-size: 1.1rem; font-weight: 700; color: #1e293b; }
    .count-badge {
      background: #6366f1;
      color: white;
      padding: 2px 8px;
      border-radius: 12px;
      font-size: 0.8rem;
      font-weight: bold;
    }
  }
}

.demandes-horizontal-scroll {
  display: flex;
  gap: 1.2rem;
  overflow-x: auto;
  padding-bottom: 1rem;
  &::-webkit-scrollbar { height: 6px; }
  &::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
}

.approved-demande-card {
  min-width: 280px;
  background: white;
  border-radius: 16px;
  padding: 1.2rem;
  box-shadow: 0 4px 12px rgba(0,0,0,0.05);
  border: 1px solid #f1f5f9;
}

.filters-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
  gap: 1.5rem;
  
  .search-box {
    position: relative;
    flex: 1;
    i { position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: #94a3b8; z-index: 1; }
    :deep(.search-input) { width: 100%; padding-left: 2.5rem; border-radius: 12px; border: 1px solid #e2e8f0; }
  }
  
  .dropdown-filters {
    display: flex;
    gap: 1rem;
    :deep(.modern-dropdown) { border-radius: 12px; border: 1px solid #e2e8f0; min-width: 180px; }
  }
}

.table-container {
  background: white;
  border-radius: 20px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.05);
  overflow: hidden;
  border: 1px solid #f1f5f9;
  
  &.skeleton {
    padding: 1rem;
    .skeleton-row {
      height: 60px;
      background: #f8fafc;
      margin-bottom: 0.5rem;
      border-radius: 8px;
      animation: pulse 1.5s infinite;
    }
  }
}

@keyframes pulse {
  0% { opacity: 0.6; }
  50% { opacity: 1; }
  100% { opacity: 0.6; }
}

.modern-table {
  :deep(.p-datatable-thead > tr > th) {
    background: #f8fafc;
    color: #475569;
    font-weight: 700;
    padding: 1rem;
    border-bottom: 2px solid #f1f5f9;
  }
  
  :deep(.p-datatable-tbody > tr) {
    transition: background 0.2s;
    &:hover { background: #f8fafc; }
    > td { padding: 1rem; border-bottom: 1px solid #f1f5f9; }
  }
}

.trans-id { font-family: monospace; font-weight: 700; color: #64748b; }
.equipement-cell {
  display: flex; flex-direction: column;
  .equip-name { font-weight: 600; color: #1e293b; }
  .equip-sn { color: #94a3b8; font-size: 0.75rem; }
}

.agence-cell {
  font-weight: 600;
  &.source { color: #6366f1; }
  &.destination { color: #8b5cf6; }
}

.actions-cell { display: flex; gap: 0.25rem; }
</style>
