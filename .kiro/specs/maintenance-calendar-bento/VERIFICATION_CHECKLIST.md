# ✅ Checklist de Vérification - Calendrier de Maintenance

## 🎯 Comment Vérifier que Tout Fonctionne

Suivez cette checklist étape par étape pour vérifier que le calendrier de maintenance est correctement installé et fonctionnel.

---

## 📋 Étape 1 : Vérification Backend

### 1.1 Serveur Backend Démarré
```bash
cd backend
php artisan serve
```

**✅ Attendu** : Message "Laravel development server started"  
**URL** : `http://127.0.0.1:8000`

---

### 1.2 Fichiers Backend Existent

Vérifiez que ces fichiers existent :

#### Requests
- [ ] `backend/app/Http/Requests/MaintenanceRequest.php`

#### Services
- [ ] `backend/app/Services/MaintenanceWorkflowService.php`

#### Controllers
- [ ] `backend/app/Http/Controllers/MaintenanceController.php`

#### Notifications
- [ ] `backend/app/Notifications/AlerteMaintenancePrevue.php`

#### Jobs
- [ ] `backend/app/Jobs/SendAlerteMaintenancePrevueJob.php`

#### Factories
- [ ] `backend/database/factories/MaintenanceFactory.php`
- [ ] `backend/database/factories/EquipementFactory.php` (méthode `garantieExpireDans30Jours()`)

---

### 1.3 Routes API Configurées

Vérifiez dans `backend/routes/api.php` :

- [ ] `GET /api/maintenances`
- [ ] `GET /api/maintenances/{id}`
- [ ] `POST /api/maintenances`

**Test rapide** :
```bash
# Liste des maintenances (remplacez YOUR_TOKEN)
curl http://localhost:8000/api/maintenances?month=2026-06 \
  -H "Authorization: Bearer YOUR_TOKEN"
```

---

### 1.4 Données de Test (Seeder)

```bash
cd backend
php artisan db:seed --class=MaintenanceSeeder
```

**✅ Attendu** : "Seeding: MaintenanceSeeder"  
**Résultat** : 10 maintenances créées pour juin 2026

---

## 📋 Étape 2 : Vérification Frontend

### 2.1 Serveur Frontend Démarré

```bash
cd frontend
npm run dev
```

**✅ Attendu** : Message "Local: http://localhost:5173/"  
**URL** : `http://localhost:5173`

---

### 2.2 Fichiers Frontend Existent

Vérifiez que ces fichiers existent :

#### Utilities
- [ ] `frontend/src/utils/dateFormatter.js`
- [ ] `frontend/src/utils/gsapAnimations.js`
- [ ] `frontend/src/utils/calendarUtils.js`

#### API & Store
- [ ] `frontend/src/api/maintenanceApi.js`
- [ ] `frontend/src/stores/maintenanceStore.js`
- [ ] `frontend/src/composables/useMaintenance.js`

#### Components
- [ ] `frontend/src/components/maintenance/MaintenanceEventCard.vue`
- [ ] `frontend/src/components/maintenance/CalendarDay.vue`
- [ ] `frontend/src/components/maintenance/CalendarGrid.vue`
- [ ] `frontend/src/components/maintenance/MaintenanceDetailsModal.vue`
- [ ] `frontend/src/components/notifications/NotificationCenter.vue`

#### Views
- [ ] `frontend/src/views/agence/maintenances/MaintenancesView.vue`
- [ ] `frontend/src/views/agence/maintenances/MaintenanceCalendarView.vue`

---

### 2.3 Routes Frontend Configurées

Vérifiez dans `frontend/src/router/index.js` :

- [ ] Route `/maintenances` vers `MaintenancesView.vue`
- [ ] Route `/maintenances/calendrier` vers `MaintenanceCalendarView.vue`

---

### 2.4 Menu Navigation Configuré

Vérifiez dans `frontend/src/utils/permissions.js` :

- [ ] Entrée menu `{ label: 'Maintenances', route: '/maintenances' }`
- [ ] Entrée menu `{ label: 'Calendrier', route: '/maintenances/calendrier' }`

---

### 2.5 Dépendances Installées

Vérifiez dans `frontend/package.json` :

- [ ] `"gsap": "^3.15.0"`
- [ ] `"fast-check": "..."` (devDependencies)

Vérifiez dans `backend/composer.json` :

