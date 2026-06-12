<template>
  <div class="agent-detail">
    <div class="detail-header">
      <h3>
        <i class="pi pi-user"></i>
        Agent : {{ agent?.prenom }} {{ agent?.nom }}
      </h3>
      <button @click="$emit('close')" class="close-btn">
        <i class="pi pi-times"></i>
      </button>
    </div>
    <div class="detail-body" v-if="agent">
      <div class="detail-row">
        <span class="label">Matricule:</span>
        <span class="value">{{ agent.matricule || '—' }}</span>
      </div>
      <div class="detail-row">
        <span class="label">Nom:</span>
        <span class="value">{{ agent.nom || '—' }}</span>
      </div>
      <div class="detail-row">
        <span class="label">Prénom:</span>
        <span class="value">{{ agent.prenom || '—' }}</span>
      </div>
      <div class="detail-row">
        <span class="label">Téléphone:</span>
        <span class="value">{{ agent.telephone || '—' }}</span>
      </div>
      <div class="detail-row">
        <span class="label">Email:</span>
        <span class="value">{{ agent.email || '—' }}</span>
      </div>
      <div class="detail-item">
        <span class="label">Poste:</span>
        <span class="value">{{ agent.poste || '—' }}</span>
      </div>
      <div class="detail-row">
        <span class="label">Statut:</span>
        <span class="value" :class="agent.statut">{{ agent.statut === 'actif' ? 'Actif' : 'Inactif' }}</span>
      </div>
      <div class="detail-row" v-if="agent.user">
        <span class="label">Compte utilisateur:</span>
        <span class="value">{{ agent.user.name }}</span>
      </div>
    </div>
    <div class="detail-footer">
      <button @click="$emit('close')" class="btn-secondary">Fermer</button>
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
  background: #1e293b;
  border-radius: 12px;
  width: 100%;
  max-width: 600px;
  padding: 25px;
}

.detail-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 25px;
  padding-bottom: 15px;
  border-bottom: 1px solid #334155;
}

.detail-header h3 {
  color: #e2e8f0;
  margin: 0;
  display: flex;
  align-items: center;
  gap: 10px;
}

.close-btn {
  background: #334155;
  border: none;
  color: #94a3b8;
  font-size: 1.2rem;
  padding: 6px 10px;
  border-radius: 6px;
  cursor: pointer;
  transition: background 0.2s;
}

.close-btn:hover {
  background: #475569;
}

.detail-body {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.detail-row {
  display: flex;
  justify-content: space-between;
  padding: 10px 15px;
  background: #0f172a;
  border-radius: 8px;
}

.detail-row .label {
  color: #94a3b8;
  font-weight: 600;
}

.detail-row .value {
  color: #e2e8f0;
}

.detail-row .value.actif {
  color: #10b981;
}

.detail-row .value.inactif {
  color: #ef4444;
}

.detail-footer {
  margin-top: 25px;
  padding-top: 15px;
  border-top: 1px solid #334155;
  display: flex;
  justify-content: flex-end;
}

.btn-secondary {
  background: #334155;
  color: #e2e8f0;
  border: none;
  padding: 10px 25px;
  border-radius: 6px;
  cursor: pointer;
  font-size: 1rem;
  transition: background 0.2s;
}

.btn-secondary:hover {
  background: #475569;
}
</style>
