<template>
  <Teleport to="body">
    <Transition name="context-menu">
      <div
        v-if="isOpen && position"
        ref="menuRef"
        :style="menuStyle"
        class="fixed z-40 w-64 rounded-xl border border-pink-500/30 bg-slate-900/95 backdrop-blur-xl shadow-2xl shadow-pink-500/20 overflow-hidden"
        @click.stop
      >
        <!-- Header -->
        <div class="p-3 border-b border-gray-800 bg-gradient-to-r from-pink-500/10 to-purple-500/10">
          <div class="text-sm font-medium text-gray-300">
            {{ formattedDate }}
          </div>
          <div class="text-xs text-gray-500 mt-0.5">
            {{ eventsCount }} événement(s)
          </div>
        </div>

        <!-- Actions -->
        <div class="p-2">
          <!-- Planifier Maintenance -->
          <button
            @click="handlePlanMaintenance"
            class="w-full flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-pink-500/10 transition-all group"
          >
            <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-pink-500 to-purple-500 flex items-center justify-center group-hover:scale-110 transition-transform">
              <i class="pi pi-plus text-white"></i>
            </div>
            <div class="text-left flex-1">
              <div class="text-sm font-medium text-gray-200 group-hover:text-pink-400 transition-colors">
                Planifier une maintenance
              </div>
              <div class="text-xs text-gray-500">
                Nouvelle intervention
              </div>
            </div>
          </button>

          <!-- Planifier Remise -->
          <button
            @click="handlePlanRemise"
            class="w-full flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-cyan-500/10 transition-all group"
          >
            <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-cyan-500 to-blue-500 flex items-center justify-center group-hover:scale-110 transition-transform">
              <i class="pi pi-calendar-plus text-white"></i>
            </div>
            <div class="text-left flex-1">
              <div class="text-sm font-medium text-gray-200 group-hover:text-cyan-400 transition-colors">
                Planifier une remise
              </div>
              <div class="text-xs text-gray-500">
                Date de sortie
              </div>
            </div>
          </button>
        </div>

        <!-- Footer (si événements existants) -->
        <div v-if="eventsCount > 0" class="p-2 border-t border-gray-800">
          <button
            @click="handleViewAll"
            class="w-full px-4 py-2 rounded-lg text-sm text-gray-400 hover:text-gray-200 hover:bg-slate-800/50 transition-all flex items-center justify-center gap-2"
          >
            <i class="pi pi-list"></i>
            <span>Voir tous les événements</span>
          </button>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, computed, watch, nextTick, onMounted, onBeforeUnmount } from 'vue'
import { formatDate } from '@/utils/dateFormatter'

const props = defineProps({
  isOpen: {
    type: Boolean,
    default: false
  },
  selectedDate: {
    type: Date,
    default: null
  },
  position: {
    type: Object,
    default: null
  },
  eventsCount: {
    type: Number,
    default: 0
  }
})

const emit = defineEmits(['close', 'plan-maintenance', 'plan-remise', 'view-all'])

const menuRef = ref(null)

// Date formatée
const formattedDate = computed(() => {
  return props.selectedDate ? formatDate(props.selectedDate) : ''
})

// Style du menu positionné
const menuStyle = computed(() => {
  if (!props.position) return {}

  const { x, y } = props.position
  const menuWidth = 256 // w-64 = 16rem = 256px
  const menuHeight = props.eventsCount > 0 ? 260 : 220 // Hauteur approximative

  let left = x
  let top = y

  // Ajuster si hors écran à droite
  if (x + menuWidth > window.innerWidth) {
    left = window.innerWidth - menuWidth - 16
  }

  // Ajuster si hors écran en bas
  if (y + menuHeight > window.innerHeight) {
    top = y - menuHeight
  }

  return {
    left: `${left}px`,
    top: `${top}px`
  }
})

// Handlers
function handlePlanMaintenance() {
  emit('plan-maintenance')
  emit('close')
}

function handlePlanRemise() {
  emit('plan-remise')
  emit('close')
}

function handleViewAll() {
  emit('view-all')
  emit('close')
}

// Fermer au clic extérieur
function handleClickOutside(event) {
  if (menuRef.value && !menuRef.value.contains(event.target)) {
    emit('close')
  }
}

// Fermer à l'Esc
function handleEscape(event) {
  if (event.key === 'Escape') {
    emit('close')
  }
}

// Lifecycle
watch(() => props.isOpen, (newVal) => {
  if (newVal) {
    nextTick(() => {
      document.addEventListener('click', handleClickOutside)
      document.addEventListener('keydown', handleEscape)
    })
  } else {
    document.removeEventListener('click', handleClickOutside)
    document.removeEventListener('keydown', handleEscape)
  }
})

onBeforeUnmount(() => {
  document.removeEventListener('click', handleClickOutside)
  document.removeEventListener('keydown', handleEscape)
})
</script>

<style scoped>
/* Transitions */
.context-menu-enter-active {
  animation: context-menu-in 0.2s ease-out;
}

.context-menu-leave-active {
  animation: context-menu-out 0.15s ease-in;
}

@keyframes context-menu-in {
  from {
    opacity: 0;
    transform: scale(0.95) translateY(-10px);
  }
  to {
    opacity: 1;
    transform: scale(1) translateY(0);
  }
}

@keyframes context-menu-out {
  from {
    opacity: 1;
    transform: scale(1) translateY(0);
  }
  to {
    opacity: 0;
    transform: scale(0.95) translateY(-10px);
  }
}
</style>
