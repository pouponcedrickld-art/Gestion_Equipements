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
                    <InputText v-model="form.marque" placeholder="Ex : Lenovo" class="p-inputtext-sm" />
                  </div>
                </div>
                <div class="col-12 md:col-4">
                  <div class="field">
                    <label class="font-bold text-sm">Modèle</label>
                    <InputText v-model="form.modele" placeholder="Ex : X1 Gen 9" class="p-inputtext-sm" />
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
  etat: 'nouveau',
  localisation: '',
  responsable_id: null,
  date_acquisition: null,
  prix_achat: null,
  photo: null,
  specifications: {},
  quantite: 1,
  quantite_a_creer: 1
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
  { label: 'Maintenance', value: 'en_maintenance' },
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
        quantite: equipement.quantite || 1,
        quantite_a_creer: 1
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
  padding: 1.25rem;
  max-width: 1100px;
  margin: 0 auto;
}
 
/* ── Header ── */
.form-header {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin-bottom: 1.25rem;
}
 
.page-title {
  font-size: 1.4rem;
  font-weight: 800;
  color: #1e293b;
  margin: 0;
}
 
.back-btn {
  background: white;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
  flex-shrink: 0;
}
 
/* ── Layout deux colonnes ── */
.two-col-layout {
  display: grid;
  grid-template-columns: 260px 1fr;
  gap: 1rem;
  align-items: start;
}
 
/* ── Sidebar ── */
.sidebar {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  position: sticky;
  top: 1rem;
}
 
.sidebar-card {
  background: white;
  border: 1px solid #e2e8f0;
  border-radius: 14px;
  padding: 1rem;
}
 
/* ── Cartes principale ── */
.main-content {
  display: flex;
  flex-direction: column;
}
 
.main-card {
  background: white;
  border: 1px solid #e2e8f0;
  border-radius: 14px;
  padding: 1rem 1.25rem;
  margin-bottom: 1rem;
}
 
/* ── Label de section ── */
.card-section-label {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 0.72rem;
  font-weight: 700;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  margin-bottom: 0.85rem;
 
  i {
    color: #3b82f6;
    font-size: 0.85rem;
  }
}
 
/* ── Photo ── */
.photo-upload-placeholder {
  height: 160px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  cursor: pointer;
  color: #94a3b8;
  border: 2px dashed #e2e8f0;
  border-radius: 10px;
  background: #f8fafc;
  transition: border-color 0.2s;
 
  &:hover {
    border-color: #3b82f6;
    color: #3b82f6;
  }
}
 
.photo-preview-wrapper {
  height: 160px;
  position: relative;
  border-radius: 10px;
  overflow: hidden;
  background: #000;
 
  .photo-preview-img {
    width: 100%;
    height: 100%;
    object-fit: contain;
  }
 
  .photo-delete-btn {
    position: absolute;
    top: 0.5rem;
    right: 0.5rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
  }
}
 
/* ── Badges de statut ── */
.status-badges {
  display: flex;
  flex-wrap: wrap;
  gap: 5px;
}
 
.status-badge {
  font-size: 0.72rem;
  font-weight: 500;
  padding: 4px 10px;
  border-radius: 20px;
  cursor: pointer;
  border: 1px solid #e2e8f0;
  background: #f8fafc;
  color: #64748b;
  transition: all 0.15s;
  user-select: none;
 
  &:hover {
    border-color: #3b82f6;
    color: #3b82f6;
  }
 
  &--active {
    background: #eff6ff;
    border-color: #3b82f6;
    color: #1d4ed8;
  }
}
 
/* ── Badge catégorie dans specs ── */
.category-badge {
  font-size: 0.68rem;
  font-weight: 600;
  background: #eff6ff;
  color: #1d4ed8;
  padding: 2px 8px;
  border-radius: 20px;
  margin-left: 6px;
  text-transform: none;
  letter-spacing: 0;
}
 
/* ── Spécifications ── */
.specs-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 0.65rem;
}
 
.specs-empty {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 1rem;
  border: 1px dashed #e2e8f0;
  border-radius: 10px;
  color: #94a3b8;
  font-size: 0.85rem;
}
 
/* ── Grid tight ── */
.grid-tight {
  margin: -0.4rem;
 
  > [class*="col"] {
    padding: 0.4rem;
  }
}
 
/* ── Footer ── */
.form-footer {
  display: flex;
  justify-content: flex-end;
  gap: 0.5rem;
  padding-top: 0.75rem;
  border-top: 1px solid #f1f5f9;
  margin-top: 0.25rem;
}
 
/* ── PrimeVue overrides ── */
:deep(.p-inputtext-sm) {
  padding: 0.45rem 0.75rem;
}
 
:deep(.p-dropdown),
:deep(.p-inputnumber-input),
:deep(.p-calendar .p-inputtext) {
  border: 1.5px solid #e2e8f0;
  border-radius: 8px;
 
  &:enabled:focus {
    border-color: #3b82f6;
    box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.1);
  }
}
 
.hidden {
  display: none;
}
 
/* ── Responsive ── */
@media (max-width: 768px) {
  .two-col-layout {
    grid-template-columns: 1fr;
  }
 
  .sidebar {
    position: static;
  }
 
  .specs-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}
</style>