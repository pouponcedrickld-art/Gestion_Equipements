<template>
<<<<<<< HEAD:frontend/src/views/equipements/EquipementsView.vue
  <MainLayout>
    <div class="equipements-page" ref="pageContainer">
      <!-- En-tête -->
      <div class="page-header animate-in">
        <div class="header-left">
          <div class="title-with-icon">
            <div class="icon-wrapper">
              <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="svg-icon">
                <path d="M9 17H5C3.89543 17 3 16.1046 3 15V5C3 3.89543 3.89543 3 5 3H19C20.1046 3 21 3.89543 21 5V15C21 16.1046 20.1046 17 19 17H15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M7 21H17" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M12 17V21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </div>
            <div>
              <h1>Gestion des Équipements</h1>
              <p class="subtitle">Suivi en temps réel de votre parc informatique</p>
            </div>
          </div>
        </div>
        <div class="header-actions">
          <Button 
            label="Importer CSV" 
            icon="pi pi-upload" 
            class="p-button-outlined p-button-secondary action-btn mr-2"
            @click="showImportDialog = true"
          />
          <Button 
            label="Scanner QR" 
            icon="pi pi-qrcode" 
            class="p-button-outlined p-button-info action-btn mr-2"
            @click="$router.push('/equipements/scan')"
          />
          <Button 
            label="Nouvel Équipement" 
            icon="pi pi-plus" 
            class="p-button-success p-button-raised action-btn"
            @click="$router.push('/equipements/nouveau')"
          />
        </div>
      </div>

      <!-- Statistiques Modernes -->
      <div class="stats-container animate-in">
        <div class="stat-glass-card total">
          <div class="stat-icon-box">
            <i class="pi pi-desktop"></i>
          </div>
          <div class="stat-details">
            <span class="value">{{ equipements.length }}</span>
            <span class="label">Total Parc</span>
          </div>
          <div class="stat-progress" style="--progress: 100%"></div>
        </div>
        
        <div class="stat-glass-card success">
          <div class="stat-icon-box">
            <i class="pi pi-check-circle"></i>
          </div>
          <div class="stat-details">
            <span class="value">{{ equipementsDisponibles }}</span>
            <span class="label">En Stock</span>
          </div>
          <div class="stat-progress" :style="`--progress: ${equipements.length ? (equipementsDisponibles / equipements.length) * 100 : 0}%`"></div>
        </div>

        <div class="stat-glass-card warning">
          <div class="stat-icon-box">
            <i class="pi pi-user"></i>
          </div>
          <div class="stat-details">
            <span class="value">{{ equipementsAffectes }}</span>
            <span class="label">Affectés</span>
          </div>
          <div class="stat-progress" :style="`--progress: ${equipements.length ? (equipementsAffectes / equipements.length) * 100 : 0}%`"></div>
        </div>

        <div class="stat-glass-card danger">
          <div class="stat-icon-box">
            <i class="pi pi-wrench"></i>
          </div>
          <div class="stat-details">
            <span class="value">{{ equipementsMaintenance }}</span>
            <span class="label">Maintenance</span>
          </div>
          <div class="stat-progress" :style="`--progress: ${equipements.length ? (equipementsMaintenance / equipements.length) * 100 : 0}%`"></div>
        </div>
      </div>

      <!-- Filtres Modernes -->
      <div class="filters-bar animate-in">
        <div class="search-box">
          <i class="pi pi-search"></i>
          <InputText
            v-model="searchQuery"
            placeholder="Référence, N° Série, Modèle..."
            class="search-input"
          />
        </div>
        <div class="dropdown-filters">
          <Dropdown
            v-model="selectedCategorie"
            :options="categories"
            optionLabel="nom"
            optionValue="id"
            placeholder="Catégorie"
            class="modern-dropdown"
            showClear
          />
          <Dropdown
            v-model="selectedStatut"
            :options="statutOptions"
            optionLabel="label"
            optionValue="value"
            placeholder="Statut"
            class="modern-dropdown"
            showClear
          />
          <Button 
            icon="pi pi-filter-slash" 
            class="p-button-text p-button-rounded p-button-secondary"
            @click="resetFilters"
            v-tooltip.top="'Réinitialiser'"
          />
        </div>
      </div>

      <!-- Liste des Équipements (Cards avec animations) -->
      <div class="equipements-grid" v-if="!loading">
        <div 
          v-for="(equip, index) in filteredEquipements" 
          :key="equip.id"
          class="equip-card animate-card"
          :style="`--index: ${index}`"
        >
          <div class="card-image-box" @click="viewDetails(equip)">
            <img v-if="equip.photo" :src="`${apiBaseUrl}/storage/${equip.photo}`" :alt="equip.marque" />
            <div v-else class="img-placeholder">
              <i class="pi pi-image"></i>
            </div>
            <div class="status-overlay">
              <Tag :value="getStatutLabel(equip.statut_global)" :severity="getStatutSeverity(equip.statut_global)" />
            </div>
          </div>
          
          <div class="card-body">
            <div class="card-header-info">
              <span class="category-tag">{{ equip.categorie?.nom }}</span>
              <span class="serial-no">{{ equip.numero_serie }}</span>
            </div>
            
            <h3 class="equip-title" @click="viewDetails(equip)">{{ equip.marque }} {{ equip.modele }}</h3>
            
            <div class="equip-meta">
              <div class="meta-item">
                <i class="pi pi-map-marker"></i>
                <span>{{ equip.agence_actuelle?.nom || 'Non attribué' }}</span>
              </div>
              <div class="meta-item">
                <i class="pi pi-info-circle"></i>
                <span>{{ getEtatLabel(equip.etat) }}</span>
              </div>
            </div>
          </div>
          
          <div class="card-actions-bar">
            <Button icon="pi pi-eye" class="p-button-text p-button-rounded p-button-info" @click="viewDetails(equip)" v-tooltip.top="'Détails'" />
            <Button icon="pi pi-pencil" class="p-button-text p-button-rounded p-button-warning" @click="editEquipement(equip)" v-tooltip.top="'Modifier'" />
            <Button icon="pi pi-qrcode" class="p-button-text p-button-rounded p-button-secondary" @click="showQRCode(equip)" v-tooltip.top="'QR Code'" />
            <Button icon="pi pi-trash" class="p-button-text p-button-rounded p-button-danger" @click="confirmDelete(equip)" v-tooltip.top="'Supprimer'" />
          </div>
        </div>
      </div>

      <!-- Skeleton loading -->
      <div class="equipements-grid" v-else>
        <div v-for="n in 8" :key="n" class="equip-card skeleton">
          <div class="skeleton-img"></div>
          <div class="skeleton-body"></div>
        </div>
      </div>

      <!-- Dialog QR Code Moderne -->
      <Dialog 
        v-model:visible="showQRDialog" 
        header="Identification Digitale"
        :modal="true"
        class="modern-dialog qr-dialog"
        :draggable="false"
      >
        <div class="qr-dialog-body" v-if="selectedEquipement">
          <div class="qr-card-preview">
            <div class="qr-header">
              <span class="brand">{{ selectedEquipement.marque }}</span>
              <span class="model">{{ selectedEquipement.modele }}</span>
            </div>
            
            <div class="qr-main">
              <div class="qr-frame">
                <img 
                  v-if="selectedEquipement.qr_code" 
                  :src="`${apiBaseUrl}/storage/${selectedEquipement.qr_code}`" 
                  alt="QR Code"
                />
                <div v-else class="qr-placeholder">
                  <i class="pi pi-qrcode"></i>
                  <span>Non généré</span>
                </div>
              </div>
            </div>

            <div class="qr-footer">
              <span class="serial">{{ selectedEquipement.numero_serie }}</span>
            </div>
          </div>
          
          <div class="qr-actions-grid">
            <Button 
              label="Télécharger" 
              icon="pi pi-download" 
              class="p-button-outlined action-card"
              @click="downloadQRCode"
              :disabled="!selectedEquipement.qr_code"
            />
            <Button 
              label="Régénérer" 
              icon="pi pi-refresh" 
              class="p-button-outlined p-button-secondary action-card"
              @click="generateQRCode"
            />
          </div>
        </div>
      </Dialog>

      <!-- Dialog Import CSV Moderne -->
      <Dialog 
        v-model:visible="showImportDialog" 
        header="Importation de Données"
        :modal="true"
        class="modern-dialog import-dialog"
        :draggable="false"
      >
        <div class="import-body">
          <div class="info-alert">
            <i class="pi pi-info-circle"></i>
            <p>Utilisez notre modèle standard pour garantir une importation sans erreur.</p>
          </div>
          
          <div class="import-steps">
            <div class="import-step">
              <div class="step-num">1</div>
              <div class="step-text">
                <strong>Télécharger le modèle</strong>
                <p>Fichier CSV pré-configuré</p>
              </div>
              <Button icon="pi pi-download" class="p-button-rounded p-button-text" @click="downloadTemplate" />
            </div>
            
            <div class="import-step">
              <div class="step-num">2</div>
              <div class="step-text">
                <strong>Uploader votre fichier</strong>
                <p>Glissez votre CSV rempli ici</p>
              </div>
            </div>
          </div>

          <FileUpload
            mode="basic"
            accept=".csv"
            :maxFileSize="5000000"
            chooseLabel="Sélectionner le fichier CSV"
            @select="handleFileSelect"
            class="modern-file-upload"
          />
        </div>
      </Dialog>

      <!-- Dialog Confirmation Suppression Moderne -->
      <Dialog 
        v-model:visible="showDeleteDialog" 
        header="Confirmation de Retrait"
        :modal="true"
        class="modern-dialog delete-dialog"
        :draggable="false"
      >
        <div class="delete-body" v-if="selectedEquipement">
          <div class="warning-icon-wrapper">
            <i class="pi pi-trash"></i>
          </div>
          <h3>Supprimer cet équipement ?</h3>
          <p>Vous êtes sur le point de retirer définitivement l'unité <strong>{{ selectedEquipement.reference }}</strong> du système.</p>
          
          <div class="item-preview-mini">
            <img v-if="selectedEquipement.photo" :src="`${apiBaseUrl}/storage/${selectedEquipement.photo}`" />
            <div class="item-info">
              <span class="name">{{ selectedEquipement.marque }} {{ selectedEquipement.modele }}</span>
              <span class="sn">{{ selectedEquipement.numero_serie }}</span>
            </div>
          </div>
        </div>
        <template #footer>
          <div class="delete-actions">
            <Button label="Annuler" class="p-button-text p-button-secondary" @click="showDeleteDialog = false" />
            <Button label="Confirmer la suppression" class="p-button-danger p-button-raised" @click="deleteEquipement" />
          </div>
        </template>
      </Dialog>
