<template>
<<<<<<< HEAD:frontend/src/views/categories/CategoriesView.vue
  <MainLayout>
    <div class="categories-page" ref="pageContainer">
      <!-- En-tête avec titre et actions -->
      <div class="page-header animate-in">
        <div class="page-title">
          <div class="title-with-icon">
            <div class="icon-wrapper">
              <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="svg-icon">
                <path d="M7 7H7.01M7 3H17C18.1046 3 19 3.89543 19 5V19C19 20.1046 18.1046 21 17 21H7C5.89543 21 5 20.1046 5 19V5C5 3.89543 5.89543 3 7 3Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </div>
            <div>
              <h1>Gestion des Catégories</h1>
              <p class="page-subtitle">Organisez votre inventaire avec élégance</p>
            </div>
          </div>
        </div>
        
        <div class="page-actions" v-if="hasRole(['super_admin', 'gestionnaire_stock_general'])">
          <Button 
            label="Nouvelle Catégorie" 
            icon="pi pi-plus"
            @click="showCreateDialog = true"
            class="p-button-success p-button-raised action-btn"
          />
        </div>
      </div>

      <!-- Barre de recherche et filtres -->
      <div class="filters-section animate-in">
        <div class="search-container">
          <span class="p-input-icon-left search-input-wrapper">
            <i class="pi pi-search" />
            <InputText
              v-model="searchTerm"
              placeholder="Rechercher une catégorie..."
              @input="handleSearch"
              class="modern-input"
            />
          </span>
        </div>
        
        <div class="filter-pills">
          <button
            class="pill"
            :class="{ active: !showWithEquipementsOnly }"
            @click="toggleEquipementsFilter(false)"
          >
            Toutes ({{ categorieStore.pagination.total }})
          </button>
          <button
            class="pill"
            :class="{ active: showWithEquipementsOnly }"
            @click="toggleEquipementsFilter(true)"
          >
            Avec équipements
          </button>
        </div>
      </div>

      <!-- Grille de cartes au lieu du tableau pour un look plus moderne -->
      <div class="categories-grid" v-if="!categorieStore.loading">
        <div 
          v-for="(categorie, index) in categorieStore.categories" 
          :key="categorie.id"
          class="category-card animate-card"
          :style="{ '--index': index }"
        >
          <div class="card-content">
            <div class="card-header">
              <div class="category-icon-bg">
                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="card-svg">
                  <path d="M3 7V17C3 18.1046 3.89543 19 5 19H19C20.1046 19 21 18.1046 21 17V7C21 5.89543 20.1046 5 19 5H5C3.89543 5 3 5.89543 3 7Z" stroke="currentColor" stroke-width="2"/>
                  <path d="M3 10H21" stroke="currentColor" stroke-width="2"/>
                </svg>
              </div>
              <div class="card-actions" v-if="hasRole(['super_admin', 'gestionnaire_stock_general'])">
                <Button icon="pi pi-pencil" class="p-button-text p-button-rounded p-button-sm" @click="editCategorie(categorie)" />
                <Button 
                  icon="pi pi-trash" 
                  class="p-button-text p-button-rounded p-button-sm p-button-danger" 
                  @click="confirmDelete(categorie)"
                  :disabled="categorie.nombre_equipements > 0"
                />
              </div>
            </div>
            
            <h3 class="category-name">{{ categorie.nom }}</h3>
            <p class="category-desc">{{ categorie.description || 'Aucune description fournie' }}</p>
            
            <div class="card-footer">
              <div class="equip-badge" @click="viewCategorie(categorie)">
                <span class="count">{{ categorie.nombre_equipements || 0 }}</span>
                <span class="label">Équipements</span>
              </div>
              <Button icon="pi pi-chevron-right" class="p-button-text p-button-rounded" @click="viewCategorie(categorie)" />
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
        <Paginator 
          :rows="categorieStore.pagination.per_page" 
          :totalRecords="categorieStore.pagination.total" 
          @page="onPageChange"
          template="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink"
        />
      </div>

      <!-- Dialog de création/modification -->
      <Dialog
        :header="editingCategorie ? 'Modifier la Catégorie' : 'Nouvelle Catégorie'"
        v-model:visible="showCreateDialog"
        :style="{ width: '500px' }"
        :modal="true"
        class="category-dialog"
      >
        <form @submit.prevent="saveCategorie" class="category-form">
          <div class="p-field">
            <label for="nom" class="p-field-label">Nom *</label>
            <InputText
              id="nom"
              v-model="categorieForm.nom"
              :class="{ 'p-invalid': formErrors.nom }"
              placeholder="Ex: Smartphones, Tablettes..."
              autofocus
            />
            <small class="p-error" v-if="formErrors.nom">{{ formErrors.nom }}</small>
          </div>

          <div class="p-field">
            <label for="description" class="p-field-label">Description</label>
            <Textarea
              id="description"
              v-model="categorieForm.description"
              rows="3"
              placeholder="Description détaillée de la catégorie (optionnel)"
              :class="{ 'p-invalid': formErrors.description }"
            />
            <small class="p-error" v-if="formErrors.description">{{ formErrors.description }}</small>
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
              :label="editingCategorie ? 'Modifier' : 'Créer'"
              :loading="categorieStore.loading"
              :disabled="!canSubmit"
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
            Êtes-vous sûr de vouloir supprimer la catégorie 
            <strong>{{ categorieToDelete?.nom }}</strong> ?
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
            @click="deleteCategorie"
            :loading="categorieStore.loading"
          />
        </template>
      </Dialog>

      <!-- Dialog de détails -->
      <Dialog
        header="Détails de la Catégorie"
        v-model:visible="showDetailsDialog"
        :style="{ width: '600px' }"
        :modal="true"
      >
        <div v-if="selectedCategorie" class="category-details">
          <div class="detail-section">
            <h3>{{ selectedCategorie.nom }}</h3>
            <p v-if="selectedCategorie.description" class="category-description">
              {{ selectedCategorie.description }}
            </p>
            <p v-else class="no-description">Aucune description</p>
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
              
              <div class="stat-item" v-if="selectedCategorie.statistiques.par_statut">
                <div class="status-breakdown">
                  <div v-for="(count, status) in selectedCategorie.statistiques.par_statut" :key="status" class="status-stat">
                    <Badge :value="count" class="p-badge-info" />
                    <span>{{ status.replace('_', ' ') }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Liste des équipements récents -->
          <div class="detail-section" v-if="selectedCategorie.equipements?.length > 0">
            <h4>Équipements récents</h4>
            <div class="equipements-list">
              <div v-for="equipement in selectedCategorie.equipements.slice(0, 5)" :key="equipement.id" class="equipement-item">
                <div class="equipement-info">
                  <strong>{{ equipement.reference }}</strong>
                  <span>{{ equipement.marque }} {{ equipement.modele }}</span>
                </div>
                <Badge :value="equipement.statut_global" :class="`status-${equipement.statut_global.replace('_', '-')}`" />
              </div>
            </div>
          </div>
        </div>
      </Dialog>
=======
  <DirectionLayout>
    <div class="page-placeholder">
      <h2>{{ pageTitle }}</h2>
      <p>Page en cours de développement...</p>
>>>>>>> 2c965af4f2361eccbef055db409105b763f2340d:frontend/src/views/direction/categories/CategoriesView.vue
    </div>
  </DirectionLayout>
</template>

<script setup>
<<<<<<< HEAD
import { computed } from 'vue'
import { useRoute } from 'vue-router'
<<<<<<< HEAD:frontend/src/views/categories/CategoriesView.vue
=======
import { ref, computed, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useToast } from 'primevue/usetoast'
import { useConfirm } from 'primevue/useconfirm'
>>>>>>> ffa46c85f50ce66431a07232052669c1f18451a6
=======
import DirectionLayout from '@/layouts/DirectionLayout.vue'
>>>>>>> 2c965af4f2361eccbef055db409105b763f2340d:frontend/src/views/direction/categories/CategoriesView.vue

// Composants PrimeVue
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import Textarea from 'primevue/textarea'
import Dialog from 'primevue/dialog'
import Badge from 'primevue/badge'

// Layout et stores
import MainLayout from '@/layouts/MainLayout.vue'
import { useCategorieStore } from '@/stores/categorieStore'
import { hasRole } from '@/utils/permissions'
import Paginator from 'primevue/paginator'
import gsap from 'gsap'

// Initialisation
const router = useRouter()
const toast = useToast()
const confirm = useConfirm()
const categorieStore = useCategorieStore()

// Refs pour GSAP
const pageContainer = ref(null)

// États réactifs
const searchTerm = ref('')
const showWithEquipementsOnly = ref(false)
const showCreateDialog = ref(false)
const showDeleteDialog = ref(false)
const showDetailsDialog = ref(false)
const editingCategorie = ref(null)
const selectedCategorie = ref(null)
const categorieToDelete = ref(null)

// Formulaire
const categorieForm = ref({
  nom: '',
  description: ''
})

const formErrors = ref({})

// Getters calculés
const canSubmit = computed(() => {
  return categorieForm.value.nom.trim().length >= 2 &&
         !categorieStore.loading
})

// Animations GSAP
const runInitialAnimations = () => {
  gsap.from('.animate-in', {
    opacity: 0,
    y: 20,
    duration: 0.8,
    stagger: 0.2,
    ease: 'power3.out'
  })
}

const animateCards = () => {
  gsap.from('.animate-card', {
    opacity: 0,
    y: 20,
    duration: 0.6,
    stagger: 0.1,
    ease: 'back.out(1.7)'
  })
}

// Méthodes

/**
 * Charger les catégories initiales
 */
onMounted(async () => {
  await categorieStore.fetchCategories()
  setTimeout(runInitialAnimations, 100)
  setTimeout(animateCards, 300)
})

// Observer les changements de catégories pour relancer les animations des cartes
watch(() => categorieStore.categories, () => {
  setTimeout(animateCards, 100)
}, { deep: true })

/**
 * Gérer la recherche avec debounce
 */
let searchTimeout = null
const handleSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    categorieStore.searchCategories(searchTerm.value)
  }, 300)
}

