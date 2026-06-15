<template>
  <AgenceLayout>
    <div class="dashboard-bulletin-board animate-fade-in">
      <!-- Header Section -->
      <div class="dashboard-header mb-8">
        <div class="welcome-section">
          <h1 class="text-3xl font-extrabold text-dark tracking-tight flex items-center gap-3">
            <span class="p-2 bg-primary rounded-xl shadow-sm"><i class="pi pi-file-pdf text-dark"></i></span>
            Centre de Rapports & Analytique
          </h1>
          <p class="text-muted mt-2 font-medium">
            Générez des rapports détaillés au format <span class="text-primary-hover font-bold">PDF</span> ou <span class="text-success font-bold">Excel</span>
          </p>
        </div>
      </div>

      <!-- Main Layout -->
      <div class="grid grid-cols-12 gap-8">
        
        <!-- Left Column: Report Selection -->
        <div class="col-span-12 lg:col-span-8 space-y-8">
          
          <!-- Category Tabs -->
          <div class="bento-card p-2 flex gap-2 bg-app inline-flex">
            <button 
              @click="activeTab = 'pdf'" 
              class="px-8 py-3 rounded-xl font-extrabold transition-all"
              :class="activeTab === 'pdf' ? 'bg-primary text-dark shadow-sm' : 'text-muted hover:bg-white'"
            >
              <i class="pi pi-file-pdf mr-2"></i> Documents PDF
            </button>
            <button 
              @click="activeTab = 'excel'" 
              class="px-8 py-3 rounded-xl font-extrabold transition-all"
              :class="activeTab === 'excel' ? 'bg-success text-white shadow-sm' : 'text-muted hover:bg-white'"
            >
              <i class="pi pi-file-excel mr-2"></i> Données Excel
            </button>
          </div>

          <!-- Reports Grid -->
          <div class="reports-bento-grid grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
            <div 
              v-for="report in currentReports" 
              :key="report.id" 
              class="report-item-card group"
              :class="{ 'active': selectedReportType === report.id }"
              @click="selectReport(report.id)"
            >
              <div class="report-icon-circle" :class="activeTab === 'pdf' ? 'pdf' : 'excel'">
                <i :class="report.icon"></i>
              </div>
              <div class="report-info">
                <h3 class="font-extrabold text-dark group-hover:text-primary-hover transition-colors">{{ report.title }}</h3>
                <p class="text-xs text-muted mt-1 leading-relaxed">{{ report.description }}</p>
              </div>
              <div class="selection-indicator">
                <i class="pi pi-check-circle"></i>
              </div>
            </div>
          </div>
        </div>

        <!-- Right Column: Filters & Generation -->
        <div class="col-span-12 lg:col-span-4">
          <div class="sticky top-6">
            <div class="bento-card p-6 border-2" :class="selectedReportType ? 'border-primary shadow-lg' : 'border-dashed opacity-75'">
              <div v-if="selectedReportType" class="animate-in">
                <div class="flex items-center gap-3 mb-6 border-b pb-4">
                  <div class="w-10 h-10 rounded-lg bg-primary-light flex items-center justify-center text-primary-hover">
                    <i :class="currentSelectedReport?.icon"></i>
                  </div>
                  <div>
                    <h3 class="font-extrabold text-dark">Configuration</h3>
                    <p class="text-xs text-muted">Personnalisez votre rapport</p>
                  </div>
                </div>

                <div class="space-y-5">
                  <!-- Dynamic Filters based on report type -->
                  <div v-if="needsFilter('agence_id')" class="form-group-custom">
                    <label>Agence Cible</label>
                    <select v-model="filters.agence_id" class="input-bulletin">
                      <option value="">-- Toutes les agences --</option>
                      <option v-for="a in agences" :key="a.id" :value="a.id">{{ a.nom }}</option>
                    </select>
                  </div>

                  <div v-if="needsFilter('categorie_id')" class="form-group-custom">
                    <label>Catégorie</label>
                    <select v-model="filters.categorie_id" class="input-bulletin">
                      <option value="">-- Toutes les catégories --</option>
                      <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.nom }}</option>
                    </select>
                  </div>

                  <div v-if="needsFilter('date_range')" class="grid grid-cols-2 gap-4">
                    <div class="form-group-custom">
                      <label>Du</label>
                      <input type="date" v-model="filters.date_debut" class="input-bulletin" />
                    </div>
                    <div class="form-group-custom">
                      <label>Au</label>
                      <input type="date" v-model="filters.date_fin" class="input-bulletin" />
                    </div>
                  </div>

                  <div v-if="needsFilter('statut')" class="form-group-custom">
                    <label>Filtrer par Statut</label>
                    <select v-model="filters.statut" class="input-bulletin">
                      <option value="">-- Tous --</option>
                      <option value="actif">Actif / En service</option>
                      <option value="en_panne">En Panne</option>
                      <option value="en_maintenance">En Maintenance</option>
                    </select>
                  </div>
                </div>

                <div class="mt-8 pt-6 border-t border-dashed flex flex-col gap-3">
                  <button 
                    v-if="activeTab === 'pdf'" 
                    @click="previewReport" 
                    class="btn btn-primary w-full justify-center py-4"
                    :disabled="loading"
                  >
                    <i class="pi pi-eye mr-2"></i> Aperçu Interactif
                  </button>
                  <button 
                    @click="activeTab === 'pdf' ? downloadReport() : downloadExcel()" 
                    class="btn w-full justify-center py-4"
                    :class="activeTab === 'pdf' ? 'btn-outline' : 'btn-success'"
                    :disabled="loading"
                  >
                    <i :class="activeTab === 'pdf' ? 'pi pi-download' : 'pi pi-file-excel'" class="mr-2"></i>
                    {{ activeTab === 'pdf' ? 'Télécharger PDF' : 'Exporter en Excel' }}
                  </button>
                </div>
              </div>

              <div v-else class="py-20 text-center space-y-4">
                <div class="w-16 h-16 bg-app rounded-full flex items-center justify-center mx-auto text-muted opacity-50">
                  <i class="pi pi-mouse-pointer text-3xl"></i>
                </div>
                <h3 class="font-bold text-muted">Sélectionnez un rapport</h3>
                <p class="text-xs text-muted px-6">Choisissez un type de rapport à gauche pour commencer la configuration.</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Preview Modal -->
      <div v-if="showPreview" class="modal-overlay" @click.self="showPreview = false">
        <div class="modal-preview-card animate-in">
          <div class="modal-header">
            <div class="flex items-center gap-3">
              <i class="pi pi-file-pdf text-2xl text-primary"></i>
              <h3 class="font-extrabold text-white">Aperçu du Rapport</h3>
            </div>
            <button @click="showPreview = false" class="text-white hover:text-primary"><i class="pi pi-times"></i></button>
          </div>
          <div class="modal-body bg-white p-0 overflow-hidden">
            <iframe :src="previewUrl" width="100%" height="750px" frameborder="0"></iframe>
          </div>
        </div>
      </div>

    </div>
  </AgenceLayout>
