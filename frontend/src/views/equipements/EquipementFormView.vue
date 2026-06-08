<template>
  <MainLayout>
    <div class="equipement-form-container" ref="pageContainer">
      <!-- En-tête Moderne -->
      <div class="form-header animate-header">
        <div class="header-content">
          <Button 
            icon="pi pi-arrow-left" 
            class="p-button-text p-button-rounded back-btn" 
            @click="$router.back()" 
          />
          <div class="title-section">
            <h1>{{ isEditing ? 'Mise à jour de l\'unité' : 'Nouvel Équipement' }}</h1>
            <p>{{ isEditing ? 'Modifier les spécifications de cet équipement' : 'Enregistrement d\'un nouveau matériel dans le parc' }}</p>
          </div>
        </div>
        
        <!-- Stepper Progress -->
        <div class="stepper-progress">
          <div v-for="step in 3" :key="step" class="step-item" :class="{ active: currentStep >= step, current: currentStep === step }">
            <div class="step-number">{{ step }}</div>
            <span class="step-label">{{ stepLabels[step-1] }}</span>
          </div>
          <div class="progress-line">
            <div class="line-fill" :style="{ width: `${(currentStep - 1) * 50}%` }"></div>
          </div>
        </div>
      </div>

      <div class="form-content-wrapper">
        <Card class="modern-form-card animate-card">
          <template #content>
            <form @submit.prevent="handleSubmit">
              
              <!-- Étape 1: Identification -->
              <div v-show="currentStep === 1" class="step-content step-1">
                <div class="section-info">
                  <i class="pi pi-id-card"></i>
                  <h3>Identification de l'équipement</h3>
                </div>
                
                <div class="grid p-fluid">
                  <div class="field col-12 md:col-6">
                    <label>Référence Interne *</label>
                    <div class="input-wrapper">
                      <i class="pi pi-bookmark"></i>
                      <InputText v-model="form.reference" :class="{ 'p-invalid': errors.reference }" placeholder="EQ-2026-XXXX" />
                    </div>
                    <small class="p-error" v-if="errors.reference">{{ errors.reference[0] }}</small>
                  </div>

                  <div class="field col-12 md:col-6">
                    <label>Code Inventaire *</label>
                    <div class="input-wrapper">
                      <i class="pi pi-barcode"></i>
                      <InputText v-model="form.code_inventaire" :class="{ 'p-invalid': errors.code_inventaire }" placeholder="INV-XXXX" />
                    </div>
                    <small class="p-error" v-if="errors.code_inventaire">{{ errors.code_inventaire[0] }}</small>
                  </div>

                  <div class="field col-12 md:col-6">
                    <label>Numéro de Série *</label>
                    <div class="input-wrapper">
                      <i class="pi pi-hashtag"></i>
                      <InputText v-model="form.numero_serie" :class="{ 'p-invalid': errors.numero_serie }" />
                    </div>
                    <small class="p-error" v-if="errors.numero_serie">{{ errors.numero_serie[0] }}</small>
                  </div>

                  <div class="field col-12 md:col-6">
                    <label>IMEI (Optionnel)</label>
                    <div class="input-wrapper">
                      <i class="pi pi-mobile"></i>
                      <InputText v-model="form.imei" :class="{ 'p-invalid': errors.imei }" />
                    </div>
                  </div>
                </div>
              </div>

              <!-- Étape 2: Détails Techniques & Photo -->
              <div v-show="currentStep === 2" class="step-content step-2">
                <div class="section-info">
                  <i class="pi pi-cog"></i>
                  <h3>Détails techniques & Visuel</h3>
                </div>

                <div class="grid p-fluid">
                  <div class="field col-12 md:col-6">
                    <label>Catégorie *</label>
                    <Dropdown 
                      v-model="form.categorie_id" 
                      :options="categories" 
                      optionLabel="nom" 
                      optionValue="id" 
                      placeholder="Choisir une catégorie"
                      :class="{ 'p-invalid': errors.categorie_id }"
                      filter
                    />
                  </div>
                  <div class="field col-12 md:col-3">
                    <label>Marque</label>
                    <InputText v-model="form.marque" placeholder="Ex: Dell, Apple" />
                  </div>
                  <div class="field col-12 md:col-3">
                    <label>Modèle</label>
                    <InputText v-model="form.modele" placeholder="Ex: Latitude 5420" />
                  </div>

                  <div class="field col-12">
                    <label>Image de l'équipement</label>
                    <div class="modern-upload-zone" :class="{ 'has-preview': photoPreview }">
                      <input type="file" @change="handleFileChange" accept="image/*" class="hidden-input" id="photo-upload" />
                      <label for="photo-upload" class="upload-label" v-if="!photoPreview">
                        <i class="pi pi-cloud-upload"></i>
                        <span>Cliquez ou déposez une photo ici</span>
                        <small>PNG, JPG (Max. 2Mo)</small>
                      </label>
                      <div v-else class="preview-container">
                        <img :src="photoPreview" alt="Aperçu" />
                        <div class="preview-actions">
                          <Button icon="pi pi-trash" class="p-button-rounded p-button-danger" @click="removePhoto" />
                          <label for="photo-upload" class="change-photo-btn">Changer</label>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Étape 3: Acquisition & Localisation -->
              <div v-show="currentStep === 3" class="step-content step-3">
                <div class="section-info">
                  <i class="pi pi-map-marker"></i>
                  <h3>Acquisition & Localisation</h3>
                </div>

                <div class="grid p-fluid">
                  <div class="field col-12 md:col-4">
                    <label>Date d'acquisition</label>
                    <Calendar v-model="form.date_acquisition" dateFormat="dd/mm/yy" showIcon />
                  </div>
                  <div class="field col-12 md:col-4">
                    <label>Prix d'achat</label>
                    <InputNumber v-model="form.prix_achat" mode="currency" currency="XOF" locale="fr-FR" />
                  </div>
                  <div class="field col-12 md:col-4">
                    <label>Fin de garantie</label>
                    <Calendar v-model="form.garantie_date_fin" dateFormat="dd/mm/yy" showIcon />
                  </div>

                  <div class="field col-12 md:col-6">
                    <label>État de l'unité *</label>
                    <Dropdown v-model="form.etat" :options="etatOptions" optionLabel="label" optionValue="value" />
                  </div>
                  <div class="field col-12 md:col-6">
                    <label>Localisation précise</label>
                    <InputText v-model="form.localisation" placeholder="Bureau, Étage, Rack..." />
                  </div>
                </div>
              </div>

              <!-- Actions de navigation -->
              <div class="form-actions-footer">
                <Button 
                  v-if="currentStep > 1" 
                  label="Précédent" 
                  icon="pi pi-chevron-left" 
                  class="p-button-text p-button-secondary" 
                  @click="prevStep" 
                />
                <div class="spacer"></div>
                <Button 
                  v-if="currentStep < 3" 
                  label="Suivant" 
                  icon="pi pi-chevron-right" 
                  iconPos="right" 
                  @click="nextStep" 
                />
                <Button 
                  v-else 
                  type="submit" 
                  :label="isEditing ? 'Mettre à jour' : 'Enregistrer'" 
                  icon="pi pi-check" 
                  class="p-button-success p-button-raised"
                  :loading="loading"
                />
              </div>

            </form>
          </template>
        </Card>
      </div>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useToast } from 'primevue/usetoast'
