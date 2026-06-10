<template>
  <div>
    <!-- Afficher un loader tant que l'utilisateur n'est pas chargé -->
    <div v-if="loading" class="loading-container">
      <i class="pi pi-spin pi-spinner" style="font-size: 2rem"></i>
      <p style="margin-top: 1rem">Chargement...</p>
    </div>
    <!-- Afficher la vue correspondante en fonction du rôle -->
    <component v-else :is="currentView"></component>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { useAuthStore } from '@/stores/authStore'

// Import des vues
import DirectionEquipementsView from '@/views/direction/equipements/EquipementsView.vue'
import AgenceStockView from '@/views/agence/stock/StockAgencesView.vue'

const authStore = useAuthStore()
const loading = ref(true)

// Attendre que l'utilisateur soit chargé
watch(() => authStore.user, (newVal) => {
  if (newVal) {
    loading.value = false
  }
}, { immediate: true })

// Déterminer quelle vue afficher
const currentView = computed(() => {
  if (authStore.isSuperAdmin || authStore.isGestionnaireGeneral) {
    return DirectionEquipementsView
  }
  return AgenceStockView
})
</script>

<style scoped>
.loading-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 100vh;
  color: #64748b;
}
</style>
