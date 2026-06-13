<template>
  <AgenceLayout>
    <div class="maintenances-container">
      <div class="header-bar">
        <div class="header-left">
          <h2>Gestion des Maintenances</h2>
          <p>Suivez les maintenances préventives et correctives</p>
        </div>
        <div class="header-right">
          <button class="add-btn" @click="openAddModal">
            <i class="pi pi-plus"></i> Nouvelle Maintenance
          </button>
        </div>
      </div>

      <div class="filters-card">
        <div class="filters-row">
          <div class="search-box">
            <i class="pi pi-search"></i>
            <input v-model="search" type="text" placeholder="Rechercher une maintenance...">
          </div>
          <div class="select-box">
            <select v-model="filters.statut">
              <option value="">Tous les statuts</option>
              <option value="planifiee">Planifiée</option>
              <option value="en_cours">En cours</option>
              <option value="terminee">Terminée</option>
            </select>
          </div>
          <div class="select-box">
            <select v-model="filters.type_maintenance">
              <option value="">Tous les types</option>
              <option value="preventive">Préventive</option>
              <option value="corrective">Corrective</option>
            </select>
          </div>
        </div>
      </div>

      <div class="table-card">
        <div v-if="loading" class="loading-state">
          <i class="pi pi-spin pi-spinner"></i> Chargement...
        </div>
        <div v-else-if="maintenances.length === 0" class="empty-state">
          <i class="pi pi-info-circle"></i>
          <p>Aucune maintenance trouvée.</p>
        </div>
        <div v-else class="table-wrapper">
          <table class="data-table">
            <thead>
              <tr>
                <th>Date Prévue</th>
                <th>Équipement</th>
                <th>Type</th>
                <th>Statut</th>
                <th>Technicien</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="m in filteredMaintenances" :key="m.id">
                <td>{{ formatDate(m.date_prevue) }}</td>
                <td>{{ m.equipement?.nom }} ({{ m.equipement?.reference }})</td>
                <td><span class="type-badge" :class="m.type_maintenance">{{ formatType(m.type_maintenance) }}</span></td>
                <td><span class="status-badge" :class="m.statut">{{ formatStatus(m.statut) }}</span></td>
                <td>{{ m.technicienUser?.name || '-' }}</td>
                <td>
                  <div class="actions">
                    <button class="detail-btn" @click="showDetail(m)">Détails</button>
                    <button v-if="m.statut === 'planifiee'" class="start-btn" @click="startMaintenance(m)">Démarrer</button>
                    <button v-if="m.statut === 'en_cours'" class="complete-btn" @click="openCompleteModal(m)">Terminer</button>
                    <button class="edit-btn" @click="openEditModal(m)">Modifier</button>
                    <button class="delete-btn" @click="confirmDelete(m)">Supprimer</button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <Dialog v-model:visible="showModal" :header="isEdit ? 'Modifier la Maintenance' : 'Nouvelle Maintenance'"
        :style="{ width: '600px' }" modal class="p-fluid dark-modal">
        <form @submit.prevent="submitMaintenance" class="maintenance-form">
          <div class="field mb-4">
            <label class="font-bold block mb-2">Équipement</label>
            <select v-model="maintenanceForm.equipement_id" class="w-full" required>
              <option value="">Sélectionner un équipement</option>
              <option v-for="eq in equipements" :key="eq.id" :value="eq.id">{{ eq.nom }} ({{ eq.reference }})</option>
            </select>
          </div>
          <div class="field mb-4">
            <label class="font-bold block mb-2">Type Maintenance</label>
            <select v-model="maintenanceForm.type_maintenance" class="w-full" required>
              <option value="preventive">Préventive</option>
              <option value="corrective">Corrective</option>
            </select>
          </div>
          <div class="field mb-4">
            <label class="font-bold block mb-2">Date Prévue</label>
            <input v-model="maintenanceForm.date_prevue" type="date" class="w-full" required />
          </div>
          <div v-if="maintenanceForm.type_maintenance === 'corrective'" class="field mb-4">
            <label class="font-bold block mb-2">Panne associée</label>
            <select v-model="maintenanceForm.panne_id" class="w-full">
              <option value="">Aucune</option>
              <option v-for="p in pannes" :key="p.id" :value="p.id">{{ formatDate(p.date_declaration) }} - {{ p.description }}</option>
            </select>
          </div>
          <div class="field mb-4">
            <label class="font-bold block mb-2">Technicien</label>
            <select v-model="maintenanceForm.technicien_id" class="w-full">
              <option value="">Sélectionner un technicien</option>
              <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }}</option>
            </select>
          </div>
          <div class="field mb-4">
            <label class="font-bold block mb-2">Diagnostic</label>
            <textarea v-model="maintenanceForm.diagnostic" rows="3" class="w-full"></textarea>
          </div>
          <div class="field mb-4">
            <label class="font-bold block mb-2">Coût</label>
            <input v-model="maintenanceForm.cout" type="number" step="0.01" class="w-full" />
          </div>
          <div v-if="isEdit && maintenanceForm.statut === 'terminee'" class="field mb-4">
            <label class="font-bold block mb-2">Résultat / Observations</label>
            <textarea v-model="maintenanceForm.observations" rows="3" class="w-full"></textarea>
          </div>
          <div class="modal-footer">
            <Button label="Annuler" class="p-button-secondary" @click="showModal = false" />
            <Button label="Enregistrer" type="submit" :loading="submitting" class="p-button-primary" />
          </div>
        </form>
      </Dialog>

      <Dialog v-model:visible="showCompleteModal" header="Terminer la Maintenance" :style="{ width: '500px' }" modal
        class="p-fluid dark-modal">
        <form @submit.prevent="submitComplete" class="maintenance-form">
          <div class="field mb-4">
            <label class="font-bold block mb-2">Date Fin</label>
            <input v-model="completeForm.date_fin" type="date" class="w-full" required />
          </div>
          <div class="field mb-4">
            <label class="font-bold block mb-2">Diagnostic Final</label>
            <textarea v-model="completeForm.diagnostic" rows="3" class="w-full"></textarea>
          </div>
          <div class="field mb-4">
            <label class="font-bold block mb-2">Coût Final</label>
            <input v-model="completeForm.cout" type="number" step="0.01" class="w-full" />
          </div>
          <div class="field mb-4">
            <label class="font-bold block mb-2">Observations / Résultat</label>
            <textarea v-model="completeForm.observations" rows="3" class="w-full"></textarea>
          </div>
          <div class="modal-footer">
            <Button label="Annuler" class="p-button-secondary" @click="showCompleteModal = false" />
            <Button label="Terminer" type="submit" :loading="submitting" class="p-button-primary" />
          </div>
        </form>
      </Dialog>

      <Dialog v-model:visible="showDetailModal" header="Détails de la Maintenance" :style="{ width: '600px' }" modal
        class="p-fluid dark-modal">
        <div v-if="selectedMaintenance" class="detail-content">
          <div class="detail-row"><span class="label">Équipement:</span> <span class="value">{{
              selectedMaintenance.equipement?.nom }} ({{ selectedMaintenance.equipement?.reference }})</span></div>
          <div class="detail-row"><span class="label">Type:</span> <span class="value"><span class="type-badge"
                :class="selectedMaintenance.type_maintenance">{{ formatType(selectedMaintenance.type_maintenance) }}</span></span></div>
          <div class="detail-row"><span class="label">Statut:</span> <span class="value"><span class="status-badge"
                :class="selectedMaintenance.statut">{{ formatStatus(selectedMaintenance.statut) }}</span></span></div>
          <div class="detail-row"><span class="label">Date Prévue:</span> <span class="value">{{
              formatDate(selectedMaintenance.date_prevue) }}</span></div>
          <div v-if="selectedMaintenance.date_debut" class="detail-row"><span class="label">Date Début:</span> <span class="value">{{
              formatDate(selectedMaintenance.date_debut) }}</span></div>
          <div v-if="selectedMaintenance.date_fin" class="detail-row"><span class="label">Date Fin:</span> <span class="value">{{
              formatDate(selectedMaintenance.date_fin) }}</span></div>
          <div v-if="selectedMaintenance.technicienUser" class="detail-row"><span class="label">Technicien:</span> <span class="value">{{
              selectedMaintenance.technicienUser.name }}</span></div>
          <div v-if="selectedMaintenance.panne" class="detail-row"><span class="label">Panne associée:</span> <span class="value">{{
              formatDate(selectedMaintenance.panne.date_declaration) }} - {{ selectedMaintenance.panne.description }}</span></div>
          <div v-if="selectedMaintenance.diagnostic" class="detail-row mt-4"><span class="label">Diagnostic:</span></div>
          <div v-if="selectedMaintenance.diagnostic" class="detail-text">{{ selectedMaintenance.diagnostic }}</div>
          <div v-if="selectedMaintenance.cout" class="detail-row"><span class="label">Coût:</span> <span class="value">{{
              selectedMaintenance.cout }} €</span></div>
          <div v-if="selectedMaintenance.observations" class="detail-row mt-4"><span class="label">Observations / Résultat:</span></div>
          <div v-if="selectedMaintenance.observations" class="detail-text">{{ selectedMaintenance.observations }}</div>
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
import { useMaintenanceStore } from '@/stores/maintenanceStore.js'
import { useEquipementStore } from '@/stores/equipementStore.js'
import { usePanneStore } from '@/stores/panneStore.js'
import { useUserStore } from '@/stores/userStore.js'
import Dialog from 'primevue/dialog'
import Button from 'primevue/button'
import { useToast } from 'primevue/usetoast'
import { useConfirm } from 'primevue/useconfirm'

