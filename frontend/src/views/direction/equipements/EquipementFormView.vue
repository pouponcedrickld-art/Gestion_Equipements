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
            <form @submit.prevent="handleSubmit" class="p-fluid">
              <div class="grid">
                <!-- Informations de base -->
                <div class="col-12 md:col-6">
                  <div class="field">
                    <label class="font-bold">Nom de l'équipement *</label>
                    <InputText v-model="form.nom" :class="{ 'p-invalid': errors.nom }" placeholder="Ex: Ordinateur HP..." />
                    <small class="p-error" v-if="errors.nom">{{ errors.nom[0] }}</small>
                  </div>
                </div>

                <div class="col-12 md:col-6">
                  <div class="field">
                    <label class="font-bold">Numéro de Série *</label>
                    <InputText v-model="form.numero_serie" :class="{ 'p-invalid': errors.numero_serie }" placeholder="S/N unique" />
                    <small class="p-error" v-if="errors.numero_serie">{{ errors.numero_serie[0] }}</small>
                  </div>
                </div>

                <div class="col-12 md:col-6">
                  <div class="field">
                    <label class="font-bold">Catégorie *</label>
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
                </div>

                <div class="col-12 md:col-6">
                  <div class="field">
                    <label class="font-bold">État actuel *</label>
                    <Dropdown v-model="form.etat" :options="etatOptions" optionLabel="label" optionValue="value" />
                  </div>
                </div>

                <!-- Attributs Spécifiques (Genres) -->
                <div v-if="selectedCategoryAttributes.length > 0" class="col-12">
                  <div class="specific-attrs-box p-4 border-round bg-blue-50 border-1 border-blue-100 mb-4">
                    <h3 class="text-blue-800 mt-0 mb-3 flex align-items-center gap-2">
                      <i class="pi pi-list"></i>
                      Détails pour {{ selectedCategoryName }}
                    </h3>
                    <div class="grid">
                      <div v-for="attr in selectedCategoryAttributes" :key="attr.nom" class="col-12 md:col-4">
                        <div class="field mb-0">
                          <label class="font-medium">{{ attr.nom }}</label>
                          <div v-if="attr.type === 'texte'">
                            <InputText v-model="form.specifications[attr.nom]" />
                          </div>
                          <div v-else-if="attr.type === 'nombre'">
                            <InputNumber v-model="form.specifications[attr.nom]" class="w-full" />
                          </div>
                          <div v-else-if="attr.type === 'date'">
                            <Calendar v-model="form.specifications[attr.nom]" dateFormat="dd/mm/yy" class="w-full" />
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Autres infos importantes -->
                <div class="col-12 md:col-6">
                  <div class="field">
                    <label class="font-bold">Localisation</label>
                    <InputText v-model="form.localisation" placeholder="Bâtiment, bureau..." />
                  </div>
                </div>

                <div class="col-12 md:col-6">
                  <div class="field">
                    <label class="font-bold">Responsable</label>
                    <Dropdown 
                      v-model="form.responsable_id" 
                      :options="users" 
                      optionLabel="name" 
                      optionValue="id" 
                      placeholder="Assigner à..."
                      filter
                      showClear
                    />
                  </div>
                </div>

                <div class="col-12">
                  <div class="field">
                    <label class="font-bold">Photo de l'équipement</label>
                    <div class="flex align-items-center gap-4">
                      <div class="photo-preview-small" v-if="photoPreview">
                        <img :src="photoPreview" alt="Aperçu" class="w-6rem h-6rem border-round shadow-1 object-cover" />
                        <Button icon="pi pi-times" class="p-button-rounded p-button-danger p-button-text -mt-2 -ml-2" @click="removePhoto" />
                      </div>
                      <div class="upload-btn-wrapper">
                        <input type="file" @change="handleFileChange" accept="image/*" class="hidden" id="photo-input" />
                        <label for="photo-input" class="p-button p-component p-button-outlined">
                          <i class="pi pi-camera mr-2"></i>
                          {{ photoPreview ? 'Changer la photo' : 'Prendre/Ajouter une photo' }}
                        </label>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="form-footer flex justify-content-end gap-3 mt-4 pt-4 border-top-1 border-gray-100">
                <Button label="Annuler" class="p-button-text p-button-secondary" @click="$router.back()" />
                <Button 
                  type="submit" 
                  :label="isEditing ? 'Mettre à jour' : 'Enregistrer l\'équipement'" 
                  icon="pi pi-check" 
                  class="p-button-primary p-button-raised"
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
import { useUserStore } from '@/stores/userStore'
import gsap from 'gsap'

