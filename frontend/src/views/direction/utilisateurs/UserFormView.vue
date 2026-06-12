<template>
  <div class="modal-form">
    <div class="form-header">
      <h2>{{ editData ? 'Modifier l\'utilisateur' : 'Nouvel utilisateur' }}</h2>
      <button @click="$emit('cancel')" class="close-btn"><i class="pi pi-times"></i></button>
    </div>
    <form @submit.prevent="handleSubmit">
      <div class="form-group" v-if="!editData">
        <label>Lier à un agent (optionnel)</label>
        <div class="search-select">
          <input v-model="agentSearch" placeholder="Rechercher un agent..." class="search-input" />
          <select v-model="formData.agent_id" @change="onAgentSelect">
            <option :value="null">-- Aucun / Nouvel utilisateur --</option>
            <option v-for="a in filteredAgents" :key="a.id" :value="a.id">
              {{ a.nom }} {{ a.prenom }} ({{ a.matricule }})
            </option>
          </select>
        </div>
      </div>

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
        <button type="submit" class="btn-primary" :disabled="saving">
          {{ saving ? 'Enregistrement...' : (editData ? 'Mettre à jour' : 'Créer') }}
        </button>
      </div>
      <p v-if="error" class="error">{{ error }}</p>
    </form>
  </div>
</template>

<script setup>
import { ref, reactive, watch, onMounted, computed } from 'vue'
import { useUserStore } from '@/stores/userStore.js'
import { useAgentStore } from '@/stores/agentStore.js'

const props = defineProps({
  editData: Object,
  agences: Array,
})

const emit = defineEmits(['saved', 'cancel'])

const userStore = useUserStore()
const agentStore = useAgentStore()
const saving = ref(false)
const error = ref(null)
const availableAgents = ref([])
const agentSearch = ref('')

const formData = reactive({
  name: '',
  email: '',
  password: '',
  role: '',
  agence_id: null,
  telephone: '',
  poste: '',
  actif: true,
  agent_id: null,
})

const filteredAgents = computed(() => {
  if (!agentSearch.value) return availableAgents.value
  const s = agentSearch.value.toLowerCase()
  return availableAgents.value.filter(a => 
    a.nom.toLowerCase().includes(s) || 
    a.prenom.toLowerCase().includes(s) || 
    a.matricule.toLowerCase().includes(s)
  )
})

onMounted(async () => {
  if (!props.editData) {
    try {
      const agents = await agentStore.fetchAvailableAgents()
      availableAgents.value = agents
    } catch (err) {
      console.error('Erreur chargement agents dispo:', err)
    }
  }
})

const onAgentSelect = () => {
  if (formData.agent_id) {
    const agent = availableAgents.value.find(a => a.id === formData.agent_id)
    if (agent) {
      formData.name = `${agent.prenom} ${agent.nom}`
      formData.email = agent.email || ''
      formData.telephone = agent.telephone || ''
      formData.poste = agent.poste || ''
    }
  }
}

watch(() => props.editData, (val) => {
  if (val) {
    Object.assign(formData, {
      name: val.name || '',
      email: val.email || '',
      agence_id: val.agence_id || null,
      telephone: val.telephone || '',
      poste: val.poste || '',
      actif: val.actif ?? true,
      role: val.roles?.[0]?.name || '',
    })
  } else {
    resetForm()
  }
}, { immediate: true })

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
    agent_id: null,
  })
  error.value = null
  agentSearch.value = ''
}

const handleSubmit = async () => {
  saving.value = true
  error.value = null
  try {
    if (props.editData) {
      await userStore.updateUser(props.editData.id, formData)
    } else {
      await userStore.createUser(formData)
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

.search-select {
  display: flex;
  flex-direction: column;
  gap: 5px;
  margin-bottom: 10px;
}

.search-input {
  border-bottom-left-radius: 0 !important;
  border-bottom-right-radius: 0 !important;
  border-bottom: none !important;
  background: #0f172a !important;
}

.search-select select {
  border-top-left-radius: 0 !important;
  border-top-right-radius: 0 !important;
}
</style>
