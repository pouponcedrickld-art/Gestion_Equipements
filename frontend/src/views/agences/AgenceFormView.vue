<template>
  <div class="form-modal">
    <h3>{{ isEdit ? 'Modifier' : 'Nouvelle' }} Agence</h3>
    <form @submit.prevent="submit">
      <div class="form-row">
        <div class="form-group">
          <label>Type</label>
          <select v-model="form.type" required>
            <option value="generale">Siège</option>
            <option value="sous_agence">Sous-Agence</option>
          </select>
        </div>
        <div class="form-group">
          <label>Nom</label>
          <input v-model="form.nom" required placeholder="Nom" />
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label>Ville</label>
          <input v-model="form.ville" placeholder="Ville" />
        </div>
        <div class="form-group">
          <label>Code Postal</label>
          <input v-model="form.code_postal" placeholder="Code postal" />
        </div>
      </div>
      <div class="form-group">
        <label>Adresse</label>
        <textarea v-model="form.adresse" rows="2" placeholder="Adresse"></textarea>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label>Téléphone</label>
          <input v-model="form.telephone" placeholder="+225 XX XX XX XX" />
        </div>
        <div class="form-group">
          <label>Email</label>
          <input v-model="form.email" type="email" placeholder="contact@agence.ci" />
        </div>
      </div>
      <div v-if="form.type === 'sous_agence'" class="form-group">
        <label>Agence Parente</label>
        <select v-model="form.parent_id" required>
          <option v-for="a in agenceStore.agences.filter(x => x.type === 'generale')" :key="a.id" :value="a.id">{{ a.nom }}</option>
        </select>
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
import { useAgenceStore } from '@/stores/agenceStore'

const props = defineProps({ editData: Object })
const emit = defineEmits(['saved', 'cancel'])

const agenceStore = useAgenceStore()
const saving = ref(false)
const isEdit = computed(() => !!props.editData)

const form = ref({
  type: props.editData?.type || 'sous_agence',
  nom: props.editData?.nom || '',
  ville: props.editData?.ville || '',
  adresse: props.editData?.adresse || '',
  code_postal: props.editData?.code_postal || '',
  telephone: props.editData?.telephone || '',
  email: props.editData?.email || '',
  parent_id: props.editData?.parent_id || null,
})

const submit = async () => {
  saving.value = true
  try {
    if (isEdit.value) await agenceStore.updateAgence(props.editData.id, form.value)
    else await agenceStore.createAgence(form.value)
    emit('saved')
  } finally { saving.value = false }
}
</script>

<style scoped>
.form-modal { background: #1e293b; padding: 30px; border-radius: 12px; width: 500px; border: 1px solid #334155; }
h3 { margin: 0 0 20px 0; color: #e2e8f0; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
.form-group { margin-bottom: 15px; }
label { display: block; color: #94a3b8; margin-bottom: 6px; font-size: 0.85rem; }
input, select, textarea { width: 100%; padding: 10px; border: 1px solid #334155; border-radius: 6px; background: #0f172a; color: #e2e8f0; }
input:focus, select:focus, textarea:focus { outline: none; border-color: #3b82f6; }
.form-actions { display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px; }
.btn-secondary { background: #334155; color: #e2e8f0; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer; }
.btn-primary { background: #3b82f6; color: white; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer; }
.btn-primary:disabled { opacity: 0.6; }
</style>
