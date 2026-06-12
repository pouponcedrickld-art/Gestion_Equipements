<template>
  <Toast position="top-right" />
  <router-view />
</template>

<script setup>
import Toast from 'primevue/toast'
</script>

<style>
body {
  margin: 0;
  font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
  background-color: var(--bg-app);
  color: var(--text-main);
}
</style>
