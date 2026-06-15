<!-- =============================================
  FICHIER : AgenceLayout.vue
  RÔLE : Wrapper (alias) pour MainLayout.vue
  C'est juste pour la sémantique : les pages d'agence utilisent AgenceLayout,
  mais ça fait exactement la même chose que MainLayout !
============================================== -->
<template>
  <!-- Utilise MainLayout.vue et transmet le contenu (slot) -->
  <MainLayout>
    <slot />
  </MainLayout>
</template>

<script setup>
// Importe MainLayout.vue
import MainLayout from './MainLayout.vue'
</script>
