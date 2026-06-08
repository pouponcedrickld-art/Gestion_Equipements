<template>
  <div class="agences-page">
    <div class="header">
      <h2><i class="pi pi-building"></i> Agences</h2>
      <button @click="showForm = true" class="btn-primary">
        <i class="pi pi-plus"></i> Nouvelle
      </button>
    </div>

    <div v-if="agenceStore.loading" class="loading">
      <i class="pi pi-spin pi-spinner"></i> Chargement...
    </div>

    <div v-else class="agences-grid">
      <!-- Siège -->
      <div v-if="agenceStore.agenceGenerale" class="agence-card generale">
        <div class="card-header">
          <span class="badge badge-generale">SIÈGE</span>
          <h3>{{ agenceStore.agenceGenerale.nom }}</h3>
        </div>
        <div class="card-body">
          <p><i class="pi pi-map-marker"></i> {{ agenceStore.agenceGenerale.ville || '—' }}</p>
          <p><i class="pi pi-envelope"></i> {{ agenceStore.agenceGenerale.email || '—' }}</p>
          <p><i class="pi pi-phone"></i> {{ agenceStore.agenceGenerale.telephone || '—' }}</p>
        </div>
        <div class="card-footer">
          <span>{{ agenceStore.sousAgences.length }} sous-agences</span>
          <button @click="editAgence(agenceStore.agenceGenerale)" class="btn-icon">
            <i class="pi pi-pencil"></i>
          </button>
        </div>
      </div>

      <!-- Sous-agences -->
      <div v-for="a in agenceStore.sousAgences" :key="a.id" class="agence-card">
        <div class="card-header">
          <span class="badge badge-sous">SOUS-AGENCE</span>
          <h3>{{ a.nom }}</h3>
        </div>
        <div class="card-body">
          <p><i class="pi pi-map-marker"></i> {{ a.ville || '—' }}</p>
          <p><i class="pi pi-user"></i> {{ a.responsable?.name || 'Pas de responsable' }}</p>
        </div>
        <div class="card-footer">
          <span :class="a.statut">{{ a.statut }}</span>
          <div class="actions">
            <button @click="editAgence(a)" class="btn-icon"><i class="pi pi-pencil"></i></button>
            <button @click="deleteAgence(a.id)" class="btn-icon btn-danger"><i class="pi pi-trash"></i></button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal -->
    <div v-if="showForm" class="modal-overlay" @click.self="showForm = false">
      <AgenceFormView :edit-data="editingAgence" @saved="onSaved" @cancel="showForm = false" />
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useAgenceStore } from '@/stores/agenceStore.js'
import AgenceFormView from './AgenceFormView.vue'

const agenceStore = useAgenceStore()
const showForm = ref(false)
const editingAgence = ref(null)

onMounted(() => agenceStore.fetchAgences())

const editAgence = (a) => { editingAgence.value = { ...a }; showForm.value = true }
const deleteAgence = async (id) => { if (!confirm('Supprimer ?')) return; await agenceStore.deleteAgence(id) }
const onSaved = () => { showForm.value = false; editingAgence.value = null; agenceStore.fetchAgences() }
</script>

<style scoped>
.agences-page { padding: 20px; }
.header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
.header h2 { color: #e2e8f0; margin: 0; }
.btn-primary { background: #3b82f6; color: white; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer; display: flex; align-items: center; gap: 8px; }
.agences-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; }
.agence-card { background: #1e293b; border: 1px solid #334155; border-radius: 10px; padding: 20px; }
.agence-card.generale { border-color: #3b82f6; }
.card-header { display: flex; align-items: center; gap: 10px; margin-bottom: 15px; }
.card-header h3 { margin: 0; color: #e2e8f0; font-size: 1.1rem; }
.badge { padding: 3px 10px; border-radius: 4px; font-size: 0.7rem; font-weight: bold; }
.badge-generale { background: #3b82f6; color: white; }
.badge-sous { background: #10b981; color: white; }
.card-body p { margin: 8px 0; color: #94a3b8; font-size: 0.9rem; display: flex; align-items: center; gap: 8px; }
.card-footer { display: flex; justify-content: space-between; align-items: center; margin-top: 15px; padding-top: 15px; border-top: 1px solid #334155; }
.card-footer span.active { color: #10b981; }
.card-footer span.inactive { color: #ef4444; }
.actions { display: flex; gap: 8px; }
.btn-icon { background: #334155; border: none; color: #e2e8f0; padding: 6px 10px; border-radius: 4px; cursor: pointer; }
.btn-danger:hover { background: #ef4444; }
.modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.7); display: flex; align-items: center; justify-content: center; z-index: 100; }
.loading { text-align: center; color: #94a3b8; padding: 40px; }
</style>
