<template>
  <Teleport to="body">
    <Transition
      @enter="onEnter"
      @leave="onLeave"
    >
      <div
        v-if="isOpen"
        class="fixed inset-0 z-50 flex items-end sm:items-center justify-end"
        @click.self="close"
      >
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm"></div>

        <!-- Slide-over Panel -->
        <div
          ref="panelRef"
          data-panel
          class="relative w-full sm:w-[480px] h-[80vh] sm:h-full sm:max-h-screen bg-slate-900/95 backdrop-blur-xl border-l border-t sm:border-t-0 border-pink-500/30 shadow-2xl shadow-pink-500/20 overflow-hidden"
        >
          <!-- Header -->
          <div class="sticky top-0 z-10 border-b border-pink-500/20 bg-gradient-to-r from-pink-500/10 to-purple-500/10 p-6">
            <div class="flex items-center justify-between mb-4">
              <div>
                <h2 class="text-xl font-bold bg-gradient-to-r from-pink-400 to-purple-400 bg-clip-text text-transparent">
                  Événements du Jour
                </h2>
                <p class="text-sm text-gray-400 mt-1">
                  {{ formattedDate }} - {{ totalEvents }} événement(s)
                </p>
              </div>
              <button
                @click="close"
                class="w-10 h-10 rounded-lg bg-slate-800/50 hover:bg-slate-700 border border-gray-700 flex items-center justify-center transition-all"
                aria-label="Fermer"
              >
                <i class="pi pi-times text-gray-400"></i>
              </button>
            </div>

            <!-- Recherche et Tri -->
            <div class="flex gap-3">
              <div class="flex-1">
                <InputText
                  v-model="searchQuery"
                  placeholder="Rechercher..."
                  class="w-full"
                >
                  <template #prefix>
                    <i class="pi pi-search text-gray-500"></i>
                  </template>
                </InputText>
              </div>
              <Dropdown
                v-model="sortBy"
                :options="sortOptions"
                optionLabel="label"
                optionValue="value"
                class="w-32"
              >
                <template #value="slotProps">
                  <div class="flex items-center gap-2">
                    <i class="pi pi-sort-alt text-xs"></i>
                    <span class="text-sm">{{ slotProps.value === 'heure' ? 'Heure' : 'Type' }}</span>
                  </div>
                </template>
              </Dropdown>
            </div>
          </div>

          <!-- Liste des événements -->
          <div class="p-6 overflow-y-auto h-[calc(100%-140px)]">
            <!-- Message vide -->
            <div v-if="filteredEvents.length === 0" class="text-center py-12">
              <i class="pi pi-inbox text-5xl text-gray-600 mb-4"></i>
              <p class="text-gray-400">Aucun événement trouvé</p>
            </div>

            <!-- Liste -->
            <div v-else class="space-y-3">
              <div
                v-for="event in filteredEvents"
                :key="event.id"
                @click="$emit('event-click', event)"
                class="group p-4 rounded-xl bg-slate-800/30 border border-gray-700 hover:border-pink-500/50 hover:bg-slate-800/50 transition-all cursor-pointer"
              >
                <!-- Header -->
                <div class="flex items-start justify-between mb-3">
                  <div class="flex items-center gap-3">
                    <!-- Icône Type -->
                    <div
                      :class="[
                        'w-10 h-10 rounded-lg flex items-center justify-center',
                        event.type_maintenance === 'préventif'
                          ? 'bg-blue-500/20'
                          : 'bg-orange-500/20'
                      ]"
                    >
                      <i
                        :class="[
                          'text-lg',
                          event.type_maintenance === 'préventif'
                            ? 'pi pi-calendar-clock text-blue-400'
                            : 'pi pi-wrench text-orange-400'
                        ]"
                      ></i>
                    </div>

                    <!-- Heure -->
                    <div>
                      <div class="text-lg font-bold text-gray-200">
                        {{ formatTime(event.date_prevue) }}
                      </div>
                      <div class="text-xs text-gray-500">
                        {{ event.type_maintenance }}
                      </div>
                    </div>
                  </div>

                  <!-- Badge Statut -->
                  <span
                    :class="[
                      'px-2 py-1 rounded-lg text-xs font-medium',
                      event.statut === 'planifiee' ? 'bg-blue-500/20 text-blue-400' :
                      event.statut === 'en_cours' ? 'bg-orange-500/20 text-orange-400' :
                      'bg-green-500/20 text-green-400'
                    ]"
                  >
                    {{ getStatutLabel(event.statut) }}
                  </span>
                </div>

                <!-- Équipement -->
                <div class="mb-2">
                  <div class="text-sm font-medium text-gray-300">
                    {{ event.equipement?.reference || 'N/A' }}
                  </div>
                  <div class="text-xs text-gray-500">
                    {{ event.equipement?.marque }} - {{ event.equipement?.modele }}
                  </div>
                </div>

                <!-- Détails -->
                <div class="flex items-center gap-4 text-xs text-gray-400">
                  <span class="flex items-center gap-1">
                    <i class="pi pi-user"></i>
                    {{ event.responsable }}
                  </span>
                  <span v-if="event.technicienUser" class="flex items-center gap-1">
                    <i class="pi pi-wrench"></i>
                    {{ event.technicienUser.name }}
                  </span>
                  <span v-if="event.cout" class="flex items-center gap-1">
                    <i class="pi pi-money-bill"></i>
                    {{ formatCurrency(event.cout) }}
                  </span>
                </div>

                <!-- Badge gravité (si applicable) -->
                <div v-if="event.gravite" class="mt-2">
                  <span
                    :class="[
                      'inline-flex items-center gap-1 px-2 py-1 rounded text-xs font-medium',
                      event.gravite === 'critique' ? 'bg-red-500/20 text-red-400' :
                      event.gravite === 'majeur' ? 'bg-orange-500/20 text-orange-400' :
                      event.gravite === 'moyen' ? 'bg-yellow-500/20 text-yellow-400' :
                      'bg-blue-500/20 text-blue-400'
                    ]"
                  >
                    <i class="pi pi-circle-fill text-[6px]"></i>
                    {{ event.gravite }}
                  </span>
                </div>

                <!-- Hover indicator -->
                <div class="opacity-0 group-hover:opacity-100 transition-opacity mt-3 pt-3 border-t border-gray-700 flex items-center gap-2 text-xs text-pink-400">
                  <i class="pi pi-arrow-right"></i>
                  <span>Cliquer pour voir les détails</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, computed } from 'vue'
