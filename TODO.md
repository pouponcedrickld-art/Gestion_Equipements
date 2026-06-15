# TODO - Module Gestion des Pannes

## Étape 1 — Audit technique (fait)
- Vérifier Backend: Panne model, PanneController, PannePolicy, PanneWorkflowService (vide), FormRequest StorePanneRequest (incomplet)
- Vérifier Routes API
- Vérifier Frontend: PannesView existant

## Étape 2 — Backend : Historisation
- Créer migration `panne_status_histories`
- Créer model `PanneStatusHistory`
- Ajouter relation dans `Panne`

## Étape 3 — Backend : Statuts normalisés + compatibilité
- Standardiser `Panne.statut` sur: déclarée, en_cours, en_maintenance, résolue, irrécupérable
- Mettre en place un mapping de compatibilité depuis anciennes valeurs (déclarée / en cours / réparée / remplacée / irrécupérable)

## Étape 4 — Backend : Service Layer (SOLID)
- Implémenter `PanneWorkflowService` avec transitions autorisées
- À chaque transition: update Panne + update Equipement + création d’historique + event

## Étape 5 — Backend : FormRequests + Controller refactor
- Compléter `StorePanneRequest`
- Ajouter FormRequests pour transitions
- Refactor `PanneController` pour appeler le service (controller fin)

## Étape 6 — Backend : API Resources
- Créer `PanneResource` (et détail si besoin)
- Utiliser resources dans les endpoints

## Étape 7 — Backend : Tests
- Tests Feature: scope agence, workflow transitions, historisation, events

## Étape 8 — Frontend : Structure Vue
- Créer `PanneFormView`, `PanneDetailView` (si manquants)
- Décomposer `PannesView` si nécessaire
- Implémenter composable `usePanne`
- Mettre/ajuster Pinia store

## Étape 9 — Frontend : PrimeVue/UX
- PrimeVue DataView/Table + Dialogs pour transitions
- Upload photos compatible

## Étape 10 — Vérification finale
- Lancer tests backend
- Lancer build frontend

