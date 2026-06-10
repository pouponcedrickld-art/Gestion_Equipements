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
            <label for="equipement_id">Type de matériel</label>
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
            <label for="quantite">Quantité demandée</label>
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
            <label for="urgence">Urgence</label>
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
            <label for="date_souhaitee">Date souhaitée</label>
            <Calendar 
              v-model="form.date_souhaitee" 
              id="date_souhaitee" 
              required
              :minDate="new Date()"
              dateFormat="yy-mm-dd"
              class="w-full"
            />
          </div>

          <!-- Motif -->
          <div class="form-group full-width">
            <label for="motif">Motif de la demande</label>
            <textarea 
              v-model="form.motif" 
              id="motif" 
              rows="4" 
              required
              placeholder="Expliquez pourquoi vous avez besoin de ce matériel..."
            ></textarea>
          </div>
        </div>

        <div class="form-actions">
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

const router = useRouter()
const toast = useToast()
const equipements = ref([])
const submitting = ref(false)
const loadingEquipements = ref(false)
const errorMsg = ref('')

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
    // On récupère tous les équipements pour que l'agence puisse choisir
    // Le filtrage se fera par le gestionnaire lors du traitement
    const res = await equipementApi.index({ 
      all_equipements: true,
      per_page: 100 
    })
    
    if (res.data && res.data.success) {
      // Laravel Paginated response: res.data.data.data
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
  if (!form.value.date_souhaitee) {
    toast.add({ severity: 'warn', summary: 'Attention', detail: 'Veuillez choisir une date.', life: 3000 })
    return
  }

  submitting.value = true
  
  // Formatage de la date pour l'API
  const dateFormatted = form.value.date_souhaitee instanceof Date 
    ? form.value.date_souhaitee.toISOString().split('T')[0]
    : form.value.date_souhaitee

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
  padding: 24px;
  max-width: 900px;
  margin: 0 auto;
}

.header-section {
  display: flex;
  flex-direction: column;
  gap: 12px;
  margin-bottom: 24px;
}

.back-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  color: #3b82f6;
  text-decoration: none;
  font-size: 0.9rem;
}

.back-btn:hover {
  text-decoration: underline;
}

.header-section h2 {
  color: #f8fafc;
  margin: 0;
  font-size: 1.5rem;
}

.demande-form {
  background: #1e293b;
  border: 1px solid #334155;
  border-radius: 12px;
  padding: 24px;
}

.form-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 20px;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.form-group.full-width {
  grid-column: span 2;
}

label {
  color: #94a3b8;
  font-size: 0.9rem;
}

.error-text {
  color: #ef4444;
  font-size: 0.8rem;
  margin-top: 4px;
}

input, select, textarea, :deep(.p-dropdown), :deep(.p-inputnumber-input), :deep(.p-calendar-input) {
  background: #0f172a !important;
  border: 1px solid #334155 !important;
  border-radius: 8px !important;
  color: #f8fafc !important;
}

textarea {
  padding: 10px 12px;
  font-size: 1rem;
  width: 100%;
}

input:focus, select:focus, textarea:focus, :deep(.p-focus) {
  outline: none !important;
  border-color: #3b82f6 !important;
  box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2) !important;
}

:deep(.p-dropdown-label), :deep(.p-inputtext) {
  color: #f8fafc !important;
}

:deep(.p-dropdown-panel) {
  background: #1e293b;
  border: 1px solid #334155;
}

:deep(.p-dropdown-item) {
  color: #f8fafc;
}

:deep(.p-dropdown-item:hover) {
  background: #334155;
}

:deep(.p-dropdown-filter) {
  background: #0f172a;
  color: #f8fafc;
}

.form-actions {
  margin-top: 32px;
  display: flex;
  justify-content: flex-end;
}

.submit-btn {
  background: #3b82f6;
  color: white;
  border: none;
  border-radius: 8px;
  padding: 12px 24px;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 10px;
  cursor: pointer;
  transition: background 0.2s;
}

.submit-btn:hover:not(:disabled) {
  background: #2563eb;
}

.submit-btn:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

@media (max-width: 640px) {
  .form-grid {
    grid-template-columns: 1fr;
  }
  .form-group.full-width {
    grid-column: span 1;
  }
}
</style>
