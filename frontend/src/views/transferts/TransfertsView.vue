<template>
  <MainLayout>
    <div class="kanban-container">
      <div class="header-bar">
        <div>
          <h2>Gestion des Transferts</h2>
          <p>Workflow logistique des équipements</p>
        </div>
        <button class="refresh-btn" @click="fetchKanban" :disabled="loading">
          <i class="pi pi-refresh" :class="{ 'pi-spin': loading }"></i> Actualiser
        </button>
      </div>

      <div class="kanban-board">
        <!-- Colonne: A EXPEDIER -->
        <div 
          class="kanban-column" 
          :class="{ 'drag-over': dragOverColumn === 'a_expedier' }"
          @dragover.prevent 
          @dragenter="dragOverColumn = 'a_expedier'"
          @dragleave="dragOverColumn = null"
          @drop="onDrop($event, 'a_expedier')"
        >
          <div class="column-header a-expedier">
            <h3>A EXPÉDIER ({{ columns.a_expedier.length }})</h3>
          </div>
          <div class="column-content">
            <div 
              v-for="item in columns.a_expedier" 
              :key="item.id" 
              class="kanban-card"
              draggable="true"
              @dragstart="onDragStart($event, item)"
              @dragend="onDragEnd"
            >
              <div class="card-tag" :class="item.urgence?.toLowerCase()">{{ item.urgence }}</div>
              <div class="card-title">{{ item.nom_materiel }}</div>
              <div class="card-info">
                <span><i class="pi pi-building"></i> {{ item.agence }}</span>
                <span><i class="pi pi-shopping-cart"></i> Qté: {{ item.quantite }}</span>
              </div>
              <div class="card-date">
                <i class="pi pi-calendar"></i> {{ formatDate(item.date) }}
              </div>
            </div>
          </div>
        </div>

        <!-- Colonne: EN TRANSIT -->
        <div 
          class="kanban-column" 
          :class="{ 'drag-over': dragOverColumn === 'en_transit' }"
          @dragover.prevent 
          @dragenter="dragOverColumn = 'en_transit'"
          @dragleave="dragOverColumn = null"
          @drop="onDrop($event, 'en_transit')"
        >
          <div class="column-header en-transit">
            <h3>EN TRANSIT ({{ columns.en_transit.length }})</h3>
          </div>
          <div class="column-content">
            <div 
              v-for="item in columns.en_transit" 
              :key="item.id" 
              class="kanban-card"
              draggable="true"
              @dragstart="onDragStart($event, item)"
              @dragend="onDragEnd"
            >
              <div class="card-title">{{ item.nom_materiel }}</div>
              <div class="card-info">
                <span><i class="pi pi-map-marker"></i> Vers: {{ item.agence }}</span>
                <span><i class="pi pi-shopping-cart"></i> Qté: {{ item.quantite }}</span>
              </div>
              <div class="card-status-label">Expédié</div>
            </div>
          </div>
        </div>

        <!-- Colonne: DESTINATION ANNEX -->
        <div 
          class="kanban-column" 
          :class="{ 'drag-over': dragOverColumn === 'recu' }"
          @dragover.prevent 
          @dragenter="dragOverColumn = 'recu'"
          @dragleave="dragOverColumn = null"
          @drop="onDrop($event, 'recu')"
        >
          <div class="column-header recu">
            <h3>DESTINATION ANNEX ({{ columns.recu.length }})</h3>
          </div>
          <div class="column-content">
            <div 
              v-for="item in columns.recu" 
              :key="item.id" 
              class="kanban-card"
            >
              <div class="card-title">{{ item.nom_materiel }}</div>
              <div class="card-info">
                <span><i class="pi pi-check-circle"></i> Reçu par: {{ item.agence }}</span>
                <span><i class="pi pi-shopping-cart"></i> Qté: {{ item.quantite }}</span>
              </div>
              <div class="card-date">Reçu le {{ formatDate(item.date) }}</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import MainLayout from '@/layouts/MainLayout.vue'
import transfertApi from '@/api/transfertApi'
import { useToast } from 'primevue/usetoast'

const toast = useToast()
const loading = ref(false)
const dragOverColumn = ref(null)
const columns = ref({
  a_expedier: [],
  en_transit: [],
  recu: []
})

const fetchKanban = async () => {
  loading.value = true
  try {
    const { data } = await transfertApi.getKanban()
    columns.value = data
  } catch (error) {
    console.error(error)
    toast.add({ severity: 'error', summary: 'Erreur', detail: 'Impossible de charger le tableau de bord', life: 3000 })
  } finally {
    loading.value = false
  }
}

// Drag & Drop Natif
const onDragStart = (event, item) => {
  event.dataTransfer.setData('itemId', item.id)
  event.dataTransfer.effectAllowed = 'move'
  // On peut ajouter une classe CSS au drag image si besoin
  event.target.classList.add('dragging')
}

const onDragEnd = (event) => {
  event.target.classList.remove('dragging')
  dragOverColumn.value = null
}

