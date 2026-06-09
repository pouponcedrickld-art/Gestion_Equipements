<template>
  <AgenceLayout>
    <div class="page-placeholder">
      <h2>{{ pageTitle }}</h2>
      <p>Page en cours de développement...</p>
    </div>
  </AgenceLayout>
</template>

<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'
<<<<<<< HEAD:frontend/src/views/maintenances/MaintenancesView.vue
=======
import AgenceLayout from '@/layouts/AgenceLayout.vue'
>>>>>>> 2c965af4f2361eccbef055db409105b763f2340d:frontend/src/views/agence/maintenances/MaintenancesView.vue

const route = useRoute()
const pageTitle = computed(() => route.name || 'Page')
</script>

<style scoped>
.page-placeholder {
  padding: 20px;
  text-align: center;
  color: #94a3b8;
}

.page-placeholder h2 {
  color: #e2e8f0;
  margin-bottom: 15px;
}
</style>
