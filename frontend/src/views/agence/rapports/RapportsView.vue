<template>
  <AgenceLayout>
    <div class="rapports-container">
      <div class="header-bar">
        <h2>Rapports</h2>
        <p>Générez et téléchargez des rapports</p>
      </div>

      <!-- Tabs -->
      <div class="tabs-container">
        <div 
          :class="['tab', activeTab === 'pdf' ? 'active' : '']" 
          @click="activeTab = 'pdf'"
        >
          PDF
        </div>
        <div 
          :class="['tab', activeTab === 'excel' ? 'active' : '']" 
          @click="activeTab = 'excel'"
        >
          Excel
        </div>
      </div>

      <!-- PDF Content -->
      <div v-show="activeTab === 'pdf'">
        <div class="rapports-grid">
          <!-- Inventaire par Agence -->
          <div class="rapport-card" :class="{ active: selectedReport.type === 'inventaire-par-agence' }" @click="selectReport('inventaire-par-agence')">
            <div class="rapport-icon"><i class="pi pi-box"></i></div>
            <h3>Inventaire par Agence</h3>
          </div>

          <!-- Équipements Affectés -->
          <div class="rapport-card" :class="{ active: selectedReport.type === 'equipements-affectes' }" @click="selectReport('equipements-affectes')">
            <div class="rapport-icon"><i class="pi pi-users"></i></div>
            <h3>Équipements Affectés</h3>
          </div>

          <!-- Équipements en Panne -->
          <div class="rapport-card" :class="{ active: selectedReport.type === 'equipements-en-panne' }" @click="selectReport('equipements-en-panne')">
            <div class="rapport-icon"><i class="pi pi-exclamation-triangle"></i></div>
            <h3>Équipements en Panne</h3>
          </div>

          <!-- Maintenances -->
          <div class="rapport-card" :class="{ active: selectedReport.type === 'maintenances' }" @click="selectReport('maintenances')">
            <div class="rapport-icon"><i class="pi pi-wrench"></i></div>
            <h3>Maintenances</h3>
          </div>

          <!-- Pertes et Casses -->
          <div class="rapport-card" :class="{ active: selectedReport.type === 'pertes-et-casses' }" @click="selectReport('pertes-et-casses')">
            <div class="rapport-icon"><i class="pi pi-trash"></i></div>
            <h3>Pertes et Casses</h3>
          </div>

          <!-- Audit Complet -->
          <div class="rapport-card" :class="{ active: selectedReport.type === 'audit-complet' }" @click="selectReport('audit-complet')">
            <div class="rapport-icon"><i class="pi pi-chart-bar"></i></div>
            <h3>Audit Complet</h3>
          </div>
        </div>

        <!-- Filtres PDF -->
        <div v-if="selectedReport.type" class="filters-card">
          <h3>Filtres</h3>

          <!-- Inventaire par Agence -->
          <div v-if="selectedReport.type === 'inventaire-par-agence'" class="filters-row">
            <div class="filter-group">
              <label>Agence</label>
              <select v-model="selectedReport.filters.agence_id">
                <option value="">-- Choisir --</option>
                <option v-for="a in agences" :key="a.id" :value="a.id">{{ a.nom }}</option>
              </select>
            </div>
            <div class="filter-group">
              <label>Catégorie</label>
              <select v-model="selectedReport.filters.categorie_id">
                <option value="">-- Toutes --</option>
                <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.nom }}</option>
              </select>
            </div>
          </div>

          <!-- Équipements Affectés -->
          <div v-else-if="selectedReport.type === 'equipements-affectes'" class="filters-row">
            <div class="filter-group">
              <label>Agence</label>
              <select v-model="selectedReport.filters.agence_id">
                <option value="">-- Toutes --</option>
                <option v-for="a in agences" :key="a.id" :value="a.id">{{ a.nom }}</option>
              </select>
            </div>
            <div class="filter-group">
              <label>Utilisateur</label>
              <select v-model="selectedReport.filters.user_id">
                <option value="">-- Tous --</option>
                <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }}</option>
              </select>
            </div>
          </div>

          <!-- Équipements en Panne -->
          <div v-else-if="selectedReport.type === 'equipements-en-panne'" class="filters-row">
            <div class="filter-group">
              <label>Agence</label>
              <select v-model="selectedReport.filters.agence_id">
                <option value="">-- Toutes --</option>
                <option v-for="a in agences" :key="a.id" :value="a.id">{{ a.nom }}</option>
              </select>
            </div>
            <div class="filter-group">
              <label>Date début</label>
              <input type="date" v-model="selectedReport.filters.date_debut" />
            </div>
            <div class="filter-group">
              <label>Date fin</label>
              <input type="date" v-model="selectedReport.filters.date_fin" />
            </div>
          </div>

          <!-- Maintenances -->
          <div v-else-if="selectedReport.type === 'maintenances'" class="filters-row">
            <div class="filter-group">
              <label>Agence</label>
              <select v-model="selectedReport.filters.agence_id">
                <option value="">-- Toutes --</option>
                <option v-for="a in agences" :key="a.id" :value="a.id">{{ a.nom }}</option>
              </select>
            </div>
            <div class="filter-group">
              <label>Type</label>
              <select v-model="selectedReport.filters.type_maintenance">
                <option value="">-- Tous --</option>
                <option value="preventive">Préventive</option>
                <option value="corrective">Corrective</option>
              </select>
            </div>
            <div class="filter-group">
              <label>Statut</label>
              <select v-model="selectedReport.filters.statut">
                <option value="">-- Tous --</option>
                <option value="planifiee">Planifiée</option>
                <option value="en_cours">En cours</option>
                <option value="terminee">Terminée</option>
              </select>
            </div>
            <div class="filter-group">
              <label>Date début</label>
              <input type="date" v-model="selectedReport.filters.date_debut" />
            </div>
            <div class="filter-group">
              <label>Date fin</label>
              <input type="date" v-model="selectedReport.filters.date_fin" />
            </div>
          </div>

          <!-- Pertes et Casses -->
          <div v-else-if="selectedReport.type === 'pertes-et-casses'" class="filters-row">
            <div class="filter-group">
              <label>Agence</label>
              <select v-model="selectedReport.filters.agence_id">
                <option value="">-- Toutes --</option>
                <option v-for="a in agences" :key="a.id" :value="a.id">{{ a.nom }}</option>
              </select>
            </div>
            <div class="filter-group">
              <label>Type</label>
              <select v-model="selectedReport.filters.type">
                <option value="">-- Tous --</option>
                <option value="perte">Perte</option>
                <option value="casse">Casse</option>
              </select>
            </div>
            <div class="filter-group">
              <label>Date début</label>
              <input type="date" v-model="selectedReport.filters.date_debut" />
            </div>
            <div class="filter-group">
              <label>Date fin</label>
              <input type="date" v-model="selectedReport.filters.date_fin" />
            </div>
          </div>

          <!-- Audit Complet -->
          <div v-else-if="selectedReport.type === 'audit-complet'" class="filters-row">
            <div class="filter-group">
              <label>Agence</label>
              <select v-model="selectedReport.filters.agence_id">
                <option value="">-- Toutes --</option>
                <option v-for="a in agences" :key="a.id" :value="a.id">{{ a.nom }}</option>
              </select>
            </div>
          </div>

          <!-- Actions -->
          <div class="actions-row">
            <button class="btn-primary" @click="previewReport" :disabled="loading">
              <i class="pi pi-eye"></i> Aperçu
            </button>
            <button class="btn-success" @click="downloadReport" :disabled="loading">
              <i class="pi pi-download"></i> Télécharger PDF
            </button>
          </div>
        </div>

        <!-- Preview Modal -->
        <div v-if="showPreview" class="preview-modal" @click.self="showPreview = false">
          <div class="preview-content">
            <div class="preview-header">
            <h3>Aperçu du rapport</h3>
            <button class="close-btn" @click="showPreview = false"><i class="pi pi-times"></i></button>
            </div>
            <iframe :src="previewUrl" width="100%" height="600px"></iframe>
          </div>
        </div>
      </div>

      <!-- Excel Content -->
      <div v-show="activeTab === 'excel'">
        <div class="rapports-grid">
          <!-- Inventaire -->
          <div class="rapport-card" :class="{ active: selectedExcel.type === 'inventaire' }" @click="selectedExcel.type = 'inventaire'">
            <div class="rapport-icon"><i class="pi pi-box"></i></div>
            <h3>Inventaire</h3>
          </div>

          <!-- Pannes -->
          <div class="rapport-card" :class="{ active: selectedExcel.type === 'pannes' }" @click="selectedExcel.type = 'pannes'">
            <div class="rapport-icon"><i class="pi pi-exclamation-triangle"></i></div>
            <h3>Pannes</h3>
          </div>

          <!-- Maintenances -->
          <div class="rapport-card" :class="{ active: selectedExcel.type === 'maintenances' }" @click="selectedExcel.type = 'maintenances'">
            <div class="rapport-icon"><i class="pi pi-wrench"></i></div>
            <h3>Maintenances</h3>
          </div>

          <!-- Affectations -->
          <div class="rapport-card" :class="{ active: selectedExcel.type === 'affectations' }" @click="selectedExcel.type = 'affectations'">
            <div class="rapport-icon"><i class="pi pi-users"></i></div>
            <h3>Affectations</h3>
          </div>

          <!-- Mouvements -->
          <div class="rapport-card" :class="{ active: selectedExcel.type === 'mouvements' }" @click="selectedExcel.type = 'mouvements'">
            <div class="rapport-icon"><i class="pi pi-arrows-h"></i></div>
            <h3>Mouvements</h3>
          </div>
        </div>

        <!-- Filtres Excel -->
        <div v-if="selectedExcel.type" class="filters-card">
          <h3>Filtres</h3>

          <div class="filters-row">
            <div class="filter-group">
              <label>Agence</label>
              <select v-model="selectedExcel.filters.agence_id">
                <option value="">-- Toutes --</option>
                <option v-for="a in agences" :key="a.id" :value="a.id">{{ a.nom }}</option>
              </select>
            </div>

            <template v-if="selectedExcel.type === 'inventaire'">
              <div class="filter-group">
                <label>Catégorie</label>
                <select v-model="selectedExcel.filters.categorie_id">
                  <option value="">-- Toutes --</option>
                  <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.nom }}</option>
                </select>
              </div>
              <div class="filter-group">
                <label>Statut</label>
                <select v-model="selectedExcel.filters.statut">
                  <option value="">-- Tous --</option>
                  <option value="en_service">En Service</option>
                  <option value="en_panne">En Panne</option>
                  <option value="en_maintenance">En Maintenance</option>
                  <option value="reforme">Réformé</option>
                </select>
              </div>
            </template>

            <template v-if="['pannes', 'maintenances'].includes(selectedExcel.type)">
              <div class="filter-group">
                <label>Date début</label>
                <input type="date" v-model="selectedExcel.filters.date_debut" />
              </div>
              <div class="filter-group">
                <label>Date fin</label>
                <input type="date" v-model="selectedExcel.filters.date_fin" />
              </div>
              <template v-if="selectedExcel.type === 'pannes'">
                <div class="filter-group">
                  <label>Statut</label>
                  <select v-model="selectedExcel.filters.statut">
                    <option value="">-- Tous --</option>
                    <option value="declaree">Déclarée</option>
                    <option value="en_cours">En cours</option>
                    <option value="en_maintenance">En Maintenance</option>
                    <option value="resolue">Résolue</option>
                    <option value="cloturee">Clôturée</option>
                  </select>
                </div>
              </template>
              <template v-if="selectedExcel.type === 'maintenances'">
                <div class="filter-group">
                  <label>Type</label>
                  <select v-model="selectedExcel.filters.type_maintenance">
                    <option value="">-- Tous --</option>
                    <option value="preventive">Préventive</option>
                    <option value="corrective">Corrective</option>
                  </select>
                </div>
                <div class="filter-group">
                  <label>Statut</label>
                  <select v-model="selectedExcel.filters.statut">
                    <option value="">-- Tous --</option>
                    <option value="planifiee">Planifiée</option>
                    <option value="en_cours">En cours</option>
                    <option value="terminee">Terminée</option>
                  </select>
                </div>
              </template>
            </template>

            <template v-if="['affectations', 'mouvements'].includes(selectedExcel.type)">
              <div class="filter-group">
                <label>Date début</label>
                <input type="date" v-model="selectedExcel.filters.date_debut" />
              </div>
              <div class="filter-group">
                <label>Date fin</label>
                <input type="date" v-model="selectedExcel.filters.date_fin" />
              </div>
            </template>
          </div>

          <!-- Actions Excel -->
          <div class="actions-row">
            <button class="btn-success" @click="downloadExcel" :disabled="loading">
              <i class="pi pi-file-excel"></i> Télécharger Excel
            </button>
          </div>
        </div>
      </div>
    </div>
  </AgenceLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import AgenceLayout from '@/layouts/AgenceLayout.vue'
