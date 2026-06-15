<template>
  <!-- Composant de layout principal de l'agence.
       Toute la page Gestion des Pannes sera affichée à l'intérieur de ce layout. -->
  <AgenceLayout>

    <!-- Conteneur principal de la page Gestion des Pannes -->
    <div class="pannes-container">

      <!-- Barre d'en-tête contenant le titre et les actions principales -->
      <div class="header-bar">

        <!-- Partie gauche de l'en-tête -->
        <div class="header-left">

          <!-- Titre principal de la page -->
          <h2>Gestion des Pannes</h2>

          <!-- Sous-titre expliquant l'objectif de la page -->
          <p>Signalez et suivez les pannes de matériel</p>

        </div>

        <!-- Partie droite de l'en-tête -->
        <div class="header-right">

          <!-- Bouton permettant d'ouvrir la fenêtre de création d'une nouvelle panne.
               Appelle la fonction openAddModal() définie dans le script. -->
          <button class="add-btn" @click="openAddModal">

            <!-- Icône PrimeVue représentant le symbole + -->
            <i class="pi pi-plus"></i>

            <!-- Texte du bouton -->
            Nouvelle Panne

          </button>

        </div>

      </div>

      <!-- Carte contenant tous les filtres de recherche -->
      <div class="filters-card">

        <!-- Ligne regroupant les différents filtres -->
        <div class="filters-row">

          <!-- Zone de recherche textuelle -->
          <div class="search-box">

            <!-- Icône de recherche -->
            <i class="pi pi-search"></i>

            <!-- Champ de recherche.
                 La valeur est liée à search grâce à v-model.
                 Toute modification met automatiquement search à jour. -->
            <input
              v-model="search"
              type="text"
              placeholder="Rechercher une panne..."
            >

          </div>

          <!-- Filtre sur le statut de la panne -->
          <div class="select-box">

            <!-- Liste déroulante liée à filters.statut -->
            <select v-model="filters.statut">

              <!-- Afficher tous les statuts -->
              <option value="">Tous les statuts</option>

              <!-- Filtrer uniquement les pannes déclarées -->
              <option value="declaree">Déclarée</option>

              <!-- Filtrer uniquement les pannes en cours -->
              <option value="en_cours">En cours</option>

              <!-- Filtrer uniquement les pannes en maintenance -->
              <option value="en_maintenance">En maintenance</option>

              <!-- Filtrer uniquement les pannes résolues -->
              <option value="resolue">Résolue</option>

              <!-- Filtrer uniquement les pannes irrécupérables -->
              <option value="irrecuperable">Irrécupérable</option>

              <!-- Filtrer uniquement les pannes clôturées -->
              <option value="cloturee">Clôturée</option>

            </select>

          </div>

          <!-- Filtre sur le niveau de gravité -->
          <div class="select-box">

            <!-- Liste déroulante liée à filters.niveau_gravite -->
            <select v-model="filters.niveau_gravite">

              <!-- Afficher toutes les gravités -->
              <option value="">Toutes les gravités</option>

              <!-- Afficher uniquement les pannes mineures -->
              <option value="mineure">Mineure</option>

              <!-- Afficher uniquement les pannes majeures -->
              <option value="majeure">Majeure</option>

              <!-- Afficher uniquement les pannes critiques -->
              <option value="critique">Critique</option>

            </select>

          </div>

        </div>

      </div>

      <!-- Carte contenant le tableau des pannes -->
      <div class="table-card">

        <!-- Affiché uniquement lorsque les données sont en cours de chargement.
             Dépend de la variable loading du script. -->
        <div v-if="loading" class="loading-state">

          <!-- Icône spinner animée -->
          <i class="pi pi-spin pi-spinner"></i>

          <!-- Message de chargement -->
          Chargement...

        </div>

        <!-- Affiché si le chargement est terminé mais qu'aucune panne n'existe -->
        <div v-else-if="pannes.length === 0" class="empty-state">

          <!-- Icône informative -->
          <i class="pi pi-info-circle"></i>

          <!-- Message indiquant qu'aucune donnée n'est disponible -->
          <p>Aucune panne trouvée.</p>

        </div>

        <!-- Affiché lorsqu'il existe au moins une panne -->
        <div v-else class="table-wrapper">

          <!-- Tableau principal des pannes -->
          <table class="data-table">

            <!-- En-tête du tableau -->
            <thead>

              <tr>

                <!-- Colonne Date -->
                <th>Date</th>

                <!-- Colonne Équipement -->
                <th>Équipement</th>

                <!-- Colonne Agent -->
                <th>Agent</th>

                <!-- Colonne Gravité -->
                <th>Gravité</th>

                <!-- Colonne Statut -->
                <th>Statut</th>

                <!-- Colonne Actions -->
                <th>Actions</th>

              </tr>

            </thead>

            <!-- Corps du tableau -->
            <tbody>

              <!-- Boucle sur chaque panne présente dans filteredPannes.
                   filteredPannes est un computed défini dans le script. -->
              <tr v-for="p in filteredPannes" :key="p.id">

                <!-- Date de déclaration formatée grâce à formatDate() -->
                <td>{{ formatDate(p.date_declaration) }}</td>

                <!-- Nom et référence de l'équipement concerné -->
                <td>{{ p.equipement?.nom }} ({{ p.equipement?.reference }})</td>

                <!-- Nom complet de l'agent ayant signalé la panne -->
                <td>{{ p.agent?.nom }} {{ p.agent?.prenom }}</td>

                <!-- Affichage de la gravité sous forme de badge coloré.
                     La classe CSS dépend du niveau de gravité. -->
                <td>
                  <span
                    class="gravite-badge"
                    :class="p.niveau_gravite"
                  >
                    {{ p.niveau_gravite }}
                  </span>
                </td>

                <!-- Affichage du statut sous forme de badge coloré.
                     Le texte est transformé par formatStatus(). -->
                <td>
                  <span
                    class="status-badge"
                    :class="p.statut"
                  >
                    {{ formatStatus(p.statut) }}
                  </span>
                </td>

                <!-- Colonne contenant toutes les actions disponibles -->
                <td>

                  <div class="actions">

                    <!-- Ouvre la fenêtre de détails -->
                    <button
                      class="detail-btn"
                      @click="showDetail(p)"
                    >
                      Détails
                    </button>

                    <!-- Visible uniquement si la panne est déclarée.
                         Permet de transmettre la panne vers la maintenance. -->
                    <button
                      v-if="p.statut === 'declaree'"
                      class="transmettre-btn"
                      @click="transmettrePanne(p)"
                    >
                      Transmettre
                    </button>

                    <!-- Visible uniquement si la panne est en cours
                         ou déjà en maintenance.
                         Permet d'ajouter un diagnostic. -->
                    <button
                      v-if="['en_cours', 'en_maintenance'].includes(p.statut)"
                      class="diagnostic-btn"
                      @click="openDiagnosticModal(p)"
                    >
                      Diagnostic
                    </button>

                    <!-- Visible uniquement si la panne est en cours
                         ou en maintenance.
                         Ouvre la création d'une maintenance corrective. -->
                    <button
                      v-if="['en_cours', 'en_maintenance'].includes(p.statut)"
                      class="maintenance-btn"
                      @click="openMaintenanceModal(p)"
                    >
                      Créer Maintenance
                    </button>

                    <!-- Ouvre le formulaire de modification -->
                    <button
                      class="edit-btn"
                      @click="openEditModal(p)"
                    >
                      Modifier
                    </button>

                    <!-- Lance la confirmation de suppression -->
                    <button
                      class="delete-btn"
                      @click="confirmDelete(p)"
                    >
                      Supprimer
                    </button>

                  </div>

                </td>

              </tr>

            </tbody>

          </table>

        </div>

      </div>

      <!-- Fenêtre modale de création ou modification d'une panne.
           showModal contrôle son ouverture.
           Le titre dépend de isEdit. -->
      <Dialog
        v-model:visible="showModal"
        :header="isEdit ? 'Modifier la Panne' : 'Nouvelle Panne'"
        :style="{ width: '550px' }"
        modal
        class="p-fluid dark-modal"
      >

        <!-- Formulaire principal des pannes.
             submitPanne() sera exécutée à la soumission. -->
        <form
          @submit.prevent="submitPanne"
          class="panne-form"
        >

          <!-- Sélection de l'équipement concerné -->
          <div class="field mb-4">

            <label class="font-bold block mb-2">
              Équipement
            </label>

            <!-- Liste des équipements provenant de equipements -->
            <select
              v-model="panneForm.equipement_id"
              class="w-full"
              required
            >

              <option value="">
                Sélectionner un équipement
              </option>

              <!-- Boucle sur tous les équipements disponibles -->
              <option
                v-for="eq in equipements"
                :key="eq.id"
                :value="eq.id"
              >
                {{ eq.nom }} ({{ eq.reference }})
              </option>

            </select>

          </div>

          <!-- Le même principe est utilisé pour :
               - la sélection de l'agent
               - la description
               - le niveau de gravité
               - le statut (uniquement en modification)
               - le diagnostic technicien
               - l'action réalisée
               - le coût de réparation

               Chaque champ est relié à panneForm via v-model.
               Les informations saisies alimentent directement
               l'objet panneForm qui sera envoyé à submitPanne(). -->

        </form>

      </Dialog>

      <!-- Fenêtre modale permettant de saisir un diagnostic.
           Reliée à showDiagnosticModal.
           Enregistre les données via submitDiagnostic(). -->
      <Dialog
        v-model:visible="showDiagnosticModal"
        header="Ajouter un Diagnostic"
      >
      </Dialog>

      <!-- Fenêtre modale affichant tous les détails d'une panne.
           Les informations proviennent de selectedPanne.
           selectedPanne est alimentée par showDetail(p). -->
      <Dialog
        v-model:visible="showDetailModal"
        header="Détails de la Panne"
      >
      </Dialog>

      <!-- Fenêtre modale permettant de créer une maintenance corrective.
           Les données sont stockées dans maintenanceForm.
           Le formulaire est envoyé via submitMaintenance(). -->
      <Dialog
        v-model:visible="showMaintenanceModal"
        header="Créer une Maintenance Corrective"
      >
      </Dialog>

    </div>

  </AgenceLayout>