import { gsap } from 'gsap'
import InputText from 'primevue/inputtext'
import Dropdown from 'primevue/dropdown'
import { formatDate, formatTime } from '@/utils/dateFormatter'

const props = defineProps({
  isOpen: {
    type: Boolean,
    default: false
  },
  date: {
    type: Date,
    default: null
  },
  events: {
    type: Array,
    default: () => []
  }
})

const emit = defineEmits(['close', 'event-click'])

const panelRef = ref(null)
const searchQuery = ref('')
const sortBy = ref('heure')

const sortOptions = [
  { label: 'Par heure', value: 'heure' },
  { label: 'Par type', value: 'type' }
]

// Date formatée
const formattedDate = computed(() => {
  return props.date ? formatDate(props.date) : ''
})

// Total événements
const totalEvents = computed(() => props.events.length)

// Événements filtrés et triés
const filteredEvents = computed(() => {
  let result = [...props.events]

  // Recherche
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    result = result.filter(e => 
      e.equipement?.reference?.toLowerCase().includes(query) ||
      e.equipement?.marque?.toLowerCase().includes(query) ||
      e.responsable?.toLowerCase().includes(query) ||
      e.technicienUser?.name?.toLowerCase().includes(query)
    )
  }

  // Tri
  if (sortBy.value === 'heure') {
    result.sort((a, b) => {
      const timeA = new Date(a.date_prevue).getTime()
      const timeB = new Date(b.date_prevue).getTime()
      return timeA - timeB
    })
  } else if (sortBy.value === 'type') {
    result.sort((a, b) => a.type_maintenance.localeCompare(b.type_maintenance))
  }

  return result
})

// Formater monnaie
function formatCurrency(value) {
  return new Intl.NumberFormat('fr-FR', {
    style: 'currency',
    currency: 'XOF',
    minimumFractionDigits: 0
  }).format(value)
}

// Label statut
function getStatutLabel(statut) {
  const labels = {
    planifiee: 'Planifiée',
    en_cours: 'En cours',
    terminee: 'Terminée'
  }
  return labels[statut] || statut
}

// Fermer
function close() {
  emit('close')
}

// Animations GSAP
function onEnter(el, done) {
  gsap.fromTo(
    el.querySelector('[data-panel]') || el.children[1],
    { x: '100%', opacity: 0 },
    { x: '0%', opacity: 1, duration: 0.4, ease: 'power3.out', onComplete: done }
  )
}

function onLeave(el, done) {
  gsap.to(
    el.querySelector('[data-panel]') || el.children[1],
    { x: '100%', opacity: 0, duration: 0.3, ease: 'power2.in', onComplete: done }
  )
}
</script>

<style scoped>
:deep(.p-inputtext),
:deep(.p-dropdown) {
  @apply bg-slate-800/50 border-gray-700 text-gray-200;
}

:deep(.p-inputtext:focus),
:deep(.p-dropdown:focus) {
  @apply border-pink-500 ring-2 ring-pink-500/20;
}

/* Scrollbar personnalisée */
:deep(*::-webkit-scrollbar) {
  width: 8px;
}

:deep(*::-webkit-scrollbar-track) {
  @apply bg-slate-950/50;
}

:deep(*::-webkit-scrollbar-thumb) {
  @apply bg-pink-500/30 rounded-full;
}

:deep(*::-webkit-scrollbar-thumb:hover) {
  @apply bg-pink-500/50;
}
</style>
