<template>
  <DirectionLayout>
    <div class="transfert-form-container" ref="pageContainer">
      <div class="page-header animate-in">
        <div class="title-container">
          <div class="flex align-items-center gap-2">
            <Button icon="pi pi-arrow-left" class="p-button-text p-button-rounded p-button-sm" @click="$router.push('/transferts')" />
            <h1>Nouveau Transfert</h1>
          </div>
          <p class="subtitle">Transférer un équipement vers une autre agence</p>
        </div>
      </div>

      <Card class="form-card animate-card">
        <template #title>
          <div class="flex align-items-center gap-2 text-primary">
            <i class="pi pi-send"></i>
            <span class="text-lg">Détails du transfert</span>
          </div>
        </template>
        <template #content>
          <form @submit.prevent="submitForm">
            <div class="form-grid">
              <div class="field full-width">
                <label for="equipement_id">Équipement <span class="required">*</span></label>
                <Dropdown
                  id="equipement_id"
                  v-model="form.equipement_id"
                  :options="equipements"
                  optionLabel="label"
                  optionValue="id"
                  placeholder="Sélectionner un équipement"
                  filter
                  :loading="loadingEquipements"
                  :filterPlaceholder="'Rechercher un équipement...'"
                  class="w-full"
                >
                  <template #option="slotProps">
                    <div class="flex flex-column">
                      <span class="font-bold">{{ slotProps.option.nom || slotProps.option.marque + ' ' + slotProps.option.modele }}</span>
                      <small>SN: {{ slotProps.option.numero_serie }}</small>
                    </div>
                  </template>
                </Dropdown>
              </div>

              <div class="field">
                <label for="agence_destination_id">Agence de destination <span class="required">*</span></label>
                <Dropdown
                  id="agence_destination_id"
                  v-model="form.agence_destination_id"
                  :options="agences"
                  optionLabel="nom"
                  optionValue="id"
                  placeholder="Sélectionner une agence"
                  filter
                  :loading="loadingAgences"
                  class="w-full"
                />
              </div>

              <div class="field">
                <label for="type_transfert">Type de transfert <span class="required">*</span></label>
                <SelectButton
                  id="type_transfert"
                  v-model="form.type_transfert"
                  :options="typesTransfert"
                  optionLabel="label"
                  optionValue="value"
                  class="w-full"
                />
              </div>

              <div class="field full-width">
                <label for="observations">Observations</label>
                <Textarea
                  id="observations"
                  v-model="form.observations"
                  rows="4"
                  placeholder="Observations éventuelles..."
                  :maxlength="1000"
                  class="w-full"
                />
                <small class="text-muted">{{ form.observations.length }}/1000</small>
              </div>
            </div>

            <div class="form-actions">
              <Button label="Annuler" icon="pi pi-times" class="p-button-text p-button-secondary" @click="$router.push('/transferts')" />
              <Button
                label="Créer le transfert"
                icon="pi pi-send"
                class="p-button-primary"
                :loading="submitting"
                type="submit"
              />
            </div>
          </form>
        </template>
      </Card>
    </div>
  </DirectionLayout>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useToast } from 'primevue/usetoast'
import DirectionLayout from '@/layouts/DirectionLayout.vue'
import transfertApi from '@/api/transfertApi'
import equipementApi from '@/api/equipementApi'
import agenceApi from '@/api/agenceApi'
import gsap from 'gsap'

import Card from 'primevue/card'
import Button from 'primevue/button'
import Dropdown from 'primevue/dropdown'
import SelectButton from 'primevue/selectbutton'
import Textarea from 'primevue/textarea'

const router = useRouter()
const toast = useToast()

const submitting = ref(false)
const loadingEquipements = ref(false)
const loadingAgences = ref(false)
const equipements = ref([])
const agences = ref([])

const form = reactive({
  equipement_id: null,
  agence_destination_id: null,
  type_transfert: 'livraison_generale',
  observations: ''
})

const typesTransfert = [
  { label: 'Livraison générale', value: 'livraison_generale' },
  { label: 'Retour générale', value: 'retour_generale' },
  { label: 'Transfert interne', value: 'transfert_interne' }
]

