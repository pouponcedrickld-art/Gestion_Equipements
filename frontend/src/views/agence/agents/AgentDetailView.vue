<template>
  <div class="agent-detail">
    <div class="detail-header">
      <h3 class="text-xl font-extrabold text-dark">
        <i class="pi pi-user text-primary"></i>
        Agent : {{ agent?.prenom }} {{ agent?.nom }}
      </h3>
      <button @click="$emit('close')" class="btn btn-outline btn-icon">
        <i class="pi pi-times"></i>
      </button>
    </div>
    <div class="detail-body" v-if="agent">
      <div class="detail-row">
        <span class="label">Matricule:</span>
        <span class="value font-bold text-dark">{{ agent.matricule || '—' }}</span>
      </div>
      <div class="detail-row">
        <span class="label">Nom:</span>
        <span class="value font-bold text-dark">{{ agent.nom || '—' }}</span>
      </div>
      <div class="detail-row">
        <span class="label">Prénom:</span>
        <span class="value font-bold text-dark">{{ agent.prenom || '—' }}</span>
      </div>
      <div class="detail-row">
        <span class="label">Téléphone:</span>
        <span class="value text-dark">{{ agent.telephone || '—' }}</span>
      </div>
      <div class="detail-row">
        <span class="label">Email:</span>
        <span class="value text-dark">{{ agent.email || '—' }}</span>
      </div>
      <div class="detail-row">
        <span class="label">Poste:</span>
        <span class="value text-dark font-medium">{{ agent.poste || '—' }}</span>
      </div>
      <div class="detail-row">
        <span class="label">Statut:</span>
        <span class="status-pill" :class="agent.statut === 'actif' ? 'status-active' : 'status-danger'">
          {{ agent.statut === 'actif' ? 'Actif' : 'Inactif' }}
        </span>
      </div>
      <div class="detail-row" v-if="agent.user">
        <span class="label">Compte utilisateur:</span>
        <span class="value font-bold text-primary">{{ agent.user.name }}</span>
      </div>
    </div>
    <div class="detail-footer mt-6 pt-6 border-t border-color flex justify-end">
      <button @click="$emit('close')" class="btn btn-secondary btn-md">Fermer</button>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  agent: Object
})
const emit = defineEmits(['close'])
</script>

<style scoped>
.agent-detail {
  background: var(--bg-card);
  border-radius: var(--radius-xl);
  width: 100%;
  max-width: 500px;
  padding: 2.5rem;
  box-shadow: var(--shadow-lg);
  border: 1px solid var(--border-color);
}

.detail-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
  padding-bottom: 1.5rem;
  border-bottom: 1px solid var(--border-color);
}

.detail-body {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.detail-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 14px 16px;
  background: var(--bg-input);
  border-radius: var(--radius-md);
  border: 1px solid var(--border-color);
}

.detail-row .label {
  color: var(--text-muted);
  font-weight: 600;
  font-size: 0.85rem;
}

.detail-row .value {
  font-size: 0.95rem;
}

.status-pill {
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: 800;
  text-transform: uppercase;
}

.status-active {
  background: #dcfce7;
  color: #10b981;
}

.status-danger {
  background: #fee2e2;
  color: #ef4444;
}
</style>