<template>
  <AgenceLayout>
    <div class="pannes-container">
      <div class="header-bar">
        <div class="header-left">
          <h2>Gestion des Pannes</h2>
          <p>Signalez et suivez les pannes de matériel</p>
          <p>Debug: showModal = {{ showModal }}</p>
        </div>
        <div class="header-right">
          <button class="add-btn" @click="openAddModal">
            <i class="pi pi-plus"></i> Nouvelle Panne
          </button>
        </div>
      </div>

      <!-- Debug inline modal first -->
      <div v-if="showModal"
        style="position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(255,0,0,0.3);z-index:9999;display:flex;align-items:center;justify-content:center;">
        <div style="background:#1e293b;padding:20px;border-radius:12px;width:550px;color:white;">
          <h3>Nouvelle Panne</h3>
          <p>🎉 Modal affiché!</p>
          <button @click="showModal = false"
            style="background:#3b82f6;color:white;border:none;padding:8px 16px;border-radius:6px;cursor:pointer;">Fermer</button>
        </div>
      </div>

      <div class="filters-card">
        <div class="filters-row">
          <div class="search-box">
            <i class="pi pi-search"></i>
            <input v-model="search" type="text" placeholder="Rechercher une panne...">
          </div>
          <div class="select-box">
            <select v-model="filters.statut">
              <option value="">Tous les statuts</option>
              <option value="déclarée">Déclarée</option>
              <option value="en cours">En cours</option>
              <option value="réparée">Réparée</option>
              <option value="irrécupérable">Irrécupérable</option>
              <option value="remplacée">Remplacée</option>
            </select>
          </div>
          <div class="select-box">
            <select v-model="filters.niveau_gravite">
              <option value="">Toutes les gravités</option>
              <option value="basse">Basse</option>
              <option value="moyenne">Moyenne</option>
              <option value="haute">Haute</option>
              <option value="critique">Critique</option>
            </select>
          </div>
        </div>
      </div>

      <div class="table-card">
        <div v-if="loading" class="loading-state">
          <i class="pi pi-spin pi-spinner"></i> Chargement...
        </div>
        <div v-else-if="pannes.length === 0" class="empty-state">
          <i class="pi pi-info-circle"></i>
          <p>Aucune panne trouvée.</p>
        </div>
        <div v-else class="table-wrapper">
          <table class="data-table">
            <thead>
              <tr>
                <th>Date</th>
                <th>Équipement</th>
                <th>Agent</th>
                <th>Gravité</th>
                <th>Statut</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="p in filteredPannes" :key="p.id">
                <td>{{ formatDate(p.date_declaration) }}</td>
                <td>{{ p.equipement?.nom }} ({{ p.equipement?.reference }})</td>
                <td>{{ p.agent?.nom }} {{ p.agent?.prenom }}</td>
                <td><span class="gravite-badge" :class="p.niveau_gravite">{{ p.niveau_gravite }}</span></td>
                <td><span class="status-badge" :class="p.statut">{{ p.statut }}</span></td>
                <td>
                  <div class="actions">
                    <button class="detail-btn" @click="showDetail(p)">Détails</button>
                    <button v-if="p.statut === 'déclarée'" class="transmettre-btn"
                      @click="transmettrePanne(p)">Transmettre</button>
                    <button v-if="p.statut === 'en cours'" class="diagnostic-btn"
                      @click="openDiagnosticModal(p)">Diagnostic</button>
                    <button class="edit-btn" @click="openEditModal(p)">Modifier</button>
                    <button class="delete-btn" @click="deletePanne(p)">Supprimer</button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <Dialog v-model:visible="showModal" :header="isEdit ? 'Modifier la Panne' : 'Nouvelle Panne'"
        :style="{ width: '550px' }" modal class="p-fluid dark-modal">
        <form @submit.prevent="submitPanne" class="panne-form">
          <div class="field mb-4">
            <label class="font-bold block mb-2">Équipement</label>
            <select v-model="panneForm.equipement_id" class="w-full" required>
              <option value="">Sélectionner un équipement</option>
              <option v-for="eq in equipements" :key="eq.id" :value="eq.id">{{ eq.nom }} ({{ eq.reference }})</option>
            </select>
          </div>
          <div class="field mb-4">
            <label class="font-bold block mb-2">Agent</label>
            <select v-model="panneForm.agent_id" class="w-full" required>
              <option value="">Sélectionner un agent</option>
              <option v-for="a in agents" :key="a.id" :value="a.id">{{ a.nom }} {{ a.prenom }}</option>
            </select>
          </div>
          <div class="field mb-4">
            <label class="font-bold block mb-2">Description</label>
            <textarea v-model="panneForm.description" rows="4" class="w-full" required></textarea>
          </div>
          <div class="field mb-4">
            <label class="font-bold block mb-2">Niveau de Gravité</label>
            <select v-model="panneForm.niveau_gravite" class="w-full" required>
              <option value="basse">Basse</option>
              <option value="moyenne">Moyenne</option>
              <option value="haute">Haute</option>
              <option value="critique">Critique</option>
            </select>
          </div>
          <div v-if="isEdit" class="field mb-4">
            <label class="font-bold block mb-2">Statut</label>
            <select v-model="panneForm.statut" class="w-full">
              <option value="déclarée">Déclarée</option>
              <option value="en cours">En cours</option>
              <option value="réparée">Réparée</option>
              <option value="irrécupérable">Irrécupérable</option>
              <option value="remplacée">Remplacée</option>
            </select>
          </div>
          <div v-if="isEdit" class="field mb-4">
            <label class="font-bold block mb-2">Diagnostic</label>
            <textarea v-model="panneForm.diagnostic_technicien" rows="3" class="w-full"></textarea>
          </div>
          <div v-if="isEdit" class="field mb-4">
            <label class="font-bold block mb-2">Action Réalisée</label>
            <textarea v-model="panneForm.action_realisee" rows="3" class="w-full"></textarea>
          </div>
          <div v-if="isEdit" class="field mb-4">
            <label class="font-bold block mb-2">Coût Réparation</label>
            <input v-model="panneForm.cout_reparation" type="number" step="0.01" class="w-full">
          </div>
          <div class="modal-footer">
            <Button label="Annuler" class="p-button-secondary" @click="showModal = false" />
            <Button label="Enregistrer" type="submit" :loading="submitting" class="p-button-primary" />
          </div>
        </form>
      </Dialog>

      <Dialog v-model:visible="showDiagnosticModal" header="Ajouter un Diagnostic" :style="{ width: '500px' }" modal
        class="p-fluid dark-modal">
        <form @submit.prevent="submitDiagnostic" class="panne-form">
          <div class="field mb-4">
            <label class="font-bold block mb-2">Diagnostic</label>
            <textarea v-model="diagnosticForm.diagnostic_technicien" rows="5" class="w-full" required></textarea>
          </div>
          <div class="modal-footer">
            <Button label="Annuler" class="p-button-secondary" @click="showDiagnosticModal = false" />
            <Button label="Enregistrer" type="submit" :loading="submitting" class="p-button-primary" />
          </div>
        </form>
      </Dialog>

      <Dialog v-model:visible="showDetailModal" header="Détails de la Panne" :style="{ width: '600px' }" modal
        class="p-fluid dark-modal">
        <div v-if="selectedPanne" class="detail-content">
          <div class="detail-row"><span class="label">Équipement:</span> <span class="value">{{
            selectedPanne.equipement?.nom }} ({{ selectedPanne.equipement?.reference }})</span></div>
          <div class="detail-row"><span class="label">Agent:</span> <span class="value">{{ selectedPanne.agent?.nom }}
              {{ selectedPanne.agent?.prenom }}</span></div>
          <div class="detail-row"><span class="label">Date Déclaration:</span> <span class="value">{{
            formatDate(selectedPanne.date_declaration) }}</span></div>
          <div class="detail-row"><span class="label">Gravité:</span> <span class="value"><span class="gravite-badge"
                :class="selectedPanne.niveau_gravite">{{ selectedPanne.niveau_gravite }}</span></span></div>
          <div class="detail-row"><span class="label">Statut:</span> <span class="value"><span class="status-badge"
                :class="selectedPanne.statut">{{ selectedPanne.statut }}</span></span></div>
          <div class="detail-row"><span class="label">Description:</span></div>
          <div class="detail-text">{{ selectedPanne.description }}</div>
          <div v-if="selectedPanne.diagnostic_technicien" class="detail-row mt-4"><span class="label">Diagnostic:</span>
          </div>
          <div v-if="selectedPanne.diagnostic_technicien" class="detail-text">{{ selectedPanne.diagnostic_technicien }}
          </div>
          <div v-if="selectedPanne.action_realisee" class="detail-row mt-4"><span class="label">Action Réalisée:</span>
          </div>
          <div v-if="selectedPanne.action_realisee" class="detail-text">{{ selectedPanne.action_realisee }}</div>
          <div v-if="selectedPanne.cout_reparation" class="detail-row"><span class="label">Coût Réparation:</span> <span
              class="value">{{ selectedPanne.cout_reparation }} €</span></div>
          <div v-if="selectedPanne.date_resolution" class="detail-row"><span class="label">Date Résolution:</span> <span
              class="value">{{ formatDate(selectedPanne.date_resolution) }}</span></div>
        </div>
        <div class="modal-footer">
          <Button label="Fermer" class="p-button-secondary" @click="showDetailModal = false" />
        </div>
      </Dialog>
    </div>
  </AgenceLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import AgenceLayout from '@/layouts/AgenceLayout.vue'
