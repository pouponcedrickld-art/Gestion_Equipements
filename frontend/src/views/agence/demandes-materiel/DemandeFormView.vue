<template>
  <AgenceLayout>
    <div class="demande-form-container">
      <div class="header-section">
        <router-link to="/demandes-materiel" class="back-btn">
          <i class="pi pi-arrow-left"></i> Retour à la liste
        </router-link>
        <h2>Nouvelle Demande de Matériel</h2>
      </div>

      <form @submit.prevent="submitForm" class="demande-form">
        <div class="form-grid">
          <!-- Type de matériel -->
          <div class="form-group full-width">
            <label for="equipement_id">Type de matériel <span class="required">*</span></label>
            <Dropdown
              v-model="form.equipement_id"
              :options="equipements"
              optionLabel="label"
              optionValue="id"
              placeholder="Sélectionner un matériel au stock"
              filter
              class="w-full"
              :loading="loadingEquipements"
              required
            >
              <template #option="slotProps">
                <div class="flex flex-column">
                  <span class="font-bold">{{ slotProps.option.nom }}</span>
                  <small>{{ slotProps.option.marque }} {{ slotProps.option.modele }} ({{ slotProps.option.reference }})</small>
                </div>
              </template>
            </Dropdown>
            <small v-if="errorMsg" class="error-text">
              {{ errorMsg }}
            </small>
          </div>

          <!-- Quantité demandée -->
          <div class="form-group">
            <label for="quantite">Quantité demandée <span class="required">*</span></label>
            <InputNumber 
              v-model="form.quantite" 
              id="quantite" 
              :min="1" 
              showButtons
              buttonLayout="horizontal"
              decrementButtonClass="p-button-secondary"
              incrementButtonClass="p-button-primary"
              incrementButtonIcon="pi pi-plus"
              decrementButtonIcon="pi pi-minus"
              required
              class="w-full"
            />
          </div>

          <!-- Urgence -->
          <div class="form-group">
            <label for="urgence">Urgence <span class="required">*</span></label>
            <Dropdown
              v-model="form.urgence"
              :options="urgenceOptions"
              optionLabel="label"
              optionValue="value"
              placeholder="Sélectionner l'urgence"
              class="w-full"
              required
            />
          </div>

          <!-- Date souhaitée -->
          <div class="form-group">
            <label for="date_souhaitee">Date souhaitée <span class="required">*</span></label>
            <Calendar 
              v-model="form.date_souhaitee" 
              id="date_souhaitee" 
              required
              :minDate="new Date()"
              dateFormat="yy-mm-dd"
              class="w-full"
              showIcon
            />
          </div>

          <!-- Motif -->
          <div class="form-group full-width">
            <label for="motif">Motif de la demande <span class="required">*</span></label>
            <Textarea 
              v-model="form.motif" 
              id="motif" 
              :rows="5" 
              required
              placeholder="Expliquez pourquoi vous avez besoin de ce matériel..."
              class="w-full"
            />
          </div>
        </div>

        <div class="form-actions">
          <router-link to="/demandes-materiel" class="cancel-btn">
            Annuler
          </router-link>
          <button type="submit" class="submit-btn" :disabled="submitting">
            <i class="pi pi-check-circle" v-if="!submitting"></i>
            <i class="pi pi-spin pi-spinner" v-else></i>
            {{ submitting ? 'Envoi en cours...' : 'Envoyer la demande' }}
          </button>
        </div>
      </form>
    </div>
  </AgenceLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useToast } from 'primevue/usetoast'
import AgenceLayout from '@/layouts/AgenceLayout.vue'
import equipementApi from '@/api/equipementApi'
import demandeAgenceApi from '@/api/demandeAgenceApi'

// PrimeVue components
import Dropdown from 'primevue/dropdown'
import InputNumber from 'primevue/inputnumber'
import Calendar from 'primevue/calendar'
import Textarea from 'primevue/textarea'

const router = useRouter()
const toast = useToast()
const equipements = ref([])
const submitting = ref(false)
const loadingEquipements = ref(false)
const errorMsg = ref('')

// Options d'urgence
const urgenceOptions = [
  { label: 'Basse', value: 'Basse' },
  { label: 'Moyenne', value: 'Moyenne' },
  { label: 'Haute', value: 'Haute' }
]

const form = ref({
  equipement_id: '',
  quantite: 1,
  urgence: 'Basse',
  motif: '',
  date_souhaitee: null
})

// Charge les équipements disponibles
onMounted(async () => {
  loadingEquipements.value = true
  try {
    const res = await equipementApi.index({ 
      all_equipements: true,
      per_page: 100 
    })
    
    if (res.data && res.data.success) {
      const rawData = res.data.data.data || res.data.data
      equipements.value = rawData.map(eq => ({
        ...eq,
        label: `${eq.nom} (${eq.reference})`
      }))
      
      if (equipements.value.length === 0) {
        errorMsg.value = "Aucun matériel n'est répertorié dans le système."
      }
    }
  } catch (error) {
    console.error('Erreur lors du chargement des équipements', error)
    errorMsg.value = "Erreur de connexion lors du chargement des équipements."
    toast.add({ severity: 'error', summary: 'Erreur', detail: errorMsg.value, life: 3000 })
  } finally {
    loadingEquipements.value = false
  }
})

