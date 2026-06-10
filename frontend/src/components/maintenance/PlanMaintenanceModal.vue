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
          class="relative w-full max-w-2xl max-h-[90vh] overflow-hidden rounded-2xl border border-pink-500/30 bg-slate-900/95 backdrop-blur-xl shadow-2xl shadow-pink-500/20"
        >
          <!-- Header -->
          <div class="relative border-b border-pink-500/20 bg-gradient-to-r from-pink-500/10 to-purple-500/10 p-6">
            <div class="flex items-center justify-between">
              <div>
                <h2 class="text-2xl font-bold bg-gradient-to-r from-pink-400 to-purple-400 bg-clip-text text-transparent">
                  Planifier une Maintenance
                </h2>
                <p class="text-sm text-gray-400 mt-1">
                  Date sélectionnée : <span class="text-pink-400 font-medium">{{ formattedDate }}</span>
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
            <form @submit.prevent="handleSubmit" class="space-y-6">
              <!-- Sélection Équipements -->
              <div class="space-y-2">
                <label class="block text-sm font-medium text-gray-300">
                  Équipement(s) <span class="text-pink-400">*</span>
                </label>
                <MultiSelect
                  v-model="form.equipements"
                  :options="equipements"
                  optionLabel="reference"
                  optionValue="id"
                  placeholder="Rechercher par référence ou ID..."
                  :filter="true"
                  filterPlaceholder="Rechercher..."
                  :maxSelectedLabels="3"
                  class="w-full"
                  :class="{'border-red-500': errors.equipements}"
                >
                  <template #value="slotProps">
                    <div v-if="slotProps.value && slotProps.value.length" class="flex items-center gap-2">
                      <span class="px-2 py-1 rounded-lg bg-pink-500/20 text-pink-400 text-sm">
                        {{ slotProps.value.length }} équipement(s)
                      </span>
                    </div>
                    <span v-else class="text-gray-500">Sélectionner...</span>
                  </template>
                  <template #option="slotProps">
                    <div class="flex items-center gap-3 py-2">
                      <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-pink-500 to-purple-500 flex items-center justify-center text-white text-xs font-bold">
                        {{ slotProps.option.reference?.substring(0, 2) }}
                      </div>
                      <div>
                        <div class="font-medium text-gray-200">{{ slotProps.option.reference }}</div>
                        <div class="text-xs text-gray-500">{{ slotProps.option.marque }} - {{ slotProps.option.modele }}</div>
                      </div>
                    </div>
                  </template>
                </MultiSelect>
                <span v-if="errors.equipements" class="text-xs text-red-400">{{ errors.equipements }}</span>
              </div>

              <!-- Type de Maintenance -->
              <div class="space-y-2">
                <label class="block text-sm font-medium text-gray-300">
                  Type de Maintenance <span class="text-pink-400">*</span>
                </label>
                <div class="grid grid-cols-2 gap-3">
                  <button
                    type="button"
                    @click="form.type = 'préventif'"
                    :class="[
                      'p-4 rounded-xl border-2 transition-all',
                      form.type === 'préventif'
                        ? 'border-blue-500 bg-blue-500/20'
                        : 'border-gray-700 bg-slate-800/50 hover:border-gray-600'
                    ]"
                  >
                    <i class="pi pi-calendar-clock text-2xl mb-2" :class="form.type === 'préventif' ? 'text-blue-400' : 'text-gray-500'"></i>
                    <div class="font-medium" :class="form.type === 'préventif' ? 'text-blue-400' : 'text-gray-400'">Préventif</div>
                  </button>
                  <button
                    type="button"
                    @click="form.type = 'correctif'"
                    :class="[
                      'p-4 rounded-xl border-2 transition-all',
                      form.type === 'correctif'
                        ? 'border-orange-500 bg-orange-500/20'
                        : 'border-gray-700 bg-slate-800/50 hover:border-gray-600'
                    ]"
                  >
                    <i class="pi pi-wrench text-2xl mb-2" :class="form.type === 'correctif' ? 'text-orange-400' : 'text-gray-500'"></i>
                    <div class="font-medium" :class="form.type === 'correctif' ? 'text-orange-400' : 'text-gray-400'">Correctif</div>
                  </button>
                </div>
              </div>

              <!-- Responsable -->
              <div class="space-y-2">
                <label class="block text-sm font-medium text-gray-300">
                  Responsable <span class="text-pink-400">*</span>
                </label>
                <InputText
                  v-model="form.responsable"
                  placeholder="Nom du responsable"
                  class="w-full"
                  :class="{'border-red-500': errors.responsable}"
                />
                <span v-if="errors.responsable" class="text-xs text-red-400">{{ errors.responsable }}</span>
              </div>

              <!-- Coût Estimé -->
              <div class="space-y-2">
                <label class="block text-sm font-medium text-gray-300">
                  Coût Estimé (XOF)
                </label>
                <InputNumber
                  v-model="form.cout"
                  placeholder="0.00"
                  mode="currency"
                  currency="XOF"
                  locale="fr-FR"
                  class="w-full"
                />
              </div>

              <!-- Observations -->
              <div class="space-y-2">
                <label class="block text-sm font-medium text-gray-300">
                  Observations
                </label>
                <Textarea
                  v-model="form.observations"
                  rows="3"
                  placeholder="Notes additionnelles..."
                  class="w-full"
                />
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
              label="Planifier"
              :loading="isSubmitting"
              @click="handleSubmit"
              class="px-6 bg-gradient-to-r from-pink-500 to-purple-500 hover:from-pink-600 hover:to-purple-600 border-0"
            >
              <template #icon>
                <i class="pi pi-check mr-2"></i>
              </template>
            </Button>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { gsap } from 'gsap'