const maintenanceStore = useMaintenanceStore()
const equipementStore = useEquipementStore()
const panneStore = usePanneStore()
const userStore = useUserStore()
const toast = useToast()
const confirm = useConfirm()

const maintenances = ref([])
const equipements = ref([])
const pannes = ref([])
const users = ref([])
const loading = ref(false)
const submitting = ref(false)
const search = ref('')
const filters = ref({ statut: '', type_maintenance: '' })
const showModal = ref(false)
const showCompleteModal = ref(false)
const showDetailModal = ref(false)
const isEdit = ref(false)
const selectedMaintenance = ref(null)
const maintenanceForm = ref({})
const completeForm = ref({})

const filteredMaintenances = computed(() => {
  return maintenances.value.filter(m => {
    const matchesSearch = !search.value ||
      (m.equipement?.nom?.toLowerCase().includes(search.value.toLowerCase()) ||
        m.diagnostic?.toLowerCase().includes(search.value.toLowerCase()))
    const matchesStatut = !filters.value.statut || m.statut === filters.value.statut
    const matchesType = !filters.value.type_maintenance || m.type_maintenance === filters.value.type_maintenance
    return matchesSearch && matchesStatut && matchesType
  })
})

const formatStatus = (statut) => {
  const statusMap = {
    'planifiee': 'Planifiée',
    'en_cours': 'En cours',
    'terminee': 'Terminée'
  }
  return statusMap[statut] || statut
}

