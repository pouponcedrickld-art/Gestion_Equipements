<template>
<<<<<<< HEAD:frontend/src/views/consommables/ConsommablesView.vue
  <MainLayout>
    <div class="consommables-page">
      <!-- En-tête avec titre et actions -->
      <div class="page-header">
        <div class="page-title">
          <h1>
            <i class="pi pi-box"></i>
            Gestion des Consommables
          </h1>
          <p class="page-subtitle">
            Gérez les consommables et accessoires de vos équipements
          </p>
        </div>
        
        <div class="page-actions">
          <Button 
            label="Nouveau Consommable" 
            icon="pi pi-plus"
            @click="showCreateDialog = true"
            class="p-button-success"
          />
          <Button 
            label="Alertes Stock" 
            icon="pi pi-exclamation-triangle"
            @click="showStockAlertes"
            class="p-button-warning"
            :badge="consommableStore.consommablesStockFaible.length > 0 ? consommableStore.consommablesStockFaible.length.toString() : null"
          />
        </div>
      </div>

      <!-- Statistiques rapides -->
      <div class="stats-cards" v-if="statistiques">
        <div class="stat-card">
          <div class="stat-icon">
            <i class="pi pi-box"></i>
          </div>
          <div class="stat-content">
            <div class="stat-value">{{ statistiques.total_consommables }}</div>
            <div class="stat-label">Total Consommables</div>
          </div>
        </div>
        
        <div class="stat-card">
          <div class="stat-icon">
            <i class="pi pi-shopping-cart"></i>
          </div>
          <div class="stat-content">
            <div class="stat-value">{{ statistiques.stock_total }}</div>
            <div class="stat-label">Stock Total</div>
          </div>
        </div>
        
        <div class="stat-card warning" v-if="statistiques.stock_faible > 0">
          <div class="stat-icon">
            <i class="pi pi-exclamation-triangle"></i>
          </div>
          <div class="stat-content">
            <div class="stat-value">{{ statistiques.stock_faible }}</div>
            <div class="stat-label">Stock Faible</div>
          </div>
        </div>
        
        <div class="stat-card danger" v-if="statistiques.rupture_stock > 0">
          <div class="stat-icon">
            <i class="pi pi-times-circle"></i>
          </div>
          <div class="stat-content">
            <div class="stat-value">{{ statistiques.rupture_stock }}</div>
            <div class="stat-label">Rupture Stock</div>
          </div>
        </div>
      </div>
      <!-- Barre de recherche et filtres -->
      <div class="filters-section">
        <div class="p-inputgroup search-input">
          <InputText
            v-model="searchTerm"
            placeholder="Rechercher un consommable..."
            @input="handleSearch"
          />
          <Button icon="pi pi-search" class="p-button-outlined" />
        </div>
        
        <div class="filter-controls">
          <Dropdown
            v-model="selectedType"
            :options="typeOptions"
            placeholder="Tous types"
            optionLabel="label"
            optionValue="value"
            showClear
            @change="handleTypeFilter"
          />
          
          <Dropdown
            v-model="selectedEquipement"
            :options="equipementOptions"
            placeholder="Tous équipements"
            optionLabel="label"
            optionValue="value"
            showClear
            filter
            @change="handleEquipementFilter"
          />
          
          <div class="filter-buttons">
            <Button
              :label="`Tous (${consommableStore.pagination.total})`"
              :class="{ 'p-button-outlined': activeFilter !== 'tous' }"
              @click="setFilter('tous')"
            />
            <Button
              label="Stock Faible"
              :class="{ 'p-button-outlined': activeFilter !== 'stock_faible' }"
              @click="setFilter('stock_faible')"
              severity="warning"
            />
            <Button
              label="Rupture"
              :class="{ 'p-button-outlined': activeFilter !== 'rupture' }"
              @click="setFilter('rupture')"
              severity="danger"
            />
          </div>
        </div>
      </div>

      <!-- Tableau des consommables -->
      <div class="consommables-table">
        <DataTable
          :value="consommableStore.consommables"
          :loading="consommableStore.loading"
          paginator
          :rows="consommableStore.pagination.per_page"
          :totalRecords="consommableStore.pagination.total"
          :lazy="true"
          @page="onPageChange"
          dataKey="id"
          class="p-datatable-striped"
          responsiveLayout="scroll"
          :emptyMessage="consommableStore.consommables.length === 0 ? 'Aucun consommable trouvé' : 'Chargement...'"
        >
          <!-- Colonne Nom -->
          <Column field="nom" header="Nom" sortable>
            <template #body="{ data }">
              <div class="consommable-name">
                <i :class="getTypeIcon(data.type)" class="type-icon"></i>
                <div>
                  <strong>{{ data.nom }}</strong>
                  <div class="type-badge">
                    <Badge :value="getTypeLabel(data.type)" class="p-badge-info" />
                  </div>
                </div>
              </div>
            </template>
          </Column>

          <!-- Colonne Équipement -->
          <Column header="Équipement" sortable sortField="equipement.reference">
            <template #body="{ data }">
              <div v-if="data.equipement" class="equipement-info">
                <div class="equipement-ref">{{ data.equipement.reference }}</div>
                <div class="equipement-details">
                  {{ data.equipement.marque }} {{ data.equipement.modele }}
                </div>
              </div>
              <span v-else class="no-equipement">Non assigné</span>
            </template>
          </Column>

          <!-- Colonne Stock -->
          <Column header="Stock" class="text-center">
            <template #body="{ data }">
              <div class="stock-info">
                <div class="stock-quantity">
                  <span 
                    :class="getStockClass(data.quantite, data.is_stock_faible)"
                    class="stock-value"
                  >
                    {{ data.quantite }}
                  </span>
                </div>
                <div class="stock-status">
                  <Tag 
                    :value="getStockStatusLabel(data)" 
                    :severity="getStockStatusSeverity(data)"
                  />
                </div>
              </div>
            </template>
          </Column>

          <!-- Colonne Actions -->
          <Column header="Actions" class="text-center" :exportable="false">
            <template #body="{ data }">
              <div class="action-buttons">
                <Button
                  icon="pi pi-eye"
                  class="p-button-rounded p-button-text p-button-info"
                  @click="viewConsommable(data)"
                  v-tooltip.top="'Voir détails'"
                />
                <Button
                  icon="pi pi-pencil"
                  class="p-button-rounded p-button-text p-button-warning"
                  @click="editConsommable(data)"
                  v-tooltip.top="'Modifier'"
                />
                <SplitButton
                  icon="pi pi-cog"
                  class="p-button-rounded p-button-text p-button-secondary"
                  :model="getStockActions(data)"
                  v-tooltip.top="'Gérer le stock'"
                />
                <Button
                  icon="pi pi-trash"
                  class="p-button-rounded p-button-text p-button-danger"
                  @click="confirmDelete(data)"
                  v-tooltip.top="'Supprimer'"
                />
              </div>
            </template>
          </Column>
        </DataTable>
      </div>
      <!-- Dialog de création/modification -->
      <Dialog
        :header="editingConsommable ? 'Modifier le Consommable' : 'Nouveau Consommable'"
        v-model:visible="showCreateDialog"
        :style="{ width: '600px' }"
        :modal="true"
        class="consommable-dialog"
      >
        <form @submit.prevent="saveConsommable" class="consommable-form">
          <div class="form-grid">
            <div class="p-field">
              <label for="nom" class="p-field-label">Nom *</label>
              <InputText
                id="nom"
                v-model="consommableForm.nom"
                :class="{ 'p-invalid': formErrors.nom }"
                placeholder="Ex: Batterie Li-ion, Chargeur USB-C..."
                autofocus
              />
              <small class="p-error" v-if="formErrors.nom">{{ formErrors.nom }}</small>
            </div>

            <div class="p-field">
              <label for="type" class="p-field-label">Type *</label>
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

            <div class="p-field">
              <label for="equipement_id" class="p-field-label">Équipement *</label>
              <Dropdown
                id="equipement_id"
                v-model="consommableForm.equipement_id"
                :options="equipementOptions"
                optionLabel="label"
                optionValue="value"
                placeholder="Sélectionner un équipement"
                filter
                :class="{ 'p-invalid': formErrors.equipement_id }"
              />
              <small class="p-error" v-if="formErrors.equipement_id">{{ formErrors.equipement_id }}</small>
            </div>

            <div class="p-field">
              <label for="quantite" class="p-field-label">Quantité initiale *</label>
              <InputNumber
                id="quantite"
                v-model="consommableForm.quantite"
                :min="0"
                :class="{ 'p-invalid': formErrors.quantite }"
                placeholder="0"
              />
              <small class="p-error" v-if="formErrors.quantite">{{ formErrors.quantite }}</small>
            </div>
          </div>

          <div class="form-actions">
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
          <div class="current-stock">
            <h4>Stock actuel: {{ selectedConsommable.quantite }} unités</h4>
          </div>

          <div class="p-field">
            <label class="p-field-label">Action *</label>
            <div class="p-formgrid p-grid">
              <div class="p-field-radiobutton p-col-6">
                <RadioButton 
                  id="ajouter" 
                  name="action" 
                  value="ajouter" 
                  v-model="stockForm.action" 
                />
                <label for="ajouter">Ajouter au stock</label>
              </div>
              <div class="p-field-radiobutton p-col-6">
                <RadioButton 
                  id="retirer" 
                  name="action" 
                  value="retirer" 
                  v-model="stockForm.action" 
                />
                <label for="retirer">Retirer du stock</label>
              </div>
            </div>
          </div>

          <div class="p-field">
            <label for="quantite_ajust" class="p-field-label">Quantité *</label>
            <InputNumber
              id="quantite_ajust"
              v-model="stockForm.quantite"
              :min="1"
              :max="stockForm.action === 'retirer' ? selectedConsommable.quantite : null"
              placeholder="Nombre d'unités"
              :class="{ 'p-invalid': stockErrors.quantite }"
            />
            <small class="p-error" v-if="stockErrors.quantite">{{ stockErrors.quantite }}</small>
          </div>

          <div class="p-field">
            <label for="description_ajust" class="p-field-label">Description</label>
            <Textarea
              id="description_ajust"
              v-model="stockForm.description"
              rows="3"
              placeholder="Motif de l'ajustement (optionnel)"
            />
          </div>

          <div class="stock-preview" v-if="stockForm.quantite > 0">
            <p><strong>Nouveau stock :</strong> 
              {{ calculerNouveauStock() }} unités
            </p>
          </div>

          <div class="form-actions">
            <Button
              type="button"
              label="Annuler"
              class="p-button-text"
              @click="showStockDialog = false"
            />
            <Button
              type="submit"
              :label="`${stockForm.action === 'ajouter' ? 'Ajouter' : 'Retirer'} Stock`"
              :loading="consommableStore.loading"
              :disabled="!stockForm.quantite || stockForm.quantite <= 0"
            />
          </div>
        </form>
      </Dialog>
      <!-- Dialog de confirmation de suppression -->
      <Dialog
        header="Confirmer la suppression"
        v-model:visible="showDeleteDialog"
        :style="{ width: '400px' }"
        :modal="true"
      >
        <div class="confirmation-content">
          <i class="pi pi-exclamation-triangle confirmation-icon"></i>
          <span>
            Êtes-vous sûr de vouloir supprimer le consommable 
            <strong>{{ consommableToDelete?.nom }}</strong> ?
          </span>
        </div>
        <template #footer>
          <Button
            label="Annuler"
            class="p-button-text"
            @click="showDeleteDialog = false"
          />
          <Button
            label="Supprimer"
            class="p-button-danger"
            @click="deleteConsommable"
            :loading="consommableStore.loading"
          />
        </template>
      </Dialog>

      <!-- Dialog de détails -->
      <Dialog
        header="Détails du Consommable"
        v-model:visible="showDetailsDialog"
        :style="{ width: '700px' }"
        :modal="true"
      >
        <div v-if="selectedConsommable" class="consommable-details">
          <div class="detail-header">
            <div class="detail-title">
              <i :class="getTypeIcon(selectedConsommable.type)" class="detail-icon"></i>
              <h3>{{ selectedConsommable.nom }}</h3>
            </div>
            <Badge :value="getTypeLabel(selectedConsommable.type)" class="p-badge-info" />
          </div>

          <div class="detail-grid">
            <div class="detail-section">
              <h4>Informations générales</h4>
              <div class="info-grid">
                <div class="info-item">
                  <label>Type :</label>
                  <span>{{ getTypeLabel(selectedConsommable.type) }}</span>
                </div>
                <div class="info-item">
                  <label>Stock actuel :</label>
                  <span :class="getStockClass(selectedConsommable.quantite, selectedConsommable.is_stock_faible)">
                    {{ selectedConsommable.quantite }} unités
                  </span>
                </div>
                <div class="info-item">
                  <label>Statut :</label>
                  <Tag 
                    :value="getStockStatusLabel(selectedConsommable)" 
                    :severity="getStockStatusSeverity(selectedConsommable)"
                  />
                </div>
              </div>
            </div>

            <div class="detail-section" v-if="selectedConsommable.equipement">
              <h4>Équipement associé</h4>
              <div class="equipement-detail">
                <div class="equipement-ref">{{ selectedConsommable.equipement.reference }}</div>
                <div class="equipement-info">
                  {{ selectedConsommable.equipement.marque }} {{ selectedConsommable.equipement.modele }}
                </div>
              </div>
            </div>
          </div>
        </div>
      </Dialog>

      <!-- Dialog d'alertes stock -->
      <Dialog
        header="Alertes Stock Faible"
        v-model:visible="showAlertsDialog"
        :style="{ width: '800px' }"
        :modal="true"
        class="alerts-dialog"
      >
        <div class="alerts-content" v-if="consommableStore.consommablesStockFaible.length > 0">
          <p class="alerts-info">
            <i class="pi pi-exclamation-triangle"></i>
            {{ consommableStore.consommablesStockFaible.length }} consommables nécessitent votre attention
          </p>
          
          <DataTable 
            :value="consommableStore.consommablesStockFaible" 
            class="alerts-table"
            responsiveLayout="scroll"
          >
            <Column field="nom" header="Consommable" />
            <Column header="Stock">
              <template #body="{ data }">
                <span class="stock-alert">{{ data.quantite }} unités</span>
              </template>
            </Column>
            <Column header="Statut">
              <template #body="{ data }">
                <Tag 
                  :value="data.quantite === 0 ? 'Rupture' : 'Stock faible'" 
                  :severity="data.quantite === 0 ? 'danger' : 'warning'"
                />
              </template>
            </Column>
            <Column header="Action">
              <template #body="{ data }">
                <Button 
                  label="Ajuster Stock" 
                  class="p-button-sm"
                  @click="ajusterStockFromAlert(data)"
                />
              </template>
            </Column>
          </DataTable>
        </div>
        <div v-else class="no-alerts">
          <i class="pi pi-check-circle"></i>
          <p>Aucune alerte de stock en cours</p>
        </div>
      </Dialog>
