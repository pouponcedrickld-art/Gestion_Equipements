<template>
  <DirectionLayout>
    <div class="import-page" ref="pageContainer">
      <!-- En-tête -->
      <div class="page-header animate-in">
        <div class="header-left">
          <div class="title-with-icon">
            <div class="icon-wrapper">
              <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="svg-icon">
                <path d="M12 16V12M12 12V8M12 12H8M12 12H16M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </div>
            <div>
              <h1>Importation de Masse</h1>
              <p class="subtitle">Ajoutez des centaines d'équipements en quelques secondes</p>
            </div>
          </div>
        </div>
        <div class="header-actions">
          <Button 
            label="Télécharger le Modèle" 
            icon="pi pi-download" 
            class="p-button-outlined action-btn"
            @click="downloadTemplate"
            :loading="loadingTemplate"
          />
        </div>
      </div>

      <!-- Zone de Téléchargement -->
      <div class="upload-section animate-in" v-if="!previewData">
        <div 
          class="drop-zone"
          :class="{ 'drag-over': isDragging }"
          @dragover.prevent="isDragging = true"
          @dragleave="isDragging = false"
          @drop.prevent="handleDrop"
          @click="$refs.fileInput.click()"
        >
          <input 
            type="file" 
            ref="fileInput" 
            class="hidden" 
            accept=".csv,.xlsx,.xls"
            @change="handleFileSelect"
          />
          <div class="drop-content">
            <div class="cloud-icon">
              <i class="pi pi-cloud-upload"></i>
            </div>
            <h3>Glissez votre fichier ici ou cliquez pour parcourir</h3>
            <p>Supporte les formats CSV, XLSX et XLS (Max 10 Mo)</p>
          </div>
        </div>
      </div>

      <!-- Aperçu de l'Importation -->
      <div class="preview-section animate-in" v-else>
        <div class="preview-header">
          <div class="preview-stats">
            <Tag :value="`${previewData.total_rows} lignes détectées`" severity="info" class="mr-2" />
            <Tag :value="`${previewData.errors_count} erreurs`" :severity="previewData.errors_count > 0 ? 'danger' : 'success'" />
          </div>
          <div class="preview-actions">
            <Button label="Changer de fichier" icon="pi pi-refresh" class="p-button-text mr-2" @click="resetImport" />
            <Button 
              label="Confirmer l'Importation" 
              icon="pi pi-check" 
              class="p-button-success" 
              :disabled="previewData.errors_count > 0"
              :loading="importing"
              @click="confirmImport"
            />
          </div>
        </div>

        <Card class="preview-card mt-4">
          <template #content>
            <DataTable :value="previewData.preview" responsiveLayout="scroll" stripedRows>
              <Column field="row" header="Ligne" style="width: 80px"></Column>
              <Column header="Données">
                <template #body="{ data }">
                  <div class="data-preview">
                    <span v-for="(val, key) in data.data" :key="key" class="data-chip">
                      <strong>{{ key }}:</strong> {{ val }}
                    </span>
                  </div>
                </template>
              </Column>
              <Column header="Statut" style="width: 150px">
                <template #body="{ data }">
                  <Tag 
                    :value="data.valid ? 'Valide' : 'Invalide'" 
                    :severity="data.valid ? 'success' : 'danger'"
                  />
                  <div v-if="!data.valid" class="error-text">
                    {{ data.errors.join(', ') }}
                  </div>
                </template>
              </Column>
            </DataTable>
          </template>
        </Card>
      </div>

      <!-- Guide d'importation -->
      <div class="guide-section animate-in mt-8" v-if="!previewData">
        <h3>Comment ça marche ?</h3>
        <div class="steps-grid">
          <div class="step-card">
            <div class="step-num">1</div>
            <h4>Téléchargez le modèle</h4>
            <p>Utilisez notre fichier CSV structuré pour garantir la compatibilité des données.</p>
          </div>
          <div class="step-card">
            <div class="step-num">2</div>
            <h4>Remplissez vos données</h4>
            <p>Ajoutez vos équipements (référence, marque, catégorie, etc.) dans le fichier.</p>
          </div>
          <div class="step-card">
            <div class="step-num">3</div>
            <h4>Importez et validez</h4>
            <p>Téléchargez le fichier et vérifiez l'aperçu avant de confirmer l'ajout définitif.</p>
          </div>
        </div>
      </div>
    </div>
  </DirectionLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useToast } from 'primevue/usetoast'
import { useEquipementStore } from '@/stores/equipementStore'
import DirectionLayout from '@/layouts/DirectionLayout.vue'
import gsap from 'gsap'

// PrimeVue
import Button from 'primevue/button'
import Card from 'primevue/card'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Tag from 'primevue/tag'

