<template>
  <Teleport to="body">
    <Transition
      @enter="onEnter"
      @leave="onLeave"
    >
      <div
        v-if="isOpen"
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
        @click.self="closeModal"
      >
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm"></div>

        <!-- Modal -->
        <div
          ref="modalRef"
          data-modal
          class="relative w-full max-w-2xl max-h-[90vh] overflow-hidden rounded-2xl border border-cyan-500/30 bg-slate-900/95 backdrop-blur-xl shadow-2xl shadow-cyan-500/20"
        >
          <!-- Header -->
          <div class="relative border-b border-cyan-500/20 bg-gradient-to-r from-cyan-500/10 to-blue-500/10 p-6">
            <div class="flex items-center justify-between">
              <div>
                <h2 class="text-2xl font-bold bg-gradient-to-r from-cyan-400 to-blue-400 bg-clip-text text-transparent">
                  Planifier une Remise
                </h2>
                <p class="text-sm text-gray-400 mt-1">
                  Date de sortie : <span class="text-cyan-400 font-medium">{{ formattedDate }}</span>
                </p>
              </div>
              <button
                @click="closeModal"
                class="w-10 h-10 rounded-lg bg-slate-800/50 hover:bg-slate-700 border border-gray-700 flex items-center justify-center transition-all"
                aria-label="Fermer"
              >
                <i class="pi pi-times text-gray-400"></i>
              </button>
            </div>
          </div>

          <!-- Content -->
          <div class="p-6 overflow-y-auto max-h-[calc(90vh-200px)]">
            <!-- État de chargement -->
            <div v-if="loading" class="flex items-center justify-center py-12">
              <i class="pi pi-spin pi-spinner text-4xl text-cyan-400"></i>
            </div>

            <!-- Aucune maintenance -->
            <div v-else-if="maintenancesDisponibles.length === 0" class="text-center py-12">
              <i class="pi pi-calendar-times text-5xl text-gray-600 mb-4"></i>
              <p class="text-gray-400">Aucune maintenance en cours ou planifiée</p>
            </div>

            <!-- Formulaire -->
            <form v-else @submit.prevent="handleSubmit" class="space-y-6">
              <!-- Filtre et Recherche -->
              <div class="flex gap-3">
                <div class="flex-1">
                  <InputText
                    v-model="searchQuery"
                    placeholder="Rechercher par équipement..."
                    class="w-full"
                  >
                    <template #prefix>
                      <i class="pi pi-search text-gray-500"></i>
                    </template>
                  </InputText>
                </div>
                <Dropdown
                  v-model="filterStatut"
                  :options="statutOptions"
                  optionLabel="label"
                  optionValue="value"
                  placeholder="Filtrer par statut"
                  class="w-48"
                />
              </div>

              <!-- Liste des maintenances -->
              <div class="space-y-3">
                <div
                  v-for="maintenance in filteredMaintenances"
                  :key="maintenance.id"
                  @click="toggleMaintenance(maintenance.id)"
                  :class="[
                    'p-4 rounded-xl border-2 transition-all cursor-pointer',
                    isSelected(maintenance.id)
                      ? 'border-cyan-500 bg-cyan-500/10'
                      : 'border-gray-700 bg-slate-800/30 hover:border-gray-600'
                  ]"
                >
                  <div class="flex items-start justify-between">
                    <div class="flex-1">
                      <div class="flex items-center gap-3 mb-2">
                        <!-- Checkbox -->
                        <div
                          :class="[
                            'w-5 h-5 rounded border-2 flex items-center justify-center transition-all',
                            isSelected(maintenance.id)
                              ? 'border-cyan-500 bg-cyan-500'
                              : 'border-gray-600'
                          ]"
                        >
                          <i v-if="isSelected(maintenance.id)" class="pi pi-check text-white text-xs"></i>
                        </div>

                        <!-- Info -->
                        <div>
                          <div class="font-medium text-gray-200">
                            {{ maintenance.equipement?.reference || 'N/A' }}
                          </div>
                          <div class="text-xs text-gray-500">
                            {{ maintenance.equipement?.marque }} - {{ maintenance.equipement?.modele }}
                          </div>
                        </div>
                      </div>

                      <!-- Détails -->
                      <div class="flex items-center gap-4 text-xs text-gray-400 ml-8">
                        <span class="flex items-center gap-1">
                          <i class="pi pi-calendar"></i>
                          {{ formatDate(maintenance.date_prevue) }}
                        </span>
                        <span class="flex items-center gap-1">
                          <i class="pi pi-user"></i>
                          {{ maintenance.responsable }}
                        </span>
                        <span
                          :class="[
                            'px-2 py-0.5 rounded-full text-[10px] font-medium',
                            maintenance.statut === 'planifiee' ? 'bg-blue-500/20 text-blue-400' :
                            maintenance.statut === 'en_cours' ? 'bg-orange-500/20 text-orange-400' :
                            'bg-green-500/20 text-green-400'
                          ]"
                        >
                          {{ getStatutLabel(maintenance.statut) }}
                        </span>
                      </div>
                    </div>

                    <!-- Type -->
                    <div
                      :class="[
                        'px-3 py-1 rounded-lg text-xs font-medium',
                        maintenance.type_maintenance === 'préventif'
                          ? 'bg-blue-500/20 text-blue-400'
                          : 'bg-orange-500/20 text-orange-400'
                      ]"
                    >
                      {{ maintenance.type_maintenance }}
                    </div>
                  </div>
                </div>
              </div>

              <!-- Sélection multiple -->
              <div v-if="form.maintenances.length > 0" class="p-4 rounded-xl bg-cyan-500/10 border border-cyan-500/30">
                <div class="flex items-center gap-2 text-sm text-cyan-400">
                  <i class="pi pi-check-circle"></i>
                  <span class="font-medium">{{ form.maintenances.length }} maintenance(s) sélectionnée(s)</span>
                </div>
              </div>
            </form>
          </div>

          <!-- Footer -->
          <div class="border-t border-gray-800 bg-slate-950/50 p-6 flex items-center justify-end gap-3">
            <Button
              label="Annuler"
              severity="secondary"
              text
              @click="closeModal"
              class="px-6"
            />
            <Button
              label="Planifier la Remise"
              :loading="isSubmitting"
              :disabled="form.maintenances.length === 0"
              @click="handleSubmit"
              class="px-6 bg-gradient-to-r from-cyan-500 to-blue-500 hover:from-cyan-600 hover:to-blue-600 border-0"
            >
              <template #icon>
                <i class="pi pi-calendar-plus mr-2"></i>
              </template>
            </Button>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { gsap } from 'gsap'
