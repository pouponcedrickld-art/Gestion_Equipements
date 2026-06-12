<template>
  <DirectionLayout>
    <div class="equipement-form-container" ref="pageContainer">
 
      <!-- Header -->
      <div class="form-header animate-header">
        <Button
          icon="pi pi-arrow-left"
          class="p-button-text p-button-rounded back-btn"
          @click="$router.back()"
        />
        <h1 class="page-title">{{ isEditing ? 'Mise à jour' : 'Nouvel Équipement' }}</h1>
      </div>
 
      <!-- Layout deux colonnes -->
      <div class="two-col-layout animate-card">
 
        <!-- ─── SIDEBAR GAUCHE ─── -->
        <aside class="sidebar">
 
          <!-- Photo -->
          <div class="sidebar-card">
            <div class="card-section-label">
              <i class="pi pi-camera"></i> Photo
            </div>
            <div
              v-if="photoPreview"
              class="photo-preview-wrapper"
            >
              <img :src="photoPreview" alt="Aperçu" class="photo-preview-img" />
              <Button
                icon="pi pi-trash"
                class="p-button-rounded p-button-danger p-button-sm photo-delete-btn"
                @click="removePhoto"
              />
            </div>
            <div
              v-else
              class="photo-upload-placeholder"
              @click="$refs.photoInput.click()"
            >
              <i class="pi pi-camera text-3xl"></i>
              <span class="text-xs font-bold">Cliquer pour ajouter</span>
            </div>
            <input type="file" ref="photoInput" @change="handleFileChange" accept="image/*" class="hidden" />
          </div>
 
          <!-- Identification -->
          <div class="sidebar-card">
            <div class="card-section-label">
              <i class="pi pi-tag"></i> Identification
            </div>
 
            <div class="field mb-3">
              <label class="font-bold text-sm">Catégorie *</label>
              <Dropdown
                v-model="form.categorie_id"
                :options="categories"
                optionLabel="nom"
                optionValue="id"
                placeholder="Sélectionner"
                :class="{ 'p-invalid': errors.categorie_id }"
                filter
                class="w-full p-inputtext-sm"
              />
              <small class="p-error" v-if="errors.categorie_id">{{ errors.categorie_id[0] }}</small>
            </div>
 
            <div class="field mb-3">
              <label class="font-bold text-sm">Statut</label>
              <div class="status-badges">
                <span
                  v-for="opt in etatOptions"
                  :key="opt.value"
                  class="status-badge"
                  :class="{ 'status-badge--active': form.etat === opt.value }"
                  @click="form.etat = opt.value"
                >
                  {{ opt.label }}
                </span>
              </div>
            </div>
 
            <div class="field mb-3">
              <label class="font-bold text-sm">Quantité</label>
              <InputNumber
                v-model="form.quantite"
                :min="1"
                showButtons
                placeholder="Nombre d'unités"
                class="w-full p-inputtext-sm"
              />
            </div>

            <div class="field" v-if="!isEditing">
              <label class="font-bold text-sm text-blue-700">Quantité à créer (Lots)</label>
              <InputNumber
                v-model="form.quantite_a_creer"
                :min="1"
                :max="100"
                showButtons
                class="w-full p-inputtext-sm"
              />
            </div>

            <div class="field mb-3" v-if="form.quantite_a_creer > 1">
              <label class="font-bold text-sm">Mode d'enregistrement</label>
              <div class="flex flex-column gap-2 mt-2">
                <div class="flex align-items-center">
                  <RadioButton v-model="form.mode_enregistrement" inputId="modeIndividuel" name="mode" value="individuel" />
                  <label for="modeIndividuel" class="ml-2 text-sm">Individuel (X lignes)</label>
                </div>
                <div class="flex align-items-center">
                  <RadioButton v-model="form.mode_enregistrement" inputId="modeLot" name="mode" value="lot" />
                  <label for="modeLot" class="ml-2 text-sm">Lot (1 seule ligne)</label>
                </div>
              </div>
            </div>
          </div>
 
          <!-- Attribution -->
          <div class="sidebar-card">
            <div class="card-section-label">
              <i class="pi pi-user"></i> Attribution
            </div>
 
            <div class="field mb-3">
              <label class="font-bold text-sm">Responsable</label>
              <Dropdown
                v-model="form.responsable_id"
                :options="users"
                optionLabel="name"
                optionValue="id"
                placeholder="Attribuer à..."
                filter
                showClear
                class="w-full p-inputtext-sm"
              />
            </div>
 
            <div class="field">
              <label class="font-bold text-sm">Localisation</label>
              <InputText
                v-model="form.localisation"
                placeholder="Bureau / Site"
                class="w-full p-inputtext-sm"
              />
            </div>
          </div>
 
        </aside>
 
        <!-- ─── CONTENU PRINCIPAL ─── -->
        <div class="main-content">
          <form @submit.prevent="handleSubmit" class="p-fluid">
 
            <!-- Informations générales -->
            <div class="main-card">
              <div class="card-section-label">
                <i class="pi pi-desktop"></i> Informations générales
              </div>
 
              <div class="field mb-3">
                <label class="font-bold text-sm">Désignation *</label>
                <InputText
                  v-model="form.nom"
                  :class="{ 'p-invalid': errors.nom }"
                  placeholder="Nom du matériel"
                  class="p-inputtext-sm"
                />
                <small class="p-error" v-if="errors.nom">{{ errors.nom[0] }}</small>
              </div>
 
              <div class="grid grid-tight">
                <div class="col-12 md:col-4">
                  <div class="field">
                    <label class="font-bold text-sm">Marque</label>
                    <InputText v-model="form.marque" placeholder="Ex : Lenovo" class="p-inputtext-sm" :class="{ 'p-invalid': errors.marque }" />
                    <small class="p-error" v-if="errors.marque">{{ errors.marque[0] }}</small>
                  </div>
                </div>
                <div class="col-12 md:col-4">
                  <div class="field">
                    <label class="font-bold text-sm">Modèle</label>
                    <InputText v-model="form.modele" placeholder="Ex : X1 Gen 9" class="p-inputtext-sm" :class="{ 'p-invalid': errors.modele }" />
                    <small class="p-error" v-if="errors.modele">{{ errors.modele[0] }}</small>
                  </div>
                </div>
                <div class="col-12 md:col-4">
                  <div class="field">
                    <label class="font-bold text-sm">Numéro de série</label>
                    <InputText
                      v-model="form.numero_serie"
                      :class="{ 'p-invalid': errors.numero_serie }"
                      :placeholder="form.quantite_a_creer > 1 ? 'Auto' : 'S/N'"
                      class="p-inputtext-sm"
                    />
                    <small class="p-error" v-if="errors.numero_serie">{{ errors.numero_serie[0] }}</small>
                  </div>
                </div>
              </div>
            </div>
 
            <!-- Inventaire & Acquisition -->
            <div class="main-card">
              <div class="card-section-label">
                <i class="pi pi-clipboard"></i> Inventaire &amp; acquisition
              </div>
 
              <div class="grid grid-tight">
                <div class="col-12 md:col-6">
                  <div class="field mb-3">
                    <label class="font-bold text-sm">Code inventaire</label>
                    <InputText v-model="form.code_inventaire" placeholder="Ex : INV-2024" class="p-inputtext-sm" />
                  </div>
                </div>
                <div class="col-12 md:col-6">
                  <div class="field mb-3">
                    <label class="font-bold text-sm">Date d'acquisition</label>
                    <Calendar
                      v-model="form.date_acquisition"
                      dateFormat="dd/mm/yy"
                      showIcon
                      class="p-inputtext-sm"
                    />
                  </div>
                </div>
                <div class="col-12">
                  <div class="field">
                    <label class="font-bold text-sm">Prix d'achat</label>
                    <InputNumber
                      v-model="form.prix_achat"
                      mode="currency"
                      currency="XOF"
                      locale="fr-FR"
                      class="p-inputtext-sm"
                    />
                  </div>
                </div>
              </div>
            </div>
 
            <!-- Spécifications techniques -->
            <div class="main-card">
              <div class="card-section-label">
                <i class="pi pi-sliders-h"></i> Spécifications techniques
                <span v-if="selectedCategoryName" class="category-badge">
                  {{ selectedCategoryName }}
                </span>
              </div>
 
              <div v-if="selectedCategoryAttributes.length > 0" class="specs-grid">
                <div
                  v-for="attr in selectedCategoryAttributes"
                  :key="attr.nom"
                  class="field"
                >
                  <label class="font-bold text-xs">{{ attr.nom }}</label>
                  <InputText
                    v-if="attr.type === 'texte'"
                    v-model="form.specifications[attr.nom]"
                    class="p-inputtext-sm w-full"
                  />
                  <InputNumber
                    v-else-if="attr.type === 'nombre'"
                    v-model="form.specifications[attr.nom]"
                    class="p-inputtext-sm w-full"
                  />
                  <Calendar
                    v-else-if="attr.type === 'date'"
                    v-model="form.specifications[attr.nom]"
                    dateFormat="dd/mm/yy"
                    showIcon
                    class="p-inputtext-sm w-full"
                  />
                </div>
              </div>
 
              <div v-else class="specs-empty">
                <i class="pi pi-info-circle"></i>
                <span>Aucune spécification technique pour cette catégorie</span>
              </div>
            </div>
 
            <!-- Footer actions -->
            <div class="form-footer">
              <Button
                label="Annuler"
                class="p-button-text p-button-secondary p-button-sm"
                @click="$router.back()"
              />
              <Button
                type="submit"
                :label="isEditing ? 'Mettre à jour' : 'Enregistrer'"
                icon="pi pi-check"
                class="p-button-primary p-button-sm"
                :loading="loading"
              />
            </div>
 
          </form>
        </div>
 
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
 
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import Dropdown from 'primevue/dropdown'
import RadioButton from 'primevue/radiobutton'
import Card from 'primevue/card'
import Calendar from 'primevue/calendar'
import InputNumber from 'primevue/inputnumber'
import Divider from 'primevue/divider'
 
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
  marque: '',
  modele: '',
  code_inventaire: '',
  categorie_id: null,
  etat: 'neuf',
  localisation: '',
  responsable_id: null,
  date_acquisition: null,
  prix_achat: null,
  photo: null,
  specifications: {},
  quantite: 1,
  quantite_a_creer: 1,
  mode_enregistrement: 'individuel'
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
  { label: 'Neuf', value: 'neuf' },
  { label: 'En service', value: 'en_service' },
  { label: 'Maintenance', value: 'en_maintenance' },
  { label: 'En panne', value: 'en_panne' },
  { label: 'Réformé', value: 'reforme' },
  { label: 'Perdu', value: 'perdu' }
]