// Soumet le formulaire
const submitForm = async () => {
  if (!form.value.equipement_id) {
    toast.add({ severity: 'warn', summary: 'Attention', detail: 'Veuillez sélectionner un matériel.', life: 3000 })
    return
  }
  if (!form.value.date_souhaitee) {
    toast.add({ severity: 'warn', summary: 'Attention', detail: 'Veuillez choisir une date.', life: 3000 })
    return
  }
  if (!form.value.motif.trim()) {
    toast.add({ severity: 'warn', summary: 'Attention', detail: 'Veuillez indiquer le motif de la demande.', life: 3000 })
    return
  }

  submitting.value = true
  
  let dateFormatted = form.value.date_souhaitee
  if (form.value.date_souhaitee instanceof Date) {
    const year = form.value.date_souhaitee.getFullYear()
    const month = String(form.value.date_souhaitee.getMonth() + 1).padStart(2, '0')
    const day = String(form.value.date_souhaitee.getDate()).padStart(2, '0')
    dateFormatted = `${year}-${month}-${day}`
  }

  // Envoi de la demande
  try {
    await demandeAgenceApi.store({
      ...form.value,
      date_souhaitee: dateFormatted
    })
    toast.add({ severity: 'success', summary: 'Succès', detail: 'Demande envoyée avec succès', life: 3000 })
    setTimeout(() => {
      router.push('/demandes-materiel')
    }, 1500)
  } catch (error) {
    console.error('Erreur lors de la création de la demande', error)
    const message = error.response?.data?.message || 'Une erreur est survenue lors de la création de la demande.'
    toast.add({ severity: 'error', summary: 'Erreur', detail: message, life: 5000 })
  } finally {
    submitting.value = false
  }
}
</script>

<style scoped>
.demande-form-container {
  padding: 2rem;
  max-width: 900px;
  margin: 0 auto;
}

.header-section {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  margin-bottom: 2rem;
}

.back-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  color: #3b82f6;
  text-decoration: none;
  font-size: 0.9rem;
  font-weight: 500;
  transition: color 0.2s;
}

.back-btn:hover {
  color: #2563eb;
  text-decoration: underline;
}

.header-section h2 {
  color: #1e293b;
  margin: 0;
  font-size: 1.75rem;
  font-weight: 700;
}

.demande-form {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 16px;
  padding: 2.5rem;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

.form-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1.5rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.form-group.full-width {
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

.error-text {
  color: #ef4444;
  font-size: 0.8rem;
  margin-top: 0.25rem;
  font-weight: 500;
}

.form-actions {
  margin-top: 2.5rem;
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  padding-top: 1.5rem;
  border-top: 1px solid #e2e8f0;
}

.cancel-btn {
  padding: 0.75rem 1.5rem;
  border-radius: 8px;
  font-weight: 600;
  text-decoration: none;
  color: #475569;
  background: #f1f5f9;
  border: 1px solid #e2e8f0;
  transition: all 0.2s;
  display: inline-flex;
  align-items: center;
}

.cancel-btn:hover {
  background: #e2e8f0;
  color: #1e293b;
}

.submit-btn {
  background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
  color: white;
  border: none;
  border-radius: 8px;
  padding: 0.75rem 1.5rem;
  font-weight: 600;
  font-size: 1rem;
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  cursor: pointer;
  transition: all 0.2s;
  box-shadow: 0 4px 6px -1px rgba(59, 130, 246, 0.2);
}

.submit-btn:hover:not(:disabled) {
  transform: translateY(-1px);
  box-shadow: 0 10px 15px -3px rgba(59, 130, 246, 0.3);
}

.submit-btn:disabled {
  opacity: 0.7;
  cursor: not-allowed;
  transform: none;
}

/* PrimeVue Component Styling */
:deep(.p-select),
:deep(.p-inputnumber),
:deep(.p-datepicker),
:deep(.p-textarea) {
  width: 100% !important;
}

:deep(.p-select),
:deep(.p-inputnumber-input),
:deep(.p-datepicker-input),
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

:deep(.p-inputnumber-button) {
  background: #f1f5f9 !important;
  border: 1px solid #cbd5e1 !important;
}

:deep(.p-inputnumber-button:hover) {
  background: #e2e8f0 !important;
}

:deep(.p-datepicker-dropdown) {
  background: #3b82f6 !important;
  border: 1px solid #3b82f6 !important;
  color: white !important;
}

:deep(.p-datepicker-dropdown:hover) {
  background: #2563eb !important;
}

:deep(.p-focus),
:deep(.p-select.p-focus),
:deep(.p-inputnumber-input:focus),
:deep(.p-datepicker-input:focus),
:deep(.p-textarea:focus) {
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

@media (max-width: 768px) {
  .demande-form-container {
    padding: 1.25rem;
  }
  
  .demande-form {
    padding: 1.5rem;
  }
  
  .form-grid {
    grid-template-columns: 1fr;
  }
  
  .form-group.full-width {
    grid-column: span 1;
  }
  
  .form-actions {
    flex-direction: column;
  }
  
  .cancel-btn,
  .submit-btn {
    width: 100%;
    justify-content: center;
  }
}
</style>
