<template>
  <DirectionLayout>
    <div class="transfert-detail-view" v-if="!loading && transfert">
      <div class="page-header animate-in">
        <div class="title-container">
          <div class="flex align-items-center gap-2">
            <h1>Transfert #{{ transfert.id }}</h1>
            <Tag :value="transfert.statut" :severity="statutSeverity" />
          </div>
          <p class="subtitle">Créé le {{ formatDate(transfert.date_demande) }} par {{ transfert.demande_par?.nom || 'N/A' }}</p>
        </div>
        <div class="actions">
          <Button icon="pi pi-arrow-left" label="Retour" class="p-button-text p-button-secondary mr-2" @click="$router.push('/transferts')" />
        </div>
      </div>

      <div class="grid mt-2 animate-in">
        <div class="col-12 lg:col-4">
          <Card class="info-card mb-3">
            <template #title>
              <div class="flex align-items-center gap-2 text-primary">
                <i class="pi pi-info-circle"></i>
                <span class="text-lg">Informations générales</span>
              </div>
            </template>
            <template #content>
              <div class="detail-list">
                <div class="detail-item">
                  <span class="label">Type</span>
                  <span class="value">{{ typeLabel }}</span>
                </div>
                <div class="detail-item">
                  <span class="label">Date demande</span>
                  <span class="value">{{ formatDate(transfert.date_demande) }}</span>
                </div>
                <div class="detail-item" v-if="transfert.date_expedition">
                  <span class="label">Date expédition</span>
                  <span class="value">{{ formatDate(transfert.date_expedition) }}</span>
                </div>
                <div class="detail-item" v-if="transfert.date_reception">
                  <span class="label">Date réception</span>
                  <span class="value">{{ formatDate(transfert.date_reception) }}</span>
                </div>
                <div class="detail-item">
                  <span class="label">Demandé par</span>
                  <span class="value">{{ transfert.demande_par?.nom || 'N/A' }}</span>
                </div>
                <div class="detail-item" v-if="transfert.valide_par">
                  <span class="label">Validé par</span>
                  <span class="value">{{ transfert.valide_par.nom }}</span>
                </div>
              </div>
            </template>
          </Card>

          <Card class="info-card mb-3" v-if="transfert.observations">
            <template #title>
              <div class="flex align-items-center gap-2 text-primary">
                <i class="pi pi-comment"></i>
                <span class="text-lg">Observations</span>
              </div>
            </template>
            <template #content>
              <p class="text-sm text-gray-600 line-height-3">{{ transfert.observations }}</p>
            </template>
          </Card>

          <Card class="info-card" v-if="transfert.motif_refus">
            <template #title>
              <div class="flex align-items-center gap-2 text-danger">
                <i class="pi pi-exclamation-triangle"></i>
                <span class="text-lg">Motif de refus</span>
              </div>
            </template>
            <template #content>
              <p class="text-sm text-red-500 font-bold line-height-3">{{ transfert.motif_refus }}</p>
            </template>
          </Card>
        </div>

        <div class="col-12 lg:col-8">
          <Card class="info-card mb-3">
            <template #title>
              <div class="flex align-items-center gap-2 text-primary">
                <i class="pi pi-share-alt"></i>
                <span class="text-lg">Parcours</span>
              </div>
            </template>
            <template #content>
              <div class="timeline">
                <div class="timeline-item">
                  <div class="timeline-marker source"></div>
                  <div class="timeline-content">
                    <span class="agence-label">Source</span>
                    <strong>{{ transfert.agence_source?.nom || 'Siège' }}</strong>
                  </div>
                </div>
                <div class="timeline-arrow"><i class="pi pi-arrow-down"></i></div>
                <div class="timeline-item">
                  <div class="timeline-marker destination"></div>
                  <div class="timeline-content">
                    <span class="agence-label">Destination</span>
                    <strong>{{ transfert.agence_destination?.nom }}</strong>
                  </div>
                </div>
              </div>
            </template>
          </Card>

          <Card class="info-card mb-3">
            <template #title>
              <div class="flex align-items-center gap-2 text-primary">
                <i class="pi pi-box"></i>
                <span class="text-lg">Équipement</span>
              </div>
            </template>
            <template #content>
              <div class="flex align-items-center gap-3">
                <div class="equip-icon">
                  <i class="pi pi-mobile text-2xl"></i>
                </div>
                <div class="flex flex-column">
                  <span class="font-bold text-lg">{{ transfert.equipement?.nom || transfert.equipement?.marque + ' ' + transfert.equipement?.modele }}</span>
                  <small class="text-gray-500">SN: {{ transfert.equipement?.numero_serie || 'N/A' }}</small>
                  <small class="text-gray-500" v-if="transfert.equipement?.categorie">Catégorie: {{ transfert.equipement.categorie.nom }}</small>
                </div>
              </div>
            </template>
          </Card>

          <Card class="actions-card" v-if="availableActions.length > 0">
            <template #title>
              <div class="flex align-items-center gap-2 text-primary">
                <i class="pi pi-cog"></i>
                <span class="text-lg">Actions</span>
              </div>
            </template>
            <template #content>
              <div class="actions-bar">
                <Button v-if="availableActions.includes('approuver')" label="Approuver" icon="pi pi-check" class="p-button-success" @click="approuver" :loading="actionLoading" />
                <Button v-if="availableActions.includes('refuser')" label="Refuser" icon="pi pi-times" class="p-button-danger" @click="ouvrirRefusDialog" :loading="actionLoading" />
                <Button v-if="availableActions.includes('expedier')" label="Expédier" icon="pi pi-send" class="p-button-warning" @click="expedier" :loading="actionLoading" />
                <Button v-if="availableActions.includes('recevoir')" label="Recevoir" icon="pi pi-inbox" class="p-button-primary" @click="recevoir" :loading="actionLoading" />
              </div>
            </template>
          </Card>
        </div>
      </div>
    </div>

    <div v-else-if="loading" class="loading-state">
      <i class="pi pi-spin pi-spinner" style="font-size: 2rem"></i>
      <p>Chargement des détails du transfert...</p>
    </div>

    <Dialog v-model:visible="refusDialogVisible" header="Refuser le transfert" :modal="true" :style="{ width: '450px' }">
      <div class="flex flex-column gap-2">
        <label for="motif" class="font-bold block mb-2">Motif du refus <span class="required">*</span></label>
        <Textarea id="motif" v-model="refusMotif" rows="4" placeholder="Veuillez indiquer la raison..." class="w-full" />
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

