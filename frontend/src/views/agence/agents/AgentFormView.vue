<template>
  <div class="modal-form">
    <div class="form-header">
      <h2>{{ editData ? 'Modifier l\'agent' : 'Nouvel agent' }}</h2>
      <button @click="$emit('cancel')" class="close-btn"><i class="pi pi-times"></i></button>
    </div>
    <form @submit.prevent="handleSubmit">
      <div class="form-group">
        <label>Matricule *</label>
        <input v-model="formData.matricule" required placeholder="Matricule de l'agent" />
      </div>
      <div class="form-row">
        <div class="form-group">
          <label>Nom *</label>
          <input v-model="formData.nom" required placeholder="Nom" />
        </div>
        <div class="form-group">
          <label>Prénom *</label>
          <input v-model="formData.prenom" required placeholder="Prénom" />
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label>Téléphone</label>
          <input v-model="formData.telephone" placeholder="Numéro de téléphone" />
        </div>
        <div class="form-group">
          <label>Email</label>
          <input v-model="formData.email" type="email" placeholder="Email" />
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label>Direction</label>
          <input v-model="formData.direction" placeholder="Direction" />
        </div>
        <div class="form-group">
          <label>Service</label>
          <input v-model="formData.service" placeholder="Service" />
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label>Poste</label>
          <input v-model="formData.poste" placeholder="Poste" />
        </div>
        <div class="form-group">
          <label>Statut</label>
          <select v-model="formData.statut">
            <option value="actif">Actif</option>
            <option value="inactif">Inactif</option>
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
import { ref, reactive, watch } from 'vue'
import { useAgentStore } from '@/stores/agentStore.js'

const props = defineProps({
  editData: Object,
})

const emit = defineEmits(['saved', 'cancel'])

const agentStore = useAgentStore()
const saving = ref(false)
const error = ref(null)

const formData = reactive({
  matricule: '',
  nom: '',
  prenom: '',
  telephone: '',
  email: '',
  direction: '',
  service: '',
  poste: '',
  statut: 'actif',
  photo: null,
  user_id: null,
})

const resetForm = () => {
  Object.assign(formData, {
    matricule: '',
    nom: '',
    prenom: '',
    telephone: '',
    email: '',
    direction: '',
    service: '',
    poste: '',
    statut: 'actif',
    photo: null,
    user_id: null,
  })
  error.value = null
}

watch(() => props.editData, (val) => {
  if (val) {
    formData.matricule = val.matricule || ''
    formData.nom = val.nom || ''
    formData.prenom = val.prenom || ''
    formData.telephone = val.telephone || ''
    formData.email = val.email || ''
    formData.direction = val.direction || ''
    formData.service = val.service || ''
    formData.poste = val.poste || ''
    formData.statut = val.statut || 'actif'
    formData.photo = val.photo || null
    formData.user_id = val.user_id || null
  } else {
    resetForm()
  }
}, { immediate: true, deep: true })

const handleSubmit = async () => {
  saving.value = true
  error.value = null
  try {
    if (props.editData) {
      await agentStore.updateAgent(props.editData.id, formData)
    } else {
      await agentStore.createAgent(formData)
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
