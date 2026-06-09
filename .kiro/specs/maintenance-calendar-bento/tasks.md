# Plan d'Implémentation - Calendrier de Maintenance Moderne (Bento Style)

## Vue d'Ensemble

Ce plan d'implémentation couvre la création complète d'un calendrier de maintenance moderne avec design Bento et Glassmorphism, incluant :
- Backend Laravel (PHP) avec API RESTful pour la gestion des maintenances
- Frontend Vue.js 3 (JavaScript) avec Pinia, Tailwind CSS et animations GSAP
- Système de notifications pour les maintenances prévues
- Tests unitaires et property-based tests

**Stack technique** :
- **Backend** : PHP (Laravel 13)
- **Frontend** : JavaScript (Vue.js 3 Composition API)
- **Styling** : Tailwind CSS combiné avec PrimeVue
- **Animations** : GSAP
- **Testing** : PHPUnit + Eris (backend), Vitest + fast-check (frontend)

## Tâches

### 1. Configuration Initiale et Dépendances

- [x] 1.1 Installer et configurer GSAP dans le frontend
  - Ajouter GSAP aux dépendances : `npm install gsap`
  - Vérifier la configuration dans vite.config.js
  - _Requirements : 11.1, 11.2_

- [x] 1.2 Installer les bibliothèques de property-based testing
  - Backend : `composer require --dev giorgiosironi/eris`
  - Frontend : `npm install --save-dev fast-check`
  - _Requirements : Tests PBT du design_

- [x] 1.3 Vérifier la configuration Tailwind CSS
  - Confirmer que Tailwind est installé et configuré
  - Ajouter les classes personnalisées pour Glassmorphism si nécessaire
  - _Requirements : 12.1, 12.2, 12.3_

### 2. Backend API - Endpoints de Maintenance

- [x] 2.1 Créer MaintenanceRequest (Form Request)
  - Fichier : `backend/app/Http/Requests/MaintenanceRequest.php`
  - Implémenter les règles de validation (equipement_id, date_prevue, responsable, type_maintenance, cout, observations)
  - Ajouter les messages de validation en français
  - _Requirements : 1.5, Validation errors du design_

- [x] 2.2 Créer MaintenanceWorkflowService
  - Fichier : `backend/app/Services/MaintenanceWorkflowService.php`
  - Méthode `planifierPreventive(array $data): Maintenance`
  - Méthode `getByPeriod(string $startDate, string $endDate, array $filters = []): Collection`
  - Méthode `getMaintenanceWithRelations(int $id): Maintenance`
  - Appliquer eager loading pour éviter N+1
  - _Requirements : 1.2, 1.6, 19.4_

- [x] 2.3 Implémenter MaintenanceController endpoints
  - Fichier : `backend/app/Http/Controllers/MaintenanceController.php`
  - Méthode `index(Request $request): JsonResponse` avec filtres start_date, end_date, month, type_maintenance, statut
  - Méthode `show(int $id): JsonResponse` avec relations eager-loaded
  - Méthode `store(MaintenanceRequest $request): JsonResponse`
  - Appliquer MaintenancePolicy pour autorisation
  - _Requirements : 1.1, 1.3, 1.4, 1.7, 2.1, 2.2, 2.3, 17.2, 17.3_

- [x] 2.4 Ajouter les routes API
  - Fichier : `backend/routes/api.php`
  - Routes sous middleware `auth:sanctum` et `agence.scope`
  - GET `/api/maintenances` → index
  - GET `/api/maintenances/{id}` → show
  - POST `/api/maintenances` → store
  - _Requirements : 1.1, 17.2, 18.1_

- [x]* 2.5 Créer MaintenanceFactory pour tests
  - Fichier : `backend/database/factories/MaintenanceFactory.php`
  - Générer des maintenances avec dates, types, statuts variés
  - _Requirements : 13.2, Testing strategy_

### 3. Backend - Système de Notification

- [x] 3.1 Créer la notification AlerteMaintenancePrevue
  - Fichier : `backend/app/Notifications/AlerteMaintenancePrevue.php`
  - Implémenter les canaux `database` et `mail`
  - Méthode `toMail()` avec template Markdown professionnel
  - Passer les détails de la maintenance (équipement, date prévue, responsable)
  - _User Request : Notification backend_

- [x] 3.2 Créer un Job pour envoyer les alertes de maintenance
  - Fichier : `backend/app/Jobs/SendAlerteMaintenancePrevueJob.php`
  - Logique : sélectionner les maintenances planifiées dans les 24-48h
  - Envoyer la notification aux utilisateurs concernés
  - _User Request : Notification backend_

