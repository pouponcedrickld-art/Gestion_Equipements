<template>
  <DirectionLayout>
    <div class="categories-page" ref="pageContainer">
      <!-- En-tête avec titre et actions -->
      <div class="page-header animate-in">
        <div class="page-title">
          <div class="title-with-icon">
            <div class="icon-wrapper">
              <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="svg-icon">
                <path
                  d="M7 7H7.01M7 3H17C18.1046 3 19 3.89543 19 5V19C19 20.1046 18.1046 21 17 21H7C5.89543 21 5 20.1046 5 19V5C5 3.89543 5.89543 3 7 3Z"
                  stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
              </svg>
            </div>
            <div>
              <h1>Gestion des Catégories</h1>
              <p class="page-subtitle">Organisez votre inventaire avec élégance</p>
            </div>
          </div>
        </div>

        <div class="page-actions" v-if="hasRole(['super_admin', 'gestionnaire_stock_general'])">
          <Button label="Nouvelle Catégorie" icon="pi pi-plus" @click="openCreateDialog"
            class="p-button-success p-button-raised action-btn" />
        </div>
      </div>

      <!-- Barre de recherche et filtres -->
      <div class="filters-section animate-in">
        <div class="search-container">
          <span class="p-input-icon-left search-input-wrapper">
            <i class="pi pi-search" />
            <InputText v-model="searchTerm" placeholder="Rechercher une catégorie..." @input="handleSearch"
              class="modern-input" />
          </span>
        </div>

        <div class="filter-pills">
          <button class="pill" :class="{ active: !showWithEquipementsOnly }" @click="toggleEquipementsFilter(false)">
            Tous ({{ categorieStore.pagination.total }})
          </button>
          <button class="pill" :class="{ active: showWithEquipementsOnly }" @click="toggleEquipementsFilter(true)">
            Avec équipements
          </button>
        </div>
      </div>

      <!-- Grille de cartes -->
      <div class="categories-grid" v-if="!categorieStore.loading">
        <div v-for="(categorie, index) in categorieStore.categories" :key="categorie.id"
          class="category-card animate-card" :style="{ '--index': index }">
          <div class="card-content">
            <div class="card-header">
              <div class="category-icon-bg">
                <div class="cat-code" v-if="categorie.code">{{ categorie.code }}</div>
                <svg v-else viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="card-svg">
                  <path
                    d="M3 7V17C3 18.1046 3.89543 19 5 19H19C20.1046 19 21 18.1046 21 17V7C21 5.89543 20.1046 5 19 5H5C3.89543 5 3 5.89543 3 7Z"
                    stroke="currentColor" stroke-width="2" />
                  <path d="M3 10H21" stroke="currentColor" stroke-width="2" />
                </svg>
              </div>
              <div class="card-actions" v-if="hasRole(['super_admin', 'gestionnaire_stock_general'])">
                <Button icon="pi pi-pencil" class="p-button-text p-button-rounded p-button-sm"
                  @click="editCategorie(categorie)" />
                <Button icon="pi pi-trash" class="p-button-text p-button-rounded p-button-sm p-button-danger"
                  @click="confirmDelete(categorie)" :disabled="categorie.nombre_equipements > 0" />
              </div>
            </div>

            <h3 class="category-name">{{ categorie.nom }}</h3>
            <p class="category-desc">{{ categorie.description || 'Aucune description fournie' }}</p>

            <div class="card-footer">
              <div class="equip-badge" @click="viewCategorie(categorie)">
                <span class="count">{{ categorie.nombre_equipements || 0 }}</span>
                <span class="label">Équipements</span>
              </div>
              <Button icon="pi pi-chevron-right" class="p-button-text p-button-rounded"
                @click="viewCategorie(categorie)" />
            </div>
          </div>
        </div>
      </div>

      <!-- Skeleton loading -->
      <div class="categories-grid" v-else>
        <div v-for="n in 6" :key="n" class="category-card skeleton">
          <div class="skeleton-content"></div>
        </div>
      </div>

      <!-- Pagination moderne -->
      <div class="modern-pagination" v-if="categorieStore.pagination.total > categorieStore.pagination.per_page">
        <Paginator :rows="categorieStore.pagination.per_page" :totalRecords="categorieStore.pagination.total"
          @page="onPageChange" template="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink" />
      </div>

      <!-- Dialog de création/modification -->
      <Dialog :header="editingCategorie ? 'Modifier la Catégorie' : 'Nouvelle Catégorie'"
        v-model:visible="showCreateDialog" :style="{ width: '550px' }" :modal="true" class="category-dialog"
        :closeOnEscape="true" :dismissableMask="true">
        <form @submit.prevent="saveCategorie" class="category-form p-fluid">
          <div class="form-content">
            <!-- Nom de la catégorie -->
            <div class="field mb-4">
              <label for="nom" class="font-bold block mb-2">Nom de la catégorie *</label>
              <div class="p-inputgroup">
                <span class="p-inputgroup-addon"><i class="pi pi-tag"></i></span>
                <InputText id="nom" v-model="categorieForm.nom" :class="{ 'p-invalid': formErrors.nom }"
                  placeholder="Ex: Informatique, Électronique, Mécanique..." autofocus />
              </div>
              <small class="p-error" v-if="formErrors.nom">{{ formErrors.nom }}</small>
            </div>

            <!-- Description -->
            <div class="field mb-4">
              <label for="description" class="font-bold block mb-2">Description / Usage</label>
              <Textarea id="description" v-model="categorieForm.description" rows="2"
                placeholder="À quoi sert cette catégorie ?" />
            </div>

            <!-- Attributs Spécifiques (Les "Genres") -->
            <div class="custom-attributes-section border-top-1 border-gray-200 pt-4 mt-2">
              <div class="flex justify-content-between align-items-center mb-3">
                <label class="font-bold text-lg">Détails importants (Genres)</label>
                <Button type="button" icon="pi pi-plus" label="Ajouter un genre" 
                  class="p-button-sm p-button-outlined p-button-info" @click="addAttribute" />
              </div>
              
              <div v-if="categorieForm.attributs_personnalises.length === 0" class="empty-attrs-msg py-4">
                <p class="text-gray-500 italic">Aucun détail spécifique défini pour cette catégorie.</p>
              </div>

              <div v-else class="attributes-list">
                <div v-for="(attr, index) in categorieForm.attributs_personnalises" :key="index" 
                  class="attr-row flex gap-3 align-items-center mb-2">
                  <div class="flex-1">
                    <InputText v-model="attr.nom" placeholder="Ex: RAM, Voltage, Moteur..." class="w-full" />
                  </div>
                  <div class="w-4">
                    <Dropdown v-model="attr.type" :options="attrTypes" optionLabel="label" optionValue="value" class="w-full" />
                  </div>
                  <Button type="button" icon="pi pi-times" class="p-button-danger p-button-text p-button-rounded" 
                    @click="removeAttribute(index)" />
                </div>
              </div>
              
              <div class="info-hint mt-3 p-2 bg-blue-50 border-round flex align-items-center gap-2">
                <i class="pi pi-info-circle text-blue-600"></i>
                <small class="text-blue-700 font-medium">
                  Ces champs seront demandés lors de l'enregistrement d'un équipement.
                </small>
              </div>
            </div>
          </div>

          <div class="form-actions mt-5 flex justify-content-end gap-2">
            <Button type="button" label="Annuler" class="p-button-text p-button-secondary" @click="cancelEdit" />
            <Button type="submit" :label="editingCategorie ? 'Enregistrer les modifications' : 'Créer la catégorie'" 
              icon="pi pi-check" :loading="categorieStore.loading" :disabled="!canSubmit" 
              class="p-button-primary" />
          </div>
        </form>
      </Dialog>

      <!-- Dialog de confirmation de suppression -->
      <Dialog header="Confirmer la suppression" v-model:visible="showDeleteDialog" :style="{ width: '400px' }"
        :modal="true">
        <div class="confirmation-content">
          <i class="pi pi-exclamation-triangle confirmation-icon"></i>
          <span>
            Êtes-vous sûr de vouloir supprimer la catégorie
            <strong>{{ categorieToDelete?.nom }}</strong> ?
          </span>
        </div>
        <template #footer>
          <Button label="Annuler" class="p-button-text" @click="showDeleteDialog = false" />
          <Button label="Supprimer" class="p-button-danger" @click="deleteCategorie"
            :loading="categorieStore.loading" />
        </template>
      </Dialog>

      <!-- Dialog de détails -->
      <Dialog header="Détails de la Catégorie" v-model:visible="showDetailsDialog" :style="{ width: '600px' }"
        :modal="true">
        <div v-if="selectedCategorie" class="category-details">
          <div class="detail-header">
            <div class="flex align-items-center gap-3">
              <div class="detail-cat-icon" v-if="selectedCategorie.code">{{ selectedCategorie.code }}</div>
              <div>
                <h3>{{ selectedCategorie.nom }}</h3>
                <span class="text-sm text-gray-500" v-if="selectedCategorie.parent">Sous-catégorie de : {{ selectedCategorie.parent.nom }}</span>
              </div>
            </div>
          </div>

          <div class="detail-grid mt-4">
            <div class="detail-item" v-if="selectedCategorie.frequence_maintenance">
              <label>Maintenance</label>
              <span>Tous les {{ selectedCategorie.frequence_maintenance }} mois</span>
            </div>
            <div class="detail-item" v-if="selectedCategorie.duree_vie">
              <label>Durée de vie</label>
              <span>{{ selectedCategorie.duree_vie }} ans</span>
            </div>
          </div>

          <div class="detail-section mt-4">
            <p v-if="selectedCategorie.description" class="category-description">
              {{ selectedCategorie.description }}
            </p>
          </div>

          <div class="detail-section" v-if="selectedCategorie.attributs_personnalises?.length > 0">
            <h4>Attributs définis</h4>
            <div class="flex flex-wrap gap-2">
              <Badge v-for="attr in selectedCategorie.attributs_personnalises" :key="attr.nom" :value="`${attr.nom} (${attr.type})`" severity="info" />
            </div>
          </div>

          <div class="detail-section" v-if="selectedCategorie.statistiques">
            <h4>Statistiques</h4>
            <div class="stats-grid">
              <div class="stat-item">
                <i class="pi pi-mobile stat-icon"></i>
                <div>
                  <div class="stat-value">{{ selectedCategorie.statistiques.total_equipements }}</div>
                  <div class="stat-label">Équipements total</div>
                </div>
              </div>
            </div>
          </div>

          <div class="flex justify-content-center mt-6">
            <Button 
              label="Ajouter un équipement dans cette catégorie" 
              icon="pi pi-plus-circle" 
              class="p-button-primary p-button-raised"
              @click="addEquipementToCategory(selectedCategorie)"
            />
          </div>
        </div>
      </Dialog>
    </div>
  </DirectionLayout>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useToast } from 'primevue/usetoast'
