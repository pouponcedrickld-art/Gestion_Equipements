<template>
  <AgenceLayout>
    <div class="rapports-page" ref="pageContainer">
      <!-- En-tête Moderne -->
      <div class="page-header animate-in">
        <div class="header-left">
          <div class="title-with-icon">
            <div class="icon-wrapper">
              <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="svg-icon">
                <path d="M9 17V11M12 17V7M15 17V13M4 5H20C21.1046 5 22 5.89543 22 7V17C22 18.1046 21.1046 19 20 19H4C2.89543 19 2 18.1046 2 17V7C2 5.89543 2.89543 5 4 5Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </div>
            <div>
              <h1>Centre de Rapports</h1>
              <p class="subtitle">Analyse et extraction des données du parc</p>
            </div>
          </div>
        </div>
        <div class="header-actions">
          <Button 
            label="Exporter PDF" 
            icon="pi pi-file-pdf" 
            class="p-button-outlined p-button-danger action-btn mr-2"
            @click="exportPDF"
          />
          <Button 
            label="Exporter Excel" 
            icon="pi pi-file-excel" 
            class="p-button-outlined p-button-success action-btn"
            @click="exportExcel"
          />
        </div>
      </div>

      <!-- Résumé Analytique -->
      <div class="grid animate-in" v-if="stats">
        <div class="col-12 lg:col-8">
          <Card class="chart-card">
            <template #title>Répartition par Statut</template>
            <template #content>
              <div class="chart-container">
                <Chart type="doughnut" :data="chartData" :options="chartOptions" class="w-full md:w-30rem" />
              </div>
            </template>
          </Card>
        </div>
        <div class="col-12 lg:col-4">
          <div class="stats-stack">
            <div class="mini-stat-card">
              <span class="label">Valeur Totale du Parc</span>
              <span class="value">{{ formatCurrency(stats.valeur_parc) }}</span>
              <div class="stat-icon"><i class="pi pi-money-bill"></i></div>
            </div>
            <div class="mini-stat-card">
              <span class="label">Affectations Actives</span>
              <span class="value">{{ stats.affectations_actives }}</span>
              <div class="stat-icon"><i class="pi pi-user-plus"></i></div>
            </div>
            <div class="mini-stat-card">
              <span class="label">Transferts en Cours</span>
              <span class="value">{{ stats.transferts_en_cours }}</span>
              <div class="stat-icon"><i class="pi pi-sync"></i></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Filtres de Rapport -->
      <div class="filters-bar animate-in">
        <div class="dropdown-filters">
          <Dropdown v-model="filters.agence_id" :options="agences" optionLabel="nom" optionValue="id" placeholder="Filtrer par Agence" class="modern-dropdown" showClear />
          <Dropdown v-model="filters.categorie_id" :options="categories" optionLabel="nom" optionValue="id" placeholder="Filtrer par Catégorie" class="modern-dropdown" showClear />
          <Button label="Générer l'inventaire" icon="pi pi-refresh" @click="loadReport" :loading="loading" />
        </div>
      </div>

      <!-- Aperçu des données -->
      <Card class="table-card animate-in" v-if="reportData.length">
        <template #content>
          <DataTable :value="reportData" stripedRows paginator :rows="10" responsiveLayout="scroll">
            <Column field="reference" header="Référence" sortable></Column>
            <Column field="marque" header="Marque/Modèle">
              <template #body="{data}">{{ data.marque }} {{ data.modele }}</template>
            </Column>
            <Column field="categorie.nom" header="Catégorie"></Column>
            <Column field="agence_actuelle.nom" header="Agence"></Column>
            <Column field="statut_global" header="Statut">
              <template #body="{data}">
                <Tag :value="data.statut_global" :severity="getStatutSeverity(data.statut_global)" />
              </template>
            </Column>
          </DataTable>
        </template>
      </Card>
    </div>
  </AgenceLayout>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import AgenceLayout from '@/layouts/AgenceLayout.vue'
import axios from '@/api/axiosConfig'
import { useToast } from 'primevue/usetoast'
import gsap from 'gsap'

// PrimeVue
import Button from 'primevue/button'
import Card from 'primevue/card'
import Dropdown from 'primevue/dropdown'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Tag from 'primevue/tag'
import Chart from 'primevue/chart'

const toast = useToast()
const stats = ref(null)
const reportData = ref([])
const loading = ref(false)
const agences = ref([])
const categories = ref([])
const filters = ref({
  agence_id: null,
  categorie_id: null
})

const chartData = computed(() => {
  if (!stats.value) return null
  return {
    labels: stats.value.equipements_par_statut.map(s => s.statut_global.replace('_', ' ').toUpperCase()),
    datasets: [{
      data: stats.value.equipements_par_statut.map(s => s.total),
      backgroundColor: ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#6366f1'],
      hoverBackgroundColor: ['#2563eb', '#059669', '#d97706', '#dc2626', '#4f46e5']
    }]
  }
})