// PrimeVue components
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import Dropdown from 'primevue/dropdown'
import Card from 'primevue/card'
import Calendar from 'primevue/calendar'
import InputNumber from 'primevue/inputnumber'

const route = useRoute()
const router = useRouter()
const toast = useToast()
const equipementStore = useEquipementStore()
const categorieStore = useCategorieStore()
const userStore = useUserStore()

const isEditing = computed(() => route.params.id !== undefined)
const loading = ref(false)
const errors = ref({})

const form = ref({
  nom: '',
  numero_serie: '',
  categorie_id: null,
  etat: 'nouveau',
  localisation: '',
  responsable_id: null,
  photo: null,
  specifications: {}
})

const selectedCategoryAttributes = computed(() => {
  if (!form.value.categorie_id) return []
  const cat = categories.value.find(c => c.id === form.value.categorie_id)
  return cat?.attributs_personnalises || []
})

const selectedCategoryName = computed(() => {
  const cat = categories.value.find(c => c.id === form.value.categorie_id)
  return cat?.nom || ''
})

const photoPreview = ref(null)

const etatOptions = [
  { label: 'Nouveau', value: 'nouveau' },
  { label: 'Actif', value: 'actif' },
  { label: 'En maintenance', value: 'en_maintenance' },
  { label: 'Hors service', value: 'hors_service' },
  { label: 'Archivé', value: 'archive' }
]

const categories = computed(() => categorieStore.categories)
const users = computed(() => userStore.users)

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
    const formData = new FormData()
    Object.keys(form.value).forEach(key => {
      if (form.value[key] !== null && form.value[key] !== undefined) {
        if (form.value[key] instanceof Date) {
          formData.append(key, form.value[key].toISOString().split('T')[0])
        } else if (key === 'specifications') {
          formData.append(key, JSON.stringify(form.value[key]))
        } else {
          formData.append(key, form.value[key])
        }
      }
    })

    if (isEditing.value) {
      formData.append('_method', 'PUT')
      await equipementStore.updateEquipement(route.params.id, formData)
      toast.add({ severity: 'success', summary: 'Succès', detail: 'Équipement mis à jour', life: 3000 })
    } else {
      await equipementStore.createEquipement(formData)
      toast.add({ severity: 'success', summary: 'Succès', detail: 'Équipement enregistré', life: 3000 })
    }
    
    router.push('/equipements')
  } catch (err) {
    if (err.response?.data?.errors) {
      errors.value = err.response.data.errors
      toast.add({ severity: 'error', summary: 'Erreur', detail: 'Veuillez vérifier les champs', life: 3000 })
      
      // Retourner à l'étape avec des erreurs
      if (errors.value.nom || errors.value.numero_serie) {
        // Plus d'étapes, l'erreur s'affichera directement
      }
    } else {
      toast.add({ severity: 'error', summary: 'Erreur', detail: 'Une erreur est survenue', life: 3000 })
    }
  } finally {
    loading.value = false
  }
}

onMounted(async () => {
  await Promise.all([
    categorieStore.fetchCategories(),
    userStore.fetchUsers()
  ])

  // Pré-remplir la catégorie si passée en paramètre
  if (route.query.categorie_id) {
    form.value.categorie_id = parseInt(route.query.categorie_id)
  }

  if (isEditing.value) {
    loading.value = true
    try {
      const equipement = await equipementStore.fetchEquipementById(route.params.id)
      form.value = {
        nom: equipement.nom || '',
        reference: equipement.reference || '',
        numero_serie: equipement.numero_serie || '',
        imei: equipement.imei || '',
        code_inventaire: equipement.code_inventaire || '',
        marque: equipement.marque || '',
        modele: equipement.modele || '',
        categorie_id: equipement.categorie_id,
        fournisseur: equipement.fournisseur || '',
        date_acquisition: equipement.date_acquisition ? new Date(equipement.date_acquisition) : null,
        prix_achat: equipement.prix_achat,
        garantie_date_fin: equipement.garantie_date_fin ? new Date(equipement.garantie_date_fin) : null,
        etat: equipement.etat || 'actif',
        localisation: equipement.localisation || '',
        responsable_id: equipement.responsable_id,
        photo: null,
        specifications: equipement.specifications || {}
      }
      if (equipement.photo) {
        photoPreview.value = equipement.photo_url
      }
    } catch (err) {
      toast.add({ severity: 'error', summary: 'Erreur', detail: 'Impossible de charger l\'équipement', life: 3000 })
    } finally {
      loading.value = false
    }
  }

  gsap.from('.animate-header', { opacity: 0, x: -30, duration: 0.6 })
  gsap.from('.animate-card', { opacity: 0, y: 30, duration: 0.8, delay: 0.2 })
})
</script>

