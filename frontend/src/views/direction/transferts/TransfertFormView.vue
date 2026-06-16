<template>
  <DirectionLayout>
    <div class="transfert-form-container" ref="pageContainer">
      <div class="form-header animate-header">
        <Button
          icon="pi pi-arrow-left"
          class="p-button-text p-button-rounded back-btn"
          @click="$router.push('/transferts')"
        />
        <h1 class="page-title">Nouveau Transfert</h1>
      </div>

      <div class="form-card animate-card">
        <div class="form-grid">
          <div class="field">
            <label class="font-bold text-sm">Équipement *</label>
            <Dropdown
              v-model="form.equipement_id"
              :options="equipements"
              optionLabel="label"
              optionValue="id"
              placeholder="Sélectionner un équipement"
              class="w-full"
              filter
              :loading="loadingEquipements"
              :filterPlaceholder="'Rechercher un équipement...'"
            >
              <template #value="slotProps">
                <div v-if="slotProps.value" class="equip-option">
                  {{ getEquipementLabel(slotProps.value) }}
                </div>
                <span v-else>{{ slotProps.placeholder }}</span>
              </template>
              <template #option="slotProps">
                <div class="equip-option">
                  <span class="font-semibold">{{ slotProps.option.nom || slotProps.option.marque + ' ' + slotProps.option.modele }}</span>
                  <small class="text-muted ml-2">SN: {{ slotProps.option.numero_serie }}</small>
                </div>
              </template>
            </Dropdown>
          </div>

          <div class="field">
            <label class="font-bold text-sm">Agence de destination *</label>
            <Dropdown
              v-model="form.agence_destination_id"
              :options="agences"
              optionLabel="nom"
              optionValue="id"
              placeholder="Sélectionner une agence"
              class="w-full"
              filter
              :loading="loadingAgences"
            />
          </div>

          <div class="field">
            <label class="font-bold text-sm">Type de transfert *</label>
            <SelectButton
              v-model="form.type_transfert"
              :options="typesTransfert"
              optionLabel="label"
              optionValue="value"
              class="w-full"
            />
          </div>

          <div class="field full-width">
            <label class="font-bold text-sm">Observations</label>
            <Textarea
              v-model="form.observations"
              rows="4"
              class="w-full"
              placeholder="Observations éventuelles..."
              :maxlength="1000"
            />
            <small class="text-muted">{{ form.observations.length }}/1000</small>
          </div>
        </div>

        <div class="form-actions">
          <Button label="Annuler" icon="pi pi-times" class="p-button-text" @click="$router.push('/transferts')" />
          <Button
            label="Créer le transfert"
            icon="pi pi-send"
            class="p-button-success"
            :loading="submitting"
            @click="submitForm"
          />
        </div>
      </div>
    </div>
  </DirectionLayout>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useToast } from 'primevue/usetoast'
import DirectionLayout from '@/layouts/DirectionLayout.vue'
import transfertApi from '@/api/transfertApi'
import equipementApi from '@/api/equipementApi'
import agenceApi from '@/api/agenceApi'
import gsap from 'gsap'

import Button from 'primevue/button'
import Dropdown from 'primevue/dropdown'
import SelectButton from 'primevue/selectbutton'
import Textarea from 'primevue/textarea'

const router = useRouter()
const toast = useToast()

const submitting = ref(false)
const loadingEquipements = ref(false)
const loadingAgences = ref(false)
const equipements = ref([])
const agences = ref([])

const form = reactive({
  equipement_id: null,
  agence_destination_id: null,
  type_transfert: 'livraison_generale',
  observations: ''
})

const typesTransfert = [
  { label: 'Livraison générale', value: 'livraison_generale' },
  { label: 'Retour générale', value: 'retour_generale' },
  { label: 'Transfert interne', value: 'transfert_interne' }
]

const getEquipementLabel = (id) => {
  const eq = equipements.value.find(e => e.id === id)
  if (!eq) return ''
  return `${eq.nom || eq.marque + ' ' + eq.modele} (SN: ${eq.numero_serie})`
}

const submitForm = async () => {
  if (!form.equipement_id || !form.agence_destination_id || !form.type_transfert) {
    toast.add({ severity: 'warn', summary: 'Champs requis', detail: 'Veuillez remplir tous les champs obligatoires', life: 3000 })
    return
  }
  submitting.value = true
  try {
    const res = await transfertApi.store(form)
    if (res.data.success) {
      toast.add({ severity: 'success', summary: 'Succès', detail: 'Transfert créé avec succès', life: 3000 })
      router.push('/transferts')
    } else {
      throw new Error(res.data.message || 'Erreur lors de la création')
    }
  } catch (err) {
    toast.add({ severity: 'error', summary: 'Erreur', detail: err.response?.data?.message || err.message, life: 5000 })
  } finally {
    submitting.value = false
  }
}

onMounted(async () => {
  loadingEquipements.value = true
  loadingAgences.value = true
  try {
    const [eqRes, agRes] = await Promise.all([
      equipementApi.getDisponiblesTransfert(),
      agenceApi.index()
    ])
    if (eqRes.data.success) {
      equipements.value = eqRes.data.data.map(e => ({
        ...e,
        label: `${e.nom || e.marque + ' ' + e.modele} (SN: ${e.numero_serie})`
      }))
    }
    if (agRes.data.success) {
      agences.value = agRes.data.data
    }
  } catch (err) {
    toast.add({ severity: 'error', summary: 'Erreur', detail: 'Impossible de charger les données', life: 3000 })
  } finally {
    loadingEquipements.value = false
    loadingAgences.value = false
  }

  gsap.from('.animate-header', { opacity: 0, y: -20, duration: 0.5, ease: 'power3.out' })
  gsap.from('.animate-card', { opacity: 0, y: 20, duration: 0.6, delay: 0.2, ease: 'power3.out' })
})
</script>

<style scoped lang="scss">
.transfert-form-container {
  padding: 2rem;
  max-width: 800px;
  margin: 0 auto;
}

.form-header {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-bottom: 2rem;

  .page-title {
    font-size: 1.8rem;
    font-weight: 800;
    color: #1e293b;
    margin: 0;
  }
}

.form-card {
  background: white;
  border-radius: 20px;
  padding: 2rem;
  box-shadow: 0 4px 12px rgba(0,0,0,0.05);
  border: 1px solid #f1f5f9;
}

.form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1.5rem;

  .full-width {
    grid-column: 1 / -1;
  }

  .field label {
    display: block;
    margin-bottom: 0.5rem;
    color: #475569;
  }
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  margin-top: 2rem;
  padding-top: 1.5rem;
  border-top: 1px solid #f1f5f9;
}

.equip-option {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}
</style>
