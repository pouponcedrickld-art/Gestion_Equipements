<template>
  <AgenceLayout>
    <div class="reception-view" ref="pageContainer">
      <!-- En-tête -->
      <div class="page-header animate-in">
        <div class="header-left">
          <div class="title-with-icon">
            <div class="icon-wrapper">
              <i class="pi pi-download"></i>
            </div>
            <div>
              <h1>Réceptions de Matériel</h1>
              <p class="subtitle">Confirmez la réception des équipements envoyés par le Siège</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Filtres et Recherche -->
      <div class="filters-bar animate-in">
        <div class="search-box">
          <i class="pi pi-search"></i>
          <InputText v-model="searchQuery" placeholder="Rechercher par équipement, référence..." class="search-input" />
        </div>
        <div class="dropdown-filters">
          <Dropdown v-model="selectedStatut" :options="statutOptions" optionLabel="label" optionValue="value" placeholder="Statut" class="modern-dropdown" showClear />
        </div>
      </div>

      <!-- Liste des Réceptions (Tableau) -->
      <div class="table-container animate-in" v-if="!loading">
        <DataTable 
          :value="filteredTransferts" 
          responsiveLayout="stack" 
          breakpoint="960px"
          stripedRows
          class="professional-table"
          :paginator="true" 
          :rows="10"
          emptyMessage="Aucun transfert en attente de réception"
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

          <Column field="date_expedition" header="Expédié le" sortable>
            <template #body="slotProps">
              {{ formatDate(slotProps.data.date_expedition) }}
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
                <Button 
                  v-if="slotProps.data.statut === 'expedie'" 
                  label="Confirmer Réception" 
                  icon="pi pi-check" 
                  class="p-button-success p-button-sm" 
                  @click="confirmReception(slotProps.data)" 
                  :loading="submitting === slotProps.data.id"
                />
                <span v-else-if="slotProps.data.statut === 'recu'" class="text-success">
                  <i class="pi pi-check-circle"></i> Reçu
                </span>
                <Button 
                  v-if="slotProps.data.statut === 'expedie'" 
                  icon="pi pi-times" 
                  class="p-button-text p-button-danger p-button-sm" 
                  @click="ouvrirRefusDialog(slotProps.data)" 
                  v-tooltip.top="'Signaler un problème / Refuser'"
                />
              </div>
            </template>
          </Column>
        </DataTable>
      </div>

      <!-- Skeleton loading -->
      <div class="table-container skeleton" v-else>
        <div v-for="n in 5" :key="n" class="skeleton-row"></div>
      </div>

      <!-- Dialogue de refus -->
      <Dialog v-model:visible="refusDialogVisible" header="Signaler un problème / Refuser" :modal="true" :style="{ width: '450px' }">
        <div class="confirmation-content">
          <i class="pi pi-exclamation-triangle mr-3" style="font-size: 2rem; color: var(--red-500)" />
          <span>Veuillez indiquer la raison du refus ou le problème constaté.</span>
        </div>
        <div class="form-group mt-4">
          <label for="observations">Observations *</label>
          <Textarea id="observations" v-model="refusObservations" rows="4" class="w-full" placeholder="Ex: Matériel endommagé, erreur de référence..." />
        </div>
        <template #footer>
          <Button label="Annuler" icon="pi pi-times" class="p-button-text" @click="fermerRefusDialog" />
          <Button label="Confirmer le Refus" icon="pi pi-check" class="p-button-danger" @click="confirmerRefus" :loading="submittingRefus" />
        </template>
      </Dialog>
    </div>
  </AgenceLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useToast } from 'primevue/usetoast'
import { useTransfertStore } from '@/stores/transfertStore'
import { useAuthStore } from '@/stores/authStore'
import AgenceLayout from '@/layouts/AgenceLayout.vue'
import gsap from 'gsap'

// PrimeVue Components
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import Dropdown from 'primevue/dropdown'
import Tag from 'primevue/tag'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Dialog from 'primevue/dialog'
import Textarea from 'primevue/textarea'

const toast = useToast()
const transfertStore = useTransfertStore()
const authStore = useAuthStore()

const loading = ref(false)
const submitting = ref(null)
const searchQuery = ref('')
const selectedStatut = ref('expedie') // Par défaut, on montre ce qu'il faut recevoir

