<template>
  <div
    :class="cellClasses"
    class="min-h-[120px] p-2 border border-gray-200 dark:border-gray-700 transition-colors"
    data-day-cell
  >
    <!-- Numéro du jour -->
    <div :class="dayNumberClasses" class="text-sm font-medium mb-1">
      {{ day.day }}
    </div>

    <!-- Événements visibles (max 3) -->
    <div v-if="day.visibleMaintenances.length > 0" class="space-y-1">
      <MaintenanceEventCard
        v-for="maintenance in day.visibleMaintenances"
        :key="maintenance.id"
        :maintenance="maintenance"
        compact
        @click="$emit('event-click', maintenance)"
      />

      <!-- Indicateur "+N autres" si plus de 3 événements -->
      <div
        v-if="day.hasMore"
        class="text-xs text-gray-600 dark:text-gray-400 font-medium pl-2 py-1 cursor-pointer hover:text-gray-800 dark:hover:text-gray-200"
        @click="$emit('show-more', day)"
      >
        +{{ day.moreCount }} autre{{ day.moreCount > 1 ? 's' : '' }}
      </div>
    </div>

    <!-- Message si aucun événement -->
    <div
      v-else
      class="text-xs text-gray-400 dark:text-gray-600 italic h-full flex items-center justify-center"
    >
      <!-- Vide intentionnellement pour les jours sans maintenance -->
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import MaintenanceEventCard from './MaintenanceEventCard.vue'

const props = defineProps({
  day: {
    type: Object,
    required: true
  }
})

defineEmits(['event-click', 'show-more'])

// Classes dynamiques pour la cellule
const cellClasses = computed(() => {
  const classes = []

  // Si c'est aujourd'hui
  if (props.day.isToday) {
    classes.push('bg-blue-50/50 dark:bg-blue-900/10 border-blue-300 dark:border-blue-700')
  }
  // Si ce n'est pas le mois actuel
  else if (!props.day.isCurrentMonth) {
    classes.push('bg-gray-50/30 dark:bg-gray-900/30 opacity-50')
  }
  // Jour normal du mois actuel
  else {
    classes.push('bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700/50')
  }

  return classes
})

// Classes pour le numéro du jour
const dayNumberClasses = computed(() => {
  const classes = []

  // Si c'est aujourd'hui
  if (props.day.isToday) {
    classes.push(
      'inline-flex items-center justify-center',
      'w-7 h-7 rounded-full',
      'bg-blue-500 text-white',
      'font-bold'
    )
  }
  // Si ce n'est pas le mois actuel
  else if (!props.day.isCurrentMonth) {
    classes.push('text-gray-400 dark:text-gray-600')
  }
  // Jour normal
  else {
    classes.push('text-gray-700 dark:text-gray-300')
  }

  return classes
})
</script>

<style scoped>
/* Style minimal - tout géré par Tailwind */
</style>