import InputText from 'primevue/inputtext'
import Dropdown from 'primevue/dropdown'
import Button from 'primevue/button'
import { formatDate } from '@/utils/dateFormatter'
import { useToast } from 'primevue/usetoast'

const props = defineProps({
  isOpen: {
    type: Boolean,
    default: false
  },
  selectedDate: {
    type: Date,
    default: null
  },
  maintenances: {
    type: Array,
    default: () => []
  }
})

const emit = defineEmits(['close', 'submit'])

const toast = useToast()
const modalRef = ref(null)
const isSubmitting = ref(false)
const loading = ref(false)

// Formulaire
const form = ref({
  maintenances: []
})

// Filtres
const searchQuery = ref('')
const filterStatut = ref('all')

const statutOptions = [
  { label: 'Tous', value: 'all' },
  { label: 'Planifiée', value: 'planifiee' },
  { label: 'En cours', value: 'en_cours' }
]

// Maintenances disponibles (planifiées ou en cours)
const maintenancesDisponibles = computed(() => {
  return props.maintenances.filter(m => 
    m.statut === 'planifiee' || m.statut === 'en_cours'
  )
})

// Maintenances filtrées
const filteredMaintenances = computed(() => {
  let result = maintenancesDisponibles.value

  // Filtre par statut
  if (filterStatut.value !== 'all') {
    result = result.filter(m => m.statut === filterStatut.value)
  }

  // Recherche
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    result = result.filter(m => 
      m.equipement?.reference?.toLowerCase().includes(query) ||
      m.equipement?.marque?.toLowerCase().includes(query) ||
      m.equipement?.modele?.toLowerCase().includes(query)
    )
  }

  return result
})

// Date formatée
const formattedDate = computed(() => {
  return props.selectedDate ? formatDate(props.selectedDate) : ''
})

// Vérifier si une maintenance est sélectionnée
function isSelected(id) {
  return form.value.maintenances.includes(id)
}

// Toggle maintenance
function toggleMaintenance(id) {
  const index = form.value.maintenances.indexOf(id)
  if (index > -1) {
    form.value.maintenances.splice(index, 1)
  } else {
    form.value.maintenances.push(id)
  }
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

// Réinitialiser le formulaire
watch(() => props.isOpen, (newVal) => {
  if (newVal) {
    resetForm()
  }
})

// Soumission
async function handleSubmit() {
  if (form.value.maintenances.length === 0) {
    toast.add({
      severity: 'warn',
      summary: 'Attention',
      detail: 'Veuillez sélectionner au moins une maintenance',
      life: 3000
    })
    return
  }

  isSubmitting.value = true

  try {
    const data = {
      maintenance_ids: form.value.maintenances,
      date_fin: props.selectedDate.toISOString().split('T')[0]
    }

    emit('submit', data)
    closeModal()
    
    toast.add({
      severity: 'success',
      summary: 'Succès',
      detail: `Remise planifiée pour ${form.value.maintenances.length} maintenance(s)`,
      life: 3000
    })
  } catch (error) {
    toast.add({
      severity: 'error',
      summary: 'Erreur',
      detail: 'Erreur lors de la planification de la remise',
      life: 3000
    })
  } finally {
    isSubmitting.value = false
  }
}

// Fermer
function closeModal() {
  emit('close')
}

// Réinitialiser
function resetForm() {
  form.value = {
    maintenances: []
  }
  searchQuery.value = ''
  filterStatut.value = 'all'
}

// Animations GSAP
function onEnter(el, done) {
  gsap.fromTo(
    el.querySelector('[data-modal]') || el.children[1],
    { scale: 0.8, opacity: 0 },
    { scale: 1, opacity: 1, duration: 0.3, ease: 'back.out(1.7)', onComplete: done }
  )
}

function onLeave(el, done) {
  gsap.to(
    el.querySelector('[data-modal]') || el.children[1],
    { scale: 0.8, opacity: 0, duration: 0.2, ease: 'power2.in', onComplete: done }
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
  @apply border-cyan-500 ring-2 ring-cyan-500/20;
}
</style>