const formatType = (type) => {
  const typeMap = {
    'preventive': 'Préventive',
    'corrective': 'Corrective'
  }
  return typeMap[type] || type
}

const fetchData = async () => {
  loading.value = true
  try {
    await Promise.all([
      maintenanceStore.fetchMaintenancesByPeriod(),
      equipementStore.fetchEquipements(),
      panneStore.fetchPannes(),
      userStore.fetchUsers()
    ])
    maintenances.value = maintenanceStore.maintenances
    equipements.value = equipementStore.equipements
    pannes.value = panneStore.pannes
    users.value = userStore.users
  } catch (err) {
    console.error(err)
  } finally {
    loading.value = false
  }
}

const openAddModal = () => {
  isEdit.value = false
  maintenanceForm.value = { 
    equipement_id: '', 
    type_maintenance: 'preventive', 
    date_prevue: new Date().toISOString().split('T')[0],
    panne_id: '',
    technicien_id: '',
    diagnostic: '',
    cout: '',
    observations: ''
  }
  showModal.value = true
}

const openEditModal = (maintenance) => {
  isEdit.value = true
  maintenanceForm.value = { 
    ...maintenance, 
    equipement_id: maintenance.equipement_id,
    panne_id: maintenance.panne_id || '',
    technicien_id: maintenance.technicien_id || ''
  }
  showModal.value = true
}

