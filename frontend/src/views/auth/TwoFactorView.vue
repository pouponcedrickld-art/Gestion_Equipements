<!-- =============================================
  FICHIER : views/auth/TwoFactorView.vue
  RÔLE : Page de double authentification (2FA)
  Demande un code à 6 chiffres → appelle authStore.verify2FA()
  Si ok → redirige vers dashboard
============================================== -->
<template>
  <div class="twofa-box">
    <h1>GESTPARK</h1>
    <p class="subtitle">Double authentification</p>

    <!-- Formulaire de saisie du code 2FA -->
    <form @submit.prevent="handleVerify">
      <div class="form-group">
        <label>Code à 6 chiffres</label>
        <input v-model="code" type="text" maxlength="6" placeholder="000000" required />
      </div>

      <!-- Bouton de vérification -->
      <button type="submit" :disabled="loading">
        {{ loading ? 'Vérification...' : 'Vérifier' }}
      </button>

      <!-- Message d'erreur si le code est invalide -->
      <p v-if="error" class="error">{{ error }}</p>
    </form>

    <!-- Bouton pour retourner à la page de login -->
    <button @click="goBack" class="back-btn">
      <i class="pi pi-arrow-left"></i> Retour
    </button>
  </div>
</template>

<script setup>
// =============================================
// 1. IMPORTS
// =============================================
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/authStore.js'

// =============================================
// 2. INITIALISATIONS
// =============================================
const router = useRouter()
const authStore = useAuthStore()

// =============================================
// 3. VARIABLES RÉACTIVES
// =============================================
const code = ref('') // Code 2FA saisi par l'utilisateur
const loading = ref(false) // True pendant la vérification
const error = ref('') // Message d'erreur

// =============================================
// 4. FONCTIONS
// =============================================
// handleVerify() : Vérifie le code 2FA
const handleVerify = async () => {
  loading.value = true
  error.value = ''
  try {
    // Appelle verify2FA du store avec le code
    await authStore.verify2FA(code.value)
    // Si ça fonctionne → redirige vers le dashboard
    router.push('/')
  } catch (err) {
    // Si erreur → affiche le message
    error.value = err.response?.data?.message || 'Code invalide'
  } finally {
    loading.value = false
  }
}

// goBack() : Retourne à la page de login et réinitialise les variables du store
const goBack = () => {
  authStore.requires2FA = false
  authStore.tempUserId = null
  router.push('/login')
}
</script>

<style scoped>
.twofa-box {
  width: 100%;
  max-width: 400px;
  background: var(--bg-card);
  padding: 40px;
  border-radius: var(--radius-xl);
}

h1 {
  color: var(--text-dark);
  margin: 0;
  text-align: center;
  font-size: 2.2rem;
  font-weight: 800;
  letter-spacing: 0.05em;
}

.subtitle {
  color: var(--text-muted);
  text-align: center;
  margin: 10px 0 30px 0;
  font-size: 1rem;
  font-weight: 600;
}

.form-group {
  margin-bottom: 25px;
}

label {
  display: block;
  color: var(--text-dark);
  margin-bottom: 8px;
  font-size: 0.9rem;
  font-weight: 700;
}

input {
  width: 100%;
  padding: 14px;
  border: 1px solid var(--border-color);
  border-radius: var(--radius-md);
  background: var(--bg-input);
  color: var(--text-dark);
  font-size: 1.5rem;
  letter-spacing: 0.3em;
  text-align: center;
  box-sizing: border-box;
  transition: all 0.2s;
}

input:focus {
  outline: none;
  border-color: var(--primary);
  background: var(--bg-card);
  box-shadow: 0 0 0 3px var(--primary-light);
}

button[type="submit"] {
  width: 100%;
  padding: 14px;
  background: var(--primary);
  color: var(--text-dark);
  border: none;
  border-radius: var(--radius-md);
  font-size: 1rem;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s;
  box-shadow: var(--shadow-md);
}

button[type="submit"]:hover:not(:disabled) {
  background: var(--primary-hover);
  box-shadow: 0 6px 16px rgba(250, 204, 21, 0.35);
}

button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.error {
  color: var(--error);
  text-align: center;
  margin-top: 15px;
  font-weight: 600;
}

.back-btn {
  width: 100%;
  background: var(--bg-input);
  color: var(--text-muted);
  border: 1px solid var(--border-color);
  margin-top: 15px;
  padding: 10px;
  border-radius: var(--radius-md);
  font-size: 0.9rem;
  font-weight: 600;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  cursor: pointer;
  transition: all 0.2s;
}

.back-btn:hover {
  background: var(--bg-card);
  color: var(--text-dark);
  border-color: var(--primary);
}
</style>
