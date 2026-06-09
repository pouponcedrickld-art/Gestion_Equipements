<template>
  <DirectionLayout>
    <div class="equipement-detail-view" v-if="equipement">
      <div class="page-header">
        <Button icon="pi pi-arrow-left" class="p-button-text p-button-secondary mr-2"
          @click="$router.push('/equipements')" />
        <div class="title-container">
          <div class="flex align-items-center gap-2">
            <h1>{{ equipement.nom }}</h1>
            <Tag v-if="equipement.is_lot" :value="`Lot x${equipement.quantite}`" severity="info" />
          </div>
          <p class="subtitle">{{ equipement.marque }} {{ equipement.modele }}</p>
          <div class="badges">
            <Tag :value="getStatutLabel(equipement.statut_global)"
              :severity="getStatutSeverity(equipement.statut_global)" />
            <Tag :value="getEtatLabel(equipement.etat)" :severity="getEtatSeverity(equipement.etat)" />
          </div>
        </div>
      </div>

      <div class="grid">
        <div class="col-12 lg:col-4">
          <Card class="info-card mb-4">
            <template #header>
              <div class="photo-container">
                <img v-if="equipement.photo" :src="`${apiBaseUrl}/storage/${equipement.photo}`" alt="Photo"
                  class="equipement-photo" />
                <div v-else class="photo-placeholder">
                  <i class="pi pi-image"></i>
                  <span>Aucune photo</span>
                </div>
              </div>
            </template>
            <template #content>
              <div class="detail-list">
                <div class="detail-item">
                  <span class="label">Référence</span>
                  <span class="value">{{ equipement.reference }}</span>
                </div>
                <div class="detail-item">
                  <span class="label">Code Inventaire</span>
                  <span class="value">{{ equipement.code_inventaire }}</span>
                </div>
                <div class="detail-item">
                  <span class="label">Numéro de Série</span>
                  <span class="value">{{ equipement.numero_serie }}</span>
                </div>
                <div class="detail-item" v-if="equipement.imei">
                  <span class="label">IMEI</span>
                  <span class="value">{{ equipement.imei }}</span>
                </div>
                <div class="detail-item">
                  <span class="label">Catégorie</span>
                  <span class="value">
                    <i class="pi pi-tag mr-1 text-primary"></i>
                    {{ equipement.categorie?.nom }}
                  </span>
                </div>
              </div>
            </template>
          </Card>

          <Card class="qr-card" v-if="equipement.qr_code">
            <template #title>Identification QR</template>
            <template #content>
              <div class="qr-container">
                <img :src="`${apiBaseUrl}/storage/${equipement.qr_code}`" alt="QR Code" class="qr-img" />
                <Button label="Télécharger QR" icon="pi pi-download" class="p-button-text p-button-sm mt-2"
                  @click="downloadQR" />
              </div>
            </template>
          </Card>
        </div>

        <div class="col-12 lg:col-8">
          <Card class="tabs-card">
            <template #content>
              <Tabs value="details">
                <TabList>
                  <Tab value="details">Détails</Tab>
                  <Tab value="historique">Historique</Tab>
                  <Tab value="affectations">Affectations</Tab>
                  <Tab value="consommables">Consommables</Tab>
                  <Tab value="lot" v-if="equipement.lot_reference">Membres du lot</Tab>
                </TabList>

                <TabPanels>
                  <TabPanel value="details">
                    <div class="grid">
                      <div class="col-12 md:col-6">
                        <h4 class="tab-section-title">Localisation & Propriété</h4>
                        <div class="detail-list">
                          <div class="detail-item">
                            <span class="label">Agence Actuelle</span>
                            <span class="value">{{ equipement.agence_actuelle?.nom || 'Non définie' }}</span>
                          </div>
                          <div class="detail-item">
                            <span class="label">Agence Propriétaire</span>
                            <span class="value">{{ equipement.agence_proprietaire?.nom || 'Non définie' }}</span>
                          </div>
                          <div class="detail-item">
                            <span class="label">Localisation précise</span>
                            <span class="value">{{ equipement.localisation || 'Non précisée' }}</span>
                          </div>
                        </div>
                      </div>
                      <div class="col-12 md:col-6">
                        <h4 class="tab-section-title">Acquisition & Garantie</h4>
                        <div class="detail-list">
                          <div class="detail-item">
                            <span class="label">Date d'acquisition</span>
                            <span class="value">{{ formatDate(equipement.date_acquisition) }}</span>
                          </div>
                          <div class="detail-item">
                            <span class="label">Prix d'achat</span>
                            <span class="value">{{ formatCurrency(equipement.prix_achat) }}</span>
                          </div>
                          <div class="detail-item">
                            <span class="label">Fin de garantie</span>
                            <span :class="getGarantieClass(equipement.garantie_date_fin)">{{
                              formatDate(equipement.garantie_date_fin)
                              }}</span>
                          </div>
                          <div class="detail-item">
                            <span class="label">Fournisseur</span>
                            <span class="value">{{ equipement.fournisseur || 'Non renseigné' }}</span>
                          </div>
                        </div>
                      </div>
                    </div>
                  </TabPanel>

                  <TabPanel value="historique">
                    <Timeline v-if="equipement.mouvements?.length" :value="equipement.mouvements" align="left"
                      class="custom-timeline">
                      <template #opposite="slotProps">
                        <small class="text-secondary">
                          {{ formatDate(slotProps.item.date_mouvement, true) }}
                        </small>
                      </template>
                      <template #content="slotProps">
                        <div class="timeline-content">
                          <div class="timeline-type">
                            {{ getMouvementLabel(slotProps.item.type_mouvement) }}
                          </div>
                          <div class="timeline-desc">
                            {{ slotProps.item.description }}
                          </div>
                          <small class="timeline-user">
                            Par: {{ slotProps.item.user?.name || 'Système' }}
                          </small>
                        </div>
                      </template>
                    </Timeline>
                    <div v-else class="empty-tab">
                      <i class="pi pi-history"></i>
                      <p>Aucun mouvement enregistré</p>
                    </div>
                  </TabPanel>

                  <TabPanel value="affectations">
                    <DataTable :value="equipement.affectations" responsiveLayout="scroll" class="p-datatable-sm">
                      <Column field="agent.nom" header="Agent">
                        <template #body="slotProps">
                          {{ slotProps.data.agent?.nom }} {{ slotProps.data.agent?.prenom }}
                        </template>
                      </Column>
                      <Column field="date_affectation" header="Début">
                        <template #body="slotProps">
                          {{ formatDate(slotProps.data.date_affectation) }}
                        </template>
                      </Column>
                      <Column field="date_retour_effectif" header="Fin">
                        <template #body="slotProps">
                          {{ slotProps.data.date_retour_effectif
                            ? formatDate(slotProps.data.date_retour_effectif)
                            : 'En cours'
                          }}
                        </template>
                      </Column>
                      <Column field="statut" header="Statut">
                        <template #body="slotProps">
                          <Tag :value="slotProps.data.statut === 'active' ? 'En cours' : 'Terminé'"
                            :severity="slotProps.data.statut === 'active' ? 'success' : 'secondary'" />
                        </template>
                      </Column>
                    </DataTable>
                    <div v-if="!equipement.affectations?.length" class="empty-tab">
                      <i class="pi pi-user"></i>
                      <p>Aucune affectation enregistrée</p>
                    </div>
                  </TabPanel>

                  <TabPanel value="consommables">
                    <div class="grid" v-if="equipement.consommables?.length">
                      <div v-for="cons in equipement.consommables" :key="cons.id" class="col-12 md:col-6">
                        <div class="cons-item">
                          <div class="cons-info">
                            <strong>{{ cons.nom }}</strong>
                            <small>{{ cons.type }}</small>
                          </div>
                          <Badge :value="cons.quantite" :severity="cons.quantite > 0 ? 'success' : 'danger'" />
                        </div>
                      </div>
                    </div>
                    <div v-else class="empty-tab">
                      <i class="pi pi-box"></i>
                      <p>Aucun consommable lié</p>
                    </div>
                  </TabPanel>

                  <!-- Nouvel onglet pour le lot -->
                  <TabPanel value="lot" v-if="equipement.lot_reference">
                    <div class="lot-info-banner mb-4">
                      <i class="pi pi-info-circle mr-2"></i>
                      <span>Cet équipement fait partie du lot <strong>{{ equipement.lot_reference }}</strong></span>
                    </div>

                    <DataTable :value="[equipement, ...(equipement.related_in_lot || [])]" class="p-datatable-sm">
                      <Column field="code_inventaire" header="Code Inventaire">
                        <template #body="{ data }">
                          <span :class="{ 'font-bold text-blue-600': data.id === equipement.id }">
                            {{ data.code_inventaire }}
                            <Tag v-if="data.id === equipement.id" value="Actuel" severity="info" class="ml-2 text-xs" />
                          </span>
                        </template>
                      </Column>
                      <Column field="nom" header="Désignation"></Column>
                      <Column field="numero_serie" header="N° Série"></Column>
                      <Column field="etat" header="État">
                        <template #body="{ data }">
                          <Tag :value="getEtatLabel(data.etat)" :severity="getEtatSeverity(data.etat)" />
                        </template>
                      </Column>
                      <Column header="Actions">
                        <template #body="{ data }">
                          <Button v-if="data.id !== equipement.id" icon="pi pi-eye" class="p-button-text p-button-sm"
                            @click="$router.push(`/equipements/${data.id}`)" />
                        </template>
                      </Column>
                    </DataTable>
                  </TabPanel>
                </TabPanels>
              </Tabs>
            </template>
          </Card>
        </div>
      </div>
    </div>
    <div v-else-if="loading" class="loading-state">
      <i class="pi pi-spin pi-spinner" style="font-size: 2rem"></i>
      <p>Chargement des détails de l'équipement...</p>
    </div>
  </DirectionLayout>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useToast } from 'primevue/usetoast'
