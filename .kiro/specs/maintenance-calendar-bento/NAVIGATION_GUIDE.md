# 🧭 Guide de Navigation - Calendrier de Maintenance

## 📍 Chemins d'Accès

### 1️⃣ Via le Menu Principal (Sidebar)

```
Menu Latéral → Calendrier 📅
```

**Chemin direct** : Cliquez sur **"Calendrier"** dans le menu de gauche

**Rôles autorisés** :
- ✅ super_admin
- ✅ gestionnaire_stock_general
- ✅ technicien_maintenance
- ✅ gestionnaire_stock

---

### 2️⃣ Via la Page Maintenances

```
Menu Latéral → Maintenances 🔧 → Carte "Calendrier" 📅
```

**Parcours** :
1. Cliquez sur **"Maintenances"** dans le menu latéral
2. Sur la page d'accueil des maintenances, cliquez sur la grande carte **"📅 Calendrier"**
3. Vous arrivez sur le calendrier

---

### 3️⃣ Via URL Directe

```
http://localhost:5173/maintenances/calendrier
```

Tapez directement cette URL dans votre navigateur (si connecté)

---

## 🔄 Navigation Entre les Pages

### Depuis le Calendrier

**Retour à la page Maintenances** :
- Cliquez sur **"← Retour aux Maintenances"** en haut à gauche du calendrier

**Retour au Dashboard** :
- Cliquez sur **"Dashboard"** dans le menu latéral

**Accès aux autres modules** :
- Utilisez le menu latéral à gauche

---

### Depuis la Page Maintenances

**Vers le Calendrier** :
- Cliquez sur la carte **"📅 Calendrier"**
- OU cliquez sur le bouton **"Voir le Calendrier"** en bas de page

**Vers la Liste** (à venir) :
- Carte **"📋 Liste"** (grisée, prochainement disponible)

**Créer une Maintenance** (à venir) :
- Carte **"➕ Nouvelle"** (grisée, prochainement disponible)

---

## 🗺️ Arborescence de Navigation

```
┌─────────────────────────────────────────┐
│           MENU PRINCIPAL                │
│  (Sidebar - Toujours visible)           │
└─────────────────────────────────────────┘
           │
           ├── Dashboard
           ├── Agences
           ├── Agents
           ├── Équipements
           ├── Transferts
           ├── Demandes
           ├── Affectations
           ├── Pannes
           │
           ├── Maintenances ───────┐
           │                       │
           ├── Calendrier ─────────┤
           │                       │
           ├── Pertes              │
           ├── Notifications       │
           ├── Rapports            │
           └── Utilisateurs        │
                                   │
                                   ▼
         ┌─────────────────────────────────────┐
         │    PAGE: MAINTENANCES HUB           │
         │    /maintenances                    │
         ├─────────────────────────────────────┤
         │                                     │
         │  📅 Calendrier  (ACTIF)             │
         │  📋 Liste       (À venir)           │
         │  ➕ Nouvelle    (À venir)           │
         │                                     │
         │  [Statistiques Rapides]             │
         │  [Accès Rapide]                     │
         └─────────────────────────────────────┘
                         │
                         │ Clic sur "Calendrier"
                         ▼
         ┌─────────────────────────────────────┐
         │    PAGE: CALENDRIER                 │
         │    /maintenances/calendrier         │
         ├─────────────────────────────────────┤
         │                                     │
         │  ← Retour aux Maintenances          │
         │                                     │
         │  [Navigation: ◀ JUIN 2026 ▶]       │
         │  [Filtres: Type | Statut]          │
         │                                     │
         │  ┌───────────────────────────┐     │
         │  │   GRILLE CALENDAIRE       │     │
         │  │   Lun Mar Mer Jeu ...     │     │
         │  │                           │     │
         │  │   [Événements cliquables] │     │
         │  └───────────────────────────┘     │
         │                                     │
         └─────────────────────────────────────┘
                         │
                         │ Clic sur événement
                         ▼
         ┌─────────────────────────────────────┐
         │    MODAL: DÉTAILS MAINTENANCE       │
         ├─────────────────────────────────────┤
         │                                     │
         │  ❌ [Fermer]                        │
         │                                     │
         │  🔧 Maintenance Préventive          │
         │  🔵 Planifié                        │
         │                                     │
         │  📅 Dates                           │
         │  👥 Responsables                    │
         │  📦 Équipement                      │
         │  💰 Coût                            │
         │  📝 Observations                    │
         │                                     │
         └─────────────────────────────────────┘
```

---

## 🎯 Scénarios d'Utilisation

### Scénario 1 : Consultation Rapide du Calendrier

```
1. Connexion à l'application
2. Clic sur "Calendrier" dans le menu latéral
3. Vue immédiate du mois actuel avec toutes les maintenances
```

**⏱️ Temps estimé** : 2 secondes

---

### Scénario 2 : Exploration Complète

```
1. Connexion à l'application
2. Clic sur "Maintenances" dans le menu
3. Vue de la page d'accueil avec statistiques
4. Clic sur la carte "📅 Calendrier"
5. Navigation dans le calendrier
6. Clic sur un événement pour voir les détails
7. Fermeture de la modal
8. Changement de mois avec ◀ ▶
9. Application de filtres
10. Retour via "← Retour aux Maintenances"
```

