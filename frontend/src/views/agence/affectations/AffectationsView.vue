<template>
  <AgenceLayout>
    <div class="affectations-container">
      <div class="header-bar">
        <div class="header-left">
          <h2>Gestion des Affectations</h2>
          <p>Affectez du matériel aux agents et suivez les retours</p>
        </div>
        <div class="header-right">
          <button class="add-btn" @click="openAddModal">
            <i class="pi pi-plus"></i> Nouvelle Affectation
          </button>
        </div>
      </div>

      <!-- Filtres et Recherche -->
      <div class="filters-card">
        <div class="search-box">
          <i class="pi pi-search"></i>
          <input 
            v-model="filters.search" 
            type="text" 
            placeholder="Rechercher par agent, équipement ou référence..." 
            @input="debounceSearch"
          >
        </div>
      </div>

      <!-- Tableau des affectations -->
      <div class="table-card">
        <div v-if="loading" class="loading-state">
          <i class="pi pi-spin pi-spinner"></i> Chargement...
        </div>

        <div v-else-if="affectations.length === 0" class="empty-state">
          <i class="pi pi-info-circle"></i>
          <p>Aucune affectation trouvée.</p>
        </div>

        <div v-else class="table-wrapper">
          <table class="data-table">
            <thead>
              <tr>
                <th>Agent</th>
                <th>Équipement</th>
                <th>Date Affectation</th>
                <th>Retour Prévu</th>
                <th>Statut</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="a in affectations" :key="a.id">
                <td>
                  <div class="agent-info">
                    <span class="agent-name">{{ a.agent?.nom }} {{ a.agent?.prenom }}</span>
                    <small>{{ a.agent?.service }}</small>
                  </div>
                </td>
                <td>
                  <div class="eq-info">
                    <span class="eq-name">{{ a.equipement?.nom }}</span>
                    <small>{{ a.equipement?.reference }}</small>
                  </div>
                </td>
                <td>{{ formatDate(a.date_affectation) }}</td>
                <td>{{ a.date_retour_prevu ? formatDate(a.date_retour_prevu) : 'Non défini' }}</td>
                <td>
                  <span class="status-badge" :class="a.statut">
                    {{ formatStatut(a.statut) }}
                  </span>
                </td>
                <td>
                  <div class="actions">
                    <button v-if="a.statut === 'active'" class="return-btn" @click="openReturnModal(a)" v-tooltip.top="'Enregistrer le retour'">
                      <i class="pi pi-sign-in"></i>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Modal Nouvelle Affectation -->
      <Dialog 
        v-model:visible="showAddModal" 
        header="Nouvelle Affectation" 
        :style="{ width: '50vw' }" 
        modal
        class="p-fluid dark-modal"
      >
        <form @submit.prevent="submitAffectation" class="affectation-form">
          <div class="form-grid">
            <!-- Agent -->
            <div class="field mb-4">
              <label class="font-bold block mb-2">Agent bénéficiaire</label>
              <Dropdown 
                v-model="newAffectation.agent_id" 
                :options="agents" 
                optionLabel="label" 
                optionValue="id" 
                placeholder="Sélectionner un agent" 
                filter
                required
              />
            </div>

            <!-- Équipements (Multi-sélection) -->
            <div class="field mb-4">
              <label class="font-bold block mb-2">Équipement(s) à affecter</label>
              <MultiSelect 
                v-model="newAffectation.equipement_ids" 
                :options="equipementsDisponibles" 
                optionLabel="label" 
                optionValue="id" 
                placeholder="Sélectionner un ou plusieurs équipements" 
                filter
                required
              />
              <small class="text-gray-400">Seuls les équipements disponibles en stock agence sont affichés.</small>
            </div>

            <div class="form-row">
              <!-- État -->
              <div class="field mb-4 flex-1">
                <label class="font-bold block mb-2">État de l'équipement</label>
                <Dropdown 
                  v-model="newAffectation.etat_equipement" 
                  :options="etatOptions" 
                  optionLabel="label" 
                  optionValue="value" 
                  required
                />
              </div>

              <!-- Date Affectation -->
              <div class="field mb-4 flex-1">
                <label class="font-bold block mb-2">Date d'affectation</label>
                <Calendar v-model="newAffectation.date_affectation" dateFormat="dd/mm/yy" showIcon required />
              </div>
            </div>

            <!-- Date Retour Prévu -->
            <div class="field mb-4">
              <label class="font-bold block mb-2">Date de retour prévue (Facultatif)</label>
              <Calendar v-model="newAffectation.date_retour_prevu" dateFormat="dd/mm/yy" showIcon :minDate="newAffectation.date_affectation" />
            </div>

            <!-- Observations -->
            <div class="field">
              <label class="font-bold block mb-2">Observations (Facultatif)</label>
              <Textarea v-model="newAffectation.observations" rows="3" autoResize placeholder="Commentaires éventuels..." />
            </div>
          </div>

          <div class="modal-footer mt-4 flex justify-end gap-2">
            <Button label="Annuler" icon="pi pi-times" class="p-button-text p-button-secondary" @click="showAddModal = false" />
            <Button label="Enregistrer" icon="pi pi-check" type="submit" :loading="submitting" class="p-button-primary" />
          </div>
        </form>
      </Dialog>

      <!-- Modal Retour -->
      <Dialog 
        v-model:visible="showReturnModal" 
        header="Enregistrer un Retour" 
        :style="{ width: '400px' }" 
        modal
        class="p-fluid dark-modal"
      >
        <form @submit.prevent="submitRetour" class="return-form">
          <div class="field mb-4">
            <label class="font-bold block mb-2">Date de retour effectif</label>
            <Calendar v-model="retourForm.date_retour_effectif" dateFormat="dd/mm/yy" showIcon required />
          </div>

          <div class="field mb-4">
            <label class="font-bold block mb-2">État au retour</label>
            <Dropdown 
              v-model="retourForm.etat_retour" 
              :options="etatRetourOptions" 
              optionLabel="label" 
              optionValue="value" 
              required
            />
          </div>

          <div class="field mb-4">
            <label class="font-bold block mb-2">Observations</label>
            <Textarea v-model="retourForm.observations" rows="3" placeholder="État du matériel au retour..." />
          </div>

          <div class="modal-footer mt-4 flex justify-end gap-2">
            <Button label="Annuler" icon="pi pi-times" class="p-button-text p-button-secondary" @click="showReturnModal = false" />
            <Button label="Confirmer le retour" icon="pi pi-check" type="submit" :loading="submitting" class="p-button-success" />
          </div>
        </form>
      </Dialog>

      <!-- Section Gestionnaire Général (Traitement des demandes) -->
      <div v-if="authStore.isGestionnaireGeneral" class="section-divider"></div>
      
      <div v-if="authStore.isGestionnaireGeneral" class="section-card mt-6">
        <div class="section-header">
          <h3><i class="pi pi-clock"></i> Demandes en attente de traitement</h3>
        </div>
        
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

      <!-- Modal de traitement (Gestionnaire) -->
      <Dialog 
        v-model:visible="showProcessModal" 
        :header="'Traiter la demande #' + selectedDemande?.id" 
        :style="{ width: '450px' }" 
        modal
        class="p-fluid dark-modal"
      >
        <form @submit.prevent="submitTraitement" class="process-form">
          <div class="field mb-4">
            <label class="font-bold block mb-2">Décision</label>
            <Dropdown 
              v-model="traitement.decision" 
              :options="[
                {label: 'Approuver', value: 'Approuver'},
                {label: 'Approbation Partielle', value: 'Partiel'},
                {label: 'Refuser', value: 'Refuser'}
              ]" 
              optionLabel="label" 
              optionValue="value" 
              required
            />
          </div>

          <div class="field mb-4" v-if="traitement.decision !== 'Refuser'">
            <label class="font-bold block mb-2">Quantité validée</label>
            <InputNumber v-model="traitement.quantite_validee" :min="1" :max="selectedDemande?.equipement?.quantite" showButtons />
            <small class="text-gray-400">Demandé: {{ selectedDemande?.quantite }} | Dispo: {{ selectedDemande?.equipement?.quantite }}</small>
          </div>

          <div class="field mb-4">
            <label class="font-bold block mb-2">Commentaire {{ traitement.decision === 'Refuser' ? '(Obligatoire)' : '(Optionnel)' }}</label>
            <Textarea v-model="traitement.observations" :required="traitement.decision === 'Refuser'" rows="3" />
          </div>

          <div class="modal-footer mt-4 flex justify-end gap-2">
            <Button label="Annuler" icon="pi pi-times" class="p-button-text p-button-secondary" @click="showProcessModal = false" />
            <Button label="Confirmer" icon="pi pi-check" type="submit" :loading="processing" class="p-button-primary" />
          </div>
        </form>
      </Dialog>
    </div>
  </AgenceLayout>
