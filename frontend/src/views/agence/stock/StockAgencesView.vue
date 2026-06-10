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
              <p class="subtitle">Gestion des transferts entrants et équipements reçus</p>
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

      <!-- Liste des Transferts (Cartes) -->
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
            <!-- Boutons d'action pour les transferts en statut "expedie" -->
            <Button v-if="trans.statut === 'expedie'" icon="pi pi-check" class="p-button-text p-button-rounded p-button-success" @click="recevoirTransfert(trans)" label="Recevoir" />
            <Button v-if="trans.statut === 'expedie'" icon="pi pi-times" class="p-button-text p-button-rounded p-button-danger" @click="ouvrirRefusDialog(trans)" label="Refuser" />
          </div>
        </div>
      </div>

      <!-- Skeleton loading -->
      <div class="transferts-grid" v-else>
        <div v-for="n in 6" :key="n" class="transfert-card skeleton">
          <div class="skeleton-body"></div>
        </div>
      </div>

      <!-- Dialogue de refus -->
      <Dialog v-model:visible="refusDialogVisible" header="Refuser le transfert" :modal="true" :style="{ width: '400px' }">
        <div class="form-group">
          <label for="observations">Observations *</label>
          <Textarea id="observations" v-model="refusObservations" placeholder="Veuillez indiquer la raison du refus..." rows="4" />
        </div>
        <template #footer>
          <Button icon="pi pi-times" label="Annuler" class="p-button-text" @click="fermerRefusDialog" />
          <Button icon="pi pi-check" label="Confirmer" class="p-button-danger" @click="confirmerRefus" />
        </template>
      </Dialog>
    </div>
  </AgenceLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
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
import Dialog from 'primevue/dialog'
import Textarea from 'primevue/textarea'

const router = useRouter()
const toast = useToast()
const transfertStore = useTransfertStore()
const authStore = useAuthStore()

const loading = ref(false)
const selectedStatut = ref(null)
const searchQuery = ref('')
const refusDialogVisible = ref(false)
const refusObservations = ref('')
const transfertARefuser = ref(null)

// Statut options (Expédié, Reçu, Refusé)
const statutOptions = [
  { label: 'Expédié', value: 'expedie' },
  { label: 'Reçu', value: 'recu' },
  { label: 'Refusé', value: 'refuse' }
]

// Transferts entrants (pour l'agence de l'utilisateur connecté)
const transfertsEntrants = computed(() => {
  return transfertStore.transferts.filter(t => t.agence_destination_id === authStore.userAgence)
})

// Filtrage des transferts
const filteredTransferts = computed(() => {
  let list = transfertsEntrants.value
  if (searchQuery.value) {
    const q = searchQuery.value.toLowerCase()
    list = list.filter(t => 
      t.numero_transfert?.toLowerCase().includes(q) || 
      t.equipement?.marque?.toLowerCase().includes(q) ||
      t.agence_origine?.nom?.toLowerCase().includes(q) ||
      t.agence_destination?.nom?.toLowerCase().includes(q)
    )
  }
  if (selectedStatut.value) {
    list = list.filter(t => t.statut === selectedStatut.value)
  }
  return list
})

// Récupération de la sévérité pour le tag
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

// Formatage de date
const formatDate = (date) => date ? new Date(date).toLocaleDateString('fr-FR') : 'N/A'

// Actions
const viewDetails = (trans) => {
  // Rediriger vers la page de détails du transfert
  // Pour l'instant, on affiche un toast
  toast.add({ severity: 'info', summary: 'Détails', detail: `Voir détails du transfert #${trans.id}` })
}

