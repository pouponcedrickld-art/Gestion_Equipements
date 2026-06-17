<template>
  <AgenceLayout>
    <div class="reception-view" ref="pageContainer">

      <!-- ════ En-tête ════════════════════════════════════════════════════════ -->
      <div class="page-header animate-in">
        <div class="header-left">
          <div class="title-with-icon">
            <div class="icon-wrapper">
              <i class="pi pi-download"></i>
            </div>
            <div>
              <h1>Réceptions de Matériel</h1>
              <p class="subtitle">Acceptez ou refusez les équipements envoyés par le Siège</p>
            </div>
          </div>
        </div>
      </div>

      <!-- ════ Filtres & Recherche ══════════════════════════════════════════════ -->
      <div class="filters-bar animate-in">
        <div class="search-box">
          <i class="pi pi-search"></i>
          <InputText
            v-model="searchQuery"
            placeholder="Rechercher par équipement, référence..."
            class="search-input"
          />
        </div>
        <div class="dropdown-filters">
          <Dropdown
            v-model="selectedStatut"
            :options="statutOptions"
            optionLabel="label"
            optionValue="value"
            placeholder="Statut"
            class="modern-dropdown"
            showClear
          />
        </div>
      </div>

      <!-- ════ Tableau des transferts ══════════════════════════════════════════ -->
      <div class="table-container animate-in" v-if="!loading">
        <DataTable
          :value="filteredTransferts"
          responsiveLayout="stack"
          breakpoint="960px"
          stripedRows
          class="professional-table"
          :paginator="true"
          :rows="10"
          emptyMessage="Aucun transfert en attente de réception"
        >
          <!-- ID -->
          <Column field="id" header="ID" sortable>
            <template #body="{ data }">
              <span class="trans-id">#{{ data.id }}</span>
            </template>
          </Column>

          <!-- Équipement -->
          <Column header="Équipement" sortable sortField="equipement.nom">
            <template #body="{ data }">
              <div class="equipement-cell">
                <span class="equip-name">
                  {{ data.equipement?.nom || `${data.equipement?.marque} ${data.equipement?.modele}` }}
                </span>
                <small class="equip-sn">SN: {{ data.equipement?.numero_serie || 'N/A' }}</small>
              </div>
            </template>
          </Column>

          <!-- Source -->
          <Column header="Source" sortable sortField="agenceSource.nom">
            <template #body="{ data }">
              <span class="agence-cell source">{{ data.agence_source?.nom || 'Siège' }}</span>
            </template>
          </Column>

          <!-- Date expédition -->
          <Column field="date_expedition" header="Expédié le" sortable>
            <template #body="{ data }">
              {{ formatDate(data.date_expedition) }}
            </template>
          </Column>

          <!-- Statut -->
          <Column field="statut" header="Statut" sortable>
            <template #body="{ data }">
              <Tag :value="statutLabel(data.statut)" :severity="getStatutSeverity(data.statut)" />
            </template>
          </Column>

          <!-- ── Actions : Accepter / Refuser ── -->
          <Column header="Actions" style="min-width: 200px">
            <template #body="{ data }">
              <div class="actions-cell">

                <!--
                  BOUTON ACCEPTER
                  Visible si le transfert est approuvé ('approuve') OU en transit ('expedie').
                  Au clic → dialog de confirmation → traiterReception({ statut: 'accepte' })
                  Le backend incrémente alors le stock de l'agence destination.
                -->
                <Button
                  v-if="['approuve', 'expedie'].includes(data.statut)"
                  id="btn-accepter"
                  label="Accepter"
                  icon="pi pi-check"
                  class="p-button-success p-button-sm btn-action"
                  :loading="submitting === data.id && decisionEnCours === 'accepte'"
                  :disabled="submitting !== null"
                  @click="ouvrirConfirmationAcceptation(data)"
                  title="Accepter et ajouter au stock de l'agence"
                />

                <!--
                  BOUTON REFUSER
                  Visible si le transfert est approuvé ('approuve') OU en transit ('expedie').
                  Au clic → dialog de saisie du motif → traiterReception({ statut: 'refuse', motif_refus })
                  Le backend marque l'équipement en 'en_retour' (visible dans le menu Retours).
                -->
                <Button
                  v-if="['approuve', 'expedie'].includes(data.statut)"
                  id="btn-refuser"
                  label="Refuser"
                  icon="pi pi-times"
                  class="p-button-danger p-button-sm btn-action"
                  :loading="submitting === data.id && decisionEnCours === 'refuse'"
                  :disabled="submitting !== null"
                  @click="ouvrirRefusDialog(data)"
                  title="Refuser et signaler un problème"
                />

                <!-- Badge état final : affiché une fois la décision prise -->
                <span v-if="data.statut === 'recu'" class="badge-final success">
                  <i class="pi pi-check-circle"></i> Accepté
                </span>
                <span v-if="data.statut === 'refuse'" class="badge-final danger">
                  <i class="pi pi-times-circle"></i> Refusé
                </span>

              </div>
            </template>
          </Column>

        </DataTable>
      </div>

      <!-- Skeleton chargement -->
      <div class="table-container skeleton" v-else>
        <div v-for="n in 5" :key="n" class="skeleton-row"></div>
      </div>


      <!-- ════ Dialog Confirmation Acceptation ════════════════════════════════ -->
      <!--
        L'utilisateur clique "Accepter" → on lui demande de confirmer avant d'appeler
        traiterReception({ statut: 'accepte' }).
      -->
      <Dialog
        v-model:visible="acceptationDialogVisible"
        header="Confirmer l'acceptation"
        :modal="true"
        :style="{ width: '420px' }"
      >
        <div class="confirmation-content">
          <i class="pi pi-check-circle dialog-icon success-icon"></i>
          <div>
            <p class="dialog-title">Accepter cet équipement ?</p>
            <p class="dialog-subtitle">
              L'équipement
              <strong>{{ transfertSelectionne?.equipement?.nom }}</strong>
              sera ajouté au stock de votre agence.
            </p>
          </div>
        </div>
        <template #footer>
          <Button label="Annuler" icon="pi pi-times" class="p-button-text" @click="fermerAcceptationDialog" />
          <Button
            label="Confirmer l'acceptation"
            icon="pi pi-check"
            class="p-button-success"
            :loading="submittingDecision"
            @click="confirmerAcceptation"
          />
        </template>
      </Dialog>


      <!-- ════ Dialog Refus avec motif ════════════════════════════════════════ -->
      <!--
        L'utilisateur clique "Refuser" → on lui demande un motif avant d'appeler
        traiterReception({ statut: 'refuse', motif_refus }).
      -->
      <Dialog
        v-model:visible="refusDialogVisible"
        header="Refuser le transfert"
        :modal="true"
        :style="{ width: '460px' }"
      >
        <div class="confirmation-content">
          <i class="pi pi-exclamation-triangle dialog-icon danger-icon"></i>
          <span>Indiquez la raison du refus. L'équipement sera marqué en attente de retour.</span>
        </div>
        <div class="form-group mt-4">
          <label for="motif-refus">Motif du refus <span class="required">*</span></label>
          <Textarea
            id="motif-refus"
            v-model="motifRefus"
            rows="4"
            class="w-full"
            placeholder="Ex : Matériel endommagé, erreur de référence, mauvais équipement..."
          />
          <small class="char-count">{{ motifRefus.length }} / 1000</small>
        </div>
        <template #footer>
          <Button label="Annuler" icon="pi pi-times" class="p-button-text" @click="fermerRefusDialog" />
          <Button
            label="Confirmer le refus"
            icon="pi pi-ban"
            class="p-button-danger"
            :loading="submittingDecision"
            :disabled="!motifRefus.trim()"
            @click="confirmerRefus"
          />
        </template>
      </Dialog>

    </div>
  </AgenceLayout>