/**
 * Basculer le filtre "avec équipements seulement"
 */
const toggleEquipementsFilter = (withEquipements) => {
  showWithEquipementsOnly.value = withEquipements
  if (withEquipements) {
    categorieStore.fetchCategories({ with_equipements_only: true })
  } else {
    categorieStore.fetchCategories()
  }
}

/**
 * Gérer le changement de page
 */
const onPageChange = (event) => {
  categorieStore.changePage(event.page + 1)
}

/**
 * Voir les détails d'une catégorie
 */
const viewCategorie = async (categorie) => {
  try {
    selectedCategorie.value = await categorieStore.fetchCategorie(categorie.id)
    showDetailsDialog.value = true
  } catch (error) {
    toast.add({
      severity: 'error',
      summary: 'Erreur',
      detail: 'Impossible de charger les détails de la catégorie',
      life: 3000
    })
  }
}

/**
 * Éditer une catégorie
 */
const editCategorie = (categorie) => {
  editingCategorie.value = categorie
  categorieForm.value = {
    nom: categorie.nom,
    description: categorie.description || ''
  }
  formErrors.value = {}
  showCreateDialog.value = true
}

/**
 * Confirmer la suppression
 */
const confirmDelete = (categorie) => {
  if (categorie.nombre_equipements > 0) {
    toast.add({
      severity: 'warn',
      summary: 'Suppression impossible',
      detail: 'Cette catégorie contient des équipements et ne peut pas être supprimée',
      life: 4000
    })
    return
  }
  
  categorieToDelete.value = categorie
  showDeleteDialog.value = true
}

