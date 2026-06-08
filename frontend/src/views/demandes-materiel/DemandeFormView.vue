<template>
  <MainLayout>
    <div class="demande-form-container">
      <div class="header-section">
        <router-link to="/demandes-materiel" class="back-btn">
          <i class="pi pi-arrow-left"></i> Retour à la liste
        </router-link>
        <h2>Nouvelle Demande de Matériel</h2>
      </div>

      <form @submit.prevent="submitForm" class="demande-form">
        <div class="form-grid">
          <!-- Type de matériel -->
          <div class="form-group">
            <label for="equipement_id">Type de matériel</label>
            <select v-model="form.equipement_id" id="equipement_id" required>
              <option value="" disabled>
                {{ equipements.length === 0 ? 'Aucun équipement disponible' : 'Sélectionner un équipement' }}
              </option>
              <option v-for="eq in equipements" :key="eq.id" :value="eq.id">
                {{ eq.nom }} - {{ eq.marque }} {{ eq.modele }} ({{ eq.reference }})
              </option>
            </select>
            <small v-if="errorMsg" class="error-text">
              {{ errorMsg }}
            </small>
          </div>

          <!-- Quantité demandée -->
          <div class="form-group">
            <label for="quantite">Quantité demandée</label>
            <input 
              type="number" 
              v-model="form.quantite" 
              id="quantite" 
              min="1" 
              required
              placeholder="Minimum 1"
            >
          </div>

          <!-- Urgence -->
          <div class="form-group">
            <label for="urgence">Urgence</label>
            <select v-model="form.urgence" id="urgence" required>
              <option value="Basse">Basse</option>
              <option value="Moyenne">Moyenne</option>
              <option value="Haute">Haute</option>
            </select>
          </div>

          <!-- Date souhaitée -->
          <div class="form-group">
            <label for="date_souhaitee">Date souhaitée</label>
            <input 
              type="date" 
              v-model="form.date_souhaitee" 
              id="date_souhaitee" 
              required
              :min="today"
            >
          </div>

          <!-- Motif -->
          <div class="form-group full-width">
            <label for="motif">Motif de la demande</label>
            <textarea 
              v-model="form.motif" 
              id="motif" 
              rows="4" 
              required
              placeholder="Expliquez pourquoi vous avez besoin de ce matériel..."
            ></textarea>
          </div>
        </div>

        <div class="form-actions">
          <button type="submit" class="submit-btn" :disabled="submitting">
            <i class="pi pi-check-circle" v-if="!submitting"></i>
            <i class="pi pi-spin pi-spinner" v-else></i>
            {{ submitting ? 'Envoi en cours...' : 'Envoyer la demande' }}
          </button>
        </div>
      </form>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useToast } from 'primevue/usetoast'
import MainLayout from '@/layouts/MainLayout.vue'
import equipementApi from '@/api/equipementApi'
import demandeMaterielApi from '@/api/demandeMaterielApi'

const router = useRouter()
const toast = useToast()
const equipements = ref([])
const submitting = ref(false)
const errorMsg = ref('')

// Date du jour au format YYYY-MM-DD (local)
const today = new Date().toLocaleDateString('en-CA')

const form = ref({
  equipement_id: '',
  quantite: 1,
  urgence: 'Basse',
  motif: '',
  date_souhaitee: ''
})

// Charge les équipements disponibles
onMounted(async () => {
  try {
    const res = await equipementApi.index()
    console.log('API Response:', res)
    if (res.data) {
      equipements.value = Array.isArray(res.data) ? res.data : (res.data.data || [])
      if (equipements.value.length === 0) {
        errorMsg.value = "Aucun matériel n'est disponible au stock."
      }
    }
  } catch (error) {
    console.error('Erreur lors du chargement des équipements', error)
    errorMsg.value = "Erreur de connexion au serveur lors du chargement des équipements."
    toast.add({ severity: 'error', summary: 'Erreur', detail: errorMsg.value, life: 3000 })
  }
})

// Soumet le formulaire
const submitForm = async () => {
  // Validation supplémentaire de la date
  if (form.value.date_souhaitee < today) {
    toast.add({ severity: 'warn', summary: 'Attention', detail: 'La date souhaitée ne peut pas être dans le passé.', life: 3000 })
    return
  }

  submitting.value = true
  try {
    await demandeMaterielApi.store(form.value)
    toast.add({ severity: 'success', summary: 'Succès', detail: 'Demande envoyée avec succès', life: 3000 })
    setTimeout(() => {
      router.push('/demandes-materiel')
    }, 1500)
  } catch (error) {
    console.error('Erreur lors de la création de la demande', error)
    const message = error.response?.data?.message || 'Une erreur est survenue lors de la création de la demande.'
    toast.add({ severity: 'error', summary: 'Erreur', detail: message, life: 5000 })
  } finally {
    submitting.value = false
  }
}
</script>

<style scoped>
.demande-form-container {
  padding: 24px;
  max-width: 900px;
  margin: 0 auto;
}

.header-section {
  display: flex;
  flex-direction: column;
  gap: 12px;
  margin-bottom: 24px;
}

.back-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  color: #3b82f6;
  text-decoration: none;
  font-size: 0.9rem;
}

.back-btn:hover {
  text-decoration: underline;
}

.header-section h2 {
  color: #f8fafc;
  margin: 0;
  font-size: 1.5rem;
}

.demande-form {
  background: #1e293b;
  border: 1px solid #334155;
  border-radius: 12px;
  padding: 24px;
}

.form-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 20px;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.form-group.full-width {
  grid-column: span 2;
}

label {
  color: #94a3b8;
  font-size: 0.9rem;
}

.error-text {
  color: #ef4444;
  font-size: 0.8rem;
  margin-top: 4px;
}

input, select, textarea {
  background: #0f172a;
  border: 1px solid #334155;
  border-radius: 8px;
  padding: 10px 12px;
  color: #f8fafc;
  font-size: 1rem;
}

input:focus, select:focus, textarea:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2);
}

.form-actions {
  margin-top: 32px;
  display: flex;
  justify-content: flex-end;
}

.submit-btn {
  background: #3b82f6;
  color: white;
  border: none;
  border-radius: 8px;
  padding: 12px 24px;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 10px;
  cursor: pointer;
  transition: background 0.2s;
}

.submit-btn:hover:not(:disabled) {
  background: #2563eb;
}

.submit-btn:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

@media (max-width: 640px) {
  .form-grid {
    grid-template-columns: 1fr;
  }
  .form-group.full-width {
    grid-column: span 1;
  }
}
</style>
