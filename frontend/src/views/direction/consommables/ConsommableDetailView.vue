<template>
  <DirectionLayout>
    <div class="consommable-detail-view" v-if="!loading && consommable">
      <!-- En-tête -->
      <div class="page-header animate-in">
        <div class="title-container">
          <div class="flex align-items-center gap-2">
            <h1>{{ consommable.nom }}</h1>
            <Tag :value="getStockStatusLabel(consommable)" :severity="getStockStatusSeverity(consommable)" />
          </div>
          <p class="subtitle">{{ getTypeLabel(consommable.type) }} | Créé le {{ formatDate(consommable.created_at) }}</p>
        </div>
        <div class="actions">
          <Button icon="pi pi-arrow-left" label="Retour" class="p-button-text p-button-secondary mr-2" @click="$router.push('/consommables')" />
          <Button icon="pi pi-plus-minus" label="Ajuster Stock" class="p-button-outlined p-button-secondary mr-2" @click="showStockDialog = true" />
          <Button icon="pi pi-pencil" label="Modifier" class="p-button-primary" @click="editConsommable" />
        </div>
      </div>

      <div class="grid mt-2 animate-in">
        <!-- Colonne Gauche: Infos & Stock -->
        <div class="col-12 lg:col-4">
          <Card class="info-card mb-3">
            <template #title>
              <div class="flex align-items-center gap-2 text-primary">
                <i class="pi pi-info-circle"></i>
                <span class="text-lg">État du Stock</span>
              </div>
            </template>
            <template #content>
              <div class="stock-hero">
                <div class="stock-main-value" :class="getStockClass(consommable.quantite, consommable.is_stock_faible)">
                  {{ consommable.quantite }}
                </div>
                <div class="stock-label">Unités disponibles</div>
              </div>

              <div class="detail-list mt-4">
                <div class="detail-item">
                  <span class="label">Seuil d'alerte</span>
                  <span class="value">{{ consommable.seuil_alerte || 1 }} unités</span>
                </div>
                <div class="detail-item">
                  <span class="label">Statut</span>
                  <span class="value">
                    <Tag :value="getStockStatusLabel(consommable)" :severity="getStockStatusSeverity(consommable)" />
                  </span>
                </div>
              </div>
            </template>
          </Card>

          <Card class="equipement-card" v-if="consommable.equipement">
            <template #title>
              <div class="flex align-items-center gap-2 text-primary">
                <i class="pi pi-desktop"></i>
                <span class="text-lg">Équipement lié</span>
              </div>
            </template>
            <template #content>
              <div class="linked-eq-box" @click="$router.push(`/equipements/${consommable.equipement.id}`)">
                <div class="eq-info">
                  <strong>{{ consommable.equipement.reference }}</strong>
                  <p>{{ consommable.equipement.marque }} {{ consommable.equipement.modele }}</p>
                </div>
                <i class="pi pi-chevron-right"></i>
              </div>
            </template>
          </Card>
        </div>

        <!-- Colonne Droite: Historique -->
        <div class="col-12 lg:col-8">
          <Card class="tabs-card">
            <template #content>
              <Tabs value="historique">
                <TabList>
                  <Tab value="historique">Historique des mouvements</Tab>
                </TabList>

                <TabPanels>
                  <TabPanel value="historique">
                    <Timeline v-if="consommable.mouvements?.length" :value="consommable.mouvements" align="left" class="custom-timeline">
                      <template #opposite="slotProps">
                        <small class="text-secondary">
                          {{ formatDate(slotProps.item.date_mouvement, true) }}
                        </small>
                      </template>
                      <template #content="slotProps">
                        <div class="timeline-content">
                          <div class="timeline-type" :class="slotProps.item.type_mouvement">
                            {{ getMouvementLabel(slotProps.item.type_mouvement) }}
                          </div>
                          <div class="timeline-desc">
                            {{ slotProps.item.description }}
                          </div>
                          <div class="flex justify-content-between align-items-center">
                            <small class="timeline-user">Par: {{ slotProps.item.user?.name || 'Système' }}</small>
                            <Badge v-if="slotProps.item.nouvelle_valeur?.quantite" 
                              :value="`Stock: ${slotProps.item.nouvelle_valeur.quantite}`" 
                              severity="secondary" class="text-xs" />
                          </div>
                        </div>
                      </template>
                    </Timeline>
                    <div v-else class="empty-tab">
                      <i class="pi pi-history"></i>
                      <p>Aucun mouvement enregistré</p>
                    </div>
                  </TabPanel>
                </TabPanels>
              </Tabs>
            </template>
          </Card>
        </div>
      </div>

      <!-- Dialog Ajustement Stock -->
      <Dialog header="Ajuster le Stock" v-model:visible="showStockDialog" :style="{ width: '400px' }" :modal="true">
        <div class="p-fluid">
          <div class="field mb-4">
            <label class="font-bold mb-2 block">Action</label>
            <div class="flex gap-4">
              <div class="flex align-items-center">
                <RadioButton v-model="stockForm.action" inputId="add" name="action" value="ajouter" />
                <label for="add" class="ml-2">Ajouter</label>
              </div>
              <div class="flex align-items-center">
                <RadioButton v-model="stockForm.action" inputId="remove" name="action" value="retirer" />
                <label for="remove" class="ml-2">Retirer</label>
              </div>
            </div>
          </div>
          
          <div class="field mb-4">
            <label for="qty" class="font-bold mb-2 block">Quantité</label>
            <InputNumber id="qty" v-model="stockForm.quantite" :min="1" showButtons />
          </div>

          <div class="field mb-4">
            <label for="desc" class="font-bold mb-2 block">Description (optionnel)</label>
            <Textarea id="desc" v-model="stockForm.description" rows="3" placeholder="Raison de l'ajustement..." />
          </div>
        </div>
        <template #footer>
          <Button label="Annuler" class="p-button-text" @click="showStockDialog = false" />
          <Button label="Confirmer" class="p-button-primary" @click="confirmAjustement" :loading="consommableStore.loading" />
        </template>
      </Dialog>
    </div>

    <!-- Chargement -->
    <div v-else-if="loading" class="loading-state">
      <i class="pi pi-spin pi-spinner" style="font-size: 2rem"></i>
      <p>Chargement du consommable...</p>
    </div>
  </DirectionLayout>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useToast } from 'primevue/usetoast'