import { useToast } from 'primevue/usetoast'
import api from '@/api/axiosConfig.js'

const toast = useToast()
const activeTab = ref('pdf')

const selectedReport = ref({
  type: null,
  filters: {}
})
const selectedExcel = ref({
  type: null,
  filters: {}
})

const agences = ref([])
const categories = ref([])
const users = ref([])
const loading = ref(false)
const showPreview = ref(false)
const previewUrl = ref('')

const selectReport = (type) => {
  selectedReport.value = {
    type,
    filters: {}
  }
}

const fetchAgences = async () => {
  try {
    const response = await api.get('/agences')
    agences.value = response.data
  } catch (error) {
    console.error(error)
  }
}

const fetchCategories = async () => {
  try {
    const response = await api.get('/categories')
    categories.value = response.data
  } catch (error) {
    console.error(error)
  }
}

const fetchUsers = async () => {
  try {
    const response = await api.get('/users')
    users.value = response.data
  } catch (error) {
    console.error(error)
  }
}

const buildQueryParams = () => {
  const params = new URLSearchParams()
  for (const [key, value] of Object.entries(selectedReport.value.filters)) {
    if (value) {
      params.append(key, value)
    }
  }
  return params.toString()
}

const buildExcelQueryParams = () => {
  const params = new URLSearchParams()
  for (const [key, value] of Object.entries(selectedExcel.value.filters)) {
    if (value) {
      params.append(key, value)
    }
  }
  return params.toString()
}