=======
  <DirectionLayout>
    <div class="page-placeholder">
      <h2>{{ pageTitle }}</h2>
      <p>Page en cours de développement...</p>
>>>>>>> 2c965af4f2361eccbef055db409105b763f2340d:frontend/src/views/direction/consommables/ConsommablesView.vue
    </div>
  </DirectionLayout>
</template>

<script setup>
<<<<<<< HEAD:frontend/src/views/consommables/ConsommablesView.vue
import { ref, computed, onMounted, watch } from 'vue'
import { useToast } from 'primevue/usetoast'
import { useConsommableStore } from '@/stores/consommableStore'
import { useEquipementStore } from '@/stores/equipementStore'
import MainLayout from '@/layouts/MainLayout.vue'
=======
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import DirectionLayout from '@/layouts/DirectionLayout.vue'
>>>>>>> 2c965af4f2361eccbef055db409105b763f2340d:frontend/src/views/direction/consommables/ConsommablesView.vue

// Services et stores
const toast = useToast()
const consommableStore = useConsommableStore()
const equipementStore = useEquipementStore()

// État réactif des composants
const showCreateDialog = ref(false)
const showDeleteDialog = ref(false)
const showDetailsDialog = ref(false)
const showStockDialog = ref(false)
const showAlertsDialog = ref(false)
const editingConsommable = ref(null)
const consommableToDelete = ref(null)
const selectedConsommable = ref(null)