import DirectionLayout from '@/layouts/DirectionLayout.vue'
import { useEquipementStore } from '@/stores/equipementStore'

// PrimeVue Components
import Button from 'primevue/button'
import Card from 'primevue/card'
import Tag from 'primevue/tag'
import Tabs from 'primevue/tabs'
import TabList from 'primevue/tablist'
import Tab from 'primevue/tab'
import TabPanels from 'primevue/tabpanels'
import TabPanel from 'primevue/tabpanel'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Timeline from 'primevue/timeline'
import Badge from 'primevue/badge'

const route = useRoute()
const router = useRouter()
const toast = useToast()
const equipementStore = useEquipementStore()

const equipement = ref(null)
const loading = ref(true)
const loadingQR = ref(false)
const apiBaseUrl = import.meta.env.VITE_API_URL?.split('/api')[0] || 'http://localhost:8000'
console.log('API Base URL:', apiBaseUrl)

const loadEquipement = async () => {
  loading.value = true
  try {
    const data = await equipementStore.fetchEquipement(route.params.id)
    console.log('Equipement data loaded:', data)
    equipement.value = data
  } catch (error) {
    toast.add({
      severity: 'error',
      summary: 'Erreur',
      detail: 'Impossible de charger l\'équipement',
      life: 3000
    })
    router.push('/equipements')
  } finally {
    loading.value = false
  }
}

