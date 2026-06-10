# 🚀 Guide de Démarrage Rapide - Calendrier de Maintenance

## ⚡ Démarrer en 5 Minutes

### 1. Lancer le Backend (Laravel)
```bash
cd backend
php artisan serve
```
Le backend sera accessible sur `http://localhost:8000`

### 2. Lancer le Frontend (Vue.js)
```bash
cd frontend
npm run dev
```
Le frontend sera accessible sur `http://localhost:5173`

### 3. Se Connecter
1. Ouvrez `http://localhost:5173`
2. Connectez-vous avec vos identifiants
3. Cliquez sur **"Calendrier"** 📅 dans le menu latéral

---

## 📊 Voir des Données de Test

### Option 1 : Utiliser le Seeder Existant
```bash
cd backend
php artisan db:seed --class=MaintenanceSeeder
```

Cela créera 10 maintenances de test pour **juin 2026**.

### Option 2 : Créer Manuellement via l'API

#### Créer une maintenance préventive
```bash
curl -X POST http://localhost:8000/api/maintenances \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{
    "equipement_id": 1,
    "date_prevue": "2026-06-15 10:00:00",
    "responsable": "Jean Dupont",
    "type_maintenance": "preventive",
    "duree_estimee": 2,
    "cout": 150.00,
    "observations": "Maintenance préventive annuelle"
  }'
```

#### Créer une maintenance corrective
```bash
curl -X POST http://localhost:8000/api/maintenances \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{
    "equipement_id": 2,
    "date_prevue": "2026-06-20 14:30:00",
    "responsable": "Marie Martin",
    "type_maintenance": "corrective",
    "duree_estimee": 3,
    "cout": 250.00,
    "observations": "Réparation suite à panne"
  }'
```

---

## 🎯 Fonctionnalités Clés à Tester

### 1. Navigation du Calendrier
- Cliquez sur **◀️** pour voir le mois précédent
- Cliquez sur **▶️** pour voir le mois suivant
- Cliquez sur **"Aujourd'hui"** pour revenir au mois actuel

### 2. Filtres
Testez les filtres en haut à droite :
- **Type** : Préventif / Correctif / Tous
- **Statut** : Planifié / En cours / Terminé / Tous

### 3. Voir les Détails
- Cliquez sur n'importe quelle carte de maintenance
- La modal s'ouvre avec tous les détails
- Fermez avec le bouton ❌, le backdrop, ou touche `Esc`

### 4. NotificationCenter
- Cliquez sur l'icône 🔔 en haut à droite
- Le panneau s'ouvre avec les notifications
- Le badge rouge indique le nombre de notifications non lues

---

## 🎨 Personnalisation Rapide

### Changer les Couleurs de Statut
Éditez `frontend/src/components/maintenance/MaintenanceEventCard.vue` :

```javascript
const statusColor = computed(() => {
  const colors = {
    planifiee: {
      bar: 'bg-blue-500',      // 👈 Changez ici
      badge: 'bg-blue-100 text-blue-700',
      icon: 'text-blue-600'
    },
    // ... autres statuts
  }
  return colors[props.maintenance.statut] || colors.planifiee
})
```

### Modifier les Labels de Statut
Éditez `frontend/src/components/maintenance/MaintenanceEventCard.vue` :

```javascript
const statusText = computed(() => {
  const statusMap = {
    planifiee: 'Prévu',      // 👈 Changez ici
    en_cours: 'En cours',
    terminee: 'Fait'
  }
  return statusMap[props.maintenance.statut] || 'Inconnu'
})
```

### Changer la Durée du Cache
Éditez `frontend/src/stores/maintenanceStore.js` :

```javascript
const CACHE_TTL = 5 * 60 * 1000  // 👈 5 minutes par défaut
```

---

## 🐛 Dépannage

### Le calendrier est vide
**Cause** : Pas de données dans la base  
**Solution** : Exécutez le seeder
```bash
php artisan db:seed --class=MaintenanceSeeder
```

### Les maintenances ne s'affichent pas
**Cause 1** : Problème de permissions (agence scope)  
**Solution** : Vérifiez que l'utilisateur a le bon rôle

**Cause 2** : Mauvais mois sélectionné  
**Solution** : Vérifiez que les maintenances sont en juin 2026

### Erreur 401 Unauthorized
**Cause** : Token expiré ou manquant  
**Solution** : Reconnectez-vous

### Erreur 404 sur l'API
**Cause** : Backend non démarré  
**Solution** : Vérifiez que `php artisan serve` tourne

### Les filtres ne fonctionnent pas
**Cause** : Cache navigateur  
**Solution** : Videz le cache (`Ctrl + Shift + R`)

---

## 📱 Test sur Mobile

### 1. Trouver votre IP locale
```bash
ipconfig  # Windows
```
Notez votre adresse IPv4 (ex: 192.168.1.100)

### 2. Modifier Vite Config
Éditez `frontend/vite.config.js` :
```javascript
export default defineConfig({
  server: {
    host: '0.0.0.0',  // 👈 Ajoutez ceci
    port: 5173
  }
})
```

### 3. Accéder depuis Mobile
Sur votre téléphone, ouvrez :
```
http://192.168.1.100:5173
```

---

## ⌨️ Raccourcis Clavier

| Touche | Action |
|--------|--------|
| `Tab` | Naviguer entre les éléments |
| `Enter` | Ouvrir une maintenance |
| `Esc` | Fermer la modal |
| `←` / `→` | Naviguer les mois (focus sur boutons) |

---

## 📊 Monitoring

### Vérifier les Logs Backend
```bash
cd backend
tail -f storage/logs/laravel.log
```

### Vérifier les Erreurs Frontend
Ouvrez la console navigateur (`F12` > Console)

### Tester les Endpoints API

#### Liste des maintenances
```bash
curl http://localhost:8000/api/maintenances?month=2026-06 \
  -H "Authorization: Bearer YOUR_TOKEN"
```

#### Détails d'une maintenance
```bash
curl http://localhost:8000/api/maintenances/1 \
  -H "Authorization: Bearer YOUR_TOKEN"
```

---

## 🎯 Checklist de Validation

Après le démarrage, vérifiez :

- [ ] ✅ Backend démarré (port 8000)
- [ ] ✅ Frontend démarré (port 5173)
- [ ] ✅ Connexion réussie
- [ ] ✅ Menu "Calendrier" visible
- [ ] ✅ Grille calendaire s'affiche
- [ ] ✅ Maintenances visibles (si seedées)
- [ ] ✅ Navigation mois fonctionne
- [ ] ✅ Filtres fonctionnent
- [ ] ✅ Modal s'ouvre au clic
- [ ] ✅ NotificationCenter fonctionne
- [ ] ✅ Animations fluides

---

## 🚀 Prêt !

Votre calendrier de maintenance est maintenant opérationnel.

**Accès direct** : `http://localhost:5173/maintenances/calendrier`

**Besoin d'aide ?** Consultez `IMPLEMENTATION_COMPLETE.md` pour plus de détails.

---

*Bon développement ! 🎉*