const openCompleteModal = (maintenance) => {
  selectedMaintenance.value = maintenance
  completeForm.value = {
    diagnostic: maintenance.diagnostic || '',
    cout: maintenance.cout || '',
    observations: maintenance.observations || '',
    date_fin: new Date().toISOString().split('T')[0]
  }
  showCompleteModal.value = true
}

const showDetail = (maintenance) => {
  selectedMaintenance.value = maintenance
  showDetailModal.value = true
}

const submitMaintenance = async () => {
  submitting.value = true
  try {
    if (isEdit.value) {
      await maintenanceStore.updateMaintenance(maintenanceForm.value.id, maintenanceForm.value)
    } else {
      await maintenanceStore.createMaintenance(maintenanceForm.value)
    }
    toast.add({ severity: 'success', summary: 'Succès', detail: 'Maintenance enregistrée', life: 3000 })
    showModal.value = false
    await fetchData()
  } catch (err) {
    toast.add({ severity: 'error', summary: 'Erreur', detail: 'Échec de l\'enregistrement', life: 3000 })
  } finally {
    submitting.value = false
  }
}

const startMaintenance = async (maintenance) => {
  try {
    await maintenanceStore.startMaintenance(maintenance.id, { technicien_id: maintenance.technicien_id })
    toast.add({ severity: 'success', summary: 'Succès', detail: 'Maintenance démarrée', life: 3000 })
    await fetchData()
  } catch (err) {
    toast.add({ severity: 'error', summary: 'Erreur', detail: 'Échec du démarrage', life: 3000 })
  }
}

const submitComplete = async () => {
  submitting.value = true
  try {
    await maintenanceStore.completeMaintenance(selectedMaintenance.value.id, completeForm.value)
    toast.add({ severity: 'success', summary: 'Succès', detail: 'Maintenance terminée', life: 3000 })
    showCompleteModal.value = false
    await fetchData()
  } catch (err) {
    toast.add({ severity: 'error', summary: 'Erreur', detail: 'Échec de la terminaison', life: 3000 })
  } finally {
    submitting.value = false
  }
}

const confirmDelete = (maintenance) => {
  confirm.require({
    message: 'Êtes-vous sûr de vouloir supprimer cette maintenance ?',
    header: 'Confirmation de suppression',
    icon: 'pi pi-exclamation-triangle',
    accept: async () => {
      try {
        await maintenanceStore.deleteMaintenance(maintenance.id)
        toast.add({ severity: 'success', summary: 'Succès', detail: 'Maintenance supprimée', life: 3000 })
        await fetchData()
      } catch (err) {
        toast.add({ severity: 'error', summary: 'Erreur', detail: 'Échec de la suppression', life: 3000 })
      }
    }
  })
}

const formatDate = (date) => {
  if (!date) return ''
  return new Date(date).toLocaleDateString('fr-FR')
}

onMounted(fetchData)
</script>

<style scoped>
.maintenances-container {
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
  font-size: 0.875rem;
  font-weight: 600;
  text-transform: uppercase;
}

.data-table td {
  padding: 14px 16px;
  border-bottom: 1px solid #334155;
}

.type-badge,
.status-badge {
  padding: 4px 10px;
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: 700;
}

.type-badge.preventive {
  background: rgba(16, 185, 129, 0.15);
  color: #10b981;
}

.type-badge.corrective {
  background: rgba(245, 158, 11, 0.15);
  color: #f59e0b;
}

.status-badge.planifiee {
  background: rgba(107, 114, 128, 0.15);
  color: #94a3b8;
}

.status-badge.en_cours {
  background: rgba(59, 130, 246, 0.15);
  color: #3b82f6;
}

.status-badge.terminee {
  background: rgba(16, 185, 129, 0.15);
  color: #10b981;
}

.actions {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
}

.detail-btn,
.edit-btn {
  background: #334155;
  color: white;
  border: none;
  padding: 6px 12px;
  border-radius: 6px;
  cursor: pointer;
}

.start-btn {
  background: #3b82f6;
  color: white;
  border: none;
  padding: 6px 12px;
  border-radius: 6px;
  cursor: pointer;
}

.complete-btn {
  background: #10b981;
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

.maintenance-form select,
.maintenance-form textarea,
.maintenance-form input {
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