**⏱️ Temps estimé** : 30 secondes

---

### Scénario 3 : Recherche d'une Maintenance Spécifique

```
1. Accès au calendrier
2. Navigation vers le bon mois (ex: Juin 2026)
3. Application du filtre "Type: Préventif"
4. Repérage visuel de la maintenance (couleur bleue)
5. Clic pour voir les détails complets
```

**⏱️ Temps estimé** : 10 secondes

---

## 🔑 Raccourcis Clavier

Une fois sur le calendrier :

| Touche | Action |
|--------|--------|
| `Tab` | Naviguer entre les éléments interactifs |
| `Enter` | Ouvrir un événement (si focus dessus) |
| `Esc` | Fermer la modal de détails |
| `Shift + Tab` | Navigation arrière |

---

## 📱 Navigation Mobile

### Menu Burger
Sur mobile, le menu latéral devient un menu hamburger (☰)

**Pour accéder au calendrier sur mobile** :
```
1. Appuyez sur ☰ (en haut à gauche)
2. Scroll jusqu'à "Calendrier"
3. Appuyez sur "Calendrier"
```

### Gestes Tactiles
- **Swipe** sur la grille calendaire : Non implémenté (utilisez les boutons ◀ ▶)
- **Tap** sur événement : Ouvre la modal
- **Tap** sur backdrop modal : Ferme la modal

---

## 🎨 Repères Visuels

### Icônes du Menu
- 🏠 **Dashboard** - Accueil
- 🔧 **Maintenances** - Page hub
- 📅 **Calendrier** - Vue calendaire (directe)

### Couleurs de Navigation
- **Bleu** : Éléments cliquables actifs
- **Gris** : Éléments désactivés (à venir)
- **Hover** : Effet de survol (scale, shadow)

### Indicateurs d'État
- **Bordure bleue** : Page active dans le menu
- **Background hover** : Élément survolé
- **Badge rouge** : Notifications (🔔)

---

## ⚡ Conseils de Navigation Rapide

### Pour les Utilisateurs Fréquents
1. **Marquez le calendrier en favoris** :
   ```
   http://localhost:5173/maintenances/calendrier
   ```

2. **Utilisez le lien direct du menu** :
   - Évitez de passer par la page Maintenances
   - Cliquez directement sur "Calendrier" dans le menu

3. **Raccourcis clavier** :
   - Utilisez `Tab` + `Enter` pour navigation rapide

### Pour les Administrateurs
- Accédez rapidement aux statistiques via `/maintenances`
- Puis basculez au calendrier pour la vue détaillée

---

## 🔍 Dépannage Navigation

### "Je ne vois pas le lien Calendrier"
**Cause** : Permissions insuffisantes  
**Solution** : Votre rôle doit être l'un des suivants :
- super_admin
- gestionnaire_stock_general
- technicien_maintenance
- gestionnaire_stock

### "Le calendrier est vide"
**Cause** : Pas de données pour le mois affiché  
**Solution** :
1. Naviguez vers juin 2026 (données de test)
2. Ou créez des maintenances via l'API
3. Ou exécutez le seeder : `php artisan db:seed --class=MaintenanceSeeder`

### "Bouton retour ne fonctionne pas"
**Cause** : JavaScript désactivé ou erreur  
**Solution** :
1. Vérifiez la console navigateur (F12)
2. Rechargez la page (Ctrl + R)
3. Utilisez le menu latéral comme alternative

---

## 📊 Plan du Site (Maintenances)

```
/maintenances
    │
    ├─ Hub (Page d'accueil)
    │   ├─ Carte Calendrier → /maintenances/calendrier
    │   ├─ Carte Liste (à venir)
    │   └─ Carte Nouvelle (à venir)
    │
    ├─ /calendrier
    │   ├─ Grille mensuelle
    │   ├─ Filtres
    │   ├─ Navigation mois
    │   └─ Modal détails (overlay)
    │
    ├─ /liste (à venir)
    │   └─ Tableau DataTable
    │
    └─ /nouveau (à venir)
        └─ Formulaire création
```

---

## ✅ Checklist de Test Navigation

Testez tous ces chemins pour vérifier la navigation :

- [ ] Menu latéral → Calendrier (direct)
- [ ] Menu latéral → Maintenances → Carte Calendrier
- [ ] URL directe : `/maintenances/calendrier`
- [ ] Bouton retour depuis calendrier → Maintenances
- [ ] Navigation avec ◀ et ▶ sur le calendrier
- [ ] Clic événement → Modal s'ouvre
- [ ] Fermer modal → Retour au calendrier
- [ ] Menu latéral reste accessible depuis calendrier
- [ ] Breadcrumb visible et fonctionnel
- [ ] Mobile : Menu burger accessible

---

## 🎯 Conclusion

La navigation est **simple et intuitive** :

**Accès le plus rapide** :
```
Menu → Calendrier (1 clic)
```

**Accès avec contexte** :
```
Menu → Maintenances → Calendrier (2 clics)
```

**Retour facile** :
```
Bouton Retour ou Menu latéral (1 clic)
```

---

*🧭 Bonne navigation !*