import Card from 'primevue/card'
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
  const severities = {
    demande: 'warning',
    approuve: 'info',
    expedie: 'primary',
    recu: 'success',
    refuse: 'danger'
  }
  return severities[transfert.value?.statut] || 'secondary'
})

const availableActions = computed(() => {
  return transfertStore.getAvailableActions(transfert.value)
})

const formatDate = (date) => {
  if (!date) return 'N/A'
  return new Date(date).toLocaleDateString('fr-FR', { dateStyle: 'long' })
}

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
  } catch {
    toast.add({ severity: 'error', summary: 'Erreur', detail: 'Impossible de charger le transfert', life: 3000 })
    router.push('/transferts')
  } finally {
    loading.value = false
  }

  gsap.from('.animate-in', { opacity: 0, y: 20, duration: 0.8, stagger: 0.2, ease: 'power3.out' })
})
</script>

<style scoped lang="scss">
.transfert-detail-view {
  padding: 1rem;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;

  .title-container {
    h1 {
      margin: 0;
      color: #1e293b;
      font-size: 1.4rem;
      font-weight: 800;
    }
  }

  .subtitle {
    color: #64748b;
    margin: 0.25rem 0 0 0;
    font-size: 0.85rem;
  }
}

.info-card {
  border-radius: 12px;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.05);
  border: none;
}

.detail-list .detail-item {
  display: flex;
  justify-content: space-between;
  padding: 0.5rem 0;
  border-bottom: 1px solid #f1f5f9;

  &:last-child {
    border-bottom: none;
  }

  .label {
    color: #64748b;
    font-size: 0.8rem;
  }

  .value {
    color: #1e293b;
    font-weight: 600;
    font-size: 0.85rem;
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
        letter-spacing: 0.06em;
        display: block;
      }

      strong {
        color: #1e293b;
        font-size: 0.95rem;
      }
    }
  }

  .timeline-arrow {
    color: #cbd5e1;
    font-size: 1.2rem;
  }
}

.equip-icon {
  width: 48px;
  height: 48px;
  background: #f0f4ff;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #6366f1;
}

.actions-card {
  border-radius: 12px;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.05);
  border: none;
}

.actions-bar {
  display: flex;
  gap: 0.75rem;
  flex-wrap: wrap;
}

.loading-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 60vh;
  color: #94a3b8;
}

:deep(.p-dialog) .required {
  color: #ef4444;
}
</style>
