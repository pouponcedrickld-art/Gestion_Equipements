<template>
  <AgenceLayout>
    <div class="mes-materiels-page">
      <!-- En-tête -->
      <div class="page-header animate-in">
        <div class="title-with-icon">
          <div class="icon-wrapper">
            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="svg-icon">
              <path d="M9 17H5C3.89543 17 3 16.1046 3 15V5C3 3.89543 3.89543 3 5 3H19C20.1046 3 21 3.89543 21 5V15C21 16.1046 20.1046 17 19 17H15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              <path d="M7 21H17" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              <path d="M12 17V21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </div>
          <div>
            <h1>Mes Matériels</h1>
            <p class="subtitle">Équipements affectés à vous</p>
          </div>
        </div>
      </div>

      <!-- Statistiques -->
      <div class="stats-container animate-in">
        <div class="stat-glass-card total">
          <div class="stat-icon-box">
            <i class="pi pi-box"></i>
          </div>
          <div class="stat-details">
            <span class="value">{{ mesAffectations.length }}</span>
            <span class="label">Total</span>
          </div>
        </div>
      </div>

      <!-- Liste des matériels -->
      <div class="equipements-container" v-if="!loading && mesAffectations.length > 0">
        <div class="equipements-grid">
          <div v-for="affectation in mesAffectations" :key="affectation.id" class="equipement-card animate-card">
            <div class="card-image">
              <img v-if="affectation.equipement?.photo" :src="`${apiBaseUrl}/storage/${affectation.equipement.photo}`" alt="Photo" />
              <div v-else class="image-placeholder">
                <i class="pi pi-desktop"></i>
              </div>
              <div class="card-status" :class="`bg-${getStatutColor(affectation.equipement?.etat)}`">
                {{ getStatutLabel(affectation.equipement?.etat) }}
              </div>
            </div>
            <div class="card-content">
              <div class="card-category">{{ affectation.equipement?.categorie?.nom }}</div>
              <h3 class="card-title">{{ affectation.equipement?.nom }}</h3>
              <p class="card-subtitle">{{ affectation.equipement?.marque }} {{ affectation.equipement?.modele }}</p>
              <div class="card-info">
                <div class="info-row">
                  <i class="pi pi-barcode"></i>
                  <span>{{ affectation.equipement?.code_inventaire || 'N/A' }}</span>
                </div>
                <div class="info-row" v-if="affectation.equipement?.numero_serie">
                  <i class="pi pi-tag"></i>
                  <span>{{ affectation.equipement.numero_serie }}</span>
                </div>
                <div class="info-row">
                  <i class="pi pi-calendar"></i>
                  <span>Depuis {{ formatDate(affectation.date_affectation) }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- État vide -->
      <div v-else-if="!loading && mesAffectations.length === 0" class="empty-state-grid">
        <i class="pi pi-box"></i>
        <p>Aucun matériel affecté pour le moment</p>
      </div>

      <!-- Skeleton loading -->
      <div class="equipements-grid" v-else>
        <div v-for="n in 4" :key="n" class="equipement-card skeleton">
          <div class="card-image skeleton-animate"></div>
          <div class="card-content">
            <div class="skeleton-line w-40"></div>
            <div class="skeleton-line w-full h-2"></div>
            <div class="skeleton-line w-60"></div>
          </div>
        </div>
      </div>
    </div>
  </AgenceLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAffectationStore } from '@/stores/affectationStore'
import AgenceLayout from '@/layouts/AgenceLayout.vue'
import gsap from 'gsap'

const affectationStore = useAffectationStore()
const apiBaseUrl = import.meta.env.VITE_API_URL?.split('/api')[0] || 'http://localhost:8000'

const loading = computed(() => affectationStore.loading)
const mesAffectations = computed(() => affectationStore.mesAffectations)

const statutOptions = [
  { label: 'Nouveau', value: 'nouveau', color: 'blue-500' },
  { label: 'Actif', value: 'actif', color: 'green-500' },
  { label: 'En maintenance', value: 'en_maintenance', color: 'orange-500' },
  { label: 'Hors service', value: 'hors_service', color: 'red-500' },
  { label: 'Archivé', value: 'archive', color: 'gray-500' }
]

const getStatutLabel = (etat) => {
  const found = statutOptions.find(opt => opt.value === etat)
  return found ? found.label : etat || 'N/A'
}

const getStatutColor = (etat) => {
  const found = statutOptions.find(opt => opt.value === etat)
  return found ? found.color : 'gray-500'
}

const formatDate = (dateStr) => {
  if (!dateStr) return 'N/A'
  const date = new Date(dateStr)
  return date.toLocaleDateString('fr-FR')
}

