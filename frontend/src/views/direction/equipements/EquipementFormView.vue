<template>
  <DirectionLayout>
    <div class="equipement-form-container" ref="pageContainer">
      <div class="form-header animate-header">
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

      <div class="form-content-wrapper">
        <Card class="modern-form-card animate-card">
          <template #content>
            <form @submit.prevent="handleSubmit">
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
  </DirectionLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useToast } from 'primevue/usetoast'
import DirectionLayout from '@/layouts/DirectionLayout.vue'
import { useEquipementStore } from '@/stores/equipementStore'
import { useCategorieStore } from '@/stores/categorieStore'

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

const nextStep = () => {
  if (currentStep.value < 3) {
    currentStep.value++
  }
}

const prevStep = () => {
  if (currentStep.value > 1) {
    currentStep.value--
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
        const apiBaseUrl = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000'
        photoPreview.value = `${apiBaseUrl}/storage/${equipement.photo}`
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
  display: flex;
  align-items: center;
  gap: 1.5rem;
}

.form-header .title-section h1 {
  font-size: 2.25rem;
  font-weight: 800;
  color: #e2e8f0;
  margin: 0 0 0.5rem 0;
}

.form-header .title-section p {
  color: #94a3b8;
  margin: 0;
  font-size: 1.1rem;
}

.modern-form-card {
  background: #1e293b;
  border-radius: 30px;
  border: 1px solid #334155;
}

.section-info {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-bottom: 2.5rem;
  padding-bottom: 1rem;
  border-bottom: 1px solid #334155;
}

.section-info i {
  font-size: 1.5rem;
  color: #3b82f6;
}

.section-info h3 {
  font-size: 1.25rem;
  font-weight: 700;
  color: #e2e8f0;
  margin: 0;
}

.input-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}

.input-wrapper i {
  position: absolute;
  left: 1rem;
  color: #94a3b8;
  z-index: 1;
}

.input-wrapper :deep(.p-inputtext) {
  padding-left: 2.75rem;
  border-radius: 12px;
  background: #0f172a;
  border-color: #334155;
  height: 3rem;
}

.input-wrapper :deep(.p-inputtext:focus) {
  border-color: #3b82f6;
  background: #0f172a;
}

.field label {
  display: block;
  font-weight: 600;
  color: #e2e8f0;
  margin-bottom: 0.75rem;
  font-size: 0.95rem;
}

.modern-upload-zone {
  border: 2px dashed #334155;
  border-radius: 20px;
  min-height: 200px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #0f172a;
  transition: all 0.3s ease;
  position: relative;
}

.modern-upload-zone:hover {
  border-color: #3b82f6;
  background: #0f172a;
}

.modern-upload-zone .hidden-input {
  position: absolute;
  opacity: 0;
  width: 100%;
  height: 100%;
  cursor: pointer;
}

.modern-upload-zone .upload-label {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.5rem;
  color: #94a3b8;
}

.modern-upload-zone .upload-label i {
  font-size: 2.5rem;
  color: #3b82f6;
}

.modern-upload-zone .upload-label span {
  font-weight: 600;
  font-size: 1.1rem;
}

.modern-upload-zone .preview-container {
  width: 100%;
  height: 100%;
  position: relative;
  padding: 1rem;
  display: flex;
  justify-content: center;
}

.modern-upload-zone .preview-container img {
  max-height: 300px;
  border-radius: 15px;
  object-fit: contain;
}

.modern-upload-zone .preview-container .preview-actions {
  position: absolute;
  top: 2rem;
  right: 2rem;
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.modern-upload-zone .preview-container .preview-actions .change-photo-btn {
  background: #1e293b;
  color: #e2e8f0;
  padding: 0.5rem 1rem;
  border-radius: 20px;
  font-size: 0.85rem;
  font-weight: 700;
  cursor: pointer;
  text-align: center;
}

.form-actions-footer {
  margin-top: 3rem;
  padding-top: 2rem;
  border-top: 1px solid #334155;
  display: flex;
  align-items: center;
}

.form-actions-footer .spacer {
  flex: 1;
}

:deep(.p-dropdown),
:deep(.p-calendar-w-btn .p-inputtext),
:deep(.p-inputnumber-input) {
  border-radius: 12px;
  background: #0f172a;
  border-color: #334155;
  height: 3rem;
  color: #e2e8f0;
}

:deep(.p-dropdown-item) {
  color: #1e293b;
}

@media (max-width: 768px) {
  .equipement-form-container {
    padding: 1.5rem;
  }
}
</style>