<style scoped lang="scss">
.equipement-form-container {
  padding: 2rem;
  max-width: 1000px;
  margin: 0 auto;
}

.form-header {
  display: flex;
  align-items: flex-start;
  gap: 1.5rem;
  margin-bottom: 2.5rem;
  
  .back-btn {
    margin-top: 0.5rem;
    background: white;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
  }
  
  h1 {
    font-size: 2.2rem;
    font-weight: 800;
    color: #1e293b;
    margin: 0;
    letter-spacing: -0.02em;
  }
  
  p {
    color: #64748b;
    margin: 0.3rem 0 0 0;
    font-size: 1.1rem;
  }
}

.modern-form-card {
  border-radius: 24px;
  border: none;
  box-shadow: 0 10px 40px rgba(0,0,0,0.04);
  overflow: hidden;
  
  :deep(.p-card-body) {
    padding: 2.5rem;
  }
}

.section-info {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-bottom: 2rem;
  padding-bottom: 1rem;
  border-bottom: 1px solid #f1f5f9;
  
  i {
    font-size: 1.5rem;
    color: #3b82f6;
    background: #eff6ff;
    padding: 0.8rem;
    border-radius: 12px;
  }
  
  h3 {
    margin: 0;
    font-size: 1.3rem;
    font-weight: 700;
    color: #334155;
  }
}

.input-wrapper {
  position: relative;
  display: flex;
  align-items: center;
  
  i {
    position: absolute;
    left: 1rem;
    color: #94a3b8;
    z-index: 1;
  }
  
  :deep(.p-inputtext) {
    padding-left: 3rem;
    border-radius: 12px;
    border: 1.5px solid #e2e8f0;
    transition: all 0.2s;
    
    &:focus {
      border-color: #3b82f6;
      box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
    }
  }
}

.modern-upload-zone {
  border: 2px dashed #e2e8f0;
  border-radius: 20px;
  padding: 2rem;
  text-align: center;
  transition: all 0.3s;
  cursor: pointer;
  background: #f8fafc;
  
  &:hover {
    border-color: #3b82f6;
    background: #f1f5f9;
  }
  
  &.has-preview {
    padding: 0;
    border-style: solid;
    overflow: hidden;
  }
  
  .upload-label {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.8rem;
    cursor: pointer;
    
    i {
      font-size: 3rem;
      color: #3b82f6;
    }
    
    span {
      font-weight: 600;
      color: #475569;
    }
    
    small {
      color: #94a3b8;
    }
  }
}

.preview-container {
  position: relative;
  height: 300px;
  
  img {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }
  
  .preview-actions {
    position: absolute;
    bottom: 1.5rem;
    left: 0;
    right: 0;
    display: flex;
    justify-content: center;
    gap: 1rem;
    
    .change-photo-btn {
      background: rgba(255, 255, 255, 0.9);
      padding: 0.5rem 1.5rem;
      border-radius: 30px;
      font-weight: 600;
      color: #334155;
      cursor: pointer;
      backdrop-filter: blur(4px);
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
  }
}

.form-actions-footer {
  display: flex;
  align-items: center;
  margin-top: 3rem;
  padding-top: 2rem;
  border-top: 1px solid #f1f5f9;
  
  .spacer {
    flex: 1;
  }
  
  :deep(.p-button) {
    border-radius: 12px;
    padding: 0.8rem 2rem;
    font-weight: 600;
  }
}

.hidden-input {
  display: none;
}
</style>