const recevoirTransfert = async (trans) => {
  try {
    await transfertStore.recevoirTransfert(trans.id)
    toast.add({ severity: 'success', summary: 'Succès', detail: 'Transfert reçu avec succès', life: 3000 })
    // Recharger les transferts
    await transfertStore.fetchTransferts()
  } catch (err) {
    toast.add({ severity: 'error', summary: 'Erreur', detail: 'Impossible de recevoir le transfert', life: 3000 })
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
    toast.add({ severity: 'warn', summary: 'Attention', detail: 'Veuillez indiquer une raison de refus', life: 3000 })
    return
  }
  
  try {
    await transfertStore.refuserTransfert(transfertARefuser.value.id, refusObservations.value)
    toast.add({ severity: 'success', summary: 'Succès', detail: 'Transfert refusé avec succès', life: 3000 })
    fermerRefusDialog()
    // Recharger les transferts
    await transfertStore.fetchTransferts()
  } catch (err) {
    toast.add({ severity: 'error', summary: 'Erreur', detail: 'Impossible de refuser le transfert', life: 3000 })
  }
}

// Chargement initial
onMounted(async () => {
  loading.value = true
  try {
    await transfertStore.fetchTransferts()
  } catch (err) {
    console.error('Erreur lors du chargement des transferts:', err)
  } finally {
    loading.value = false
  }
  
  gsap.from('.animate-in', { opacity: 0, y: 20, duration: 0.8, stagger: 0.2, ease: 'power3.out' })
  gsap.from('.animate-card', { opacity: 0, y: 20, duration: 0.6, stagger: 0.05, ease: 'power2.out' })
})
</script>

<style scoped lang="scss">
.stock-agences-view { padding: 1rem; }
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

.filters-bar {
  display: flex; justify-content: space-between; gap: 0.75rem; margin-bottom: 1rem;
  .search-box { flex: 1; position: relative; i { position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%); color: #64748b; font-size: 0.85rem; } .search-input { width: 100%; padding-left: 2.25rem; border-radius: 10px; border: none; box-shadow: 0 2px 6px rgba(0,0,0,0.05); font-size: 0.85rem; height: 36px; } }
  .dropdown-filters { display: flex; gap: 0.75rem; align-items: center; .modern-dropdown { border-radius: 10px; border: none; box-shadow: 0 2px 6px rgba(0,0,0,0.05); font-size: 0.85rem; height: 36px; } }
}

.transferts-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 1rem; }
.transfert-card {
  background: white; border-radius: 16px; overflow: hidden; box-shadow: 0 2px 12px rgba(0,0,0,0.05); position: relative;
  .card-status-line { height: 4px; width: 100%; &.demande { background: #f59e0b; } &.approuve { background: #3b82f6; } &.expedie { background: #2563eb; } &.recu { background: #10b981; } &.refuse { background: #ef4444; } }
  .card-body { padding: 1rem; .card-header-top { display: flex; justify-content: space-between; .trans-no { font-family: monospace; font-weight: bold; color: #64748b; } } }
  .card-actions-bar { display: flex; justify-content: flex-end; gap: 0.5rem; padding: 0.75rem; border-top: 1px solid #f1f5f9; }
}

.route-display {
  display: flex; align-items: center; justify-content: space-between; margin: 1rem 0; padding: 0.75rem; background: #f8fafc; border-radius: 10px;
  .node { display: flex; flex-direction: column; align-items: center; gap: 0.25rem; i { color: #3b82f6; } span { font-size: 0.75rem; font-weight: 600; } }
  .path { flex: 1; display: flex; align-items: center; padding: 0 0.75rem; .line { height: 2px; flex: 1; background: #e2e8f0; } i { color: #cbd5e1; margin-left: -5px; } }
}

.equipement-info-box {
  display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem; border: 1px solid #f1f5f9; border-radius: 10px; margin-bottom: 1rem;
  .equip-icon { width: 36px; height: 36px; background: #eff6ff; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #3b82f6; }
  .equip-details { display: flex; flex-direction: column; strong { font-size: 0.85rem; } span { font-size: 0.7rem; color: #64748b; } }
}

.form-group {
  display: flex; flex-direction: column; gap: 0.5rem; margin-bottom: 1rem;
  label { font-size: 0.85rem; font-weight: 600; color: #475569; }
}
</style>
