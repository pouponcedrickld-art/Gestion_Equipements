<template>
  <div class="page-placeholder">
    <h2>{{ pageTitle }}</h2>
    <p>Page en cours de développement...</p>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'

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
