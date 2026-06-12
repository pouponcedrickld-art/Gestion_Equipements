
<template>
  <div class="login-container">
    <div class="login-box">
      <!-- Zone Logo & Titre -->
      <div class="header-section">
        <div class="logo-wrapper">
          <!-- Icône Compteur/Dashboard intégrée en SVG pour être pixel-perfect -->
          <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M12 4C7.58 4 4 7.58 4 12C4 13.1 4.22 14.16 4.63 15.13L6.14 13.62C6.05 13.11 6 12.56 6 12C6 8.69 8.69 6 12 6C15.31 6 18 8.69 18 12C18 15.31 15.31 18 12 18C11.44 18 10.89 17.95 10.38 17.86L8.87 19.37C9.84 19.78 10.9 20 12 20C16.42 20 20 16.42 20 12C20 7.58 16.42 4 12 4ZM12 8C9.79 8 8 9.79 8 12C8 12.38 8.05 12.74 8.16 13.09L10.32 10.93C10.5 10.36 11.04 9.92 11.69 9.92C12.47 9.92 13.1 10.55 13.1 11.33C13.1 11.98 12.66 12.52 12.09 12.7L9.93 14.86C10.28 14.97 10.64 15 11 15C13.21 15 15 13.21 15 12C15 9.79 13.21 8 12 8ZM12 11C12.28 11 12.5 11.22 12.5 11.5C12.5 11.78 12.28 12 12 12C11.72 12 11.5 11.78 11.5 11.5C11.5 11.22 11.72 11 12 11Z" fill="#111827"/>
            <rect x="11" y="16" width="2" height="2" rx="0.5" fill="#111827"/>
            <rect x="7" y="14" width="2" height="2" rx="0.5" fill="#111827"/>
            <rect x="15" y="14" width="2" height="2" rx="0.5" fill="#111827"/>
          </svg>
        </div>
        <h1>GESTPARK</h1>
        <div class="separator"></div>
        <p class="subtitle">Gestion de Matériel</p>
      </div>

      <!-- Formulaire -->
      <form @submit.prevent="handleLogin">
        <div class="form-group">
          <label>Email</label>
          <input 
            v-model="credentials.email" 
            type="email" 
            placeholder="admin@gestpark.local" 
            required 
          />
        </div>

        <div class="form-group">
          <label>Mot de passe</label>
          <div class="password-wrapper">
            <input 
              v-model="credentials.password" 
              type="password" 
              placeholder="••••••••••••" 
              required 
            />
            <!-- Icône Œil pour simuler le visuel de l'image -->
            <span class="toggle-password">
              <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                <circle cx="12" cy="12" r="3"/>
              </svg>
            </span>
          </div>
        </div>

        <button type="submit" :disabled="loading" class="btn-submit">
          {{ loading ? 'Connexion...' : 'Se connecter' }}
        </button>

        <p v-if="error" class="error-msg">{{ error }}</p>
      </form>

      <!-- Section Comptes de démo -->
      <div class="demo-card">
        <h3>Comptes de démo</h3>
        <div class="demo-list">
          <span>admin@gestpark.local</span>
          <span>tech@gestpark.local</span>
          <span>magasinier@gestpark.local</span>
          <span>consult@gestpark.local</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/authStore.js'

const router = useRouter()
const authStore = useAuthStore()

const credentials = ref({
  email: '',
  password: ''
})

const loading = ref(false)
const error = ref('')

const handleLogin = async () => {
  loading.value = true
  error.value = ''

  try {
    const result = await authStore.login(credentials.value)
    if (result && result.requires2FA) {
      router.push('/login/2fa')
    } else {
      router.push('/')
    }
  } catch (err) {
    error.value = err.response?.data?.message || 'Erreur de connexion'
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
/* ==========================================================================
   1. GLOBAL CONTAINER & FONDS (Mobile First)
   ========================================================================== */
.login-container {
  width: 100%;
  min-height: 100vh;
  background-color: #fbf9f4; /* Fond légèrement cassé/crème de l'image */
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1rem;
  box-sizing: border-box;
  font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
}

/* Boîte blanche principale */
.login-box {
  width: 100%;
  background: #ffffff;
  padding: 2.5rem 1.5rem;
  box-sizing: border-box;
}

/* ==========================================================================
   2. EN-TÊTE (Logo, Titre, Sous-titre)
   ========================================================================== */
.header-section {
  display: flex;
  flex-direction: column;
  align-items: center;
  margin-bottom: 2rem;
}

.logo-wrapper {
  width: 72px;
  height: 72px;
  background-color: #facc15; /* Jaune vif du logo */
  border-radius: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 12px;
  box-sizing: border-box;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
  margin-bottom: 1rem;
}

.logo-wrapper svg {
  width: 100%;
  height: 100%;
}

h1 {
  color: #000000;
  font-size: 2.2rem;
  font-weight: 800;
  letter-spacing: 0.05em;
  margin: 0;
  text-align: center;
}

.separator {
  width: 45px;
  height: 1.5px;
  background-color: #78716c;
  margin: 8px 0;
}

.subtitle {
  color: #78716c;
  font-size: 0.95rem;
  font-weight: 400;
  margin: 0;
  text-align: center;
}

/* ==========================================================================
   3. FORMULAIRE & INPUTS
   ========================================================================== */
.form-group {
  margin-bottom: 1.25rem;
}

label {
  display: block;
  color: #000000;
  font-size: 0.9rem;
  font-weight: 600;
  margin-bottom: 6px;
}

.password-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}

input {
  width: 100%;
  padding: 11px 14px;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  background-color: #fafafa;
  color: #111827;
  font-size: 0.95rem;
  box-sizing: border-box;
  transition: all 0.2s ease;
}

input:focus {
  outline: none;
  border-color: #facc15;
  background-color: #ffffff;
  box-shadow: 0 0 0 3px rgba(250, 204, 21, 0.2);
}

.toggle-password {
  position: absolute;
  right: 14px;
  color: #94a3b8;
  display: flex;
  align-items: center;
  cursor: pointer;
}

/* Bouton Se Connecter Jaune */
.btn-submit {
  width: 100%;
  padding: 14px;
  background-color: #facc15;
  color: #000000;
  border: none;
  border-radius: 8px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  margin-top: 1.25rem;
  box-shadow: 0 4px 12px rgba(250, 204, 21, 0.25);
  transition: all 0.2s ease;
}

.btn-submit:hover:not(:disabled) {
  background-color: #eab308;
  box-shadow: 0 6px 16px rgba(250, 204, 21, 0.35);
}

.btn-submit:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.error-msg {
  color: #ef4444;
  font-size: 0.85rem;
  text-align: center;
  margin-top: 10px;
}

/* ==========================================================================
   4. ENCART COMPTES DE DÉMO
   ========================================================================== */
.demo-card {
  background-color: #fefaf0; /* Teinte beige très claire */
  border-radius: 10px;
  padding: 1.25rem;
  margin-top: 2rem;
  box-sizing: border-box;
}

.demo-card h3 {
  margin: 0 0 8px 0;
  color: #44403c;
  font-size: 0.9rem;
  font-weight: 700;
}

.demo-list {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.demo-list span {
  font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
  font-size: 0.85rem;
  color: #57534e;
}

/* ==========================================================================
   5. RESPONSIVE DESIGN (Tablettes & Écrans PC)
   ========================================================================== */
@media (min-width: 640px) {
  .login-box {
    max-width: 460px;
    border-radius: 24px; /* Coins très arrondis du container central */
    padding: 3.5rem 3rem;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.03), 0 10px 10px -5px rgba(0, 0, 0, 0.02);
  }
}
</style>

