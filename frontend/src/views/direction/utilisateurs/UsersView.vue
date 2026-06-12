<template>
  <DirectionLayout>
    <div class="users-page animate-in">
      <div class="page-header">
        <div class="page-title-section">
          <div class="flex items-center gap-3">
            <div class="icon-box bg-primary-light">
              <i class="pi pi-users text-primary-hover text-xl"></i>
            </div>
            <div>
              <h1 class="text-2xl font-extrabold text-dark">Gestion des Utilisateurs</h1>
              <p class="text-muted text-sm">Gérez les accès et les rôles de votre équipe</p>
            </div>
          </div>
        </div>
        <div class="page-actions">
          <button @click="showForm = true" class="btn btn-primary btn-md">
            <i class="pi pi-plus"></i> Nouvel Utilisateur
          </button>
        </div>
      </div>

      <div class="filters-bar card mb-6 flex items-center justify-between">
        <div class="flex items-center gap-4 flex-1">
          <div class="search-wrapper flex-1 max-w-md relative">
            <i class="pi pi-search absolute left-4 top-1/2 -translate-y-1/2 text-muted"></i>
            <input v-model="search" placeholder="Rechercher par nom ou email..." class="pl-10" />
          </div>
          <select v-model="filterRole" class="max-w-xs">
            <option value="">Tous les rôles</option>
            <option value="super_admin">Super Admin</option>
            <option value="gestionnaire_stock_general">G. Stock Général</option>
            <option value="chef_agence">Chef d'Agence</option>
            <option value="gestionnaire_stock">G. Stock Local</option>
            <option value="technicien_maintenance">Technicien</option>
            <option value="agent">Agent</option>
          </select>
        </div>
      </div>

      <div v-if="userStore.loading" class="flex flex-col items-center justify-center py-20 opacity-50">
        <i class="pi pi-spin pi-spinner text-4xl mb-4"></i>
        <p class="font-bold">Chargement des utilisateurs...</p>
      </div>

      <div v-else class="card p-0 overflow-hidden">
        <table class="grid-table">
          <thead>
            <tr>
              <th>Utilisateur</th>
              <th>Email</th>
              <th>Rôle</th>
              <th>Agence</th>
              <th>Statut</th>
              <th class="text-right">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="user in filteredUsers" :key="user.id" :class="{ 'opacity-60': !user.actif }">
              <td>
                <div class="user-cell">
                  <div class="avatar">{{ user.name.charAt(0).toUpperCase() }}</div>
                  <div>
                    <div class="font-bold text-dark">{{ user.name }}</div>
                    <small class="text-muted" v-if="user.poste">{{ user.poste }}</small>
                  </div>
                </div>
              </td>
              <td><span class="text-sm">{{ user.email }}</span></td>
              <td><span class="role-badge" :class="user.roles?.[0]?.name">{{ getRoleLabel(user.roles?.[0]?.name) }}</span></td>
              <td><span class="text-sm font-medium">{{ user.agence?.nom || '—' }}</span></td>
              <td>
                <span class="status-pill" :class="user.actif ? 'status-active' : 'status-danger'">
                  {{ user.actif ? 'Actif' : 'Inactif' }}
                </span>
              </td>
              <td>
                <div class="flex justify-end gap-2">
                  <button @click="editUser(user)" class="btn btn-outline btn-xs" title="Modifier">
                    <i class="pi pi-pencil"></i>
                  </button>
                  <button @click="toggleUser(user)" class="btn btn-xs" :class="user.actif ? 'btn-secondary' : 'btn-success'" :title="user.actif ? 'Désactiver' : 'Activer'">
                    <i :class="user.actif ? 'pi pi-pause' : 'pi pi-play'"></i>
                  </button>
                  <button @click="deleteUser(user.id)" class="btn btn-danger btn-xs" title="Supprimer">
                    <i class="pi pi-trash"></i>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Modal Creation/Edition -->
      <div v-if="showForm" class="modal-overlay" @click.self="showForm = false">
        <div class="modal-content-wrapper animate-in">
          <UserFormView :edit-data="editingUser" :agences="agenceStore.agences" @saved="onSaved" @cancel="showForm = false" />
        </div>
      </div>
    </div>
  </DirectionLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useUserStore } from '@/stores/userStore'
