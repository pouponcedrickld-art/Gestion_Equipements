<template>
<<<<<<< HEAD:frontend/src/views/transferts/TransfertsView.vue
  <MainLayout>
    <div class="transferts-view">
      <!-- En-tête -->
      <div class="page-header">
        <div class="header-left">
          <h1>Gestion des Transferts</h1>
          <p class="subtitle">Suivi des transferts d'équipements entre agences</p>
        </div>
        <div class="header-actions">
          <Button 
            label="Nouveau Transfert" 
            icon="pi pi-plus" 
            @click="$router.push('/transferts/nouveau')"
          />
        </div>
      </div>

      <!-- Statistiques -->
      <div class="stats-grid" v-if="!loading">
        <Card class="stat-card stat-en-attente">
          <template #content>
            <div class="stat-content">
              <i class="pi pi-clock stat-icon"></i>
              <div class="stat-info">
                <span class="stat-value">{{ transfertsEnAttente }}</span>
                <span class="stat-label">En Attente</span>
              </div>
            </div>
          </template>
        </Card>
        <Card class="stat-card stat-approuves">
          <template #content>
            <div class="stat-content">
              <i class="pi pi-check-circle stat-icon"></i>
              <div class="stat-info">
                <span class="stat-value">{{ transfertsApprouves }}</span>
                <span class="stat-label">Approuvés</span>
              </div>
            </div>
          </template>
        </Card>
        <Card class="stat-card stat-en-transit">
          <template #content>
            <div class="stat-content">
              <i class="pi pi-send stat-icon"></i>
              <div class="stat-info">
                <span class="stat-value">{{ transfertsEnTransit }}</span>
                <span class="stat-label">En Transit</span>
              </div>
            </div>
          </template>
        </Card>
        <Card class="stat-card stat-termines">
          <template #content>
            <div class="stat-content">
              <i class="pi pi-check stat-icon"></i>
              <div class="stat-info">
                <span class="stat-value">{{ transfertsTermines }}</span>
                <span class="stat-label">Terminés</span>
              </div>
            </div>
          </template>
        </Card>
      </div>

      <!-- Filtres -->
      <Card class="filters-card">
        <template #content>
          <div class="filters-container">
            <div class="filter-group">
              <Dropdown
                v-model="selectedStatut"
                :options="statutOptions"
                optionLabel="label"
                optionValue="value"
                placeholder="Tous les statuts"
                class="filter-dropdown"
                showClear
              />
            </div>
            <div class="filter-group">
              <Dropdown
                v-model="selectedType"
                :options="typeOptions"
                optionLabel="label"
                optionValue="value"
                placeholder="Tous les types"
                class="filter-dropdown"
                showClear
              />
            </div>
            <div class="filter-group">
              <Button 
                label="Réinitialiser" 
                icon="pi pi-filter-slash" 
                class="p-button-outlined"
                @click="resetFilters"
              />
            </div>
          </div>
        </template>
      </Card>

      <!-- Tableau des transferts -->
      <Card class="table-card">
        <template #content>
          <DataTable
            :value="filteredTransferts"
            :loading="loading"
            :paginator="true"
            :rows="10"
            :rowsPerPageOptions="[10, 25, 50]"
            stripedRows
            responsiveLayout="scroll"
            currentPageReportTemplate="Affichage de {first} à {last} sur {totalRecords} transferts"
            paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
          >
            <Column field="numero_transfert" header="N° Transfert" :sortable="true" style="min-width: 150px">
              <template #body="slotProps">
                <strong>{{ slotProps.data.numero_transfert }}</strong>
              </template>
            </Column>

            <Column field="type" header="Type" :sortable="true" style="min-width: 130px">
              <template #body="slotProps">
                <Tag 
                  :value="getTypeLabel(slotProps.data.type)" 
                  :severity="getTypeSeverity(slotProps.data.type)"
                />
              </template>
            </Column>

            <Column field="statut" header="Statut" :sortable="true" style="min-width: 150px">
              <template #body="slotProps">
                <Tag 
                  :value="getStatutLabel(slotProps.data.statut)" 
                  :severity="getStatutSeverity(slotProps.data.statut)"
                />
              </template>
            </Column>

            <Column header="Origine → Destination" style="min-width: 300px">
              <template #body="slotProps">
                <div class="route-cell">
                  <div class="agence-box origin">
                    <i class="pi pi-map-marker"></i>
                    <span>{{ slotProps.data.agence_origine?.nom }}</span>
                  </div>
                  <i class="pi pi-arrow-right route-arrow"></i>
                  <div class="agence-box destination">
                    <i class="pi pi-map-marker"></i>
                    <span>{{ slotProps.data.agence_destination?.nom }}</span>
                  </div>
                </div>
              </template>
            </Column>

            <Column field="equipement" header="Équipement" style="min-width: 200px">
              <template #body="slotProps">
                <div class="equipement-cell">
                  <strong>{{ slotProps.data.equipement?.nom }}</strong>
                  <small>{{ slotProps.data.equipement?.numero_serie }}</small>
                </div>
              </template>
            </Column>

            <Column field="date_demande" header="Date Demande" :sortable="true" style="min-width: 140px">
              <template #body="slotProps">
                {{ formatDate(slotProps.data.date_demande) }}
              </template>
            </Column>

            <Column header="Actions" :exportable="false" style="min-width: 250px">
              <template #body="slotProps">
                <div class="action-buttons">
                  <Button 
                    icon="pi pi-eye" 
                    class="p-button-rounded p-button-text p-button-info"
                    v-tooltip.top="'Détails'"
                    @click="viewDetails(slotProps.data)"
                  />
                  
                  <!-- Actions selon le statut et le rôle -->
                  <Button 
                    v-if="canApprove(slotProps.data)"
                    icon="pi pi-check" 
                    class="p-button-rounded p-button-text p-button-success"
                    v-tooltip.top="'Approuver'"
                    @click="approveTransfert(slotProps.data)"
                  />
                  
                  <Button 
                    v-if="canReject(slotProps.data)"
                    icon="pi pi-times" 
                    class="p-button-rounded p-button-text p-button-danger"
                    v-tooltip.top="'Refuser'"
                    @click="rejectTransfert(slotProps.data)"
                  />
                  
                  <Button 
                    v-if="canShip(slotProps.data)"
                    icon="pi pi-send" 
                    class="p-button-rounded p-button-text p-button-warning"
                    v-tooltip.top="'Expédier'"
                    @click="shipTransfert(slotProps.data)"
                  />
                  
                  <Button 
                    v-if="canReceive(slotProps.data)"
                    icon="pi pi-inbox" 
                    class="p-button-rounded p-button-text p-button-primary"
                    v-tooltip.top="'Recevoir'"
                    @click="receiveTransfert(slotProps.data)"
                  />
                </div>
              </template>
            </Column>
          </DataTable>
        </template>
      </Card>