</template>

<script setup>
/**
 * ReceptionView.vue — Vue de gestion des réceptions (côté agence destination)
 *
 * Pattern MVC respecté :
 *   Vue (ReceptionView) → Store (traiterReception) → API (transfertApi.traiterReception)
 *                       → Backend (TransfertController@traiterReception)
 *
 * Flux Accepter :
 *   clic "Accepter" → dialog confirmation → traiterReception({ statut: 'accepte' })
 *   → backend : $transfert->recevoir() → stock incrémenté
 *
 * Flux Refuser :
 *   clic "Refuser" → dialog motif → traiterReception({ statut: 'refuse', motif_refus })
 *   → backend : $transfert->refuser() + équipement statut_global='en_retour'
 *              (apparaît dans le menu "Retours" de l'agence destination)
 */
import { ref, computed, onMounted } from 'vue'
import { useToast } from 'primevue/usetoast'
import { useTransfertStore } from '@/stores/transfertStore'
import { useAuthStore } from '@/stores/authStore'
import AgenceLayout from '@/layouts/AgenceLayout.vue'
import gsap from 'gsap'

// Composants PrimeVue
import Button    from 'primevue/button'
import InputText from 'primevue/inputtext'
import Dropdown  from 'primevue/dropdown'
import Tag       from 'primevue/tag'
import DataTable from 'primevue/datatable'
import Column    from 'primevue/column'
import Dialog    from 'primevue/dialog'
import Textarea  from 'primevue/textarea'

