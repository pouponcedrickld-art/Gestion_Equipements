<template>
  <DirectionLayout>
    <div class="consommables-page" ref="pageContainer">
      <!-- En-tête Moderne -->
      <div class="page-header animate-in">
        <div class="header-left">
          <div class="title-with-icon">
            <div class="icon-wrapper">
              <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="svg-icon">
                <path d="M20 7L12 3L4 7M20 7L12 11M20 7V17L12 21M12 11L4 7M12 11V21M4 7V17L12 21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </div>
            <div>
              <h1>Gestion des Consommables</h1>
              <p class="subtitle">Suivi des stocks de composants et accessoires</p>
            </div>
          </div>
        </div>
        <div class="header-actions">
          <Button 
            label="Alertes Stock" 
            icon="pi pi-bell" 
            class="p-button-outlined p-button-warning action-btn mr-2"
            :badge="consommableStore.consommablesStockFaible.length.toString()"
            @click="showStockAlertes"
          />
          <Button 
            label="Nouveau Consommable" 
            icon="pi pi-plus" 
            class="p-button-success p-button-raised action-btn"
            @click="openNew"
          />
        </div>
      </div>

      <!-- Statistiques Glassmorphism -->
      <div class="stats-container animate-in">
        <div class="stat-glass-card info">
          <div class="stat-icon-box"><i class="pi pi-box"></i></div>
          <div class="stat-details">
            <span class="value">{{ statistiques?.total_consommables || 0 }}</span>
            <span class="label">Références</span>
          </div>
        </div>
        <div class="stat-glass-card success">
          <div class="stat-icon-box"><i class="pi pi-shopping-cart"></i></div>
          <div class="stat-details">
            <span class="value">{{ statistiques?.stock_total || 0 }}</span>
            <span class="label">Unités en Stock</span>
          </div>
        </div>
        <div class="stat-glass-card danger">
          <div class="stat-icon-box"><i class="pi pi-exclamation-triangle"></i></div>
          <div class="stat-details">
            <span class="value">{{ statistiques?.stock_faible || 0 }}</span>
            <span class="label">Ruptures Imminentes</span>
          </div>
        </div>
      </div>

      <!-- Liste des Consommables (Cards) -->
      <div class="consommables-grid" v-if="!consommableStore.loading">
        <div v-for="(item, index) in consommableStore.consommables" :key="item.id" class="cons-card animate-card" :style="`--index: ${index}`">
          <div class="card-body">
            <div class="card-header-top">
              <span class="type-tag">{{ getTypeLabel(item.type) }}</span>
              <div class="card-actions">
                <Button icon="pi pi-eye" class="p-button-text p-button-rounded p-button-sm p-button-info" @click="viewConsommable(item)" />
                <Button icon="pi pi-pencil" class="p-button-text p-button-rounded p-button-sm" @click="editConsommable(item)" />
                <Button icon="pi pi-trash" class="p-button-text p-button-rounded p-button-sm p-button-danger" @click="confirmDelete(item)" />
              </div>
            </div>

            <h3 class="cons-name">{{ item.nom }}</h3>
            <p class="cons-desc">{{ item.equipement ? item.equipement.reference + ' - ' + item.equipement.marque + ' ' + item.equipement.modele : 'Non assigné' }}</p>

            <div class="stock-display">
              <div class="stock-info">
                <span class="label">Quantité disponible</span>
                <span class="value" :class="getStockClass(item.quantite, item.is_stock_faible)">
                  {{ item.quantite }}
                </span>
              </div>
              <div class="stock-progress">
                <div class="progress-bar" :style="{ width: `${Math.min((item.quantite / (item.seuil_alerte * 3 || 20)) * 100, 100)}%`, background: item.is_stock_faible ? '#ef4444' : '#10b981' }"></div>
              </div>
            </div>

            <div class="card-footer">
              <div class="seuil-info">
                <Tag :value="getStockStatusLabel(item)" :severity="getStockStatusSeverity(item)" />
                <span v-if="item.seuil_alerte" class="seuil-label ml-2">(Seuil: {{ item.seuil_alerte }})</span>
              </div>
              <Button label="Ajuster Stock" icon="pi pi-plus-minus" class="p-button-outlined p-button-sm" @click="ajusterStockDialog(item)" />
            </div>
          </div>
        </div>
      </div>

      <!-- Skeleton loading -->
      <div class="consommables-grid" v-else>
        <div v-for="n in 6" :key="n" class="cons-card skeleton">
          <div class="skeleton-body"></div>
        </div>
      </div>

      <!-- Dialog de création/modification -->
      <Dialog
        :header="editingConsommable ? 'Modifier le Consommable' : 'Nouveau Consommable'"
        v-model:visible="showCreateDialog"
        :style="{ width: '600px' }"
        :modal="true"
        class="consommable-dialog"
      >
        <form @submit.prevent="saveConsommable" class="consommable-form p-fluid">
          <div class="grid">
            <div class="field col-12">
              <label for="nom">Nom *</label>
              <InputText
                id="nom"
                v-model="consommableForm.nom"
                :class="{ 'p-invalid': formErrors.nom }"
                placeholder="Ex: Batterie Li-ion, Chargeur USB-C..."
                autofocus
              />
              <small class="p-error" v-if="formErrors.nom">{{ formErrors.nom }}</small>
            </div>

            <div class="field col-12 md:col-6">
              <label for="type">Type *</label>
              <Dropdown
                id="type"
                v-model="consommableForm.type"
                :options="typeOptions"
                optionLabel="label"
                optionValue="value"
                placeholder="Sélectionner un type"
                :class="{ 'p-invalid': formErrors.type }"
              />
              <small class="p-error" v-if="formErrors.type">{{ formErrors.type }}</small>
            </div>

            <div class="field col-12 md:col-6">
              <label for="equipement_id">Équipement lié *</label>
              <Dropdown
                id="equipement_id"
                v-model="consommableForm.equipement_id"
                :options="equipementOptions"
                optionLabel="label"
                optionValue="value"
                placeholder="Choisir un équipement"
                filter
                :class="{ 'p-invalid': formErrors.equipement_id }"
              />
              <small class="p-error" v-if="formErrors.equipement_id">{{ formErrors.equipement_id }}</small>
            </div>

            <div class="field col-12 md:col-6">
              <label for="quantite">Quantité initiale *</label>
              <InputNumber
                id="quantite"
                v-model="consommableForm.quantite"
                :min="0"
                :class="{ 'p-invalid': formErrors.quantite }"
              />
              <small class="p-error" v-if="formErrors.quantite">{{ formErrors.quantite }}</small>
            </div>

            <div class="field col-12 md:col-6">
              <label for="seuil_alerte">Seuil d'alerte (Stock faible)</label>
              <InputNumber
                id="seuil_alerte"
                v-model="consommableForm.seuil_alerte"
                :min="0"
                placeholder="Alerte si <= à..."
              />
            </div>
          </div>

          <div class="form-actions mt-4">
            <Button
              type="button"
              label="Annuler"
              class="p-button-text"
              @click="cancelEdit"
            />
            <Button
              type="submit"
              :label="editingConsommable ? 'Modifier' : 'Créer'"
              :loading="consommableStore.loading"
              :disabled="!canSubmit"
            />
          </div>
        </form>
      </Dialog>

      <!-- Dialog d'ajustement de stock -->
      <Dialog
        header="Ajustement de Stock"
        v-model:visible="showStockDialog"
        :style="{ width: '500px' }"
        :modal="true"
        class="stock-dialog"
      >
        <form @submit.prevent="ajusterStock" class="stock-form" v-if="selectedConsommable">
          <div class="current-stock mb-4 p-3 border-round bg-gray-50 flex justify-content-between">
            <span class="font-bold">Stock actuel:</span>
            <span class="text-xl font-bold text-primary">{{ selectedConsommable.quantite }} unités</span>
          </div>

          <div class="field mb-4">
            <label class="font-bold block mb-2">Action *</label>
            <div class="flex gap-4">
              <div class="flex align-items-center">
                <RadioButton id="ajouter" name="action" value="ajouter" v-model="stockForm.action" />
                <label for="ajouter" class="ml-2">Ajouter (Réception)</label>
              </div>
              <div class="flex align-items-center">
                <RadioButton id="retirer" name="action" value="retirer" v-model="stockForm.action" />
                <label for="retirer" class="ml-2">Retirer (Utilisation)</label>
              </div>
            </div>
          </div>

          <div class="field mb-4">
            <label for="quantite_ajust" class="font-bold block mb-2">Quantité *</label>
            <InputNumber
              id="quantite_ajust"
              v-model="stockForm.quantite"
              :min="1"
              :max="stockForm.action === 'retirer' ? selectedConsommable.quantite : null"
              placeholder="Nombre d'unités"
              class="w-full"
              :class="{ 'p-invalid': stockErrors.quantite }"
            />
            <small class="p-error" v-if="stockErrors.quantite">{{ stockErrors.quantite }}</small>
          </div>

          <div class="field mb-4">
            <label for="description_ajust" class="font-bold block mb-2">Description</label>
            <Textarea
              id="description_ajust"
              v-model="stockForm.description"
              rows="3"
              placeholder="Motif de l'ajustement (ex: Nouvelle commande, Remplacement clavier...)"
              class="w-full"
            />
          </div>

          <div class="flex justify-content-end gap-2 mt-4">
            <Button type="button" label="Annuler" class="p-button-text" @click="showStockDialog = false" />
            <Button type="submit" label="Valider" :loading="consommableStore.loading" :disabled="!stockForm.quantite" />
          </div>
        </form>
      </Dialog>
    </div>
  </DirectionLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useToast } from 'primevue/usetoast'