const categories = computed(() => categorieStore.categoriesList)
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
    // On passe directement l'objet réactif form.value
    // Le store s'occupera de la conversion FormData
    if (isEditing.value) {
      await equipementStore.updateEquipement(route.params.id, form.value)
      toast.add({ severity: 'success', summary: 'Succès', detail: 'Équipement mis à jour', life: 3000 })
    } else {
      await equipementStore.createEquipement(form.value)
      toast.add({ severity: 'success', summary: 'Succès', detail: 'Équipement enregistré', life: 3000 })
    }

    router.push('/equipements')
  } catch (err) {
    if (err.response?.data?.errors) {
      errors.value = err.response.data.errors
      toast.add({ severity: 'error', summary: 'Erreur', detail: 'Veuillez vérifier les champs', life: 3000 })
    } else {
      toast.add({ severity: 'error', summary: 'Erreur', detail: err.message || 'Une erreur est survenue', life: 3000 })
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
 
  if (route.query.categorie_id) {
    form.value.categorie_id = parseInt(route.query.categorie_id)
  }
 
  if (isEditing.value) {
    loading.value = true
    try {
      const equipement = await equipementStore.fetchEquipement(route.params.id)
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
        specifications: equipement.specifications || {},
        quantite_a_creer: equipement.quantite || 1,
        mode_enregistrement: equipement.is_lot ? 'lot' : 'individuel'
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
  max-width: 1200px;
  margin: 0 auto;
}
 
/* ── Header ── */
.form-header {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-bottom: 2rem;
}
 
.page-title {
  font-size: 1.75rem;
  font-weight: 800;
  color: var(--text-dark);
  margin: 0;
}
 
.back-btn {
  background: var(--bg-card);
  box-shadow: var(--shadow-sm);
  border: 1px solid var(--border-color);
}
 
/* ── Layout deux colonnes ── */
.two-col-layout {
  display: grid;
  grid-template-columns: 300px 1fr;
  gap: 1.5rem;
  align-items: start;
}
 
/* ── Sidebar ── */
.sidebar {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}
 
.sidebar-card {
  background: var(--bg-card);
  border: 1px solid var(--border-color);
  border-radius: var(--radius-lg);
  padding: 1.5rem;
  box-shadow: var(--shadow-sm);
}
 
/* ── Cartes principale ── */
.main-content {
  display: flex;
  flex-direction: column;
}
 
.main-card {
  background: var(--bg-card);
  border: 1px solid var(--border-color);
  border-radius: var(--radius-lg);
  padding: 1.5rem 2rem;
  margin-bottom: 1.5rem;
  box-shadow: var(--shadow-sm);
}
 
/* ── Label de section ── */
.card-section-label {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 0.75rem;
  font-weight: 800;
  color: var(--text-muted);
  text-transform: uppercase;
  letter-spacing: 0.05em;
  margin-bottom: 1.25rem;
 
  i {
    color: var(--primary-hover);
    font-size: 1rem;
  }
}
 
/* ── Photo ── */
.photo-upload-placeholder {
  height: 200px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 0.75rem;
  cursor: pointer;
  color: var(--text-muted);
  border: 2px dashed var(--border-color);
  border-radius: var(--radius-md);
  background: var(--bg-input);
  transition: all 0.2s;
 
  &:hover {
    border-color: var(--primary);
    color: var(--text-dark);
    background: var(--bg-card);
  }
}
 
.photo-preview-wrapper {
  height: 200px;
  position: relative;
  border-radius: var(--radius-md);
  overflow: hidden;
  background: #000;
 
  .photo-preview-img {
    width: 100%;
    height: 100%;
    object-fit: contain;
  }
 
  .photo-delete-btn {
    position: absolute;
    top: 0.75rem;
    right: 0.75rem;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
  }
}
 
/* ── Badges de statut ── */
.status-badges {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}
 
.status-badge {
  font-size: 0.75rem;
  font-weight: 700;
  padding: 6px 14px;
  border-radius: 20px;
  cursor: pointer;
  border: 1px solid var(--border-color);
  background: var(--bg-input);
  color: var(--text-muted);
  transition: all 0.2s;
  user-select: none;
 
  &:hover {
    border-color: var(--primary);
    color: var(--text-dark);
  }
 
  &--active {
    background: var(--primary);
    border-color: var(--primary);
    color: var(--text-dark);
    box-shadow: var(--shadow-sm);
  }
}
 
/* ── Badge catégorie dans specs ── */
.category-badge {
  font-size: 0.7rem;
  font-weight: 700;
  background: var(--secondary-light);
  color: var(--text-dark);
  padding: 4px 12px;
  border-radius: 20px;
  margin-left: 10px;
  text-transform: none;
  letter-spacing: 0;
}
 
/* ── Spécifications ── */
.specs-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1rem;
}
 
.specs-empty {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.75rem;
  padding: 2.5rem;
  border: 2px dashed var(--border-color);
  border-radius: var(--radius-md);
  color: var(--text-muted);
  font-weight: 600;
}
 
/* ── Footer ── */
.form-footer {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  padding: 1.5rem 0;
  margin-top: 1rem;
}
 
/* ── Responsive ── */
@media (max-width: 1024px) {
  .two-col-layout {
    grid-template-columns: 1fr;
  }
 
  .sidebar {
    display: grid;
    grid-template-columns: 1fr 1fr;
  }
}

@media (max-width: 640px) {
  .sidebar {
    grid-template-columns: 1fr;
  }
  .specs-grid {
    grid-template-columns: 1fr;
  }
}
</style>