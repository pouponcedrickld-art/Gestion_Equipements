<template>
  <AgenceLayout>
    <div class="professional-calendar p-6">
      <!-- Header -->
      <div class="mb-8 flex items-center justify-between flex-wrap gap-4">
        <div>
          <h1 class="text-3xl font-bold bg-gradient-to-r from-blue-500 to-indigo-600 bg-clip-text text-transparent">
            <i class="pi pi-calendar mr-3"></i>
            Calendrier de Maintenance
          </h1>
          <p class="text-gray-400 mt-2">Gérez vos maintenances avec une vue professionnelle</p>
        </div>

        <button
          class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg flex items-center gap-2 transition-all"
          @click="openAddModal"
        >
          <i class="pi pi-plus"></i>
          Nouvelle Maintenance
        </button>
      </div>

      <!-- Filters -->
      <div class="bg-slate-800 rounded-xl p-4 mb-6 border border-slate-700">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label class="text-sm font-medium text-gray-300 mb-2 block">Agence</label>
            <select
              v-model="filters.agenceId"
              @change="applyFilters"
              class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-2 text-gray-200"
            >
              <option value="">Toutes les agences</option>
              <option v-for="agence in agences" :key="agence.id" :value="agence.id">{{ agence.nom }}</option>
            </select>
          </div>

          <div>
            <label class="text-sm font-medium text-gray-300 mb-2 block">Technicien</label>
            <select
              v-model="filters.technicienId"
              @change="applyFilters"
              class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-2 text-gray-200"
            >
              <option value="">Tous les techniciens</option>
              <option v-for="user in techniciens" :key="user.id" :value="user.id">{{ user.name }}</option>
            </select>
          </div>

          <div>
            <label class="text-sm font-medium text-gray-300 mb-2 block">Statut</label>
            <select
              v-model="filters.statut"
              @change="applyFilters"
              class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-2 text-gray-200"
            >
              <option value="">Tous les statuts</option>
              <option value="planifiee">Planifiée</option>
              <option value="en_cours">En cours</option>
              <option value="terminee">Terminée</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Loading/Error states -->
      <div v-if="loading" class="flex items-center justify-center py-20">
        <div class="animate-spin rounded-full h-12 w-12 border-4 border-blue-500 border-t-transparent"></div>
      </div>

      <div v-else-if="error" class="bg-red-900/30 text-red-300 border border-red-800 rounded-xl p-6 flex items-center gap-3 mb-6">
        <i class="pi pi-exclamation-triangle text-red-400"></i>
        <div>
          <p class="font-bold">{{ error }}</p>
          <button
            @click="fetchData"
            class="text-red-300 underline mt-2"
          >
            Réessayer
          </button>
        </div>
      </div>

      <!-- FullCalendar Component -->
      <div v-else class="bg-slate-800 rounded-xl p-4 border border-slate-700">
        <FullCalendar
          ref="calendarRef"
          :options="calendarOptions"
        />
      </div>

      <!-- Create/Edit Maintenance Modal -->
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

      <!-- Maintenance Details Modal -->
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
          <Button v-if="selectedMaintenance && selectedMaintenance.statut === 'planifiee'" label="Démarrer" class="p-button-primary" @click="startMaintenance" />
          <Button v-if="selectedMaintenance && selectedMaintenance.statut === 'en_cours'" label="Terminer" class="p-button-success" @click="openCompleteModal" />
          <Button label="Modifier" class="p-button-warning" @click="openEditModal(selectedMaintenance)" />
        </div>
      </Dialog>

      <!-- Complete Maintenance Modal -->
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

    </div>
  </AgenceLayout>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import FullCalendar from '@fullcalendar/vue3'
