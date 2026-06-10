<template>
  <AgenceLayout>
    <div class="demandes-container">
      <div class="header-bar">
        <div>
          <h2>{{ authStore.isGestionnaireGeneral ? 'Toutes les Demandes' : 'Demandes de Matériel' }}</h2>
          <p>{{ authStore.isGestionnaireGeneral ? 'Gérez les demandes de matériel de toutes les agences' : 'Liste des demandes effectuées par votre agence' }}</p>
        </div>
        <router-link v-if="authStore.isChefAgence" to="/demandes-materiel/nouveau" class="add-btn">
          <i class="pi pi-plus"></i> Nouvelle demande
        </router-link>
      </div>

      <div v-if="loading" class="loading-state">
        <i class="pi pi-spin pi-spinner"></i> Chargement des demandes...
      </div>

      <div v-else-if="demandes.length === 0" class="empty-state">
        <i class="pi pi-inbox"></i>
        <p>Aucune demande de matériel trouvée.</p>
        <router-link v-if="authStore.isChefAgence" to="/demandes-materiel/nouveau" class="link">Créer votre première demande</router-link>
      </div>

      <div v-else class="table-wrapper">
        <table class="demandes-table">
          <thead>
            <tr>
              <th>Date</th>
              <th v-if="authStore.isGestionnaireGeneral">Agence</th>
              <th>Équipement</th>
              <th>Quantité</th>
              <th>Urgence</th>
              <th>Statut</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="demande in demandes" :key="demande.id">
              <td>{{ formatDate(demande.date_souhaitee) }}</td>
              <td v-if="authStore.isGestionnaireGeneral">
                <span class="agence-name">{{ demande.agence?.nom }}</span>
              </td>
              <td>
                <div class="equip-info">
                  <span class="name">{{ demande.equipement?.nom }}</span>
                  <span class="detail">{{ demande.equipement?.marque }} {{ demande.equipement?.modele }}</span>
                </div>
              </td>
              <td>{{ demande.quantite }}</td>
              <td>
                <span class="badge-urgence" :class="demande.urgence.toLowerCase()">
                  {{ demande.urgence }}
                </span>
              </td>
              <td>
                <span class="badge-statut" :class="statusClass(demande.statut)">
                  {{ demande.statut }}
                </span>
              </td>
              <td>
                <div class="actions-cell">
                  <button class="view-btn" title="Voir détails" @click="showDetails(demande)">
                    <i class="pi pi-eye"></i>
                  </button>
                  
                  <!-- Actions CRUD pour le Gestionnaire -->
                  <template v-if="authStore.isGestionnaireGeneral">
                    <button class="edit-btn" title="Modifier" @click="openEditModal(demande)">
                      <i class="pi pi-pencil"></i>
                    </button>
                    <button class="delete-btn" title="Supprimer" @click="confirmDelete(demande)">
                      <i class="pi pi-trash"></i>
                    </button>
                  </template>

                  <button 
                    v-if="authStore.isGestionnaireGeneral && demande.statut === 'en attente'" 
                    class="process-btn-table" 
                    title="Traiter la demande"
                    @click="openProcessModal(demande)"
                  >
                    <i class="pi pi-check-square"></i> Traiter
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Modal de détails -->
      <div v-if="selectedDemande" class="modal-overlay" @click="selectedDemande = null">
        <div class="modal-content" @click.stop>
          <div class="modal-header">
            <h3>Détails de la demande</h3>
            <button @click="selectedDemande = null"><i class="pi pi-times"></i></button>
          </div>
          <div class="modal-body">
            <div class="detail-row">
              <span class="label">Motif :</span>
              <p class="value">{{ selectedDemande.motif }}</p>
            </div>
            <div v-if="selectedDemande.observations" class="detail-row">
              <span class="label">Observations (Administration) :</span>
              <p class="value">{{ selectedDemande.observations }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Modal de traitement (Gestionnaire) -->
      <div v-if="showProcessModal" class="modal-overlay" @click="showProcessModal = false">
        <div class="modal-content" @click.stop>
          <div class="modal-header">
            <h3>Traiter la demande de {{ processingDemande?.agence?.nom }}</h3>
            <button @click="showProcessModal = false"><i class="pi pi-times"></i></button>
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
              <input type="number" v-model="traitement.quantite_validee" min="0" :max="processingDemande?.quantite" required>
              <small>Demandé : {{ processingDemande?.quantite }}</small>
            </div>

            <div class="form-group">
              <label>Commentaire {{ traitement.decision === 'Refuser' ? '(Obligatoire)' : '(Optionnel)' }}</label>
              <textarea v-model="traitement.observations" :required="traitement.decision === 'Refuser'" rows="3"></textarea>
            </div>

            <div class="modal-actions">
              <button type="button" class="cancel-btn" @click="showProcessModal = false">Annuler</button>
              <button type="submit" class="confirm-btn" :disabled="submittingTraitement">
                {{ submittingTraitement ? 'Traitement...' : 'Confirmer' }}
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- Modal de modification (Gestionnaire) -->
      <div v-if="showEditModal" class="modal-overlay" @click="showEditModal = false">
        <div class="modal-content" @click.stop>
          <div class="modal-header">
            <h3>Modifier la demande #{{ editingDemande?.id }}</h3>
            <button @click="showEditModal = false"><i class="pi pi-times"></i></button>
          </div>
          
          <form @submit.prevent="submitEdit" class="process-form">
            <div class="form-group">
              <label>Quantité</label>
              <input type="number" v-model="editForm.quantite" min="1" required>
            </div>

            <div class="form-group">
              <label>Urgence</label>
              <select v-model="editForm.urgence" required>
                <option value="Basse">Basse</option>
                <option value="Moyenne">Moyenne</option>
                <option value="Haute">Haute</option>
              </select>
            </div>

            <div class="form-group">
              <label>Date souhaitée</label>
              <input type="date" v-model="editForm.date_souhaitee" required>
            </div>

            <div class="form-group">
              <label>Statut</label>
              <select v-model="editForm.statut" required>
                <option value="en attente">En attente</option>
                <option value="approuvé">Approuvé</option>
                <option value="rejeté">Rejeté</option>
              </select>
            </div>

            <div class="form-group">
              <label>Motif</label>
              <textarea v-model="editForm.motif" rows="3" required></textarea>
            </div>

            <div class="modal-actions">
              <button type="button" class="cancel-btn" @click="showEditModal = false">Annuler</button>
              <button type="submit" class="confirm-btn" :disabled="submittingEdit">
                {{ submittingEdit ? 'Enregistrement...' : 'Enregistrer' }}
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- Modal de confirmation de suppression -->
      <div v-if="showDeleteModal" class="modal-overlay" @click="showDeleteModal = false">
        <div class="modal-content" @click.stop>
          <div class="modal-header">
            <h3>Confirmer la suppression</h3>
            <button @click="showDeleteModal = false"><i class="pi pi-times"></i></button>
          </div>
          <div class="modal-body">
            <p>Êtes-vous sûr de vouloir supprimer cette demande ? Cette action est irréversible.</p>
            <div class="demande-summary" v-if="deletingDemande">
              <strong>Agence:</strong> {{ deletingDemande.agence?.nom }}<br>
              <strong>Matériel:</strong> {{ deletingDemande.equipement?.nom }}
            </div>
          </div>
          <div class="modal-actions">
            <button class="cancel-btn" @click="showDeleteModal = false">Annuler</button>
            <button class="delete-confirm-btn" @click="executeDelete" :disabled="submittingDelete">
              {{ submittingDelete ? 'Suppression...' : 'Supprimer définitivement' }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </AgenceLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useAuthStore } from '@/stores/authStore'
import { useToast } from 'primevue/usetoast'
import AgenceLayout from '@/layouts/AgenceLayout.vue'
import demandeAgenceApi from '@/api/demandeAgenceApi'

const authStore = useAuthStore()
const toast = useToast()
const demandes = ref([])
const loading = ref(true)
const selectedDemande = ref(null)

// États pour le traitement (Gestionnaire)
const showProcessModal = ref(false)
const processingDemande = ref(null)
const submittingTraitement = ref(false)
const traitement = ref({
  decision: 'Approuver',
  quantite_validee: 0,
  observations: ''
})

// États pour Edition
const showEditModal = ref(false)
const editingDemande = ref(null)
const submittingEdit = ref(false)
const editForm = ref({
  quantite: 1,
  urgence: 'Basse',
  date_souhaitee: '',
  statut: 'en attente',
  motif: ''
})

// États pour Suppression
const showDeleteModal = ref(false)
const deletingDemande = ref(null)
const submittingDelete = ref(false)

const fetchDemandes = async () => {
  loading.value = true
  try {
    const { data } = await demandeAgenceApi.index()
    demandes.value = data
  } catch (error) {
    console.error('Erreur chargement demandes', error)
    toast.add({ severity: 'error', summary: 'Erreur', detail: 'Impossible de charger les demandes', life: 3000 })
  } finally {
    loading.value = false
  }
}

const formatDate = (dateString) => {
  if (!dateString) return '-'
  return new Date(dateString).toLocaleDateString('fr-FR')
}

const statusClass = (status) => {
  switch (status) {
    case 'en attente': return 'pending'
    case 'approuvé': return 'approved'
    case 'rejeté': return 'rejected'
    default: return ''
  }
}

const showDetails = (demande) => {
  selectedDemande.value = demande
}

// Logique de traitement
const openProcessModal = (demande) => {
  processingDemande.value = demande
  traitement.value = {
    decision: 'Approuver',
    quantite_validee: demande.quantite,
    observations: ''
  }
  showProcessModal.value = true
}

const submitTraitement = async () => {
  submittingTraitement.value = true
  try {
    await demandeAgenceApi.traiter(processingDemande.value.id, traitement.value)
    toast.add({ severity: 'success', summary: 'Succès', detail: 'Demande traitée avec succès', life: 3000 })
    showProcessModal.value = false
    fetchDemandes()
  } catch (error) {
    toast.add({ severity: 'error', summary: 'Erreur', detail: 'Échec du traitement', life: 3000 })
  } finally {
    submittingTraitement.value = false
  }
}

// Méthodes CRUD
const openEditModal = (demande) => {
  editingDemande.value = demande
  editForm.value = {
    quantite: demande.quantite,
    urgence: demande.urgence,
    date_souhaitee: demande.date_souhaitee.split('T')[0],
    statut: demande.statut,
    motif: demande.motif
  }
  showEditModal.value = true
}

const submitEdit = async () => {
  submittingEdit.value = true
  try {
    await demandeAgenceApi.update(editingDemande.value.id, editForm.value)
    toast.add({ severity: 'success', summary: 'Succès', detail: 'Demande mise à jour', life: 3000 })
    showEditModal.value = false
    fetchDemandes()
  } catch (error) {
    toast.add({ severity: 'error', summary: 'Erreur', detail: 'Échec de la mise à jour', life: 3000 })
  } finally {
    submittingEdit.value = false
  }
}

const confirmDelete = (demande) => {
  deletingDemande.value = demande
  showDeleteModal.value = true
}

const executeDelete = async () => {
  submittingDelete.value = true
  try {
    await demandeAgenceApi.destroy(deletingDemande.value.id)
    toast.add({ severity: 'success', summary: 'Succès', detail: 'Demande supprimée', life: 3000 })
    showDeleteModal.value = false
    fetchDemandes()
  } catch (error) {
    toast.add({ severity: 'error', summary: 'Erreur', detail: 'Échec de la suppression', life: 3000 })
  } finally {
    submittingDelete.value = false
  }
}

onMounted(fetchDemandes)
</script>

<style scoped>
.demandes-container {
  padding: 24px;
}

.header-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 32px;
}

