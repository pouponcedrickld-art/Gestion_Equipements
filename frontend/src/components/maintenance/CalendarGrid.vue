<template>
  <div ref="gridRef" class="calendar-grid">
    <!-- En-tête des jours de la semaine -->
    <div class="grid grid-cols-7 gap-0 mb-2">
      <div
        v-for="dayName in daysOfWeek"
        :key="dayName"
        class="text-center text-sm font-semibold text-gray-700 dark:text-gray-300 py-2"
      >
        {{ dayName }}
      </div>
    </div>

    <!-- Grille du calendrier -->
    <div class="grid grid-cols-7 gap-0 border-t border-l border-gray-200 dark:border-gray-700">
      <template v-for="(week, weekIndex) in monthGrid.weeks" :key="`week-${weekIndex}`">
        <CalendarDay
          v-for="(day, dayIndex) in week"
          :key="`day-${weekIndex}-${dayIndex}`"
          :day="day"
          @event-click="$emit('event-click', $event)"
          @show-more="$emit('show-more', $event)"
          @day-click="$emit('day-click', $event)"
        />
      </template>
    </div>

    <!-- Message si aucune donnée -->
    <div
      v-if="!maintenances || maintenances.length === 0"
      class="mt-8 text-center text-gray-500 dark:text-gray-400"
    >
      <i class="pi pi-calendar text-3xl mb-2 opacity-50"></i>
      <p>Aucune maintenance prévue ce mois</p>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import CalendarDay from './CalendarDay.vue'
import { generateMonthGrid, getDaysOfWeek } from '@/utils/calendarUtils'
import { animateCalendarEntry } from '@/utils/gsapAnimations'

const props = defineProps({
  maintenances: {
    type: Array,
    default: () => []
  },
  currentMonth: {
    type: Date,
    required: true
  }
})

defineEmits(['event-click', 'show-more', 'day-click'])

const gridRef = ref(null)

// Jours de la semaine
const daysOfWeek = computed(() => getDaysOfWeek())

// Génération de la grille mensuelle
const monthGrid = computed(() => {
  const year = props.currentMonth.getFullYear()
  const month = props.currentMonth.getMonth()
  return generateMonthGrid(year, month, props.maintenances)
})

// Animation au montage
onMounted(() => {
  if (gridRef.value) {
    animateCalendarEntry(gridRef.value)
  }
})

// Re-animer lors du changement de mois
watch(() => props.currentMonth, () => {
  if (gridRef.value) {
    animateCalendarEntry(gridRef.value)
  }
})
</script>

<style scoped>
.calendar-grid {
  @apply w-full;
}
</style>