import dayGridPlugin from '@fullcalendar/daygrid'
import timeGridPlugin from '@fullcalendar/timegrid'
import interactionPlugin from '@fullcalendar/interaction'
import frLocale from '@fullcalendar/core/locales/fr'
import AgenceLayout from '@/layouts/AgenceLayout.vue'
import Dialog from 'primevue/dialog'
import Button from 'primevue/button'
import { useToast } from 'primevue/usetoast'
import { useConfirm } from 'primevue/useconfirm'
import { useMaintenanceStore } from '@/stores/maintenanceStore.js'
import { useEquipementStore } from '@/stores/equipementStore.js'
import { usePanneStore } from '@/stores/panneStore.js'
import { useUserStore } from '@/stores/userStore.js'
import { useAgenceStore } from '@/stores/agenceStore.js'

const toast = useToast()
const confirm = useConfirm()
const maintenanceStore = useMaintenanceStore()
const equipementStore = useEquipementStore()
const panneStore = usePanneStore()
const userStore = useUserStore()
const agenceStore = useAgenceStore()

// State
const calendarRef = ref(null)
const loading = ref(false)
const submitting = ref(false)
const error = ref(null)
const filters = ref({ agenceId: '', technicienId: '', statut: '' })

const maintenances = ref([])
const equipements = ref([])
const pannes = ref([])
const users = ref([])
const agences = ref([])

// Modal states
const showModal = ref(false)
const showDetailModal = ref(false)
const showCompleteModal = ref(false)
const isEdit = ref(false)
const selectedMaintenance = ref(null)
const maintenanceForm = ref({})
const completeForm = ref({})

// Computed for filters
const filteredMaintenances = computed(() => {
  return maintenances.value.filter(m => {
    const matchesAgence = !filters.value.agenceId || m.equipement?.agence_actuelle_id === Number(filters.value.agenceId)
    const matchesTechnicien = !filters.value.technicienId || m.technicien_id === Number(filters.value.technicienId)
    const matchesStatut = !filters.value.statut || m.statut === filters.value.statut
    return matchesAgence && matchesTechnicien && matchesStatut
  })
})

const techniciens = computed(() => users.value.filter(u => u.roles?.some(r => r.name === 'technicien_maintenance')))

// Calendar Options
const calendarOptions = computed(() => ({
  plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
  initialView: 'dayGridMonth',
  locale: frLocale,
  headerToolbar: {
    left: 'prev,next today',
    center: 'title',
    right: 'dayGridMonth,timeGridWeek,timeGridDay'
  },
  buttonText: {
    today: 'Aujourd\'hui',
    month: 'Mois',
    week: 'Semaine',
    day: 'Jour'
  },
  editable: true,
  selectable: true,
  selectMirror: true,
  weekends: true,
  allDaySlot: true,
  height: 'auto',
  aspectRatio: 2,
  datesSet: handleDatesSet,
  eventClick: handleEventClick,
  dateClick: handleDateClick,
  events: fetchEvents
}))

// Methods

function formatDate(date) {
  if (!date) return ''
  return new Date(date).toLocaleDateString('fr-FR')
}

function formatStatus(statut) {
  const statusMap = {
    'planifiee': 'Planifiée',
    'en_cours': 'En cours',
    'terminee': 'Terminée'
  }
  return statusMap[statut] || statut
}

function formatType(type) {
  const typeMap = {
    'preventive': 'Préventive',
    'corrective': 'Corrective'
  }
  return typeMap[type] || type
}

function getStatusColor(statut) {
  switch (statut) {
    case 'planifiee': return '#3b82f6'
    case 'en_cours': return '#f59e0b'
    case 'terminee': return '#10b981'
    default: return '#6366f1'
  }
}

const fetchData = async () => {
  loading.value = true
  error.value = null
  try {
    await Promise.all([
      maintenanceStore.fetchMaintenancesByPeriod(),
      equipementStore.fetchEquipements(),
      panneStore.fetchPannes(),
      userStore.fetchUsers(),
      agenceStore.fetchAgences()
    ])
    maintenances.value = maintenanceStore.maintenances
    equipements.value = equipementStore.equipements
    pannes.value = panneStore.pannes
    users.value = userStore.users
    agences.value = agenceStore.agences
  } catch (err) {
    console.error(err)
    error.value = 'Erreur lors du chargement des données'
  } finally {
    loading.value = false
  }
}