import MainLayout from '@/layouts/MainLayout.vue'
import { useEquipementStore } from '@/stores/equipementStore'
import { useCategorieStore } from '@/stores/categorieStore'
import gsap from 'gsap'

// PrimeVue Components
import Button from 'primevue/button'
import Card from 'primevue/card'
import InputText from 'primevue/inputtext'
import InputNumber from 'primevue/inputnumber'
import Dropdown from 'primevue/dropdown'
import Calendar from 'primevue/calendar'

const route = useRoute()
const router = useRouter()
const toast = useToast()
const equipementStore = useEquipementStore()
const categorieStore = useCategorieStore()

const currentStep = ref(1)
const stepLabels = ['Identification', 'Spécifications', 'Logistique']
const isEditing = computed(() => route.params.id !== undefined)
const loading = ref(false)
const errors = ref({})

const form = ref({
  reference: '',
  numero_serie: '',
  imei: '',
  code_inventaire: '',
  marque: '',
  modele: '',
  categorie_id: null,
  fournisseur: '',
  date_acquisition: null,
  prix_achat: null,
  garantie_date_fin: null,
  etat: 'neuf',
  localisation: '',
  photo: null
})

const photoPreview = ref(null)

const etatOptions = [
  { label: 'Neuf', value: 'neuf' },
  { label: 'En service', value: 'en_service' },
  { label: 'En panne', value: 'en_panne' },
  { label: 'En maintenance', value: 'en_maintenance' },
  { label: 'Réformé', value: 'reforme' },
  { label: 'Perdu', value: 'perdu' }
]

const categories = computed(() => categorieStore.categories)