const generateQR = async () => {
  loadingQR.value = true
  try {
    await equipementStore.generateQRCode(equipement.value.id)
    await loadEquipement()
    toast.add({
      severity: 'success',
      summary: 'Succès',
      detail: 'QR Code généré avec succès',
      life: 3000
    })
  } catch (error) {
    toast.add({
      severity: 'error',
      summary: 'Erreur',
      detail: 'Échec de la génération du QR',
      life: 3000
    })
  } finally {
    loadingQR.value = false
  }
}

const downloadQR = () => {
  if (equipement.value.qr_code) {
    const link = document.createElement('a')
    link.href = `${apiBaseUrl}/storage/${equipement.value.qr_code}`
    link.download = `QR_${equipement.value.reference}.png`
    link.click()
  }
}

const formatDate = (date, withTime = false) => {
  if (!date) return '-'
  const options = { day: '2-digit', month: '2-digit', year: 'numeric' }
  if (withTime) {
    options.hour = '2-digit'
    options.minute = '2-digit'
  }
  return new Date(date).toLocaleDateString('fr-FR', options)
}

const formatCurrency = (value) => {
  if (value === null || value === undefined) return '-'
  return new Intl.NumberFormat('fr-FR', {
    style: 'currency',
    currency: 'XOF'
  }).format(value)
}

