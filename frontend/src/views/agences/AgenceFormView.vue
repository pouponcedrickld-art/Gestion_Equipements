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
        <div class="form-group" v-if="formData.type === 'sous_agence'">
          <label>Agence parente</label>
          <select v-model="formData.parent_id">
            <option value="">-- Choisir --</option>
            <option v-for="a in agenceStore.agences" :key="a.id" :value="a.id" v-if="a.type === 'generale'">
              {{ a.nom }}
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
      <div class="form-actions">
        <button type="button" @click="$emit('cancel')" class="btn-secondary">Annuler</button>
        <button type="submit" class="btn-primary">
          {{ editData ? 'Mettre à jour' : 'Créer' }}
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, reactive, watch, onMounted } from 'vue'
import { useAgenceStore } from '@/stores/agenceStore'

const props = defineProps({
  editData: Object,
})

const emit = defineEmits(['saved', 'cancel'])

const agenceStore = useAgenceStore()

const formData = reactive({
  type: 'sous_agence',
  nom: '',
  ville: '',
  adresse: '',
  telephone: '',
  email: '',
  parent_id: null,
  responsable_id: null,
  gestionnaire_stock_id: null,
})

watch(() => props.editData, (val) => {
  if (val) {
    Object.assign(formData, val)
  } else {
    resetForm()
  }
})

const resetForm = () => {
  Object.assign(formData, {
    type: 'sous_agence',
    nom: '',
    ville: '',
    adresse: '',
    telephone: '',
    email: '',
    parent_id: null,
    responsable_id: null,
    gestionnaire_stock_id: null,
  })
}

onMounted(() => agenceStore.fetchAgences())

const handleSubmit = async () => {
  if (props.editData) {
    await agenceStore.updateAgence(props.editData.id, formData)
  } else {
    await agenceStore.createAgence(formData)
  }
  emit('saved')
}
</script>

<style scoped>
.modal-form { background: #1e293b; border-radius: 12px; width: 100%; max-width: 550px; padding: 25px; }
.form-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
.form-header h2 { color: #e2e8f0; margin: 0; font-size: 1.3rem; }
.close-btn { background: none; border: none; color: #94a3b8; font-size: 1.3rem; cursor: pointer; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
.form-group { display: flex; flex-direction: column; gap: 8px; margin-bottom: 15px; }
.form-group label { color: #cbd5e1; font-size: 0.9rem; }
.form-group input, .form-group select { width: 100%; padding: 10px; border: 1px solid #334155; border-radius: 6px; background: #0f172a; color: #e2e8f0; }
.form-actions { display: flex; justify-content: flex-end; gap: 10px; margin-top: 10px; }
.btn-primary { background: #3b82f6; color: white; border: none; padding: 10px 25px; border-radius: 6px; cursor: pointer; font-size: 1rem; }
.btn-secondary { background: #334155; color: #e2e8f0; border: none; padding: 10px 25px; border-radius: 6px; cursor: pointer; font-size: 1rem; }
</style>
