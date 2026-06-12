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
  background-color: var(--bg-app);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
}

.auth-container {
  width: 100%;
  max-width: 900px;
  background: var(--bg-card);
  border-radius: var(--radius-xl);
  box-shadow: var(--shadow-lg);
  display: grid;
  grid-template-columns: 1fr 1fr;
  overflow: hidden;
}

.auth-sidebar {
  background-color: var(--primary);
  padding: 60px 40px;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  text-align: center;
}

.auth-logo i {
  font-size: 3.5rem !important;
  color: var(--text-dark) !important;
  margin-bottom: 20px;
}

.auth-logo h1 {
  margin: 10px 0 5px;
  color: var(--text-dark);
  font-size: 2.5rem;
  font-weight: 800;
  letter-spacing: 0.1em;
}

.auth-logo p {
  color: var(--text-dark);
  opacity: 0.7;
  margin: 0;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.auth-content {
  padding: 60px 50px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--bg-card);
}

@media (max-width: 768px) {
  .auth-container {
    grid-template-columns: 1fr;
  }
  .auth-sidebar {
    display: none;
  }
}
</style>