=======
  <DirectionLayout>
    <div class="page-placeholder">
      <h2>{{ pageTitle }}</h2>
      <p>Page en cours de développement...</p>
>>>>>>> 2c965af4f2361eccbef055db409105b763f2340d:frontend/src/views/direction/equipements/EquipementsView.vue
    </div>
  </DirectionLayout>
</template>

<script setup>
<<<<<<< HEAD:frontend/src/views/equipements/EquipementsView.vue
import { ref, computed, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useToast } from 'primevue/usetoast'
import MainLayout from '@/layouts/MainLayout.vue'
import { useEquipementStore } from '@/stores/equipementStore'
import { useCategorieStore } from '@/stores/categorieStore'
=======
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import DirectionLayout from '@/layouts/DirectionLayout.vue'
>>>>>>> 2c965af4f2361eccbef055db409105b763f2340d:frontend/src/views/direction/equipements/EquipementsView.vue

// PrimeVue Components
import Button from 'primevue/button'
import Card from 'primevue/card'
import InputText from 'primevue/inputtext'
import Dropdown from 'primevue/dropdown'
import Dialog from 'primevue/dialog'
import Tag from 'primevue/tag'
import FileUpload from 'primevue/fileupload'

import gsap from 'gsap'

const router = useRouter()
const toast = useToast()
const equipementStore = useEquipementStore()
const categorieStore = useCategorieStore()

