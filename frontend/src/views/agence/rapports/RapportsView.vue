<template>
  <AgenceLayout>
    <div class="p-6 max-w-7xl mx-auto">
      <!-- Header -->
      <div class="flex items-center justify-between mb-8">
        <div class="flex items-center gap-4">
          <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-primary-500 to-primary-700 flex items-center justify-center text-white text-2xl">
            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-7 h-7">
              <path d="M9 17V11M12 17V7M15 17V13M4 5H20C21.1046 5 22 5.89543 22 7V17C22 18.1046 21.1046 19 20 19H4C2.89543 19 2 18.1046 2 17V7C2 5.89543 2.89543 5 4 5Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </div>
          <div>
            <h1 class="text-2xl font-bold text-neutral-800">Centre de Rapports</h1>
            <p class="text-neutral-500 mt-1">Analyse et extraction des données du parc</p>
          </div>
        </div>
        <div class="flex items-center gap-3">
          <button 
            @click="exportPDF"
            class="btn btn-secondary flex items-center gap-2"
          >
            <i class="pi pi-file-pdf"></i>
            <span>Exporter PDF</span>
          </button>
          <button 
            @click="exportExcel"
            class="btn btn-success flex items-center gap-2"
          >
            <i class="pi pi-file-excel"></i>
            <span>Exporter Excel</span>
          </button>
        </div>
      </div>

      <div v-if="!stats" class="flex items-center justify-center py-20">
        <div class="flex items-center gap-2">
          <div class="animate-spin h-6 w-6 border-2 border-primary-500 border-t-transparent rounded-full"></div>
          <span class="text-neutral-500">Chargement...</span>
        </div>
      </div>

      <div v-else>
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 mb-8">
          <div class="card p-6 flex items-center gap-4">
            <div class="w-14 h-14 rounded-xl bg-success-100 text-success-600 flex items-center justify-center text-2xl">
              <i class="pi pi-money-bill"></i>
            </div>
            <div>
              <div class="text-sm text-neutral-500">Valeur Totale du Parc</div>
              <div class="text-2xl font-bold text-neutral-800 mt-1">{{ formatCurrency(stats.valeur_parc) }}</div>
            </div>
          </div>

          <div class="card p-6 flex items-center gap-4">
            <div class="w-14 h-14 rounded-xl bg-primary-100 text-primary-600 flex items-center justify-center text-2xl">
              <i class="pi pi-user-plus"></i>
            </div>
            <div>
              <div class="text-sm text-neutral-500">Affectations Actives</div>
              <div class="text-2xl font-bold text-neutral-800 mt-1">{{ stats.affectations_actives }}</div>
            </div>
          </div>

          <div class="card p-6 flex items-center gap-4">
            <div class="w-14 h-14 rounded-xl bg-warning-100 text-warning-600 flex items-center justify-center text-2xl">
              <i class="pi pi-sync"></i>
            </div>
            <div>
              <div class="text-sm text-neutral-500">Transferts en Cours</div>
              <div class="text-2xl font-bold text-neutral-800 mt-1">{{ stats.transferts_en_cours }}</div>
            </div>
          </div>

          <div class="card p-6 flex items-center gap-4">
            <div class="w-14 h-14 rounded-xl bg-danger-100 text-danger-600 flex items-center justify-center text-2xl">
              <i class="pi pi-exclamation-circle"></i>
            </div>
            <div>
              <div class="text-sm text-neutral-500">Équipements en Panne</div>
              <div class="text-2xl font-bold text-neutral-800 mt-1">{{ stats.equipements_par_statut?.find(s => s.statut_global === 'en_panne')?.total || 0 }}</div>
            </div>
          </div>
        </div>

        <!-- Filters Bar -->
        <div class="card p-6 mb-8">
          <div class="flex flex-wrap items-center gap-4">
            <div class="flex-1 min-w-[200px]">
              <select v-model="filters.agence_id" class="input">
                <option value="">Toutes les agences</option>
                <option v-for="agence in agences" :key="agence.id" :value="agence.id">{{ agence.nom }}</option>
              </select>
            </div>
            <div class="flex-1 min-w-[200px]">
              <select v-model="filters.categorie_id" class="input">
                <option value="">Toutes les catégories</option>
                <option v-for="categorie in categories" :key="categorie.id" :value="categorie.id">{{ categorie.nom }}</option>
              </select>
            </div>
            <button 
              @click="loadReport" 
              :disabled="loading"
              class="btn btn-primary flex items-center gap-2"
            >
              <i v-if="loading" class="pi pi-spin pi-spinner"></i>
              <i v-else class="pi pi-refresh"></i>
              <span>Générer l'inventaire</span>
            </button>
          </div>
        </div>

        <!-- Data Table -->
        <div class="card overflow-hidden">
          <div class="overflow-x-auto">
            <table class="w-full table">
              <thead class="bg-neutral-50">
                <tr>
                  <th class="px-4 py-3 text-left text-xs font-semibold text-neutral-500 uppercase tracking-wider">Référence</th>
                  <th class="px-4 py-3 text-left text-xs font-semibold text-neutral-500 uppercase tracking-wider">Marque/Modèle</th>
                  <th class="px-4 py-3 text-left text-xs font-semibold text-neutral-500 uppercase tracking-wider">Catégorie</th>
                  <th class="px-4 py-3 text-left text-xs font-semibold text-neutral-500 uppercase tracking-wider">Agence</th>
                  <th class="px-4 py-3 text-left text-xs font-semibold text-neutral-500 uppercase tracking-wider">Statut</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-neutral-200">
                <tr v-for="item in reportData" :key="item.id" class="hover:bg-neutral-50 transition-colors">
                  <td class="px-4 py-4 text-sm text-neutral-800 font-medium">{{ item.reference }}</td>
                  <td class="px-4 py-4 text-sm text-neutral-600">{{ item.marque }} {{ item.modele }}</td>
                  <td class="px-4 py-4 text-sm text-neutral-600">{{ item.categorie?.nom || 'N/A' }}</td>
                  <td class="px-4 py-4 text-sm text-neutral-600">{{ item.agence_actuelle?.nom || 'N/A' }}</td>
                  <td class="px-4 py-4">
                    <span class="badge" :class="getStatutBadgeClass(item.statut_global)">{{ item.statut_global }}</span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </AgenceLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import AgenceLayout from '@/layouts/AgenceLayout.vue'
