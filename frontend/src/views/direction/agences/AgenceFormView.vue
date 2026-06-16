<template>
  <div class="modal-form">
    <div class="form-header">
      <h2>{{ editData ? 'Modifier l\'agence' : 'Nouvelle agence' }}</h2>
      <Button @click="$emit('cancel')" icon="pi pi-times" class="p-button-text p-button-rounded" />
    </div>
    <form @submit.prevent="handleSubmit" class="p-fluid">
      <div class="grid">
        <div class="col-12 md:col-6 field">
          <label>Type</label>
          <Dropdown 
            v-model="formData.type" 
            :options="typeOptions" 
            optionLabel="label" 
            optionValue="value" 
            required
            class="w-full"
          />
        </div>
        <div class="col-12 md:col-6 field">
          <label>Nom</label>
          <InputText v-model="formData.nom" required placeholder="Nom de l'agence" />
        </div>
      </div>
      <div class="grid">
        <div class="col-12 md:col-6 field">
          <label>Ville</label>
          <InputText v-model="formData.ville" placeholder="Ville" />
        </div>
        <div class="col-12 md:col-6 field">
          <label>Code Postal</label>
          <InputText v-model="formData.code_postal" placeholder="Code postal" />
        </div>
      </div>
      <div class="grid" v-if="formData.type === 'sous_agence'">
        <div class="col-12 field">
          <label>Agence parente</label>
          <Dropdown 
            v-model="formData.parent_id" 
            :options="agenceOptions" 
            optionLabel="nom" 
            optionValue="id" 
            placeholder="-- Choisir --"
            class="w-full"
          />
        </div>
      </div>
      <div class="field">
        <label>Adresse</label>
        <InputText v-model="formData.adresse" placeholder="Adresse complète" />
      </div>
      <div class="grid">
        <div class="col-12 md:col-6 field">
          <label>Téléphone</label>
          <InputText v-model="formData.telephone" placeholder="Numéro de téléphone" />
        </div>
        <div class="col-12 md:col-6 field">
          <label>Email</label>
          <InputText v-model="formData.email" type="email" placeholder="Email de l'agence" />
        </div>
      </div>
      <div class="grid">
        <div class="col-12 md:col-6 field">
          <label>Chef d'agence</label>
          <Dropdown 
            v-model="formData.responsable_id" 
            :options="userOptions" 
            optionLabel="name" 
            optionValue="id" 
            placeholder="-- Sélectionner --"
            class="w-full"
            filter
          />
        </div>
        <div class="col-12 md:col-6 field">
          <label>Gestionnaire Stock</label>
          <Dropdown 
            v-model="formData.gestionnaire_stock_id" 
            :options="userOptions" 
            optionLabel="name" 
            optionValue="id" 
            placeholder="-- Sélectionner --"
            class="w-full"
            filter
          />
        </div>
      </div>
      <div class="form-actions">
        <Button type="button" @click="$emit('cancel')" class="p-button-secondary" label="Annuler" />
        <Button type="submit" :loading="saving" :label="saving ? 'Enregistrement...' : (editData ? 'Mettre à jour' : 'Créer')" />
      </div>
      <p v-if="error" class="error">{{ error }}</p>
    </form>
  </div>
</template>

<script setup>
import { ref, reactive, watch, onMounted, computed } from 'vue'
import { useAgenceStore } from '@/stores/agenceStore.js'
import { useUserStore } from '@/stores/userStore.js'
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import Dropdown from 'primevue/dropdown'

const props = defineProps({
  editData: Object,
})

const emit = defineEmits(['saved', 'cancel'])

const agenceStore = useAgenceStore()
const userStore = useUserStore()
const saving = ref(false)
const error = ref(null)

const typeOptions = [
  { label: 'Agence Générale', value: 'generale' },
  { label: 'Sous-Agence', value: 'sous_agence' }
]

const agenceOptions = computed(() => {
  return agenceStore.agences ? agenceStore.agences.filter(a => a.type === 'generale') : []
})

const userOptions = computed(() => {
  return userStore.users || []
})

const formData = reactive({
  type: 'sous_agence',
  nom: '',
  ville: '',
  adresse: '',
  code_postal: '',
  telephone: '',
  email: '',
  parent_id: null,
  responsable_id: null,
  gestionnaire_stock_id: null,
})

onMounted(() => userStore.fetchUsers())

const resetForm = () => {
  Object.assign(formData, {
    type: 'sous_agence',
    nom: '',
    ville: '',
    adresse: '',
    code_postal: '',
    telephone: '',
    email: '',
    parent_id: null,
    responsable_id: null,
    gestionnaire_stock_id: null,
  })
  error.value = null
}

watch(() => props.editData, (val) => {
  if (val) {
    formData.type = val.type || 'sous_agence'
    formData.nom = val.nom || ''
    formData.ville = val.ville || ''
    formData.adresse = val.adresse || ''
    formData.code_postal = val.code_postal || ''
    formData.telephone = val.telephone || ''
    formData.email = val.email || ''
    formData.parent_id = val.parent_id || null
    formData.responsable_id = val.responsable_id || null
    formData.gestionnaire_stock_id = val.gestionnaire_stock_id || null
  } else {
    resetForm()
  }
}, { immediate: true, deep: true })

const handleSubmit = async () => {
  saving.value = true
  error.value = null
  try {
    // Préparer les données : convertir parent_id vide en null
    const payload = { ...formData }
    if (payload.parent_id === '') {
      payload.parent_id = null
    }

    if (props.editData) {
      await agenceStore.updateAgence(props.editData.id, payload)
    } else {
      await agenceStore.createAgence(payload)
    }
    emit('saved')
  } catch (err) {
    console.error(err)
    error.value = err.response?.data?.message || 'Erreur lors de l\'enregistrement'
  } finally {
    saving.value = false
  }
}
</script>

<style scoped>
.modal-form {
  background: var(--bg-card);
  border-radius: var(--radius-xl);
  width: 100%;
  max-width: 550px;
  padding: 25px;
  border: 1px solid var(--border-color);
}

.form-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
  border-bottom: 1px solid var(--border-color);
  padding-bottom: 1rem;
}

.form-header h2 {
  color: var(--text-dark);
  margin: 0;
  font-size: 1.3rem;
  font-weight: 800;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  margin-top: 1.5rem;
  padding-top: 1.5rem;
  border-top: 1px solid var(--border-color);
}

.error {
  color: var(--error);
  margin-top: 10px;
  text-align: center;
  font-weight: 600;
}
</style>