import DirectionLayout from '@/layouts/DirectionLayout.vue'
import { useConsommableStore } from '@/stores/consommableStore'
import { useEquipementStore } from '@/stores/equipementStore'
import gsap from 'gsap'

// Composants PrimeVue
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import Dropdown from 'primevue/dropdown'
import InputNumber from 'primevue/inputnumber'
import Dialog from 'primevue/dialog'
import Tag from 'primevue/tag'
import RadioButton from 'primevue/radiobutton'
import Textarea from 'primevue/textarea'

const toast = useToast()
const consommableStore = useConsommableStore()
const equipementStore = useEquipementStore()

const pageContainer = ref(null)
const showCreateDialog = ref(false)
const showStockDialog = ref(false)
const editingConsommable = ref(null)
const selectedConsommable = ref(null)

const statistiques = computed(() => consommableStore.statistiques)

const typeOptions = [
  { label: 'Batterie', value: 'batterie' },
  { label: 'Chargeur', value: 'chargeur' },
  { label: 'Câble', value: 'cable' },
  { label: 'Protection', value: 'protection' },
  { label: 'Accessoire', value: 'accessoire' },
  { label: 'Consommable', value: 'consommable' }
]

const equipementOptions = computed(() => 
  equipementStore.equipements.map(e => ({
    label: `${e.reference} - ${e.marque} ${e.modele}`,
    value: e.id
  }))
)