import { useConfirm } from 'primevue/useconfirm'
import DirectionLayout from '@/layouts/DirectionLayout.vue'
import gsap from 'gsap'

// Composants PrimeVue
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import Textarea from 'primevue/textarea'
import Dialog from 'primevue/dialog'
import Badge from 'primevue/badge'
import Paginator from 'primevue/paginator'
import InputNumber from 'primevue/inputnumber'
import Dropdown from 'primevue/dropdown'

// Stores
import { useCategorieStore } from '@/stores/categorieStore'
import { hasRole } from '@/utils/permissions'

const router = useRouter()
const toast = useToast()
const categorieStore = useCategorieStore()

const pageContainer = ref(null)
const searchTerm = ref('')
const showWithEquipementsOnly = ref(false)
const showCreateDialog = ref(false)
const showDeleteDialog = ref(false)
const showDetailsDialog = ref(false)
const editingCategorie = ref(null)
const selectedCategorie = ref(null)
const categorieToDelete = ref(null)

const attrTypes = [
  { label: 'Texte', value: 'texte' },
  { label: 'Nombre', value: 'nombre' },
  { label: 'Date', value: 'date' },
  { label: 'Liste', value: 'liste' }
]

const categorieForm = ref({
  nom: '',
  code: '',
  description: '',
  parent_id: null,
  frequence_maintenance: null,
  duree_vie: null,
  attributs_personnalises: []
})