</template>

<script setup>
import { ref, computed, onMounted, reactive } from 'vue'
import AgenceLayout from '@/layouts/AgenceLayout.vue'
import { useToast } from 'primevue/usetoast'
import api from '@/api/axiosConfig.js'

const toast = useToast()
const activeTab = ref('pdf')
const selectedReportType = ref(null)
const loading = ref(false)
const showPreview = ref(false)
const previewUrl = ref('')
const agences = ref([])
const categories = ref([])

const filters = reactive({
  agence_id: '',
  categorie_id: '',
  date_debut: '',
  date_fin: '',
  statut: '',
  user_id: '',
  type_maintenance: ''
})

const pdfReports = [
  { id: 'inventaire-par-agence', title: 'Inventaire Global', icon: 'pi pi-box', description: 'État complet du stock par agence et catégorie.' },
  { id: 'equipements-affectes', title: 'Affectations Actives', icon: 'pi pi-users', description: 'Liste des matériels actuellement en possession du personnel.' },
  { id: 'equipements-en-panne', title: 'Registre des Pannes', icon: 'pi pi-exclamation-triangle', description: 'Historique et état des pannes signalées.' },
  { id: 'maintenances', title: 'Calendrier Maintenance', icon: 'pi pi-wrench', description: 'Suivi des interventions préventives et curatives.' },
  { id: 'pertes-et-casses', title: 'Pertes & Casses', icon: 'pi pi-trash', description: 'Rapport sur le matériel hors-service ou égaré.' },
  { id: 'audit-complet', title: 'Audit de Performance', icon: 'pi pi-chart-bar', description: 'Statistiques globales et KPIs de gestion.' }
]

