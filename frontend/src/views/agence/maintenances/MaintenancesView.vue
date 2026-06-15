<template>
  <!-- Layout de l'agence (menu + en-tête) -->
  <AgenceLayout>
    <!-- Conteneur principal de la page -->
    <div class="maintenances-container">
      <!-- Barre d'en-tête avec titre et bouton d'ajout -->
      <div class="header-bar">
        <div class="header-left">
          <h2>Gestion des Maintenances</h2>
          <p>Suivez les maintenances préventives et correctives</p>
        </div>
        <div class="header-right">
          <!-- Bouton pour ajouter une nouvelle maintenance : ouvre le modal -->
          <button class="add-btn" @click="openAddModal">
            <i class="pi pi-plus"></i> Nouvelle Maintenance
          </button>
        </div>
      </div>

      <!-- Carte des filtres : recherche + filtre par statut + filtre par type -->
      <div class="filters-card">
        <div class="filters-row">
          <!-- Barre de recherche : filtre par nom d'équipement ou diagnostic -->
          <div class="search-box">
            <i class="pi pi-search"></i>
            <input v-model="search" type="text" placeholder="Rechercher une maintenance...">
          </div>
          <!-- Filtre par statut de la maintenance -->
          <div class="select-box">
            <select v-model="filters.statut">
              <option value="">Tous les statuts</option>
              <option value="planifiee">Planifiée</option>
              <option value="en_cours">En cours</option>
              <option value="terminee">Terminée</option>
            </select>
          </div>
          <!-- Filtre par type de maintenance (préventive ou corrective) -->
          <div class="select-box">
            <select v-model="filters.type_maintenance">
              <option value="">Tous les types</option>
              <option value="preventive">Préventive</option>
              <option value="corrective">Corrective</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Carte du tableau des maintenances -->
      <div class="table-card">
        <!-- État de chargement : affiche un spinner pendant le chargement des données -->
        <div v-if="loading" class="loading-state">
          <i class="pi pi-spin pi-spinner"></i> Chargement...
        </div>
        <!-- État vide : si aucune maintenance n'est trouvée -->
        <div v-else-if="maintenances.length === 0" class="empty-state">
          <i class="pi pi-info-circle"></i>
          <p>Aucune maintenance trouvée.</p>
        </div>
        <!-- Sinon, affiche le tableau des maintenances filtrées -->
        <div v-else class="table-wrapper">
          <table class="data-table">
            <thead>
              <tr>
                <th>Date Prévue</th>
                <th>Équipement</th>
                <th>Type</th>
                <th>Statut</th>
                <th>Technicien</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <!-- Pour chaque maintenance filtrée, on affiche une ligne -->
              <tr v-for="m in filteredMaintenances" :key="m.id">
                <td>{{ formatDate(m.date_prevue) }}</td>
                <td>{{ m.equipement?.nom }} ({{ m.equipement?.reference }})</td>
                <td><span class="type-badge" :class="m.type_maintenance">{{ formatType(m.type_maintenance) }}</span></td>
                <td><span class="status-badge" :class="m.statut">{{ formatStatus(m.statut) }}</span></td>
                <td>{{ m.technicienUser?.name || '-' }}</td>
                <td>
                  <div class="actions">
                    <button class="detail-btn" @click="showDetail(m)">Détails</button>
                    <!-- Bouton "Démarrer" : seulement si la maintenance est planifiée -->
                    <button v-if="m.statut === 'planifiee'" class="start-btn" @click="startMaintenance(m)">Démarrer</button>
                    <!-- Bouton "Terminer" : seulement si la maintenance est en cours -->
                    <button v-if="m.statut === 'en_cours'" class="complete-btn" @click="openCompleteModal(m)">Terminer</button>
                    <button class="edit-btn" @click="openEditModal(m)">Modifier</button>
                    <button class="delete-btn" @click="confirmDelete(m)">Supprimer</button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Modal d'ajout ou de modification d'une maintenance -->
      <Dialog v-model:visible="showModal" :header="isEdit ? 'Modifier la Maintenance' : 'Nouvelle Maintenance'"
        :style="{ width: '600px' }" modal class="p-fluid dark-modal">
        <form @submit.prevent="submitMaintenance" class="maintenance-form">
          <div class="field mb-4">
            <label class="font-bold block mb-2">Équipement</label>
            <select v-model="maintenanceForm.equipement_id" class="w-full" required>
              <option value="">Sélectionner un équipement</option>
              <option v-for="eq in equipements" :key="eq.id" :value="eq.id">{{ eq.nom }} ({{ eq.reference }})</option>
            </select>
          </div>
          <div class="field mb-4">
            <label class="font-bold block mb-2">Type Maintenance</label>
            <select v-model="maintenanceForm.type_maintenance" class="w-full" required>
              <option value="preventive">Préventive</option>
              <option value="corrective">Corrective</option>
            </select>
          </div>
          <div class="field mb-4">
            <label class="font-bold block mb-2">Date Prévue</label>
            <input v-model="maintenanceForm.date_prevue" type="date" class="w-full" required />
          </div>
          <!-- Champ "Panne associée" : seulement si le type est "corrective" -->
          <div v-if="maintenanceForm.type_maintenance === 'corrective'" class="field mb-4">
            <label class="font-bold block mb-2">Panne associée</label>
            <select v-model="maintenanceForm.panne_id" class="w-full">
              <option value="">Aucune</option>
              <option v-for="p in pannes" :key="p.id" :value="p.id">{{ formatDate(p.date_declaration) }} - {{ p.description }}</option>
            </select>
          </div>
          <div class="field mb-4">
            <label class="font-bold block mb-2">Technicien</label>
            <select v-model="maintenanceForm.technicien_id" class="w-full">
              <option value="">Sélectionner un technicien</option>
              <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }}</option>
            </select>
          </div>
          <div class="field mb-4">
            <label class="font-bold block mb-2">Diagnostic</label>
            <textarea v-model="maintenanceForm.diagnostic" rows="3" class="w-full"></textarea>
          </div>
          <div class="field mb-4">
            <label class="font-bold block mb-2">Coût</label>
            <input v-model="maintenanceForm.cout" type="number" step="0.01" class="w-full" />
          </div>
          <!-- Champ "Observations" : seulement si on est en mode édition et la maintenance est terminée -->
          <div v-if="isEdit && maintenanceForm.statut === 'terminee'" class="field mb-4">
            <label class="font-bold block mb-2">Résultat / Observations</label>
            <textarea v-model="maintenanceForm.observations" rows="3" class="w-full"></textarea>
          </div>
          <div class="modal-footer">
            <Button label="Annuler" class="p-button-secondary" @click="showModal = false" />
            <Button label="Enregistrer" type="submit" :loading="submitting" class="p-button-primary" />
          </div>
        </form>
      </Dialog>

      <!-- Modal pour terminer une maintenance (quand elle est en cours) -->
      <Dialog v-model:visible="showCompleteModal" header="Terminer la Maintenance" :style="{ width: '500px' }" modal
        class="p-fluid dark-modal">
        <form @submit.prevent="submitComplete" class="maintenance-form">
          <div class="field mb-4">
            <label class="font-bold block mb-2">Date Fin</label>
            <input v-model="completeForm.date_fin" type="date" class="w-full" required />
          </div>
          <div class="field mb-4">
            <label class="font-bold block mb-2">Diagnostic Final</label>
            <textarea v-model="completeForm.diagnostic" rows="3" class="w-full"></textarea>
          </div>
          <div class="field mb-4">
            <label class="font-bold block mb-2">Coût Final</label>
            <input v-model="completeForm.cout" type="number" step="0.01" class="w-full" />
          </div>
          <div class="field mb-4">
            <label class="font-bold block mb-2">Observations / Résultat</label>
            <textarea v-model="completeForm.observations" rows="3" class="w-full"></textarea>
          </div>
          <div class="modal-footer">
            <Button label="Annuler" class="p-button-secondary" @click="showCompleteModal = false" />
            <Button label="Terminer" type="submit" :loading="submitting" class="p-button-primary" />
          </div>
        </form>
      </Dialog>

      <!-- Modal pour afficher les détails d'une maintenance -->
      <Dialog v-model:visible="showDetailModal" header="Détails de la Maintenance" :style="{ width: '600px' }" modal
        class="p-fluid dark-modal">
        <div v-if="selectedMaintenance" class="detail-content">
          <div class="detail-row"><span class="label">Équipement:</span> <span class="value">{{
              selectedMaintenance.equipement?.nom }} ({{ selectedMaintenance.equipement?.reference }})</span></div>
          <div class="detail-row"><span class="label">Type:</span> <span class="value"><span class="type-badge"
                :class="selectedMaintenance.type_maintenance">{{ formatType(selectedMaintenance.type_maintenance) }}</span></span></div>
          <div class="detail-row"><span class="label">Statut:</span> <span class="value"><span class="status-badge"
                :class="selectedMaintenance.statut">{{ formatStatus(selectedMaintenance.statut) }}</span></span></div>
          <div class="detail-row"><span class="label">Date Prévue:</span> <span class="value">{{
              formatDate(selectedMaintenance.date_prevue) }}</span></div>
          <div v-if="selectedMaintenance.date_debut" class="detail-row"><span class="label">Date Début:</span> <span class="value">{{
              formatDate(selectedMaintenance.date_debut) }}</span></div>
          <div v-if="selectedMaintenance.date_fin" class="detail-row"><span class="label">Date Fin:</span> <span class="value">{{
              formatDate(selectedMaintenance.date_fin) }}</span></div>
          <div v-if="selectedMaintenance.technicienUser" class="detail-row"><span class="label">Technicien:</span> <span class="value">{{
              selectedMaintenance.technicienUser.name }}</span></div>
          <div v-if="selectedMaintenance.panne" class="detail-row"><span class="label">Panne associée:</span> <span class="value">{{
              formatDate(selectedMaintenance.panne.date_declaration) }} - {{ selectedMaintenance.panne.description }}</span></div>
          <div v-if="selectedMaintenance.diagnostic" class="detail-row mt-4"><span class="label">Diagnostic:</span></div>
          <div v-if="selectedMaintenance.diagnostic" class="detail-text">{{ selectedMaintenance.diagnostic }}</div>
          <div v-if="selectedMaintenance.cout" class="detail-row"><span class="label">Coût:</span> <span class="value">{{
              selectedMaintenance.cout }} €</span></div>
          <div v-if="selectedMaintenance.observations" class="detail-row mt-4"><span class="label">Observations / Résultat:</span></div>
          <div v-if="selectedMaintenance.observations" class="detail-text">{{ selectedMaintenance.observations }}</div>
        </div>
        <div class="modal-footer">
          <Button label="Fermer" class="p-button-secondary" @click="showDetailModal = false" />
        </div>
      </Dialog>
    </div>
  </AgenceLayout>
