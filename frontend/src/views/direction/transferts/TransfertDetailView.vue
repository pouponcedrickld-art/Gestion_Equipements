<template>
  <DirectionLayout>
    <div class="transfert-detail-container" v-if="!loading">
      <div class="detail-header animate-in">
        <Button icon="pi pi-arrow-left" class="p-button-text p-button-rounded back-btn" @click="$router.push('/transferts')" />
        <div class="header-info">
          <h1>Transfert #{{ transfert.id }}</h1>
          <Tag :value="transfert.statut" :severity="statutSeverity" />
        </div>
      </div>

      <div class="detail-grid animate-in">
        <div class="info-card">
          <h3><i class="pi pi-info-circle"></i> Informations générales</h3>
          <div class="info-row">
            <span class="label">Type</span>
            <span class="value">{{ typeLabel }}</span>
          </div>
          <div class="info-row">
            <span class="label">Date demande</span>
            <span class="value">{{ formatDate(transfert.date_demande) }}</span>
          </div>
          <div class="info-row" v-if="transfert.date_expedition">
            <span class="label">Date expédition</span>
            <span class="value">{{ formatDate(transfert.date_expedition) }}</span>
          </div>
          <div class="info-row" v-if="transfert.date_reception">
            <span class="label">Date réception</span>
            <span class="value">{{ formatDate(transfert.date_reception) }}</span>
          </div>
          <div class="info-row">
            <span class="label">Demandé par</span>
            <span class="value">{{ transfert.demande_par?.nom || 'N/A' }}</span>
          </div>
          <div class="info-row" v-if="transfert.valide_par">
            <span class="label">Validé par</span>
            <span class="value">{{ transfert.valide_par.nom }}</span>
          </div>
        </div>

        <div class="info-card">
          <h3><i class="pi pi-share-alt"></i> Parcours</h3>
          <div class="timeline">
            <div class="timeline-item">
              <div class="timeline-marker source"></div>
              <div class="timeline-content">
                <span class="agence-label">Source</span>
                <strong>{{ transfert.agence_source?.nom || 'Siège' }}</strong>
              </div>
            </div>
            <div class="timeline-arrow">
              <i class="pi pi-arrow-down"></i>
            </div>
            <div class="timeline-item">
              <div class="timeline-marker destination"></div>
              <div class="timeline-content">
                <span class="agence-label">Destination</span>
                <strong>{{ transfert.agence_destination?.nom }}</strong>
              </div>
            </div>
          </div>
        </div>

        <div class="info-card">
          <h3><i class="pi pi-box"></i> Équipement</h3>
          <div class="equip-detail">
            <div class="equip-icon">
              <i class="pi pi-mobile"></i>
            </div>
            <div class="equip-info">
              <strong>{{ transfert.equipement?.nom || transfert.equipement?.marque + ' ' + transfert.equipement?.modele }}</strong>
              <span class="text-muted">SN: {{ transfert.equipement?.numero_serie || 'N/A' }}</span>
              <span class="text-muted" v-if="transfert.equipement?.categorie">Catégorie: {{ transfert.equipement.categorie.nom }}</span>
            </div>
          </div>
        </div>

        <div class="info-card" v-if="transfert.observations">
          <h3><i class="pi pi-comment"></i> Observations</h3>
          <p class="observations-text">{{ transfert.observations }}</p>
        </div>

        <div class="info-card" v-if="transfert.motif_refus">
          <h3><i class="pi pi-exclamation-triangle text-danger"></i> Motif de refus</h3>
          <p class="refus-text">{{ transfert.motif_refus }}</p>
        </div>
      </div>

      <div class="actions-bar animate-in" v-if="availableActions.length > 0">
        <Button v-if="availableActions.includes('approuver')" label="Approuver" icon="pi pi-check" class="p-button-success" @click="approuver" :loading="actionLoading" />
        <Button v-if="availableActions.includes('refuser')" label="Refuser" icon="pi pi-times" class="p-button-danger" @click="ouvrirRefusDialog" :loading="actionLoading" />
        <Button v-if="availableActions.includes('expedier')" label="Expédier" icon="pi pi-send" class="p-button-warning" @click="expedier" :loading="actionLoading" />
        <Button v-if="availableActions.includes('recevoir')" label="Recevoir" icon="pi pi-inbox" class="p-button-primary" @click="recevoir" :loading="actionLoading" />
      </div>
    </div>

    <div class="loading-container" v-else>
      <i class="pi pi-spin pi-spinner text-4xl text-primary"></i>
    </div>

    <Dialog v-model:visible="refusDialogVisible" header="Refuser le transfert" :modal="true" :style="{ width: '450px' }">
      <div class="form-group">
        <label for="motif">Motif du refus *</label>
        <Textarea id="motif" v-model="refusMotif" rows="4" class="w-full" placeholder="Veuillez indiquer la raison..." />
      </div>
      <template #footer>
        <Button label="Annuler" icon="pi pi-times" class="p-button-text" @click="refusDialogVisible = false" />
        <Button label="Confirmer le refus" icon="pi pi-check" class="p-button-danger" @click="confirmerRefus" :loading="actionLoading" />
      </template>
    </Dialog>
  </DirectionLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useToast } from 'primevue/usetoast'