// Refs pour GSAP
const pageContainer = ref(null)

// État local
const loading = ref(false)
const searchQuery = ref('')
const selectedCategorie = ref(null)
const selectedStatut = ref(null)
const selectedEquipement = ref(null)
const showQRDialog = ref(false)
const showImportDialog = ref(false)
const showDeleteDialog = ref(false)

// Configuration API
const apiBaseUrl = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000'

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
    stagger: 0.05,
    ease: 'power2.out'
  })
}

// Options de filtres
const statutOptions = [
  { label: 'Stock Général', value: 'en_stock_general' },
  { label: 'Stock Local', value: 'en_stock_local' },
  { label: 'Affecté', value: 'affecte' },
  { label: 'En Transit', value: 'en_transit' },
  { label: 'En Panne', value: 'en_panne' },
  { label: 'En Maintenance', value: 'en_maintenance' },
  { label: 'Réformé', value: 'reforme' }
]

// Données computed
const equipements = computed(() => equipementStore.equipements)
const categories = computed(() => categorieStore.categories)

const filteredEquipements = computed(() => {
  let result = [...equipements.value]

  // Filtre par recherche
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    result = result.filter(eq => 
      eq.marque?.toLowerCase().includes(query) ||
      eq.modele?.toLowerCase().includes(query) ||
      eq.numero_serie?.toLowerCase().includes(query) ||
      eq.code_inventaire?.toLowerCase().includes(query) ||
      eq.reference?.toLowerCase().includes(query)
    )
  }

  // Filtre par catégorie
  if (selectedCategorie.value) {
    result = result.filter(eq => eq.categorie_id === selectedCategorie.value)
  }

  // Filtre par statut
  if (selectedStatut.value) {
    result = result.filter(eq => eq.statut_global === selectedStatut.value)
  }

  return result
})