const chartOptions = {
  plugins: { legend: { position: 'bottom' } },
  cutout: '60%'
}

const loadStats = async () => {
  try {
    const res = await axios.get('/rapports/global')
    stats.value = res.data.data
  } catch (err) {
    toast.add({ severity: 'error', summary: 'Erreur', detail: 'Impossible de charger les statistiques' })
  }
}

const loadReport = async () => {
  loading.value = true
  try {
    const res = await axios.get('/rapports/inventaire', { params: filters.value })
    reportData.value = res.data.data
    gsap.from('.table-card', { opacity: 0, y: 20, duration: 0.5 })
  } finally {
    loading.value = false
  }
}

const formatCurrency = (val) => new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'XOF' }).format(val || 0)
const getStatutSeverity = (s) => s === 'en_stock_general' ? 'success' : 'info'

// Fonctions d'exportation
const exportPDF = () => {
  toast.add({ severity: 'info', summary: 'Préparation', detail: 'Génération du rapport PDF en cours...' })
  setTimeout(() => {
    window.print()
  }, 500)
}

const exportExcel = () => {
  if (reportData.value.length === 0) {
    toast.add({ severity: 'warn', summary: 'Attention', detail: 'Aucune donnée à exporter' })
    return
  }
  
  const headers = ['Référence', 'Marque', 'Modèle', 'Catégorie', 'Agence', 'Statut']
  const rows = reportData.value.map(item => [
    item.reference,
    item.marque,
    item.modele,
    item.categorie?.nom || 'N/A',
    item.agence_actuelle?.nom || 'N/A',
    item.statut_global
  ])
  
  const csvContent = "data:text/csv;charset=utf-8," 
    + headers.join(",") + "\n"
    + rows.map(e => e.join(",")).join("\n")

  const encodedUri = encodeURI(csvContent)
  const link = document.createElement("a")
  link.setAttribute("href", encodedUri)
  link.setAttribute("download", `inventaire_${new Date().toISOString().split('T')[0]}.csv`)
  document.body.appendChild(link)
  link.click()
  document.body.removeChild(link)
  
  toast.add({ severity: 'success', summary: 'Succès', detail: 'Export Excel (CSV) réussi' })
}

onMounted(async () => {
  loadStats()
  const [resAg, resCat] = await Promise.all([
    axios.get('/agences'),
    axios.get('/categories/list')
  ])
  agences.value = resAg.data.data || resAg.data
  categories.value = resCat.data.data || resCat.data
  
  gsap.from('.animate-in', { opacity: 0, y: 20, duration: 0.8, stagger: 0.2, ease: 'power3.out' })
})
</script>

<style scoped lang="scss">
.rapports-page {
  padding: 2rem;
  min-height: 100vh;
}

.title-with-icon {
  display: flex;
  align-items: center;
  gap: 1.5rem;
  .icon-wrapper {
    width: 60px; height: 60px;
    background: linear-gradient(135deg, #f43f5e 0%, #e11d48 100%);
    border-radius: 16px;
    display: flex; align-items: center; justify-content: center;
    color: white; box-shadow: 0 8px 16px rgba(225, 29, 72, 0.2);
    .svg-icon { width: 32px; height: 32px; }
  }
  h1 { font-size: 2rem; font-weight: 800; color: #1e293b; margin: 0; }
  .subtitle { color: #64748b; margin-top: 0.25rem; }
}

.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 3rem; }

.chart-card {
  border-radius: 24px; border: none; box-shadow: 0 10px 25px rgba(0,0,0,0.05);
  background: white; height: 100%;
}

.stats-stack {
  display: flex; flex-direction: column; gap: 1.5rem;
}

.mini-stat-card {
  background: white; padding: 1.5rem; border-radius: 20px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.03);
  position: relative; overflow: hidden;
  
  .label { color: #64748b; font-size: 0.85rem; font-weight: 600; text-transform: uppercase; }
  .value { display: block; font-size: 1.5rem; font-weight: 800; color: #1e293b; margin-top: 0.5rem; }
  .stat-icon {
    position: absolute; right: 1.5rem; top: 50%; transform: translateY(-50%);
    font-size: 2rem; opacity: 0.1; color: #e11d48;
  }
}

.filters-bar {
  background: white; padding: 1.5rem; border-radius: 16px; margin: 2rem 0;
  display: flex; justify-content: space-between; align-items: center;
  box-shadow: 0 4px 12px rgba(0,0,0,0.03);
  
  .dropdown-filters { display: flex; gap: 1.5rem; width: 100%; }
  .modern-dropdown { flex: 1; border: none; background: #f8fafc; border-radius: 10px; }
}

.table-card { border-radius: 24px; border: none; box-shadow: 0 10px 25px rgba(0,0,0,0.05); }
</style>