=======
  <AgenceLayout>
    <div class="page-placeholder">
      <h2>{{ pageTitle }}</h2>
      <p>Page en cours de développement...</p>
>>>>>>> 2c965af4f2361eccbef055db409105b763f2340d:frontend/src/views/agence/affectations/AffectationFormView.vue
    </div>
  </AgenceLayout>
</template>

<script setup>
<<<<<<< HEAD:frontend/src/views/transferts/TransfertsView.vue
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useToast } from 'primevue/usetoast'
import { useAuthStore } from '@/stores/authStore'
import MainLayout from '@/layouts/MainLayout.vue'
import { useTransfertStore } from '@/stores/transfertStore'
=======
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import AgenceLayout from '@/layouts/AgenceLayout.vue'
>>>>>>> 2c965af4f2361eccbef055db409105b763f2340d:frontend/src/views/agence/affectations/AffectationFormView.vue

// PrimeVue Components
import Button from 'primevue/button'
import Card from 'primevue/card'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Dropdown from 'primevue/dropdown'
import Tag from 'primevue/tag'

const router = useRouter()
const toast = useToast()
const transfertStore = useTransfertStore()
const authStore = useAuthStore()

// État local
const loading = ref(false)
const selectedStatut = ref(null)
const selectedType = ref(null)