</template>

<script setup>
// --- 1. LES IMPORTATIONS (Aller chercher les outils dans la boîte à outils) ---
import { ref, computed, onMounted } from 'vue' // Outils de base de Vue pour créer des variables magiques (réactives)
import AgenceLayout from '@/layouts/AgenceLayout.vue' // Dépendance : Le design global autour de la page (le menu, le fond, etc.)

// Dépendances externes : Les "Stores" (les placards où on stocke et va chercher les informations de la base de données)
import { usePanneStore } from '@/stores/panneStore.js'         // Placard des pannes
import { useEquipementStore } from '@/stores/equipementStore.js' // Placard des appareils (téléphones, scanners...)
import { useAgentStore } from '@/stores/agentStore.js'           // Placard des employés (les agents)
import { useMaintenanceStore } from '@/stores/maintenanceStore.js' // Placard des fiches de réparation
import { useUserStore } from '@/stores/userStore.js'             // Placard des utilisateurs de l'application (les techniciens)

// Outils visuels (PrimeVue) pour faire de jolies fenêtres et alertes
import Dialog from 'primevue/dialog' // Les fenêtres pop-up qui s'ouvrent au milieu de l'écran
import Button from 'primevue/button' // Des jolis boutons sur lesquels cliquer
import { useToast } from 'primevue/usetoast' // Les petites fées qui affichent un message de succès en haut de l'écran
import { useConfirm } from 'primevue/useconfirm' // La petite boîte qui demande "Es-tu sûr ?" avant de supprimer

