<template>
  <AgenceLayout>
    <div class="agents-page">
      <div class="header">
        <h2><i class="pi pi-users"></i> Agents</h2>
        <button @click="showForm = true" class="btn-primary">
          <i class="pi pi-plus"></i> Nouvel agent
        </button>
      </div>

      <div v-if="agentStore.loading" class="loading">
        <i class="pi pi-spin pi-spinner"></i> Chargement...
      </div>

      <div v-else class="agents-grid">
        <div v-for="agent in agentStore.agents" :key="agent.id" class="agent-card">
          <div class="card-header">
            <div class="agent-avatar">
              {{ agent.prenom?.charAt(0) }}{{ agent.nom?.charAt(0) }}
            </div>
            <div class="agent-info">
              <h3>{{ agent.prenom }} {{ agent.nom }}</h3>
              <span class="matricule">{{ agent.matricule }}</span>
            </div>
            <span :class="['badge', agent.statut]">{{ agent.statut }}</span>
          </div>
          <div class="card-body">
            <p v-if="agent.poste"><i class="pi pi-briefcase"></i> {{ agent.poste }}</p>
            <p v-if="agent.direction"><i class="pi pi-building"></i> {{ agent.direction }}</p>
            <p v-if="agent.service"><i class="pi pi-sitemap"></i> {{ agent.service }}</p>
            <p v-if="agent.telephone"><i class="pi pi-phone"></i> {{ agent.telephone }}</p>
            <p v-if="agent.email"><i class="pi pi-envelope"></i> {{ agent.email }}</p>
          </div>
          <div class="card-footer">
            <div class="actions">
              <button @click="showAgent(agent)" class="btn-icon" title="Afficher">
                <i class="pi pi-eye"></i>
              </button>
              <button @click="editAgent(agent)" class="btn-icon" title="Modifier">
                <i class="pi pi-pencil"></i>
              </button>
              <button @click="deleteAgent(agent.id)" class="btn-icon btn-danger" title="Supprimer">
                <i class="pi pi-trash"></i>
              </button>
            </div>
          </div>
        </div>

        <div v-if="agentStore.agents.length === 0" class="empty-state">
          <i class="pi pi-users"></i>
          <p>Aucun agent enregistré</p>
        </div>
      </div>

      <div v-if="showForm" class="modal-overlay" @click.self="showForm = false">
        <AgentFormView :edit-data="editingAgent" @saved="onSaved" @cancel="showForm = false" />
      </div>

      <div v-if="showDetail" class="modal-overlay" @click.self="showDetail = false">
        <AgentDetailView :agent="viewingAgent" @close="showDetail = false" />
      </div>
    </div>
  </AgenceLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useAgentStore } from '@/stores/agentStore.js'
import AgenceLayout from '@/layouts/AgenceLayout.vue'
import AgentFormView from './AgentFormView.vue'
import AgentDetailView from './AgentDetailView.vue'

const agentStore = useAgentStore()
const showForm = ref(false)
const showDetail = ref(false)
const editingAgent = ref(null)
const viewingAgent = ref(null)

onMounted(() => agentStore.fetchAgents())

const showAgent = (agent) => { viewingAgent.value = agent; showDetail.value = true }
const editAgent = (agent) => { editingAgent.value = { ...agent }; showForm.value = true }
const deleteAgent = async (id) => { if (!confirm('Supprimer cet agent ?')) return; await agentStore.deleteAgent(id) }
const onSaved = () => { showForm.value = false; editingAgent.value = null; agentStore.fetchAgents() }
</script>

<style scoped>
.agents-page {
  padding: 20px;
}

.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 25px;
}

.header h2 {
  color: #e2e8f0;
  margin: 0;
}

.btn-primary {
  background: #3b82f6;
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 6px;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 8px;
}

.agents-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 20px;
}

.agent-card {
  background: #1e293b;
  border: 1px solid #334155;
  border-radius: 10px;
  padding: 20px;
}

.card-header {
  display: flex;
  align-items: center;
  gap: 15px;
  margin-bottom: 15px;
}

.agent-avatar {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  background: linear-gradient(135deg, #3b82f6, #8b5cf6);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: bold;
  font-size: 1.1rem;
}

.agent-info {
  flex: 1;
}

.agent-info h3 {
  margin: 0;
  color: #e2e8f0;
  font-size: 1rem;
}

.matricule {
  color: #94a3b8;
  font-size: 0.85rem;
}

.badge {
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: bold;
}

.badge.actif {
  background: #10b981;
  color: white;
}

.badge.inactif {
  background: #ef4444;
  color: white;
}

.card-body p {
  margin: 8px 0;
  color: #94a3b8;
  font-size: 0.9rem;
  display: flex;
  align-items: center;
  gap: 8px;
}

.card-footer {
  display: flex;
  justify-content: flex-end;
  align-items: center;
  margin-top: 15px;
  padding-top: 15px;
  border-top: 1px solid #334155;
}

.actions {
  display: flex;
  gap: 8px;
}

.btn-icon {
  background: #334155;
  border: none;
  color: #e2e8f0;
  padding: 6px 10px;
  border-radius: 4px;
  cursor: pointer;
}

.btn-danger:hover {
  background: #ef4444;
}

.empty-state {
  grid-column: 1 / -1;
  text-align: center;
  padding: 60px 20px;
  color: #94a3b8;
}

.empty-state i {
  font-size: 3rem;
  margin-bottom: 15px;
  opacity: 0.5;
}

.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.7);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 100;
  padding: 20px;
}

.loading {
  text-align: center;
  color: #94a3b8;
  padding: 40px;
}
</style>