import DirectionLayout from '@/layouts/DirectionLayout.vue'
import { useTransfertStore } from '@/stores/transfertStore'
import gsap from 'gsap'

import Button from 'primevue/button'
import Tag from 'primevue/tag'
import Dialog from 'primevue/dialog'
import Textarea from 'primevue/textarea'

const route = useRoute()
const router = useRouter()
const toast = useToast()
const transfertStore = useTransfertStore()

const loading = ref(true)
const actionLoading = ref(false)
const transfert = ref(null)
const refusDialogVisible = ref(false)
const refusMotif = ref('')

const typeLabel = computed(() => {
  const labels = {
    livraison_generale: 'Livraison générale',
    retour_generale: 'Retour générale',
    transfert_interne: 'Transfert interne'
  }
  return labels[transfert.value?.type_transfert] || transfert.value?.type_transfert
})

const statutSeverity = computed(() => {
  switch (transfert.value?.statut) {
    case 'demande': return 'warning'
    case 'approuve': return 'info'
    case 'expedie': return 'primary'
    case 'recu': return 'success'
    case 'refuse': return 'danger'
    default: return 'secondary'
  }
})

const availableActions = computed(() => {
  return transfertStore.getAvailableActions(transfert.value)
})

const formatDate = (date) => date ? new Date(date).toLocaleDateString('fr-FR', { dateStyle: 'long' }) : 'N/A'

const approuver = async () => {
  actionLoading.value = true
  try {
    const updated = await transfertStore.approuverTransfert(transfert.value.id)
    transfert.value = updated
    toast.add({ severity: 'success', summary: 'Succès', detail: 'Transfert approuvé', life: 3000 })
  } catch {
    toast.add({ severity: 'error', summary: 'Erreur', detail: 'Échec de l\'approbation', life: 3000 })
  } finally {
    actionLoading.value = false
  }
}

const expedier = async () => {
  actionLoading.value = true
  try {
    const updated = await transfertStore.expedierTransfert(transfert.value.id)
    transfert.value = updated
    toast.add({ severity: 'success', summary: 'Succès', detail: 'Équipement expédié', life: 3000 })
  } catch {
    toast.add({ severity: 'error', summary: 'Erreur', detail: 'Échec de l\'expédition', life: 3000 })
  } finally {
    actionLoading.value = false
  }
}

const recevoir = async () => {
  actionLoading.value = true
  try {
    const updated = await transfertStore.recevoirTransfert(transfert.value.id)
    transfert.value = updated
    toast.add({ severity: 'success', summary: 'Succès', detail: 'Équipement reçu', life: 3000 })
  } catch {
    toast.add({ severity: 'error', summary: 'Erreur', detail: 'Échec de la réception', life: 3000 })
  } finally {
    actionLoading.value = false
  }
}

