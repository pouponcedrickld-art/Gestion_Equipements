<template>
  <AgenceLayout>
    <div class="pertes-container">
      <div class="header-bar">
        <div class="header-left">
          <h2>Gestion des Pertes & Casse</h2>
          <p>Signalez et validez les pertes, vols ou casse de matériel</p>
        </div>
        <div class="header-right">
          <button class="add-btn" @click="openAddModal">
            <i class="pi pi-plus"></i> Nouvelle Déclaration
          </button>
        </div>
      </div>

      <div class="filters-card">
        <div class="filters-row">
          <div class="search-box">
            <i class="pi pi-search"></i>
            <input v-model="search" type="text" placeholder="Rechercher une déclaration...">
          </div>
          <div class="select-box">
            <select v-model="filters.statut">
              <option value="">Tous les statuts</option>
              <option value="en attente">En attente</option>
              <option value="validée">Validée</option>
              <option value="clôturée">Clôturée</option>
            </select>
          </div>
        </div>
      </div>

      <div class="table-card">
        <div v-if="loading" class="loading-state">
          <i class="pi pi-spin pi-spinner"></i> Chargement...
        </div>
        <div v-else-if="pertes.length === 0" class="empty-state">
          <i class="pi pi-info-circle"></i>
          <p>Aucune déclaration trouvée.</p>
        </div>
        <div v-else class="table-wrapper">
          <table class="data-table">
            <thead>
              <tr>
                <th>Date</th>
                <th>Type</th>
                <th>Équipement</th>
                <th>Agent</th>
                <th>Statut</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="p in filteredPertes" :key="p.id">
                <td>{{ formatDate(p.date_declaration) }}</td>
                <td><span class="type-badge" :class="p.type">{{ formatType(p.type) }}</span></td>
                <td>{{ p.equipement?.nom }} ({{ p.equipement?.reference }})</td>
                <td>{{ p.agent?.nom }} {{ p.agent?.prenom }}</td>
                <td><span class="status-badge" :class="p.statut">{{ p.statut }}</span></td>
                <td>
                  <div class="actions">
                    <button v-if="p.statut === 'en attente'" class="validate-btn" @click="validatePerte(p)">Valider</button>
                    <button v-if="p.statut === 'en attente'" class="edit-btn" @click="openEditModal(p)">Modifier</button>
                    <button class="delete-btn" @click="deletePerte(p)">Supprimer</button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <Dialog 
        v-model:visible="showModal" 
        :header="isEdit ? 'Modifier la Déclaration' : 'Nouvelle Déclaration'" 
        :style="{ width: '500px' }" 
        modal
        class="p-fluid dark-modal"
      >
        <form @submit.prevent="submitPerte" class="perte-form">
          <div class="field mb-4">
            <label class="font-bold block mb-2">Type</label>
            <select v-model="perteForm.type" class="w-full" required>
              <option value="perte">Perte</option>
              <option value="vol">Vol</option>
              <option value="casse">Casse</option>
            </select>
          </div>
          <div class="field mb-4">
            <label class="font-bold block mb-2">Équipement</label>
            <select v-model="perteForm.equipement_id" class="w-full" required>
              <option value="">Sélectionner un équipement</option>
              <option v-for="eq in equipements" :key="eq.id" :value="eq.id">{{ eq.nom }} ({{ eq.reference }})</option>
            </select>
          </div>
          <div class="field mb-4">
            <label class="font-bold block mb-2">Agent</label>
            <select v-model="perteForm.agent_id" class="w-full" required>
              <option value="">Sélectionner un agent</option>
              <option v-for="a in agents" :key="a.id" :value="a.id">{{ a.nom }} {{ a.prenom }}</option>
            </select>
          </div>
          <div class="field mb-4">
            <label class="font-bold block mb-2">Description</label>
            <textarea v-model="perteForm.description" rows="4" class="w-full" required></textarea>
          </div>
          <div v-if="isEdit" class="field mb-4">
            <label class="font-bold block mb-2">Statut</label>
            <select v-model="perteForm.statut" class="w-full">
              <option value="en attente">En attente</option>
              <option value="validée">Validée</option>
              <option value="clôturée">Clôturée</option>
            </select>
          </div>
          <div class="modal-footer">
            <Button label="Annuler" class="p-button-secondary" @click="showModal = false" />
            <Button label="Enregistrer" type="submit" :loading="submitting" class="p-button-primary" />
          </div>
        </form>
      </Dialog>
    </div>
  </AgenceLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import AgenceLayout from '@/layouts/AgenceLayout.vue'
import { usePerteStore } from '@/stores/perteStore.js'
import { useEquipementStore } from '@/stores/equipementStore.js'
import { useAgentStore } from '@/stores/agentStore.js'
import Dialog from 'primevue/dialog'
import Button from 'primevue/button'
import { useToast } from 'primevue/usetoast'

const perteStore = usePerteStore()
const equipementStore = useEquipementStore()
const agentStore = useAgentStore()
const toast = useToast()

const pertes = ref([])
const equipements = ref([])
const agents = ref([])
const loading = ref(false)
const submitting = ref(false)
const search = ref('')
const filters = ref({ statut: '' })
const showModal = ref(false)
const isEdit = ref(false)
const perteForm = ref({})

const filteredPertes = computed(() => {
  return pertes.value.filter(p => {
    const matchesSearch = !search.value || 
      p.equipement?.nom.toLowerCase().includes(search.value.toLowerCase()) ||
      p.agent?.nom.toLowerCase().includes(search.value.toLowerCase())
    const matchesStatut = !filters.value.statut || p.statut === filters.value.statut
    return matchesSearch && matchesStatut
  })
})

