<template>
<<<<<<< HEAD:frontend/src/views/users/UsersView.vue
  <div class="users-page">
    <div class="header">
      <h2><i class="pi pi-users"></i> Utilisateurs</h2>
      <button @click="showForm = true" class="btn-primary">
        <i class="pi pi-plus"></i> Nouveau
      </button>
    </div>

    <div class="filters">
      <input v-model="search" placeholder="Rechercher..." class="search-input" />
      <select v-model="filterRole" class="filter-select">
        <option value="">Tous les rôles</option>
        <option value="super_admin">Super Admin</option>
        <option value="gestionnaire_stock_general">G. Stock Général</option>
        <option value="chef_agence">Chef d'Agence</option>
        <option value="gestionnaire_stock">G. Stock Local</option>
        <option value="technicien_maintenance">Technicien</option>
        <option value="agent">Agent</option>
      </select>
    </div>

    <div v-if="userStore.loading" class="loading">
      <i class="pi pi-spin pi-spinner"></i> Chargement...
    </div>

    <table v-else class="data-table">
      <thead>
        <tr>
          <th>Nom</th><th>Email</th><th>Rôle</th><th>Agence</th><th>Statut</th><th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="user in filteredUsers" :key="user.id" :class="{ inactive: !user.actif }">
          <td>
            <div class="user-cell">
              <div class="avatar">{{ user.name.charAt(0).toUpperCase() }}</div>
              <div><strong>{{ user.name }}</strong><small v-if="user.poste">{{ user.poste }}</small></div>
            </div>
          </td>
          <td>{{ user.email }}</td>
          <td><span class="role-badge" :class="user.roles?.[0]?.name">{{ user.roles?.[0]?.name }}</span></td>
          <td>{{ user.agence?.nom || '—' }}</td>
          <td><span class="status-badge" :class="user.actif ? 'active' : 'inactive'">{{ user.actif ? 'Actif' : 'Inactif' }}</span></td>
          <td>
            <div class="actions">
              <button @click="editUser(user)" class="btn-icon"><i class="pi pi-pencil"></i></button>
              <button @click="toggleUser(user)" class="btn-icon" :class="user.actif ? 'btn-warn' : 'btn-success'"><i :class="user.actif ? 'pi pi-pause' : 'pi pi-play'"></i></button>
              <button @click="deleteUser(user.id)" class="btn-icon btn-danger"><i class="pi pi-trash"></i></button>
            </div>
          </td>
        </tr>
      </tbody>
    </table>

    <div v-if="showForm" class="modal-overlay" @click.self="showForm = false">
      <UserFormView :edit-data="editingUser" :agences="agenceStore.agences" @saved="onSaved" @cancel="showForm = false" />
    </div>
  </div>
=======
  <DirectionLayout>
    <div class="users-page">
      <div class="header">
        <h2><i class="pi pi-users"></i> Utilisateurs</h2>
        <button @click="showForm = true" class="btn-primary">
          <i class="pi pi-plus"></i> Nouveau
        </button>
      </div>

      <div class="filters">
        <input v-model="search" placeholder="Rechercher..." class="search-input" />
        <select v-model="filterRole" class="filter-select">
          <option value="">Tous les rôles</option>
          <option value="super_admin">Super Admin</option>
          <option value="gestionnaire_stock_general">G. Stock Général</option>
          <option value="chef_agence">Chef d'Agence</option>
          <option value="gestionnaire_stock">G. Stock Local</option>
          <option value="technicien_maintenance">Technicien</option>
          <option value="agent">Agent</option>
        </select>
      </div>

      <div v-if="userStore.loading" class="loading">
        <i class="pi pi-spin pi-spinner"></i> Chargement...
      </div>

      <table v-else class="data-table">
        <thead>
          <tr>
            <th>Nom</th><th>Email</th><th>Rôle</th><th>Agence</th><th>Statut</th><th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="user in filteredUsers" :key="user.id" :class="{ inactive: !user.actif }">
            <td>
              <div class="user-cell">
                <div class="avatar">{{ user.name.charAt(0).toUpperCase() }}</div>
                <div><strong>{{ user.name }}</strong><small v-if="user.poste">{{ user.poste }}</small></div>
              </div>
            </td>
            <td>{{ user.email }}</td>
            <td><span class="role-badge" :class="user.roles?.[0]?.name">{{ user.roles?.[0]?.name }}</span></td>
            <td>{{ user.agence?.nom || '—' }}</td>
            <td><span class="status-badge" :class="user.actif ? 'active' : 'inactive'">{{ user.actif ? 'Actif' : 'Inactif' }}</span></td>
            <td>
              <div class="actions">
                <button @click="editUser(user)" class="btn-icon"><i class="pi pi-pencil"></i></button>
                <button @click="toggleUser(user)" class="btn-icon" :class="user.actif ? 'btn-warn' : 'btn-success'"><i :class="user.actif ? 'pi pi-pause' : 'pi pi-play'"></i></button>
                <button @click="deleteUser(user.id)" class="btn-icon btn-danger"><i class="pi pi-trash"></i></button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>

      <div v-if="showForm" class="modal-overlay" @click.self="showForm = false">
        <UserFormView :edit-data="editingUser" :agences="agenceStore.agences" @saved="onSaved" @cancel="showForm = false" />
      </div>
    </div>
  </DirectionLayout>
