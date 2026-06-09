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
import AgenceLayout from '@/layouts/AgenceLayout.vue'

const route = useRoute()
const pageTitle = computed(() => route.params.id ? 'Modifier Affectation' : 'Nouvelle Affectation')
</script>
