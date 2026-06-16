<template>
  <MainLayout>
    <div class="account-page animate-fade-in">
      <div class="page-header mb-6">
        <h1 class="text-2xl font-extrabold text-dark flex items-center gap-3">
          <span class="p-2 bg-primary rounded-xl shadow-sm"><i class="pi pi-cog text-dark"></i></span>
          Mon Compte
        </h1>
        <p class="text-muted mt-1">Gérez vos informations personnelles et votre mot de passe</p>
      </div>

      <div class="profile-card mb-6">
        <div class="card-header">
          <i class="pi pi-user"></i>
          <span>Informations du compte</span>
        </div>
        <div class="card-body">
          <div class="info-grid">
            <div class="info-item">
              <label>Nom</label>
              <p>{{ authStore.user?.name }}</p>
            </div>
            <div class="info-item">
              <label>Email</label>
              <p>{{ authStore.user?.email }}</p>
            </div>
            <div class="info-item">
              <label>Rôle</label>
              <span class="role-badge" :class="roleClass">{{ authStore.userRole }}</span>
            </div>
            <div class="info-item">
              <label>Agence</label>
              <p>{{ authStore.user?.agence?.nom || '—' }}</p>
            </div>
          </div>
        </div>
      </div>

      <div class="password-card">
        <div class="card-header">
          <i class="pi pi-lock"></i>
          <span>Changer le mot de passe</span>
        </div>
        <div class="card-body">
          <form @submit.prevent="handleSubmit" class="password-form">
            <div class="form-group">
              <label>Mot de passe actuel *</label>
              <div class="input-wrapper">
                <i class="pi pi-lock input-icon"></i>
                <input v-model="form.current_password" type="password" required placeholder="Votre mot de passe actuel" />
              </div>
            </div>

            <div class="form-group">
              <label>Nouveau mot de passe *</label>
              <div class="input-wrapper">
                <i class="pi pi-lock-open input-icon"></i>
                <input v-model="form.new_password" type="password" required placeholder="Au moins 6 caractères" />
              </div>
              <div class="password-rules">
                <span :class="{ valid: rules.minLength }">
                  <i :class="rules.minLength ? 'pi pi-check-circle' : 'pi pi-circle'"></i>
                  Min. 6 caractères
                </span>
                <span :class="{ valid: rules.hasUpper }">
                  <i :class="rules.hasUpper ? 'pi pi-check-circle' : 'pi pi-circle'"></i>
                  Une lettre majuscule
                </span>
                <span :class="{ valid: rules.hasNumber }">
                  <i :class="rules.hasNumber ? 'pi pi-check-circle' : 'pi pi-circle'"></i>
                  Un chiffre
                </span>
              </div>
            </div>

            <div class="form-group">
              <label>Confirmer le nouveau mot de passe *</label>
              <div class="input-wrapper">
                <i class="pi pi-check-circle input-icon"></i>
                <input v-model="form.new_password_confirmation" type="password" required placeholder="Confirmer le mot de passe" />
              </div>
            </div>

            <div v-if="error" class="error-msg">
              <i class="pi pi-exclamation-circle"></i>
              <span>{{ error }}</span>
            </div>

            <button type="submit" class="btn-submit" :disabled="loading || !isFormValid">
              <i v-if="loading" class="pi pi-spin pi-spinner mr-2"></i>
              {{ loading ? 'Modification...' : 'Modifier le mot de passe' }}
            </button>
          </form>
        </div>
      </div>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useAuthStore } from '@/stores/authStore'
import MainLayout from '@/layouts/MainLayout.vue'
import gsap from 'gsap'

const authStore = useAuthStore()

const form = reactive({
  current_password: '',
  new_password: '',
  new_password_confirmation: '',
})

const loading = ref(false)
const error = ref('')

const rules = computed(() => ({
  minLength: form.new_password.length >= 6,
  hasUpper: /[A-Z]/.test(form.new_password),
  hasNumber: /[0-9]/.test(form.new_password),
}))

const isFormValid = computed(() =>
  rules.value.minLength &&
  rules.value.hasUpper &&
  rules.value.hasNumber &&
  form.new_password === form.new_password_confirmation &&
  form.current_password.length > 0
)

const roleClass = computed(() => {
  const classes = {
    super_admin: 'badge-admin',
    gestionnaire_stock_general: 'badge-gestionnaire',
    chef_agence: 'badge-chef',
    gestionnaire_stock: 'badge-gestionnaire',
    technicien_maintenance: 'badge-tech',
    agent: 'badge-agent'
  }
  return classes[authStore.userRole] || ''
})