>>>>>>> 2c965af4f2361eccbef055db409105b763f2340d:frontend/src/views/direction/utilisateurs/UsersView.vue
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
<<<<<<< HEAD:frontend/src/views/users/UsersView.vue
import { useUserStore } from '@/stores/userStore.js'
import { useAgenceStore } from '@/stores/agenceStore.js'
=======
import { useUserStore } from '@/stores/userStore'
import { useAgenceStore } from '@/stores/agenceStore'
import DirectionLayout from '@/layouts/DirectionLayout.vue'
>>>>>>> 2c965af4f2361eccbef055db409105b763f2340d:frontend/src/views/direction/utilisateurs/UsersView.vue
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

const editUser = (u) => { editingUser.value = { ...u, role: u.roles?.[0]?.name }; showForm.value = true }
const toggleUser = async (u) => { await userStore.toggleActif(u.id) }
const deleteUser = async (id) => { if (!confirm('Supprimer ?')) return; await userStore.deleteUser(id) }
const onSaved = () => { showForm.value = false; editingUser.value = null; userStore.fetchUsers() }
</script>

<style scoped>
.users-page { padding: 20px; }
.header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
.header h2 { color: #e2e8f0; margin: 0; }
.filters { display: flex; gap: 15px; margin-bottom: 20px; }
.search-input { flex: 1; padding: 10px 15px; border: 1px solid #334155; border-radius: 6px; background: #0f172a; color: #e2e8f0; }
.filter-select { padding: 10px; border: 1px solid #334155; border-radius: 6px; background: #0f172a; color: #e2e8f0; min-width: 180px; }
.data-table { width: 100%; border-collapse: collapse; }
.data-table th { text-align: left; padding: 12px 15px; color: #94a3b8; font-weight: 600; border-bottom: 1px solid #334155; font-size: 0.85rem; text-transform: uppercase; }
.data-table td { padding: 12px 15px; border-bottom: 1px solid #1e293b; color: #e2e8f0; }
.data-table tr:hover { background: #1e293b; }
.data-table tr.inactive { opacity: 0.5; }
.user-cell { display: flex; align-items: center; gap: 12px; }
.avatar { width: 36px; height: 36px; background: #3b82f6; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 0.9rem; }
.user-cell div { display: flex; flex-direction: column; }
.user-cell small { color: #64748b; font-size: 0.8rem; }
.role-badge { padding: 3px 10px; border-radius: 4px; font-size: 0.75rem; font-weight: bold; text-transform: uppercase; }
.role-badge.super_admin { background: #ef4444; color: white; }
.role-badge.gestionnaire_stock_general { background: #f59e0b; color: white; }
.role-badge.chef_agence { background: #8b5cf6; color: white; }
.role-badge.gestionnaire_stock { background: #06b6d4; color: white; }
.role-badge.technicien_maintenance { background: #10b981; color: white; }
.role-badge.agent { background: #64748b; color: white; }
.status-badge { padding: 3px 10px; border-radius: 4px; font-size: 0.75rem; }
.status-badge.active { background: rgba(16, 185, 129, 0.2); color: #10b981; }
.status-badge.inactive { background: rgba(239, 68, 68, 0.2); color: #ef4444; }
.actions { display: flex; gap: 6px; }
.btn-icon { background: #334155; border: none; color: #e2e8f0; padding: 6px 10px; border-radius: 4px; cursor: pointer; }
.btn-warn:hover { background: #f59e0b; }
.btn-success:hover { background: #10b981; }
.btn-danger:hover { background: #ef4444; }
.modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.7); display: flex; align-items: center; justify-content: center; z-index: 100; }
.loading { text-align: center; color: #94a3b8; padding: 40px; }
</style>