const previewReport = async () => {
  loading.value = true
  try {
    const token = localStorage.getItem('token')
    previewUrl.value = `${import.meta.env.VITE_API_URL}/rapports/${selectedReport.value.type}/preview?${buildQueryParams()}&token=${token}`
    showPreview.value = true
  } catch (error) {
    toast.add({ severity: 'error', summary: 'Erreur', detail: 'Impossible de générer l\'aperçu', life: 3000 })
  } finally {
    loading.value = false
  }
}

const downloadReport = async () => {
  loading.value = true
  try {
    const token = localStorage.getItem('token')
    const url = `${import.meta.env.VITE_API_URL}/rapports/${selectedReport.value.type}/download?${buildQueryParams()}`
    window.location.href = url
  } catch (error) {
    toast.add({ severity: 'error', summary: 'Erreur', detail: 'Impossible de télécharger le rapport', life: 3000 })
  } finally {
    loading.value = false
  }
}

const downloadExcel = async () => {
  loading.value = true
  try {
    const token = localStorage.getItem('token')
    const url = `${import.meta.env.VITE_API_URL}/rapports/export/${selectedExcel.value.type}?${buildExcelQueryParams()}`
    window.location.href = url
  } catch (error) {
    toast.add({ severity: 'error', summary: 'Erreur', detail: 'Impossible de télécharger le fichier Excel', life: 3000 })
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchAgences()
  fetchCategories()
  fetchUsers()
})
</script>

