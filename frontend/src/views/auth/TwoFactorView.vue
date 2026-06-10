<template>
  <div class="twofa-box">
    <h1>GESTPARK</h1>
    <p class="subtitle">Double authentification</p>

    <form @submit.prevent="handleVerify">
      <div class="form-group">
        <label>Code à 6 chiffres</label>
        <input v-model="code" type="text" maxlength="6" placeholder="000000" required />
      </div>

      <button type="submit" :disabled="loading">
        {{ loading ? 'Vérification...' : 'Vérifier' }}
      </button>

      <p v-if="error" class="error">{{ error }}</p>
    </form>

    <button @click="goBack" class="back-btn">
      <i class="pi pi-arrow-left"></i> Retour
    </button>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/authStore.js'

const router = useRouter()
const authStore = useAuthStore()

const code = ref('')
const loading = ref(false)
const error = ref('')

const handleVerify = async () => {
  loading.value = true
  error.value = ''
  try {
    await authStore.verify2FA(code.value)
    router.push('/')
  } catch (err) {
    error.value = err.response?.data?.message || 'Code invalide'
  } finally {
    loading.value = false
  }
}

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
}

h1 {
  color: #3b82f6;
  margin: 0 0 5px 0;
  text-align: center;
  font-size: 2.5rem;
}

.subtitle {
  color: #94a3b8;
  text-align: center;
  margin: 0 0 30px 0;
  font-size: 1.1rem;
}

.form-group {
  margin-bottom: 20px;
}

label {
  display: block;
  color: #cbd5e1;
  margin-bottom: 8px;
  font-size: 0.9rem;
}

input {
  width: 100%;
  padding: 12px;
  border: 1px solid #334155;
  border-radius: 6px;
  background: #0f172a;
  color: #e2e8f0;
  font-size: 1.2rem;
  letter-spacing: 0.2em;
  text-align: center;
  box-sizing: border-box;
}

input:focus {
  outline: none;
  border-color: #3b82f6;
}

button {
  width: 100%;
  padding: 12px;
  background: #3b82f6;
  color: white;
  border: none;
  border-radius: 6px;
  font-size: 1rem;
  cursor: pointer;
  transition: background 0.2s;
}

button:hover:not(:disabled) {
  background: #2563eb;
}

button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.error {
  color: #ef4444;
  text-align: center;
  margin-top: 15px;
}

.back-btn {
  background: #334155;
  margin-top: 15px;
  font-size: 0.9rem;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
}
</style>