const ouvrirRefusDialog = () => {
  refusMotif.value = ''
  refusDialogVisible.value = true
}

const confirmerRefus = async () => {
  if (!refusMotif.value.trim()) {
    toast.add({ severity: 'warn', summary: 'Attention', detail: 'Indiquez un motif', life: 3000 })
    return
  }
  actionLoading.value = true
  try {
    const updated = await transfertStore.refuserTransfert(transfert.value.id, refusMotif.value)
    transfert.value = updated
    refusDialogVisible.value = false
    toast.add({ severity: 'success', summary: 'Succès', detail: 'Transfert refusé', life: 3000 })
  } catch {
    toast.add({ severity: 'error', summary: 'Erreur', detail: 'Échec du refus', life: 3000 })
  } finally {
    actionLoading.value = false
  }
}

onMounted(async () => {
  const id = parseInt(route.params.id)
  try {
    transfert.value = await transfertStore.fetchTransfert(id)
  } catch (err) {
    toast.add({ severity: 'error', summary: 'Erreur', detail: 'Impossible de charger le transfert', life: 3000 })
    router.push('/transferts')
  } finally {
    loading.value = false
  }

  gsap.from('.animate-in', { opacity: 0, y: 20, duration: 0.6, stagger: 0.15, ease: 'power3.out' })
})
</script>

<style scoped lang="scss">
.transfert-detail-container {
  padding: 2rem;
  max-width: 1000px;
  margin: 0 auto;
}

.loading-container {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 400px;
}

.detail-header {
  display: flex;
  align-items: center;
  gap: 1.5rem;
  margin-bottom: 2rem;

  .header-info {
    display: flex;
    align-items: center;
    gap: 1rem;

    h1 {
      font-size: 1.8rem;
      font-weight: 800;
      color: #1e293b;
      margin: 0;
    }
  }
}

.detail-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1.5rem;
}

.info-card {
  background: white;
  border-radius: 16px;
  padding: 1.5rem;
  box-shadow: 0 4px 12px rgba(0,0,0,0.05);
  border: 1px solid #f1f5f9;

  h3 {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 1rem;
    font-weight: 700;
    color: #1e293b;
    margin: 0 0 1.2rem;
    padding-bottom: 0.8rem;
    border-bottom: 1px solid #f1f5f9;
  }
}

.info-row {
  display: flex;
  justify-content: space-between;
  padding: 0.5rem 0;

  .label {
    color: #64748b;
    font-weight: 500;
  }

  .value {
    color: #1e293b;
    font-weight: 600;
  }
}

.timeline {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.5rem;

  .timeline-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    width: 100%;

    .timeline-marker {
      width: 16px;
      height: 16px;
      border-radius: 50%;
      flex-shrink: 0;

      &.source { background: #6366f1; }
      &.destination { background: #8b5cf6; }
    }

    .timeline-content {
      .agence-label {
        font-size: 0.75rem;
        color: #94a3b8;
        text-transform: uppercase;
        font-weight: 600;
        display: block;
      }
    }
  }

  .timeline-arrow {
    color: #cbd5e1;
  }
}

.equip-detail {
  display: flex;
  align-items: center;
  gap: 1rem;

  .equip-icon {
    width: 48px;
    height: 48px;
    background: #f0f4ff;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #6366f1;
    font-size: 1.5rem;
  }

  .equip-info {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
  }
}

.observations-text {
  color: #475569;
  line-height: 1.6;
  margin: 0;
}

.refus-text {
  color: #ef4444;
  line-height: 1.6;
  margin: 0;
  font-weight: 500;
}

.actions-bar {
  display: flex;
  gap: 1rem;
  margin-top: 2rem;
  padding: 1.5rem;
  background: white;
  border-radius: 16px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.05);
  border: 1px solid #f1f5f9;
}
</style>