</template>

<script setup>
// =============================================
// IMPORTS : Ce qu'on a besoin pour la page
// =============================================
import { ref, computed, onMounted } from 'vue'
import AgenceLayout from '@/layouts/AgenceLayout.vue'
// Les stores pour gérer les données (maintenances, équipements, pannes, utilisateurs)
import { useMaintenanceStore } from '@/stores/maintenanceStore.js'
import { useEquipementStore } from '@/stores/equipementStore.js'
import { usePanneStore } from '@/stores/panneStore.js'
import { useUserStore } from '@/stores/userStore.js'
// Les composants PrimeVue pour le modal et les boutons
import Dialog from 'primevue/dialog'
import Button from 'primevue/button'
// Les services pour les notifications et la confirmation
import { useToast } from 'primevue/usetoast'
import { useConfirm } from 'primevue/useconfirm'

// =============================================
// INITIALISATION DES STORES ET SERVICES
// =============================================
const maintenanceStore = useMaintenanceStore() // Pour les maintenances
const equipementStore = useEquipementStore() // Pour les équipements
const panneStore = usePanneStore() // Pour les pannes
const userStore = useUserStore() // Pour les utilisateurs (techniciens)
const toast = useToast() // Pour les notifications toast (petits messages en haut à droite)
const confirm = useConfirm() // Pour la confirmation de suppression