// Filtres et recherche
const searchTerm = ref('')
const selectedType = ref(null)
const selectedEquipement = ref(null)
const activeFilter = ref('tous')

// Formulaires
const consommableForm = ref({
  nom: '',
  type: null,
  equipement_id: null,
  quantite: 0
})

const stockForm = ref({
  action: 'ajouter',
  quantite: null,
  description: ''
})

const formErrors = ref({})
const stockErrors = ref({})

// Options pour les dropdowns
const typeOptions = [
  { label: 'Batterie', value: 'batterie', icon: 'pi-battery-empty' },
  { label: 'Chargeur', value: 'chargeur', icon: 'pi-bolt' },
  { label: 'Câble', value: 'cable', icon: 'pi-link' },
  { label: 'Protection', value: 'protection', icon: 'pi-shield' },
  { label: 'Accessoire', value: 'accessoire', icon: 'pi-cog' },
  { label: 'Consommable', value: 'consommable', icon: 'pi-box' }
]

// Computed properties
const equipementOptions = computed(() => {
  return equipementStore.equipements.map(equipement => ({
    label: `${equipement.reference} - ${equipement.marque} ${equipement.modele}`,
    value: equipement.id
  }))
})

const canSubmit = computed(() => {
  return consommableForm.value.nom &&
         consommableForm.value.type &&
         consommableForm.value.equipement_id &&
         consommableForm.value.quantite >= 0
})

