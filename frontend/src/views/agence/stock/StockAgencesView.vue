<template>
  <AgenceLayout>
    <div class="dashboard-bulletin-board animate-fade-in">
      <!-- Header Section -->
      <div class="dashboard-header mb-8">
        <div class="welcome-section">
          <h1 class="text-3xl font-extrabold text-dark tracking-tight flex items-center gap-3">
            <span class="p-2 bg-primary rounded-xl shadow-sm"><i class="pi pi-box text-dark"></i></span>
            Inventaire & Stock Agence
          </h1>
          <p class="text-muted mt-2 font-medium">
            Vue d'ensemble des équipements localisés dans l'agence <span class="text-primary-hover font-bold">{{ authStore.user?.agence?.nom }}</span>
          </p>
        </div>
      </div>

      <div v-if="loading" class="loader-overlay">
        <div class="loader-content">
          <div class="spinner"></div>
          <p>Chargement de l'inventaire...</p>
        </div>
      </div>

      <div v-else>
        <!-- Top Stats - Inventory Health -->
        <div class="kpi-row grid grid-cols-1 md:grid-cols-4 gap-5 mb-8">
          <div class="kpi-card primary">
            <div class="kpi-icon-box"><i class="pi pi-database"></i></div>
            <div class="kpi-content">
              <span class="kpi-label">Total Stock</span>
              <span class="kpi-value">{{ equipementsAgence.length }}</span>
            </div>
          </div>
          <div class="kpi-card success">
            <div class="kpi-icon-box"><i class="pi pi-check-circle"></i></div>
            <div class="kpi-content">
              <span class="kpi-label">Disponible</span>
              <span class="kpi-value">{{ countByStatus('en_stock_local') + countByStatus('en_service') }}</span>
            </div>
          </div>
          <div class="kpi-card warning">
            <div class="kpi-icon-box"><i class="pi pi-users"></i></div>
            <div class="kpi-content">
              <span class="kpi-label">Assignés</span>
              <span class="kpi-value">{{ countByStatus('affecte') }}</span>
            </div>
          </div>
          <div class="kpi-card error">
            <div class="kpi-icon-box"><i class="pi pi-wrench"></i></div>
            <div class="kpi-content">
              <span class="kpi-label">Hors Service / Panne</span>
              <span class="kpi-value">{{ countByStatus('en_panne') + countByStatus('hors_service') }}</span>
            </div>
          </div>
        </div>

        <!-- Inventory List Board -->
        <div class="bento-card p-8">
          <div class="flex flex-wrap justify-between items-center mb-8 gap-4">
            <h2 class="text-xl font-extrabold text-dark flex items-center gap-2">
              <i class="pi pi-table text-primary"></i> Répertoire des Actifs
            </h2>
            
            <div class="flex flex-wrap items-center gap-4">
              <div class="search-box relative">
                <i class="pi pi-search absolute left-3 top-1/2 -translate-y-1/2 text-muted"></i>
                <input v-model="search" placeholder="Rechercher un actif..." class="pl-10 input-bulletin w-64" />
              </div>
              <select v-model="filterStatus" class="input-bulletin bg-app font-bold cursor-pointer">
                <option value="">Tous les états</option>
                <option value="actif">Actif</option>
                <option value="en_panne">En Panne</option>
                <option value="en_maintenance">Maintenance</option>
                <option value="nouveau">Nouveau</option>
              </select>
            </div>
          </div>

          <div v-if="filteredEquipements.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <div v-for="equipement in filteredEquipements" :key="equipement.id" class="stock-item-card animate-card">
              <div class="item-header p-4 border-b border-color flex justify-between items-start">
                <div class="flex flex-col">
                  <span class="item-sn text-[0.6rem] font-black uppercase text-muted tracking-widest">INV: {{ equipement.code_inventaire }}</span>
                  <h3 class="item-name font-black text-dark text-sm truncate w-40">{{ equipement.nom }}</h3>
                </div>
                <div class="item-status-dot" :class="`bg-${getStatutColor(equipement.etat)}`" v-tooltip="getStatutLabel(equipement.etat)"></div>
              </div>
              
              <div class="item-body p-4 bg-app/30">
                <div class="space-y-3">
                  <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-white flex items-center justify-center text-xs text-muted shadow-sm">
                      <i class="pi pi-tag"></i>
                    </div>
                    <span class="text-xs font-bold text-dark">{{ equipement.categorie?.nom || '-' }}</span>
                  </div>
                  <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-white flex items-center justify-center text-xs text-muted shadow-sm">
                      <i class="pi pi-map-marker"></i>
                    </div>
                    <span class="text-xs font-bold text-dark">{{ equipement.localisation || 'Localisation non définie' }}</span>
                  </div>
                </div>
              </div>

              <div class="item-footer p-4 flex justify-between items-center border-t border-color">
                <span class="text-[0.65rem] font-bold px-2 py-1 rounded bg-app text-muted">{{ equipement.statut_global }}</span>
                <button class="text-primary-hover hover:scale-110 transition-transform"><i class="pi pi-external-link"></i></button>
              </div>
            </div>
          </div>

          <div v-else class="empty-bulletin py-20 text-center">
            <div class="empty-icon mb-4 opacity-20"><i class="pi pi-box"></i></div>
            <h3 class="text-lg font-bold text-dark">Aucun actif trouvé</h3>
            <p class="text-muted">Votre recherche ne correspond à aucun équipement de l'inventaire.</p>
          </div>
        </div>
      </div>


    </div>
  </AgenceLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useToast } from 'primevue/usetoast'
