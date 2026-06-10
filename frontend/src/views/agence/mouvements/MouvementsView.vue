<template>
  <AgenceLayout>
    <div class="mouvements-container">
      <div class="header-bar">
        <div class="header-left">
          <h2>Historique des Mouvements</h2>
          <p>Suivez tous les mouvements de matériel</p>
        </div>
      </div>

      <!-- Filtres et Recherche -->
      <div class="filters-card">
        <div class="filters-row">
          <div class="search-box">
            <i class="pi pi-search"></i>
            <input 
              v-model="filters.search" 
              type="text" 
              placeholder="Rechercher un mouvement..." 
              @input="debounceSearch"
            >
          </div>
          <div class="select-box">
            <select v-model="filters.type_mouvement">
              <option value="">Tous les types</option>
              <option value="ajout">Ajout</option>
              <option value="transfert">Transfert</option>
              <option value="panne">Panne</option>
              <option value="perte">Perte</option>
              <option value="reparation">Réparation</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Tableau des mouvements -->
      <div class="table-card">
        <div v-if="loading" class="loading-state">
          <i class="pi pi-spin pi-spinner"></i> Chargement...
        </div>
        <div v-else-if="mouvements.length === 0" class="empty-state">
          <i class="pi pi-info-circle"></i>
          <p>Aucun mouvement trouvé.</p>
        </div>
        <div v-else class="table-wrapper">
          <table class="data-table">
            <thead>
              <tr>
                <th>Date</th>
                <th>Type</th>
                <th>Équipement/Consommable</th>
                <th>Agent</th>
                <th>Description</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="m in mouvements" :key="m.id">
                <td>{{ formatDate(m.date_mouvement) }}</td>
                <td><span class="status-badge" :class="m.type_mouvement">{{ formatType(m.type_mouvement) }}</span></td>
                <td>
                  <div v-if="m.equipement" class="info-row">
                    <span>{{ m.equipement.nom }}</span>
                    <small>{{ m.equipement.reference }}</small>
                  </div>
                  <div v-else-if="m.consommable" class="info-row">
                    <span>{{ m.consommable.nom }}</span>
                  </div>
                  <span v-else>-</span>
                </td>
                <td>{{ m.agent ? `${m.agent.nom} ${m.agent.prenom}` : '-' }}</td>
                <td class="description">{{ m.description }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AgenceLayout>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import AgenceLayout from '@/layouts/AgenceLayout.vue'
import { useMouvementStore } from '@/stores/mouvementStore.js'

const mouvementStore = useMouvementStore()

const mouvements = ref([])
const loading = ref(false)
const filters = reactive({ search: '', type_mouvement: '' })

const fetchMouvements = async () => {
  loading.value = true
  await mouvementStore.fetchMouvements(filters)
  mouvements.value = mouvementStore.mouvements
  loading.value = false
}

const debounceTimer = ref(null)
const debounceSearch = () => {
  clearTimeout(debounceTimer.value)
  debounceTimer.value = setTimeout(fetchMouvements, 500)
}

const formatDate = (date) => {
  if (!date) return ''
  return new Date(date).toLocaleString('fr-FR')
}

const formatType = (type) => {
  const map = {
    ajout: 'Ajout',
    transfert: 'Transfert',
    panne: 'Panne',
    perte: 'Perte',
    reparation: 'Réparation'
  }
  return map[type] || type
}

onMounted(fetchMouvements)
</script>

<style scoped>
.mouvements-container { padding: 24px; color: #f8fafc; }
.header-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; }
.header-bar h2 { margin: 0; font-size: 1.5rem; }
.header-bar p { color: #94a3b8; margin: 4px 0 0 0; }
.filters-card {
  background: #1e293b; border: 1px solid #334155; padding: 16px;
  border-radius: 12px; margin-bottom: 20px;
}
.filters-row { display: flex; gap: 16px; flex-wrap: wrap; }
.search-box { position: relative; flex: 1; min-width: 200px; }
.search-box i { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8; }
.search-box input, .select-box select {
  width: 100%; background: #0f172a; border: 1px solid #334155; color: #f8fafc;
  padding: 10px 12px 10px 40px; border-radius: 8px; outline: none;
}
.select-box select { padding-left: 12px; width: 180px; }
.table-card { background: #1e293b; border: 1px solid #334155; border-radius: 12px; overflow: hidden; }
.data-table { width: 100%; border-collapse: collapse; }
.data-table th { background: #0f172a; padding: 14px 16px; text-align: left; color: #94a3b8; font-size: 0.85rem; font-weight: 600; text-transform: uppercase; }
.data-table td { padding: 14px 16px; border-bottom: 1px solid #334155; }
.info-row { display: flex; flex-direction: column; }
.info-row span { font-weight: 600; }
.info-row small { color: #64748b; font-size: 0.8rem; }
.description { max-width: 400px; word-break: break-word; }
.status-badge { padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 700; }
.status-badge.ajout { background: rgba(16, 185, 129, 0.15); color: #10b981; }
.status-badge.transfert { background: rgba(59, 130, 246, 0.15); color: #3b82f6; }
.status-badge.panne { background: rgba(239, 68, 68, 0.15); color: #ef4444; }
.status-badge.perte { background: rgba(139, 92, 246, 0.15); color: #8b5cf6; }
.status-badge.reparation { background: rgba(245, 158, 11, 0.15); color: #f59e0b; }
.loading-state, .empty-state { padding: 60px; text-align: center; color: #94a3b8; }
.loading-state i { font-size: 2rem; margin-bottom: 12px; color: #3b82f6; }
.empty-state i { font-size: 3rem; margin-bottom: 12px; opacity: 0.2; }
</style>