// Observer les changements de filtres pour relancer les animations des cartes
watch(filteredEquipements, () => {
  setTimeout(animateCards, 50)
}, { deep: true })

// Statistiques
const equipementsDisponibles = computed(() => 
  equipements.value.filter(eq => ['en_stock_general', 'en_stock_local'].includes(eq.statut_global)).length
)
const equipementsAffectes = computed(() => 
  equipements.value.filter(eq => eq.statut_global === 'affecte').length
)
const equipementsMaintenance = computed(() => 
  equipements.value.filter(eq => ['en_panne', 'en_maintenance'].includes(eq.statut_global)).length
)

// Méthodes
const loadData = async () => {
  loading.value = true
  try {
    await equipementStore.fetchEquipements()
    await categorieStore.fetchCategories()
    setTimeout(runInitialAnimations, 100)
  } catch (error) {
    toast.add({
      severity: 'error',
      summary: 'Erreur',
      detail: 'Impossible de charger les données',
      life: 3000
    })
  } finally {
    loading.value = false
    setTimeout(animateCards, 100)
  }
}

const resetFilters = () => {
  searchQuery.value = ''
  selectedCategorie.value = null
  selectedStatut.value = null
}

const viewDetails = (equipement) => {
  router.push(`/equipements/${equipement.id}`)
}

const editEquipement = (equipement) => {
  router.push(`/equipements/${equipement.id}/modifier`)
}

const showQRCode = (equipement) => {
  selectedEquipement.value = equipement
  showQRDialog.value = true
}