const formErrors = ref({})

const canSubmit = computed(() => {
  return categorieForm.value.nom.trim().length >= 2 && !categorieStore.loading
})

const addAttribute = () => {
  categorieForm.value.attributs_personnalises.push({ nom: '', type: 'texte' })
}

const removeAttribute = (index) => {
  categorieForm.value.attributs_personnalises.splice(index, 1)
}

const handleSearch = () => {
  categorieStore.fetchCategories({ 
    search: searchTerm.value, 
    with_equipements_only: showWithEquipementsOnly.value 
  })
}

const toggleEquipementsFilter = (val) => {
  showWithEquipementsOnly.value = val
  handleSearch()
}

const onPageChange = (event) => {
  categorieStore.fetchCategories({ 
    page: event.page + 1,
    search: searchTerm.value,
    with_equipements_only: showWithEquipementsOnly.value
  })
}

const openCreateDialog = () => {
  editingCategorie.value = null
  categorieForm.value = {
    nom: '',
    code: '',
    description: '',
    parent_id: null,
    frequence_maintenance: null,
    duree_vie: null,
    attributs_personnalises: []
  }
  showCreateDialog.value = true
}

const editCategorie = (categorie) => {
  editingCategorie.value = categorie
  categorieForm.value = {
    nom: categorie.nom,
    code: categorie.code || '',
    description: categorie.description || '',
    parent_id: categorie.parent_id,
    frequence_maintenance: categorie.frequence_maintenance,
    duree_vie: categorie.duree_vie,
    attributs_personnalises: categorie.attributs_personnalises || []
  }
  showCreateDialog.value = true
}

const saveCategorie = async () => {
  try {
    if (editingCategorie.value) {
      await categorieStore.updateCategorie(editingCategorie.value.id, categorieForm.value)
      toast.add({ severity: 'success', summary: 'Succès', detail: 'Catégorie modifiée', life: 3000 })
    } else {
      await categorieStore.createCategorie(categorieForm.value)
      toast.add({ severity: 'success', summary: 'Succès', detail: 'Catégorie créée', life: 3000 })
    }
    showCreateDialog.value = false
  } catch (err) {
    if (err.response?.data?.errors) {
      formErrors.value = err.response.data.errors
    }
  }
}