import MultiSelect from 'primevue/multiselect'
import InputText from 'primevue/inputtext'
import InputNumber from 'primevue/inputnumber'
import Textarea from 'primevue/textarea'
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
  equipements: {
    type: Array,
    default: () => []
  }
})

const emit = defineEmits(['close', 'submit'])

const toast = useToast()
const modalRef = ref(null)
const isSubmitting = ref(false)

// Formulaire
const form = ref({
  equipements: [],
  type: 'préventif',
  responsable: '',
  cout: null,
  observations: ''
})

// Erreurs
const errors = ref({})

// Date formatée
const formattedDate = computed(() => {
  return props.selectedDate ? formatDate(props.selectedDate) : ''
})

// Réinitialiser le formulaire quand la modal s'ouvre
watch(() => props.isOpen, (newVal) => {
  if (newVal) {
    resetForm()
  }
})

// Validation
function validate() {
  errors.value = {}
  
  if (!form.value.equipements || form.value.equipements.length === 0) {
    errors.value.equipements = 'Veuillez sélectionner au moins un équipement'
  }
  
  if (!form.value.responsable) {
    errors.value.responsable = 'Le responsable est requis'
  }
  
  return Object.keys(errors.value).length === 0
}

// Soumission
async function handleSubmit() {
  if (!validate()) {
    toast.add({
      severity: 'error',
      summary: 'Erreur',
      detail: 'Veuillez corriger les erreurs du formulaire',
      life: 3000
    })
    return
  }

  isSubmitting.value = true

  try {
    // Créer une maintenance pour chaque équipement sélectionné
    const maintenances = form.value.equipements.map(equipementId => ({
      equipement_id: equipementId,
      type_maintenance: form.value.type,
      date_prevue: props.selectedDate.toISOString().split('T')[0],
      responsable: form.value.responsable,
      cout: form.value.cout,
      observations: form.value.observations
    }))

    emit('submit', maintenances)
    closeModal()
    
    toast.add({
      severity: 'success',
      summary: 'Succès',
      detail: `${maintenances.length} maintenance(s) planifiée(s)`,
      life: 3000
    })
  } catch (error) {
    toast.add({
      severity: 'error',
      summary: 'Erreur',
      detail: 'Erreur lors de la planification',
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
    equipements: [],
    type: 'préventif',
    responsable: '',
    cout: null,
    observations: ''
  }
  errors.value = {}
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
/* Styles pour PrimeVue avec dark mode */
:deep(.p-multiselect) {
  @apply bg-slate-800/50 border-gray-700 text-gray-200;
}

:deep(.p-multiselect:hover) {
  @apply border-gray-600;
}

:deep(.p-inputtext),
:deep(.p-inputnumber-input),
:deep(.p-textarea) {
  @apply bg-slate-800/50 border-gray-700 text-gray-200;
}

:deep(.p-inputtext:focus),
:deep(.p-inputnumber-input:focus),
:deep(.p-textarea:focus) {
  @apply border-pink-500 ring-2 ring-pink-500/20;
}
</style>