// =============================================
// VARIABLES RÉACTIVES (ref) : Elles changent et la page se met à jour
// =============================================
const maintenances = ref([]) // Tableau de toutes les maintenances
const equipements = ref([]) // Tableau de tous les équipements
const pannes = ref([]) // Tableau de toutes les pannes
const users = ref([]) // Tableau de tous les utilisateurs (techniciens)
const loading = ref(false) // true pendant le chargement des données
const submitting = ref(false) // true pendant l'enregistrement d'une maintenance
const search = ref('') // Texte de la barre de recherche
const filters = ref({ statut: '', type_maintenance: '' }) // Filtres sélectionnés
// Visibilité des modals
const showModal = ref(false) // Modal d'ajout/modification
const showCompleteModal = ref(false) // Modal de terminaison
const showDetailModal = ref(false) // Modal de détails
const isEdit = ref(false) // true si on est en mode modification, false si ajout
const selectedMaintenance = ref(null) // Maintenance sélectionnée pour les détails ou la terminaison
const maintenanceForm = ref({}) // Données du formulaire d'ajout/modification
const completeForm = ref({}) // Données du formulaire de terminaison

// =============================================
// PROPRIÉTÉS CALCULÉES (computed) : Elles se calculent automatiquement
// =============================================
// Filtre les maintenances selon la recherche et les filtres sélectionnés
const filteredMaintenances = computed(() => {
  return maintenances.value.filter(m => {
    // Filtre par recherche : vérifie le nom de l'équipement ou le diagnostic
    const matchesSearch = !search.value ||
      (m.equipement?.nom?.toLowerCase().includes(search.value.toLowerCase()) ||
        m.diagnostic?.toLowerCase().includes(search.value.toLowerCase()))
    // Filtre par statut
    const matchesStatut = !filters.value.statut || m.statut === filters.value.statut
    // Filtre par type de maintenance
    const matchesType = !filters.value.type_maintenance || m.type_maintenance === filters.value.type_maintenance
    // Retourne true seulement si tous les filtres correspondent
    return matchesSearch && matchesStatut && matchesType
  })
})