const consommableForm = ref({
  nom: '',
  type: 'consommable',
  equipement_id: null,
  quantite: 0,
  seuil_alerte: 1
})

const stockForm = ref({
  action: 'ajouter',
  quantite: 1,
  description: ''
})

const formErrors = ref({})
const stockErrors = ref({})

const canSubmit = computed(() => {
  return consommableForm.value.nom.trim().length >= 2 && 
         consommableForm.value.equipement_id && 
         !consommableStore.loading
})

const getTypeLabel = (type) => {
  const option = typeOptions.find(o => o.value === type)
  return option ? option.label : type
}

const getStockClass = (qty, isLow) => {
  if (qty === 0) return 'text-red-600 font-bold'
  if (isLow) return 'text-orange-500 font-bold'
  return 'text-green-600 font-bold'
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

const openNew = () => {
  editingConsommable.value = null
  consommableForm.value = { nom: '', type: 'consommable', equipement_id: null, quantite: 0, seuil_alerte: 1 }
  showCreateDialog.value = true
}

const editConsommable = (item) => {
  editingConsommable.value = item
  consommableForm.value = {
    nom: item.nom,
    type: item.type,
    equipement_id: item.equipement_id,
    quantite: item.quantite,
    seuil_alerte: item.seuil_alerte || 1
  }
  showCreateDialog.value = true
}

const ajusterStockDialog = (item) => {
  selectedConsommable.value = item
  stockForm.value = { action: 'ajouter', quantite: 1, description: '' }
  showStockDialog.value = true
}

const saveConsommable = async () => {
  try {
    if (editingConsommable.value) {
      await consommableStore.updateConsommable(editingConsommable.value.id, consommableForm.value)
      toast.add({ severity: 'success', summary: 'Succès', detail: 'Consommable mis à jour', life: 3000 })
    } else {
      await consommableStore.createConsommable(consommableForm.value)
      toast.add({ severity: 'success', summary: 'Succès', detail: 'Consommable créé', life: 3000 })
    }
    showCreateDialog.value = false
    await consommableStore.fetchStatistiques()
  } catch (err) {
    if (err.response?.data?.errors) formErrors.value = err.response.data.errors
  }
}

const ajusterStock = async () => {
  try {
    await consommableStore.ajusterStock(selectedConsommable.value.id, stockForm.value)
    toast.add({ severity: 'success', summary: 'Succès', detail: 'Stock ajusté avec succès', life: 3000 })
    showStockDialog.value = false
    await consommableStore.fetchStatistiques()
  } catch (err) {
    toast.add({ severity: 'error', summary: 'Erreur', detail: 'Impossible d\'ajuster le stock', life: 3000 })
  }
}

const confirmDelete = async (item) => {
  if (confirm(`Voulez-vous vraiment supprimer le consommable ${item.nom} ?`)) {
    await consommableStore.deleteConsommable(item.id)
    toast.add({ severity: 'success', summary: 'Succès', detail: 'Consommable supprimé', life: 3000 })
    await consommableStore.fetchStatistiques()
  }
}

const cancelEdit = () => {
  showCreateDialog.value = false
  editingConsommable.value = null
}

onMounted(() => {
  consommableStore.fetchConsommables()
  consommableStore.fetchStatistiques()
  equipementStore.fetchEquipements({ per_page: 100 })
  gsap.from('.animate-in', { opacity: 0, y: 20, duration: 0.6, stagger: 0.2 })
})
</script>

<style scoped lang="scss">
.consommables-page { padding: 2rem; max-width: 1200px; margin: 0 auto; }
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
.title-with-icon { display: flex; align-items: center; gap: 1rem; .icon-wrapper { width: 50px; height: 50px; background: #ecfdf5; color: #10b981; border-radius: 12px; display: flex; align-items: center; justify-content: center; } h1 { margin: 0; font-size: 1.8rem; } .subtitle { color: #64748b; margin: 0; } }
.stats-container { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem; margin-bottom: 2.5rem; }
.stat-glass-card { background: rgba(255, 255, 255, 0.7); backdrop-filter: blur(10px); border-radius: 20px; padding: 1.5rem; display: flex; align-items: center; gap: 1.5rem; box-shadow: 0 8px 32px rgba(0,0,0,0.05); border: 1px solid rgba(255,255,255,0.3); .stat-icon-box { width: 50px; height: 50px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; } .value { display: block; font-size: 1.8rem; font-weight: 800; } .label { font-size: 0.9rem; color: #64748b; } &.info .stat-icon-box { background: #eff6ff; color: #3b82f6; } &.success .stat-icon-box { background: #ecfdf5; color: #10b981; } &.danger .stat-icon-box { background: #fef2f2; color: #ef4444; } }
.consommables-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 1.5rem; }
.cons-card { background: white; border-radius: 20px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); overflow: hidden; transition: transform 0.3s; &:hover { transform: translateY(-5px); } .card-body { padding: 1.5rem; } }
.card-header-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; .type-tag { background: #f1f5f9; padding: 0.3rem 0.8rem; border-radius: 30px; font-size: 0.75rem; font-weight: 700; color: #475569; text-transform: uppercase; } }
.cons-name { margin: 0 0 0.5rem 0; font-size: 1.25rem; font-weight: 700; }
.cons-desc { color: #64748b; font-size: 0.85rem; margin-bottom: 1.5rem; min-height: 2.5rem; }
.stock-display { margin-bottom: 1.5rem; .stock-info { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 0.5rem; .label { font-size: 0.8rem; color: #94a3b8; } .value { font-size: 1.5rem; } } .stock-progress { height: 6px; background: #f1f5f9; border-radius: 3px; overflow: hidden; .progress-bar { height: 100%; transition: width 0.5s ease; } } }
.card-footer { display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #f1f5f9; padding-top: 1rem; .seuil-label { font-size: 0.75rem; color: #94a3b8; } }
</style>
