<template>
  <DirectionLayout>
    <div class="categorie-detail-view" v-if="!loading && categorie">
      <!-- En-tête de la page -->
      <div class="page-header animate-in">
        <div class="title-container">
          <div class="flex align-items-center gap-2">
            <h1>{{ categorie.nom }}</h1>
            <Tag :value="getStatusLabel(categorie.statut)" :severity="getStatusSeverity(categorie.statut)" />
          </div>
          <p class="subtitle">Code: {{ categorie.code || categorie.slug }} | Créée le {{ formatDate(categorie.created_at) }}</p>
        </div>
        <div class="actions">
          <Button icon="pi pi-arrow-left" label="Retour" class="p-button-text p-button-secondary mr-2" @click="$router.push('/categories')" />
          <Button icon="pi pi-pencil" label="Modifier" class="p-button-primary" @click="editCategorie" />
        </div>
      </div>

      <div class="grid mt-2 animate-in">
        <!-- Colonne de gauche: Infos générales -->
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
                  <span class="label">Catégorie parente</span>
                  <span class="value">{{ categorie.parent?.nom || 'Aucune (Catégorie racine)' }}</span>
                </div>
                <div class="detail-item">
                  <span class="label">Fréquence maintenance</span>
                  <span class="value">{{ categorie.frequence_maintenance_mois || 'Non définie' }} mois</span>
                </div>
                <div class="detail-item">
                  <span class="label">Durée de vie estimée</span>
                  <span class="value">{{ categorie.duree_vie_mois || 'Non définie' }} mois</span>
                </div>
                <div class="detail-item">
                  <span class="label">Total équipements</span>
                  <span class="value">{{ categorie.equipements?.length || 0 }}</span>
                </div>
              </div>
              
              <div class="mt-4" v-if="categorie.description">
                <h4 class="tab-section-title">Description</h4>
                <p class="text-sm text-gray-600 line-height-3">{{ categorie.description }}</p>
              </div>
            </template>
          </Card>

          <Card class="stats-card" v-if="categorie.attributs_personnalises">
            <template #title>
              <div class="flex align-items-center gap-2 text-primary">
                <i class="pi pi-list"></i>
                <span class="text-lg">Champs personnalisés</span>
              </div>
            </template>
            <template #content>
              <div class="flex flex-wrap gap-2">
                <Chip v-for="(attr, index) in categorie.attributs_personnalises" :key="index" 
                  :label="attr" icon="pi pi-tag" class="text-xs" />
              </div>
            </template>
          </Card>
        </div>

        <!-- Colonne de droite: Liste des équipements -->
        <div class="col-12 lg:col-8">
          <Card class="equipements-card">
            <template #title>
              <div class="flex align-items-center justify-content-between">
                <div class="flex align-items-center gap-2 text-primary">
                  <i class="pi pi-desktop"></i>
                  <span class="text-lg">Équipements dans cette catégorie</span>
                </div>
                <Badge :value="categorie.equipements?.length || 0" severity="info" />
              </div>
            </template>
            <template #content>
              <DataTable :value="categorie.equipements" responsiveLayout="scroll" class="p-datatable-sm" 
                :rows="10" paginator v-if="categorie.equipements?.length">
                <Column field="code_inventaire" header="Code" sortable>
                  <template #body="{ data }">
                    <code class="text-xs">{{ data.code_inventaire }}</code>
                  </template>
                </Column>
                <Column field="nom" header="Désignation" sortable>
                  <template #body="{ data }">
                    <div class="font-bold text-sm">{{ data.nom }}</div>
                    <small class="text-gray-500">{{ data.marque }} {{ data.modele }}</small>
                  </template>
                </Column>
                <Column field="etat" header="État">
                  <template #body="{ data }">
                    <Tag :value="getEtatLabel(data.etat)" :severity="getEtatSeverity(data.etat)" class="text-xs" />
                  </template>
                </Column>
                <Column header="Actions" class="text-right">
                  <template #body="{ data }">
                    <Button icon="pi pi-eye" class="p-button-text p-button-rounded p-button-sm" 
                      @click="$router.push(`/equipements/${data.id}`)" />
                  </template>
                </Column>
              </DataTable>
              <div v-else class="empty-tab">
                <i class="pi pi-box"></i>
                <p>Aucun équipement dans cette catégorie</p>
              </div>
            </template>
          </Card>

          <Card class="mt-3" v-if="categorie.children?.length">
            <template #title>
              <div class="flex align-items-center gap-2 text-primary">
                <i class="pi pi-sitemap"></i>
                <span class="text-lg">Sous-catégories</span>
              </div>
            </template>
            <template #content>
              <div class="grid">
                <div v-for="child in categorie.children" :key="child.id" class="col-12 md:col-6 lg:col-4">
                  <div class="child-cat-card" @click="$router.push(`/categories/${child.id}`)">
                    <i class="pi pi-tag text-blue-500 mr-2"></i>
                    <span class="font-bold">{{ child.nom }}</span>
                  </div>
                </div>
              </div>
            </template>
          </Card>
        </div>
      </div>
    </div>

    <!-- État de chargement -->
    <div v-else-if="loading" class="loading-state">
      <i class="pi pi-spin pi-spinner" style="font-size: 2rem"></i>
      <p>Chargement des détails de la catégorie...</p>
    </div>
  </DirectionLayout>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import DirectionLayout from '@/layouts/DirectionLayout.vue'