const handleSubmit = async () => {
  if ((form.new_password !== form.new_password_confirmation)) {
    error.value = 'Les mots de passe ne correspondent pas.'
    return
  }

  if (!isFormValid.value) return

  loading.value = true
  error.value = ''

  try {
    await authStore.changePassword({
      current_password: form.current_password,
      new_password: form.new_password,
    })
    form.current_password = ''
    form.new_password = ''
    form.new_password_confirmation = ''
    alert('Mot de passe modifié avec succès !')
  } catch (err) {
    error.value = err.response?.data?.message || 'Erreur lors de la modification du mot de passe.'
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  gsap.from('.animate-fade-in', { opacity: 0, y: 20, duration: 0.5, ease: 'power2.out' })
})
</script>

<style scoped>
.account-page {
  max-width: 700px;
  margin: 0 auto;
}

.profile-card,
.password-card {
  background: var(--bg-card);
  border: 1px solid var(--border-color);
  border-radius: var(--radius-xl);
  overflow: hidden;
}

.card-header {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 1.25rem 1.75rem;
  border-bottom: 1px solid var(--border-color);
  font-weight: 800;
  font-size: 0.9rem;
  text-transform: uppercase;
  color: var(--text-muted);
  background: var(--bg-app);
}

.card-header i {
  color: var(--primary-hover);
  font-size: 1.1rem;
}

.card-body {
  padding: 1.75rem;
}

.info-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1.5rem;
}

.info-item label {
  display: block;
  font-size: 0.75rem;
  font-weight: 700;
  text-transform: uppercase;
  color: var(--text-muted);
  margin-bottom: 4px;
}

.info-item p {
  margin: 0;
  font-weight: 600;
  color: var(--text-dark);
}

.role-badge {
  display: inline-block;
  padding: 4px 10px;
  border-radius: 20px;
  font-size: 0.65rem;
  text-transform: uppercase;
  font-weight: 800;
}

.badge-admin { background: #fee2e2; color: #ef4444; }
.badge-gestionnaire { background: #fef3c7; color: #f59e0b; }
.badge-chef { background: #ede9fe; color: #8b5cf6; }
.badge-tech { background: #e0f2fe; color: #06b6d4; }
.badge-agent { background: #dcfce7; color: #10b981; }

.password-form {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.form-group label {
  font-weight: 700;
  font-size: 0.85rem;
  color: var(--text-dark);
}

.input-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}

.input-icon {
  position: absolute;
  left: 12px;
  color: var(--text-muted);
  font-size: 0.9rem;
  z-index: 1;
}

.input-wrapper input {
  width: 100%;
  padding: 10px 12px 10px 38px;
  border: 1px solid var(--border-color);
  border-radius: var(--radius-md);
  background: var(--bg-input);
  color: var(--text-dark);
  font-size: 0.9rem;
  box-sizing: border-box;
  transition: border-color 0.2s;
}

.input-wrapper input:focus {
  outline: none;
  border-color: var(--primary);
  background: var(--bg-card);
}

.password-rules {
  display: flex;
  flex-direction: column;
  gap: 4px;
  margin-top: 4px;
}

.password-rules span {
  font-size: 0.75rem;
  color: var(--text-muted);
  display: flex;
  align-items: center;
  gap: 6px;
}

.password-rules span i {
  font-size: 0.65rem;
}

.password-rules span.valid {
  color: #10b981;
}

.password-rules span.valid i {
  color: #10b981;
}

.error-msg {
  background: #fef2f2;
  color: #ef4444;
  padding: 0.85rem 1rem;
  border-radius: var(--radius-md);
  display: flex;
  align-items: center;
  gap: 8px;
  font-weight: 600;
  font-size: 0.85rem;
}

.btn-submit {
  width: 100%;
  padding: 12px;
  background: var(--primary);
  color: var(--text-dark);
  border: none;
  border-radius: var(--radius-md);
  font-size: 0.9rem;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
}

.btn-submit:hover:not(:disabled) {
  opacity: 0.9;
  box-shadow: var(--shadow-md);
}

.btn-submit:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

@media (max-width: 640px) {
  .info-grid {
    grid-template-columns: 1fr;
  }
  .card-body {
    padding: 1.25rem;
  }
}
</style>
