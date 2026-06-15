<template>
  <div class="modal-form">
    <div class="form-header">
      <h2>{{ editData ? 'Modifier l\'agence' : 'Nouvelle agence' }}</h2>
      <button @click="$emit('cancel')" class="close-btn"><i class="pi pi-times"></i></button>
    </div>
    <form @submit.prevent="handleSubmit">
      <div class="form-row">
        <div class="form-group">
          <label>Type</label>
          <select v-model="formData.type" required>
            <option value="generale">Agence Générale</option>
            <option value="sous_agence">Sous-Agence</option>
          </select>
        </div>
        <div class="form-group">
          <label>Nom</label>
          <input v-model="formData.nom" required placeholder="Nom de l'agence" />
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label>Ville</label>
          <input v-model="formData.ville" placeholder="Ville" />
        </div>
        <div class="form-group">
          <label>Code Postal</label>
          <input v-model="formData.code_postal" placeholder="Code postal" />
        </div>
      </div>
      <div class="form-row" v-if="formData.type === 'sous_agence'">
        <div class="form-group">
          <label>Agence parente</label>
          <select v-model="formData.parent_id">
            <option value="">-- Choisir --</option>
            <option v-if="agenceStore.agenceGenerale" :value="agenceStore.agenceGenerale.id">
              {{ agenceStore.agenceGenerale.nom }}
            </option>
          </select>
        </div>
      </div>
      <div class="form-group">
        <label>Adresse</label>
        <input v-model="formData.adresse" placeholder="Adresse complète" />
      </div>
      <div class="form-row">
        <div class="form-group">
          <label>Téléphone</label>
          <input v-model="formData.telephone" placeholder="Numéro de téléphone" />
        </div>
        <div class="form-group">
          <label>Email</label>
          <input v-model="formData.email" type="email" placeholder="Email de l'agence" />
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label>Chef d'agence</label>
          <select v-model="formData.responsable_id">
            <option :value="null">-- Sélectionner --</option>
            <option v-for="user in userStore.users" :key="user.id" :value="user.id">
              {{ user.name }}
            </option>
          </select>
        </div>
        <div class="form-group">
          <label>Gestionnaire Stock</label>
          <select v-model="formData.gestionnaire_stock_id">
            <option :value="null">-- Sélectionner --</option>
            <option v-for="user in userStore.users" :key="user.id" :value="user.id">
              {{ user.name }}
            </option>
          </select>
        </div>
      </div>
      <div class="form-actions">
        <button type="button" @click="$emit('cancel')" class="btn-secondary">Annuler</button>
        <button type="submit" class="btn-primary" :disabled="saving">
          {{ saving ? 'Enregistrement...' : (editData ? 'Mettre à jour' : 'Créer') }}
        </button>
      </div>
      <p v-if="error" class="error">{{ error }}</p>
    </form>
  </div>
</template>

<script setup>
import { ref, reactive, watch, onMounted } from 'vue'
import { useAgenceStore } from '@/stores/agenceStore.js'
import { useUserStore } from '@/stores/userStore.js'

const props = defineProps({
  editData: Object,
})

const emit = defineEmits(['saved', 'cancel'])

const agenceStore = useAgenceStore()
const userStore = useUserStore()
const saving = ref(false)
const error = ref(null)

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
  background: #1e293b;
  border-radius: 12px;
  width: 100%;
  max-width: 550px;
  padding: 25px;
}

.form-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.form-header h2 {
  color: #e2e8f0;
  margin: 0;
  font-size: 1.3rem;
}

.close-btn {
  background: none;
  border: none;
  color: #94a3b8;
  font-size: 1.3rem;
  cursor: pointer;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 15px;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
  margin-bottom: 15px;
}

.form-group label {
  color: #cbd5e1;
  font-size: 0.9rem;
}

.form-group input,
.form-group select {
  width: 100%;
  padding: 10px;
  border: 1px solid #334155;
  border-radius: 6px;
  background: #0f172a;
  color: #e2e8f0;
  box-sizing: border-box;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  margin-top: 10px;
}

.btn-primary {
  background: #3b82f6;
  color: white;
  border: none;
  padding: 10px 25px;
  border-radius: 6px;
  cursor: pointer;
  font-size: 1rem;
}

.btn-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-secondary {
  background: #334155;
  color: #e2e8f0;
  border: none;
  padding: 10px 25px;
  border-radius: 6px;
  cursor: pointer;
  font-size: 1rem;
}

.error {
  color: #ef4444;
  margin-top: 10px;
  text-align: center;
}
</style>