import DirectionLayout from '@/layouts/DirectionLayout.vue'
import { useConsommableStore } from '@/stores/consommableStore'
import gsap from 'gsap'

// PrimeVue
import Card from 'primevue/card'
import Button from 'primevue/button'
import Tag from 'primevue/tag'
import Badge from 'primevue/badge'
import Tabs from 'primevue/tabs'
import TabList from 'primevue/tablist'
import Tab from 'primevue/tab'
import TabPanels from 'primevue/tabpanels'
import TabPanel from 'primevue/tabpanel'
import Timeline from 'primevue/timeline'
import Dialog from 'primevue/dialog'
import RadioButton from 'primevue/radiobutton'
import InputNumber from 'primevue/inputnumber'
import Textarea from 'primevue/textarea'

const route = useRoute()
const router = useRouter()
const toast = useToast()
const consommableStore = useConsommableStore()

const consommable = ref(null)
const loading = ref(true)
const showStockDialog = ref(false)
const stockForm = ref({
  action: 'ajouter',
  quantite: 1,
  description: ''
})

const loadConsommable = async () => {
  loading.value = true
  try {
    const data = await consommableStore.fetchConsommable(route.params.id)
    consommable.value = data
  } catch (error) {
    console.error('Erreur:', error)
    toast.add({ severity: 'error', summary: 'Erreur', detail: 'Impossible de charger le consommable' })
  } finally {
    loading.value = false
  }
}

onMounted(async () => {
  await loadConsommable()
  gsap.from('.animate-in', { opacity: 0, y: 20, duration: 0.6, stagger: 0.2, ease: 'power3.out' })
})

watch(() => route.params.id, (newId) => {
  if (newId) loadConsommable()
})

const confirmAjustement = async () => {
  try {
    await consommableStore.ajusterStock(consommable.value.id, stockForm.value)
    toast.add({ severity: 'success', summary: 'Succès', detail: 'Stock mis à jour' })
    showStockDialog.value = false
    loadConsommable() // Rafraîchir les données
  } catch (error) {
    toast.add({ severity: 'error', summary: 'Erreur', detail: error.message })
  }
}

const editConsommable = () => {
  // Navigation vers la liste avec paramètre d'édition si implémenté ainsi
  // Ou ouvrir un dialog global si le store le permet
  router.push({ name: 'Consommables', query: { edit: consommable.value.id } })
}