const statistiques = computed(() => consommableStore.statistiques)

// Méthodes utilitaires
const getTypeIcon = (type) => {
  const typeOption = typeOptions.find(opt => opt.value === type)
  return typeOption ? typeOption.icon : 'pi-box'
}

const getTypeLabel = (type) => {
  const typeOption = typeOptions.find(opt => opt.value === type)
  return typeOption ? typeOption.label : type
}

const getStockClass = (quantite, isStockFaible) => {
  if (quantite === 0) return 'stock-rupture'
  if (isStockFaible) return 'stock-faible'
  return 'stock-normal'
}

const getStockStatusLabel = (consommable) => {
  if (consommable.quantite === 0) return 'Rupture'
  if (consommable.is_stock_faible) return 'Stock faible'
  return 'Disponible'
}

const getStockStatusSeverity = (consommable) => {
  if (consommable.quantite === 0) return 'danger'
  if (consommable.is_stock_faible) return 'warning'
  return 'success'
}

const getStockActions = (consommable) => [
  {
    label: 'Ajouter Stock',
    icon: 'pi pi-plus',
    command: () => ajusterStockDialog(consommable, 'ajouter')
  },
  {
    label: 'Retirer Stock',
    icon: 'pi pi-minus',
    command: () => ajusterStockDialog(consommable, 'retirer'),
    disabled: consommable.quantite === 0
  },
  {
    label: 'Historique',
    icon: 'pi pi-history',
    command: () => voirHistoriqueStock(consommable)
  }
]

