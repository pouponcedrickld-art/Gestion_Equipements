<template>
  <div class="modal-form">
    <div class="form-header">
      <h2>{{ editData ? 'Modifier l\'utilisateur' : 'Nouvel utilisateur' }}</h2>
      <button @click="$emit('cancel')" class="close-btn"><i class="pi pi-times"></i></button>
    </div>
    <form @submit.prevent="handleSubmit">
      <div class="form-row">
        <div class="form-group">
          <label>Nom complet</label>
          <input v-model="formData.name" required placeholder="Nom et prénom" />
        </div>
        <div class="form-group">
          <label>Email</label>
          <input v-model="formData.email" type="email" required placeholder="Email" />
        </div>
      </div>
      <div class="form-row" v-if="!editData">
        <div class="form-group">
          <label>Mot de passe</label>
          <input v-model="formData.password" type="password" required placeholder="••••••••" />
        </div>
        <div class="form-group">
          <label>Rôle</label>
          <select v-model="formData.role" required>
            <option value="">-- Choisir --</option>
            <option value="super_admin">Super Admin</option>
            <option value="gestionnaire_stock_general">G. Stock Général</option>
            <option value="chef_agence">Chef d'Agence</option>
            <option value="gestionnaire_stock">G. Stock Local</option>
            <option value="technicien_maintenance">Technicien</option>
            <option value="agent">Agent</option>
          </select>
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label>Agence</label>
          <select v-model="formData.agence_id" required>
            <option value="">-- Choisir --</option>
            <option v-for="a in agences" :key="a.id" :value="a.id">{{ a.nom }}</option>
          </select>
        </div>
        <div class="form-group">
          <label>Téléphone</label>
          <input v-model="formData.telephone" placeholder="Numéro de téléphone" />
        </div>
      </div>
      <div class="form-group">
        <label>Poste</label>
        <input v-model="formData.poste" placeholder="Poste occupé" />
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
import { ref, reactive, watch } from 'vue'
import { useUserStore } from '@/stores/userStore'

const props = defineProps({
  editData: Object,
  agences: Array,
})

const emit = defineEmits(['saved', 'cancel'])

const userStore = useUserStore()

const formData = reactive({
  name: '',
  email: '',
  password: '',
  role: '',
  agence_id: null,
  telephone: '',
  poste: '',
  actif: true,
})

watch(() => props.editData, (val) => {
  if (val) {
    Object.assign(formData, {
      name: val.name || '',
      email: val.email || '',
      agence_id: val.agence_id || null,
      telephone: val.telephone || '',
      poste: val.poste || '',
      actif: val.actif ?? true,
      role: val.role || '',
    })
  } else {
    resetForm()
  }
})

const resetForm = () => {
  Object.assign(formData, {
    name: '',
    email: '',
    password: '',
    role: '',
    agence_id: null,
    telephone: '',
    poste: '',
    actif: true,
  })
}

const handleSubmit = async () => {
  if (props.editData) {
    await userStore.updateUser(props.editData.id, formData)
  } else {
    await userStore.createUser(formData)
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