import api from '@/api/axiosConfig'
import { toast } from 'vue3-toastify'

const stats = ref(null)
const reportData = ref([])
const loading = ref(false)
const agences = ref([])
const categories = ref([])
const filters = ref({
  agence_id: null,
  categorie_id: null
})

const formatCurrency = (val) => new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'XOF' }).format(val || 0)

const getStatutBadgeClass = (s) => {
  switch (s) {
    case 'en_stock_general':
    case 'en_stock_local':
      return 'badge-success'
    case 'affecte':
      return 'badge-primary'
    case 'en_panne':
    case 'en_maintenance':
      return 'badge-warning'
    case 'hors_service':
      return 'badge-danger'
    default:
      return 'badge-neutral'
  }
}

const loadStats = async () => {
  try {
    const res = await api.get('/rapports/global')
    stats.value = res.data.data
  } catch (err) {
    toast.error('Impossible de charger les statistiques')
  }
}

const loadReport = async () => {
  loading.value = true
  try {
    const res = await api.get('/rapports/inventaire', { params: filters.value })
    reportData.value = res.data.data
  } catch (err) {
    toast.error('Erreur lors du chargement du rapport')
  } finally {
    loading.value = false
  }
}

const exportPDF = () => {
  toast.info('Génération du rapport PDF en cours...')
  setTimeout(() => {
    window.print()
  }, 500)
}

const exportExcel = () => {
  if (reportData.value.length === 0) {
    toast.warn('Aucune donnée à exporter')
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
  
  toast.success('Export Excel (CSV) réussi')
}

onMounted(async () => {
  loadStats()
  loadReport()
  const [resAg, resCat] = await Promise.all([
    api.get('/agences'),
    api.get('/categories/list')
  ])
  agences.value = resAg.data.data || resAg.data
  categories.value = resCat.data.data || resCat.data
})
</script>