</template>

<script setup>
import { ref, onMounted, reactive } from 'vue'
import { useAuthStore } from '@/stores/authStore'
import { useToast } from 'primevue/usetoast'
import AgenceLayout from '@/layouts/AgenceLayout.vue'
import affectationApi from '@/api/affectationApi'
import demandeAgenceApi from '@/api/demandeAgenceApi'

// PrimeVue Components
import Dialog from 'primevue/dialog'
import Dropdown from 'primevue/dropdown'
import MultiSelect from 'primevue/multiselect'
import Calendar from 'primevue/calendar'
import Textarea from 'primevue/textarea'
import Button from 'primevue/button'
import InputNumber from 'primevue/inputnumber'

const authStore = useAuthStore()
const toast = useToast()

// États
const affectations = ref([])
const loading = ref(false)
const submitting = ref(false)
const showAddModal = ref(false)
const showReturnModal = ref(false)
const agents = ref([])
const equipementsDisponibles = ref([])

const filters = reactive({
  search: '',
  page: 1
})

const newAffectation = ref({
  agent_id: null,
  equipement_ids: [],
  date_affectation: new Date(),
  date_retour_prevu: null,
  observations: '',
  etat_equipement: 'actif'
})

const retourForm = ref({
  id: null,
  date_retour_effectif: new Date(),
  etat_retour: 'bon',
  observations: ''
})