import { usePanneStore } from '@/stores/panneStore.js'
import { useEquipementStore } from '@/stores/equipementStore.js'
import { useAgentStore } from '@/stores/agentStore.js'
import Dialog from 'primevue/dialog'
import Button from 'primevue/button'
import { useToast } from 'primevue/usetoast'

const panneStore = usePanneStore()
const equipementStore = useEquipementStore()
const agentStore = useAgentStore()
const toast = useToast()

const pannes = ref([])
const equipements = ref([])
const agents = ref([])
const loading = ref(false)
const submitting = ref(false)
const search = ref('')
const filters = ref({ statut: '', niveau_gravite: '' })
const showModal = ref(false)
const showDiagnosticModal = ref(false)
const showDetailModal = ref(false)
const isEdit = ref(false)
const selectedPanne = ref(null)
const panneForm = ref({})
const diagnosticForm = ref({})

const filteredPannes = computed(() => {
  return pannes.value.filter(p => {
    const matchesSearch = !search.value ||
      p.equipement?.nom.toLowerCase().includes(search.value.toLowerCase()) ||
      p.agent?.nom.toLowerCase().includes(search.value.toLowerCase()) ||
      p.description.toLowerCase().includes(search.value.toLowerCase())
    const matchesStatut = !filters.value.statut || p.statut === filters.value.statut
    const matchesGravite = !filters.value.niveau_gravite || p.niveau_gravite === filters.value.niveau_gravite
    return matchesSearch && matchesStatut && matchesGravite
  })
})