/**
 * Sauvegarder une catégorie (création ou modification)
 */
const saveCategorie = async () => {
  formErrors.value = {}
  
  // Validation côté client
  if (!categorieForm.value.nom.trim()) {
    formErrors.value.nom = 'Le nom est obligatoire'
    return
  }
  
  if (categorieForm.value.nom.length < 2) {
    formErrors.value.nom = 'Le nom doit contenir au moins 2 caractères'
    return
  }

  try {
    const data = {
      nom: categorieForm.value.nom.trim(),
      description: categorieForm.value.description.trim() || null
    }

    if (editingCategorie.value) {
      // Modification
      await categorieStore.updateCategorie(editingCategorie.value.id, data)
      toast.add({
        severity: 'success',
        summary: 'Succès',
        detail: 'Catégorie modifiée avec succès',
        life: 3000
      })
    } else {
      // Création
      await categorieStore.createCategorie(data)
      toast.add({
        severity: 'success',
        summary: 'Succès',
        detail: 'Catégorie créée avec succès',
        life: 3000
      })
    }

    cancelEdit()
    
  } catch (error) {
    // Gérer les erreurs de validation du serveur
    if (error.response?.data?.errors) {
      formErrors.value = error.response.data.errors
    } else {
      toast.add({
        severity: 'error',
        summary: 'Erreur',
        detail: error.message || 'Erreur lors de la sauvegarde',
        life: 4000
      })
    }
  }
}

