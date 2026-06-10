# 📋 Plan Détaillé de Développement - Tâche DESCARTES

**Projet:** Gestion d'Équipements - GESTPARK  
**Développeur:** Koffi Maxime  
**Module:** Équipements, Catégories, Consommables, Transferts, Scan/Import  
**Durée estimée:** 22 heures  
**Date création:** 8 juin 2026  

---

## 🎯 **OBJECTIFS & PÉRIMÈTRE**

### **Ce que livre cette tâche :**
- ✅ CRUD équipements avec tracking agence (`agence_proprietaire_id` + `agence_actuelle_id`)
- ✅ CRUD catégories et consommables
- ✅ **Workflow Transferts** : Gestionnaire Stock Général expédie vers sous-agence, Gestionnaire Stock Local reçoit
- ✅ Import Excel/CSV en masse
- ✅ Scan QR/code-barres

### **Dépendances disponibles :**
- ✅ Infrastructure Laravel + Vue.js fonctionnelle
- ✅ Système d'authentification et rôles
- ✅ Modèles `User`, `Agence` avec seeders
- ✅ Structure frontend de base

---

## 🏗️ **PHASE 1 : PRÉPARATION & FONDATIONS** *(~2h)*

### **1.1 Audit de l'existant** *(30min)*
- [✅] Vérifier les modèles existants (`Equipement`, `Categorie`, `Consommable`, `Transfert`)
- [✅] Analyser les migrations déjà présentes
- [✅] Identifier les contrôleurs existants et leur état
- [✅] Vérifier les routes API définies
- [✅] Analyser l'état des vues frontend existantes

### **1.2 Complétion des migrations** *(45min)*
- [✅] Créer/compléter migration `categories` si nécessaire
- [✅] Créer/compléter migration `consommables` 
- [✅] Créer/compléter migration `transferts`
- [✅] Vérifier/créer migration `update_equipements_add_agence_tracking`
- [✅] Tester les migrations (rollback/migrate)

