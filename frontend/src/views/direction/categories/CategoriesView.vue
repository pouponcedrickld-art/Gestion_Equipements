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

        <div class="page-actions" v-if="canManageCategories">
          <Button label="Nouvelle Catégorie" icon="pi pi-plus" @click="openCreateDialog"
            class="p-button-success p-button-raised action-btn" />
        </div>
      </div>

      <!-- Barre de recherche et filtres -->
      <div class="filters-section animate-in">
        <div class="search-container">
          <span class="p-input-icon-left search-input-wrapper">
            <i class="pi pi-search" />
            <InputText v-model="searchTerm" placeholder="Rechercher par nom, code ou description..." @input="handleSearch"
              class="modern-input" />
          </span>
        </div>

        <div class="filter-groups">
          <Dropdown v-model="statusFilter" :options="statusOptions" optionLabel="label" optionValue="value" 
            placeholder="Statut" @change="handleSearch" class="status-dropdown" />
          
          <SelectButton v-model="viewMode" :options="viewOptions" optionLabel="icon" optionValue="value" class="view-toggle">
            <template #option="slotProps">
              <i :class="slotProps.option.icon"></i>
            </template>
          </SelectButton>

          <div class="filter-pills">
            <button class="pill" :class="{ active: !showWithEquipementsOnly }" @click="toggleEquipementsFilter(false)">
              Toutes
            </button>
            <button class="pill" :class="{ active: showWithEquipementsOnly }" @click="toggleEquipementsFilter(true)">
              Avec équipements
            </button>
          </div>
        </div>
      </div>

      <!-- Liste structurée des catégories -->
      <div class="categories-container" v-if="!categorieStore.loading">
        <!-- Vue Table -->
        <DataTable v-if="viewMode === 'table' && categorieStore.categories.length > 0" 
          :value="categorieStore.categories" responsiveLayout="scroll" class="modern-table" :rows="10">
          <!-- ... colonnes ... -->
          <Column field="nom" header="Nom de la catégorie" sortable>
            <template #body="{ data }">
              <div class="flex align-items-center gap-3">
                <div class="cat-icon-small">
                  <i :class="data.parent_id ? 'pi pi-tag' : 'pi pi-folder'"></i>
                </div>
                <div>
                  <div class="font-bold">{{ data.nom }}</div>
                  <div class="text-xs text-gray-500" v-if="data.parent">Parent: {{ data.parent.nom }}</div>
                </div>
              </div>
            </template>
          </Column>
          <Column field="code" header="Code / Slug" sortable>
            <template #body="{ data }">
              <code class="slug-badge">{{ data.code || data.slug }}</code>
            </template>
          </Column>
          <Column field="nombre_equipements" header="Équipements" sortable class="text-center">
            <template #body="{ data }">
              <Badge :value="data.nombre_equipements || '0'" :severity="data.nombre_equipements > 0 ? 'info' : 'secondary'" />
            </template>
          </Column>
          <Column field="statut" header="Statut" sortable>
            <template #body="{ data }">
              <Tag :value="getStatusLabel(data.statut)" :severity="getStatusSeverity(data.statut)" />
            </template>
          </Column>
          <Column header="Actions" class="text-right">
            <template #body="{ data }">
              <div class="flex justify-content-end gap-2">
                <Button icon="pi pi-eye" class="p-button-text p-button-rounded p-button-info" v-tooltip.top="'Détails'" @click="viewCategorie(data)" />
                <Button v-if="canManageCategories" icon="pi pi-pencil" class="p-button-text p-button-rounded" v-tooltip.top="'Modifier'" @click="editCategorie(data)" />
                <Button 
                  v-if="canManageCategories"
                  :icon="data.statut === 'archive' ? 'pi pi-refresh' : 'pi pi-trash'" 
                  :class="['p-button-text p-button-rounded', data.statut === 'archive' ? 'p-button-success' : 'p-button-danger']"
                  v-tooltip.top="data.statut === 'archive' ? 'Désarchiver' : 'Supprimer / Archiver'"
                  @click="handleDeleteOrArchive(data)"
                />
              </div>
            </template>
          </Column>
        </DataTable>

        <!-- Vue Cartes (Grille) -->
        <div v-else-if="viewMode === 'grid' && categorieStore.categories.length > 0" class="categories-grid-display">
          <div v-for="cat in categorieStore.categories" :key="cat.id" class="cat-modern-card animate-card" @click="viewCategorie(cat)">
            <div class="card-header">
              <div class="cat-icon-box">
                <i :class="cat.parent_id ? 'pi pi-tag' : 'pi pi-folder'"></i>
              </div>
              <Tag :value="getStatusLabel(cat.statut)" :severity="getStatusSeverity(cat.statut)" class="compact-tag" />
            </div>
            
            <div class="card-body">
              <h3 class="cat-title">{{ cat.nom }}</h3>
              <code class="cat-code">{{ cat.code || cat.slug }}</code>
              
              <div class="cat-stats-row mt-3">
                <div class="stat">
                  <span class="val">{{ cat.nombre_equipements || 0 }}</span>
                  <span class="lab">Équipements</span>
                </div>
              </div>
              
              <p class="cat-desc" v-if="cat.description">{{ cat.description }}</p>
            </div>

            <div class="card-footer">
              <div class="flex gap-1">
                <Button icon="pi pi-eye" class="p-button-text p-button-rounded p-button-sm p-button-info" @click="viewCategorie(cat)" />
                <Button v-if="canManageCategories" icon="pi pi-pencil" class="p-button-text p-button-rounded p-button-sm" @click="editCategorie(cat)" />
                <Button 
                  v-if="canManageCategories"
                  :icon="cat.statut === 'archive' ? 'pi pi-refresh' : 'pi pi-trash'" 
                  :class="['p-button-text p-button-rounded p-button-sm', cat.statut === 'archive' ? 'p-button-success' : 'p-button-danger']"
                  @click="handleDeleteOrArchive(cat)"
                />
              </div>
            </div>
          </div>
        </div>

        <!-- Vue vide -->
        <div v-else class="empty-state animate-in">
          <i class="pi pi-folder-open"></i>
          <h3>Aucune catégorie trouvée</h3>
          <p>Commencez par créer une catégorie pour organiser votre matériel.</p>
          <Button label="Créer ma première catégorie" icon="pi pi-plus" @click="openCreateDialog" class="p-button-raised mt-3" />
        </div>
      </div>

      <!-- Skeleton loading -->
      <div class="categories-grid-display" v-else>
        <div v-for="n in 8" :key="n" class="cat-modern-card skeleton">
          <div class="card-header skeleton-animate" style="height: 40px"></div>
          <div class="card-body">
            <div class="skeleton-line w-60"></div>
            <div class="skeleton-line w-40"></div>
            <div class="skeleton-line w-full h-2"></div>
          </div>
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
import SelectButton from 'primevue/selectbutton'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Tag from 'primevue/tag'
import Tooltip from 'primevue/tooltip'

