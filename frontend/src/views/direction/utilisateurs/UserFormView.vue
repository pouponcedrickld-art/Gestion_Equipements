<template>
  <div class="user-form-card">
    <div class="form-header">
      <div class="flex items-center gap-3">
        <div class="icon-circle">
          <i :class="editData ? 'pi pi-user-edit' : 'pi pi-user-plus'"></i>
        </div>
        <h2 class="text-xl font-extrabold text-dark">{{ editData ? 'Modifier l\'utilisateur' : 'Nouvel utilisateur' }}</h2>
      </div>
      <button @click="$emit('cancel')" class="btn btn-outline btn-icon"><i class="pi pi-times"></i></button>
    </div>

    <form @submit.prevent="handleSubmit" class="mt-6">
      <!-- Section Liaison Agent -->
      <div class="form-section mb-6" v-if="!editData">
        <div class="section-title">
          <i class="pi pi-link"></i> Liaison Agent
        </div>
        <div class="form-group">
          <label>Lier à un agent existant (optionnel)</label>
          <div class="search-select-custom">
            <div class="relative mb-2">
              <i class="pi pi-search absolute left-3 top-1/2 -translate-y-1/2 text-muted text-xs"></i>
              <input v-model="agentSearch" placeholder="Filtrer les agents..." class="pl-8 text-sm" />
            </div>
            <select v-model="formData.agent_id" @change="onAgentSelect" class="text-sm">
              <option :value="null">-- Aucun / Nouvel utilisateur indépendant --</option>
              <option v-for="a in filteredAgents" :key="a.id" :value="a.id">
                {{ a.nom }} {{ a.prenom }} ({{ a.matricule }})
              </option>
            </select>
          </div>
        </div>
      </div>

      <!-- Informations de base -->
      <div class="grid grid-cols-2 gap-4">
        <div class="form-group">
          <label>Nom complet *</label>
          <input v-model="formData.name" required placeholder="Ex: Jean Dupont" />
        </div>
        <div class="form-group">
          <label>Email professionnel *</label>
          <input v-model="formData.email" type="email" required placeholder="email@exemple.com" />
        </div>
      </div>

      <!-- Mot de passe et Rôle -->
      <div class="grid grid-cols-2 gap-4">
        <div class="form-group" v-if="!editData">
          <label>Mot de passe *</label>
          <input v-model="formData.password" type="password" required placeholder="••••••••" />
        </div>
        <div class="form-group" v-else>
          <label>Changer mot de passe</label>
          <input v-model="formData.password" type="password" placeholder="Laisser vide si inchangé" />
        </div>
        <div class="form-group">
          <label>Rôle du compte *</label>
          <select v-model="formData.role" required>
            <option value="">-- Choisir un rôle --</option>
            <option value="super_admin">Super Admin</option>
            <option value="gestionnaire_stock_general">Stock Général</option>
            <option value="chef_agence">Chef d'Agence</option>
            <option value="gestionnaire_stock">Stock Local</option>
            <option value="technicien_maintenance">Technicien</option>
            <option value="agent">Agent</option>
          </select>
        </div>
      </div>

      <!-- Agence et Téléphone -->
      <div class="grid grid-cols-2 gap-4">
        <div class="form-group">
          <label>Agence de rattachement *</label>
          <select v-model="formData.agence_id" required>
            <option value="">-- Choisir une agence --</option>
            <option v-for="a in agences" :key="a.id" :value="a.id">{{ a.nom }}</option>
          </select>
        </div>
        <div class="form-group">
          <label>Téléphone</label>
          <input v-model="formData.telephone" placeholder="+229 ..." />
        </div>
      </div>

      <!-- Poste -->
      <div class="form-group">
        <label>Poste / Fonction</label>
        <input v-model="formData.poste" placeholder="Ex: Responsable IT, Comptable..." />
      </div>

      <!-- Actions -->
      <div class="flex justify-end gap-3 mt-8 pt-6 border-t border-color">
        <button type="button" @click="$emit('cancel')" class="btn btn-secondary btn-md">Annuler</button>
        <button type="submit" class="btn btn-primary btn-md" :disabled="saving">
          <i v-if="saving" class="pi pi-spin pi-spinner mr-2"></i>
          {{ saving ? 'Traitement...' : (editData ? 'Mettre à jour' : 'Créer le compte') }}
        </button>
      </div>
      
      <div v-if="error" class="error-msg mt-4">
        <i class="pi pi-exclamation-circle"></i>
        <span>{{ error }}</span>
      </div>
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
      password: '',
    })
  } else {
    resetForm()
  }
}, { immediate: true })

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
.user-form-card {
  background: var(--bg-card);
  padding: 2.5rem;
  border-radius: var(--radius-xl);
  max-width: 650px;
  width: 100%;
  border: 1px solid var(--border-color);
}

.form-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 1px solid var(--border-color);
  padding-bottom: 1.5rem;
  margin-bottom: 1.5rem;
}

.icon-circle {
  width: 44px;
  height: 44px;
  border-radius: 50%;
  background: var(--primary-light);
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--primary-hover);
  font-size: 1.4rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
  margin-bottom: 1.25rem;
}

.form-section {
  background: var(--bg-app);
  padding: 1.25rem;
  border-radius: var(--radius-lg);
  border: 1px solid var(--border-color);
}

.section-title {
  display: flex;
  align-items: center;
  gap: 8px;
  font-weight: 800;
  font-size: 0.85rem;
  text-transform: uppercase;
  color: var(--text-muted);
  margin-bottom: 1.25rem;
}

.section-title i {
  color: var(--primary-hover);
}

.search-select-custom {
  background: var(--bg-card);
  padding: 12px;
  border-radius: var(--radius-md);
  border: 1px solid var(--border-color);
}

.error-msg {
  background: #fef2f2;
  color: #ef4444;
  padding: 1rem;
  border-radius: var(--radius-md);
  display: flex;
  align-items: center;
  gap: 10px;
  font-weight: 600;
  font-size: 0.9rem;
}

@media (max-width: 640px) {
  .user-form-card {
    padding: 1.5rem;
  }
}
</style>
