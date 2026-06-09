<template>
  <AgenceLayout>
    <div class="maintenance-calendar-view p-6 bg-slate-950 min-h-screen">
      <!-- En-tête -->
      <div class="mb-8">
        <!-- Breadcrumb / Navigation -->
        <div class="mb-4">
          <router-link
            to="/maintenances"
            class="inline-flex items-center gap-2 text-gray-400 hover:text-pink-400 transition-colors"
          >
            <i class="pi pi-arrow-left"></i>
            <span>Retour aux Maintenances</span>
          </router-link>
        </div>
        
        <h1 class="text-3xl font-bold bg-gradient-to-r from-pink-400 to-purple-400 bg-clip-text text-transparent mb-2 flex items-center gap-3">
          <i class="pi pi-calendar text-pink-500"></i>
          Calendrier de Maintenance
        </h1>
        <p class="text-gray-400">
          Vue mensuelle des maintenances planifiées et en cours
        </p>
      </div>

      <!-- Contrôles de navigation -->
      <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <!-- Navigation mois -->
        <div class="flex items-center gap-3">
          <button
            class="px-4 py-2 rounded-lg bg-slate-800 hover:bg-slate-700 border border-pink-500/30 text-pink-400 transition-colors flex items-center gap-2"
            @click="navigateToPreviousMonth"
            aria-label="Mois précédent"
          >
            <i class="pi pi-chevron-left"></i>
          </button>

          <div class="text-xl font-bold text-gray-200 min-w-[200px] text-center">
            {{ currentMonthName }} {{ currentYear }}
          </div>

          <button
            class="px-4 py-2 rounded-lg bg-slate-800 hover:bg-slate-700 border border-pink-500/30 text-pink-400 transition-colors flex items-center gap-2"
            @click="navigateToNextMonth"
            aria-label="Mois suivant"
          >
            <i class="pi pi-chevron-right"></i>
          </button>

          <button
            class="px-4 py-2 rounded-lg bg-gradient-to-r from-pink-500 to-purple-500 hover:from-pink-600 hover:to-purple-600 text-white transition-colors"
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
            class="px-4 py-2 rounded-lg border border-gray-700 bg-slate-800/50 text-gray-200 focus:border-pink-500 focus:ring-2 focus:ring-pink-500/20 outline-none"
            @change="applyFilters"
          >
            <option value="all">Tous les types</option>
            <option value="préventif">Préventif</option>
            <option value="correctif">Correctif</option>
          </select>

          <!-- Filtre par statut -->
          <select
            v-model="filters.statut"
            class="px-4 py-2 rounded-lg border border-gray-700 bg-slate-800/50 text-gray-200 focus:border-pink-500 focus:ring-2 focus:ring-pink-500/20 outline-none"
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
      <div v-if="loading" class="flex flex-col items-center justify-center py-20 bg-slate-900/50 rounded-lg shadow border border-gray-800">
        <i class="pi pi-spin pi-spinner text-4xl text-pink-500 mb-4"></i>
        <p class="text-gray-400">Chargement des maintenances...</p>
      </div>

      <!-- État d'erreur -->
      <div v-else-if="error" class="flex flex-col items-center justify-center py-20 bg-slate-900/50 rounded-lg shadow border border-gray-800">
        <i class="pi pi-exclamation-triangle text-4xl text-red-500 mb-4"></i>
        <p class="text-gray-400 mb-4">{{ error }}</p>
        <button
          class="px-6 py-2 rounded-lg bg-gradient-to-r from-pink-500 to-purple-500 hover:from-pink-600 hover:to-purple-600 text-white transition-colors"
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
        @day-click="handleDayClick"
      />

      <!-- Modale de détails -->
      <MaintenanceDetailsModal
        :maintenance="selectedMaintenance"
        :is-open="isModalOpen"
        @close="closeDetails"
      />

      <!-- Modal Planifier Maintenance -->
      <PlanMaintenanceModal
        :is-open="isPlanMaintenanceModalOpen"
        :selected-date="selectedDate"
        :equipements="equipementsDisponibles"
        @close="isPlanMaintenanceModalOpen = false"
        @submit="handleMaintenanceSubmit"
      />

      <!-- Modal Planifier Remise -->
      <PlanRemiseModal
        :is-open="isPlanRemiseModalOpen"
        :selected-date="selectedDate"
        :maintenances="filteredMaintenances"
        @close="isPlanRemiseModalOpen = false"
        @submit="handleRemiseSubmit"
      />

      <!-- Liste Événements du Jour -->
      <DayEventList
        :is-open="isDayEventListOpen"
        :date="selectedDate"
        :events="selectedDayEvents"
        @close="isDayEventListOpen = false"
        @event-click="openDetails"
      />

      <!-- Menu Contextuel Jour -->
      <DayContextMenu
        :is-open="isDayContextMenuOpen"
        :selected-date="selectedDate"
        :position="contextMenuPosition"
        :events-count="selectedDayData?.maintenances?.length || 0"
        @close="isDayContextMenuOpen = false"
        @plan-maintenance="openPlanMaintenanceModal"
        @plan-remise="openPlanRemiseModal"
        @view-all="viewAllDayEvents"
      />
    </div>
  </AgenceLayout>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import AgenceLayout from '@/layouts/AgenceLayout.vue'