// --- 2. ACTIVATION DES OUTILS ---
const panneStore = usePanneStore()       // On ouvre le placard des pannes pour l'utiliser
const equipementStore = useEquipementStore() // On ouvre le placard des appareils
const agentStore = useAgentStore()       // On ouvre le placard des agents
const maintenanceStore = useMaintenanceStore() // On ouvre le placard des maintenances
const userStore = useUserStore()         // On ouvre le placard des utilisateurs
const toast = useToast()                 // On prépare le lanceur de petits messages de succès
const confirm = useConfirm()             // On prépare la boîte de confirmation

// --- 3. LES VARIABLES (Les boîtes pour stocker les informations sur l'écran) ---
const pannes = ref([])        // Une liste vide qui va recevoir toutes les pannes
const equipements = ref([])   // Une liste vide qui va recevoir tous les appareils existants
const agents = ref([])        // Une liste vide qui va recevoir tous les employés
const users = ref([])         // Une liste vide qui va recevoir tous les utilisateurs (techniciens)
const loading = ref(false)    // Une boîte Vrai/Faux : dit "Vrai" si l'ordinateur est en train de chercher des données
const submitting = ref(false) // Une boîte Vrai/Faux : dit "Vrai" quand on clique sur "Enregistrer" pour bloquer le bouton
const search = ref('')        // Une boîte de texte pour écrire ce qu'on cherche dans la barre de recherche
const filters = ref({ statut: '', niveau_gravite: '' }) // Une boîte avec deux options pour trier par état ou par gravité