const fetchData = async () => {
  loading.value = true
  await Promise.all([
    perteStore.fetchPertes(),
    equipementStore.fetchEquipements(),
    agentStore.fetchAgents()
  ])
  pertes.value = perteStore.pertes
  equipements.value = equipementStore.equipements
  agents.value = agentStore.agents
  loading.value = false
}

const openAddModal = () => {
  isEdit.value = false
  perteForm.value = { type: 'perte', equipement_id: '', agent_id: '', description: '', statut: 'en attente' }
  showModal.value = true
}

const openEditModal = (perte) => {
  isEdit.value = true
  perteForm.value = { ...perte, equipement_id: perte.equipement_id, agent_id: perte.agent_id }
  showModal.value = true
}

const submitPerte = async () => {
  submitting.value = true
  try {
    if (isEdit.value) {
      await perteStore.updatePerte(perteForm.value.id, perteForm.value)
    } else {
      await perteStore.createPerte(perteForm.value)
    }
    toast.add({ severity: 'success', summary: 'Succès', detail: 'Déclaration enregistrée', life: 3000 })
    showModal.value = false
    await fetchData()
  } catch (err) {
    toast.add({ severity: 'error', summary: 'Erreur', detail: 'Échec de l\'enregistrement', life: 3000 })
  } finally {
    submitting.value = false
  }
}

const validatePerte = async (perte) => {
  try {
    await perteStore.updatePerte(perte.id, { ...perte, statut: 'validée' })
    toast.add({ severity: 'success', summary: 'Succès', detail: 'Déclaration validée', life: 3000 })
    await fetchData()
  } catch (err) {
    toast.add({ severity: 'error', summary: 'Erreur', detail: 'Échec de la validation', life: 3000 })
  }
}

const deletePerte = async (perte) => {
  if (confirm('Supprimer cette déclaration ?')) {
    try {
      await perteStore.deletePerte(perte.id)
      toast.add({ severity: 'success', summary: 'Succès', detail: 'Déclaration supprimée', life: 3000 })
      await fetchData()
    } catch (err) {
      toast.add({ severity: 'error', summary: 'Erreur', detail: 'Échec de la suppression', life: 3000 })
    }
  }
}

const formatDate = (date) => {
  if (!date) return ''
  return new Date(date).toLocaleDateString('fr-FR')
}

const formatType = (type) => {
  return { perte: 'Perte', vol: 'Vol', casse: 'Casse' }[type] || type
}

onMounted(fetchData)
</script>

<style scoped>
.pertes-container { padding: 24px; color: #f8fafc; }
.header-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; }
.header-bar h2 { margin: 0; font-size: 1.5rem; }
.header-bar p { color: #94a3b8; margin: 4px 0 0 0; }
.add-btn { background: #3b82f6; color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; display: flex; align-items: center; gap: 8px; font-weight: 600; }
.add-btn:hover { background: #2563eb; }
.filters-card { background: #1e293b; border: 1px solid #334155; padding: 16px; border-radius: 12px; margin-bottom: 20px; }
.filters-row { display: flex; gap: 16px; flex-wrap: wrap; }
.search-box { position: relative; flex: 1; min-width: 200px; }
.search-box i { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8; }
.search-box input, select { background: #0f172a; border: 1px solid #334155; color: #f8fafc; padding: 10px 12px 10px 40px; border-radius: 8px; width: 100%; }
select { padding-left: 12px; width: 180px; }
.table-card { background: #1e293b; border: 1px solid #334155; border-radius: 12px; overflow: hidden; }
.data-table { width: 100%; border-collapse: collapse; }
.data-table th { background: #0f172a; padding: 14px 16px; text-align: left; color: #94a3b8; font-size: 0.85rem; font-weight: 600; text-transform: uppercase; }
.data-table td { padding: 14px 16px; border-bottom: 1px solid #334155; }
.type-badge, .status-badge { padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 700; }
.type-badge.perte { background: rgba(139, 92, 246, 0.15); color: #8b5cf6; }
.type-badge.vol { background: rgba(239, 68, 68, 0.15); color: #ef4444; }
.type-badge.casse { background: rgba(245, 158, 11, 0.15); color: #f59e0b; }
.status-badge.en-attente { background: rgba(107, 114, 128, 0.15); color: #94a3b8; }
.status-badge.validée { background: rgba(59, 130, 246, 0.15); color: #3b82f6; }
.status-badge.clôturée { background: rgba(16, 185, 129, 0.15); color: #10b981; }
.actions { display: flex; gap: 8px; }
.validate-btn { background: #10b981; color: white; border: none; padding: 6px 12px; border-radius: 6px; cursor: pointer; }
.edit-btn { background: #334155; color: white; border: none; padding: 6px 12px; border-radius: 6px; cursor: pointer; }
.delete-btn { background: #ef4444; color: white; border: none; padding: 6px 12px; border-radius: 6px; cursor: pointer; }
.loading-state, .empty-state { padding: 60px; text-align: center; color: #94a3b8; }
.loading-state i { font-size: 2rem; margin-bottom: 12px; color: #3b82f6; }
.modal-footer { display: flex; justify-content: flex-end; gap: 8px; margin-top: 16px; }
.perte-form select, .perte-form textarea { background: #0f172a; border: 1px solid #334155; color: #f8fafc; padding: 8px; border-radius: 6px; width: 100%; }
:deep(.dark-modal) .p-dialog-content, :deep(.dark-modal) .p-dialog-header { background: #1e293b; color: #f8fafc; border-color: #334155; }
</style>