import { useTransfertStore } from '@/stores/transfertStore'
import { useEquipementStore } from '@/stores/equipementStore'
import { useAuthStore } from '@/stores/authStore'
import AgenceLayout from '@/layouts/AgenceLayout.vue'

const router = useRouter()
const toast = useToast()
const transfertStore = useTransfertStore()
const equipementStore = useEquipementStore()
const authStore = useAuthStore()
const search = ref('')
const filterStatus = ref('')
const loading = ref(false)

const equipementsAgence = computed(() => {
  const list = Array.isArray(equipementStore.equipements) 
    ? equipementStore.equipements 
    : (equipementStore.equipements?.data || [])
    
  return list.filter(e => e.agence_actuelle_id === authStore.userAgence)
})



// --- Filtrage des équipements ---
const filteredEquipements = computed(() => {
  let list = equipementsAgence.value
  if (search.value) {
    const q = search.value.toLowerCase()
    list = list.filter(e => 
      e.nom?.toLowerCase().includes(q) || 
      e.code_inventaire?.toLowerCase().includes(q) ||
      e.categorie?.nom?.toLowerCase().includes(q)
    )
  }
  if (filterStatus.value) {
    list = list.filter(e => e.etat === filterStatus.value)
  }
  return list
})

const countByStatus = (status) => {
  return equipementsAgence.value.filter(e => e.etat === status || e.statut_global === status).length
}

const getStatutLabel = (etat) => {
  const options = { nouveau: 'Nouveau', actif: 'Actif', en_maintenance: 'Maintenance', en_panne: 'Panne', hors_service: 'HS' }
  return options[etat] || etat || 'N/A'
}

const getStatutColor = (etat) => {
  const colors = { nouveau: 'info', actif: 'success', en_maintenance: 'warning', en_panne: 'error', hors_service: 'dark' }
  return colors[etat] || 'muted'
}


// --- Chargement des équipements ---
const fetchEquipements = async () => {
  loading.value = true
  try {
    await equipementStore.fetchEquipements()
  } finally {
    loading.value = false
  }
}

onMounted(fetchEquipements)
</script>

<style scoped>
.dashboard-bulletin-board {
  padding: 1rem 1.5rem;
  background-color: var(--bg-app);
  min-height: 100vh;
}

.dashboard-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
}

/* KPI Cards */
.kpi-card {
  background: white;
  padding: 1.5rem;
  border-radius: var(--radius-xl);
  display: flex;
  align-items: center;
  gap: 1.25rem;
  box-shadow: var(--shadow-sm);
  border: 1px solid var(--border-color);
  transition: transform 0.3s;
}

.kpi-card:hover { transform: translateY(-5px); }

.kpi-icon-box {
  width: 50px;
  height: 50px;
  border-radius: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.25rem;
}

.kpi-card.primary .kpi-icon-box { background: var(--primary-light); color: var(--primary-hover); }
.kpi-card.error .kpi-icon-box { background: #fee2e2; color: #ef4444; }
.kpi-card.success .kpi-icon-box { background: #dcfce7; color: #10b981; }
.kpi-card.warning .kpi-icon-box { background: #fef3c7; color: #f59e0b; }

.kpi-label { font-size: 0.75rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; }
.kpi-value { font-size: 1.75rem; font-weight: 900; color: var(--text-dark); line-height: 1; display: block; margin-top: 4px; }

/* Bento Card */
.bento-card {
  background: white;
  border-radius: var(--radius-xl);
  border: 1px solid var(--border-color);
  box-shadow: var(--shadow-sm);
}

.input-bulletin {
  background: white;
  border: 1px solid var(--border-color);
  border-radius: 12px;
  padding: 10px 16px;
  font-size: 0.85rem;
  color: var(--text-dark);
}

/* Stock Item Cards */
.stock-item-card {
  background: white;
  border-radius: 16px;
  border: 1px solid var(--border-color);
  transition: all 0.3s ease;
  overflow: clip;
}

.stock-item-card:hover {
  border-color: var(--primary);
  box-shadow: var(--shadow-md);
  transform: translateY(-4px);
}

.item-status-dot {
  width: 10px;
  height: 10px;
  border-radius: 50%;
  flex-shrink: 0;
}

.bg-muted { background: var(--text-muted); }

/* Loader & States */
.loader-overlay {
  height: 400px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.spinner {
  width: 40px;
  height: 40px;
  border: 3px solid var(--primary-light);
  border-top-color: var(--primary);
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin: 0 auto 1rem;
}

@keyframes spin { to { transform: rotate(360deg); } }
@keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

.animate-fade-in {
  animation: fadeIn 0.6s cubic-bezier(0.16, 1, 0.3, 1);
}

.empty-icon { font-size: 4rem; color: var(--border-color); }
</style>