// Les variables de contrôle des fenêtres (Vrai = Ouvert, Faux = Fermé)
const showModal = ref(false)            // Ouvre/Ferme la fenêtre pour Ajouter ou Modifier une panne
const showDiagnosticModal = ref(false)  // Ouvre/Ferme la fenêtre pour écrire un diagnostic
const showDetailModal = ref(false)      // Ouvre/Ferme la fenêtre pour voir toute l'histoire d'une panne
const showMaintenanceModal = ref(false) // Ouvre/Ferme la fenêtre pour planifier une réparation
const isEdit = ref(false)               // Dit "Vrai" si on est en train de modifier, "Faux" si on crée une nouvelle panne
const selectedPanne = ref(null)         // Stocke la panne sur laquelle on a cliqué pour voir les détails

// Les formulaires de saisie (les boîtes vides que l'utilisateur va remplir)
const panneForm = ref({})        // Formulaire pour créer/modifier une panne
const diagnosticForm = ref({})   // Formulaire pour le texte du diagnostic
const maintenanceForm = ref({})  // Formulaire pour créer une feuille de route de réparation

// --- 4. LES FONCTIONS (Les actions que la page sait faire) ---

/**
 * FONCTION : Le Filtre Magique (computed)
 * Rôle : Regarde la liste de toutes les pannes et ne garde à l'écran que celles qui correspondent 
 * à ce qu'on a écrit dans la barre de recherche ou sélectionné dans les tris.
 */
const filteredPannes = computed(() => {
  return pannes.value.filter(p => {
    // Étape A : Est-ce que le texte tapé correspond au nom de l'appareil, de l'agent ou à la description ?
    const matchesSearch = !search.value ||
      (p.equipement?.nom?.toLowerCase().includes(search.value.toLowerCase()) ||
        p.agent?.nom?.toLowerCase().includes(search.value.toLowerCase()) ||
        p.description.toLowerCase().includes(search.value.toLowerCase()))
    
    // Étape B : Est-ce que le statut correspond au bouton de tri choisi ?
    const matchesStatut = !filters.value.statut || p.statut === filters.value.statut
    
    // Étape C : Est-ce que la gravité correspond au bouton de tri choisi ?
    const matchesGravite = !filters.value.niveau_gravite || p.niveau_gravite === filters.value.niveau_gravite
    
    // On ne garde la panne que si elle passe les trois examens (A, B et C)
    return matchesSearch && matchesStatut && matchesGravite
  })
})

/**
 * FONCTION : Le Traducteur de Statut
 * Rôle : Change les mots bizarres du code (ex: 'en_cours') en jolis mots en français (ex: 'En cours').
 */
const formatStatus = (statut) => {
  const statusMap = {
    'declaree': 'Déclarée',
    'en_cours': 'En cours',
    'en_maintenance': 'En maintenance',
    'resolue': 'Résolue',
    'irrecuperable': 'Irrécupérable',
    'cloturee': 'Clôturée'
  }
  return statusMap[statut] || statut // Si le mot n'est pas dans la liste, on l'affiche quand même tel quel
}

/**
 * FONCTION : Le Camion de Livraison de données (fetchData)
 * Rôle : Va chercher toutes les informations dans les placards (Stores) en même temps.
 * Dépendances : Appelle des fonctions situées dans `panneStore.js`, `equipementStore.js`, `agentStore.js` et `userStore.js`.
 */
