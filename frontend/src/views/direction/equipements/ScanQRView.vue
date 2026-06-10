<template>
  <DirectionLayout>
    <div class="scan-qr-page" ref="pageContainer">
      <!-- En-tête -->
      <div class="page-header animate-in">
        <div class="header-left">
          <div class="title-with-icon">
            <div class="icon-wrapper">
              <i class="pi pi-qrcode"></i>
            </div>
            <div>
              <h1>Scanner un Équipement</h1>
              <p class="subtitle">Pointez la caméra vers un QR Code pour voir les détails</p>
            </div>
          </div>
        </div>
        <div class="header-actions">
          <Button 
            label="Retour" 
            icon="pi pi-arrow-left" 
            class="p-button-text p-button-secondary"
            @click="$router.push('/equipements')"
          />
        </div>
      </div>

      <!-- Zone du Scanner -->
      <div class="scanner-section animate-in">
        <div class="scanner-container">
          <div id="reader" class="qr-reader"></div>
          
          <div v-if="scanning" class="scanning-overlay">
            <div class="scan-line"></div>
            <div class="corner top-left"></div>
            <div class="corner top-right"></div>
            <div class="corner bottom-left"></div>
            <div class="corner bottom-right"></div>
          </div>

          <div v-if="error" class="error-overlay">
            <i class="pi pi-exclamation-triangle"></i>
            <p>{{ error }}</p>
            <Button label="Réessayer" icon="pi pi-refresh" class="p-button-sm mt-2" @click="startScanner" />
          </div>

          <div v-if="!scanning && !error" class="start-overlay">
            <Button label="Démarrer la Caméra" icon="pi pi-camera" class="p-button-lg p-button-raised" @click="startScanner" />
          </div>
        </div>

        <div class="scanner-info">
          <div class="info-card">
            <i class="pi pi-info-circle"></i>
            <p>Le QR Code doit contenir les informations d'identification de l'équipement.</p>
          </div>
        </div>
      </div>

      <!-- Résultat de détection (Dialog) -->
      <Dialog v-model:visible="showResultDialog" header="Équipement détecté" :style="{ width: '450px' }" :modal="true">
        <div v-if="scanResult" class="result-details">
          <div class="result-icon">
            <i class="pi pi-check-circle"></i>
          </div>
          <h3>{{ scanResult.reference }}</h3>
          <p class="serial">S/N: {{ scanResult.numero_serie }}</p>
          
          <div class="result-actions mt-6">
            <Button label="Voir la fiche complète" icon="pi pi-eye" class="p-button-primary w-full mb-3" @click="goToEquipment" />
            <Button label="Scanner à nouveau" icon="pi pi-refresh" class="p-button-text w-full" @click="resetScanner" />
          </div>
        </div>
      </Dialog>
    </div>
  </DirectionLayout>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { useToast } from 'primevue/usetoast'
import { Html5Qrcode } from 'html5-qrcode'
import DirectionLayout from '@/layouts/DirectionLayout.vue'
import gsap from 'gsap'

// PrimeVue
import Button from 'primevue/button'
import Dialog from 'primevue/dialog'

const router = useRouter()
const toast = useToast()

const scanning = ref(false)
const error = ref(null)
const scanResult = ref(null)
const showResultDialog = ref(false)
let html5QrCode = null

const startScanner = async () => {
  error.value = null
  scanning.value = true
  
  try {
    if (!html5QrCode) {
      html5QrCode = new Html5Qrcode("reader")
    }
    
    const config = { fps: 10, qrbox: { width: 250, height: 250 } }
    
    await html5QrCode.start(
      { facingMode: "environment" },
      config,
      onScanSuccess,
      onScanFailure
    )
  } catch (err) {
    scanning.value = false
    error.value = "Impossible d'accéder à la caméra. Veuillez vérifier les permissions."
    console.error(err)
  }
}

const onScanSuccess = (decodedText) => {
  try {
    const data = JSON.parse(decodedText)
    
    if (data.type === 'equipement' && data.id) {
      stopScanner()
      scanResult.value = data
      showResultDialog.value = true
      
      toast.add({ 
        severity: 'success', 
        summary: 'Équipement détecté', 
        detail: `Référence: ${data.reference}`,
        life: 3000
      })
    } else {
      toast.add({ 
        severity: 'warn', 
        summary: 'Format invalide', 
        detail: 'Ce QR Code n\'est pas un équipement valide',
        life: 3000
      })
    }
  } catch (err) {
    toast.add({ 
      severity: 'error', 
      summary: 'Erreur de lecture', 
      detail: 'Impossible de décoder les données du QR Code',
      life: 3000
    })
  }
}

