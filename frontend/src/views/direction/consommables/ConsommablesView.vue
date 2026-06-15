<template>
  <DirectionLayout>
    <div class="consommables-page">
      <!-- En-tête Compact -->
      <div class="page-header animate-in">
        <div class="title-with-icon">
          <div class="icon-wrapper">
            <i class="pi pi-box" style="font-size: 1.2rem"></i>
          </div>
          <div>
            <h1>Consommables</h1>
            <p class="subtitle">Gestion des stocks et accessoires</p>
          </div>
        </div>
        <div class="header-actions">
          <Button 
            label="Alertes" 
            icon="pi pi-bell" 
            class="p-button-outlined p-button-warning action-btn mr-2"
            :badge="consommableStore.consommablesStockFaible.length.toString()"
            @click="showStockAlertes"
          />
          <Button 
            v-if="canManageConsommables"
            label="Nouveau" 
            icon="pi pi-plus" 
            class="p-button-success p-button-raised action-btn"
            @click="openNew"
          />
        </div>
      </div>

      <!-- Statistiques Compactes -->
      <div class="stats-container animate-in">
        <div class="stat-glass-card info">
          <div class="stat-icon-box"><i class="pi pi-list"></i></div>
          <div>
            <span class="value">{{ statistiques?.total_consommables || 0 }}</span>
            <span class="label">Total types</span>
          </div>
        </div>
        <div class="stat-glass-card success">
          <div class="stat-icon-box"><i class="pi pi-check-circle"></i></div>
          <div>
            <span class="value">{{ statistiques?.stock_total || 0 }}</span>
            <span class="label">Unités en stock</span>
          </div>
        </div>
        <div class="stat-glass-card danger">
          <div class="stat-icon-box"><i class="pi pi-exclamation-triangle"></i></div>
          <div>
            <span class="value">{{ statistiques?.stock_faible || 0 }}</span>
            <span class="label">Alertes stock</span>
          </div>
        </div>
      </div>

      <!-- Barre de Filtres Compacte -->
      <div class="filters-bar animate-in">
        <div class="search-box">
          <i class="pi pi-search"></i>
          <InputText v-model="consommableStore.filters.search" placeholder="Rechercher un consommable..." class="search-input" @input="handleSearch" />
        </div>
        <div class="dropdown-filters">
          <Dropdown
            v-model="consommableStore.filters.type"
            :options="typeOptions"
            optionLabel="label"
            optionValue="value"
            placeholder="Type"
            class="modern-dropdown"
            showClear
            @change="handleSearch"
          />
          <SelectButton v-model="viewMode" :options="viewOptions" optionLabel="icon" optionValue="value" class="view-toggle">
            <template #option="slotProps">
              <i :class="slotProps.option.icon"></i>
            </template>
          </SelectButton>
        </div>
      </div>

      <!-- Liste des Consommables -->
      <div class="consommables-container" v-if="!consommableStore.loading">
        <!-- Vue Table -->
        <DataTable 
          v-if="viewMode === 'table' && consommableStore.consommables.length > 0"
          :value="consommableStore.consommables" 
          responsiveLayout="scroll" 
          class="professional-table"
          :rows="10" 
          paginator
        >
          <Column field="nom" header="Nom" sortable>
            <template #body="{ data }">
              <div class="flex flex-column">
                <span class="font-bold text-gray-800">{{ data.nom }}</span>
                <small class="text-gray-500">{{ getTypeLabel(data.type) }}</small>
              </div>
            </template>
          </Column>
          <Column field="equipement.reference" header="Équipement lié" sortable>
            <template #body="{ data }">
              <div v-if="data.equipement" class="text-sm">
                <span class="font-medium">{{ data.equipement.reference }}</span>
                <br/>
                <small class="text-gray-500">{{ data.equipement.marque }} {{ data.equipement.modele }}</small>
              </div>
              <span v-else class="text-gray-400 text-xs italic">Non assigné</span>
            </template>
          </Column>
          <Column field="quantite" header="Stock" sortable class="text-center">
            <template #body="{ data }">
              <span :class="getStockClass(data.quantite, data.is_stock_faible)">
                {{ data.quantite }}
              </span>
            </template>
          </Column>
          <Column field="statut" header="Statut">
            <template #body="{ data }">
              <Tag :value="getStockStatusLabel(data)" :severity="getStockStatusSeverity(data)" class="text-xs" />
            </template>
          </Column>
          <Column header="Actions" class="text-right">
            <template #body="{ data }">
              <div class="flex justify-content-end gap-1">
                <Button icon="pi pi-eye" class="p-button-text p-button-rounded p-button-info p-button-sm" @click="viewConsommable(data)" />
                <Button v-if="canManageConsommables" icon="pi pi-pencil" class="p-button-text p-button-rounded p-button-sm" @click="editConsommable(data)" />
                <Button v-if="canManageConsommables" icon="pi pi-plus-minus" class="p-button-text p-button-rounded p-button-secondary p-button-sm" @click="ajusterStockDialog(data)" />
                <Button v-if="canManageConsommables" icon="pi pi-trash" class="p-button-text p-button-rounded p-button-danger p-button-sm" @click="confirmDelete(data)" />
              </div>
            </template>
          </Column>
        </DataTable>

        <!-- Vue Cartes (Grille) -->
        <div v-else-if="viewMode === 'grid' && consommableStore.consommables.length > 0" class="consommables-grid">
          <div v-for="item in consommableStore.consommables" :key="item.id" class="cons-card animate-card" @click="viewConsommable(item)">
            <div class="card-body">
              <div class="card-header-top">
                <span class="type-tag">{{ getTypeLabel(item.type) }}</span>
                <div class="actions-overlay" @click.stop v-if="canManageConsommables">
                  <Button icon="pi pi-pencil" class="p-button-text p-button-rounded p-button-sm" @click="editConsommable(item)" />
                  <Button icon="pi pi-trash" class="p-button-text p-button-rounded p-button-sm p-button-danger" @click="confirmDelete(item)" />
                </div>
              </div>

              <h3 class="cons-name">{{ item.nom }}</h3>
              <p class="cons-desc">
                <i class="pi pi-desktop mr-1"></i>
                {{ item.equipement ? item.equipement.reference : 'Non assigné' }}
              </p>

              <div class="stock-display">
                <div class="stock-info">
                  <span class="label">Disponible</span>
                  <span class="value" :class="getStockClass(item.quantite, item.is_stock_faible)">
                    {{ item.quantite }}
                  </span>
                </div>
                <div class="stock-progress">
                  <div class="progress-bar" :style="{ width: `${Math.min((item.quantite / (item.seuil_alerte * 3 || 20)) * 100, 100)}%`, background: item.quantite === 0 ? '#ef4444' : (item.is_stock_faible ? '#f59e0b' : '#10b981') }"></div>
                </div>
              </div>

              <div class="card-footer">
                <Tag :value="getStockStatusLabel(item)" :severity="getStockStatusSeverity(item)" />
                <Button v-if="canManageConsommables" label="Stock" icon="pi pi-plus-minus" class="p-button-text p-button-sm" @click.stop="ajusterStockDialog(item)" />
              </div>
            </div>
          </div>
        </div>

        <!-- État vide -->
        <div v-else class="empty-state">
          <i class="pi pi-box"></i>
          <p>Aucun consommable trouvé</p>
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
import { useRouter } from 'vue-router'
import { useToast } from 'primevue/usetoast'
import DirectionLayout from '@/layouts/DirectionLayout.vue'
import { useConsommableStore } from '@/stores/consommableStore'
import { useEquipementStore } from '@/stores/equipementStore'
import { useAuthStore } from '@/stores/authStore'
import gsap from 'gsap'