// Helpers
const formatDate = (date, includeTime = false) => {
  if (!date) return 'N/A'
  const options = { 
    day: '2-digit', month: '2-digit', year: 'numeric',
    ...(includeTime ? { hour: '2-digit', minute: '2-digit' } : {})
  }
  return new Date(date).toLocaleDateString('fr-FR', options)
}

const getTypeLabel = (type) => {
  const types = {
    batterie: 'Batterie',
    chargeur: 'Chargeur',
    cable: 'Câble',
    protection: 'Protection',
    accessoire: 'Accessoire',
    consommable: 'Consommable'
  }
  return types[type] || type
}

const getStockStatusLabel = (item) => {
  if (item.quantite === 0) return 'Rupture'
  if (item.is_stock_faible) return 'Faible'
  return 'Normal'
}

const getStockStatusSeverity = (item) => {
  if (item.quantite === 0) return 'danger'
  if (item.is_stock_faible) return 'warning'
  return 'success'
}

const getStockClass = (qty, isLow) => {
  if (qty === 0) return 'text-danger'
  if (isLow) return 'text-warning'
  return 'text-success'
}

const getMouvementLabel = (type) => {
  const labels = {
    entree: 'Entrée en stock',
    sortie: 'Sortie de stock',
    creation: 'Création',
    modification: 'Modification'
  }
  return labels[type] || type
}
</script>

<style scoped lang="scss">
.consommable-detail-view { padding: 1rem; }

.page-header {
  display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;
  .title-container h1 { margin: 0 0 0.25rem 0; color: #1e293b; font-size: 1.4rem; font-weight: 800; }
  .subtitle { color: #64748b; margin: 0; font-size: 0.85rem; }
}

.info-card, .tabs-card, .equipement-card {
  border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.05); border: none; margin-bottom: 1rem;
}

.stock-hero {
  text-align: center; padding: 1.5rem 0;
  .stock-main-value { font-size: 3.5rem; font-weight: 900; line-height: 1; }
  .stock-label { color: #64748b; font-size: 0.85rem; margin-top: 0.5rem; text-transform: uppercase; font-weight: 600; }
}

.detail-list .detail-item {
  display: flex; justify-content: space-between; padding: 0.6rem 0; border-bottom: 1px solid #f1f5f9;
  &:last-child { border-bottom: none; }
  .label { color: #64748b; font-size: 0.8rem; }
  .value { color: #1e293b; font-weight: 600; font-size: 0.85rem; }
}

.linked-eq-box {
  display: flex; justify-content: space-between; align-items: center;
  padding: 0.75rem; background: #f8fafc; border-radius: 10px; cursor: pointer;
  transition: all 0.2s; border: 1px solid #f1f5f9;
  &:hover { background: #eff6ff; border-color: #3b82f6; transform: translateX(3px); }
  .eq-info {
    strong { display: block; font-size: 0.9rem; color: #1e293b; }
    p { margin: 0; font-size: 0.75rem; color: #64748b; }
  }
  i { color: #94a3b8; font-size: 0.8rem; }
}

.timeline-content {
  background: #f8fafc; padding: 0.75rem; border-radius: 10px; margin-bottom: 0.75rem; border: 1px solid #f1f5f9;
  .timeline-type { 
    font-weight: 700; font-size: 0.75rem; margin-bottom: 0.15rem; text-transform: uppercase;
    &.entree { color: #10b981; }
    &.sortie { color: #ef4444; }
  }
  .timeline-desc { color: #334155; font-size: 0.85rem; margin-bottom: 0.25rem; }
  .timeline-user { color: #94a3b8; font-style: italic; font-size: 0.75rem; }
}

.empty-tab {
  text-align: center; padding: 3rem 1rem; color: #cbd5e1;
  i { font-size: 3rem; margin-bottom: 1rem; }
  p { color: #94a3b8; }
}

.loading-state {
  display: flex; flex-direction: column; align-items: center; justify-content: center; height: 60vh; color: #94a3b8;
  p { margin-top: 1rem; font-weight: 500; }
}

:deep(.p-tablist-content) { background: transparent !important; border-bottom: 1px solid #f1f5f9 !important; }
:deep(.p-tab) { background: transparent !important; color: #64748b !important; font-weight: 600 !important; font-size: 0.85rem !important; }
:deep(.p-tab-active) { color: #3b82f6 !important; border-color: #3b82f6 !important; }

.text-danger { color: #ef4444 !important; }
.text-warning { color: #f59e0b !important; }
.text-success { color: #10b981 !important; }
</style>