onMounted(async () => {
  await affectationStore.fetchMesAffectations()
  
  gsap.from('.animate-in', { opacity: 0, y: 30, duration: 0.8, stagger: 0.2, ease: 'power3.out' })
  gsap.from('.animate-card', { opacity: 0, scale: 0.9, duration: 0.5, stagger: 0.05, delay: 0.4 })
})
</script>

<style scoped lang="scss">
.mes-materiels-page { padding: 1rem; }
.title-with-icon {
  display: flex; align-items: center; gap: 1rem;
  .icon-wrapper {
    width: 40px; height: 40px;
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    border-radius: 12px; display: flex; align-items: center; justify-content: center;
    color: white; box-shadow: 0 4px 8px rgba(37, 99, 235, 0.2);
    .svg-icon { width: 20px; height: 20px; }
  }
  h1 { font-size: 1.5rem; font-weight: 800; color: #1e293b; margin: 0; }
  .subtitle { color: #64748b; font-size: 0.85rem; }
}
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; }

.stats-container { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem; margin-bottom: 1.5rem; }
.stat-glass-card {
  background: white; padding: 0.75rem 1rem; border-radius: 16px; box-shadow: 0 2px 8px rgba(0,0,0,0.03);
  display: flex; align-items: center; gap: 0.75rem;
  .stat-icon-box { font-size: 1.2rem; color: #3b82f6; }
  .value { display: block; font-size: 1.2rem; font-weight: 800; }
  .label { color: #64748b; font-size: 0.75rem; }
}

.equipements-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 1rem;
  margin-top: 0.5rem;
}

.equipement-card {
  background: white;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 2px 12px rgba(0,0,0,0.05);
  transition: transform 0.2s ease;
  position: relative;
  display: flex;
  flex-direction: column;

  &:hover {
    transform: translateY(-3px);
  }
}

.card-image {
  height: 140px;
  position: relative;
  background: #f8fafc;
  
  img { width: 100%; height: 100%; object-fit: contain; }
  
  .image-placeholder {
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 3rem;
    color: #cbd5e1;
  }

  .card-status {
    position: absolute;
    bottom: 0.5rem;
    right: 0.5rem;
    padding: 0.25rem 0.75rem;
    border-radius: 6px;
    color: white;
    font-size: 0.7rem;
    font-weight: 600;
    &.bg-blue-500 { background: #3b82f6; }
    &.bg-green-500 { background: #22c55e; }
    &.bg-orange-500 { background: #f97316; }
    &.bg-red-500 { background: #ef4444; }
    &.bg-gray-500 { background: #64748b; }
  }
}

.card-content {
  padding: 0.75rem 1rem;
  flex: 1;

  .card-category {
    font-size: 0.6rem;
    color: #3b82f6;
    font-weight: 700;
    text-transform: uppercase;
    margin-bottom: 0.25rem;
  }

  .card-title {
    font-size: 1rem;
    font-weight: 700;
    color: #1e293b;
    margin: 0 0 0.15rem 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  .card-subtitle {
    font-size: 0.8rem;
    color: #64748b;
    margin-bottom: 0.75rem;
  }

  .card-info {
    display: flex;
    flex-direction: column;
    gap: 0.35rem;

    .info-row {
      display: flex;
      align-items: center;
      gap: 0.4rem;
      font-size: 0.75rem;
      color: #475569;
      
      i { color: #94a3b8; width: 14px; font-size: 0.75rem; }
    }
  }
}

.card-footer {
  padding: 0.5rem 1rem;
  border-top: 1px solid #f1f5f9;
  display: flex;
  justify-content: flex-end;
  gap: 0.25rem;
}

/* Skeleton */
.skeleton-animate {
  background: linear-gradient(90deg, #f1f5f9 25%, #e2e8f0 50%, #f1f5f9 75%);
  background-size: 200% 100%;
  animation: loading 1.5s infinite;
}

.skeleton-line {
  height: 1rem;
  background: #f1f5f9;
  margin-bottom: 0.5rem;
  border-radius: 4px;
  &.w-40 { width: 40%; }
  &.w-60 { width: 60%; }
  &.w-full { width: 100%; }
  &.h-2 { height: 1.5rem; }
}

@keyframes loading {
  0% { background-position: 200% 0; }
  100% { background-position: -200% 0; }
}

.empty-state-grid {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 4rem 2rem;
  background: white;
  border-radius: 16px;
  box-shadow: 0 4px 20px rgba(0,0,0,0.05);
  text-align: center;
  
  i { font-size: 4rem; color: #cbd5e1; margin-bottom: 1rem; }
  p { font-size: 1.1rem; color: #64748b; margin-bottom: 1.5rem; }
}
</style>