// Options de filtres
const statutOptions = [
  { label: 'En attente', value: 'demande' },
  { label: 'Approuvé', value: 'approuve' },
  { label: 'Refusé', value: 'refuse' },
  { label: 'Expédié', value: 'expedie' },
  { label: 'Reçu', value: 'recu' },
  { label: 'Annulé', value: 'annule' }
]

const typeOptions = [
  { label: 'Transfert inter-agences', value: 'transfert' },
  { label: 'Affectation temporaire', value: 'affectation_temporaire' },
  { label: 'Retour', value: 'retour' }
]

// Données computed
const transferts = computed(() => transfertStore.transferts)
const userRole = computed(() => authStore.user?.role)

const filteredTransferts = computed(() => {
  let result = [...transferts.value]

  // Filtre par statut
  if (selectedStatut.value) {
    result = result.filter(t => t.statut === selectedStatut.value)
  }

  // Filtre par type
  if (selectedType.value) {
    result = result.filter(t => t.type === selectedType.value)
  }

  return result
})

// Statistiques
const transfertsEnAttente = computed(() => 
  transferts.value.filter(t => t.statut === 'demande').length
)
const transfertsApprouves = computed(() => 
  transferts.value.filter(t => t.statut === 'approuve').length
)
const transfertsEnTransit = computed(() => 
  transferts.value.filter(t => t.statut === 'expedie').length
)
const transfertsTermines = computed(() => 
  transferts.value.filter(t => t.statut === 'recu').length
)

// Méthodes
const loadData = async () => {
  loading.value = true
  try {
    await transfertStore.fetchTransferts()
  } catch (error) {
    toast.add({
      severity: 'error',
      summary: 'Erreur',
      detail: 'Impossible de charger les transferts',
      life: 3000
    })
  } finally {
    loading.value = false
  }
}

const resetFilters = () => {
  selectedStatut.value = null
  selectedType.value = null
}

const viewDetails = (transfert) => {
  router.push(`/transferts/${transfert.id}`)
}

// Vérifications de permissions
const canApprove = (transfert) => {
  return transfert.statut === 'demande' && 
         ['super_admin', 'gestionnaire_stock_general'].includes(userRole.value)
}

const canReject = (transfert) => {
  return transfert.statut === 'demande' && 
         ['super_admin', 'gestionnaire_stock_general'].includes(userRole.value)
}

const canShip = (transfert) => {
  return transfert.statut === 'approuve' && 
         ['super_admin', 'gestionnaire_stock_general'].includes(userRole.value)
}

const canReceive = (transfert) => {
  return transfert.statut === 'expedie' && 
         ['super_admin', 'gestionnaire_stock_local'].includes(userRole.value)
}

// Actions de workflow
const approveTransfert = async (transfert) => {
  try {
    await transfertStore.approveTransfert(transfert.id)
    toast.add({
      severity: 'success',
      summary: 'Succès',
      detail: 'Transfert approuvé',
      life: 3000
    })
  } catch (error) {
    toast.add({
      severity: 'error',
      summary: 'Erreur',
      detail: error.message || 'Impossible d\'approuver le transfert',
      life: 3000
    })
  }
}

const rejectTransfert = async (transfert) => {
  try {
    await transfertStore.rejectTransfert(transfert.id, { motif: 'Refusé par le gestionnaire' })
    toast.add({
      severity: 'success',
      summary: 'Succès',
      detail: 'Transfert refusé',
      life: 3000
    })
  } catch (error) {
    toast.add({
      severity: 'error',
      summary: 'Erreur',
      detail: error.message || 'Impossible de refuser le transfert',
      life: 3000
    })
  }
}

const shipTransfert = async (transfert) => {
  try {
    await transfertStore.shipTransfert(transfert.id)
    toast.add({
      severity: 'success',
      summary: 'Succès',
      detail: 'Transfert expédié',
      life: 3000
    })
  } catch (error) {
    toast.add({
      severity: 'error',
      summary: 'Erreur',
      detail: error.message || 'Impossible d\'expédier le transfert',
      life: 3000
    })
  }
}

