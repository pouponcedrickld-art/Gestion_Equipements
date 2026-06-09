<template>
  <AgenceLayout>
    <div class="maintenance-calendar-view p-6">
      <!-- En-tête -->
      <div class="mb-8">
        <!-- Breadcrumb / Navigation -->
        <div class="mb-4">
          <router-link
            to="/maintenances"
            class="inline-flex items-center gap-2 text-gray-600 dark:text-gray-400 hover:text-blue-500 transition-colors"
          >
            <i class="pi pi-arrow-left"></i>
            <span>Retour aux Maintenances</span>
          </router-link>
        </div>
        
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2 flex items-center gap-3">
          <i class="pi pi-calendar text-blue-500"></i>
          Calendrier de Maintenance
        </h1>
        <p class="text-gray-600 dark:text-gray-400">
          Vue mensuelle des maintenances planifiées et en cours
        </p>
      </div>

      <!-- Contrôles de navigation -->
      <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <!-- Navigation mois -->
        <div class="flex items-center gap-3">
          <button
            class="px-4 py-2 rounded-lg bg-blue-500 hover:bg-blue-600 text-white transition-colors flex items-center gap-2"
            @click="navigateToPreviousMonth"
            aria-label="Mois précédent"
          >
            <i class="pi pi-chevron-left"></i>
          </button>

          <div class="text-xl font-bold text-gray-900 dark:text-white min-w-[200px] text-center">
            {{ currentMonthName }} {{ currentYear }}
          </div>

          <button
            class="px-4 py-2 rounded-lg bg-blue-500 hover:bg-blue-600 text-white transition-colors flex items-center gap-2"
            @click="navigateToNextMonth"
            aria-label="Mois suivant"
          >
            <i class="pi pi-chevron-right"></i>
          </button>

          <button
            class="px-4 py-2 rounded-lg bg-green-500 hover:bg-green-600 text-white transition-colors"
            @click="goToToday"
          >
            Aujourd'hui
          </button>
        </div>

        <!-- Filtres -->
        <div class="flex items-center gap-3">
          <!-- Filtre par type -->
          <select
            v-model="filters.type"
            class="px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white"
            @change="applyFilters"
          >
            <option value="all">Tous les types</option>
            <option value="préventif">Préventif</option>
            <option value="correctif">Correctif</option>
          </select>

          <!-- Filtre par statut -->
          <select
            v-model="filters.statut"
            class="px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white"
            @change="applyFilters"
          >
            <option value="all">Tous les statuts</option>
            <option value="planifiee">Planifié</option>
            <option value="en_cours">En cours</option>
            <option value="terminee">Terminé</option>
          </select>
        </div>
      </div>

      <!-- État de chargement -->
      <div v-if="loading" class="flex flex-col items-center justify-center py-20 bg-white dark:bg-gray-800 rounded-lg shadow">
        <i class="pi pi-spin pi-spinner text-4xl text-blue-500 mb-4"></i>
        <p class="text-gray-600 dark:text-gray-400">Chargement des maintenances...</p>
      </div>

      <!-- État d'erreur -->
      <div v-else-if="error" class="flex flex-col items-center justify-center py-20 bg-white dark:bg-gray-800 rounded-lg shadow">
        <i class="pi pi-exclamation-triangle text-4xl text-red-500 mb-4"></i>
        <p class="text-gray-600 dark:text-gray-400 mb-4">{{ error }}</p>
        <button
          class="px-6 py-2 rounded-lg bg-blue-500 hover:bg-blue-600 text-white transition-colors"
          @click="loadMonthData"
        >
          Réessayer
        </button>
      </div>

      <!-- Grille calendaire -->
      <CalendarGrid
        v-else
        :maintenances="filteredMaintenances"
        :current-month="currentMonth"
        @event-click="openDetails"
        @show-more="handleShowMore"
      />

      <!-- Modale de détails -->
      <MaintenanceDetailsModal
        :maintenance="selectedMaintenance"
        :is-open="isModalOpen"
        @close="closeDetails"
      />
    </div>
  </AgenceLayout>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import AgenceLayout from '@/layouts/AgenceLayout.vue'
import CalendarGrid from '@/components/maintenance/CalendarGrid.vue'
import MaintenanceDetailsModal from '@/components/maintenance/MaintenanceDetailsModal.vue'
import { useMaintenance } from '@/composables/useMaintenance'
import { getMonthName } from '@/utils/dateFormatter'
import { navigateMonth } from '@/utils/calendarUtils'
import { cleanupAnimations } from '@/utils/gsapAnimations'

// État local
const currentMonth = ref(new Date())
const selectedMaintenance = ref(null)
const isModalOpen = ref(false)
const filters = ref({
  type: 'all',
  statut: 'all'
})

// Composable pour maintenances
const {
  loading,
  error,
  loadMaintenances,
  loadMaintenanceDetails,
  filterByTypeAndStatus,
  clearError
} = useMaintenance()

// Computed
const currentYear = computed(() => currentMonth.value.getFullYear())
const currentMonthName = computed(() => getMonthName(currentMonth.value.getMonth()))

const filteredMaintenances = computed(() => {
  return filterByTypeAndStatus(filters.value.type, filters.value.statut)
})

// Méthodes
async function loadMonthData() {
  try {
    clearError()
    await loadMaintenances(currentMonth.value)
  } catch (err) {
    console.error('Failed to load month data:', err)
  }
}

function navigateToPreviousMonth() {
  currentMonth.value = navigateMonth(currentMonth.value, -1)
  loadMonthData()
}

function navigateToNextMonth() {
  currentMonth.value = navigateMonth(currentMonth.value, 1)
  loadMonthData()
}

function goToToday() {
  currentMonth.value = new Date()
  loadMonthData()
}

async function openDetails(maintenance) {
  try {
    // Charger les détails complets avec relations
    await loadMaintenanceDetails(maintenance.id)
    selectedMaintenance.value = maintenance
    isModalOpen.value = true
  } catch (err) {
    console.error('Failed to load maintenance details:', err)
    // Fallback : utiliser les données déjà disponibles
    selectedMaintenance.value = maintenance
    isModalOpen.value = true
  }
}

function closeDetails() {
  isModalOpen.value = false
  setTimeout(() => {
    selectedMaintenance.value = null
  }, 300) // Attendre la fin de l'animation
}

function handleShowMore(day) {
  // Pour l'instant, on pourrait ouvrir une liste ou modal avec toutes les maintenances du jour
  console.log('Show more maintenances for day:', day)
  // TODO: Implémenter une vue liste pour les jours avec beaucoup d'événements
}

function applyFilters() {
  // Les filtres sont réactifs, donc pas besoin de recharger
  // La computed property filteredMaintenances se met à jour automatiquement
}

// Lifecycle
onMounted(() => {
  loadMonthData()
})

onUnmounted(() => {
  cleanupAnimations()
})
</script>

<style scoped>
.maintenance-calendar-view {
  min-height: 100vh;
}
</style>