const generateQRCode = async () => {
  try {
    await equipementStore.generateQRCode(selectedEquipement.value.id)
    await loadData() // Recharger pour avoir le QR
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
      detail: 'Impossible de générer le QR Code',
      life: 3000
    })
  }
}

const downloadQRCode = () => {
  if (selectedEquipement.value?.qr_code) {
    const link = document.createElement('a')
    link.href = `${apiBaseUrl}/storage/${selectedEquipement.value.qr_code}`
    link.download = `qrcode_${selectedEquipement.value.numero_serie}.png`
    link.click()
  }
}

const downloadTemplate = async () => {
  try {
    await equipementStore.downloadTemplate()
    toast.add({
      severity: 'success',
      summary: 'Succès',
      detail: 'Template téléchargé',
      life: 3000
    })
  } catch (error) {
    toast.add({
      severity: 'error',
      summary: 'Erreur',
      detail: 'Impossible de télécharger le template',
      life: 3000
    })
  }
}

const handleFileSelect = async (event) => {
  const file = event.files[0]
  if (file) {
    try {
      await equipementStore.importCSV(file)
      await loadData()
      showImportDialog.value = false
      toast.add({
        severity: 'success',
        summary: 'Succès',
        detail: 'Import réussi',
        life: 3000
      })
    } catch (error) {
      toast.add({
        severity: 'error',
        summary: 'Erreur',
        detail: error.message || 'Erreur lors de l\'import',
        life: 3000
      })
    }
  }
}

const confirmDelete = (equipement) => {
  selectedEquipement.value = equipement
  showDeleteDialog.value = true
}

const deleteEquipement = async () => {
  try {
    await equipementStore.deleteEquipement(selectedEquipement.value.id)
    showDeleteDialog.value = false
    selectedEquipement.value = null
    toast.add({
      severity: 'success',
      summary: 'Succès',
      detail: 'Équipement supprimé',
      life: 3000
    })
  } catch (error) {
    toast.add({
      severity: 'error',
      summary: 'Erreur',
      detail: 'Impossible de supprimer l\'équipement',
      life: 3000
    })
  }
}