const getStatutLabel = (statut) => {
  const labels = {
    en_stock_general: 'Stock Général',
    en_stock_local: 'Stock Local',
    affecte: 'Affecté',
    en_transit: 'En Transit',
    en_panne: 'En Panne',
    en_maintenance: 'En Maintenance',
    reforme: 'Réformé'
  }
  return labels[statut] || statut
}

const getStatutSeverity = (statut) => {
  const severities = {
    en_stock_general: 'success',
    en_stock_local: 'success',
    affecte: 'info',
    en_transit: 'warning',
    en_panne: 'danger',
    en_maintenance: 'warning',
    reforme: 'secondary'
  }
  return severities[statut] || 'secondary'
}

const getEtatLabel = (etat) => {
  const labels = {
    neuf: 'Neuf',
    en_service: 'En Service',
    en_panne: 'En Panne',
    en_maintenance: 'En Maintenance',
    reforme: 'Réformé',
    perdu: 'Perdu'
  }
  return labels[etat] || etat
}

const getEtatSeverity = (etat) => {
  const severities = {
    neuf: 'success',
    en_service: 'success',
    en_panne: 'danger',
    en_maintenance: 'warning',
    reforme: 'secondary',
    perdu: 'danger'
  }
  return severities[etat] || 'secondary'
}

const getMouvementLabel = (type) => {
  const labels = {
    creation: 'Création',
    transfert: 'Transfert',
    affectation: 'Affectation',
    retour: 'Retour',
    modification: 'Modification',
    changement_etat: 'Changement d\'état',
    qr_generation: 'QR Code'
  }
  return labels[type] || type
}

const getGarantieClass = (date) => {
  if (!date) return ''
  const fin = new Date(date)
  const now = new Date()
  if (fin < now) return 'text-danger font-bold'
  const in30Days = new Date()
  in30Days.setDate(in30Days.getDate() + 30)
  if (fin < in30Days) return 'text-warning font-bold'
  return 'text-success'
}

onMounted(loadEquipement)

watch(() => route.params.id, (newId) => {
  if (newId) loadEquipement()
})
</script>

<style scoped>
.equipement-detail-view {
  padding: 1rem;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.page-header .title-container h1 {
  margin: 0 0 0.25rem 0;
  color: #1e293b !important;
  font-size: 1.4rem;
  font-weight: 800;
}

.subtitle {
  color: #64748b !important;
  margin: 0 0 0.25rem 0;
  font-size: 0.85rem;
}

.page-header .badges {
  display: flex;
  gap: 0.4rem;
  :deep(.p-tag) { font-size: 0.65rem; padding: 0.2rem 0.5rem; }
}

.info-card,
.qr-card,
.tabs-card {
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 12px rgba(0,0,0,0.05);
  border: none;
  margin-bottom: 1rem !important;
}

.photo-container {
  height: 180px;
  background: #f8fafc;
  display: flex;
  align-items: center;
  justify-content: center;
  border-bottom: 1px solid #f1f5f9;
}

.equipement-photo {
  width: 100%;
  height: 100%;
  object-fit: contain;
}

.photo-placeholder {
  display: flex;
  flex-direction: column;
  align-items: center;
  color: #cbd5e1;
  i { font-size: 2rem; }
  span { font-size: 0.8rem; }
}

.detail-list .detail-item {
  display: flex;
  justify-content: space-between;
  padding: 0.5rem 0;
  border-bottom: 1px solid #f1f5f9;
}

.detail-list .detail-item:last-child {
  border-bottom: none;
}

.detail-list .detail-item .label {
  color: #64748b;
  font-size: 0.8rem;
}

.detail-list .detail-item .value {
  color: #1e293b;
  font-weight: 600;
  font-size: 0.85rem;
}

.qr-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 0.75rem;
}