<style scoped>
.rapports-container {
  padding: 24px;
  color: #f8fafc;
}

.header-bar {
  margin-bottom: 24px;
}

.header-bar h2 {
  font-size: 1.8rem;
  margin: 0;
}

.header-bar p {
  color: #94a3b8;
  margin: 8px 0 0;
}

.tabs-container {
  display: flex;
  gap: 8px;
  margin-bottom: 24px;
  background: #1e293b;
  padding: 8px;
  border-radius: 8px;
}

.tab {
  padding: 10px 24px;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.2s;
}

.tab:hover {
  background: #334155;
}

.tab.active {
  background: #3b82f6;
}

.rapports-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 20px;
  margin-bottom: 24px;
}

.rapport-card {
  background: #1e293b;
  border: 2px solid #334155;
  border-radius: 12px;
  padding: 30px;
  text-align: center;
  cursor: pointer;
  transition: all 0.3s ease;
}

.rapport-card:hover {
  border-color: #3b82f6;
  transform: translateY(-5px);
}

.rapport-card.active {
  border-color: #3b82f6;
  background: #2563eb20;
}

.rapport-icon {
  width: 80px;
  height: 80px;
  background: #3b82f620;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 16px;
  font-size: 32px;
  color: #3b82f6;
}

.rapport-card h3 {
  margin: 0;
  color: #f8fafc;
}