const fetchData = async () => {
  loading.value = true
  await Promise.all([
    panneStore.fetchPannes(),
    equipementStore.fetchEquipements(),
    agentStore.fetchAgents()
  ])
  pannes.value = panneStore.pannes
  equipements.value = equipementStore.equipements
  agents.value = agentStore.agents
  loading.value = false
}

const openAddModal = () => {
  console.log('Nouvelle Panne clicked!')
  isEdit.value = false
  panneForm.value = { equipement_id: '', agent_id: '', description: '', niveau_gravite: 'moyenne', statut: 'déclarée' }
  console.log('showModal set to true!')
  showModal.value = true
}

const openEditModal = (panne) => {
  isEdit.value = true
  panneForm.value = { ...panne, equipement_id: panne.equipement_id, agent_id: panne.agent_id }
  showModal.value = true
}

const openDiagnosticModal = (panne) => {
  selectedPanne.value = panne
  diagnosticForm.value = { panne_id: panne.id, diagnostic_technicien: '' }
  showDiagnosticModal.value = true
}

const showDetail = (panne) => {
  selectedPanne.value = panne
  showDetailModal.value = true
}

const submitPanne = async () => {
  submitting.value = true
  try {
    if (isEdit.value) {
      await panneStore.updatePanne(panneForm.value.id, panneForm.value)
    } else {
      await panneStore.createPanne(panneForm.value)
    }
    toast.add({ severity: 'success', summary: 'Succès', detail: 'Panne enregistrée', life: 3000 })
    showModal.value = false
    await fetchData()
  } catch (err) {
    toast.add({ severity: 'error', summary: 'Erreur', detail: 'Échec de l\'enregistrement', life: 3000 })
  } finally {
    submitting.value = false
  }
}

const submitDiagnostic = async () => {
  submitting.value = true
  try {
    await panneStore.diagnostiquer(diagnosticForm.value.panne_id, diagnosticForm.value)
    toast.add({ severity: 'success', summary: 'Succès', detail: 'Diagnostic enregistré', life: 3000 })
    showDiagnosticModal.value = false
    await fetchData()
  } catch (err) {
    toast.add({ severity: 'error', summary: 'Erreur', detail: 'Échec du diagnostic', life: 3000 })
  } finally {
    submitting.value = false
  }
}

const transmettrePanne = async (panne) => {
  try {
    await panneStore.transmettreMaintenance(panne.id, {})
    toast.add({ severity: 'success', summary: 'Succès', detail: 'Panne transmise', life: 3000 })
    await fetchData()
  } catch (err) {
    toast.add({ severity: 'error', summary: 'Erreur', detail: 'Échec de la transmission', life: 3000 })
  }
}