// =============================================
// FONCTIONS UTILITAIRES : Formattage des données
// =============================================
// Transforme le code du statut en texte français (ex: "planifiee" → "Planifiée")
const formatStatus = (statut) => {
  const statusMap = {
    'planifiee': 'Planifiée',
    'en_cours': 'En cours',
    'terminee': 'Terminée'
  }
  return statusMap[statut] || statut
}

// Transforme le code du type en texte français (ex: "preventive" → "Préventive")
const formatType = (type) => {
  const typeMap = {
    'preventive': 'Préventive',
    'corrective': 'Corrective'
  }
  return typeMap[type] || type
}

// Formate la date en format français (JJ/MM/AAAA)
const formatDate = (date) => {
  if (!date) return ''
  return new Date(date).toLocaleDateString('fr-FR')
}

// =============================================
// FONCTIONS DE RÉCUPÉRATION DES DONNÉES
// =============================================
// Récupère toutes les données nécessaires depuis les stores (maintenances, équipements, pannes, utilisateurs)
const fetchData = async () => {
  loading.value = true // Active l'état de chargement
  try {
    // Récupère toutes les données en parallèle (plus rapide)
    await Promise.all([
      maintenanceStore.fetchMaintenancesByPeriod(),
      equipementStore.fetchEquipements(),
      panneStore.fetchPannes(),
      userStore.fetchUsers()
    ])
    // Met à jour nos variables réactives avec les données des stores
    maintenances.value = maintenanceStore.maintenances
    equipements.value = equipementStore.equipements
    pannes.value = panneStore.pannes
    users.value = userStore.users
  } catch (err) {
    console.error(err) // Affiche l'erreur dans la console si ça échoue
  } finally {
    loading.value = false // Désactive l'état de chargement, peu importe le résultat
  }
}

// =============================================
// FONCTIONS D'OUVERTURE DES MODALS
// =============================================
// Ouvre le modal pour ajouter une nouvelle maintenance
const openAddModal = () => {
  isEdit.value = false // On est pas en mode édition
  // Initialise le formulaire avec des valeurs par défaut
  maintenanceForm.value = { 
    equipement_id: '', 
    type_maintenance: 'preventive', // Par défaut : préventive
    date_prevue: new Date().toISOString().split('T')[0], // Par défaut : aujourd'hui
    panne_id: '',
    technicien_id: '',
    diagnostic: '',
    cout: '',
    observations: ''
  }
  showModal.value = true // Affiche le modal
}

// Ouvre le modal pour modifier une maintenance existante
const openEditModal = (maintenance) => {
  isEdit.value = true // On est en mode édition
  // Copie les données de la maintenance dans le formulaire
  maintenanceForm.value = { 
    ...maintenance, 
    equipement_id: maintenance.equipement_id,
    panne_id: maintenance.panne_id || '',
    technicien_id: maintenance.technicien_id || ''
  }
  showModal.value = true // Affiche le modal
}

