<template>
  <div
    :class="cardClasses"
    class="cursor-pointer transition-all duration-200 hover:scale-102"
    role="button"
    tabindex="0"
    @click="$emit('click', maintenance)"
    @keydown.enter="$emit('click', maintenance)"
    @keydown.space.prevent="$emit('click', maintenance)"
  >
    <!-- Barre de statut colorée sur le côté gauche -->
    <div :class="statusBarClass" class="absolute left-0 top-0 bottom-0 w-1 rounded-l-lg"></div>

    <!-- Contenu principal -->
    <div class="flex items-start gap-2 pl-3">
      <!-- Icône du type de maintenance -->
      <div :class="iconColorClass" class="flex-shrink-0 mt-0.5">
        <i :class="typeIcon" class="text-sm"></i>
      </div>

      <!-- Informations -->
      <div class="flex-1 min-w-0">
        <!-- Heure -->
        <div class="text-xs font-semibold text-gray-700 dark:text-gray-200">
          {{ formattedTime }}
        </div>

        <!-- Référence équipement (tronquée si nécessaire) -->
        <div
          v-if="!compact"
          class="text-xs text-gray-600 dark:text-gray-300 truncate"
          :title="maintenance.equipement?.reference"
        >
          {{ equipementReference }}
        </div>

        <!-- Badge statut (mode compact uniquement) -->
        <div v-if="compact" class="mt-1">
          <span :class="statusBadgeClass" class="text-[10px] px-1.5 py-0.5 rounded-full font-medium">
            {{ statusText }}
          </span>
        </div>
      </div>
    </div>

    <!-- Badge statut (mode normal) -->
    <div v-if="!compact" class="mt-2 flex items-center justify-between">
      <span :class="statusBadgeClass" class="text-xs px-2 py-0.5 rounded-full font-medium">
        {{ statusText }}
      </span>
      <span v-if="typeText" class="text-[10px] text-gray-500 dark:text-gray-400">
        {{ typeText }}
      </span>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { formatTime } from '@/utils/dateFormatter'

const props = defineProps({
  maintenance: {
    type: Object,
    required: true
  },
  compact: {
    type: Boolean,
    default: false
  }
})

defineEmits(['click'])

// Classes de base avec Glassmorphism
const cardClasses = computed(() => [
  'relative p-2.5 rounded-lg',
  'backdrop-blur-lg bg-white/30 dark:bg-gray-800/30',
  'border border-white/20 dark:border-gray-700/20',
  'shadow-sm hover:shadow-md'
])

// Couleur du statut
const statusColor = computed(() => {
  const colors = {
    planifiee: {
      bar: 'bg-blue-500',
      badge: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
      icon: 'text-blue-600 dark:text-blue-400'
    },
    en_cours: {
      bar: 'bg-orange-500',
      badge: 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-300',
      icon: 'text-orange-600 dark:text-orange-400'
    },
    terminee: {
      bar: 'bg-green-500',
      badge: 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300',
      icon: 'text-green-600 dark:text-green-400'
    }
  }

  return colors[props.maintenance.statut] || colors.planifiee
})

const statusBarClass = computed(() => statusColor.value.bar)
const statusBadgeClass = computed(() => statusColor.value.badge)
const iconColorClass = computed(() => statusColor.value.icon)

// Texte du statut
const statusText = computed(() => {
  const statusMap = {
    planifiee: 'Planifié',
    en_cours: 'En cours',
    terminee: 'Terminé'
  }
  return statusMap[props.maintenance.statut] || 'Inconnu'
})

// Icône du type de maintenance
const typeIcon = computed(() => {
  return props.maintenance.type_maintenance === 'préventif'
    ? 'pi pi-calendar-clock'
    : 'pi pi-wrench'
})

// Texte du type
const typeText = computed(() => {
  return props.maintenance.type_maintenance === 'préventif'
    ? 'Préventif'
    : 'Correctif'
})

// Heure formatée
const formattedTime = computed(() => {
  return formatTime(props.maintenance.date_prevue)
})

// Référence équipement
const equipementReference = computed(() => {
  return props.maintenance.equipement?.reference || 'N/A'
})
</script>

<style scoped>
.hover\:scale-102:hover {
  transform: scale(1.02);
}
</style>
