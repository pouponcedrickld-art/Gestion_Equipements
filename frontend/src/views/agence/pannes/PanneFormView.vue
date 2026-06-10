<template>
  <AgenceLayout>
    <div class="p-6 max-w-5xl mx-auto">
      <!-- Header -->
      <div class="mb-8 bg-gradient-to-br from-purple-900/30 to-blue-900/30 border border-cyan-400/30 rounded-2xl p-6">
        <h1 class="text-3xl font-bold bg-gradient-to-r from-cyan-400 to-purple-400 bg-clip-text text-transparent">
          NOUVELLE DÉCLARATION DE PANNE
        </h1>
        <p class="text-cyan-300/80 mt-2">RAPPORT D'INCIDENT SYSTÈME // ID: {{ new Date().getTime() }}</p>
      </div>

      <!-- Form -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column: Severity & Description -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Severity -->
          <div class="bg-gray-900/80 border border-yellow-500/40 rounded-xl p-6">
            <div class="flex items-center gap-3 mb-4">
              <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-yellow-500 to-orange-500 flex items-center justify-center">
                <span class="text-xl">⚠️</span>
              </div>
              <h2 class="text-xl font-bold text-yellow-400">NIVEAU DE GRAVITÉ</h2>
            </div>
            <select
              v-model="form.gravite"
              class="w-full bg-gray-800/50 border-2 border-yellow-500/30 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-cyan-400 focus:ring-2 focus:ring-cyan-400/30 transition-all"
            >
              <option value="mineur">🔵 Mineur - Interruption légère</option>
              <option value="moyen">🟡 Moyen - Interruption partielle</option>
              <option value="majeur">🟠 Majeur - Interruption critique</option>
              <option value="critique">🔴 Critique - Arrêt complet</option>
            </select>
          </div>

          <!-- Description -->
          <div class="bg-gray-900/80 border border-purple-500/40 rounded-xl p-6">
            <div class="flex items-center gap-3 mb-4">
              <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center">
                <span class="text-xl">📝</span>
              </div>
              <h2 class="text-xl font-bold text-purple-400">DESCRIPTION DE LA PANNE</h2>
            </div>
            <textarea
              v-model="form.description"
              class="w-full min-h-48 bg-gray-800/50 border-2 border-purple-500/30 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-cyan-400 focus:ring-2 focus:ring-cyan-400/30 transition-all resize-vertical"
              placeholder="Détaillez le problème rencontré..."
            ></textarea>
          </div>
        </div>

        <!-- Right Column: Photos -->
        <div class="space-y-6">
          <!-- Drag & Drop -->
          <div class="bg-gray-900/80 border border-cyan-500/40 rounded-xl p-6">
            <div class="flex items-center gap-3 mb-4">
              <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-cyan-500 to-blue-500 flex items-center justify-center">
                <span class="text-xl">📷</span>
              </div>
              <h2 class="text-xl font-bold text-cyan-400">PHOTOS</h2>
            </div>
            <div
              @dragover.prevent="isDragging = true"
              @dragleave.prevent="isDragging = false"
              @drop.prevent="handleDrop"
              class="border-2 border-dashed border-cyan-400/40 rounded-xl p-6 text-center cursor-pointer transition-all"
              :class="{
                'bg-cyan-500/10 border-cyan-400': isDragging,
                'hover:bg-cyan-500/5': !isDragging
              }"
            >
              <input
                type="file"
                ref="fileInput"
                multiple
                accept="image/*"
                class="hidden"
                @change="handleFileSelect"
              >
              <div @click="fileInput.click()">
                <span class="text-5xl mb-4 block">🖼️</span>
                <p class="text-cyan-300 font-medium">
                  Glissez-déposez vos images ici, ou cliquez pour sélectionner
                </p>
                <p class="text-cyan-300/60 text-sm mt-2">
                  Formats acceptés: JPG, PNG, WEBP
                </p>
              </div>
            </div>

            <!-- Preview -->
            <div v-if="form.photos.length > 0" class="mt-6 space-y-3">
              <div class="flex items-center justify-between">
                <h3 class="text-cyan-300 font-medium">Aperçu ({{ form.photos.length }})</h3>
                <button
                  @click="form.photos = []"
                  class="text-red-400 hover:text-red-300 text-sm"
                >
                  Effacer tout
                </button>
              </div>
              <div class="grid grid-cols-2 gap-3">
                <div
                  v-for="(photo, index) in form.photos"
                  :key="index"
                  class="relative group"
                >
                  <img
                    :src="photo.base64"
                    class="w-full h-24 object-cover rounded-lg border border-cyan-400/30"
                    :alt="`Photo ${index + 1}`"
                  >
                  <button
                    @click="removePhoto(index)"
                    class="absolute -top-2 -right-2 w-6 h-6 bg-red-500 rounded-full flex items-center justify-center text-white text-xs opacity-0 group-hover:opacity-100 transition-all"
                  >
                    ✕
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Submit Button -->
          <div class="bg-gray-900/80 border border-green-500/40 rounded-xl p-6">
            <button
              @click="handleSubmit"
              :disabled="isSubmitting"
              class="w-full py-4 rounded-xl font-bold text-lg transition-all flex items-center justify-center gap-3"
              :class="{
                'bg-gradient-to-r from-green-500 to-emerald-500 text-white hover:from-green-600 hover:to-emerald-600': !isSubmitting,
                'bg-gray-700 text-gray-400 cursor-not-allowed': isSubmitting
              }"
            >
              <span v-if="isSubmitting" class="animate-spin w-6 h-6 border-3 border-white border-t-transparent rounded-full"></span>
              <span v-else>🚀 ENREGISTRER LA PANNE</span>
            </button>
          </div>
        </div>
      </div>
    </div>
  </AgenceLayout>
</template>

<script setup>
import { ref } from 'vue'
import AgenceLayout from '@/layouts/AgenceLayout.vue'

// State
const isDragging = ref(false)
const isSubmitting = ref(false)
const fileInput = ref(null)

// Form
const form = ref({
  gravite: 'moyen',
  description: '',
  photos: []
})

// Methods
function handleDrop(event) {
  isDragging.value = false
  const files = event.dataTransfer.files
  processFiles(files)
}

function handleFileSelect(event) {
  const files = event.target.files
  processFiles(files)
}

function processFiles(files) {
  Array.from(files).forEach(file => {
    if (file.type.startsWith('image/')) {
      const reader = new FileReader()
      reader.onload = (e) => {
        form.value.photos.push({
          file,
          base64: e.target.result
        })
      }
      reader.readAsDataURL(file)
    }
  })
}

function removePhoto(index) {
  form.value.photos.splice(index, 1)
}

async function handleSubmit() {
  if (!form.value.description.trim()) {
    alert('Veuillez saisir une description')
    return
  }

  isSubmitting.value = true

  try {
    console.log('Submitting form:', form.value)
    // TODO: Call API to save panne
    await new Promise(resolve => setTimeout(resolve, 1500))
    alert('Panne enregistrée avec succès!')
    form.value = {
      gravite: 'moyen',
      description: '',
      photos: []
    }
  } catch (error) {
    console.error('Error saving panne:', error)
    alert('Erreur lors de l\'enregistrement')
  } finally {
    isSubmitting.value = false
  }
}
</script>

<style scoped>
/* Custom styles for enhanced cyberpunk feel */
@keyframes pulse-glow {
  0%, 100% {
    box-shadow: 0 0 5px rgba(0, 255, 255, 0.3);
  }
  50% {
    box-shadow: 0 0 20px rgba(0, 255, 255, 0.5);
  }
}
</style>