- [ ] `"giorgiosironi/eris": "..."` (require-dev)

---

## 📋 Étape 3 : Test de Navigation

### 3.1 Connexion à l'Application

1. Ouvrez `http://localhost:5173`
2. Connectez-vous avec vos identifiants
3. **✅ Attendu** : Redirection vers le Dashboard

---

### 3.2 Accès au Menu Maintenances

Dans le menu latéral gauche :

- [ ] Lien **"Maintenances"** visible (icône 🔧)
- [ ] Lien **"Calendrier"** visible (icône 📅)

**Si non visible** : Vérifiez votre rôle utilisateur (doit être admin, gestionnaire, ou technicien)

---

### 3.3 Navigation vers Page Maintenances

1. Cliquez sur **"Maintenances"** dans le menu
2. **✅ Attendu** : Page hub avec 3 cartes
   - 📅 Calendrier (ACTIF - bleu)
   - 📋 Liste (grisée)
   - ➕ Nouvelle (grisée)
3. **✅ Attendu** : Section "Statistiques Rapides"
4. **✅ Attendu** : Section "Accès Rapide"

---

### 3.4 Navigation vers Calendrier (Option 1)

Depuis la page Maintenances :

1. Cliquez sur la carte **"📅 Calendrier"**
2. **✅ Attendu** : Redirection vers `/maintenances/calendrier`
3. **✅ Attendu** : Grille calendaire visible

---

### 3.5 Navigation vers Calendrier (Option 2)

Depuis n'importe quelle page :

1. Cliquez sur **"Calendrier"** dans le menu latéral
2. **✅ Attendu** : Accès direct à `/maintenances/calendrier`
3. **✅ Attendu** : Grille calendaire visible

---

## 📋 Étape 4 : Test du Calendrier

### 4.1 Éléments Visibles

Une fois sur `/maintenances/calendrier` :

- [ ] Lien **"← Retour aux Maintenances"** en haut
- [ ] Titre **"📅 Calendrier de Maintenance"**
- [ ] Boutons navigation : **◀ Juin 2026 ▶**
- [ ] Bouton **"Aujourd'hui"**
- [ ] Filtres : **Type** et **Statut** (dropdowns)
- [ ] En-tête jours : **Lun, Mar, Mer, ..., Dim**
- [ ] Grille calendaire (6 semaines × 7 jours)

---

### 4.2 Affichage des Maintenances

Si vous avez exécuté le seeder :

- [ ] Des cartes bleues/oranges/vertes apparaissent dans la grille
- [ ] Les cartes montrent l'heure (ex: 10:00)
- [ ] Les cartes montrent la référence équipement (ex: EQ-2024-001)
- [ ] Les cartes ont un badge statut (🔵 Planifié, 🟠 En cours, 🟢 Terminé)

**Si aucune carte visible** :
1. Vérifiez que vous êtes en **juin 2026** (le seeder génère pour ce mois)
2. Naviguez avec ◀ ▶ pour trouver juin 2026
3. Vérifiez que le backend répond (F12 > Network > `/api/maintenances`)

---

### 4.3 Navigation Mois

- [ ] Clic **◀** → Mois précédent (mai 2026)
- [ ] Clic **▶** → Mois suivant (juillet 2026)
- [ ] Clic **"Aujourd'hui"** → Retour au mois actuel (juin 2026)

**✅ Attendu** : La grille se met à jour avec animation

---

### 4.4 Filtres

#### Filtre Type
1. Sélectionnez **"Préventif"** dans le dropdown Type
2. **✅ Attendu** : Seules les maintenances préventives s'affichent (icône 🛡️)

3. Sélectionnez **"Correctif"**
4. **✅ Attendu** : Seules les maintenances correctives s'affichent (icône 🔧)

#### Filtre Statut
1. Sélectionnez **"Planifié"**
2. **✅ Attendu** : Seules les cartes bleues s'affichent

3. Sélectionnez **"En cours"**
4. **✅ Attendu** : Seules les cartes oranges s'affichent

---

### 4.5 Interaction avec les Cartes

1. **Hover** sur une carte
2. **✅ Attendu** : Légère élévation (scale 1.02) + ombre plus prononcée

3. **Clic** sur une carte
4. **✅ Attendu** : Modal s'ouvre avec animation

---

## 📋 Étape 5 : Test de la Modal

### 5.1 Ouverture Modal

Après avoir cliqué sur une carte :