const refusDialogVisible = ref(false)
const refusObservations = ref('')
const transfertARefuser = ref(null)
const submittingRefus = ref(false)

const statutOptions = [
  { label: 'En transit (À recevoir)', value: 'expedie' },
  { label: 'Déjà reçus', value: 'recu' },
  { label: 'Refusés', value: 'refuse' }
]

const transfertsEntrants = computed(() => {
  const list = Array.isArray(transfertStore.transferts) 
    ? transfertStore.transferts 
    : (transfertStore.transferts?.data || [])

  return list.filter(t => {
    // 1. L'agence courante doit être la destination
    const isDestination = t.agence_destination_id === authStore.userAgence
    
    // 2. Doit être une livraison depuis le Siège (GSG)
    // On vérifie si c'est le bon type de transfert OU si la source est l'agence Siège (ID 1 par convention souvent)
    const isFromGSG = t.type_transfert === 'livraison_generale' || 
                     t.agence_source?.type === 'generale' || 
                     t.agence_source_id === 1

    return isDestination && isFromGSG
  })
})

const filteredTransferts = computed(() => {
  let list = transfertsEntrants.value || []
  if (searchQuery.value) {
    const q = searchQuery.value.toLowerCase()
    list = list.filter(t => 
      t.id.toString().includes(q) || 
      t.equipement?.nom?.toLowerCase().includes(q) ||
      t.equipement?.marque?.toLowerCase().includes(q) ||
      t.equipement?.reference?.toLowerCase().includes(q)
    )
  }
  if (selectedStatut.value) {
    list = list.filter(t => t.statut === selectedStatut.value)
  }
  return list
})

const getStatutSeverity = (s) => {
  switch(s) {
    case 'expedie': return 'primary'
    case 'recu': return 'success'
    case 'refuse': return 'danger'
    default: return 'secondary'
  }
}

const formatDate = (date) => date ? new Date(date).toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : 'N/A'

const confirmReception = async (trans) => {
  submitting.value = trans.id
  try {
    await transfertStore.recevoirTransfert(trans.id)
    toast.add({ severity: 'success', summary: 'Succès', detail: 'Équipement reçu et ajouté au stock', life: 3000 })
    await transfertStore.fetchTransferts()
  } catch (err) {
    toast.add({ severity: 'error', summary: 'Erreur', detail: 'Échec de la réception', life: 3000 })
  } finally {
    submitting.value = null
  }
}

const ouvrirRefusDialog = (trans) => {
  transfertARefuser.value = trans
  refusObservations.value = ''
  refusDialogVisible.value = true
}

const fermerRefusDialog = () => {
  refusDialogVisible.value = false
  transfertARefuser.value = null
  refusObservations.value = ''
}

const confirmerRefus = async () => {
  if (!refusObservations.value.trim()) {
    toast.add({ severity: 'warn', summary: 'Attention', detail: 'Veuillez indiquer une raison', life: 3000 })
    return
  }
  
  submittingRefus.value = true
  try {
    await transfertStore.refuserTransfert(transfertARefuser.value.id, refusObservations.value)
    toast.add({ severity: 'success', summary: 'Succès', detail: 'Transfert refusé', life: 3000 })
    fermerRefusDialog()
    await transfertStore.fetchTransferts()
  } catch (err) {
    toast.add({ severity: 'error', summary: 'Erreur', detail: 'Échec de l\'opération', life: 3000 })
  } finally {
    submittingRefus.value = false
  }
}

onMounted(async () => {
  loading.value = true
  // On récupère spécifiquement les transferts entrants du Siège pour l'agence
  await transfertStore.fetchTransferts({ 
    direction: 'entrants', 
    type_transfert: 'livraison_generale' 
  })
  loading.value = false
  
  gsap.from('.animate-in', { opacity: 0, y: 20, duration: 0.8, stagger: 0.2, ease: 'power3.out' })
})
</script>

<style scoped lang="scss">
.reception-view { padding: 2rem; }
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
.agence-cell { font-weight: 600; &.source { color: #6366f1; } }
.actions-cell { display: flex; gap: 0.75rem; align-items: center; }
.text-success { color: #10b981; font-weight: 600; display: flex; align-items: center; gap: 0.5rem; }

.form-group { display: flex; flex-direction: column; gap: 0.5rem; label { font-weight: 600; color: #475569; } }
.confirmation-content { display: flex; align-items: center; gap: 1rem; }
</style>