// Helpers pour affichage
const getStatutLabel = (statut) => {
  const labels = {
    en_stock_general: 'Stock Général',
    en_stock_local: 'Stock Local',
    en_transit: 'En Transit',
    affecte: 'Affecté',
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
    en_transit: 'info',
    affecte: 'info',
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

const formatDate = (date) => {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('fr-FR')
}

// Lifecycle
onMounted(() => {
  loadData()
})
</script>

<script>
export default {
  name: 'EquipementsView'
}
</script>

<style lang="scss" scoped>
.equipements-page {
  padding: 2rem;
  min-height: 100vh;
  background: #f8fafc;
}

.animate-in {
  /* On ne met plus opacity: 0 ici pour que le contenu reste visible sans JS */
}

.title-with-icon {
  display: flex;
  align-items: center;
  gap: 1.5rem;

  .icon-wrapper {
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, #0ea5e9 0%, #2563eb 100%);
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

  .subtitle {
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

.stats-container {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
  gap: 1.5rem;
  margin-bottom: 3rem;
}

.stat-glass-card {
  background: white;
  padding: 1.5rem;
  border-radius: 20px;
  border: 1px solid #f1f5f9;
  display: flex;
  align-items: center;
  gap: 1.25rem;
  position: relative;
  overflow: hidden;
  transition: all 0.3s ease;

  &:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05);
  }

  .stat-icon-box {
    width: 50px;
    height: 50px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
  }

  .stat-details {
    display: flex;
    flex-direction: column;

    .value {
      font-size: 1.75rem;
      font-weight: 800;
      color: #1e293b;
      line-height: 1;
    }

    .label {
      font-size: 0.875rem;
      color: #64748b;
      font-weight: 600;
      margin-top: 0.25rem;
    }
  }

  .stat-progress {
    position: absolute;
    bottom: 0;
    left: 0;
    height: 4px;
    background: currentColor;
    width: var(--progress);
    opacity: 0.2;
  }

  &.total { color: #3b82f6; .stat-icon-box { background: #eff6ff; } }
  &.success { color: #10b981; .stat-icon-box { background: #ecfdf5; } }
  &.warning { color: #f59e0b; .stat-icon-box { background: #fffbeb; } }
  &.danger { color: #ef4444; .stat-icon-box { background: #fef2f2; } }
}

.filters-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: white;
  padding: 0.75rem 1.5rem;
  border-radius: 16px;
  margin-bottom: 2.5rem;
  border: 1px solid #f1f5f9;
  gap: 2rem;

  .search-box {
    flex: 1;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    color: #94a3b8;

    .search-input {
      border: none;
      width: 100%;
      padding: 0.5rem 0;
      font-size: 1rem;
      
      &:focus {
        box-shadow: none;
      }
    }
  }

  .dropdown-filters {
    display: flex;
    gap: 1rem;
    align-items: center;
  }
}

.modern-dropdown {
  border: none;
  background: #f8fafc;
  border-radius: 10px;
  min-width: 160px;
}

.equipements-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 2rem;
  margin-bottom: 3rem;
}

.equip-card {
  background: white;
  border-radius: 24px;
  overflow: hidden;
  border: 1px solid #f1f5f9;
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
  display: flex;
  flex-direction: column;
  /* On enlève opacity: 0 et transform pour laisser GSAP gérer le 'from' */

  &:hover {
    transform: translateY(-10px);
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.05);
    border-color: #e2e8f0;

    .card-image-box img {
      transform: scale(1.05);
    }
  }
}

.card-image-box {
  height: 200px;
  position: relative;
  overflow: hidden;
  background: #f8fafc;
  cursor: pointer;

  img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.6s ease;
  }

  .img-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #cbd5e1;
    font-size: 3rem;
  }

  .status-overlay {
    position: absolute;
    top: 1rem;
    right: 1rem;
  }
}

.card-body {
  padding: 1.5rem;
  flex: 1;

  .card-header-info {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.75rem;
    
    .category-tag {
      font-size: 0.75rem;
      font-weight: 700;
      text-transform: uppercase;
      color: #3b82f6;
      letter-spacing: 0.05em;
    }

    .serial-no {
      font-size: 0.75rem;
      color: #94a3b8;
      font-family: monospace;
    }
  }

  .equip-title {
    font-size: 1.25rem;
    font-weight: 700;
    color: #1e293b;
    margin: 0 0 1rem 0;
    cursor: pointer;
    &:hover { color: #3b82f6; }
  }

  .equip-meta {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;

    .meta-item {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      color: #64748b;
      font-size: 0.875rem;

      i { font-size: 1rem; color: #94a3b8; }
    }
  }
}

.card-actions-bar {
  padding: 1rem 1.5rem;
  background: #f8fafc;
  display: flex;
  justify-content: space-around;
  border-top: 1px solid #f1f5f9;
}

.skeleton {
  .skeleton-img { height: 200px; background: #f1f5f9; }
  .skeleton-body { padding: 1.5rem; height: 150px; background: white; }
}

// Dialog Overrides
:deep(.modern-dialog) {
  border-radius: 24px;
  border: none;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
  
  .p-dialog-header {
    padding: 2rem 2rem 1rem 2rem;
    background: white;
    .p-dialog-title { font-weight: 800; color: #1e293b; font-size: 1.25rem; }
  }
  
  .p-dialog-content { padding: 0 2rem 2rem 2rem; background: white; }
  .p-dialog-footer { padding: 1.5rem 2rem; background: #f8fafc; border-top: 1px solid #f1f5f9; }
}

/* QR Dialog */
.qr-dialog-body {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 2rem;
  padding-top: 1rem;
}

.qr-card-preview {
  background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
  width: 100%;
  max-width: 320px;
  padding: 2rem;
  border-radius: 24px;
  color: white;
  text-align: center;
  box-shadow: 0 20px 40px rgba(15, 23, 42, 0.3);

  .qr-header {
    display: flex;
    flex-direction: column;
    margin-bottom: 1.5rem;
    .brand { font-weight: 800; font-size: 1.1rem; text-transform: uppercase; letter-spacing: 0.1em; color: #3b82f6; }
    .model { font-size: 0.9rem; opacity: 0.7; }
  }

  .qr-frame {
    background: white;
    padding: 1rem;
    border-radius: 16px;
    margin-bottom: 1.5rem;
    display: inline-block;
    img { width: 180px; height: 180px; display: block; }
    .qr-placeholder {
      width: 180px; height: 180px; display: flex; flex-direction: column; align-items: center; justify-content: center;
      color: #cbd5e1; i { font-size: 3rem; margin-bottom: 0.5rem; }
    }
  }

  .qr-footer .serial { font-family: monospace; font-size: 0.9rem; opacity: 0.6; letter-spacing: 0.2em; }
}

.qr-actions-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
  width: 100%;
  .action-card { border-radius: 12px; font-weight: 700; height: 3.5rem; }
}

/* Import Dialog */
.import-body {
  .info-alert {
    display: flex;
    align-items: center;
    gap: 1rem;
    background: #eff6ff;
    padding: 1rem;
    border-radius: 12px;
    color: #1e40af;
    margin-bottom: 2rem;
    i { font-size: 1.25rem; }
    p { margin: 0; font-size: 0.9rem; font-weight: 500; }
  }

  .import-steps {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
    margin-bottom: 2rem;

    .import-step {
      display: flex;
      align-items: center;
      gap: 1.25rem;
      padding: 1rem;
      background: #f8fafc;
      border-radius: 16px;
      border: 1px solid #f1f5f9;

      .step-num {
        width: 32px; height: 32px; background: #3b82f6; color: white; border-radius: 50%;
        display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 0.8rem;
      }

      .step-text {
        flex: 1;
        strong { display: block; color: #1e293b; font-size: 0.95rem; }
        p { margin: 0; color: #64748b; font-size: 0.85rem; }
      }
    }
  }
}

.modern-file-upload {
  :deep(.p-fileupload-choose) {
    width: 100%; border-radius: 12px; padding: 1rem; font-weight: 700; background: #3b82f6; border: none;
    &:hover { background: #2563eb; }
  }
}

/* Delete Dialog */
.delete-body {
  text-align: center;
  padding: 1rem 0;

  .warning-icon-wrapper {
    width: 80px; height: 80px; background: #fef2f2; color: #ef4444; border-radius: 50%;
    display: flex; align-items: center; justify-content: center; font-size: 2.5rem; margin: 0 auto 1.5rem auto;
  }

  h3 { font-size: 1.5rem; font-weight: 800; color: #1e293b; margin: 0 0 0.5rem 0; }
  p { color: #64748b; line-height: 1.6; margin-bottom: 2rem; }

  .item-preview-mini {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    background: #f8fafc;
    border-radius: 16px;
    text-align: left;

    img { width: 60px; height: 60px; border-radius: 10px; object-fit: cover; }
    .item-info {
      display: flex; flex-direction: column;
      .name { font-weight: 700; color: #1e293b; }
      .sn { font-size: 0.8rem; color: #94a3b8; font-family: monospace; }
    }
  }
}

.delete-actions {
  display: flex; gap: 1rem; width: 100%;
  :deep(.p-button) { flex: 1; border-radius: 12px; padding: 0.75rem; font-weight: 700; }
}

@media (max-width: 768px) {
  .page-header { flex-direction: column; align-items: stretch; gap: 1.5rem; }
  .filters-bar { flex-direction: column; padding: 1.5rem; gap: 1rem; }
  .dropdown-filters { width: 100%; justify-content: space-between; }
}
</style>