// ── Stores ─────────────────────────────────────────────────────────────────────
const toast          = useToast()
const transfertStore = useTransfertStore()
const authStore      = useAuthStore()

// ── État local ─────────────────────────────────────────────────────────────────
const loading        = ref(false)      // Chargement initial de la liste
const searchQuery    = ref('')
// Par défaut : pas de filtre statut → on voit tous les transferts à traiter (approuve + expedie)
const selectedStatut = ref(null)

/** ID du transfert en cours de traitement (pour le spinner sur le bon bouton) */
const submitting       = ref(null)
/** 'accepte' ou 'refuse' — identifie quel bouton porte le spinner */
const decisionEnCours  = ref(null)
/** Bloque les boutons de la dialog pendant l'appel API */
const submittingDecision = ref(false)

// ── Dialogs ────────────────────────────────────────────────────────────────────
const acceptationDialogVisible = ref(false)  // Dialog de confirmation Accepter
const refusDialogVisible       = ref(false)  // Dialog de saisie du motif Refuser
const motifRefus               = ref('')     // Motif saisi par l'utilisateur
const transfertSelectionne     = ref(null)   // Transfert actuellement ciblé

// ── Options filtre ──────────────────────────────────────────────────────────────
const statutOptions = [
  { label: 'Approuvés (À traiter)',  value: 'approuve' },
  { label: 'En transit (À traiter)', value: 'expedie'  },
  { label: 'Déjà acceptés',          value: 'recu'     },
  { label: 'Refusés',                value: 'refuse'   },
]

// ── Computed ────────────────────────────────────────────────────────────────────

/** Filtre : uniquement les transferts entrants du Siège vers cette agence */
const transfertsEntrants = computed(() => {
  const list = Array.isArray(transfertStore.transferts)
    ? transfertStore.transferts
    : (transfertStore.transferts?.data || [])

  return list.filter(t => {
    const isDestination = t.agence_destination_id === authStore.userAgence
    const isFromGSG =
      t.type_transfert === 'livraison_generale' ||
      t.agence_source?.type === 'generale' ||
      t.agence_source_id === 1
    return isDestination && isFromGSG
  })
})

/** Filtre combiné : recherche texte + statut sélectionné */
const filteredTransferts = computed(() => {
  let list = transfertsEntrants.value

  if (searchQuery.value) {
    const q = searchQuery.value.toLowerCase()
    list = list.filter(t =>
      t.id.toString().includes(q) ||
      t.equipement?.nom?.toLowerCase().includes(q) ||
      t.equipement?.marque?.toLowerCase().includes(q) ||
      t.equipement?.reference?.toLowerCase().includes(q)
    )
  }

  if (selectedStatut.value) {
    list = list.filter(t => t.statut === selectedStatut.value)
  }

  return list
})

// ── Utilitaires ─────────────────────────────────────────────────────────────────

const getStatutSeverity = (s) => ({
  approuve: 'warning',   // Approuvé → orange (en attente de réception agence)
  expedie:  'primary',   // En transit → bleu
  recu:     'success',   // Accepté → vert
  refuse:   'danger',    // Refusé → rouge
}[s] ?? 'secondary')

const statutLabel = (s) => ({
  approuve: 'Approuvé',
  expedie:  'En transit',
  recu:     'Accepté',
  refuse:   'Refusé',
}[s] ?? s)

