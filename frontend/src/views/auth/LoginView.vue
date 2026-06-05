<<template>
  <div class="login-container">
    <div class="login-box">
      <h1>GESTPARK</h1>
      <p class="subtitle">Gestion du Parc Matériel</p>
      
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
          <input 
            v-model="credentials.password" 
            type="password" 
            placeholder="••••••••"
            required
          />
        </div>
        
        <button type="submit" :disabled="loading">
          {{ loading ? 'Connexion...' : 'Se connecter' }}
        </button>
        
        <p v-if="error" class="error">{{ error }}</p>
      </form>
      
      <div class="demo-accounts">
        <p>Comptes de démo :</p>
        <small>admin@gestpark.local / password123</small>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/authStore'

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
    await authStore.login(credentials.value)
    router.push('/')
  } catch (err) {
    error.value = err.response?.data?.message || 'Erreur de connexion'
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.login-container {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #0f172a;
}

.login-box {
  background: #1e293b;
  padding: 40px;
  border-radius: 12px;
  width: 100%;
  max-width: 400px;
  box-shadow: 0 10px 40px rgba(0,0,0,0.3);
}

h1 {
  color: #3b82f6;
  margin: 0 0 5px 0;
  text-align: center;
}

.subtitle {
  color: #94a3b8;
  text-align: center;
  margin: 0 0 30px 0;
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
  font-size: 1rem;
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

.demo-accounts {
  margin-top: 25px;
  padding-top: 20px;
  border-top: 1px solid #334155;
  text-align: center;
  color: #64748b;
}

.demo-accounts small {
  display: block;
  margin-top: 5px;
}
</style>