/**
 * Supprimer une catégorie
 */
const deleteCategorie = async () => {
  try {
    await categorieStore.deleteCategorie(categorieToDelete.value.id)
    
    toast.add({
      severity: 'success',
      summary: 'Succès',
      detail: 'Catégorie supprimée avec succès',
      life: 3000
    })
    
    showDeleteDialog.value = false
    categorieToDelete.value = null
    
  } catch (error) {
    toast.add({
      severity: 'error',
      summary: 'Erreur',
      detail: error.message || 'Erreur lors de la suppression',
      life: 4000
    })
  }
}

/**
 * Annuler l'édition
 */
const cancelEdit = () => {
  showCreateDialog.value = false
  editingCategorie.value = null
  categorieForm.value = {
    nom: '',
    description: ''
  }
  formErrors.value = {}
}

// Nettoyer les erreurs quand on ferme les dialogs
watch(showCreateDialog, (newValue) => {
  if (!newValue) {
    categorieStore.clearError()
  }
})
</script>

<script>
export default {
  name: 'CategoriesView'
}
</script>

<style scoped lang="scss">
.categories-page {
  padding: 2rem;
  min-height: 100vh;
  background: #f8fafc;
}

.animate-in {
  /* opacity: 0; */
}

.title-with-icon {
  display: flex;
  align-items: center;
  gap: 1.5rem;

  .icon-wrapper {
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    box-shadow: 0 8px 16px rgba(37, 99, 235, 0.2);

    .svg-icon {
      width: 32px;
      height: 32px;
    }
  }

  h1 {
    font-size: 2rem;
    font-weight: 800;
    color: #1e293b;
    margin: 0;
    letter-spacing: -0.025em;
  }

  .page-subtitle {
    color: #64748b;
    margin: 0.25rem 0 0 0;
    font-size: 1rem;
  }
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 3rem;
}

.filters-section {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2.5rem;
  gap: 2rem;
}

.search-container {
  flex: 1;
  max-width: 500px;

  .search-input-wrapper {
    width: 100%;
    
    i {
      color: #94a3b8;
    }
  }

  .modern-input {
    width: 100%;
    padding: 0.75rem 1rem 0.75rem 2.5rem;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    background: white;
    transition: all 0.3s ease;

    &:focus {
      border-color: #3b82f6;
      box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
    }
  }
}