const formatDate = (date) =>
  date
    ? new Date(date).toLocaleDateString('fr-FR', {
        day: '2-digit', month: '2-digit', year: 'numeric',
        hour: '2-digit', minute: '2-digit',
      })
    : 'N/A'


// ── Gestion Dialog Acceptation ──────────────────────────────────────────────────

/**
 * Ouvre la dialog de confirmation avant d'accepter un transfert.
 * @param {Object} transfert - La ligne du tableau cliquée
 */
const ouvrirConfirmationAcceptation = (transfert) => {
  transfertSelectionne.value     = transfert
  acceptationDialogVisible.value = true
}

const fermerAcceptationDialog = () => {
  acceptationDialogVisible.value = false
  transfertSelectionne.value     = null
}

/**
 * Confirme l'acceptation et appelle traiterReception({ statut: 'accepte' }).
 *
 * Ce que fait le backend (TransfertController@traiterReception) :
 *   1. Passe le transfert en statut 'recu'
 *   2. Met à jour l'agence_actuelle_id de l'équipement
 *   3. Incrémente le stock de l'agence destination (StockAgenceService)
 *   4. Crée un mouvement de traçabilité
 */
const confirmerAcceptation = async () => {
  submitting.value         = transfertSelectionne.value.id
  decisionEnCours.value    = 'accepte'
  submittingDecision.value = true

  try {
    // ── Appel au store → API → Backend ──────────────────────────────────────
    await transfertStore.traiterReception(transfertSelectionne.value.id, {
      statut: 'accepte',
    })

    toast.add({
      severity: 'success',
      summary:  'Accepté !',
      detail:   "L'équipement a été ajouté au stock de votre agence.",
      life:     4000,
    })

    fermerAcceptationDialog()
    // Rechargement pour synchroniser l'affichage avec le serveur
    await transfertStore.fetchTransferts({ direction: 'entrants', type_transfert: 'livraison_generale' })

  } catch (err) {
    toast.add({
      severity: 'error',
      summary:  'Erreur',
      detail:   transfertStore.error || "Impossible d'accepter ce transfert.",
      life:     5000,
    })
  } finally {
    submitting.value         = null
    decisionEnCours.value    = null
    submittingDecision.value = false
  }
}


// ── Gestion Dialog Refus ────────────────────────────────────────────────────────

/**
 * Ouvre la dialog de saisie du motif de refus.
 * @param {Object} transfert - La ligne du tableau cliquée
 */
const ouvrirRefusDialog = (transfert) => {
  transfertSelectionne.value = transfert
  motifRefus.value           = ''
  refusDialogVisible.value   = true
}

const fermerRefusDialog = () => {
  refusDialogVisible.value   = false
  transfertSelectionne.value = null
  motifRefus.value           = ''
}

/**
 * Confirme le refus et appelle traiterReception({ statut: 'refuse', motif_refus }).
 *
 * Ce que fait le backend (TransfertController@traiterReception) :
 *   1. Passe le transfert en statut 'refuse' avec le motif_refus
 *   2. Met l'équipement en statut 'en_retour'
 *   3. Crée un mouvement de type 'retour_refuse' pour la traçabilité
 *   → L'équipement apparaîtra dans le menu "Retours" de l'agence destination
 */
const confirmerRefus = async () => {
  if (!motifRefus.value.trim()) {
    toast.add({ severity: 'warn', summary: 'Attention', detail: 'Le motif de refus est obligatoire.', life: 3000 })
    return
  }

  submitting.value         = transfertSelectionne.value.id
  decisionEnCours.value    = 'refuse'
  submittingDecision.value = true

  try {
    // ── Appel au store → API → Backend ──────────────────────────────────────
    await transfertStore.traiterReception(transfertSelectionne.value.id, {
      statut:      'refuse',
      motif_refus: motifRefus.value.trim(),
    })

    toast.add({
      severity: 'info',
      summary:  'Refusé',
      detail:   "Le transfert a été refusé. L'équipement est en attente de retour.",
      life:     4000,
    })

    fermerRefusDialog()
    await transfertStore.fetchTransferts({ direction: 'entrants', type_transfert: 'livraison_generale' })

  } catch (err) {
    toast.add({
      severity: 'error',
      summary:  'Erreur',
      detail:   transfertStore.error || 'Impossible de refuser ce transfert.',
      life:     5000,
    })
  } finally {
    submitting.value         = null
    decisionEnCours.value    = null
    submittingDecision.value = false
  }
}


