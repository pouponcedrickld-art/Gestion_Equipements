<template>
  <div class="agence-detail">
    <div class="detail-header">
      <h3>
        <i class="pi pi-building"></i>
        {{ agence?.type === 'generale' ? 'Siège Social' : 'Sous-agence' }} : {{ agence?.nom }}
      </h3>
      <button @click="$emit('close')" class="close-btn">
        <i class="pi pi-times"></i>
      </button>
    </div>
    <div class="detail-body" v-if="agence">
      <div class="detail-row">
        <span class="label">Ville:</span>
        <span class="value">{{ agence.ville || '—' }}</span>
      </div>
      <div class="detail-row">
        <span class="label">Code Postal:</span>
        <span class="value">{{ agence.code_postal || '—' }}</span>
      </div>
      <div class="detail-row">
        <span class="label">Adresse:</span>
        <span class="value">{{ agence.adresse || '—' }}</span>
      </div>
      <div class="detail-row">
        <span class="label">Téléphone:</span>
        <span class="value">{{ agence.telephone || '—' }}</span>
      </div>
      <div class="detail-row">
        <span class="label">Email:</span>
        <span class="value">{{ agence.email || '—' }}</span>
      </div>
      <div class="detail-row">
        <span class="label">Statut:</span>
        <span class="value" :class="agence.statut">{{ agence.statut === 'active' ? 'Active' : 'Inactive' }}</span>
      </div>
      <div class="detail-row" v-if="agence.parent">
        <span class="label">Agence parente:</span>
        <span class="value">{{ agence.parent.nom }}</span>
      </div>
      <div class="detail-row" v-if="agence.responsable">
        <span class="label">Chef d'agence:</span>
        <span class="value">{{ agence.responsable.name }}</span>
      </div>
      <div class="detail-row" v-if="agence.gestionnaireStock">
        <span class="label">Gestionnaire Stock:</span>
        <span class="value">{{ agence.gestionnaireStock.name }}</span>
      </div>
    </div>
    <div class="detail-footer">
      <button @click="$emit('close')" class="btn-secondary">Fermer</button>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  agence: Object
})
const emit = defineEmits(['close'])
</script>

<style scoped>
.agence-detail {
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
.detail-row .value.active {
  color: #10b981;
}
.detail-row .value.inactive {
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