// Gestion des filtres et recherche
const handleSearch = () => {
  consommableStore.setFilters({ 
    search: searchTerm.value,
    type: selectedType.value,
    equipement_id: selectedEquipement.value
  })
}

const handleTypeFilter = () => {
  consommableStore.setFilters({
    search: searchTerm.value,
    type: selectedType.value,
    equipement_id: selectedEquipement.value
  })
}

const handleEquipementFilter = () => {
  consommableStore.setFilters({
    search: searchTerm.value,
    type: selectedType.value,
    equipement_id: selectedEquipement.value
  })
}

const setFilter = (filter) => {
  activeFilter.value = filter
  let filterParams = {
    search: searchTerm.value,
    type: selectedType.value,
    equipement_id: selectedEquipement.value
  }
  
  switch (filter) {
    case 'stock_faible':
      filterParams.stock_faible = true
      break
    case 'rupture':
      filterParams.rupture = true
      break
    default:
      // 'tous' - pas de filtre supplémentaire
      break
  }
  
  consommableStore.setFilters(filterParams)
}

// Gestion de la pagination
const onPageChange = (event) => {
  consommableStore.changePage(event.page + 1)
}

// Actions CRUD
const viewConsommable = (consommable) => {
  selectedConsommable.value = consommable
  showDetailsDialog.value = true
}

const editConsommable = (consommable) => {
  editingConsommable.value = consommable
  consommableForm.value = {
    nom: consommable.nom,
    type: consommable.type,
    equipement_id: consommable.equipement_id,
    quantite: consommable.quantite
  }
  showCreateDialog.value = true
}