const receiveTransfert = async (transfert) => {
  try {
    await transfertStore.receiveTransfert(transfert.id)
    toast.add({
      severity: 'success',
      summary: 'Succès',
      detail: 'Transfert reçu avec succès',
      life: 3000
    })
  } catch (error) {
    toast.add({
      severity: 'error',
      summary: 'Erreur',
      detail: error.message || 'Impossible de recevoir le transfert',
      life: 3000
    })
  }
}

// Helpers pour affichage
const getTypeLabel = (type) => {
  const labels = {
    transfert: 'Transfert',
    affectation_temporaire: 'Affectation temporaire',
    retour: 'Retour'
  }
  return labels[type] || type
}

const getTypeSeverity = (type) => {
  const severities = {
    transfert: 'info',
    affectation_temporaire: 'warning',
    retour: 'secondary'
  }
  return severities[type] || 'secondary'
}

const getStatutLabel = (statut) => {
  const labels = {
    demande: 'En attente',
    approuve: 'Approuvé',
    refuse: 'Refusé',
    expedie: 'Expédié',
    recu: 'Reçu',
    annule: 'Annulé'
  }
  return labels[statut] || statut
}

const getStatutSeverity = (statut) => {
  const severities = {
    demande: 'warning',
    approuve: 'info',
    refuse: 'danger',
    expedie: 'primary',
    recu: 'success',
    annule: 'secondary'
  }
  return severities[statut] || 'secondary'
}

const formatDate = (date) => {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('fr-FR')
}

// Lifecycle
onMounted(() => {
  loadData()
})
</script>

<style lang="scss" scoped>
.transferts-view {
  padding: 1.5rem;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 2rem;

  .header-left {
    h1 {
      margin: 0 0 0.5rem 0;
      color: #e2e8f0;
      font-size: 2rem;
    }

    .subtitle {
      margin: 0;
      color: #94a3b8;
    }
  }
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1.5rem;
  margin-bottom: 2rem;

  .stat-card {
    background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
    border: 1px solid #334155;

    .stat-content {
      display: flex;
      align-items: center;
      gap: 1rem;

      .stat-icon {
        font-size: 2.5rem;
        opacity: 0.8;
      }

      .stat-info {
        display: flex;
        flex-direction: column;

        .stat-value {
          font-size: 2rem;
          font-weight: 700;
          color: #e2e8f0;
        }

        .stat-label {
          font-size: 0.875rem;
          color: #94a3b8;
        }
      }
    }

    &.stat-en-attente .stat-icon { color: #f59e0b; }
    &.stat-approuves .stat-icon { color: #3b82f6; }
    &.stat-en-transit .stat-icon { color: #8b5cf6; }
    &.stat-termines .stat-icon { color: #10b981; }
  }
}

.filters-card {
  margin-bottom: 1.5rem;
  background: #1e293b;
  border: 1px solid #334155;

  .filters-container {
    display: grid;
    grid-template-columns: 1fr 1fr auto;
    gap: 1rem;
    align-items: center;

    @media (max-width: 768px) {
      grid-template-columns: 1fr;
    }

    .filter-dropdown {
      width: 100%;
    }
  }
}

.table-card {
  background: #1e293b;
  border: 1px solid #334155;
}

.route-cell {
  display: flex;
  align-items: center;
  gap: 0.75rem;

  .agence-box {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 0.75rem;
    border-radius: 6px;
    background: #0f172a;

    i {
      font-size: 0.875rem;
    }

    span {
      font-size: 0.875rem;
      color: #e2e8f0;
    }

    &.origin {
      border-left: 3px solid #f59e0b;
    }

    &.destination {
      border-left: 3px solid #10b981;
    }
  }

  .route-arrow {
    color: #64748b;
  }
}

.equipement-cell {
  display: flex;
  flex-direction: column;

  strong {
    color: #e2e8f0;
    margin-bottom: 0.25rem;
  }

  small {
    color: #94a3b8;
    font-size: 0.75rem;
  }
}

.action-buttons {
  display: flex;
  gap: 0.25rem;
  flex-wrap: wrap;
}
</style>