### **1.3 Finalisation des seeders** *(45min)*
- [✅] Créer `CategorieSeeder` (9 catégories d'équipements IT)
- [✅] Créer `ConsommableSeeder` (données d'exemple)
- [✅] Compléter `EquipementSeeder` avec QR codes
- [✅] Créer données d'exemple pour transferts

---

## 🏭 **PHASE 2 : BACKEND - MODÈLES & CONTRÔLEURS** *(~4h)*

### **2.1 Finalisation des Modèles** *(1h)*
**Ordre prioritaire :**

#### **2.1.1 `Categorie`** *(15min)*
- [ ] Relations avec équipements
- [ ] Validation des champs
- [ ] Scopes de recherche

#### **2.1.2 `Equipement`** *(30min)*
- [ ] Cœur métier avec toutes les relations
- [ ] Tracking agence (propriétaire/actuelle)
- [ ] Gestion statuts et états
- [ ] Méthodes utilitaires (QR, mouvements)

#### **2.1.3 `Consommable`** *(10min)*
- [ ] Relation avec équipements
- [ ] Gestion stock (quantités)

#### **2.1.4 `Transfert`** *(5min)*
- [ ] Workflow avec statuts
- [ ] Relations agences source/destination

### **2.2 Création des Contrôleurs** *(2h)*

#### **2.2.1 CategorieController** *(30min)*
- [ ] `index()` - Liste avec pagination
- [ ] `store()` - Création avec validation
- [ ] `show()` - Détail avec équipements associés
- [ ] `update()` - Modification
- [ ] `destroy()` - Suppression (vérifier dépendances)

#### **2.2.2 EquipementController** *(1h)*
- [ ] `index()` - Liste avec filtres agence + statut
- [ ] `store()` - Création avec génération QR + tracking agence
- [ ] `show()` - Détail complet avec relations
- [ ] `update()` - Modification + historique mouvement
- [ ] `destroy()` - Soft delete
- [ ] `generateQR()` - Génération QR à la demande
- [ ] `search()` - Recherche par critères multiples

#### **2.2.3 ConsommableController** *(20min)*
- [ ] CRUD standard avec relation équipement
- [ ] Gestion stock (quantités)

#### **2.2.4 TransfertController** *(10min)*
- [ ] `index()` - Liste selon rôle utilisateur
- [ ] `store()` - Création demande transfert
- [ ] `expedier()` - Changement statut + tracking
- [ ] `recevoir()` - Réception + mise à jour agence_actuelle

### **2.3 Policies & Middlewares** *(30min)*
- [ ] **`EquipementPolicy`** - Contrôle accès par agence
- [ ] **`TransfertPolicy`** - Contrôle workflow par rôle
- [ ] Intégration dans contrôleurs

### **2.4 Routes API** *(30min)*
- [ ] Définition routes RESTful avec groupes par rôle
- [ ] Routes spéciales (QR, import, scan)
- [ ] Tests des routes avec Postman/Insomnia

---

## 🎨 **PHASE 3 : BACKEND AVANCÉ - SERVICES** *(~3h)*

### **3.1 ImportService** *(1h30)*
- [ ] **Parsing Excel/CSV** avec validation stricte
- [ ] **Mapping colonnes** → modèles Laravel
- [ ] **Gestion erreurs** ligne par ligne avec rapport
- [ ] **Génération QR** en masse lors import
- [ ] **Preview import** avant confirmation

### **3.2 RapportService (partagé)** *(1h)*
- [ ] **Rapports équipements** par agence/statut
- [ ] **Export Excel** données équipements
- [ ] **Statistiques** catégories/consommables
- [ ] **Rapports transferts** avec tracking

### **3.3 QR Generation Service** *(30min)*
- [ ] **Génération QR codes** avec données équipement
- [ ] **Stockage fichiers** QR dans storage/public
- [ ] **API endpoints** pour régénération

---

## 💻 **PHASE 4 : FRONTEND - STORES & API** *(~2h)*

### **4.1 Services API** *(45min)*
```
/frontend/src/api/
├── equipementApi.js     # CRUD + QR + search
├── categorieApi.js      # CRUD simple  
├── consommableApi.js    # CRUD + stock
├── transfertApi.js      # Workflow transferts
└── importApi.js         # Upload + preview
```

- [ ] `equipementApi.js` - CRUD complet + fonctions spéciales
- [ ] `categorieApi.js` - CRUD simple
- [ ] `consommableApi.js` - CRUD + gestion stock
- [ ] `transfertApi.js` - Workflow complet
- [ ] `importApi.js` - Upload et preview

### **4.2 Stores Pinia** *(1h15)*

#### **4.2.1 categorieStore** *(15min)*
- [ ] État : `categories`, `loading`, `errors`
- [ ] Actions : CRUD + cache local

#### **4.2.2 equipementStore** *(45min)*
- [ ] État complexe : `equipements`, `filters`, `pagination`, `selectedAgence`
- [ ] Actions : CRUD + search + QR + tracking agence
- [ ] Getters : filtres par statut/agence

#### **4.2.3 consommableStore** *(10min)*
- [ ] CRUD simple avec relation équipement

#### **4.2.4 transfertStore** *(5min)*
- [ ] Workflow : demande → expédition → réception

---

## 🖥️ **PHASE 5 : FRONTEND - COMPOSANTS VUE** *(~5h)*

### **5.1 Views principales** *(3h)*

#### **5.1.1 CategoriesView** *(30min)*
- [ ] DataTable PrimeVue avec CRUD
- [ ] Modal création/édition
- [ ] Confirmations suppression

#### **5.1.2 EquipementsView** *(1h30)*
- [ ] **DataTable avancée** : filtres agence, statut, catégorie
- [ ] **Actions en masse** : export, QR batch
- [ ] **Colonnes dynamiques** selon rôle utilisateur
- [ ] **Integration scan** QR pour recherche rapide

#### **5.1.3 EquipementDetailView** *(30min)*
- [ ] **Fiche complète** avec onglets
- [ ] **Historique mouvements** 
- [ ] **QR code affiché** + bouton régénération
- [ ] **Actions contextuelles** selon statut

#### **5.1.4 ConsommablesView** *(30min)*
- [ ] Table avec gestion stock
- [ ] Association équipements

### **5.2 Formulaires** *(1h30)*

#### **5.2.1 EquipementFormView** *(1h)*
- [ ] **Formulaire smart** avec validation temps réel
- [ ] **Upload photo** avec preview
- [ ] **Sélecteur catégorie** dynamique
- [ ] **Tracking agence** automatique selon rôle

#### **5.2.2 TransfertFormView** *(30min)*
- [ ] **Sélecteur équipements** avec filtres
- [ ] **Workflow guidé** selon rôle (demande/expédition/réception)

### **5.3 Fonctionnalités avancées** *(30min)*
- [ ] **ImportView** - Interface upload avec mapping colonnes
- [ ] **ScanQRView** - Caméra + détection + actions rapides

---

## 🔧 **PHASE 6 : FONCTIONNALITÉS AVANCÉES** *(~3h)*

### **6.1 Import/Export** *(1h30)*
- [ ] **Template Excel** à télécharger
- [ ] **Prévisualisation** import avec validation
- [ ] **Gestion erreurs** détaillée par ligne
- [ ] **Export personnalisé** selon filtres

### **6.2 Scan QR/Code-barres** *(1h)*
- [ ] **Intégration caméra** (WebRTC)
- [ ] **Détection automatique** formats (QR, EAN, Code128)
- [ ] **Actions rapides** : voir détail, modifier, affecter

### **6.3 Composables Vue** *(30min)*
```
/frontend/src/composables/
├── useEquipement.js     # Logique métier équipements
├── useTransfert.js      # Workflow transferts  
└── useScan.js          # Gestion scan QR/codes
```

- [ ] `useEquipement.js` - Logique métier réutilisable
- [ ] `useTransfert.js` - Workflow et validations
- [ ] `useScan.js` - Gestion scan et détection

---

## 🧪 **PHASE 7 : TESTS & VALIDATION** *(~2h)*

### **7.1 Tests Backend** *(1h)*
- [ ] **Tests unitaires** modèles et relations
- [ ] **Tests API** tous endpoints avec authentification
- [ ] **Tests policies** contrôles d'accès
- [ ] **Tests import** avec fichiers d'exemple

### **7.2 Tests Frontend** *(30min)*
- [ ] **Tests composants** critiques (formulaires)
- [ ] **Tests stores** actions et état
- [ ] **Tests navigation** selon rôles

### **7.3 Tests d'intégration** *(30min)*
- [ ] **Workflow complet** : création → transfert → réception
- [ ] **Import en masse** avec validation
- [ ] **Scan QR** → actions métier

---

## 🚀 **PHASE 8 : FINALISATION & DÉPLOIEMENT** *(~1h)*

### **8.1 Documentation** *(30min)*
- [ ] **README** pour votre module
- [ ] **API documentation** (endpoints + exemples)
- [ ] **Guide utilisateur** fonctionnalités scan/import

### **8.2 Optimisations** *(30min)*
- [ ] **Performance** requêtes DB (eager loading)
- [ ] **Cache** listes catégories
- [ ] **Indexation** colonnes recherche
- [ ] **Compression images** QR codes

---

## 📊 **RÉCAPITULATIF PLANNING**

| Phase | Durée | Focus | Statut |
|-------|--------|--------|--------|
| **1. Préparation** | 2h | Migrations + Seeders | ✅ |
| **2. Backend Core** | 4h | Modèles + Contrôleurs | ⏳ |
| **3. Backend Avancé** | 3h | Services + Import | ⏳ |
| **4. Frontend API** | 2h | Stores + API Layer | ⏳ |
| **5. Frontend UI** | 5h | Composants Vue | ⏳ |
| **6. Fonctions Avancées** | 3h | Import/Scan/QR | ⏳ |
| **7. Tests** | 2h | Validation complète | ⏳ |
| **8. Finalisation** | 1h | Doc + Optimisation | ⏳ |
| **TOTAL** | **22h** | **Livrable complet** | ⏳ |

---

## 🎯 **LIVRABLES FINAUX**

- [ ] **CRUD complet** : Équipements, Catégories, Consommables, Transferts  
- [ ] **Import Excel/CSV** en masse avec validation  
- [ ] **Scan QR/codes-barres** avec actions métier  
- [ ] **Tracking agence** complet (propriétaire/actuelle)  
- [ ] **Workflow transferts** inter-agences fonctionnel  
- [ ] **Interface responsive** adaptée par rôle  
- [ ] **Tests** et documentation complète  

---

## 📝 **NOTES DE DÉVELOPPEMENT**

### **Priorités :**
1. **Catégories** (simple, pas de dépendances)
2. **Équipements** (cœur métier)
3. **Consommables** (dépend des équipements)
4. **Transferts** (workflow complexe)
5. **Import/Scan** (fonctionnalités bonus)

### **Points d'attention :**
- Respecter les permissions par rôle
- Valider le tracking agence à chaque étape
- Tester l'import avec des fichiers volumineux
- Optimiser les performances des recherches

### **Technologies utilisées :**
- **Backend :** Laravel 11, Sanctum, Spatie/Permission
- **Frontend :** Vue.js 3, PrimeVue, Pinia
- **Libs spéciales :** QR code generation, Excel parsing, WebRTC (scan)

---

**Statut :** 🚀 **Prêt à démarrer**  
**Dernière mise à jour :** 8 juin 2026