const excelReports = [
  { id: 'inventaire', title: 'Data Inventaire', icon: 'pi pi-table', description: 'Export brut de la base de données équipements.' },
  { id: 'pannes', title: 'Data Pannes', icon: 'pi pi-list', description: 'Extraction Excel de toutes les pannes enregistrées.' },
  { id: 'maintenances', title: 'Data Maintenances', icon: 'pi pi-cog', description: 'Export des données techniques de maintenance.' },
  { id: 'affectations', title: 'Data Affectations', icon: 'pi pi-user-plus', description: 'Journal complet des affectations de matériel.' },
  { id: 'mouvements', title: 'Data Flux', icon: 'pi pi-sync', description: 'Historique des transferts entre agences.' }
]

const currentReports = computed(() => activeTab.value === 'pdf' ? pdfReports : excelReports)
const currentSelectedReport = computed(() => currentReports.value.find(r => r.id === selectedReportType.value))

const selectReport = (id) => {
  selectedReportType.value = id
  // Reset filters
  Object.keys(filters).forEach(k => filters[k] = '')
}

const needsFilter = (filter) => {
  if (!selectedReportType.value) return false
  const type = selectedReportType.value
  
  if (filter === 'agence_id') return true
  if (filter === 'categorie_id') return ['inventaire-par-agence', 'inventaire'].includes(type)
  if (filter === 'date_range') return !['inventaire-par-agence', 'inventaire', 'equipements-affectes', 'audit-complet'].includes(type)
  if (filter === 'statut') return ['inventaire', 'pannes', 'maintenances'].includes(type)
  
  return false
}

const fetchDependencies = async () => {
  try {
    const [resA, resC] = await Promise.all([api.get('/agences'), api.get('/categories')])
    agences.value = resA.data; categories.value = resC.data
  } catch (e) { console.error(e) }
}

const buildQueryParams = () => {
  const p = new URLSearchParams()
  Object.entries(filters).forEach(([k, v]) => { if (v) p.append(k, v) })
  return p.toString()
}

const previewReport = () => {
  const token = localStorage.getItem('token')
  previewUrl.value = `${import.meta.env.VITE_API_URL}/rapports/${selectedReportType.value}/preview?${buildQueryParams()}&token=${token}`
  showPreview.value = true
}

const downloadReport = () => {
  window.location.href = `${import.meta.env.VITE_API_URL}/rapports/${selectedReportType.value}/download?${buildQueryParams()}`
}

const downloadExcel = () => {
  window.location.href = `${import.meta.env.VITE_API_URL}/rapports/export/${selectedReportType.value}?${buildQueryParams()}`
}

onMounted(fetchDependencies)
</script>

<style scoped>
.dashboard-bulletin-board {
  padding: 1rem 1.5rem;
  background-color: var(--bg-app);
  min-height: 100vh;
}

.bento-card {
  background: white;
  border-radius: var(--radius-xl);
  border: 1px solid var(--border-color);
  box-shadow: var(--shadow-sm);
}

.report-item-card {
  background: white;
  padding: 1.5rem;
  border-radius: var(--radius-xl);
  border: 1px solid var(--border-color);
  cursor: pointer;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
  position: relative;
}

.report-item-card:hover {
  transform: translateY(-5px);
  border-color: var(--primary);
  box-shadow: var(--shadow-md);
}

.report-item-card.active {
  background: #fefce8;
  border-color: var(--primary);
  border-width: 2px;
}

.report-icon-circle {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.25rem;
}

.report-icon-circle.pdf { background: #fee2e2; color: #ef4444; }
.report-icon-circle.excel { background: #dcfce7; color: #10b981; }

.selection-indicator {
  position: absolute;
  top: 1rem;
  right: 1rem;
  color: var(--primary-hover);
  font-size: 1.25rem;
  opacity: 0;
  transition: opacity 0.2s;
}

.report-item-card.active .selection-indicator { opacity: 1; }

.form-group-custom label {
  font-size: 0.8rem;
  font-weight: 800;
  color: var(--text-dark);
  text-transform: uppercase;
  margin-bottom: 0.5rem;
  display: block;
}

.input-bulletin {
  background: var(--bg-app);
  border: 1px solid var(--border-color);
  border-radius: 12px;
  padding: 12px;
  font-weight: 600;
  color: var(--text-dark);
}

.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.8);
  backdrop-filter: blur(8px);
  z-index: 2000;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 2rem;
}

.modal-preview-card {
  width: 100%;
  max-width: 1100px;
  background: #111827;
  border-radius: var(--radius-xl);
  overflow: hidden;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
}

.modal-header {
  padding: 1.25rem 2rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.loader-overlay { height: 200px; display: flex; align-items: center; justify-content: center; }

@keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
.animate-fade-in { animation: fadeIn 0.6s cubic-bezier(0.16, 1, 0.3, 1); }
</style>