- [ ] Modal apparaît avec animation (fade + scale)
- [ ] Backdrop semi-transparent derrière la modal
- [ ] Bouton **❌** en haut à droite
- [ ] Titre : **"Maintenance Préventive"** ou **"Corrective"**
- [ ] Badge statut coloré (🔵/🟠/🟢)

---

### 5.2 Contenu Modal

Vérifiez que ces sections sont affichées :

- [ ] **📅 Dates** : Date prévue, durée estimée
- [ ] **👥 Responsables** : Nom du responsable
- [ ] **📦 Équipement** : Référence, marque, modèle
- [ ] **💰 Coût** : Montant en XOF (si renseigné)
- [ ] **📝 Observations** : Texte libre (si renseigné)

---

### 5.3 Fermeture Modal

Testez les 3 méthodes de fermeture :

1. **Clic sur ❌** → Modal se ferme avec animation
2. **Clic sur backdrop** (zone grise) → Modal se ferme
3. **Touche Esc** → Modal se ferme

**✅ Attendu** : Dans tous les cas, retour à la grille calendaire

---

## 📋 Étape 6 : Test NotificationCenter

### 6.1 Icône Cloche

Dans la barre supérieure (top bar) :

- [ ] Icône **🔔** visible en haut à droite
- [ ] Badge rouge avec chiffre **(2)** si notifications non lues

---

### 6.2 Ouverture Panel

1. Cliquez sur **🔔**
2. **✅ Attendu** : Panel slide-in depuis la droite (animation GSAP)
3. **✅ Attendu** : 3 notifications mockées :
   - 🔧 Maintenance prévue
   - ⚠️ Nouvelle panne déclarée
   - ✉️ Transfert approuvé

---

### 6.3 Fermeture Panel

1. Cliquez sur **❌** dans le panel
2. **✅ Attendu** : Panel slide-out vers la droite

OU

1. Cliquez sur le backdrop (zone sombre)
2. **✅ Attendu** : Panel se ferme

---

## 📋 Étape 7 : Test Responsive (Mobile)

### 7.1 Ouvrir DevTools

1. Appuyez sur **F12**
2. Cliquez sur l'icône **Toggle device toolbar** (ou Ctrl+Shift+M)
3. Sélectionnez **iPhone 12** ou **iPad**

---

### 7.2 Vérifications Mobile

- [ ] Menu devient hamburger **☰**
- [ ] Grille calendaire reste lisible (scroll horizontal si nécessaire)
- [ ] Cartes événements visibles et cliquables
- [ ] Modal occupe **100% de l'écran** (plein écran)
- [ ] Boutons suffisamment grands pour toucher (min 44px)

---

## 📋 Étape 8 : Test Accessibilité

### 8.1 Navigation Clavier

