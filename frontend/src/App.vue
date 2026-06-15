<template>
  <!-- =============================================
  FICHIER : App.vue
  RÔLE : Composant RACINE de toute l'application
  Tout ce qui est dans App.vue s'affiche sur TOUTES les pages
============================================== -->
  <!-- Le composant Toast : c'est la zone où s'affichent les petits messages (ex: "Succès !") -->
  <Toast position="top-right" /> <!-- Positionné en haut à droite -->
  
  <!-- router-view : c'est la zone où s'affiche la page actuelle (dashboard, login, etc.)
       Le routeur remplace ça par le composant correspondant à la route ! -->
  <router-view />
</template>

<script setup>
// Importe le composant Toast de PrimeVue
import Toast from 'primevue/toast'
</script>

<style>
/* Style global pour le body (la page entière) */
body {
  margin: 0; /* Enlève la marge par défaut du navigateur */
  font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; /* Police de caractère */
  background-color: var(--bg-app); /* Couleur de fond, définie dans theme.css */
  color: var(--text-main); /* Couleur du texte, définie dans theme.css */
}
</style>
