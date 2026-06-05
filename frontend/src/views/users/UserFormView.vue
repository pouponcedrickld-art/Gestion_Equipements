<template>
  <div class="form-modal">
    <h3>{{ isEdit ? 'Modifier' : 'Nouvel' }} Utilisateur</h3>
    <form @submit.prevent="submit">
      <div class="form-row">
        <div class="form-group">
          <label>Nom</label>
          <input v-model="form.name" required placeholder="Prénom Nom" />
        </div>
        <div class="form-group">
          <label>Email</label>
          <input v-model="form.email" type="email" required placeholder="email@gestpark.ci" />
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label>Rôle</label>
          <select v-model="form.role" required>
            <option value="super_admin">Super Admin</option>
            <option value="gestionnaire_stock_general">G. Stock Général</option>
            <option value="chef_agence">Chef d'Agence</option>
            <option value="gestionnaire_stock">G. Stock Local</option>
            <option value="technicien_maintenance">Technicien</option>
            <option value="agent">Agent</option>
          </select>
        </div>
        <div class="form-group">
          <label>Agence</label>
          <select v-model="form.agence_id" required>
            <option v-for="a in agences" :key="a.id" :value="a.id">{{ a.nom }}</option>
          </select>
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label>Téléphone</label>
          <input v-model="form.telephone" placeholder="+225 XX XX XX XX" />
        </div>
        <div class="form-group">
          <label>Poste</label>
          <input v-model="form.poste" placeholder="Fonction" />
        </div>
      </div>
      <div class="form-group" v-if="!isEdit">
        <label>Mot de passe</label>
        <input v-model="form.password" type="password" required placeholder="Min. 6 caractères" />
      </div>
      <div class="form-group checkbox">
        <label><input v-model="form.actif" type="checkbox" /> Compte actif</label>
      </div>
      <div class="form-actions">
        <button type="button" @click="$emit('cancel')" class="btn-secondary">Annuler</button>
        <button type="submit" :disabled="saving" class="btn-primary">{{ saving ? '...' : 'Enregistrer' }}</button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useUserStore } from '@/stores/userStore'

const props = defineProps({ editData: Object, agences: Array })
const emit = defineEmits(['saved', 'cancel'])

const userStore = useUserStore()
const saving = ref(false)
const isEdit = computed(() => !!props.editData)

const form = ref({
  name: props.editData?.name || '',
  email: props.editData?.email || '',
  role: props.editData?.role || 'agent',
  agence_id: props.editData?.agence_id || '',
  telephone: props.editData?.telephone || '',
  poste: props.editData?.poste || '',
  password: '',
  actif: props.editData?.actif ?? true,
})

const submit = async () => {
  saving.value = true
  try {
    const payload = { ...form.value }
    if (isEdit.value) delete payload.password
    if (isEdit.value) await userStore.updateUser(props.editData.id, payload)
    else await userStore.createUser(payload)
    emit('saved')
  } finally { saving.value = false }
}
</script>

<style scoped>
.form-modal { background: #1e293b; padding: 30px; border-radius: 12px; width: 550px; border: 1px solid #334155; }
h3 { margin: 0 0 20px 0; color: #e2e8f0; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
.form-group { margin-bottom: 15px; }
.form-group.checkbox { display: flex; align-items: center; gap: 8px; }
.form-group.checkbox input { width: auto; }
label { display: block; color: #94a3b8; margin-bottom: 6px; font-size: 0.85rem; }
input, select { width: 100%; padding: 10px; border: 1px solid #334155; border-radius: 6px; background: #0f172a; color: #e2e8f0; }
input:focus, select:focus { outline: none; border-color: #3b82f6; }
.form-actions { display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px; }
.btn-secondary { background: #334155; color: #e2e8f0; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer; }
.btn-primary { background: #3b82f6; color: white; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer; }
.btn-primary:disabled { opacity: 0.6; }
</style>