const onScanFailure = (error) => {
  // On ignore les échecs de lecture silencieux (quand aucun QR n'est dans le champ)
}

const stopScanner = async () => {
  if (html5QrCode && html5QrCode.isScanning) {
    await html5QrCode.stop()
    scanning.value = false
  }
}

const resetScanner = () => {
  showResultDialog.value = false
  scanResult.value = null
  startScanner()
}

const goToEquipment = () => {
  if (scanResult.value) {
    router.push(`/equipements/${scanResult.value.id}`)
  }
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

onUnmounted(() => {
  stopScanner()
})
</script>

<style scoped lang="scss">
.scan-qr-page {
  padding: 2rem;
  max-width: 800px;
  margin: 0 auto;
}

.title-with-icon {
  display: flex; align-items: center; gap: 1.5rem;
  .icon-wrapper {
    width: 60px; height: 60px;
    background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
    border-radius: 16px; display: flex; align-items: center; justify-content: center;
    color: white; box-shadow: 0 8px 16px rgba(8, 145, 178, 0.2);
    i { font-size: 2rem; }
  }
  h1 { font-size: 2rem; font-weight: 800; color: #1e293b; margin: 0; }
  .subtitle { color: #64748b; }
}

.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 3rem; }

.scanner-container {
  position: relative;
  width: 100%;
  max-width: 500px;
  aspect-ratio: 1;
  margin: 0 auto;
  background: #000;
  border-radius: 24px;
  overflow: hidden;
  box-shadow: 0 20px 40px rgba(0,0,0,0.2);
}

.qr-reader {
  width: 100%;
  height: 100%;
}

.scanning-overlay {
  position: absolute;
  top: 0; left: 0; right: 0; bottom: 0;
  pointer-events: none;
  z-index: 10;
  
  .scan-line {
    position: absolute;
    top: 20%;
    left: 10%;
    right: 10%;
    height: 2px;
    background: #06b6d4;
    box-shadow: 0 0 15px #06b6d4;
    animation: scan 2.5s infinite ease-in-out;
  }
  
  .corner {
    position: absolute;
    width: 40px;
    height: 40px;
    border: 4px solid #06b6d4;
    
    &.top-left { top: 20%; left: 20%; border-right: 0; border-bottom: 0; border-top-left-radius: 12px; }
    &.top-right { top: 20%; right: 20%; border-left: 0; border-bottom: 0; border-top-right-radius: 12px; }
    &.bottom-left { bottom: 20%; left: 20%; border-right: 0; border-top: 0; border-bottom-left-radius: 12px; }
    &.bottom-right { bottom: 20%; right: 20%; border-left: 0; border-top: 0; border-bottom-right-radius: 12px; }
  }
}

@keyframes scan {
  0%, 100% { top: 20%; }
  50% { top: 80%; }
}

.start-overlay, .error-overlay {
  position: absolute;
  top: 0; left: 0; right: 0; bottom: 0;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  background: rgba(0,0,0,0.7);
  color: white;
  padding: 2rem;
  text-align: center;
  z-index: 20;
}

.error-overlay i { font-size: 3rem; color: #ef4444; margin-bottom: 1rem; }

.scanner-info {
  margin-top: 2rem;
  .info-card {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1.2rem;
    background: #f0f9ff;
    border: 1px solid #bae6fd;
    border-radius: 16px;
    color: #0369a1;
    i { font-size: 1.2rem; }
    p { margin: 0; font-size: 0.9rem; }
  }
}

.result-details {
  text-align: center;
  padding: 1rem 0;
  .result-icon {
    font-size: 4rem;
    color: #10b981;
    margin-bottom: 1.5rem;
  }
  h3 { font-size: 1.5rem; font-weight: 700; margin-bottom: 0.5rem; }
  .serial { color: #64748b; font-family: monospace; }
}

:deep(.qr-reader video) {
  object-fit: cover !important;
}

:deep(#reader__dashboard) {
  display: none !important;
}

:deep(#reader__status_span) {
  display: none !important;
}
</style>
