# Documentation : Processus de Demande de Matériel (Agence vers Stock Général)

Cette documentation explique comment la fonctionnalité de demande de matériel d'une agence vers le Gestionnaire de Stock Général a été mise en place dans l'application.

## 1. Vue d'ensemble du Flux

Le processus permet à un **Chef d'Agence** de solliciter du matériel auprès de la direction générale. Le **Gestionnaire de Stock Général** reçoit ces demandes, les analyse et décide de les approuver ou de les rejeter.

### Étapes du processus :
1. **Création** : Le Chef d'Agence remplit un formulaire précisant le matériel, la quantité, l'urgence et le motif.
2. **Attente** : La demande apparaît avec le statut "en attente".
3. **Traitement** : Le Gestionnaire de Stock Général examine la demande.
4. **Finalisation** : La demande est soit approuvée (totalement ou partiellement), soit rejetée avec des observations.

---

## 2. Architecture Technique (Backend)

### Modèle de Données
Le fichier [DemandeMateriel.php](file:///c:/Users/marcel.yessia/Documents/LARAVEL/Gestion_Equipements/backend/app/Models/DemandeMateriel.php) représente une demande en base de données.

**Table : `demandes_materiel`**
- `agence_id` : L'agence qui effectue la demande.
- `chef_agence_id` : L'utilisateur (Chef d'Agence) ayant créé la demande.
- `equipement_id` : Référence au type de matériel demandé (lié à la table `equipements`).
- `quantite` : Nombre d'unités demandées.
- `urgence` : Niveau de priorité (Basse, Moyenne, Haute).
- `motif` : Justification de la demande.
- `statut` : État actuel (`en attente`, `approuvé`, `rejeté`, `expédié`).
- `traite_par_id` : Le gestionnaire ayant traité la demande.
- `observations` : Note explicative en cas de rejet ou modification.

### Contrôleurs
1. **[DemandeMaterielController.php](file:///c:/Users/marcel.yessia/Documents/LARAVEL/Gestion_Equipements/backend/app/Http/Controllers/Agence/DemandeMaterielController.php)** :
   - Gère l'affichage des demandes pour l'agence connectée.
   - Gère la création des nouvelles demandes via la méthode `store()`.
2. **[DemandeAgenceController.php](file:///c:/Users/marcel.yessia/Documents/LARAVEL/Gestion_Equipements/backend/app/Http/Controllers/Direction/DemandeAgenceController.php)** :
   - Utilisé par la direction/gestionnaire général.
   - Méthode `index()` : Liste toutes les demandes de toutes les agences.
   - Méthode `traiter()` : Applique la décision (Approbation/Rejet).

### Validation
Le fichier [StoreDemandeMaterielRequest.php](file:///c:/Users/marcel.yessia/Documents/LARAVEL/Gestion_Equipements/backend/app/Http/Requests/Agence/StoreDemandeMaterielRequest.php) définit les règles de validation (quantité minimum, date future, urgence valide, etc.).

---

## 3. Interface Utilisateur (Frontend)

### Composants Clés
1. **[DemandesView.vue](file:///c:/Users/marcel.yessia/Documents/LARAVEL/Gestion_Equipements/frontend/src/views/agence/demandes-materiel/DemandesView.vue)** :
   - Affiche la liste des demandes.
   - Propose le bouton "Nouvelle demande" uniquement pour les Chefs d'Agence.
   - Adapte l'affichage si l'utilisateur est un gestionnaire général (vue de toutes les agences).
2. **[AffectationsView.vue](file:///c:/Users/marcel.yessia/Documents/LARAVEL/Gestion_Equipements/frontend/src/views/agence/affectations/AffectationsView.vue)** :
   - Contient une section dédiée au traitement des demandes pour le Gestionnaire Général.

### Services API
Le fichier [demandeAgenceApi.js](file:///c:/Users/marcel.yessia/Documents/LARAVEL/Gestion_Equipements/frontend/src/api/demandeAgenceApi.js) centralise les appels vers le backend :
- `store(data)` : Envoyer une nouvelle demande.
- `traiter(id, data)` : Envoyer la décision du gestionnaire.

---

## 4. Sécurité et Rôles

Le système repose sur une gestion fine des permissions ([RoleSeeder.php](file:///c:/Users/marcel.yessia/Documents/LARAVEL/Gestion_Equipements/backend/database/seeders/RoleSeeder.php)) :

- **`chef_agence`** :
  - Permission `demandes.creer` : Peut soumettre une demande.
  - Permission `demandes.view_agence` : Ne voit que les demandes de sa propre agence.
- **`gestionnaire_stock_general`** / **`super_admin`** :
  - Permission `demandes.view_all` : Voit toutes les demandes entrantes.
  - Permission `demandes.traiter` : Peut valider ou rejeter les demandes.

---

## 5. Pourquoi cette approche ?

1. **Traçabilité** : Chaque demande est archivée avec son historique (qui a demandé, qui a traité, quand).
2. **Centralisation** : Le stock général a une vue consolidée de tous les besoins des agences.
3. **Flexibilité** : Le statut "expédié" permet de faire le lien avec le module de **Transferts** pour le suivi physique du matériel.
4. **Validation des données** : L'utilisation de `FormRequest` côté Laravel garantit que seules des données cohérentes entrent en base.