.header-bar h2 {
  color: #f8fafc;
  margin: 0;
}

.header-bar p {
  color: #94a3b8;
  margin: 4px 0 0 0;
}

.add-btn {
  background: #3b82f6;
  color: white;
  text-decoration: none;
  padding: 10px 20px;
  border-radius: 8px;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 8px;
  transition: background 0.2s;
}

.add-btn:hover {
  background: #2563eb;
}

.table-wrapper {
  background: #1e293b;
  border: 1px solid #334155;
  border-radius: 12px;
  overflow: hidden;
}

.demandes-table {
  width: 100%;
  border-collapse: collapse;
  text-align: left;
}

.demandes-table th {
  background: #0f172a;
  padding: 16px;
  color: #94a3b8;
  font-size: 0.85rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.demandes-table td {
  padding: 16px;
  border-top: 1px solid #334155;
  color: #e2e8f0;
}

.agence-name {
  color: #3b82f6;
  font-weight: 600;
}

.equip-info {
  display: flex;
  flex-direction: column;
}

.equip-info .name {
  font-weight: 600;
}

.equip-info .detail {
  font-size: 0.8rem;
  color: #94a3b8;
}

.badge-urgence {
  padding: 4px 10px;
  border-radius: 9999px;
  font-size: 0.75rem;
  font-weight: 600;
}

.badge-urgence.basse { background: rgba(16, 185, 129, 0.1); color: #10b981; }
.badge-urgence.moyenne { background: rgba(245, 158, 11, 0.1); color: #f59e0b; }
.badge-urgence.haute { background: rgba(239, 68, 68, 0.1); color: #ef4444; }

.badge-statut {
  padding: 4px 10px;
  border-radius: 6px;
  font-size: 0.8rem;
  font-weight: 600;
  text-transform: capitalize;
}

.badge-statut.pending { background: #334155; color: #94a3b8; }
.badge-statut.approved { background: rgba(16, 185, 129, 0.2); color: #10b981; }
.badge-statut.rejected { background: rgba(239, 68, 68, 0.2); color: #ef4444; }

.actions-cell {
  display: flex;
  gap: 12px;
  align-items: center;
}

.view-btn {
  background: none;
  border: none;
  color: #3b82f6;
  cursor: pointer;
  font-size: 1.1rem;
  padding: 4px;
}

.edit-btn {
  background: none;
  border: none;
  color: #f59e0b;
  cursor: pointer;
  font-size: 1.1rem;
  padding: 4px;
}

.delete-btn {
  background: none;
  border: none;
  color: #ef4444;
  cursor: pointer;
  font-size: 1.1rem;
  padding: 4px;
}

.delete-confirm-btn {
  background: #ef4444;
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 8px;
  cursor: pointer;
}

.demande-summary {
  background: #0f172a;
  padding: 12px;
  border-radius: 8px;
  margin-top: 12px;
  color: #e2e8f0;
  line-height: 1.6;
}

.process-btn-table {
  background: #10b981;
  color: white;
  border: none;
  padding: 6px 12px;
  border-radius: 6px;
  cursor: pointer;
  font-size: 0.85rem;
  display: flex;
  align-items: center;
  gap: 6px;
}

.process-btn-table:hover {
  background: #059669;
}

.loading-state, .empty-state {
  text-align: center;
  padding: 64px;
  color: #94a3b8;
}

.empty-state i {
  font-size: 3rem;
  margin-bottom: 16px;
}

.link {
  color: #3b82f6;
  text-decoration: none;
}

/* Modal Styles */
.modal-overlay {
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

.modal-content {
  background: #1e293b;
  border: 1px solid #334155;
  border-radius: 12px;
  width: 90%;
  max-width: 500px;
  padding: 24px;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

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

.detail-row {
  margin-bottom: 16px;
}

.detail-row .label {
  display: block;
  color: #94a3b8;
  font-size: 0.85rem;
  margin-bottom: 4px;
}

.detail-row .value {
  color: #e2e8f0;
  margin: 0;
  line-height: 1.5;
}
</style>