- [ ]* 3.3 Ajouter des tests unitaires pour la notification
  - Tester l'envoi de la notification
  - Vérifier le contenu du mail généré
  - _User Request : Tests notification_

### 4. Frontend - Utilitaires et Helpers

- [x] 4.1 Créer dateFormatter.js
  - Fichier : `frontend/src/utils/dateFormatter.js`
  - Fonctions : `formatDateFr(date)`, `formatDateLong(date)`, `formatTime(date)`
  - Fonctions : `parseISODate(isoString)`, `calculateDuration(start, end)`, `getMonthBounds(date)`
  - _Requirements : 16.1, 16.2, 16.3, 16.5_

- [x] 4.2 Créer gsapAnimations.js
  - Fichier : `frontend/src/utils/gsapAnimations.js`
  - Fonctions : `animateCalendarEntry(gridElement)`, `animateModalOpen(modalElement)`
  - Fonctions : `animateModalClose(modalElement)`, `animateEventCards(cardsArray)`, `cleanupAnimations()`
  - _Requirements : 5.1, 5.2, 5.3, 6.2, 11.3_

- [x] 4.3 Créer calendarUtils.js pour génération de grille
  - Fichier : `frontend/src/utils/calendarUtils.js`
  - Fonction `generateMonthGrid(year, month, maintenances)` retournant la structure MonthGrid
  - Logique de génération des semaines avec jours adjacents
  - _Requirements : 3.1, 3.3_

### 5. Frontend - Couche API et Store Pinia

- [x] 5.1 Créer maintenanceApi.js
  - Fichier : `frontend/src/api/maintenanceApi.js`
  - Méthodes : `getByPeriod(startDate, endDate, filters)`, `getById(id)`, `create(data)`, `getByMonth(month)`
  - Utiliser axiosInstance depuis axiosConfig.js
  - Gestion des erreurs réseau
  - _Requirements : 8.1, 8.2, Error handling frontend_

- [x] 5.2 Créer le Pinia store maintenanceStore.js
  - Fichier : `frontend/src/stores/maintenanceStore.js`
  - State : maintenances, loading, error, cache, selectedMaintenance
  - Getters : maintenancesByDate, maintenancesByStatus, maintenancesByType
  - Actions : fetchMaintenancesByPeriod, fetchMaintenanceById, createMaintenance, clearCache, resetError
  - Implémenter le système de cache avec TTL de 5 minutes
  - _Requirements : 7.1, 7.2, 7.3, 7.4, 7.5, 7.6, 15.3, 19.3_

- [x] 5.3 Créer le composable useMaintenance.js
  - Fichier : `frontend/src/composables/useMaintenance.js`
  - Exposer : maintenances (computed), loading (computed), error (computed)
  - Méthodes : loadMaintenances(month), loadMaintenanceDetails(id), createMaintenance(data)
  - Utilitaires : getMaintenancesForDay(date), filterByType(type), filterByStatus(status)
  - Gestion d'erreur avec messages utilisateur
  - _Requirements : 8.3, 8.4, 8.5_

### 6. Frontend - Composants du Calendrier

- [x] 6.1 Créer MaintenanceEventCard.vue
  - Fichier : `frontend/src/components/maintenance/MaintenanceEventCard.vue`
  - Props : maintenance, compact (optionnel)
  - Afficher type, référence équipement, heure
  - Classes Glassmorphism : backdrop-blur-lg bg-white/30 border border-white/20
  - Computed statusColor pour mapping Planifié→blue, En cours→orange, Terminé→green
  - Computed typeIcon pour préventif/correctif
  - _Requirements : 4.1, 4.2, 4.3, 4.4, 4.6, 9.1, 9.2, 10.1_

- [x] 6.2 Créer CalendarDay.vue
  - Fichier : `frontend/src/components/maintenance/CalendarDay.vue`
  - Props : day (DayCell)
  - Afficher visibleMaintenances (limite 3)
  - Afficher "+N autres" si hasMore (plus de 3 événements)
  - Classes CSS dynamiques selon isCurrentMonth, isToday
  - _Requirements : 3.3, 3.7, 4.5, 4.7_

- [x] 6.3 Créer CalendarGrid.vue
  - Fichier : `frontend/src/components/maintenance/CalendarGrid.vue`
  - Props : maintenances, currentMonth
  - Computed monthGrid utilisant generateMonthGrid()
  - Computed daysOfWeek ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim']
  - Rendre CalendarDay pour chaque cellule
  - Animation GSAP au montage via animateCalendarEntry
  - _Requirements : 3.1, 3.2, 5.1, 5.2_