const submitForm = async () => {
  if (!form.equipement_id) {
    toast.add({ severity: 'warn', summary: 'Attention', detail: 'Veuillez sélectionner un équipement.', life: 3000 })
    return
  }
  if (!form.agence_destination_id) {
    toast.add({ severity: 'warn', summary: 'Attention', detail: 'Veuillez sélectionner une agence de destination.', life: 3000 })
    return
  }

  submitting.value = true
  try {
    const res = await transfertApi.store(form)
    if (res.data.success) {
      toast.add({ severity: 'success', summary: 'Succès', detail: 'Transfert créé avec succès', life: 3000 })
      router.push('/transferts')
    } else {
      throw new Error(res.data.message || 'Erreur lors de la création')
    }
  } catch (err) {
    toast.add({ severity: 'error', summary: 'Erreur', detail: err.response?.data?.message || err.message, life: 5000 })
  } finally {
    submitting.value = false
  }
}

onMounted(async () => {
  loadingEquipements.value = true
  loadingAgences.value = true
  try {
    const [eqRes, agRes] = await Promise.all([
      equipementApi.getDisponiblesTransfert(),
      agenceApi.index()
    ])
    if (eqRes.data.success) {
      const rawData = eqRes.data.data.data || eqRes.data.data
      equipements.value = rawData.map(e => ({
        ...e,
        label: `${e.nom || e.marque + ' ' + e.modele} (SN: ${e.numero_serie})`
      }))
    }
    if (agRes.data.success) {
      agences.value = agRes.data.data
    }
  } catch (err) {
    toast.add({ severity: 'error', summary: 'Erreur', detail: 'Impossible de charger les données', life: 3000 })
  } finally {
    loadingEquipements.value = false
    loadingAgences.value = false
  }

  gsap.from('.animate-in', { opacity: 0, y: 20, duration: 0.8, stagger: 0.2, ease: 'power3.out' })
  gsap.from('.animate-card', { opacity: 0, y: 30, duration: 0.8, delay: 0.2, ease: 'power2.out' })
})
</script>

<style scoped lang="scss">
.transfert-form-container {
  padding: 2rem;
  max-width: 900px;
  margin: 0 auto;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;

  .title-container {
    h1 {
      margin: 0;
      color: #1e293b;
      font-size: 1.4rem;
      font-weight: 800;
    }
  }

  .subtitle {
    color: #64748b;
    margin: 0.25rem 0 0 0;
    font-size: 0.85rem;
  }
}

.form-card {
  border-radius: 12px;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.05);
  border: none;
}

.form-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1.5rem;
}

.field {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;

  &.full-width {
    grid-column: span 2;
  }

  label {
    color: #475569;
    font-size: 0.9rem;
    font-weight: 600;
  }

  .required {
    color: #ef4444;
  }

  .text-muted {
    color: #94a3b8;
    font-size: 0.75rem;
    text-align: right;
  }
}

.form-actions {
  margin-top: 2rem;
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  padding-top: 1.5rem;
  border-top: 1px solid #e2e8f0;
}

// PrimeVue Component Overrides
:deep(.p-select),
:deep(.p-textarea) {
  width: 100% !important;
}

:deep(.p-select),
:deep(.p-textarea) {
  background: #ffffff !important;
  border: 1px solid #cbd5e1 !important;
  border-radius: 8px !important;
  color: #1e293b !important;
  padding: 0.75rem 1rem !important;
  font-size: 0.95rem !important;
}

:deep(.p-select-label) {
  color: #1e293b !important;
}

:deep(.p-select).p-focus,
:deep(.p-textarea):focus {
  outline: none !important;
  border-color: #3b82f6 !important;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1) !important;
}

:deep(.p-select-overlay) {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
}

:deep(.p-select-option) {
  color: #1e293b;
  padding: 0.75rem 1rem;
}

:deep(.p-select-option:hover) {
  background: #f1f5f9;
}

:deep(.p-select-option.p-select-option-selected) {
  background: #dbeafe;
  color: #1e40af;
}

:deep(.p-select-filter) {
  background: #ffffff;
  color: #1e293b;
  border: 1px solid #e2e8f0;
}

:deep(.p-selectbutton) {
  .p-button {
    background: #f1f5f9;
    border: 1px solid #cbd5e1;
    color: #64748b;
    font-weight: 600;
    font-size: 0.85rem;
    padding: 0.75rem 1rem;

    &.p-highlight {
      background: #3b82f6;
      border-color: #3b82f6;
      color: #ffffff;
    }
  }
}

@media (max-width: 768px) {
  .transfert-form-container {
    padding: 1.25rem;
  }

  .form-grid {
    grid-template-columns: 1fr;
  }

  .field.full-width {
    grid-column: span 1;
  }

  .form-actions {
    flex-direction: column;

    :deep(.p-button) {
      width: 100%;
    }
  }
}
</style>