// Composants PrimeVue
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import Dropdown from 'primevue/dropdown'
import InputNumber from 'primevue/inputnumber'
import SelectButton from 'primevue/selectbutton'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Tag from 'primevue/tag'
import Badge from 'primevue/badge'
import Dialog from 'primevue/dialog'
import RadioButton from 'primevue/radiobutton'
import Textarea from 'primevue/textarea'

const toast = useToast()
const router = useRouter()
const consommableStore = useConsommableStore()
const equipementStore = useEquipementStore()
const authStore = useAuthStore()

const pageContainer = ref(null)
const showCreateDialog = ref(false)
const showStockDialog = ref(false)
const editingConsommable = ref(null)
const selectedConsommable = ref(null)
const viewMode = ref('grid')
const viewOptions = ref([
  { icon: 'pi pi-list', value: 'table' },
  { icon: 'pi pi-th-large', value: 'grid' }
])

const statistiques = computed(() => consommableStore.statistiques)
const canManageConsommables = computed(() => authStore.isSuperAdmin || authStore.isGestionnaireGeneral)

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
  if (qty === 0) return 'text-danger'
  if (isLow) return 'text-warning'
  return 'text-success'
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

const viewConsommable = (item) => {
  router.push(`/consommables/${item.id}`)
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
.consommables-page { padding: 1rem; }
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; }
.title-with-icon { 
  display: flex; align-items: center; gap: 0.75rem; 
  .icon-wrapper { 
    width: 36px; height: 36px; background: #ecfdf5; color: #10b981; 
    border-radius: 10px; display: flex; align-items: center; justify-content: center; 
  } 
  h1 { margin: 0; font-size: 1.4rem; font-weight: 800; color: #1e293b; } 
  .subtitle { color: #64748b; margin: 0; font-size: 0.8rem; } 
}

.stats-container { display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 1rem; margin-bottom: 1.5rem; }
.stat-glass-card { 
  background: white; border-radius: 14px; padding: 0.75rem 1rem; 
  display: flex; align-items: center; gap: 0.75rem; box-shadow: 0 2px 8px rgba(0,0,0,0.03); 
  .stat-icon-box { 
    width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 1rem; 
  } 
  .value { display: block; font-size: 1.2rem; font-weight: 800; color: #1e293b; } 
  .label { font-size: 0.7rem; color: #64748b; text-transform: uppercase; font-weight: 600; } 
  &.info .stat-icon-box { background: #eff6ff; color: #3b82f6; } 
  &.success .stat-icon-box { background: #ecfdf5; color: #10b981; } 
  &.danger .stat-icon-box { background: #fef2f2; color: #ef4444; } 
}

.filters-bar {
  display: flex; justify-content: space-between; gap: 0.75rem; margin-bottom: 1rem;
  .search-box { flex: 1; position: relative; i { position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%); color: #64748b; font-size: 0.85rem; } .search-input { width: 100%; padding-left: 2.25rem; border-radius: 10px; border: none; box-shadow: 0 2px 6px rgba(0,0,0,0.05); font-size: 0.85rem; height: 36px; } }
  .dropdown-filters { display: flex; gap: 0.75rem; align-items: center; .modern-dropdown { border-radius: 10px; border: none; box-shadow: 0 2px 6px rgba(0,0,0,0.05); font-size: 0.85rem; height: 36px; width: 140px; } }
  .view-toggle { :deep(.p-button) { padding: 0.4rem 0.6rem; } }
}

.consommables-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 1rem; }
.cons-card { 
  background: white; border-radius: 14px; box-shadow: 0 2px 10px rgba(0,0,0,0.04); 
  overflow: hidden; transition: transform 0.2s; cursor: pointer; border: 1px solid #f1f5f9;
  &:hover { transform: translateY(-3px); box-shadow: 0 4px 15px rgba(0,0,0,0.08); } 
  .card-body { padding: 0.75rem; } 
}

.card-header-top { 
  display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem; 
  .type-tag { background: #f8fafc; padding: 0.15rem 0.5rem; border-radius: 6px; font-size: 0.6rem; font-weight: 700; color: #64748b; text-transform: uppercase; border: 1px solid #e2e8f0; } 
  .actions-overlay { display: flex; gap: 0.2rem; .p-button { width: 24px; height: 24px; } }
}

.cons-name { margin: 0 0 0.15rem 0; font-size: 1rem; font-weight: 700; color: #1e293b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.cons-desc { color: #64748b; font-size: 0.75rem; margin-bottom: 0.75rem; display: flex; align-items: center; }

.stock-display { 
  margin-bottom: 0.75rem; 
  .stock-info { 
    display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 0.25rem; 
    .label { font-size: 0.7rem; color: #94a3b8; } 
    .value { font-size: 1.1rem; font-weight: 800; } 
  } 
  .stock-progress { 
    height: 4px; background: #f1f5f9; border-radius: 2px; overflow: hidden; 
    .progress-bar { height: 100%; transition: width 0.5s ease; } 
  } 
}

.card-footer { 
  display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #f1f5f9; padding-top: 0.5rem; 
  :deep(.p-tag) { font-size: 0.6rem; padding: 0.15rem 0.4rem; }
  .p-button { font-size: 0.7rem; padding: 0.2rem 0.4rem; }
}

.empty-state {
  display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 4rem 2rem;
  background: white; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); text-align: center;
  i { font-size: 3rem; color: #cbd5e1; margin-bottom: 1rem; }
  p { color: #64748b; font-size: 1rem; }
}

.modern-table {
  :deep(.p-datatable-thead > tr > th) { background: #f8fafc; color: #64748b; font-size: 0.75rem; text-transform: uppercase; padding: 0.75rem; }
  :deep(.p-datatable-tbody > tr > td) { padding: 0.75rem; font-size: 0.85rem; }
}

.text-danger { color: #ef4444 !important; font-weight: 700; }
.text-warning { color: #f59e0b !important; font-weight: 700; }
.text-success { color: #10b981 !important; font-weight: 700; }
</style>