// Stores
import { useCategorieStore } from '@/stores/categorieStore'
import { useAuthStore } from '@/stores/authStore'
import { hasRole } from '@/utils/permissions'

const router = useRouter()
const toast = useToast()
const categorieStore = useCategorieStore()
const authStore = useAuthStore()

const pageContainer = ref(null)
const searchTerm = ref('')
const statusFilter = ref('actif')
const showWithEquipementsOnly = ref(false)
const showCreateDialog = ref(false)
const showDeleteDialog = ref(false)
const editingCategorie = ref(null)
const categorieToDelete = ref(null)
const viewMode = ref('grid')
const viewOptions = ref([
  { icon: 'pi pi-list', value: 'table' },
  { icon: 'pi pi-th-large', value: 'grid' }
])

const canManageCategories = computed(() => authStore.isSuperAdmin || authStore.isGestionnaireGeneral)

const statusOptions = [
  { label: 'Actif', value: 'actif' },
  { label: 'Inactif', value: 'inactif' },
  { label: 'Archivé', value: 'archive' },
  { label: 'Tous', value: '' }
]

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
    statut: statusFilter.value,
    with_equipements_only: showWithEquipementsOnly.value 
  })
}

const getStatusLabel = (statut) => {
  const options = {
    'actif': 'Actif',
    'inactif': 'Inactif',
    'archive': 'Archivé'
  }
  return options[statut] || statut
}