import { useCategorieStore } from '@/stores/categorieStore'

// PrimeVue Components
import Card from 'primevue/card'
import Button from 'primevue/button'
import Tag from 'primevue/tag'
import Badge from 'primevue/badge'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Chip from 'primevue/chip'

const route = useRoute()
const router = useRouter()
const categorieStore = useCategorieStore()

const categorie = ref(null)
const loading = ref(true)

const loadCategorie = async () => {
  loading.value = true
  try {
    const data = await categorieStore.fetchCategorie(route.params.id)
    categorie.value = data
  } catch (error) {
    console.error('Erreur lors du chargement de la catégorie:', error)
  } finally {
    loading.value = false
  }
}

onMounted(loadCategorie)

watch(() => route.params.id, (newId) => {
  if (newId) loadCategorie()
})

const editCategorie = () => {
  router.push({
    name: 'Categories',
    query: { edit: categorie.value.id }
  })
}

// Helpers
const formatDate = (date) => {
  if (!date) return 'N/A'
  return new Date(date).toLocaleDateString('fr-FR')
}

const getStatusLabel = (status) => {
  const map = { active: 'Active', archive: 'Archivée' }
  return map[status] || status
}

const getStatusSeverity = (status) => {
  return status === 'active' ? 'success' : 'secondary'
}

const getEtatLabel = (etat) => {
  const labels = {
    nouveau: 'Nouveau',
    actif: 'Actif',
    en_maintenance: 'En maintenance',
    hors_service: 'Hors service',
    archive: 'Archivé'
  }
  return labels[etat] || etat
}

const getEtatSeverity = (etat) => {
  const severities = {
    nouveau: 'info',
    actif: 'success',
    en_maintenance: 'warning',
    hors_service: 'danger',
    archive: 'secondary'
  }
  return severities[etat] || 'info'
}
</script>

<style scoped lang="scss">
.categorie-detail-view {
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
  color: #1e293b;
  font-size: 1.4rem;
  font-weight: 800;
}

.subtitle {
  color: #64748b;
  margin: 0;
  font-size: 0.85rem;
}

.info-card, .stats-card, .equipements-card {
  border-radius: 12px;
  box-shadow: 0 2px 12px rgba(0,0,0,0.05);
  border: none;
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

.tab-section-title {
  color: #3b82f6;
  margin: 1rem 0 0.5rem 0;
  font-size: 0.85rem;
  font-weight: 700;
  text-transform: uppercase;
}

.empty-tab {
  text-align: center;
  padding: 3rem 1rem;
  color: #cbd5e1;
  i { font-size: 3rem; margin-bottom: 1rem; }
  p { color: #94a3b8; }
}

.child-cat-card {
  background: #f8fafc;
  padding: 0.75rem 1rem;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s;
  border: 1px solid #f1f5f9;
  font-size: 0.9rem;
  display: flex;
  align-items: center;

  &:hover {
    background: #eff6ff;
    border-color: #3b82f6;
    transform: translateX(3px);
  }
}

.loading-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 60vh;
  color: #94a3b8;
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