const confirmDelete = (consommable) => {
  consommableToDelete.value = consommable
  showDeleteDialog.value = true
}

// Sauvegarde consommable
const saveConsommable = async () => {
  try {
    // Réinitialiser les erreurs
    formErrors.value = {}
    
    if (editingConsommable.value) {
      await consommableStore.updateConsommable(
        editingConsommable.value.id,
        consommableForm.value
      )
      toast.add({
        severity: 'success',
        summary: 'Succès',
        detail: 'Consommable modifié avec succès',
        life: 3000
      })
    } else {
      await consommableStore.createConsommable(consommableForm.value)
      toast.add({
        severity: 'success',
        summary: 'Succès',
        detail: 'Consommable créé avec succès',
        life: 3000
      })
    }
    
    cancelEdit()
  } catch (error) {
    if (error.response?.data?.errors) {
      formErrors.value = error.response.data.errors
    } else {
      toast.add({
        severity: 'error',
        summary: 'Erreur',
        detail: error.message || 'Une erreur est survenue',
        life: 5000
      })
    }
  }
}

// Annuler modification
const cancelEdit = () => {
  showCreateDialog.value = false
  editingConsommable.value = null
  consommableForm.value = {
    nom: '',
    type: null,
    equipement_id: null,
    quantite: 0
  }
  formErrors.value = {}
}

// Suppression consommable
const deleteConsommable = async () => {
  try {
    await consommableStore.deleteConsommable(consommableToDelete.value.id)
    toast.add({
      severity: 'success',
      summary: 'Succès',
      detail: 'Consommable supprimé avec succès',
      life: 3000
    })
    showDeleteDialog.value = false
    consommableToDelete.value = null
  } catch (error) {
    toast.add({
      severity: 'error',
      summary: 'Erreur',
      detail: error.message || 'Erreur lors de la suppression',
      life: 5000
    })
  }
}

// Gestion du stock
const ajusterStockDialog = (consommable, action = 'ajouter') => {
  selectedConsommable.value = consommable
  stockForm.value = {
    action: action,
    quantite: null,
    description: ''
  }
  stockErrors.value = {}
  showStockDialog.value = true
}

const ajusterStock = async () => {
  try {
    stockErrors.value = {}
    
    await consommableStore.ajusterStock(selectedConsommable.value.id, {
      action: stockForm.value.action,
      quantite: stockForm.value.quantite,
      description: stockForm.value.description
    })
    
    toast.add({
      severity: 'success',
      summary: 'Succès',
      detail: `Stock ${stockForm.value.action === 'ajouter' ? 'ajouté' : 'retiré'} avec succès`,
      life: 3000
    })
    
    showStockDialog.value = false
    selectedConsommable.value = null
  } catch (error) {
    if (error.response?.data?.errors) {
      stockErrors.value = error.response.data.errors
    } else {
      toast.add({
        severity: 'error',
        summary: 'Erreur',
        detail: error.message || 'Erreur lors de l\'ajustement du stock',
        life: 5000
      })
    }
  }
}

const calculerNouveauStock = () => {
  if (!selectedConsommable.value || !stockForm.value.quantite) return 0
  
  const stockActuel = selectedConsommable.value.quantite
  const quantiteAjust = stockForm.value.quantite
  
  return stockForm.value.action === 'ajouter' 
    ? stockActuel + quantiteAjust
    : stockActuel - quantiteAjust
}

// Alertes stock
const showStockAlertes = () => {
  showAlertsDialog.value = true
}

const ajusterStockFromAlert = (consommable) => {
  showAlertsDialog.value = false
  ajusterStockDialog(consommable, 'ajouter')
}

// Historique du stock
const voirHistoriqueStock = (consommable) => {
  // TODO: Implémenter l'affichage de l'historique des mouvements de stock
  toast.add({
    severity: 'info',
    summary: 'Information',
    detail: 'Fonctionnalité à venir : Historique des mouvements de stock',
    life: 3000
  })
}

// Cycle de vie du composant
onMounted(async () => {
  // Charger les données initiales
  await Promise.all([
    consommableStore.fetchConsommables(),
    equipementStore.fetchEquipements(), // Pour les options du dropdown
    consommableStore.fetchStatistiques()
  ])
})

