<template>
  <MainLayout>
    <div class="affectations-container">
      <div class="header-bar">
        <div>
          <h2>Gestion des Demandes & Affectations</h2>
          <p v-if="authStore.isGestionnaireGeneral">Traitez les demandes de matériel des sous-agences</p>
        </div>
      </div>

      <!-- Section des demandes en attente pour le gestionnaire général -->
      <div v-if="authStore.isGestionnaireGeneral" class="section-card">
        <h3><i class="pi pi-clock"></i> Demandes en attente de traitement</h3>
        
        <div v-if="loadingDemandes" class="loading-inline">
          <i class="pi pi-spin pi-spinner"></i> Chargement...
        </div>

        <div v-else-if="demandes.length === 0" class="empty-mini">
          Aucune demande en attente.
        </div>

        <div v-else class="table-wrapper">
          <table class="data-table">
            <thead>
              <tr>
                <th>Agence</th>
                <th>Matériel</th>
                <th>Quantité</th>
                <th>Urgence</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="d in demandes" :key="d.id">
                <td>{{ d.agence?.nom }}</td>
                <td>{{ d.equipement?.nom }}</td>
                <td>{{ d.quantite }}</td>
                <td>
                  <span class="badge-urgence" :class="d.urgence.toLowerCase()">{{ d.urgence }}</span>
                </td>
                <td>
                  <button class="process-btn" @click="openProcessModal(d)">
                    Traiter
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Modal de traitement -->
      <div v-if="showModal" class="modal-overlay" @click="showModal = false">
        <div class="modal-content" @click.stop>
          <div class="modal-header">
            <h3>Traiter la demande #{{ selectedDemande?.id }}</h3>
            <button @click="showModal = false"><i class="pi pi-times"></i></button>
          </div>
          
          <form @submit.prevent="submitTraitement" class="process-form">
            <div class="form-group">
              <label>Décision</label>
              <select v-model="traitement.decision" required>
                <option value="Approuver">Approuver</option>
                <option value="Partiel">Approbation Partielle</option>
                <option value="Refuser">Refuser</option>
              </select>
            </div>

            <div class="form-group" v-if="traitement.decision !== 'Refuser'">
              <label>Quantité validée</label>
              <input type="number" v-model="traitement.quantite_validee" min="0" :max="selectedDemande?.quantite" required>
              <small>Demandé : {{ selectedDemande?.quantite }}</small>
            </div>

            <div class="form-group">
              <label>Commentaire {{ traitement.decision === 'Refuser' ? '(Obligatoire)' : '(Optionnel)' }}</label>
              <textarea v-model="traitement.observations" :required="traitement.decision === 'Refuser'" rows="3"></textarea>
            </div>

            <div class="modal-actions">
              <button type="button" class="cancel-btn" @click="showModal = false">Annuler</button>
              <button type="submit" class="confirm-btn" :disabled="processing">
                {{ processing ? 'Traitement...' : 'Confirmer' }}
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- Le reste de la page affectation originale... -->
      <div class="page-placeholder" v-if="!authStore.isGestionnaireGeneral">
        <p>Page des affectations en cours de développement...</p>
      </div>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useAuthStore } from '@/stores/authStore'
import { useToast } from 'primevue/usetoast'
import MainLayout from '@/layouts/MainLayout.vue'
import demandeMaterielApi from '@/api/demandeMaterielApi'

const authStore = useAuthStore()
const toast = useToast()
const demandes = ref([])
const loadingDemandes = ref(false)
const showModal = ref(false)
const selectedDemande = ref(null)
const processing = ref(false)

const traitement = ref({
  decision: 'Approuver',
  quantite_validee: 0,
  observations: ''
})

const fetchDemandes = async () => {
  if (!authStore.isGestionnaireGeneral) return
  loadingDemandes.value = true
  try {
    const { data } = await demandeMaterielApi.index()
    // Filtrer uniquement les demandes en attente pour le traitement
    demandes.value = data.filter(d => d.statut === 'en attente')
  } catch (error) {
    console.error(error)
  } finally {
    loadingDemandes.value = false
  }
}

const openProcessModal = (demande) => {
  selectedDemande.value = demande
  traitement.value = {
    decision: 'Approuver',
    quantite_validee: demande.quantite,
    observations: ''
  }
  showModal.value = true
}

const submitTraitement = async () => {
  processing.value = true
  try {
    await demandeMaterielApi.traiter(selectedDemande.value.id, traitement.value)
    toast.add({ severity: 'success', summary: 'Succès', detail: 'Demande traitée avec succès', life: 3000 })
    showModal.value = false
    fetchDemandes()
  } catch (error) {
    toast.add({ severity: 'error', summary: 'Erreur', detail: 'Échec du traitement', life: 3000 })
  } finally {
    processing.value = false
  }
}

onMounted(fetchDemandes)
</script>

<style scoped>
.affectations-container { padding: 24px; }
.header-bar { margin-bottom: 32px; }
.header-bar h2 { color: #f8fafc; margin: 0; }
.header-bar p { color: #94a3b8; margin-top: 4px; }

.section-card {
  background: #1e293b;
  border: 1px solid #334155;
  border-radius: 12px;
  padding: 20px;
  margin-bottom: 24px;
}

.section-card h3 { color: #e2e8f0; margin-top: 0; display: flex; align-items: center; gap: 10px; font-size: 1.1rem; }
.section-card h3 i { color: #3b82f6; }

.data-table { width: 100%; border-collapse: collapse; margin-top: 15px; }
.data-table th { text-align: left; padding: 12px; color: #94a3b8; font-size: 0.85rem; border-bottom: 2px solid #334155; }
.data-table td { padding: 12px; color: #e2e8f0; border-bottom: 1px solid #334155; }

.badge-urgence { padding: 2px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 600; }
.badge-urgence.basse { background: rgba(16, 185, 129, 0.1); color: #10b981; }
.badge-urgence.moyenne { background: rgba(245, 158, 11, 0.1); color: #f59e0b; }
.badge-urgence.haute { background: rgba(239, 68, 68, 0.1); color: #ef4444; }

.process-btn {
  background: #3b82f6;
  color: white;
  border: none;
  padding: 6px 12px;
  border-radius: 6px;
  cursor: pointer;
  font-size: 0.85rem;
}

.modal-overlay {
  position: fixed;
  top: 0; left: 0; right: 0; bottom: 0;
  background: rgba(0,0,0,0.7);
  display: flex; align-items: center; justify-content: center;
  z-index: 1000;
}

.modal-content {
  background: #1e293b;
  border: 1px solid #334155;
  border-radius: 12px;
  width: 90%; max-width: 500px;
  padding: 24px;
}

.modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
.modal-header h3 { margin: 0; color: #f8fafc; }
.modal-header button { background: none; border: none; color: #94a3b8; cursor: pointer; font-size: 1.2rem; }

.form-group { margin-bottom: 16px; display: flex; flex-direction: column; gap: 8px; }
.form-group label { color: #94a3b8; font-size: 0.9rem; }
.form-group select, .form-group input, .form-group textarea {
  background: #0f172a; border: 1px solid #334155; color: #f8fafc; padding: 10px; border-radius: 8px;
}

.modal-actions { display: flex; justify-content: flex-end; gap: 12px; margin-top: 24px; }
.cancel-btn { background: #334155; color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; }
.confirm-btn { background: #10b981; color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; }
.confirm-btn:disabled { opacity: 0.5; }

.empty-mini { text-align: center; padding: 20px; color: #94a3b8; }
.loading-inline { text-align: center; padding: 20px; color: #3b82f6; }
</style>