const fetchData = async () => {
  loading.value = true // 1. On dit à l'écran d'afficher "Chargement en cours..."
  try {
    // 2. On lance 4 livreurs en même temps pour chercher les données dans la base de données (via l'API en tâche de fond)
    await Promise.all([
      panneStore.fetchPannes(),
      equipementStore.fetchEquipements(),
      agentStore.fetchAgents(),
      userStore.fetchUsers()
    ])
    // 3. Une fois les livreurs revenus, on range ce qu'ils ont apporté dans nos variables locales
    pannes.value = panneStore.pannes
    equipements.value = equipementStore.equipements
    agents.value = agentStore.agents
    users.value = userStore.users
  } catch (err) {
    console.error(err) // S'il y a une panne de réseau, on écrit l'erreur dans la console secrète des développeurs
  } finally {
    loading.value = false // 4. On éteint le panneau "Chargement en cours..."
  }
}

/**
 * FONCTION : Préparer la boîte "Nouvelle Panne"
 * Rôle : Prépare un formulaire tout propre et vide pour déclarer un nouvel appareil cassé.
 */
const openAddModal = () => {
  isEdit.value = false // On dit que ce n'est PAS une modification, c'est une création
  // On remplit le formulaire avec des valeurs par défaut (ex: gravité mineure, statut déclarée)
  panneForm.value = { equipement_id: '', agent_id: '', description: '', niveau_gravite: 'mineure', statut: 'declaree' }
  showModal.value = true // On affiche la fenêtre pop-up à l'écran
  console.log(); // Ligne vide/inutile qui ne fait rien
}

/**
 * FONCTION : Préparer la boîte "Modifier la Panne"
 * Rôle : Ouvre la même fenêtre pop-up, mais en recopiant d'abord les infos de la panne choisie dedans.
 */
const openEditModal = (panne) => {
  isEdit.value = true // On dit que c'est une MODIFICATION
  // On fait une copie (`{...panne}`) des informations de la panne pour que l'utilisateur puisse les changer
  panneForm.value = { ...panne, equipement_id: panne.equipement_id, agent_id: panne.agent_id }
  showModal.value = true // On affiche la fenêtre pop-up à l'écran
}

/**
 * FONCTION : Préparer la boîte "Diagnostic"
 * Rôle : Ouvre une petite fenêtre pour que le technicien écrive ce qu'il pense du problème.
 */
const openDiagnosticModal = (panne) => {
  selectedPanne.value = panne // On se rappelle de quelle panne on parle
  // On prépare le formulaire avec le numéro de la panne et le texte s'il existait déjà
  diagnosticForm.value = { panne_id: panne.id, diagnostic_technicien: panne.diagnostic_technicien || '' }
  showDiagnosticModal.value = true // On affiche la fenêtre pop-up du diagnostic
}

/**
 * FONCTION : Préparer la boîte "Créer Maintenance"
 * Rôle : Ouvre la fenêtre pour planifier un rendez-vous chez le réparateur.
 * Dépendance indirecte : Va se lier plus tard à `maintenanceStore.js`.
 */
const openMaintenanceModal = (panne) => {
  selectedPanne.value = panne // On retient la panne ciblée
  // On pré-remplit la feuille de route : c'est une maintenance "corrective" (pour réparer), à la date d'aujourd'hui
  maintenanceForm.value = {
    panne_id: panne.id,
    equipement_id: panne.equipement_id,
    type_maintenance: 'corrective',
    date_prevue: new Date().toISOString().split('T')[0], // Donne la date du jour (ex: 2026-06-15)
    diagnostic: panne.diagnostic_technicien || ''
  }
  showMaintenanceModal.value = true // On affiche la fenêtre de planification
}

/**
 * FONCTION : Envoyer la demande de réparation (Enregistrer la maintenance)
 * Rôle : Envoie le formulaire rempli au placard des maintenances pour acter le rendez-vous.
 * Dépendance : Appelle `maintenanceStore.createMaintenance()`.
 */