// Watchers pour la recherche en temps réel
watch(searchTerm, () => {
  // Débounce la recherche
  const timeoutId = setTimeout(() => {
    handleSearch()
  }, 300)
  
  return () => clearTimeout(timeoutId)
})
</script>

<style scoped>
/* ===================== */
/* LAYOUT GÉNÉRAL        */
/* ===================== */
.consommables-page {
  padding: 1.5rem;
  max-width: 1400px;
  margin: 0 auto;
}

/* ===================== */
/* EN-TÊTE DE PAGE       */
/* ===================== */
.page-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 2rem;
  gap: 2rem;
}

.page-title h1 {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  font-size: 2rem;
  font-weight: 600;
  color: var(--text-color);
  margin: 0 0 0.5rem 0;
}

.page-title h1 i {
  color: var(--primary-color);
}

.page-subtitle {
  color: var(--text-color-secondary);
  margin: 0;
  font-size: 1rem;
}

.page-actions {
  display: flex;
  gap: 1rem;
  flex-shrink: 0;
}

/* ===================== */
/* STATISTIQUES RAPIDES  */
/* ===================== */
.stats-cards {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.stat-card {
  background: var(--surface-card);
  border: 1px solid var(--surface-border);
  border-radius: var(--border-radius);
  padding: 1.5rem;
  display: flex;
  align-items: center;
  gap: 1rem;
  transition: all 0.3s ease;
}

.stat-card:hover {
  box-shadow: var(--card-shadow);
  transform: translateY(-2px);
}

.stat-card.warning {
  border-left: 4px solid var(--orange-500);
}

.stat-card.danger {
  border-left: 4px solid var(--red-500);
}

.stat-icon {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  background: var(--primary-color-text);
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--primary-color);
  font-size: 1.5rem;
}

.stat-card.warning .stat-icon {
  background: var(--orange-50);
  color: var(--orange-500);
}

.stat-card.danger .stat-icon {
  background: var(--red-50);
  color: var(--red-500);
}

.stat-value {
  font-size: 1.75rem;
  font-weight: 700;
  color: var(--text-color);
  line-height: 1;
}

.stat-label {
  color: var(--text-color-secondary);
  font-size: 0.875rem;
  margin-top: 0.25rem;
}

/* ===================== */
/* SECTION FILTRES       */
/* ===================== */
.filters-section {
  background: var(--surface-card);
  border: 1px solid var(--surface-border);
  border-radius: var(--border-radius);
  padding: 1.5rem;
  margin-bottom: 2rem;
}

.search-input {
  margin-bottom: 1rem;
  max-width: 400px;
}

.filter-controls {
  display: flex;
  flex-wrap: wrap;
  gap: 1rem;
  align-items: center;
}

.filter-buttons {
  display: flex;
  gap: 0.5rem;
  margin-left: auto;
}

/* ===================== */
/* TABLEAU CONSOMMABLES  */
/* ===================== */
.consommables-table {
  background: var(--surface-card);
  border-radius: var(--border-radius);
  overflow: hidden;
  box-shadow: var(--card-shadow);
}

.consommable-name {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.type-icon {
  color: var(--primary-color);
  font-size: 1.25rem;
}

.type-badge {
  margin-top: 0.25rem;
}

.equipement-info {
  display: flex;
  flex-direction: column;
}

.equipement-ref {
  font-weight: 600;
  color: var(--text-color);
}

.equipement-details {
  color: var(--text-color-secondary);
  font-size: 0.875rem;
}

.no-equipement {
  color: var(--text-color-secondary);
  font-style: italic;
}

.stock-info {
  text-align: center;
}

.stock-value {
  font-weight: 600;
  font-size: 1.125rem;
}

.stock-normal {
  color: var(--green-600);
}

.stock-faible {
  color: var(--orange-600);
}

.stock-rupture {
  color: var(--red-600);
}

.stock-status {
  margin-top: 0.5rem;
}

.action-buttons {
  display: flex;
  gap: 0.5rem;
  justify-content: center;
}

/* ===================== */
/* DIALOGS FORMULAIRES   */
/* ===================== */
.consommable-dialog .consommable-form {
  padding: 1rem 0;
}

.form-grid {
  display: grid;
  gap: 1.5rem;
}

.p-field-label {
  font-weight: 600;
  margin-bottom: 0.5rem;
  display: block;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  margin-top: 2rem;
  padding-top: 1rem;
  border-top: 1px solid var(--surface-border);
}

/* ===================== */
/* DIALOG STOCK          */
/* ===================== */
.stock-dialog .stock-form {
  padding: 1rem 0;
}

.current-stock {
  background: var(--surface-ground);
  padding: 1rem;
  border-radius: var(--border-radius);
  margin-bottom: 1.5rem;
  text-align: center;
}

.current-stock h4 {
  margin: 0;
  color: var(--text-color);
}

.stock-preview {
  background: var(--primary-50);
  color: var(--primary-700);
  padding: 1rem;
  border-radius: var(--border-radius);
  margin-top: 1rem;
  text-align: center;
}

/* ===================== */
/* DIALOG DÉTAILS        */
/* ===================== */
.consommable-details {
  padding: 1rem 0;
}

.detail-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
  padding-bottom: 1rem;
  border-bottom: 1px solid var(--surface-border);
}