const onDrop = async (event, targetColumn) => {
  dragOverColumn.value = null
  const itemId = event.dataTransfer.getData('itemId')
  
  // Logique métier des colonnes
  if (targetColumn === 'a_expedier') return 
  
  const item = findItem(itemId)
  if (!item) return

  // Empêcher les sauts d'étapes (optionnel selon le besoin)
  // De "A expédier" directement à "Reçu"
  if (targetColumn === 'recu' && item.id.startsWith('demande_')) {
    toast.add({ severity: 'warn', summary: 'Action impossible', detail: 'Le matériel doit d\'abord transiter par l\'expédition.', life: 3000 })
    return
  }

  // Optimistic UI : Déplacement local immédiat pour la fluidité
  moveItemLocally(item, targetColumn)

  try {
    await transfertApi.updateStatus(item.id, targetColumn)
    toast.add({ severity: 'success', summary: 'Succès', detail: 'Mise à jour effectuée', life: 2000 })
    // On rafraîchit quand même pour être sûr de la cohérence avec le serveur
    fetchKanban()
  } catch (error) {
    toast.add({ severity: 'error', summary: 'Erreur', detail: 'Échec de la mise à jour', life: 3000 })
    fetchKanban() // Recharger l'état initial en cas d'erreur
  }
}

const findItem = (id) => {
  return [...columns.value.a_expedier, ...columns.value.en_transit, ...columns.value.recu]
    .find(i => i.id === id)
}

const moveItemLocally = (item, targetColumn) => {
  // Supprimer de toutes les colonnes
  columns.value.a_expedier = columns.value.a_expedier.filter(i => i.id !== item.id)
  columns.value.en_transit = columns.value.en_transit.filter(i => i.id !== item.id)
  columns.value.recu = columns.value.recu.filter(i => i.id !== item.id)
  
  // Ajouter à la cible
  columns.value[targetColumn].unshift(item)
}

const formatDate = (date) => {
  if (!date) return ''
  return new Date(date).toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit' })
}

onMounted(fetchKanban)
</script>

<style scoped>
.kanban-container {
  padding: 24px;
  height: 100vh;
  display: flex;
  flex-direction: column;
}

.header-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
}

.header-bar h2 { color: #f8fafc; margin: 0; }
.header-bar p { color: #94a3b8; margin: 4px 0 0 0; }

.refresh-btn {
  background: #334155;
  color: white;
  border: none;
  padding: 8px 16px;
  border-radius: 8px;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 8px;
}

.kanban-board {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 20px;
  flex: 1;
  min-height: 0;
}

.kanban-column {
  background: #0f172a;
  border-radius: 12px;
  display: flex;
  flex-direction: column;
  border: 1px solid #1e293b;
  transition: background-color 0.2s, border-color 0.2s;
}

.kanban-column.drag-over {
  background: #1e293b;
  border-color: #3b82f6;
}

.column-header {
  padding: 16px;
  border-radius: 12px 12px 0 0;
}

.column-header h3 {
  margin: 0;
  font-size: 0.9rem;
  font-weight: 700;
  letter-spacing: 0.05em;
  color: white;
}

.column-header.a-expedier { background: #1e293b; border-bottom: 3px solid #f59e0b; }
.column-header.en-transit { background: #1e293b; border-bottom: 3px solid #3b82f6; }
.column-header.recu { background: #1e293b; border-bottom: 3px solid #10b981; }

.column-content {
  padding: 12px;
  overflow-y: auto;
  flex: 1;
}

.kanban-card {
  background: #1e293b;
  border: 1px solid #334155;
  border-radius: 8px;
  padding: 12px;
  margin-bottom: 12px;
  cursor: grab;
  transition: transform 0.2s, box-shadow 0.2s, opacity 0.2s;
}

.kanban-card.dragging {
  opacity: 0.5;
  transform: scale(0.95);
}

.kanban-card:active { cursor: grabbing; }
.kanban-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0,0,0,0.3);
  border-color: #475569;
}

.card-tag {
  display: inline-block;
  padding: 2px 8px;
  border-radius: 4px;
  font-size: 0.7rem;
  font-weight: 700;
  margin-bottom: 8px;
  text-transform: uppercase;
}

.card-tag.haute { background: rgba(239, 68, 68, 0.2); color: #ef4444; }
.card-tag.moyenne { background: rgba(245, 158, 11, 0.2); color: #f59e0b; }
.card-tag.basse { background: rgba(16, 185, 129, 0.2); color: #10b981; }

.card-title {
  color: #f8fafc;
  font-weight: 600;
  margin-bottom: 8px;
}

.card-info {
  display: flex;
  flex-direction: column;
  gap: 4px;
  font-size: 0.8rem;
  color: #94a3b8;
  margin-bottom: 8px;
}

.card-info i { margin-right: 4px; }

.card-date {
  font-size: 0.75rem;
  color: #64748b;
  border-top: 1px solid #334155;
  padding-top: 8px;
}

.card-status-label {
  font-size: 0.75rem;
  color: #3b82f6;
  font-weight: 600;
  text-align: right;
}
</style>