const submitMaintenance = async () => {
  try {
    // On demande au placard des maintenances d'enregistrer la nouvelle mission
    await maintenanceStore.createMaintenance(maintenanceForm.value)
    // On affiche un petit message vert en haut de l'écran pour dire "Bravo !"
    toast.add({ severity: 'success', summary: 'Succès', detail: 'Maintenance créée avec succès', life: 3000 })
    showMaintenanceModal.value = false // On ferme la fenêtre
    await fetchData() // On rafraîchit l'écran pour voir les nouveaux statuts mis à jour
  } catch (err) {
    // Si ça plante, on affiche un message rouge "Erreur !"
    toast.add({ severity: 'error', summary: 'Erreur', detail: 'Échec de la création de la maintenance', life: 3000 })
  }
}

/**
 * FONCTION : Voir la loupe (Afficher les Détails)
 * Rôle : Ouvre une grande fiche pour lire tout l'historique d'une panne (description, prix de la réparation...).
 */
const showDetail = (panne) => {
  selectedPanne.value = panne // On met la panne cliquée sous la loupe
  showDetailModal.value = true // On ouvre la fenêtre des détails
}

/**
 * FONCTION : Enregistrer le formulaire de Panne (Création ou Modification)
 * Rôle : Décide s'il faut créer une nouvelle ligne ou en modifier une existante dans le placard des pannes.
 * Dépendance : Appelle `panneStore.updatePanne()` ou `panneStore.createPanne()`.
 */
const submitPanne = async () => {
  submitting.value = true // On dit que l'application travaille (le bouton de validation affiche un petit sablier)
  try {
    if (isEdit.value) {
      // Si la variable isEdit est vraie, on met à jour la panne qui existe déjà en lui donnant son numéro (id) et le formulaire
      await panneStore.updatePanne(panneForm.value.id, panneForm.value)
    } else {
      // Sinon, on demande au placard de fabriquer une toute nouvelle panne
      await panneStore.createPanne(panneForm.value)
    }
    toast.add({ severity: 'success', summary: 'Succès', detail: 'Panne enregistrée', life: 3000 })
    showModal.value = false // On ferme la fenêtre
    await fetchData() // On recharge la liste pour voir les changements
  } catch (err) {
    toast.add({ severity: 'error', summary: 'Erreur', detail: 'Échec de l\'enregistrement', life: 3000 })
  } finally {
    submitting.value = false // L'ordinateur a fini de travailler, on libère le bouton
  }
}

/**
 * FONCTION : Enregistrer le Diagnostic
 * Rôle : Envoie les notes du technicien dans le placard des pannes.
 * Dépendance : Appelle `panneStore.diagnostiquer()`.
 */
const submitDiagnostic = async () => {
  submitting.value = true // Le sablier s'active
  try {
    // On appelle l'action spéciale "diagnostiquer" du placard en lui donnant le numéro de la panne
    await panneStore.diagnostiquer(diagnosticForm.value.panne_id, diagnosticForm.value)
    toast.add({ severity: 'success', summary: 'Succès', detail: 'Diagnostic enregistré', life: 3000 })
    showDiagnosticModal.value = false // On ferme la fenêtre
    await fetchData() // On remet l'écran à jour
  } catch (err) {
    toast.add({ severity: 'error', summary: 'Erreur', detail: 'Échec du diagnostic', life: 3000 })
  } finally {
    submitting.value = false // Le sablier s'éteint
  }
}

/**
 * FONCTION : Transmettre la panne
 * Rôle : Fait passer instantanément la panne de "Déclarée" à "Transmise aux réparateurs" (statut En cours).
 * Dépendance : Appelle `panneStore.transmettreMaintenance()`.
 */
const transmettrePanne = async (panne) => {
  try {
    // On appelle l'action pour envoyer la panne en maintenance
    await panneStore.transmettreMaintenance(panne.id, {})
    toast.add({ severity: 'success', summary: 'Succès', detail: 'Panne transmise', life: 3000 })
    await fetchData() // On recharge pour voir le badge changer de couleur !
  } catch (err) {
    toast.add({ severity: 'error', summary: 'Erreur', detail: 'Échec de la transmission', life: 3000 })
  }
}