const etatOptions = [
  { label: 'Nouveau', value: 'nouveau' },
  { label: 'Actif / En service', value: 'actif' },
  { label: 'En maintenance', value: 'en_maintenance' },
  { label: 'Hors service', value: 'hors_service' }
]

const etatRetourOptions = [
  { label: 'Bon état', value: 'bon' },
  { label: 'Abîmé', value: 'abime' },
  { label: 'Non fonctionnel', value: 'non_fonctionnel' }
]

// Logic Gestionnaire
const demandes = ref([])
const loadingDemandes = ref(false)
const showProcessModal = ref(false)
const selectedDemande = ref(null)
const processing = ref(false)
const traitement = ref({ decision: 'Approuver', quantite_validee: 1, observations: '' })

// Méthodes
const fetchAffectations = async () => {
  loading.value = true
  try {
    const { data } = await affectationApi.index(filters)
    affectations.value = data.data || []
  } catch (error) {
    console.error(error)
  } finally {
    loading.value = false
  }
}

const fetchFormData = async () => {
  try {
    const [agentsRes, equipRes] = await Promise.all([
      affectationApi.getAgents(),
      affectationApi.getEquipementsDisponibles()
    ])
    
    agents.value = agentsRes.data.map(a => ({
      id: a.id,
      label: `${a.nom} ${a.prenom} (${a.service})`
    }))
    
    // Pour les équipements, on récupère ceux qui sont en stock agence
    const rawEq = equipRes.data.data?.data || equipRes.data.data || equipRes.data
    equipementsDisponibles.value = rawEq.map(e => ({
      id: e.id,
      label: `${e.nom} - ${e.reference} (${e.marque})`
    }))
  } catch (error) {
    console.error(error)
  }
}

const openAddModal = () => {
  newAffectation.value = {
    agent_id: null,
    equipement_ids: [],
    date_affectation: new Date(),
    date_retour_prevu: null,
    observations: '',
    etat_equipement: 'actif'
  }
  fetchFormData()
  showAddModal.value = true
}

const submitAffectation = async () => {
  submitting.value = true
  try {
    const payload = { ...newAffectation.value }
    // Formatage des dates
    payload.date_affectation = payload.date_affectation.toISOString().split('T')[0]
    if (payload.date_retour_prevu) {
      payload.date_retour_prevu = payload.date_retour_prevu.toISOString().split('T')[0]
    }

    await affectationApi.store(payload)
    toast.add({ severity: 'success', summary: 'Succès', detail: 'Affectation(s) enregistrée(s)', life: 3000 })
    showAddModal.value = false
    fetchAffectations()
  } catch (error) {
    const msg = error.response?.data?.message || 'Erreur lors de l\'affectation'
    toast.add({ severity: 'error', summary: 'Erreur', detail: msg, life: 3000 })
  } finally {
    submitting.value = false
  }
}

const openReturnModal = (affectation) => {
  retourForm.value = {
    id: affectation.id,
    date_retour_effectif: new Date(),
    etat_retour: 'bon',
    observations: ''
  }
  showReturnModal.value = true
}

const submitRetour = async () => {
  submitting.value = true
  try {
    const payload = { ...retourForm.value }
    payload.date_retour_effectif = payload.date_retour_effectif.toISOString().split('T')[0]
    
    await affectationApi.retour(payload.id, payload)
    toast.add({ severity: 'success', summary: 'Succès', detail: 'Retour enregistré avec succès', life: 3000 })
    showReturnModal.value = false
    fetchAffectations()
  } catch (error) {
    toast.add({ severity: 'error', summary: 'Erreur', detail: 'Échec du retour', life: 3000 })
  } finally {
    submitting.value = false
  }
}

// Méthodes Gestionnaire
const fetchDemandes = async () => {
  if (!authStore.isGestionnaireGeneral) return
  loadingDemandes.value = true
  try {
    const { data } = await demandeAgenceApi.index()
    demandes.value = data.filter(d => d.statut === 'en attente')
  } catch (error) {
    console.error(error)
  } finally {
    loadingDemandes.value = false
  }
}