.qr-img {
  width: 100px;
  height: 100px;
  border-radius: 8px;
  background: white;
  padding: 8px;
  border: 1px solid #f1f5f9;
}

.tab-section-title {
  color: #3b82f6;
  margin: 1rem 0 0.75rem 0;
  font-size: 0.9rem;
  font-weight: 700;
  border-left: 3px solid #3b82f6;
  padding-left: 0.5rem;
}

.custom-timeline {
  padding: 0.5rem;
}

.timeline-content {
  background: #f8fafc;
  padding: 0.75rem;
  border-radius: 10px;
  margin-bottom: 0.75rem;
  border: 1px solid #f1f5f9;
}

.timeline-type {
  font-weight: 700;
  color: #3b82f6;
  font-size: 0.8rem;
  margin-bottom: 0.15rem;
  text-transform: uppercase;
}

.timeline-desc {
  color: #334155;
  font-size: 0.85rem;
  margin-bottom: 0.25rem;
}

.timeline-user {
  color: #94a3b8;
  font-style: italic;
  font-size: 0.75rem;
}

.empty-tab {
  text-align: center;
  padding: 2rem 1rem;
  color: #cbd5e1;
}

.empty-tab i {
  font-size: 2.5rem;
  margin-bottom: 1rem;
}

.empty-tab p {
  color: #94a3b8;
  font-size: 0.95rem;
  margin: 0;
}

.lot-info-banner {
  background: #eff6ff;
  border-left: 3px solid #3b82f6;
  padding: 0.75rem 1rem;
  border-radius: 6px;
  color: #1e40af;
  display: flex;
  align-items: center;
  font-weight: 500;
  font-size: 0.85rem;
}

.cons-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: white;
  padding: 0.75rem 1rem;
  border-radius: 10px;
  border: 1px solid #f1f5f9;
  box-shadow: 0 2px 6px rgba(0,0,0,0.02);
  margin-bottom: 0.5rem;
}

.cons-item .cons-info {
  display: flex;
  flex-direction: column;
}

.cons-item .cons-info strong {
  color: #1e293b;
  font-size: 0.9rem;
}

.cons-item .cons-info small {
  color: #64748b;
  text-transform: uppercase;
  font-size: 0.65rem;
  font-weight: 600;
  margin-top: 0.15rem;
}

.loading-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 60vh;
  color: #94a3b8;
}

.loading-state p {
  margin-top: 1rem;
  font-weight: 500;
}

.text-danger {
  color: #ef4444 !important;
}

.text-warning {
  color: #f59e0b !important;
}

.text-success {
  color: #10b981 !important;
}

:deep(.p-tablist-content) {
  background: transparent !important;
  border-bottom: 1px solid #f1f5f9 !important;
}

:deep(.p-tabpanels) {
  background: transparent !important;
  padding: 1rem 0 !important;
}

:deep(.p-tab) {
  background: transparent !important;
  color: #64748b !important;
  font-weight: 600 !important;
  font-size: 0.85rem !important;
  padding: 0.75rem 1rem !important;
}

:deep(.p-tab-active) {
  color: #3b82f6 !important;
  border-color: #3b82f6 !important;
}

:deep(.p-datatable-sm) {
  .p-datatable-thead > tr > th {
    padding: 0.5rem;
    font-size: 0.75rem;
  }
  .p-datatable-tbody > tr > td {
    padding: 0.5rem;
    font-size: 0.8rem;
  }
}
</style>