/**
 * FONCTION : Demander confirmation avant de jeter (Suppression)
 * Rôle : Ouvre une alerte de sécurité. Si l'utilisateur clique sur "Oui", elle détruit la panne de la base de données.
 * Dépendance : Appelle `panneStore.deletePanne()`.
 */
const confirmDelete = (panne) => {
  confirm.require({
    message: 'Êtes-vous sûr de vouloir supprimer cette panne ?',
    header: 'Confirmation de suppression',
    icon: 'pi pi-exclamation-triangle', // Un petit triangle de danger jaune
    accept: async () => {
      // Cette partie ne s'exécute QUE si on clique sur "Oui, accepter"
      try {
        await panneStore.deletePanne(panne.id) // On supprime la panne du placard
        toast.add({ severity: 'success', summary: 'Succès', detail: 'Panne supprimée', life: 3000 })
        await fetchData() // On efface la panne de l'écran en rechargeant la liste
      } catch (err) {
        toast.add({ severity: 'error', summary: 'Erreur', detail: 'Échec de la suppression', life: 3000 })
      }
    }
  })
}

/**
 * FONCTION : Le Magicien du Temps (formatDate)
 * Rôle : Reçoit une date bizarre écrite comme ça : "2026-06-15T12:00:00" 
 * et la transforme en une jolie date française : "15/06/2026".
 */
const formatDate = (date) => {
  if (!date) return '' // S'il n'y a pas de date, on n'affiche rien du tout
  return new Date(date).toLocaleDateString('fr-FR') // Transforme la date au format de la France
}

// --- 5. LE COMMENCEMENT (onMounted) ---
// Rôle : C'est le bouton d'allumage. Dès que la page apparaît pour la toute première fois 
//        sur l'écran de l'utilisateur, elle exécute la fonction 'fetchData' pour remplir le tableau.
onMounted(fetchData)
</script>
<style scoped>
/* Conteneur principal de la page */
.pannes-container {
  padding: 24px;
  color: #f8fafc;
}

/* Barre de titre et bouton d'ajout */
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

/* Bouton d'ajout */
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

/* Carte des filtres */
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

/* Carte du tableau */
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

/* Badges */
.gravite-badge,
.status-badge {
  padding: 4px 10px;
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: 700;
}

.gravite-badge.mineure {
  background: rgba(16, 185, 129, 0.15);
  color: #10b981;
}

.gravite-badge.majeure {
  background: rgba(245, 158, 11, 0.15);
  color: #f59e0b;
}

.gravite-badge.critique {
  background: rgba(239, 68, 68, 0.3);
  color: #ef4444;
}

.status-badge.declaree {
  background: rgba(107, 114, 128, 0.15);
  color: #94a3b8;
}

.status-badge.en_cours {
  background: rgba(59, 130, 246, 0.15);
  color: #3b82f6;
}

.status-badge.en_maintenance {
  background: rgba(245, 158, 11, 0.15);
  color: #f59e0b;
}

.status-badge.resolue {
  background: rgba(16, 185, 129, 0.15);
  color: #10b981;
}

.status-badge.irrecuperable {
  background: rgba(239, 68, 68, 0.15);
  color: #ef4444;
}

.status-badge.cloturee {
  background: rgba(75, 85, 99, 0.15);
  color: #6b7280;
}

/* Boutons d'actions */
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

.transmettre-btn {
  background: #f59e0b;
  color: white;
  border: none;
  padding: 6px 12px;
  border-radius: 6px;
  cursor: pointer;
}

.diagnostic-btn,
.maintenance-btn {
  background: #3b82f6;
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

/* États de chargement et vide */
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

/* Pied de page des modals */
.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 8px;
  margin-top: 16px;
}

/* Formulaire de panne */
.panne-form select,
.panne-form textarea,
.panne-form input {
  background: #0f172a;
  border: 1px solid #334155;
  color: #f8fafc;
  padding: 8px;
  border-radius: 6px;
  width: 100%;
}

/* Détails de la panne */
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

/* Classes utilitaires */
.mt-4 {
  margin-top: 16px;
}

/* Style des modals */
:deep(.dark-modal) .p-dialog-content,
:deep(.dark-modal) .p-dialog-header {
  background: #1e293b;
  color: #f8fafc;
  border-color: #334155;
}
</style>