const toast = useToast()
const equipementStore = useEquipementStore()

const isDragging = ref(false)
const selectedFile = ref(null)
const previewData = ref(null)
const importing = ref(false)
const loadingTemplate = ref(false)

const handleFileSelect = (event) => {
  const file = event.target.files[0]
  if (file) processFile(file)
}

const handleDrop = (event) => {
  isDragging.value = false
  const file = event.dataTransfer.files[0]
  if (file) processFile(file)
}

const processFile = async (file) => {
  selectedFile.value = file
  try {
    const res = await equipementStore.previewImport(file)
    previewData.value = res
    gsap.from('.preview-section', { opacity: 0, y: 20, duration: 0.5 })
  } catch (err) {
    toast.add({ severity: 'error', summary: 'Erreur', detail: 'Fichier invalide ou corrompu' })
  }
}

const confirmImport = async () => {
  importing.value = true
  try {
    const res = await equipementStore.importEquipements(selectedFile.value)
    toast.add({ 
      severity: 'success', 
      summary: 'Succès', 
      detail: `${res.statistiques.succes} équipements importés avec succès` 
    })
    resetImport()
  } catch (err) {
    toast.add({ severity: 'error', summary: 'Erreur', detail: 'L\'importation a échoué' })
  } finally {
    importing.value = false
  }
}

const downloadTemplate = async () => {
  loadingTemplate.value = true
  try {
    await equipementStore.downloadImportTemplate()
    toast.add({ severity: 'success', summary: 'Succès', detail: 'Modèle téléchargé' })
  } catch (err) {
    toast.add({ severity: 'error', summary: 'Erreur', detail: 'Impossible de télécharger le modèle' })
  } finally {
    loadingTemplate.value = false
  }
}

const resetImport = () => {
  selectedFile.value = null
  previewData.value = null
}

onMounted(() => {
  gsap.from('.animate-in', {
    opacity: 0,
    y: 30,
    duration: 0.8,
    stagger: 0.2,
    ease: 'power3.out'
  })
})
</script>

<style scoped lang="scss">
.import-page {
  padding: 2rem;
  max-width: 1200px;
  margin: 0 auto;
}

.title-with-icon {
  display: flex; align-items: center; gap: 1.5rem;
  .icon-wrapper {
    width: 60px; height: 60px;
    background: linear-gradient(135deg, #8b5cf6 0%, #6366f1 100%);
    border-radius: 16px; display: flex; align-items: center; justify-content: center;
    color: white; box-shadow: 0 8px 16px rgba(99, 102, 241, 0.2);
    .svg-icon { width: 32px; height: 32px; }
  }
  h1 { font-size: 2rem; font-weight: 800; color: #1e293b; margin: 0; }
  .subtitle { color: #64748b; }
}

.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 3rem; }

.upload-section {
  .drop-zone {
    border: 2px dashed #e2e8f0;
    border-radius: 24px;
    padding: 5rem 2rem;
    text-align: center;
    background: white;
    cursor: pointer;
    transition: all 0.3s ease;
    
    &:hover, &.drag-over {
      border-color: #6366f1;
      background: #f5f3ff;
      transform: scale(1.01);
    }
    
    .cloud-icon {
      font-size: 4rem;
      color: #6366f1;
      margin-bottom: 1.5rem;
    }
    
    h3 { font-size: 1.5rem; color: #1e293b; margin-bottom: 0.5rem; }
    p { color: #64748b; }
  }
}

.preview-section {
  .preview-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
  }
  
  .preview-card {
    border-radius: 20px;
    overflow: hidden;
    border: none;
    box-shadow: 0 10px 25px rgba(0,0,0,0.05);
  }
  
  .data-preview {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    
    .data-chip {
      background: #f1f5f9;
      padding: 0.2rem 0.6rem;
      border-radius: 6px;
      font-size: 0.8rem;
      color: #475569;
    }
  }
  
  .error-text {
    font-size: 0.75rem;
    color: #ef4444;
    margin-top: 0.3rem;
  }
}

.steps-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 2rem;
  margin-top: 1.5rem;
  
  .step-card {
    background: white;
    padding: 2rem;
    border-radius: 20px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.03);
    position: relative;
    
    .step-num {
      position: absolute;
      top: -15px;
      left: 20px;
      width: 40px;
      height: 40px;
      background: #6366f1;
      color: white;
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 800;
      box-shadow: 0 4px 10px rgba(99, 102, 241, 0.3);
    }
    
    h4 { margin-top: 0.5rem; margin-bottom: 0.5rem; color: #1e293b; }
    p { font-size: 0.9rem; color: #64748b; line-height: 1.5; }
  }
}
</style>