- [x] 6.4 Créer MaintenanceDetailsModal.vue
  - Fichier : `frontend/src/components/maintenance/MaintenanceDetailsModal.vue`
  - Props : maintenance, isOpen
  - Emits : close
  - Sections : En-tête, Dates, Responsables, Technique, Coût, Relations
  - Animation GSAP ouverture/fermeture via animateModalOpen/Close
  - Style Glassmorphism cohérent
  - Responsive : plein écran sur mobile
  - _Requirements : 6.1, 6.2, 6.3, 6.4, 6.5, 6.6, 6.7, 14.3_

- [x] 6.5 Créer MaintenanceCalendarView.vue (vue principale)
  - Fichier : `frontend/src/views/MaintenanceCalendarView.vue`
  - État local : currentMonth, selectedMaintenance, isModalOpen, filters
  - Utiliser useMaintenance() composable
  - Méthodes : loadMonthData(), navigateMonth(direction), openDetails(maintenance), applyFilters()
  - Intégrer CalendarGrid et MaintenanceDetailsModal
  - Contrôles de navigation (mois précédent, suivant, aujourd'hui)
  - Filtres par type et statut
  - Titre "Calendrier de Maintenance"
  - _Requirements : 3.4, 3.5, 3.6, 6.1, 9.3, 9.4, 10.5, 18.3_

### 7. Frontend - Système de Notification (NotificationCenter)

- [x] 7.1 Créer NotificationCenter.vue
  - Fichier : `frontend/src/components/notifications/NotificationCenter.vue`
  - Panneau latéral (slide-over) ou dropdown avec Glassmorphism
  - Mock array de notifications pour développement
  - Badge dynamique sur icône cloche : calculer count avec `notif.lu === false`
  - Transition GSAP fluide à l'ouverture/fermeture
  - _User Request : Notification frontend_

- [ ]* 7.2 Intégrer NotificationCenter dans le layout principal
  - Ajouter le composant dans la barre de navigation
  - Positionner l'icône cloche avec badge
  - _User Request : Notification frontend_

### 8. Routing et Navigation

- [x] 8.1 Ajouter la route pour le calendrier
  - Fichier : `frontend/src/router/index.js` (ou équivalent)
  - Route `/maintenances/calendrier` → MaintenanceCalendarView.vue
  - Protéger avec guard d'authentification
  - _Requirements : 18.1, 18.4_

- [x] 8.2 Ajouter le lien de navigation dans le menu principal
  - Mettre à jour le composant de navigation
  - Lien vers `/maintenances/calendrier`
  - _Requirements : 18.2_

### 9. Gestion des Erreurs et États de Chargement

- [x] 9.1 Implémenter les indicateurs de chargement
  - Spinner ou skeleton loader dans CalendarGrid pendant loading
  - Utiliser l'état `loading` du store
  - _Requirements : 15.1, 15.3_

- [x] 9.2 Implémenter la gestion d'erreur dans les composants
  - Afficher message d'erreur convivial avec bouton réessayer
  - Utiliser l'état `error` du store
  - Message "Aucune maintenance prévue ce mois" si données vides
  - onErrorCaptured dans CalendarView pour error boundaries
  - _Requirements : 15.2, 15.4, Error handling frontend_

- [x] 9.3 Implémenter la validation robuste des dates
  - Try/catch dans dateFormatter avec fallback
  - Afficher "Date invalide" plutôt que crasher
  - _Requirements : 16.5, Data validation errors_

### 10. Responsivité et Accessibilité

- [x] 10.1 Implémenter la responsivité mobile
  - Vue liste ou grille compacte sur écrans < 768px
  - Utiliser les breakpoints Tailwind (sm, md, lg, xl)
  - Taille de police minimum 14px
  - Modal plein écran sur mobile
  - _Requirements : 14.1, 14.2, 14.3, 14.4_

- [ ]* 10.2 Ajouter le support swipe pour navigation mobile
  - Implémenter swipe horizontal pour changer de mois
  - _Requirements : 14.5_

- [x] 10.3 Implémenter l'accessibilité clavier
  - Navigation Tab, Enter, Échap dans grille et modale
  - Event_Card avec role="button" et tabindex="0"
  - Focus trap dans Modal_Details
  - Restaurer focus après fermeture modale
  - _Requirements : 20.1, 20.2, 20.3_

- [x] 10.4 Ajouter les attributs ARIA
  - Labels ARIA appropriés pour lecteurs d'écran
  - aria-label sur boutons de navigation
  - aria-live pour annonces dynamiques
  - _Requirements : 20.4_

- [ ]* 10.5 Vérifier le contraste des couleurs
  - Ratio WCAG AA minimum pour Event_Cards
  - _Requirements : 20.5_

### 11. Optimisation des Performances

- [x] 11.1 Optimiser les recalculs avec computed
  - Utiliser computed pour monthGrid et autres calculs coûteux
  - _Requirements : 19.1_

- [ ]* 11.2 Appliquer v-memo ou v-once pour cellules statiques
  - Optimiser le rendu des cellules calendaires
  - _Requirements : 19.2_

- [x] 11.3 Implémenter le nettoyage des animations GSAP
  - Appeler cleanupAnimations() dans onUnmounted
  - _Requirements : 11.4_

- [x] 11.4 Optimiser les refs pour éviter rerenders
  - Utiliser des refs granulaires plutôt qu'un gros objet réactif
  - _Requirements : 19.5_

### 12. Tests Backend

- [ ]* 12.1 Écrire les tests unitaires pour MaintenanceController
  - Fichier : `backend/tests/Feature/MaintenanceControllerTest.php`
  - Test : it_returns_422_for_invalid_dates
  - Test : it_returns_404_for_nonexistent_maintenance
  - Test : it_creates_maintenance_with_valid_data
  - _Testing strategy_

- [ ]* 12.2 Écrire les tests d'intégration pour l'agence scope
  - Test : it_applies_agence_scope_filter
  - Vérifier que les utilisateurs voient uniquement leurs maintenances
  - _Requirements : 1.3, 17.2_

- [ ]* 12.3 Écrire les property-based tests backend
  - Fichier : `backend/tests/Feature/MaintenancePropertyTest.php`
  - **Property 1 : API Date Range Filtering**
  - **Validates : Requirements 1.1**

- [ ]* 12.4 Property test : Duration Calculation Correctness
  - **Property 6 : Duration Calculation Correctness**
  - **Validates : Requirements 2.4**

### 13. Tests Frontend

- [ ]* 13.1 Écrire les tests unitaires pour MaintenanceEventCard
  - Fichier : `frontend/src/components/maintenance/__tests__/MaintenanceEventCard.test.js`
  - Test : displays status text correctly
  - Test : renders both maintenance types with different icons
  - _Testing strategy_

- [ ]* 13.2 Écrire les tests unitaires pour CalendarDay
  - Test : shows "+N autres" indicator when day has more than 3 events
  - _Requirements : 4.7_

- [ ]* 13.3 Écrire les tests d'intégration pour useMaintenance
  - Fichier : `frontend/src/composables/__tests__/useMaintenance.integration.test.js`
  - Test : handles network errors gracefully
  - Test : caches API calls for same period
  - _Requirements : 7.5, 19.3_

- [ ]* 13.4 Écrire les property-based tests frontend
  - Fichier : `frontend/src/components/maintenance/__tests__/calendar.property.test.js`
  - **Property 7 : Month Grid Structure Correctness**
  - **Validates : Requirements 3.1, 3.3**

- [ ]* 13.5 Property test : Date Parsing Round-Trip
  - **Property 17 : Date Parsing Round-Trip**
  - **Validates : Requirements 16.4**

- [ ]* 13.6 Property test : Event Placement by Date
  - **Property 8 : Event Placement by Date**
  - **Validates : Requirements 4.1**

- [ ]* 13.7 Property test : Status-to-CSS Mapping
  - **Property 10 : Status-to-CSS Mapping**
  - **Validates : Requirements 4.3, 10.2, 10.3, 10.4**

### 14. Checkpoint Final

- [x] 14.1 Vérifier que tous les tests passent
  - Backend : `php artisan test`
  - Frontend : `npm run test`
  - Ensure all tests pass, ask the user if questions arise.

- [x] 14.2 Tester manuellement l'interface
  - Navigation calendrier (mois précédent/suivant)
  - Ouverture/fermeture modale
  - Filtres par type et statut
  - Responsive sur différents écrans
  - NotificationCenter fonctionnel

- [x] 14.3 Vérifier l'intégration complète
  - API backend répond correctement
  - Frontend affiche les maintenances
  - Notifications fonctionnelles
  - Animations GSAP fluides
  - Ensure all tests pass, ask the user if questions arise.

## Notes

- Les tâches marquées avec `*` sont optionnelles et peuvent être sautées pour un MVP plus rapide
- Chaque tâche référence les requirements spécifiques pour traçabilité
- Les checkpoints assurent une validation incrémentale
- Les property tests valident les propriétés universelles de correction
- Les tests unitaires valident les exemples spécifiques et cas limites
- Le langage d'implémentation choisi est **JavaScript (Frontend) + PHP (Backend)**