// ── Lifecycle ───────────────────────────────────────────────────────────────────
onMounted(async () => {
  loading.value = true
  // Chargement des transferts entrants du Siège vers l'agence courante
  await transfertStore.fetchTransferts({ direction: 'entrants', type_transfert: 'livraison_generale' })
  loading.value = false

  // Animation d'entrée GSAP
  gsap.from('.animate-in', { opacity: 0, y: 20, duration: 0.8, stagger: 0.2, ease: 'power3.out' })
})
</script>

<style scoped lang="scss">
.reception-view { padding: 2rem; }

/* ── En-tête ── */
.title-with-icon {
  display: flex; align-items: center; gap: 1.5rem;
  .icon-wrapper {
    width: 60px; height: 60px;
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    border-radius: 16px; display: flex; align-items: center; justify-content: center;
    color: white; box-shadow: 0 8px 16px rgba(16, 185, 129, 0.2);
    i { font-size: 1.8rem; }
  }
  h1 { font-size: 2rem; font-weight: 800; color: #1e293b; margin: 0; }
  .subtitle { color: #64748b; }
}
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }

/* ── Filtres ── */
.filters-bar {
  display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; gap: 1.5rem;
  .search-box {
    position: relative; flex: 1;
    i { position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: #94a3b8; z-index: 1; }
    :deep(.search-input) { width: 100%; padding-left: 2.5rem; border-radius: 12px; border: 1px solid #e2e8f0; }
  }
  .dropdown-filters { :deep(.modern-dropdown) { border-radius: 12px; border: 1px solid #e2e8f0; min-width: 220px; } }
}

/* ── Tableau ── */
.table-container {
  background: white; border-radius: 20px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.05);
  overflow: clip; border: 1px solid #f1f5f9;
  &.skeleton {
    padding: 1rem;
    .skeleton-row { height: 60px; background: #f8fafc; margin-bottom: 0.5rem; border-radius: 8px; animation: pulse 1.5s infinite; }
  }
}
@keyframes pulse { 0%,100% { opacity: 0.6; } 50% { opacity: 1; } }

/* ── Cellules ── */
.trans-id { font-family: monospace; font-weight: 700; color: #64748b; }
.equipement-cell {
  display: flex; flex-direction: column;
  .equip-name { font-weight: 600; color: #1e293b; }
  .equip-sn   { color: #94a3b8; font-size: 0.75rem; }
}
.agence-cell { font-weight: 600; &.source { color: #6366f1; } }

/* ── Colonne Actions ── */
.actions-cell {
  display: flex; gap: 0.6rem; align-items: center; flex-wrap: wrap;

  .btn-action {
    border-radius: 8px; font-size: 0.82rem;
    transition: transform 0.15s, box-shadow 0.15s;
    &:hover:not(:disabled) {
      transform: translateY(-1px);
      box-shadow: 0 4px 10px rgba(0,0,0,0.12);
    }
  }
}

/* Badges état final */
.badge-final {
  display: inline-flex; align-items: center; gap: 0.3rem;
  font-size: 0.82rem; font-weight: 600; border-radius: 20px; padding: 0.25rem 0.75rem;
  &.success { background: #d1fae5; color: #065f46; }
  &.danger  { background: #fee2e2; color: #991b1b; }
}

/* ── Dialogs ── */
.confirmation-content {
  display: flex; align-items: flex-start; gap: 1rem; padding: 0.5rem 0;
  .dialog-icon  { font-size: 2.5rem; flex-shrink: 0; }
  .success-icon { color: #10b981; }
  .danger-icon  { color: #ef4444; }
  .dialog-title    { font-weight: 700; font-size: 1rem; color: #1e293b; margin: 0 0 0.25rem; }
  .dialog-subtitle { color: #475569; margin: 0; font-size: 0.9rem; }
}

.form-group {
  display: flex; flex-direction: column; gap: 0.4rem;
  label {
    font-weight: 600; color: #475569; font-size: 0.9rem;
    .required { color: #ef4444; }
  }
  .char-count { color: #94a3b8; font-size: 0.75rem; text-align: right; }
}
.mt-4  { margin-top: 1rem; }
.w-full { width: 100%; }
</style>
