<template>
  <!-- Modal Overlay -->
  <Teleport to="body">
    <Transition name="modal">
      <div
        v-if="isOpen"
        ref="modalRef"
        class="fixed inset-0 z-50 overflow-y-auto"
        @click.self="handleClose"
        @keydown.esc="handleClose"
      >
        <!-- Backdrop -->
        <div
          data-modal-backdrop
          class="fixed inset-0 bg-black/50 backdrop-blur-sm transition-opacity"
          @click="handleClose"
        ></div>

        <!-- Modal Panel -->
        <div class="flex min-h-full items-center justify-center p-4">
          <div
            data-modal-panel
            class="relative w-full max-w-2xl transform rounded-2xl p-6 text-left shadow-2xl transition-all glass-card"
            @click.stop
          >
            <!-- Close Button -->
            <button
              class="absolute top-4 right-4 p-2 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
              @click="handleClose"
              aria-label="Fermer"
            >
              <i class="pi pi-times text-gray-500 dark:text-gray-400"></i>
            </button>

            <!-- En-tête -->
            <div class="mb-6">
              <div class="flex items-start gap-3">
                <div :class="statusIconColorClass" class="mt-1">
                  <i :class="typeIcon" class="text-2xl"></i>
                </div>
                <div class="flex-1">
                  <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-1">
                    Maintenance {{ typeText }}
                  </h2>
                  <span :class="statusBadgeClass" class="inline-block px-3 py-1 rounded-full text-sm font-medium">
                    {{ statusText }}
                  </span>
                </div>
              </div>
            </div>

            <!-- Contenu principal -->
            <div v-if="maintenance" class="space-y-6">
              <!-- Section : Dates -->
              <div class="bg-white/40 dark:bg-gray-800/40 rounded-lg p-4">
                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3 flex items-center gap-2">
                  <i class="pi pi-calendar"></i>
                  Dates
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-sm">
                  <div>
                    <p class="text-gray-500 dark:text-gray-400 text-xs">Date prévue</p>
                    <p class="font-medium text-gray-900 dark:text-white">
                      {{ formatDateTime(maintenance.date_prevue) }}
                    </p>
                  </div>
                  <div v-if="maintenance.date_debut">
                    <p class="text-gray-500 dark:text-gray-400 text-xs">Date de début</p>
                    <p class="font-medium text-gray-900 dark:text-white">
                      {{ formatDateTime(maintenance.date_debut) }}
                    </p>
                  </div>
                  <div v-if="maintenance.date_fin">
                    <p class="text-gray-500 dark:text-gray-400 text-xs">Date de fin</p>
                    <p class="font-medium text-gray-900 dark:text-white">
                      {{ formatDateTime(maintenance.date_fin) }}
                    </p>
                  </div>
                </div>
                <div v-if="maintenance.duree_estimee" class="mt-3 text-sm">
                  <p class="text-gray-500 dark:text-gray-400 text-xs">Durée estimée</p>
                  <p class="font-medium text-gray-900 dark:text-white">
                    {{ maintenance.duree_estimee }} heure{{ maintenance.duree_estimee > 1 ? 's' : '' }}
                  </p>
                </div>
              </div>

              <!-- Section : Responsables -->
              <div class="bg-white/40 dark:bg-gray-800/40 rounded-lg p-4">
                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3 flex items-center gap-2">
                  <i class="pi pi-users"></i>
                  Responsables
                </h3>
                <div class="space-y-2 text-sm">
                  <div>
                    <p class="text-gray-500 dark:text-gray-400 text-xs">Responsable</p>
                    <p class="font-medium text-gray-900 dark:text-white">
                      {{ maintenance.responsable }}
                    </p>
                  </div>
                  <div v-if="maintenance.technicienUser">
                    <p class="text-gray-500 dark:text-gray-400 text-xs">Technicien</p>
                    <p class="font-medium text-gray-900 dark:text-white">
                      {{ maintenance.technicienUser.name }}
                    </p>
                  </div>
                </div>
              </div>

              <!-- Section : Équipement -->
              <div class="bg-white/40 dark:bg-gray-800/40 rounded-lg p-4">
                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3 flex items-center gap-2">
                  <i class="pi pi-box"></i>
                  Équipement
                </h3>
                <div v-if="maintenance.equipement" class="space-y-2 text-sm">
                  <div>
                    <p class="text-gray-500 dark:text-gray-400 text-xs">Référence</p>
                    <p class="font-medium text-gray-900 dark:text-white">
                      {{ maintenance.equipement.reference }}
                    </p>
                  </div>
                  <div class="grid grid-cols-2 gap-3">
                    <div>
                      <p class="text-gray-500 dark:text-gray-400 text-xs">Marque</p>
                      <p class="font-medium text-gray-900 dark:text-white">
                        {{ maintenance.equipement.marque }}
                      </p>
                    </div>
                    <div>
                      <p class="text-gray-500 dark:text-gray-400 text-xs">Modèle</p>
                      <p class="font-medium text-gray-900 dark:text-white">
                        {{ maintenance.equipement.modele }}
                      </p>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Section : Coût -->
              <div v-if="maintenance.cout" class="bg-white/40 dark:bg-gray-800/40 rounded-lg p-4">
                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3 flex items-center gap-2">
                  <i class="pi pi-money-bill"></i>
                  Coût
                </h3>
                <p class="text-lg font-bold text-gray-900 dark:text-white">
                  {{ formatCurrency(maintenance.cout) }}
                </p>
              </div>

              <!-- Section : Observations -->
              <div v-if="maintenance.observations" class="bg-white/40 dark:bg-gray-800/40 rounded-lg p-4">
                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3 flex items-center gap-2">
                  <i class="pi pi-file-edit"></i>
                  Observations
                </h3>
                <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-line">
                  {{ maintenance.observations }}
                </p>
              </div>

              <!-- Section : Rapport (si terminée) -->
              <div v-if="maintenance.rapport" class="bg-white/40 dark:bg-gray-800/40 rounded-lg p-4">
                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3 flex items-center gap-2">
                  <i class="pi pi-file-check"></i>
                  Rapport d'intervention
                </h3>
                <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-line">
                  {{ maintenance.rapport }}
                </p>
              </div>

              <!-- Section : Pièces changées -->
              <div v-if="maintenance.pieces_changees" class="bg-white/40 dark:bg-gray-800/40 rounded-lg p-4">
                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3 flex items-center gap-2">
                  <i class="pi pi-cog"></i>
                  Pièces changées
                </h3>
                <p class="text-sm text-gray-700 dark:text-gray-300">
                  {{ maintenance.pieces_changees }}
                </p>
              </div>
            </div>

            <!-- Footer avec actions -->
            <div class="mt-6 flex justify-end gap-3">
              <button
                class="px-4 py-2 rounded-lg text-sm font-medium bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 transition-colors"
                @click="handleClose"
              >
                Fermer
              </button>
            </div>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue'
