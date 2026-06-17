<template>
  <AgenceLayout>
    <div class="retours-view" ref="pageContainer">
      <!-- En-tête -->
      <div class="page-header animate-in">
        <div class="header-left">
          <div class="title-with-icon">
            <div class="icon-wrapper">
              <i class="pi pi-undo"></i>
            </div>
            <div>
              <h1>Retours de Matériel</h1>
              <p class="subtitle">Gérez les retours d'équipements vers le Siège Social</p>
            </div>
          </div>
        </div>
        <div class="header-right">
          <Button 
            label="Nouveau Retour" 
            icon="pi pi-plus" 
            class="p-button-primary" 
            @click="ouvrirFormulaireRetour"
          />
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

          <Column header="Destination" sortable sortField="agence_destination.nom">
            <template #body="slotProps">
              <span class="agence-cell">{{ slotProps.data.agence_destination?.nom || 'Siège Social' }}</span>
            </template>
          </Column>

          <Column field="date_expedition" header="Date de retour" sortable>
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
                <!-- Actions selon le statut -->
              </div>
            </template>
          </Column>
        </DataTable>
      </div>

      <!-- Skeleton loading -->
      <div class="table-container skeleton" v-else>
        <div v-for="n in 5" :key="n" class="skeleton-row"></div>
      </div>

      <!-- Dialogue Nouveau Retour -->
      <Dialog v-model:visible="retourDialogVisible" header="Créer un retour d'équipement" :modal="true" :style="{ width: '500px' }">
        <div class="form-group">
          <label for="equipement">Équipement *</label>
          <Dropdown 
            id="equipement" 
            v-model="nouveauRetour.equipement_id" 
            :options="equipementsEnStock" 
            optionLabel="label" 
            optionValue="value" 
            placeholder="Sélectionnez un équipement en stock" 
            class="w-full" 
            :filter="true"
          />
        </div>
        <div class="form-group mt-3">
          <label for="motif">Motif du retour *</label>
          <Textarea id="motif" v-model="nouveauRetour.observations" rows="3" class="w-full" placeholder="Ex: Matériel défectueux, fin d'utilisation..." />
        </div>
        <template #footer>
          <Button label="Annuler" icon="pi pi-times" class="p-button-text" @click="fermerFormulaireRetour" />
          <Button label="Créer le Retour" icon="pi pi-check" class="p-button-primary" @click="creerRetour" :loading="submittingRetour" />
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
import transfertApi from '@/api/transfertApi'
import agenceApi from '@/api/agenceApi'

// PrimeVue Components
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import Dropdown from 'primevue/dropdown'
import Tag from 'primevue/tag'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Dialog from 'primevue/dialog'
import Textarea from 'primevue/textarea'
import equipementApi from '@/api/equipementApi'

const toast = useToast()
const transfertStore = useTransfertStore()
const authStore = useAuthStore()

const loading = ref(false)
const submitting = ref(null)
const searchQuery = ref('')
const selectedStatut = ref('')
const equipementsEnStock = ref([])

const retourDialogVisible = ref(false)
const nouveauRetour = ref({
  equipement_id: null,
  observations: ''
})
const submittingRetour = ref(false)

const statutOptions = [
  { label: 'Demande', value: 'demande' },
  { label: 'Approuvé', value: 'approuve' },
  { label: 'En transit', value: 'expedie' },
  { label: 'Reçu par le Siège', value: 'recu' },
  { label: 'Refusé', value: 'refuse' }
]

// Transferts sortants (retours vers le siège)
const transfertsSortants = computed(() => {
  const list = Array.isArray(transfertStore.transferts) 
    ? transfertStore.transferts 
    : (transfertStore.transferts?.data || [])

  return list.filter(t => {
    // 1. L'agence courante doit être la source
    const isSource = t.agence_source_id == authStore.userAgence
    
    // 2. Doit être un retour vers le Siège
    const isRetour = t.type_transfert === 'retour_generale'

    return isSource && isRetour
  })
})

const filteredTransferts = computed(() => {
  let list = transfertsSortants.value || []
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
    case 'demande': return 'info'
    case 'approuve': return 'primary'
    case 'expedie': return 'warning'
    case 'recu': return 'success'
    case 'refuse': return 'danger'
    default: return 'secondary'
  }
}

const formatDate = (date) => date ? new Date(date).toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : 'N/A'