.filters-card {
  background: #1e293b;
  border: 1px solid #334155;
  border-radius: 12px;
  padding: 24px;
}

.filters-card h3 {
  margin: 0 0 20px;
  color: #f8fafc;
  font-size: 1.2rem;
}

.filters-row {
  display: flex;
  flex-wrap: wrap;
  gap: 16px;
  margin-bottom: 20px;
}

.filter-group {
  flex: 1;
  min-width: 180px;
}

.filter-group label {
  display: block;
  margin-bottom: 8px;
  color: #94a3b8;
  font-size: 0.9rem;
}

.filter-group input,
.filter-group select {
  width: 100%;
  padding: 10px 12px;
  background: #0f172a;
  border: 1px solid #334155;
  border-radius: 8px;
  color: #f8fafc;
  font-size: 0.95rem;
}

.actions-row {
  display: flex;
  gap: 12px;
  padding-top: 12px;
  border-top: 1px solid #334155;
}

.btn-primary,
.btn-success {
  padding: 12px 24px;
  border-radius: 8px;
  border: none;
  font-weight: 600;
  cursor: pointer;
  display: flex;
  gap: 8px;
  align-items: center;
}

.btn-primary {
  background: #3b82f6;
  color: white;
}

.btn-primary:hover {
  background: #2563eb;
}

.btn-success {
  background: #10b981;
  color: white;
}

.btn-success:hover {
  background: #059669;
}

.preview-modal {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.7);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.preview-content {
  background: #1e293b;
  width: 90%;
  max-width: 1000px;
  max-height: 90vh;
  border-radius: 12px;
  overflow: hidden;
}

.preview-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px 20px;
  border-bottom: 1px solid #334155;
}

.preview-header h3 {
  margin: 0;
  color: #f8fafc;
}

.close-btn {
  background: transparent;
  border: none;
  color: #94a3b8;
  font-size: 1.5rem;
  cursor: pointer;
}
</style>