import { formatDateTime } from '@/utils/dateFormatter'
import { animateModalOpen, animateModalClose, cleanupAnimations } from '@/utils/gsapAnimations'

const props = defineProps({
  maintenance: {
    type: Object,
    default: null
  },
  isOpen: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['close'])

const modalRef = ref(null)
let focusedElementBeforeModal = null

// Couleur du statut (même logique que EventCard)
const statusColor = computed(() => {
  if (!props.maintenance) return {}

  const colors = {
    planifiee: {
      badge: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
      icon: 'text-blue-600 dark:text-blue-400'
    },
    en_cours: {
      badge: 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-300',
      icon: 'text-orange-600 dark:text-orange-400'
    },
    terminee: {
      badge: 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300',
      icon: 'text-green-600 dark:text-green-400'
    }
  }

  return colors[props.maintenance.statut] || colors.planifiee
})

const statusBadgeClass = computed(() => statusColor.value.badge)
const statusIconColorClass = computed(() => statusColor.value.icon)

const statusText = computed(() => {
  if (!props.maintenance) return ''
  const statusMap = {
    planifiee: 'Planifié',
    en_cours: 'En cours',
    terminee: 'Terminé'
  }
  return statusMap[props.maintenance.statut] || 'Inconnu'
})

const typeIcon = computed(() => {
  if (!props.maintenance) return 'pi pi-wrench'
  return props.maintenance.type_maintenance === 'préventif'
    ? 'pi pi-calendar-clock'
    : 'pi pi-wrench'
})

const typeText = computed(() => {
  if (!props.maintenance) return ''
  return props.maintenance.type_maintenance === 'préventif'
    ? 'Préventive'
    : 'Corrective'
})

// Formatage de la monnaie
function formatCurrency(value) {
  return new Intl.NumberFormat('fr-FR', {
    style: 'currency',
    currency: 'XOF' // Franc CFA (ou EUR selon votre config)
  }).format(value)
}

// Gestion de la fermeture
function handleClose() {
  if (modalRef.value) {
    animateModalClose(modalRef.value, () => {
      emit('close')
      // Restaurer le focus
      if (focusedElementBeforeModal) {
        focusedElementBeforeModal.focus()
        focusedElementBeforeModal = null
      }
    })
  } else {
    emit('close')
  }
}

// Animation à l'ouverture
watch(() => props.isOpen, (newVal) => {
  if (newVal) {
    // Sauvegarder le focus actuel
    focusedElementBeforeModal = document.activeElement
    
    // Attendre le prochain tick pour que le DOM soit mis à jour
    setTimeout(() => {
      if (modalRef.value) {
        animateModalOpen(modalRef.value)
        // Focus trap : mettre le focus sur le modal
        const firstFocusable = modalRef.value.querySelector('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])')
        if (firstFocusable) {
          firstFocusable.focus()
        }
      }
    }, 10)
  }
})

onUnmounted(() => {
  cleanupAnimations()
})
</script>

<style scoped>
/* Transitions de la modale */
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}

/* Glassmorphism pour le panneau */
.glass-card {
  background: rgba(255, 255, 255, 0.85);
  backdrop-filter: blur(20px);
  border: 1px solid rgba(255, 255, 255, 0.3);
}

@media (prefers-color-scheme: dark) {
  .glass-card {
    background: rgba(31, 41, 55, 0.85);
    border: 1px solid rgba(75, 85, 99, 0.3);
  }
}

/* Responsive : plein écran sur mobile */
@media (max-width: 768px) {
  .glass-card {
    max-width: 100%;
    height: 100vh;
    border-radius: 0;
    overflow-y: auto;
  }
}
</style>