.detail-title {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.detail-title h3 {
  margin: 0;
  color: var(--text-color);
}

.detail-icon {
  font-size: 2rem;
  color: var(--primary-color);
}

.detail-grid {
  display: grid;
  gap: 2rem;
}

.detail-section h4 {
  color: var(--text-color);
  margin-bottom: 1rem;
  font-size: 1.125rem;
  font-weight: 600;
}

.info-grid {
  display: grid;
  gap: 1rem;
}

.info-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.75rem;
  background: var(--surface-ground);
  border-radius: var(--border-radius);
}

.info-item label {
  font-weight: 600;
  color: var(--text-color-secondary);
}

.info-item span {
  font-weight: 500;
  color: var(--text-color);
}

.equipement-detail {
  padding: 1rem;
  background: var(--surface-ground);
  border-radius: var(--border-radius);
}

.equipement-detail .equipement-ref {
  font-weight: 600;
  font-size: 1.125rem;
  color: var(--primary-color);
  margin-bottom: 0.5rem;
}

.equipement-detail .equipement-info {
  color: var(--text-color-secondary);
}

/* ===================== */
/* DIALOG ALERTES        */
/* ===================== */
.alerts-dialog .alerts-content {
  padding: 1rem 0;
}

.alerts-info {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  background: var(--orange-50);
  color: var(--orange-700);
  padding: 1rem;
  border-radius: var(--border-radius);
  margin-bottom: 1.5rem;
}

.alerts-info i {
  font-size: 1.25rem;
}

.alerts-table .stock-alert {
  font-weight: 600;
  color: var(--orange-600);
}

.no-alerts {
  text-align: center;
  padding: 2rem;
  color: var(--text-color-secondary);
}

.no-alerts i {
  font-size: 3rem;
  color: var(--green-500);
  margin-bottom: 1rem;
}

/* ===================== */
/* DIALOG CONFIRMATION   */
/* ===================== */
.confirmation-content {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.confirmation-icon {
  color: var(--orange-500);
  font-size: 2rem;
  flex-shrink: 0;
}

/* ===================== */
/* RESPONSIVE            */
/* ===================== */
@media (max-width: 768px) {
  .consommables-page {
    padding: 1rem;
  }
  
  .page-header {
    flex-direction: column;
    gap: 1rem;
  }
  
  .page-actions {
    width: 100%;
    justify-content: stretch;
  }
  
  .page-actions .p-button {
    flex: 1;
  }
  
  .stats-cards {
    grid-template-columns: 1fr;
  }
  
  .filter-controls {
    flex-direction: column;
    align-items: stretch;
  }
  
  .filter-buttons {
    margin-left: 0;
    flex-wrap: wrap;
  }
  
  .action-buttons {
    flex-wrap: wrap;
  }
  
  .consommable-dialog,
  .stock-dialog {
    width: 95vw !important;
    max-width: none !important;
  }
}

@media (max-width: 480px) {
  .page-title h1 {
    font-size: 1.5rem;
  }
  
  .stat-card {
    padding: 1rem;
  }
  
  .stat-icon {
    width: 40px;
    height: 40px;
    font-size: 1.25rem;
  }
  
  .stat-value {
    font-size: 1.5rem;
  }
}
</style>