import CalendarGrid from '@/components/maintenance/CalendarGrid.vue'
import MaintenanceDetailsModal from '@/components/maintenance/MaintenanceDetailsModal.vue'
import PlanMaintenanceModal from '@/components/maintenance/PlanMaintenanceModal.vue'
import PlanRemiseModal from '@/components/maintenance/PlanRemiseModal.vue'
import DayEventList from '@/components/maintenance/DayEventList.vue'
import DayContextMenu from '@/components/maintenance/DayContextMenu.vue'
import { useMaintenance } from '@/composables/useMaintenance'
import { useEquipementStore } from '@/stores/equipementStore'
import { getMonthName } from '@/utils/dateFormatter'
import { navigateMonth } from '@/utils/calendarUtils'
import { cleanupAnimations } from '@/utils/gsapAnimations'
import { useToast } from 'primevue/usetoast'

const toast = useToast()

// État local
const currentMonth = ref(new Date())
const selectedMaintenance = ref(null)
const isModalOpen = ref(false)
const filters = ref({
  type: 'all',
  statut: 'all'
})

// Nouveaux états pour les modales et menus
const isPlanMaintenanceModalOpen = ref(false)
const isPlanRemiseModalOpen = ref(false)
const isDayEventListOpen = ref(false)
const isDayContextMenuOpen = ref(false)

const selectedDate = ref(null)
const contextMenuPosition = ref(null)
const selectedDayEvents = ref([])
const selectedDayData = ref(null)

// Composable pour maintenances
const {
  loading,
  error,
  loadMaintenances,
  loadMaintenanceDetails,
  filterByTypeAndStatus,
  clearError,
  createMaintenance
} = useMaintenance()

// Store équipements
const equipementStore = useEquipementStore()

// Computed
const currentYear = computed(() => currentMonth.value.getFullYear())
const currentMonthName = computed(() => getMonthName(currentMonth.value.getMonth()))

const filteredMaintenances = computed(() => {
  return filterByTypeAndStatus(filters.value.type, filters.value.statut)
})

// Équipements disponibles pour les modales
const equipementsDisponibles = computed(() => equipementStore.equipementsDisponibles)

// Méthodes
async function loadMonthData() {
  try {
    clearError()
    await loadMaintenances(currentMonth.value)
    
    // Charger les équipements si pas encore chargés
    if (equipementStore.equipements.length === 0) {
      await equipementStore.fetchEquipements()
    }
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
    await loadMaintenanceDetails(maintenance.id)
    selectedMaintenance.value = maintenance
    isModalOpen.value = true
  } catch (err) {
    console.error('Failed to load maintenance details:', err)
    selectedMaintenance.value = maintenance
    isModalOpen.value = true
  }
}

function closeDetails() {
  isModalOpen.value = false
  setTimeout(() => {
    selectedMaintenance.value = null
  }, 300)
}

// Handler clic sur un jour
function handleDayClick(data) {
  selectedDate.value = data.date
  selectedDayData.value = data
  contextMenuPosition.value = data.position
  isDayContextMenuOpen.value = true
}

// Handler "show more" (afficher la liste complète du jour)
function handleShowMore(day) {
  selectedDate.value = day.date
  selectedDayEvents.value = day.maintenances
  isDayEventListOpen.value = true
}

// Ouvrir modal planifier maintenance
function openPlanMaintenanceModal() {
  isDayContextMenuOpen.value = false
  isPlanMaintenanceModalOpen.value = true
}

// Ouvrir modal planifier remise
function openPlanRemiseModal() {
  isDayContextMenuOpen.value = false
  isPlanRemiseModalOpen.value = true
}

// Voir tous les événements du jour
function viewAllDayEvents() {
  if (selectedDayData.value) {
    handleShowMore({
      date: selectedDayData.value.date,
      maintenances: selectedDayData.value.maintenances
    })
  }
}

// Soumettre nouvelle maintenance
async function handleMaintenanceSubmit(maintenances) {
  try {
    for (const maintenanceData of maintenances) {
      await createMaintenance(maintenanceData)
    }
    
    await loadMonthData()
    
    toast.add({
      severity: 'success',
      summary: 'Succès',
      detail: `${maintenances.length} maintenance(s) créée(s) avec succès`,
      life: 3000
    })
  } catch (error) {
    toast.add({
      severity: 'error',
      summary: 'Erreur',
      detail: 'Erreur lors de la création des maintenances',
      life: 3000
    })
    console.error('Error creating maintenances:', error)
  }
}

// Soumettre remise
async function handleRemiseSubmit(data) {
  try {
    // TODO: Implémenter l'API pour planifier la remise
    console.log('Remise data:', data)
    
    await loadMonthData()
    
    toast.add({
      severity: 'success',
      summary: 'Succès',
      detail: 'Remise planifiée avec succès',
      life: 3000
    })
  } catch (error) {
    toast.add({
      severity: 'error',
      summary: 'Erreur',
      detail: 'Erreur lors de la planification de la remise',
      life: 3000
    })
    console.error('Error planning remise:', error)
  }
}

function applyFilters() {
  // Les filtres sont réactifs
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