function applyFilters() {
  // Will automatically re-render filteredMaintenances
}

function handleDatesSet(info) {
  // We could fetch by date range here if needed
}

function fetchEvents(fetchInfo, successCallback, failureCallback) {
  const events = []
  filteredMaintenances.value.forEach(m => {
    events.push({
      id: m.id.toString(),
      title: `${formatType(m.type_maintenance)} - ${m.equipement?.nom || 'Équipement'}`,
      start: m.date_prevue,
      end: m.date_fin || m.date_prevue,
      allDay: true,
      backgroundColor: getStatusColor(m.statut),
      borderColor: getStatusColor(m.statut),
      extendedProps: { maintenance: m }
    })
  })
  successCallback(events)
}

function handleEventClick(info) {
  selectedMaintenance.value = info.event.extendedProps.maintenance
  showDetailModal.value = true
}

function handleDateClick(info) {
  openAddModal(info.dateStr)
}

function openAddModal(dateStr = null) {
  isEdit.value = false
  maintenanceForm.value = {
    equipement_id: '',
    type_maintenance: 'preventive',
    date_prevue: dateStr || new Date().toISOString().split('T')[0],
    panne_id: '',
    technicien_id: '',
    diagnostic: '',
    cout: '',
    observations: ''
  }
  showModal.value = true
}

function openEditModal(maintenance) {
  isEdit.value = true
  showDetailModal.value = false
  maintenanceForm.value = {
    ...maintenance,
    equipement_id: maintenance.equipement_id,
    panne_id: maintenance.panne_id || '',
    technicien_id: maintenance.technicien_id || ''
  }
  showModal.value = true
}

function openCompleteModal() {
  completeForm.value = {
    diagnostic: selectedMaintenance.value.diagnostic || '',
    cout: selectedMaintenance.value.cout || '',
    observations: selectedMaintenance.value.observations || '',
    date_fin: new Date().toISOString().split('T')[0]
  }
  showDetailModal.value = false
  showCompleteModal.value = true
}

async function submitMaintenance() {
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

async function startMaintenance() {
  try {
    await maintenanceStore.startMaintenance(selectedMaintenance.value.id, {
      technicien_id: selectedMaintenance.value.technicien_id
    })
    toast.add({ severity: 'success', summary: 'Succès', detail: 'Maintenance démarrée', life: 3000 })
    await fetchData()
    showDetailModal.value = false
  } catch (err) {
    toast.add({ severity: 'error', summary: 'Erreur', detail: 'Échec du démarrage', life: 3000 })
  }
}

async function submitComplete() {
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

onMounted(fetchData)

watch(filteredMaintenances, () => {
  if (calendarRef.value) {
    calendarRef.value.getApi().refetchEvents()
  }
})
</script>

<style scoped>
.professional-calendar {
  color: white;
  min-height: 100vh;
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

:deep(.dark-modal) .p-dialog-content,
:deep(.dark-modal) .p-dialog-header {
  background: #1e293b;
  color: #f8fafc;
  border-color: #334155;
}

:deep(.fc) {
  color: white;
}

:deep(.fc .fc-button) {
  background: #3b82f6;
  border: none;
}

:deep(.fc .fc-button:hover) {
  background: #2563eb;
}

:deep(.fc .fc-toolbar-title) {
  color: white;
}

:deep(.fc .fc-daygrid-day-number) {
  color: white;
}

:deep(.fc .fc-col-header-cell) {
  background: #0f172a;
  color: #94a3b8;
}

:deep(.fc .fc-daygrid-day) {
  background: #1e293b;
  border-color: #334155;
}

:deep(.fc .fc-daygrid-day:hover) {
  background: #334155;
}

:deep(.fc .fc-day-today) {
  background: #2563eb !important;
}
</style>