// Ouvre le modal pour terminer une maintenance (seulement si elle est en cours)
const openCompleteModal = (maintenance) => {
  selectedMaintenance.value = maintenance // Sauvegarde la maintenance sélectionnée
  // Initialise le formulaire de terminaison avec les données de la maintenance
  completeForm.value = {
    diagnostic: maintenance.diagnostic || '',
    cout: maintenance.cout || '',
    observations: maintenance.observations || '',
    date_fin: new Date().toISOString().split('T')[0] // Par défaut : aujourd'hui
  }
  showCompleteModal.value = true // Affiche le modal
}

// Ouvre le modal pour afficher les détails d'une maintenance
const showDetail = (maintenance) => {
  selectedMaintenance.value = maintenance // Sauvegarde la maintenance sélectionnée
  showDetailModal.value = true // Affiche le modal
}

// =============================================
// FONCTIONS DE SOUMISSION DES FORMULAIRES
// =============================================
// Enregistre la maintenance (ajout ou modification)
const submitMaintenance = async () => {
  submitting.value = true // Active l'état de soumission (désactive le bouton)
  try {
    if (isEdit.value) {
      // Si on est en mode édition : on met à jour la maintenance existante
      await maintenanceStore.updateMaintenance(maintenanceForm.value.id, maintenanceForm.value)
    } else {
      // Sinon : on crée une nouvelle maintenance
      await maintenanceStore.createMaintenance(maintenanceForm.value)
    }
    // Affiche une notification de succès
    toast.add({ severity: 'success', summary: 'Succès', detail: 'Maintenance enregistrée', life: 3000 })
    showModal.value = false // Ferme le modal
    await fetchData() // Actualise la liste des maintenances
  } catch (err) {
    // Si ça échoue : affiche une notification d'erreur
    toast.add({ severity: 'error', summary: 'Erreur', detail: 'Échec de l\'enregistrement', life: 3000 })
  } finally {
    submitting.value = false // Désactive l'état de soumission
  }
}

// Démarre une maintenance (change son statut de "planifiée" à "en cours")
const startMaintenance = async (maintenance) => {
  try {
    await maintenanceStore.startMaintenance(maintenance.id, { technicien_id: maintenance.technicien_id })
    toast.add({ severity: 'success', summary: 'Succès', detail: 'Maintenance démarrée', life: 3000 })
    await fetchData() // Actualise la liste
  } catch (err) {
    toast.add({ severity: 'error', summary: 'Erreur', detail: 'Échec du démarrage', life: 3000 })
  }
}

// Termine une maintenance (change son statut à "terminée")
const submitComplete = async () => {
  submitting.value = true
  try {
    await maintenanceStore.completeMaintenance(selectedMaintenance.value.id, completeForm.value)
    toast.add({ severity: 'success', summary: 'Succès', detail: 'Maintenance terminée', life: 3000 })
    showCompleteModal.value = false // Ferme le modal
    await fetchData() // Actualise la liste
  } catch (err) {
    toast.add({ severity: 'error', summary: 'Erreur', detail: 'Échec de la terminaison', life: 3000 })
  } finally {
    submitting.value = false
  }
}

// =============================================
// FONCTION DE SUPPRESSION
// =============================================
// Demande une confirmation avant de supprimer une maintenance
const confirmDelete = (maintenance) => {
  confirm.require({
    message: 'Êtes-vous sûr de vouloir supprimer cette maintenance ?',
    header: 'Confirmation de suppression',
    icon: 'pi pi-exclamation-triangle',
    accept: async () => {
      // Si l'utilisateur clique sur "Accepter" : on supprime
      try {
        await maintenanceStore.deleteMaintenance(maintenance.id)
        toast.add({ severity: 'success', summary: 'Succès', detail: 'Maintenance supprimée', life: 3000 })
        await fetchData() // Actualise la liste
      } catch (err) {
        toast.add({ severity: 'error', summary: 'Erreur', detail: 'Échec de la suppression', life: 3000 })
      }
    }
  })
}

// =============================================
// CYCLE DE VIE : Ce qui se passe quand la page se charge
// =============================================
// OnMounted : s'exécute immédiatement après que la page soit affichée
onMounted(fetchData)
</script>

