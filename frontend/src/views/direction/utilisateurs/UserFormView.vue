<template>
  <div class="user-form-card">
    <div class="form-header">
      <div class="flex items-center gap-3">
        <div class="icon-circle">
          <i :class="editData ? 'pi pi-user-edit' : 'pi pi-user-plus'"></i>
        </div>
        <h2 class="text-xl font-extrabold text-dark">{{ editData ? 'Modifier l\'utilisateur' : 'Nouvel utilisateur' }}</h2>
      </div>
      <Button @click="$emit('cancel')" icon="pi pi-times" class="p-button-text p-button-rounded" />
    </div>

    <form @submit.prevent="handleSubmit" class="p-fluid mt-6">
      <!-- Section Liaison Agent -->
      <div class="form-section mb-6" v-if="!editData">
        <div class="section-title">
          <i class="pi pi-link"></i> Liaison Agent
        </div>
        <div class="field">
          <label>Lier à un agent existant (optionnel)</label>
          <div class="search-select-custom">
            <div class="relative mb-2">
              <i class="pi pi-search absolute left-3 top-1/2 -translate-y-1/2 text-muted text-xs"></i>
              <InputText v-model="agentSearch" placeholder="Filtrer les agents..." class="pl-8 text-xs" />
            </div>
            <Dropdown 
              v-model="formData.agent_id" 
              :options="filteredAgents" 
              optionLabel="display" 
              optionValue="id" 
              placeholder="-- Aucun / Nouvel utilisateur indépendant --" 
              class="w-full" 
              @change="onAgentSelect"
              filter
            />
          </div>
        </div>
      </div>

      <!-- Informations de base -->
      <div class="grid">
        <div class="col-12 md:col-6 field">
          <label>Nom complet *</label>
          <InputText v-model="formData.name" required placeholder="Ex: Jean Dupont" />
        </div>
        <div class="col-12 md:col-6 field">
          <label>Email professionnel *</label>
          <InputText v-model="formData.email" type="email" required placeholder="email@exemple.com" />
        </div>
      </div>

      <!-- Mot de passe et Rôle -->
      <div class="grid">
        <div class="col-12 md:col-6 field" v-if="!editData">
          <label>Mot de passe *</label>
          <InputText v-model="formData.password" type="password" required placeholder="••••••••" />
        </div>
        <div class="col-12 md:col-6 field" v-else>
          <label>Changer mot de passe</label>
          <InputText v-model="formData.password" type="password" placeholder="Laisser vide si inchangé" />
        </div>
        <div class="col-12 md:col-6 field">
          <label>Rôle du compte *</label>
          <Dropdown 
            v-model="formData.role" 
            :options="roleOptions" 
            optionLabel="label" 
            optionValue="value" 
            placeholder="-- Choisir un rôle --" 
            required
            class="w-full"
          />
        </div>
      </div>

      <!-- Agence et Téléphone -->
      <div class="grid">
        <div class="col-12 md:col-6 field">
          <label>Agence de rattachement *</label>
          <Dropdown 
            v-model="formData.agence_id" 
            :options="agences" 
            optionLabel="nom" 
            optionValue="id" 
            placeholder="-- Choisir une agence --" 
            required
            class="w-full"
            filter
          />
        </div>
        <div class="col-12 md:col-6 field">
          <label>Téléphone</label>
          <InputText v-model="formData.telephone" placeholder="+229 ..." />
        </div>
      </div>

      <!-- Poste et Statut -->
      <div class="grid">
        <div class="col-12 md:col-6 field">
          <label>Poste / Fonction</label>
          <InputText v-model="formData.poste" placeholder="Ex: Responsable IT, Comptable..." />
        </div>
        <div class="col-12 md:col-6 field flex flex-col justify-end">
          <label class="flex items-center gap-2 cursor-pointer">
            <input type="checkbox" v-model="formData.actif" class="w-auto h-auto" />
            <span class="text-sm font-bold" :class="formData.actif ? 'text-success' : 'text-danger'">
              Compte {{ formData.actif ? 'Actif' : 'Inactif' }}
            </span>
          </label>
          <p class="text-xs text-muted mt-1">
            {{ formData.actif ? 'L\'utilisateur peut se connecter.' : 'L\'accès sera bloqué immédiatement.' }}
          </p>
        </div>
      </div>

      <!-- Actions -->
      <div class="flex justify-end gap-3 mt-8 pt-6 border-t border-color">
        <Button type="button" @click="$emit('cancel')" label="Annuler" class="p-button-secondary" />
        <Button type="submit" :loading="saving" :label="saving ? 'Traitement...' : (editData ? 'Mettre à jour' : 'Créer le compte')" />
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
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import Dropdown from 'primevue/dropdown'

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

const roleOptions = [
  { label: 'Super Admin', value: 'super_admin' },
  { label: 'Stock Général', value: 'gestionnaire_stock_general' },
  { label: 'Chef d\'Agence', value: 'chef_agence' },
  { label: 'Stock Local', value: 'gestionnaire_stock' },
  { label: 'Technicien', value: 'technicien_maintenance' },
  { label: 'Agent', value: 'agent' }
]

const filteredAgents = computed(() => {
  if (!agentSearch.value) return availableAgents.value.map(a => ({ ...a, display: `${a.nom} ${a.prenom} (${a.matricule})` }))
  const s = agentSearch.value.toLowerCase()
  return availableAgents.value.filter(a => 
    a.nom?.toLowerCase().includes(s) || 
    a.prenom?.toLowerCase().includes(s) || 
    a.matricule?.toLowerCase().includes(s)
  ).map(a => ({ ...a, display: `${a.nom} ${a.prenom} (${a.matricule})` }))
})

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