const openProcessModal = (demande) => {
  selectedDemande.value = demande
  traitement.value = { decision: 'Approuver', quantite_validee: demande.quantite, observations: '' }
  showProcessModal.value = true
}

const submitTraitement = async () => {
  processing.value = true
  try {
    await demandeAgenceApi.traiter(selectedDemande.value.id, traitement.value)
    toast.add({ severity: 'success', summary: 'Succès', detail: 'Demande traitée', life: 3000 })
    showProcessModal.value = false
    fetchDemandes()
  } catch (error) {
    toast.add({ severity: 'error', summary: 'Erreur', detail: 'Échec du traitement', life: 3000 })
  } finally {
    processing.value = false
  }
}

const debounceTimer = ref(null)
const debounceSearch = () => {
  clearTimeout(debounceTimer.value)
  debounceTimer.value = setTimeout(() => {
    filters.page = 1
    fetchAffectations()
  }, 500)
}

const formatDate = (date) => {
  if (!date) return ''
  return new Date(date).toLocaleDateString('fr-FR')
}

const formatStatut = (statut) => {
  const map = { 'active': 'En cours', 'retournee': 'Rendu', 'expiree': 'Expiré' }
  return map[statut] || statut
}

onMounted(() => {
  fetchAffectations()
  fetchDemandes()
})
</script>

<style scoped>
.affectations-container { padding: 24px; color: #f8fafc; }
.header-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; }
.header-bar h2 { margin: 0; font-size: 1.5rem; }
.header-bar p { color: #94a3b8; margin: 4px 0 0 0; }

.add-btn {
  background: #3b82f6; color: white; border: none; padding: 10px 20px;
  border-radius: 8px; cursor: pointer; display: flex; align-items: center; gap: 8px;
  font-weight: 600; transition: background 0.2s;
}
.add-btn:hover { background: #2563eb; }

.filters-card {
  background: #1e293b; border: 1px solid #334155; padding: 16px;
  border-radius: 12px; margin-bottom: 20px;
}

.search-box { position: relative; max-width: 400px; }
.search-box i { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8; }
.search-box input {
  width: 100%; background: #0f172a; border: 1px solid #334155; color: #f8fafc;
  padding: 10px 12px 10px 40px; border-radius: 8px; outline: none;
}

.table-card { background: #1e293b; border: 1px solid #334155; border-radius: 12px; overflow: hidden; }
.data-table { width: 100%; border-collapse: collapse; }
.data-table th { background: #0f172a; padding: 14px 16px; text-align: left; color: #94a3b8; font-size: 0.85rem; font-weight: 600; text-transform: uppercase; }
.data-table td { padding: 14px 16px; border-bottom: 1px solid #334155; }

.agent-info, .eq-info { display: flex; flex-direction: column; }
.agent-name, .eq-name { font-weight: 600; color: #e2e8f0; }
.agent-info small, .eq-info small { color: #64748b; font-size: 0.8rem; }

.status-badge { padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 700; }
.status-badge.active { background: rgba(59, 130, 246, 0.15); color: #3b82f6; }
.status-badge.retournee { background: rgba(16, 185, 129, 0.15); color: #10b981; }

.return-btn { background: #334155; color: #10b981; border: none; width: 32px; height: 32px; border-radius: 6px; cursor: pointer; transition: all 0.2s; }
.return-btn:hover { background: #10b981; color: white; }

.section-divider { height: 1px; background: #334155; margin: 40px 0; }
.section-card { background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 20px; }
.section-header h3 { margin: 0; display: flex; align-items: center; gap: 10px; color: #f59e0b; }

.badge-urgence { padding: 2px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 700; }
.badge-urgence.basse { background: rgba(16, 185, 129, 0.2); color: #10b981; }
.badge-urgence.moyenne { background: rgba(245, 158, 11, 0.2); color: #f59e0b; }
.badge-urgence.haute { background: rgba(239, 68, 68, 0.2); color: #ef4444; }

.process-btn { background: #f59e0b; color: white; border: none; padding: 6px 12px; border-radius: 6px; cursor: pointer; font-weight: 600; }

.loading-state, .empty-state { padding: 60px; text-align: center; color: #94a3b8; }
.loading-state i { font-size: 2rem; margin-bottom: 12px; color: #3b82f6; }
.empty-state i { font-size: 3rem; margin-bottom: 12px; opacity: 0.2; }

:deep(.dark-modal) .p-dialog-content { background: #1e293b; color: #f8fafc; }
:deep(.dark-modal) .p-dialog-header { background: #1e293b; color: #f8fafc; border-bottom: 1px solid #334155; }
:deep(.dark-modal) .p-inputtext, :deep(.dark-modal) .p-dropdown, :deep(.dark-modal) .p-multiselect {
  background: #0f172a; border-color: #334155; color: #f8fafc;
}
</style>