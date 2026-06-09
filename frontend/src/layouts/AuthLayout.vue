<template>
  <div class="auth-layout">
    <div class="auth-container">
      <div class="auth-sidebar">
        <div class="auth-logo">
          <i class="pi pi-box" style="font-size: 2rem; color: #3b82f6;"></i>
          <h1>GestPark</h1>
          <p>Gestion de matériel</p>
        </div>
      </div>
      <div class="auth-content">
        <router-view />
      </div>
    </div>
  </div>
</template>

<script setup>
</script>

<style scoped>
.auth-layout {
  min-height: 100vh;
  background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
  display: flex;
  align-items: center;
  justify-content: center;
}

.auth-container {
  width: 100%;
  max-width: 1000px;
  background: #1e293b;
  border-radius: 16px;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
  display: grid;
  grid-template-columns: 350px 1fr;
  overflow: hidden;
}

.auth-sidebar {
  background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
  padding: 40px;
  display: flex;
  flex-direction: column;
  justify-content: center;
}

.auth-logo {
  text-align: center;
}

.auth-logo h1 {
  margin: 10px 0 5px;
  color: white;
  font-size: 2rem;
}

.auth-logo p {
  color: rgba(255, 255, 255, 0.8);
  margin: 0;
}

.auth-content {
  padding: 60px 50px;
  display: flex;
  align-items: center;
  justify-content: center;
}
</style>