const viewCategorie = async (categorie) => {
  selectedCategorie.value = await categorieStore.fetchCategorieById(categorie.id)
  showDetailsDialog.value = true
}

const addEquipementToCategory = (categorie) => {
  router.push({
    path: '/equipements/nouveau',
    query: { categorie_id: categorie.id }
  })
}

const confirmDelete = (categorie) => {
  categorieToDelete.value = categorie
  showDeleteDialog.value = true
}

const deleteCategorie = async () => {
  try {
    await categorieStore.deleteCategorie(categorieToDelete.value.id)
    showDeleteDialog.value = false
    toast.add({ severity: 'success', summary: 'Succès', detail: 'Catégorie supprimée', life: 3000 })
  } catch (err) {
    toast.add({ severity: 'error', summary: 'Erreur', detail: 'Impossible de supprimer la catégorie', life: 3000 })
  }
}

const cancelEdit = () => {
  showCreateDialog.value = false
  editingCategorie.value = null
}

onMounted(() => {
  categorieStore.fetchCategories()
  gsap.from('.animate-in', { opacity: 0, y: 20, duration: 0.6, stagger: 0.2 })
})
</script>

<style scoped lang="scss">
.categories-page { padding: 2rem; max-width: 1200px; margin: 0 auto; }
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
.title-with-icon { display: flex; align-items: center; gap: 1rem; i { font-size: 2rem; color: #10b981; } h1 { margin: 0; font-size: 1.8rem; } }
.filters-section { display: flex; gap: 1rem; margin-bottom: 2rem; align-items: center; }
.search-container { flex: 1; }
.modern-input { width: 100%; border-radius: 12px; }
.filter-pills { display: flex; gap: 0.5rem; .pill { padding: 0.5rem 1rem; border-radius: 20px; border: 1px solid #e2e8f0; background: white; cursor: pointer; &.active { background: #10b981; color: white; border-color: #10b981; } } }
.categories-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1.5rem; }
.category-card { background: white; border-radius: 16px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); transition: transform 0.2s; &:hover { transform: translateY(-5px); } }
.card-content { padding: 1.5rem; }
.card-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem; }
.category-icon-bg { width: 48px; height: 48px; background: #ecfdf5; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #10b981; .cat-code { font-weight: 800; font-size: 0.8rem; } }
.category-name { margin: 0 0 0.5rem 0; font-size: 1.2rem; }
.category-desc { color: #64748b; font-size: 0.9rem; margin-bottom: 1.5rem; height: 3rem; overflow: hidden; }
.card-footer { display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #f1f5f9; padding-top: 1rem; }
.equip-badge { display: flex; align-items: center; gap: 0.5rem; cursor: pointer; .count { background: #f1f5f9; padding: 0.2rem 0.6rem; border-radius: 12px; font-weight: 700; } .label { font-size: 0.8rem; color: #64748b; } }
.detail-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
.detail-item { label { display: block; font-size: 0.8rem; color: #94a3b8; } span { font-weight: 600; } }
.detail-cat-icon { width: 40px; height: 40px; background: #3b82f6; color: white; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-weight: 800; }

/* Styles du formulaire arrangé */
.form-scroll-container {
  max-height: 70vh;
  overflow-y: auto;
  padding-right: 10px;
  
  &::-webkit-scrollbar { width: 6px; }
  &::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
}

.form-section {
  background: #f8fafc;
  padding: 1.5rem;
  border-radius: 12px;
  border: 1px solid #e2e8f0;
  
  .section-title {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 1.5rem;
    color: #1e293b;
    font-weight: 700;
    font-size: 1.1rem;
    
    i {
      color: #3b82f6;
      font-size: 1.2rem;
    }
  }
}

.empty-attrs-msg {
  text-align: center;
  padding: 2rem;
  background: white;
  border: 2px dashed #cbd5e1;
  border-radius: 12px;
  color: #64748b;
  
  i { font-size: 2rem; margin-bottom: 1rem; }
  p { margin: 0; font-size: 0.9rem; }
}

.attr-row {
  background: white;
  padding: 0.5rem 0.75rem;
  border-radius: 10px;
  border: 1px solid #e2e8f0;
  box-shadow: 0 1px 3px rgba(0,0,0,0.02);
  transition: all 0.2s;
  
  &:hover {
    border-color: #3b82f6;
    background: #f0f9ff;
  }
}

.attr-header-row {
  letter-spacing: 0.02em;
  text-transform: uppercase;
  opacity: 0.8;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
}
</style>