<style scoped>
.maintenances-container {
  padding: 24px;
  color: #f8fafc;
}

.header-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
}

.header-bar h2 {
  margin: 0;
  font-size: 1.5rem;
}

.header-bar p {
  color: #94a3b8;
  margin: 4px 0 0 0;
}

.add-btn {
  background: #3b82f6;
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 8px;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 8px;
  font-weight: 600;
}

.add-btn:hover {
  background: #2563eb;
}

.filters-card {
  background: #1e293b;
  border: 1px solid #334155;
  padding: 16px;
  border-radius: 12px;
  margin-bottom: 20px;
}

.filters-row {
  display: flex;
  gap: 16px;
  flex-wrap: wrap;
}

.search-box {
  position: relative;
  flex: 1;
  min-width: 200px;
}

.search-box i {
  position: absolute;
  left: 12px;
  top: 50%;
  transform: translateY(-50%);
  color: #94a3b8;
}

.search-box input,
select {
  background: #0f172a;
  border: 1px solid #334155;
  color: #f8fafc;
  padding: 10px 12px 10px 40px;
  border-radius: 8px;
  width: 100%;
}

select {
  padding-left: 12px;
  width: 180px;
}

.table-card {
  background: #1e293b;
  border: 1px solid #334155;
  border-radius: 12px;
  overflow: hidden;
}

.data-table {
  width: 100%;
  border-collapse: collapse;
}

.data-table th {
  background: #0f172a;
  padding: 14px 16px;
  text-align: left;
  color: #94a3b8;
  font-size: 0.875rem;
  font-weight: 600;
  text-transform: uppercase;
}

.data-table td {
  padding: 14px 16px;
  border-bottom: 1px solid #334155;
}

.type-badge,
.status-badge {
  padding: 4px 10px;
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: 700;
}

.type-badge.preventive {
  background: rgba(16, 185, 129, 0.15);
  color: #10b981;
}

.type-badge.corrective {
  background: rgba(245, 158, 11, 0.15);
  color: #f59e0b;
}

.status-badge.planifiee {
  background: rgba(107, 114, 128, 0.15);
  color: #94a3b8;
}

.status-badge.en_cours {
  background: rgba(59, 130, 246, 0.15);
  color: #3b82f6;
}

.status-badge.terminee {
  background: rgba(16, 185, 129, 0.15);
  color: #10b981;
}

.actions {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
}

.detail-btn,
.edit-btn {
  background: #334155;
  color: white;
  border: none;
  padding: 6px 12px;
  border-radius: 6px;
  cursor: pointer;
}

.start-btn {
  background: #3b82f6;
  color: white;
  border: none;
  padding: 6px 12px;
  border-radius: 6px;
  cursor: pointer;
}

.complete-btn {
  background: #10b981;
  color: white;
  border: none;
  padding: 6px 12px;
  border-radius: 6px;
  cursor: pointer;
}

.delete-btn {
  background: #ef4444;
  color: white;
  border: none;
  padding: 6px 12px;
  border-radius: 6px;
  cursor: pointer;
}

.loading-state,
.empty-state {
  padding: 60px;
  text-align: center;
  color: #94a3b8;
}

.loading-state i {
  font-size: 2rem;
  margin-bottom: 12px;
  color: #3b82f6;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 8px;
  margin-top: 16px;
}

.maintenance-form select,
.maintenance-form textarea,
.maintenance-form input {
  background: #0f172a;
  border: 1px solid #334155;
  color: #f8fafc;
  padding: 8px;
  border-radius: 6px;
  width: 100%;
}

.detail-content {
  padding: 8px 0;
}

.detail-row {
  display: flex;
  gap: 8px;
  margin-bottom: 12px;
}

.detail-row .label {
  font-weight: 600;
  color: #94a3b8;
  min-width: 140px;
}

.detail-row .value {
  color: #e2e8f0;
}

.detail-text {
  padding: 12px;
  background: #0f172a;
  border-radius: 8px;
  color: #e2e8f0;
  line-height: 1.5;
}

.mt-4 {
  margin-top: 16px;
}

:deep(.dark-modal) .p-dialog-content,
:deep(.dark-modal) .p-dialog-header {
  background: #1e293b;
  color: #f8fafc;
  border-color: #334155;
}
</style>