const deletePanne = async (panne) => {
  if (confirm('Supprimer cette panne ?')) {
    try {
      await panneStore.deletePanne(panne.id)
      toast.add({ severity: 'success', summary: 'Succès', detail: 'Panne supprimée', life: 3000 })
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

onMounted(fetchData)
</script>

<style scoped>
.pannes-container {
  padding: 24px;
  color: #f8fafc;
}

.header-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
}

.header-bar h2 {
  margin: 0;
  font-size: 1.5rem;
}

.header-bar p {
  color: #94a3b8;
  margin: 4px 0 0 0;
}

.add-btn {
  background: #3b82f6;
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 8px;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 8px;
  font-weight: 600;
}

.add-btn:hover {
  background: #2563eb;
}

.filters-card {
  background: #1e293b;
  border: 1px solid #334155;
  padding: 16px;
  border-radius: 12px;
  margin-bottom: 20px;
}

.filters-row {
  display: flex;
  gap: 16px;
  flex-wrap: wrap;
}

.search-box {
  position: relative;
  flex: 1;
  min-width: 200px;
}

.search-box i {
  position: absolute;
  left: 12px;
  top: 50%;
  transform: translateY(-50%);
  color: #94a3b8;
}

.search-box input,
select {
  background: #0f172a;
  border: 1px solid #334155;
  color: #f8fafc;
  padding: 10px 12px 10px 40px;
  border-radius: 8px;
  width: 100%;
}

select {
  padding-left: 12px;
  width: 180px;
}

.table-card {
  background: #1e293b;
  border: 1px solid #334155;
  border-radius: 12px;
  overflow: hidden;
}

.data-table {
  width: 100%;
  border-collapse: collapse;
}

.data-table th {
  background: #0f172a;
  padding: 14px 16px;
  text-align: left;
  color: #94a3b8;
  font-size: 0.85rem;
  font-weight: 600;
  text-transform: uppercase;
}

.data-table td {
  padding: 14px 16px;
  border-bottom: 1px solid #334155;
}

.gravite-badge,
.status-badge {
  padding: 4px 10px;
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: 700;
}

.gravite-badge.basse {
  background: rgba(16, 185, 129, 0.15);
  color: #10b981;
}

.gravite-badge.moyenne {
  background: rgba(245, 158, 11, 0.15);
  color: #f59e0b;
}

.gravite-badge.haute {
  background: rgba(239, 68, 68, 0.15);
  color: #ef4444;
}

.gravite-badge.critique {
  background: rgba(239, 68, 68, 0.3);
  color: #ef4444;
}

.status-badge.déclarée {
  background: rgba(107, 114, 128, 0.15);
  color: #94a3b8;
}

.status-badge.en-cours {
  background: rgba(59, 130, 246, 0.15);
  color: #3b82f6;
}

.status-badge.réparée {
  background: rgba(16, 185, 129, 0.15);
  color: #10b981;
}

.status-badge.irrécupérable {
  background: rgba(239, 68, 68, 0.15);
  color: #ef4444;
}

.status-badge.remplacée {
  background: rgba(139, 92, 246, 0.15);
  color: #8b5cf6;
}

.actions {
  display: flex;
  gap: 8px;
}

.detail-btn {
  background: #334155;
  color: white;
  border: none;
  padding: 6px 12px;
  border-radius: 6px;
  cursor: pointer;
}

.transmettre-btn {
  background: #f59e0b;
  color: white;
  border: none;
  padding: 6px 12px;
  border-radius: 6px;
  cursor: pointer;
}

.diagnostic-btn {
  background: #3b82f6;
  color: white;
  border: none;
  padding: 6px 12px;
  border-radius: 6px;
  cursor: pointer;
}

.edit-btn {
  background: #334155;
  color: white;
  border: none;
  padding: 6px 12px;
  border-radius: 6px;
  cursor: pointer;
}

.delete-btn {
  background: #ef4444;
  color: white;
  border: none;
  padding: 6px 12px;
  border-radius: 6px;
  cursor: pointer;
}

.loading-state,
.empty-state {
  padding: 60px;
  text-align: center;
  color: #94a3b8;
}

.loading-state i {
  font-size: 2rem;
  margin-bottom: 12px;
  color: #3b82f6;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 8px;
  margin-top: 16px;
}

.panne-form select,
.panne-form textarea,
.panne-form input {
  background: #0f172a;
  border: 1px solid #334155;
  color: #f8fafc;
  padding: 8px;
  border-radius: 6px;
  width: 100%;
}

.detail-content {
  padding: 8px 0;
}

.detail-row {
  display: flex;
  gap: 8px;
  margin-bottom: 12px;
}

.detail-row .label {
  font-weight: 600;
  color: #94a3b8;
  min-width: 140px;
}

.detail-row .value {
  color: #e2e8f0;
}

.detail-text {
  padding: 12px;
  background: #0f172a;
  border-radius: 8px;
  color: #e2e8f0;
  line-height: 1.5;
}

.mt-4 {
  margin-top: 16px;
}

:deep(.dark-modal) .p-dialog-content,
:deep(.dark-modal) .p-dialog-header {
  background: #1e293b;
  color: #f8fafc;
  border-color: #334155;
}
</style>