.filter-pills {
  display: flex;
  gap: 0.75rem;
  background: #f1f5f9;
  padding: 0.4rem;
  border-radius: 14px;

  .pill {
    padding: 0.6rem 1.2rem;
    border-radius: 10px;
    border: none;
    background: transparent;
    color: #64748b;
    font-weight: 600;
    font-size: 0.9rem;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);

    &:hover {
      color: #1e293b;
    }

    &.active {
      background: white;
      color: #3b82f6;
      box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    }
  }
}

.categories-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 2rem;
  margin-bottom: 3rem;
}

.category-card {
  background: white;
  border-radius: 24px;
  padding: 1.75rem;
  border: 1px solid #f1f5f9;
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
  position: relative;
  overflow: hidden;

  &::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 4px;
    background: linear-gradient(90deg, #3b82f6, #60a5fa);
    opacity: 0;
    transition: opacity 0.3s ease;
  }

  &:hover {
    transform: translateY(-8px);
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.05), 0 10px 10px -5px rgba(0, 0, 0, 0.02);
    border-color: #e2e8f0;

    &::before {
      opacity: 1;
    }

    .category-icon-bg {
      transform: scale(1.1) rotate(-5deg);
      background: #eff6ff;
      color: #3b82f6;
    }
  }
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1.5rem;
}

.category-icon-bg {
  width: 52px;
  height: 52px;
  background: #f8fafc;
  border-radius: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #64748b;
  transition: all 0.4s ease;

  .card-svg {
    width: 26px;
    height: 26px;
  }
}

.category-name {
  font-size: 1.25rem;
  font-weight: 700;
  color: #1e293b;
  margin: 0 0 0.75rem 0;
}

.category-desc {
  color: #64748b;
  font-size: 0.95rem;
  line-height: 1.6;
  margin-bottom: 2rem;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  height: 3.2em;
}

.card-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding-top: 1.25rem;
  border-top: 1px solid #f1f5f9;
}

.equip-badge {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  cursor: pointer;
  padding: 0.4rem 0.8rem;
  border-radius: 10px;
  transition: background 0.2s ease;

  &:hover {
    background: #f8fafc;
  }

  .count {
    background: #3b82f6;
    color: white;
    font-weight: 700;
    padding: 0.2rem 0.6rem;
    border-radius: 6px;
    font-size: 0.85rem;
  }

  .label {
    color: #475569;
    font-size: 0.85rem;
    font-weight: 600;
  }
}

.skeleton {
  min-height: 250px;
  background: #f1f5f9;
  
  .skeleton-content {
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, #f1f5f9 25%, #e2e8f0 50%, #f1f5f9 75%);
    background-size: 200% 100%;
    animation: loading 1.5s infinite;
  }
}

@keyframes loading {
  0% { background-position: 200% 0; }
  100% { background-position: -200% 0; }
}

// Dialog Overrides
:deep(.p-dialog) {
  border-radius: 24px;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
  
  .p-dialog-header {
    padding: 2rem 2rem 1rem 2rem;
    border-radius: 24px 24px 0 0;
  }
  
  .p-dialog-content {
    padding: 0 2rem 2rem 2rem;
  }
}

.modern-pagination {
  display: flex;
  justify-content: center;
  
  :deep(.p-paginator) {
    background: transparent;
    border: none;
    
    .p-paginator-page {
      border-radius: 8px;
      margin: 0 2px;
      
      &.p-highlight {
        background: #3b82f6;
        color: white;
      }
    }
  }
}

@media (max-width: 768px) {
  .page-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 1.5rem;
  }
  
  .filters-section {
    flex-direction: column;
    align-items: stretch;
  }
  
  .search-container {
    max-width: none;
  }
}
</style>