// Navigation Stepper avec GSAP
const nextStep = () => {
  if (currentStep.value < 3) {
    gsap.to(`.step-${currentStep.value}`, { opacity: 0, x: -20, duration: 0.3, onComplete: () => {
      currentStep.value++
      gsap.fromTo(`.step-${currentStep.value}`, { opacity: 0, x: 20 }, { opacity: 1, x: 0, duration: 0.4 })
    }})
  }
}

const prevStep = () => {
  if (currentStep.value > 1) {
    gsap.to(`.step-${currentStep.value}`, { opacity: 0, x: 20, duration: 0.3, onComplete: () => {
      currentStep.value--
      gsap.fromTo(`.step-${currentStep.value}`, { opacity: 0, x: -20 }, { opacity: 1, x: 0, duration: 0.4 })
    }})
  }
}

const handleFileChange = (event) => {
  const file = event.target.files[0]
  if (file) {
    form.value.photo = file
    const reader = new FileReader()
    reader.onload = (e) => photoPreview.value = e.target.result
    reader.readAsDataURL(file)
  }
}

const removePhoto = () => {
  form.value.photo = null
  photoPreview.value = null
}

const handleSubmit = async () => {
  loading.value = true
  errors.value = {}

  try {
    const payload = { ...form.value }
    if (payload.date_acquisition instanceof Date) payload.date_acquisition = payload.date_acquisition.toISOString().split('T')[0]
    if (payload.garantie_date_fin instanceof Date) payload.garantie_date_fin = payload.garantie_date_fin.toISOString().split('T')[0]

    if (isEditing.value) {
      await equipementStore.updateEquipement(route.params.id, payload)
      toast.add({ severity: 'success', summary: 'Succès', detail: 'Unité mise à jour', life: 3000 })
    } else {
      await equipementStore.createEquipement(payload)
      toast.add({ severity: 'success', summary: 'Succès', detail: 'Unité créée avec succès', life: 3000 })
    }
    router.push('/equipements')
  } catch (error) {
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors
      // Retourner à l'étape 1 s'il y a des erreurs d'identification
      if (errors.value.reference || errors.value.numero_serie || errors.value.code_inventaire) {
        currentStep.value = 1
      }
    } else {
      toast.add({ severity: 'error', summary: 'Erreur', detail: error.message || 'Une erreur est survenue', life: 5000 })
    }
  } finally {
    loading.value = false
  }
}

onMounted(async () => {
  await categorieStore.fetchCategories({ per_page: 100 })
  
  // Animations initiales
  gsap.from('.animate-header', { opacity: 0, y: -30, duration: 0.8, ease: 'power3.out' })
  gsap.from('.animate-card', { opacity: 0, scale: 0.95, duration: 0.6, delay: 0.2, ease: 'back.out(1.7)' })

  if (isEditing.value) {
    loading.value = true
    try {
      const equipement = await equipementStore.fetchEquipement(route.params.id)
      form.value = {
        reference: equipement.reference,
        numero_serie: equipement.numero_serie,
        imei: equipement.imei || '',
        code_inventaire: equipement.code_inventaire,
        marque: equipement.marque || '',
        modele: equipement.modele || '',
        categorie_id: equipement.categorie_id,
        fournisseur: equipement.fournisseur || '',
        date_acquisition: equipement.date_acquisition ? new Date(equipement.date_acquisition) : null,
        prix_achat: equipement.prix_achat ? parseFloat(equipement.prix_achat) : null,
        garantie_date_fin: equipement.garantie_date_fin ? new Date(equipement.garantie_date_fin) : null,
        etat: equipement.etat,
        localisation: equipement.localisation || '',
        photo: null
      }
      if (equipement.photo) {
        photoPreview.value = `${import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000'}/storage/${equipement.photo}`
      }
    } catch (error) {
      toast.add({ severity: 'error', summary: 'Erreur', detail: 'Impossible de charger les données', life: 3000 })
      router.push('/equipements')
    } finally {
      loading.value = false
    }
  }
})
</script>

<style scoped lang="scss">
.equipement-form-container {
  padding: 2.5rem;
  max-width: 1000px;
  margin: 0 auto;
}

