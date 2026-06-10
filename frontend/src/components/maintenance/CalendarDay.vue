<template>
  <div
    :class="cellClasses"
    class="min-h-[120px] p-2 border border-gray-200 dark:border-gray-700 transition-colors cursor-pointer group"
    data-day-cell
    @click="handleDayClick"
  >
    <!-- Numéro du jour -->
    <div :class="dayNumberClasses" class="text-sm font-medium mb-1 relative">
      {{ day.day }}
      
      <!-- Badge nombre d'événements (si > 0) -->
      <span
        v-if="day.maintenances.length > 0"
        class="absolute -top-1 -right-1 w-5 h-5 rounded-full bg-pink-500 text-white text-[10px] font-bold flex items-center justify-center ring-2 ring-slate-950"
      >
        {{ day.maintenances.length }}
      </span>
    </div>

    <!-- Événements visibles (max 3) -->
    <div v-if="day.visibleMaintenances.length > 0" class="space-y-1">
      <MaintenanceEventCard
        v-for="maintenance in day.visibleMaintenances"
        :key="maintenance.id"
        :maintenance="maintenance"
        compact
        @click.stop="$emit('event-click', maintenance)"
      />

      <!-- Indicateur "+N autres" si plus de 3 événements -->
      <button
        v-if="day.hasMore"
        @click.stop="$emit('show-more', day)"
        class="w-full text-xs text-pink-400 hover:text-pink-300 font-medium pl-2 py-1.5 text-left rounded-lg hover:bg-pink-500/10 transition-all flex items-center gap-1.5"
      >
        <i class="pi pi-plus-circle text-xs"></i>
        <span>{{ day.moreCount }} autre{{ day.moreCount > 1 ? 's' : '' }}</span>
      </button>
    </div>

    <!-- Zone cliquable si aucun événement -->
    <div
      v-else
      class="h-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity"
    >
      <div class="text-xs text-gray-500 flex items-center gap-1.5">
        <i class="pi pi-plus-circle"></i>
        <span>Ajouter</span>
      </div>
    </div>

    <!-- Hover overlay -->
    <div class="absolute inset-0 bg-pink-500/5 opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none rounded"></div>
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

defineEmits(['event-click', 'show-more', 'day-click'])

// Classes dynamiques pour la cellule
const cellClasses = computed(() => {
  const classes = ['relative']

  // Si c'est aujourd'hui
  if (props.day.isToday) {
    classes.push('bg-blue-50/50 dark:bg-blue-900/10 border-blue-300 dark:border-blue-700 ring-2 ring-blue-500/30')
  }
  // Si ce n'est pas le mois actuel
  else if (!props.day.isCurrentMonth) {
    classes.push('bg-gray-50/30 dark:bg-gray-900/30 opacity-50')
  }
  // Jour normal du mois actuel
  else {
    classes.push('bg-white dark:bg-slate-900/50 hover:bg-gray-50 dark:hover:bg-slate-800/50 hover:border-pink-500/50')
  }

  return classes
})

// Handler clic sur jour
function handleDayClick(event) {
  emit('day-click', {
    date: props.day.date,
    maintenances: props.day.maintenances,
    position: {
      x: event.clientX,
      y: event.clientY
    }
  })
}

const emit = defineEmits(['event-click', 'show-more', 'day-click'])

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
