<template>
  <MainLayout>
    <div class="affectations-view">
      <div class="page-header">
        <div class="header-left">
          <h1>Affectations des Équipements</h1>
          <p class="subtitle">Suivi des équipements attribués aux agents</p>
        </div>
        <div class="header-actions">
          <Button 
            label="Nouvelle Affectation" 
            icon="pi pi-plus" 
            @click="showCreateDialog = true"
          />
        </div>
      </div>

      <Card class="table-card">
        <template #content>
          <DataTable
            :value="affectationStore.affectations"
            :loading="affectationStore.loading"
            paginator
            :rows="10"
            stripedRows
            responsiveLayout="scroll"
          >
            <Column field="equipement" header="Équipement" sortable>
              <template #body="slotProps">
                <div class="flex flex-column">
                  <strong>{{ slotProps.data.equipement?.reference }}</strong>
                  <small>{{ slotProps.data.equipement?.marque }} {{ slotProps.data.equipement?.modele }}</small>
                </div>
              </template>
            </Column>
            <Column field="agent" header="Agent" sortable>
              <template #body="slotProps">
                {{ slotProps.data.agent?.nom }} {{ slotProps.data.agent?.prenom }}
              </template>
            </Column>
            <Column field="date_affectation" header="Date début" sortable>
              <template #body="slotProps">
                {{ formatDate(slotProps.data.date_affectation) }}
              </template>
            </Column>
            <Column field="date_retour_prevue" header="Date retour prévue" sortable>
              <template #body="slotProps">
                {{ formatDate(slotProps.data.date_retour_prevue) }}
              </template>
            </Column>
            <Column field="statut" header="Statut" sortable>
              <template #body="slotProps">
                <Tag 
                  :value="slotProps.data.statut === 'active' ? 'En cours' : 'Terminé'" 
                  :severity="slotProps.data.statut === 'active' ? 'success' : 'secondary'"
                />
              </template>
            </Column>
            <Column header="Actions">
              <template #body="slotProps">
                <Button 
                  v-if="slotProps.data.statut === 'active'"
                  label="Enregistrer Retour" 
                  icon="pi pi-replay" 
                  class="p-button-text p-button-warning"
                  @click="confirmerRetour(slotProps.data)"
                />
              </template>
            </Column>
          </DataTable>
        </template>
      </Card>

      <!-- Dialog pour le retour -->
      <Dialog 
        v-model:visible="showRetourDialog" 
        header="Confirmer le retour"
        :modal="true"
        :style="{ width: '450px' }"
      >
        <div v-if="selectedAffectation">
          <p>Confirmer la réception de l'équipement <strong>{{ selectedAffectation.equipement?.reference }}</strong> ?</p>
          <p class="text-secondary">L'équipement repassera en statut "En stock".</p>
        </div>
        <template #footer>
          <Button label="Annuler" icon="pi pi-times" class="p-button-text" @click="showRetourDialog = false" />
          <Button label="Confirmer" icon="pi pi-check" class="p-button-warning" @click="validerRetour" :loading="affectationStore.loading" />
        </template>
      </Dialog>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useToast } from 'primevue/usetoast'
import MainLayout from '@/layouts/MainLayout.vue'
import { useAffectationStore } from '@/stores/affectationStore'
import Button from 'primevue/button'
import Card from 'primevue/card'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Tag from 'primevue/tag'
import Dialog from 'primevue/dialog'

const affectationStore = useAffectationStore()
const toast = useToast()

const showCreateDialog = ref(false)
const showRetourDialog = ref(false)
const selectedAffectation = ref(null)

const loadAffectations = async () => {
  try {
    await affectationStore.fetchAffectations()
  } catch (error) {
    toast.add({ severity: 'error', summary: 'Erreur', detail: 'Impossible de charger les affectations' })
  }
}

const confirmerRetour = (affectation) => {
  selectedAffectation.value = affectation
  showRetourDialog.value = true
}

const validerRetour = async () => {
  try {
    await affectationStore.enregistrerRetour(selectedAffectation.value.id)
    toast.add({ severity: 'success', summary: 'Succès', detail: 'Retour enregistré' })
    showRetourDialog.value = false
  } catch (error) {
    toast.add({ severity: 'error', summary: 'Erreur', detail: 'Échec de l\'enregistrement' })
  }
}

const formatDate = (date) => {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('fr-FR')
}

onMounted(loadAffectations)
</script>

<style scoped>
.affectations-view {
  padding: 1.5rem;
}
.page-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 2rem;
}
.header-left h1 {
  margin: 0 0 0.5rem 0;
  color: #e2e8f0;
}
.subtitle {
  color: #94a3b8;
  margin: 0;
}
.table-card {
  background: #1e293b;
  border: 1px solid #334155;
}
</style>