1. Appuyez sur **Tab** plusieurs fois
2. **✅ Attendu** : Focus visible sur les éléments interactifs
   - Boutons navigation (◀ ▶ Aujourd'hui)
   - Filtres (dropdowns)
   - Cartes événements

3. Sur une carte, appuyez sur **Enter**
4. **✅ Attendu** : Modal s'ouvre

5. Dans la modal ouverte, appuyez sur **Esc**
6. **✅ Attendu** : Modal se ferme

---

### 8.2 Attributs ARIA

Inspectez le DOM (F12 > Elements) :

- [ ] Boutons navigation ont `aria-label="Mois précédent"`, etc.
- [ ] Cartes événements ont `role="button"` et `tabindex="0"`

---

## 📋 Étape 9 : Test Performance

### 9.1 Temps de Chargement

1. Ouvrez **F12 > Network**
2. Rechargez la page (`Ctrl+R`)
3. **✅ Attendu** : Chargement complet **< 2 secondes**

---

### 9.2 Animations Fluides

1. Ouvrez **F12 > Performance** (ou Rendering > Frame Rate)
2. Naviguez entre les mois
3. **✅ Attendu** : **60 FPS** constant
4. Ouvrez/fermez des modals
5. **✅ Attendu** : Animations sans saccades

---

### 9.3 Mémoire

1. Ouvrez **F12 > Memory**
2. Naviguez dans le calendrier pendant 30 secondes
3. **✅ Attendu** : Pas de fuite mémoire (courbe stable)

---

## 📋 Étape 10 : Test Console

### 10.1 Aucune Erreur Console

1. Ouvrez **F12 > Console**
2. Naviguez dans toute l'application
3. **✅ Attendu** : **Aucune erreur rouge**

**Warnings acceptables** :
- Messages de développement Vue
- Avertissements CORS (si backend différent domaine)

**Erreurs NON acceptables** :
- `404 Not Found` sur API
- `Uncaught TypeError`
- `Failed to fetch`

---

## 📋 Étape 11 : Test Retour Navigation

### 11.1 Depuis Calendrier vers Maintenances

1. Sur `/maintenances/calendrier`
2. Cliquez **"← Retour aux Maintenances"**
3. **✅ Attendu** : Redirection vers `/maintenances` (hub)

---

### 11.2 Breadcrumb Visible

- [ ] Lien retour présent en haut de la page calendrier
- [ ] Hover sur le lien change la couleur
- [ ] Clic redirige correctement

---

## 📋 Étape 12 : Test Cache Store

### 12.1 Vérification Cache

1. Ouvrez le calendrier (charge les données)
2. Ouvrez **F12 > Console**
3. Tapez : `window.$pinia.state.value.maintenance.cache`
4. **✅ Attendu** : Objet non vide avec clés `YYYY-MM-DD_YYYY-MM-DD`

---

### 12.2 TTL Cache (5 minutes)

1. Chargez le calendrier
2. Attendez **6 minutes**
3. Naviguez à nouveau vers le même mois
4. **✅ Attendu** : Nouvelle requête API (visible dans Network)

---

## 🎯 Résumé Final

Si **toutes les cases** sont cochées ✅, votre calendrier de maintenance est **100% fonctionnel** !

### Checklist Globale

- [ ] ✅ Backend démarré et répond
- [ ] ✅ Frontend démarré et accessible
- [ ] ✅ Routes configurées
- [ ] ✅ Menu navigation visible
- [ ] ✅ Page Maintenances hub accessible
- [ ] ✅ Calendrier accessible (2 chemins)
- [ ] ✅ Grille calendaire s'affiche
- [ ] ✅ Maintenances visibles (si seedées)
- [ ] ✅ Navigation mois fonctionne
- [ ] ✅ Filtres fonctionnent
- [ ] ✅ Modal s'ouvre/ferme
- [ ] ✅ Modal affiche toutes les infos
- [ ] ✅ NotificationCenter fonctionne
- [ ] ✅ Responsive mobile OK
- [ ] ✅ Accessibilité clavier OK
- [ ] ✅ Aucune erreur console
- [ ] ✅ Animations fluides (60fps)
- [ ] ✅ Retour navigation fonctionne

---

## 🐛 Dépannage Rapide

### Problème : Calendrier vide
**Solution** : 
```bash
cd backend
php artisan db:seed --class=MaintenanceSeeder
```
Puis rechargez le frontend

---

### Problème : Erreur 404 sur API
**Solution** :
1. Vérifiez que le backend tourne : `http://localhost:8000`
2. Vérifiez les routes : `php artisan route:list | grep maintenance`

---

### Problème : Animations saccadées
**Solution** :
1. Fermez autres applications lourdes
2. Vérifiez GPU accéléré dans navigateur
3. Testez dans navigateur différent

---

### Problème : Menu ne s'affiche pas
**Solution** :
1. Vérifiez votre rôle utilisateur
2. Relisez `frontend/src/utils/permissions.js`
3. Vérifiez console pour erreurs JavaScript

---

## ✅ Validation Finale

**Si tout fonctionne correctement, vous devriez pouvoir :**

1. ✅ Accéder au calendrier en **1 clic** depuis le menu
2. ✅ Voir les maintenances **colorées par statut**
3. ✅ Naviguer entre les mois **sans lag**
4. ✅ Ouvrir les détails d'une maintenance **instantanément**
5. ✅ Filtrer par type et statut **en temps réel**
6. ✅ Utiliser l'application sur **mobile** sans problème
7. ✅ Naviguer au clavier **de A à Z**
8. ✅ Revenir en arrière **facilement**

---

**🎉 Félicitations ! Votre calendrier de maintenance est opérationnel !**

*Pour plus de détails, consultez :*
- `QUICK_START.md` - Guide de démarrage
- `NAVIGATION_GUIDE.md` - Guide de navigation
- `VISUAL_STRUCTURE.md` - Structure visuelle
- `IMPLEMENTATION_COMPLETE.md` - Documentation technique
