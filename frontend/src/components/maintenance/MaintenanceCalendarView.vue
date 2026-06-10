<template>
  <MainLayout>
    <div class="p-6 max-w-7xl mx-auto">
      <div class="mb-6 flex items-center justify-between flex-wrap gap-4">
        <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Calendrier de Maintenance</h1>
        
        <div class="flex items-center gap-4">
          <div class="flex items-center gap-2">
            <button @click="navigateMonth(-1)" class="p-2 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg transition-colors">
              <i class="pi pi-chevron-left"></i>
            </button>
            <h2 class="text-lg font-medium min-w-32 text-center text-slate-800 dark:text-white">{{ currentMonthName }}</h2>
            <button @click="navigateMonth(1)" class="p-2 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg transition-colors">
              <i class="pi pi-chevron-right"></i>
            </button>
            <button @click="goToToday" class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg transition-colors">
              Aujourd'hui
            </button>
          </div>
          
          <div class="flex items-center gap-3">
            <select v-model="filters.type_maintenance" @change="applyFilters" class="bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded-lg px-3 py-2 text-slate-800 dark:text-white">
              <option value="">Tous les types</option>
              <option value="préventif">Préventif</option>
              <option value="correctif">Correctif</option>
            </select>
            <select v-model="filters.statut" @change="applyFilters" class="bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded-lg px-3 py-2 text-slate-800 dark:text-white">
              <option value="">Tous les statuts</option>
              <option value="planifiee">Planifié</option>
              <option value="en_cours">En cours</option>
              <option value="terminee">Terminé</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="flex items-center justify-center py-20">
        <div class="animate-spin rounded-full h-12 w-12 border-4 border-blue-500 border-t-transparent"></div>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="bg-red-50 dark:bg-red-900/30 border border-red-500/30 rounded-lg p-6">
        <div class="flex items-center justify-between">
          <div>
            <h3 class="text-red-600 dark:text-red-400 font-medium">Erreur</h3>
            <p class="text-red-500 dark:text-red-300 mt-1">{{ error }}</p>
          </div>
          <button @click="loadMonthData" class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg transition-colors">
            Réessayer
          </button>
        </div>
      </div>

      <!-- Calendar -->
      <div v-else>
        <div class="grid grid-cols-7 gap-1 mb-2">
          <div
            v-for="day in daysOfWeek"
            :key="day"
            class="text-center text-sm font-medium text-slate-500 dark:text-slate-400 py-3"
          >
            {{ day }}
          </div>
        </div>
        <div class="space-y-1">
          <div v-for="(week, weekIdx) in monthGrid" :key="weekIdx" class="grid grid-cols-7 gap-1">
            <div
              v-for="day in week"
              :key="day.date.toISOString()"
              class="calendar-day p-2 min-h-24 border border-slate-200 dark:border-slate-700 rounded-lg transition-all bg-white dark:bg-slate-800"
              :class="{
                'bg-slate-50 dark:bg-slate-800/50': !day.isCurrentMonth,
                'bg-blue-50 dark:bg-blue-900/20 ring-1 ring-blue-400': day.isToday
              }"
            >
              <div class="font-medium text-sm mb-1" :class="{
                'text-blue-600 dark:text-blue-400': day.isToday,
                'text-slate-400': !day.isCurrentMonth,
                'text-slate-800 dark:text-white': day.isCurrentMonth && !day.isToday
              }">
                {{ day.day }}
              </div>
              <div class="space-y-1">
                <MaintenanceEventCard
                  v-for="(m, idx) in day.maintenances.slice(0, 3)"
                  :key="m.id"
                  :maintenance="m"
                  compact
                  @click="openDetails(m)"
                />
                <div v-if="day.maintenances.length > 3" class="text-xs text-slate-500 dark:text-slate-400 text-center mt-1">
                  +{{ day.maintenances.length - 3 }} autres
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Details Modal -->
      <MaintenanceDetailsModal
        :maintenance="selectedMaintenance"
        :is-open="isModalOpen"
        @close="isModalOpen = false; selectedMaintenance = null"
      />
    </div>
  </MainLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useMaintenance } from '@/composables/useMaintenance.js'
import { generateMonthGrid } from '@/utils/calendarUtils.js'
import { formatDateLong, formatTime } from '@/utils/dateFormatter.js'
import MainLayout from '@/layouts/MainLayout.vue'
import MaintenanceEventCard from '@/components/maintenance/MaintenanceEventCard.vue'
import MaintenanceDetailsModal from '@/components/maintenance/MaintenanceDetailsModal.vue'

const { maintenances, loading, error, loadMaintenances } = useMaintenance()

// State
const currentMonth = ref(new Date())
const selectedMaintenance = ref(null)
const isModalOpen = ref(false)
const filters = ref({
  type_maintenance: '',
  statut: ''
})
const daysOfWeek = ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim']

// Computed
const currentMonthName = computed(() => {
  return currentMonth.value.toLocaleDateString('fr-FR', {
    month: 'long',
    year: 'numeric'
  })
})

const monthGrid = computed(() => {
  return generateMonthGrid(
    currentMonth.value.getFullYear(),
    currentMonth.value.getMonth(),
    maintenances.value
  )
})

// Methods
function navigateMonth(direction) {
  const newDate = new Date(currentMonth.value)
  newDate.setMonth(newDate.getMonth() + direction)
  currentMonth.value = newDate
  loadMonthData()
}

function goToToday() {
  currentMonth.value = new Date()
  loadMonthData()
}

function loadMonthData() {
  loadMaintenances(currentMonth.value, filters.value)
}

function applyFilters() {
  loadMonthData()
}

function openDetails(maintenance) {
  selectedMaintenance.value = maintenance
  isModalOpen.value = true
}

// Lifecycle
onMounted(() => {
  loadMonthData()
})
</script>