const getStatusSeverity = (statut) => {
  const options = {
    'actif': 'success',
    'inactif': 'warning',
    'archive': 'danger'
  }
  return options[statut] || 'info'
}

const toggleEquipementsFilter = (val) => {
  showWithEquipementsOnly.value = val
  handleSearch()
}

const onPageChange = (event) => {
  categorieStore.fetchCategories({ 
    page: event.page + 1,
    search: searchTerm.value,
    statut: statusFilter.value,
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

const viewCategorie = (categorie) => {
  router.push(`/categories/${categorie.id}`)
}

const addEquipementToCategory = (categorie) => {
  router.push({
    path: '/equipements/nouveau',
    query: { categorie_id: categorie.id }
  })
}

const handleDeleteOrArchive = async (categorie) => {
  if (categorie.statut === 'archive') {
    // Désarchiver
    try {
      await categorieStore.updateCategorie(categorie.id, { statut: 'actif' })
      toast.add({ severity: 'success', summary: 'Succès', detail: 'Catégorie désarchivée', life: 3000 })
    } catch (err) {
      toast.add({ severity: 'error', summary: 'Erreur', detail: 'Impossible de désarchiver', life: 3000 })
    }
    return
  }

  if (categorie.nombre_equipements > 0) {
    // Archiver car équipements liés
    if (confirm(`Cette catégorie contient ${categorie.nombre_equipements} équipements. Elle ne peut pas être supprimée, mais elle peut être archivée. Voulez-vous l'archiver ?`)) {
      try {
        await categorieStore.updateCategorie(categorie.id, { statut: 'archive' })
        toast.add({ severity: 'success', summary: 'Succès', detail: 'Catégorie archivée', life: 3000 })
      } catch (err) {
        toast.add({ severity: 'error', summary: 'Erreur', detail: 'Impossible d\'archiver', life: 3000 })
      }
    }
  } else {
    // Supprimer car pas d'équipements
    confirmDelete(categorie)
  }
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
.categories-page { padding: 1rem; max-width: 1400px; margin: 0 auto; }
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; }
.title-with-icon { 
  display: flex; align-items: center; gap: 0.75rem; 
  .icon-wrapper {
    width: 40px; height: 40px;
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    border-radius: 12px; display: flex; align-items: center; justify-content: center;
    color: white; box-shadow: 0 4px 8px rgba(16, 185, 129, 0.2);
    .svg-icon { width: 20px; height: 20px; }
  }
  h1 { margin: 0; font-size: 1.5rem; font-weight: 800; }
  .page-subtitle { color: #64748b; font-size: 0.85rem; margin: 0; }
}

.filters-section { 
  display: flex; 
  justify-content: space-between;
  gap: 1rem; 
  margin-bottom: 1rem; 
  background: white;
  padding: 0.75rem 1rem;
  border-radius: 12px;
  box-shadow: 0 2px 6px rgba(0,0,0,0.03);
}

.search-container { flex: 1; }
.modern-input { width: 100%; border-radius: 10px; height: 36px; font-size: 0.85rem; }

.filter-groups {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.status-dropdown {
  width: 120px;
  border-radius: 10px;
  height: 36px;
  font-size: 0.85rem;
}

.filter-pills { 
  display: flex; 
  gap: 0.25rem; 
  background: #f1f5f9;
  padding: 0.2rem;
  border-radius: 10px;
  
  .pill { 
    padding: 0.4rem 0.75rem; 
    border-radius: 8px; 
    border: none;
    background: transparent; 
    cursor: pointer; 
    font-weight: 600;
    font-size: 0.75rem;
    color: #64748b;
    transition: all 0.2s;
    
    &.active { 
      background: white; 
      color: #3b82f6; 
      box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    } 
  } 
}

.categories-container {
  background: transparent;
  box-shadow: none;
  overflow: visible;
}

.categories-grid-display {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
  gap: 1rem;
}

.cat-modern-card {
  background: white;
  border-radius: 12px;
  padding: 0.75rem;
  box-shadow: 0 2px 10px rgba(0,0,0,0.04);
  border: 1px solid #f1f5f9;
  display: flex;
  flex-direction: column;
  transition: transform 0.2s;
  cursor: pointer;

  &:hover {
    transform: translateY(-3px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.08);
  }

  .card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.75rem;

    .cat-icon-box {
      width: 32px;
      height: 32px;
      background: #eff6ff;
      color: #3b82f6;
      border-radius: 8px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 0.9rem;
    }

    .compact-tag {
      font-size: 0.65rem;
      padding: 0.15rem 0.4rem;
    }
  }

  .card-body {
    flex: 1;

    .cat-title {
      font-size: 1rem;
      font-weight: 700;
      color: #1e293b;
      margin: 0 0 0.2rem 0;
    }

    .cat-code {
      font-size: 0.7rem;
      background: #f8fafc;
      color: #64748b;
      padding: 0.1rem 0.4rem;
      border-radius: 4px;
      font-family: monospace;
    }

    .cat-desc {
      font-size: 0.75rem;
      color: #64748b;
      margin-top: 0.5rem;
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
      overflow: hidden;
    }
  }

  .cat-stats-row {
    .stat {
      display: flex;
      flex-direction: column;
      .val { font-size: 1rem; font-weight: 800; color: #1e293b; }
      .lab { font-size: 0.65rem; color: #94a3b8; text-transform: uppercase; font-weight: 600; }
    }
  }

  .card-footer {
    margin-top: 0.75rem;
    padding-top: 0.75rem;
    border-top: 1px solid #f1f5f9;
    display: flex;
    justify-content: flex-end;
    .p-button { width: 28px; height: 28px; }
  }
}

/* Skeleton */
.skeleton-animate {
  background: linear-gradient(90deg, #f1f5f9 25%, #e2e8f0 50%, #f1f5f9 75%);
  background-size: 200% 100%;
  animation: loading 1.5s infinite;
}

.skeleton-line {
  height: 0.75rem;
  background: #f1f5f9;
  margin-bottom: 0.5rem;
  border-radius: 4px;
  &.w-40 { width: 40%; }
  &.w-60 { width: 60%; }
  &.w-full { width: 100%; }
  &.h-2 { height: 1.25rem; }
}

@keyframes loading {
  0% { background-position: 200% 0; }
  100% { background-position: -200% 0; }
}

.modern-table {
  :deep(.p-datatable-thead > tr > th) {
    background: #f8fafc;
    color: #475569;
    font-weight: 700;
    text-transform: uppercase;
    font-size: 0.75rem;
    letter-spacing: 0.05em;
    padding: 1.25rem 1rem;
    border: none;
  }
  
  :deep(.p-datatable-tbody > tr) {
    transition: background 0.2s;
    &:hover { background: #f1f5f9; }
  }
  
  :deep(.p-datatable-tbody > tr > td) {
    padding: 1rem;
    border-bottom: 1px solid #f1f5f9;
  }
}

.cat-icon-small {
  width: 36px;
  height: 36px;
  background: #eff6ff;
  color: #3b82f6;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.slug-badge {
  background: #f1f5f9;
  padding: 0.25rem 0.5rem;
  border-radius: 6px;
  font-family: monospace;
  color: #475569;
}

.empty-state {
  text-align: center;
  padding: 5rem 2rem;
  color: #64748b;
  
  i { font-size: 4rem; margin-bottom: 1.5rem; color: #cbd5e1; }
  h3 { font-size: 1.5rem; font-weight: 700; margin-bottom: 0.5rem; color: #1e293b; }
}

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