import { useAgenceStore } from '@/stores/agenceStore'
import DirectionLayout from '@/layouts/DirectionLayout.vue'
import UserFormView from './UserFormView.vue'

const userStore = useUserStore()
const agenceStore = useAgenceStore()
const showForm = ref(false)
const editingUser = ref(null)
const search = ref('')
const filterRole = ref('')

onMounted(() => { userStore.fetchUsers(); agenceStore.fetchAgences() })

const filteredUsers = computed(() => {
  let u = userStore.users
  if (search.value) { const s = search.value.toLowerCase(); u = u.filter(x => x.name.toLowerCase().includes(s) || x.email.toLowerCase().includes(s)) }
  if (filterRole.value) u = u.filter(x => x.roles?.some(r => r.name === filterRole.value))
  return u
})

const getRoleLabel = (role) => {
  const labels = {
    super_admin: 'Super Admin',
    gestionnaire_stock_general: 'Stock Général',
    chef_agence: 'Chef d\'Agence',
    gestionnaire_stock: 'Stock Local',
    technicien_maintenance: 'Technicien',
    agent: 'Agent'
  }
  return labels[role] || role
}

const editUser = (u) => { editingUser.value = { ...u, role: u.roles?.[0]?.name }; showForm.value = true }
const toggleUser = async (u) => { await userStore.toggleActif(u.id) }
const deleteUser = async (id) => { if (!confirm('Voulez-vous vraiment supprimer cet utilisateur ?')) return; await userStore.deleteUser(id) }
const onSaved = () => { showForm.value = false; editingUser.value = null; userStore.fetchUsers() }
</script>

<style scoped>
.users-page { padding: 1rem; }

.icon-box {
  width: 48px;
  height: 48px;
  border-radius: var(--radius-md);
  display: flex;
  align-items: center;
  justify-content: center;
}

.search-wrapper input {
  width: 100%;
  padding: 10px 15px 10px 40px;
  border: 1px solid var(--border-color);
  border-radius: var(--radius-md);
  background: var(--bg-input);
}

.filters-bar {
  padding: 1.25rem 1.5rem;
}

.user-cell { display: flex; align-items: center; gap: 12px; }
.avatar { 
  width: 40px; 
  height: 40px; 
  background: var(--primary-light); 
  border: 2px solid var(--primary);
  border-radius: 50%; 
  display: flex; 
  align-items: center; 
  justify-content: center; 
  color: var(--text-dark); 
  font-weight: 800; 
  font-size: 1rem; 
}

.role-badge { 
  padding: 4px 10px; 
  border-radius: 6px; 
  font-size: 0.7rem; 
  font-weight: 800; 
  text-transform: uppercase;
  display: inline-block;
}

.role-badge.super_admin { background: #fee2e2; color: #ef4444; }
.role-badge.gestionnaire_stock_general { background: #fef3c7; color: #f59e0b; }
.role-badge.chef_agence { background: #ede9fe; color: #8b5cf6; }
.role-badge.gestionnaire_stock { background: #e0f2fe; color: #0ea5e9; }
.role-badge.technicien_maintenance { background: #dcfce7; color: #10b981; }
.role-badge.agent { background: #f1f5f9; color: #64748b; }

.modal-overlay { 
  position: fixed; 
  inset: 0; 
  background: rgba(0,0,0,0.4); 
  backdrop-filter: blur(4px);
  display: flex; 
  align-items: center; 
  justify-content: center; 
  z-index: 1000; 
}

.modal-content-wrapper {
  width: 100%;
  max-width: 600px;
  padding: 20px;
}

.animate-in {
  animation: fadeIn 0.4s ease-out;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}

.opacity-60 { opacity: 0.6; }
</style>