.form-header {
  margin-bottom: 3rem;
  
  .header-content {
    display: flex;
    align-items: center;
    gap: 1.5rem;
    margin-bottom: 2.5rem;
    
    .back-btn {
      width: 45px;
      height: 45px;
      background: white;
      box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
      color: #64748b;
      &:hover { color: #3b82f6; transform: translateX(-3px); }
    }
    
    .title-section {
      h1 { font-size: 2.25rem; font-weight: 800; color: #1e293b; margin: 0; letter-spacing: -0.02em; }
      p { color: #64748b; margin: 0.25rem 0 0 0; font-size: 1.1rem; }
    }
  }
}

/* Stepper Modern */
.stepper-progress {
  display: flex;
  justify-content: space-between;
  position: relative;
  padding: 0 1rem;
  
  .step-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    z-index: 2;
    gap: 0.75rem;
    
    .step-number {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      background: white;
      border: 2px solid #e2e8f0;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 700;
      color: #94a3b8;
      transition: all 0.4s ease;
    }
    
    .step-label {
      font-size: 0.85rem;
      font-weight: 600;
      color: #94a3b8;
      transition: all 0.4s ease;
    }
    
    &.active {
      .step-number { border-color: #3b82f6; color: #3b82f6; }
      .step-label { color: #3b82f6; }
    }
    
    &.current {
      .step-number { background: #3b82f6; color: white; box-shadow: 0 0 0 5px rgba(59, 130, 246, 0.15); }
      .step-label { color: #1e293b; font-weight: 700; }
    }
  }
  
  .progress-line {
    position: absolute;
    top: 20px;
    left: 45px;
    right: 45px;
    height: 2px;
    background: #e2e8f0;
    z-index: 1;
    
    .line-fill {
      height: 100%;
      background: #3b82f6;
      transition: width 0.6s cubic-bezier(0.4, 0, 0.2, 1);
    }
  }
}

.modern-form-card {
  background: white;
  border-radius: 30px;
  border: 1px solid #f1f5f9;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.05);
  overflow: hidden;
  
  :deep(.p-card-body) { padding: 2.5rem; }
}

.section-info {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-bottom: 2.5rem;
  padding-bottom: 1rem;
  border-bottom: 1px solid #f1f5f9;
  
  i { font-size: 1.5rem; color: #3b82f6; }
  h3 { font-size: 1.25rem; font-weight: 700; color: #1e293b; margin: 0; }
}

.input-wrapper {
  position: relative;
  display: flex;
  align-items: center;
  
  i { position: absolute; left: 1rem; color: #94a3b8; z-index: 1; }
  
  :deep(.p-inputtext) {
    padding-left: 2.75rem;
    border-radius: 12px;
    background: #f8fafc;
    border-color: #e2e8f0;
    height: 3rem;
    
    &:focus { border-color: #3b82f6; background: white; }
  }
}

.field label {
  display: block;
  font-weight: 600;
  color: #475569;
  margin-bottom: 0.75rem;
  font-size: 0.95rem;
}

/* Upload Zone Modern */
.modern-upload-zone {
  border: 2px dashed #e2e8f0;
  border-radius: 20px;
  min-height: 200px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #f8fafc;
  transition: all 0.3s ease;
  position: relative;
  
  &:hover { border-color: #3b82f6; background: #f0f7ff; }
  
  .hidden-input { position: absolute; opacity: 0; width: 100%; height: 100%; cursor: pointer; }
  
  .upload-label {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
    color: #64748b;
    
    i { font-size: 2.5rem; color: #3b82f6; }
    span { font-weight: 600; font-size: 1.1rem; }
    small { color: #94a3b8; }
  }
  
  .preview-container {
    width: 100%;
    height: 100%;
    position: relative;
    padding: 1rem;
    display: flex;
    justify-content: center;
    
    img { max-height: 300px; border-radius: 15px; object-fit: contain; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); }
    
    .preview-actions {
      position: absolute;
      top: 2rem;
      right: 2rem;
      display: flex;
      flex-direction: column;
      gap: 0.75rem;
      
      .change-photo-btn {
        background: white;
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 700;
        cursor: pointer;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        text-align: center;
        &:hover { background: #f8fafc; }
      }
    }
  }
}

.form-actions-footer {
  margin-top: 3rem;
  padding-top: 2rem;
  border-top: 1px solid #f1f5f9;
  display: flex;
  align-items: center;
  
  .spacer { flex: 1; }
  
  :deep(.p-button) { border-radius: 12px; padding: 0.75rem 2rem; font-weight: 700; }
}

:deep(.p-dropdown), :deep(.p-calendar-w-btn .p-inputtext), :deep(.p-inputnumber-input) {
  border-radius: 12px;
  background: #f8fafc;
  border-color: #e2e8f0;
  height: 3rem;
}

@media (max-width: 768px) {
  .equipement-form-container { padding: 1.5rem; }
  .stepper-progress .step-label { display: none; }
  .stepper-progress .progress-line { left: 25px; right: 25px; }
}
</style>