// Charger les équipements en stock pour l'agence
const chargerEquipementsEnStock = async () => {
  try {
    const response = await equipementApi.index({ agence_id: authStore.userAgence, en_stock: true })
    if (response.data.success) {
      const equipements = response.data.data.data || response.data.data
      equipementsEnStock.value = equipements.map(e => ({
        label: `${e.nom || e.marque + ' ' + e.modele} (SN: ${e.numero_serie || 'N/A'})`,
        value: e.id
      }))
    }
  } catch (err) {
    console.error('Erreur chargement équipements:', err)
  }
}

const agenceGeneraleId = ref(null)

const getAgenceGenerale = async () => {
  try {
    const response = await agenceApi.index()
    const agences = Array.isArray(response.data) ? response.data : (response.data.data || [])
    const generale = agences.find(a => a.type === 'generale')
    agenceGeneraleId.value = generale?.id || null
  } catch (err) {
    console.error('Erreur chargement agence générale:', err)
  }
}

const ouvrirFormulaireRetour = async () => {
  await Promise.all([chargerEquipementsEnStock(), getAgenceGenerale()])
  nouveauRetour.value = { equipement_id: null, observations: '' }
  retourDialogVisible.value = true
}

const fermerFormulaireRetour = () => {
  retourDialogVisible.value = false
  nouveauRetour.value = { equipement_id: null, observations: '' }
}

const creerRetour = async () => {
  if (!nouveauRetour.value.equipement_id || !nouveauRetour.value.observations.trim()) {
    toast.add({ severity: 'warn', summary: 'Attention', detail: 'Veuillez remplir tous les champs', life: 3000 })
    return
  }

  submittingRetour.value = true
  try {
    if (!agenceGeneraleId.value) {
      toast.add({ severity: 'error', summary: 'Erreur', detail: 'Aucune agence générale trouvée', life: 3000 })
      return
    }
    await transfertApi.store({
      equipement_id: nouveauRetour.value.equipement_id,
      agence_destination_id: agenceGeneraleId.value,
      type_transfert: 'retour_generale',
      observations: nouveauRetour.value.observations
    })
    toast.add({ severity: 'success', summary: 'Succès', detail: 'Retour créé avec succès', life: 3000 })
    fermerFormulaireRetour()
    await transfertStore.fetchTransferts({ direction: 'sortants', type_transfert: 'retour_generale' })
  } catch (err) {
    toast.add({ severity: 'error', summary: 'Erreur', detail: 'Échec de la création du retour', life: 3000 })
  } finally {
    submittingRetour.value = false
  }
}

onMounted(async () => {
  loading.value = true
  await transfertStore.fetchTransferts({ 
    direction: 'sortants', 
    type_transfert: 'retour_generale' 
  })
  loading.value = false
  
  gsap.from('.animate-in', { opacity: 0, y: 20, duration: 0.8, stagger: 0.2, ease: 'power3.out' })
})
</script>

<style scoped lang="scss">
.retours-view { padding: 2rem; }
.title-with-icon {
  display: flex; align-items: center; gap: 1.5rem;
  .icon-wrapper {
    width: 60px; height: 60px;
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    border-radius: 16px; display: flex; align-items: center; justify-content: center;
    color: white; box-shadow: 0 8px 16px rgba(245, 158, 11, 0.2);
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
  background: white; border-radius: 20px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); overflow: clip; border: 1px solid #f1f5f9;
  &.skeleton { padding: 1rem; .skeleton-row { height: 60px; background: #f8fafc; margin-bottom: 0.5rem; border-radius: 8px; animation: pulse 1.5s infinite; } }
}

@keyframes pulse { 0% { opacity: 0.6; } 50% { opacity: 1; } 100% { opacity: 0.6; } }

.modern-table {
  :deep(.p-datatable-thead > tr > th) { background: #f8fafc; color: #475569; font-weight: 700; padding: 1.2rem 1rem; border-bottom: 2px solid #f1f5f9; }
  :deep(.p-datatable-tbody > tr) { transition: background 0.2s; &:hover { background: #f8fafc; } > td { padding: 1.2rem 1rem; border-bottom: 1px solid #f1f5f9; } }
}

.trans-id { font-family: monospace; font-weight: 700; color: #64748b; }
.equipement-cell { display: flex; flex-direction: column; .equip-name { font-weight: 600; color: #1e293b; } .equip-sn { color: #94a3b8; font-size: 0.75rem; } }
.agence-cell { font-weight: 600; color: #6366f1; }
.actions-cell { display: flex; gap: 0.75rem; align-items: center; }
.text-success { color: #10b981; font-weight: 600; display: flex; align-items: center; gap: 0.5rem; }

.form-group { display: flex; flex-direction: column; gap: 0.5rem; label { font-weight: 600; color: #475569; } }
.confirmation-content { display: flex; align-items: center; gap: 1rem; }
</style>
