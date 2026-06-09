# Requirements Document - Calendrier de Maintenance Moderne (Bento Style)

## Introduction

Ce document définit les exigences pour le développement d'un calendrier de maintenance moderne utilisant un design "Modern Bento" avec des effets Glassmorphism. La fonctionnalité permet de visualiser, gérer et interagir avec les interventions de maintenance (préventives et correctives) dans une interface calendrier responsive et animée. Le système comprend un backend Laravel pour la gestion des données et un frontend Vue.js 3 (Composition API) pour la présentation visuelle.

## Glossaire

- **Backend_API**: API Laravel fournissant les endpoints RESTful pour la gestion des maintenances
- **Calendar_Component**: Composant Vue.js MaintenanceCalendarView.vue affichant la grille calendaire
- **Event_Card**: Carte représentant une intervention de maintenance dans le calendrier
- **Modal_Details**: Fenêtre modale affichant les détails complets d'une intervention
- **Maintenance_System**: Système complet intégrant backend et frontend pour la gestion du calendrier
- **GSAP_Engine**: Moteur d'animation GreenSock Animation Platform
- **Pinia_Store**: Gestionnaire d'état global pour Vue.js utilisant Pinia
- **API_Layer**: Couche de communication utilisant Axios pour les appels API
- **Composable**: Hook réutilisable Vue.js encapsulant la logique métier
- **Month_Grid**: Grille représentant les jours du mois courant
- **Maintenance_Record**: Enregistrement de maintenance provenant de la base de données
- **User_Interface**: Interface utilisateur Vue.js avec Tailwind CSS ou PrimeVue

## Requirements

### Requirement 1: API Backend pour Récupération des Maintenances par Période

**User Story:** En tant que développeur frontend, je veux récupérer les maintenances d'une période donnée via une API RESTful, afin d'afficher les interventions dans le calendrier.

#### Acceptance Criteria

1. WHEN une requête GET est envoyée à `/api/maintenances` avec les paramètres `start_date` et `end_date`, THE Backend_API SHALL retourner toutes les maintenances de cette période au format JSON
2. THE Backend_API SHALL inclure dans la réponse les relations `equipement`, `panne`, et `technicienUser` pour chaque maintenance
3. THE Backend_API SHALL filtrer les résultats selon le scope d'agence de l'utilisateur authentifié
4. THE Backend_API SHALL supporter un paramètre optionnel `month` (format YYYY-MM) qui calcule automatiquement start_date et end_date du mois
5. WHEN les dates sont invalides ou manquantes, THE Backend_API SHALL retourner une erreur HTTP 422 avec un message descriptif
6. THE Backend_API SHALL paginer les résultats avec un maximum de 100 maintenances par requête
7. THE Backend_API SHALL permettre le filtrage par `type_maintenance` (préventive, corrective) et `statut` (Planifié, En cours, Terminé)

### Requirement 2: Endpoint API pour Récupération d'une Maintenance Unique

**User Story:** En tant que développeur frontend, je veux récupérer les détails complets d'une maintenance spécifique, afin d'afficher ses informations dans la modale de détails.

#### Acceptance Criteria

1. WHEN une requête GET est envoyée à `/api/maintenances/{id}`, THE Backend_API SHALL retourner la maintenance avec toutes ses relations chargées
2. IF la maintenance n'existe pas, THEN THE Backend_API SHALL retourner une erreur HTTP 404
3. THE Backend_API SHALL vérifier les permissions d'accès selon la policy MaintenancePolicy
4. THE Backend_API SHALL inclure les champs calculés comme la durée (date_fin - date_debut) si applicable

### Requirement 3: Composant Calendrier Vue.js avec Grille Mensuelle

**User Story:** En tant qu'utilisateur final, je veux visualiser un calendrier mensuel des maintenances, afin de voir rapidement les interventions planifiées et leur état.

#### Acceptance Criteria

1. THE Calendar_Component SHALL générer une grille mensuelle affichant les jours du mois courant
2. THE Calendar_Component SHALL afficher les noms des jours de la semaine en en-tête
3. THE Calendar_Component SHALL gérer les jours du mois précédent et suivant pour compléter les semaines partielles
4. WHEN le mois courant change, THE Calendar_Component SHALL recharger les maintenances de la nouvelle période
5. THE Calendar_Component SHALL être responsive et s'adapter aux écrans mobiles, tablettes et desktop
6. THE Calendar_Component SHALL afficher des contrôles de navigation (mois précédent, mois suivant, aujourd'hui)
7. THE Calendar_Component SHALL mettre en évidence le jour actuel avec un style visuel distinct

### Requirement 4: Affichage des Événements de Maintenance dans les Cellules

**User Story:** En tant qu'utilisateur, je veux voir les événements de maintenance directement dans les cellules du calendrier, afin d'identifier rapidement les interventions prévues.

#### Acceptance Criteria

1. WHEN une maintenance est planifiée pour un jour donné, THE Calendar_Component SHALL afficher un Event_Card dans la cellule correspondante
2. THE Event_Card SHALL afficher le type de maintenance, l'équipement concerné, et l'heure si disponible
3. THE Event_Card SHALL utiliser des couleurs différentes selon le statut (Planifié: bleu, En cours: orange, Terminé: vert)
4. THE Event_Card SHALL utiliser des classes Tailwind pour refléter la criticité ou priorité
5. WHEN plusieurs maintenances sont prévues le même jour, THE Calendar_Component SHALL les empiler verticalement dans la cellule
6. THE Event_Card SHALL avoir un effet Glassmorphism (transparence, backdrop-filter, bordures subtiles)
7. WHEN la cellule contient plus de 3 événements, THE Calendar_Component SHALL afficher un indicateur "+N autres"

### Requirement 5: Animation GSAP au Montage du Calendrier

**User Story:** En tant qu'utilisateur, je veux que le calendrier apparaisse avec une animation fluide, afin d'avoir une expérience visuelle agréable.

#### Acceptance Criteria

1. WHEN le Calendar_Component est monté dans le DOM, THE GSAP_Engine SHALL animer l'apparition de la grille avec un effet de fondu et translation
2. THE GSAP_Engine SHALL animer les cellules individuelles avec un décalage séquentiel (stagger)
3. THE GSAP_Engine SHALL utiliser une courbe d'accélération (easing) pour un mouvement naturel
4. THE GSAP_Engine SHALL compléter l'animation en moins de 800ms
5. THE Calendar_Component SHALL s'assurer que GSAP est installé et importé correctement

### Requirement 6: Modale de Détails avec Animation GSAP

**User Story:** En tant qu'utilisateur, je veux cliquer sur un événement de maintenance pour voir ses détails complets dans une modale animée, afin d'accéder à toutes les informations pertinentes.

#### Acceptance Criteria

1. WHEN un utilisateur clique sur un Event_Card, THE Calendar_Component SHALL ouvrir le Modal_Details avec les informations complètes de la maintenance
2. THE GSAP_Engine SHALL animer l'ouverture du Modal_Details avec un effet de scale et opacity
3. THE Modal_Details SHALL afficher tous les champs: type, date prévue, dates début/fin, statut, technicien, diagnostic, coût, observations
4. THE Modal_Details SHALL afficher les informations de l'équipement et de la panne associée si disponibles
5. WHEN l'utilisateur clique en dehors du Modal_Details ou sur le bouton fermer, THE GSAP_Engine SHALL animer la fermeture avec l'effet inverse
6. THE Modal_Details SHALL être responsive et s'adapter aux petits écrans
7. THE Modal_Details SHALL utiliser le design Glassmorphism cohérent avec les Event_Cards

### Requirement 7: Gestion d'État avec Pinia Store

**User Story:** En tant que développeur, je veux centraliser l'état des maintenances dans un store Pinia, afin de faciliter le partage de données entre composants.

#### Acceptance Criteria

1. THE Pinia_Store SHALL maintenir une liste réactive des maintenances chargées
2. THE Pinia_Store SHALL exposer une action `fetchMaintenancesByPeriod(startDate, endDate)` qui appelle l'API
3. THE Pinia_Store SHALL exposer une action `fetchMaintenanceById(id)` pour récupérer une maintenance unique
4. THE Pinia_Store SHALL gérer les états de chargement (loading) et d'erreur (error)
5. THE Pinia_Store SHALL mettre en cache les maintenances pour éviter les requêtes redondantes
6. THE Pinia_Store SHALL permettre la réinitialisation du cache lors d'un changement de mois

### Requirement 8: Couche API et Composable pour Maintenances

**User Story:** En tant que développeur, je veux une couche API structurée et un composable réutilisable, afin de respecter l'architecture existante du projet.

#### Acceptance Criteria

1. THE API_Layer SHALL fournir un fichier `maintenanceApi.js` avec les méthodes `getAll`, `getById`, `getByPeriod`
2. THE API_Layer SHALL utiliser l'instance Axios configurée depuis `axiosConfig.js`
3. THE Composable SHALL fournir un fichier `useMaintenance.js` exposant les actions et états du store
4. THE Composable SHALL gérer les erreurs et fournir des messages utilisateur appropriés
5. THE Composable SHALL suivre le pattern existant observé dans `useAuth.js` et autres composables du projet

### Requirement 9: Gestion des Types de Maintenance

**User Story:** En tant qu'utilisateur, je veux distinguer visuellement les maintenances préventives des correctives, afin de comprendre rapidement la nature de l'intervention.

#### Acceptance Criteria

1. WHEN une maintenance est de type "préventive", THE Event_Card SHALL afficher une icône ou badge distinctif
2. WHEN une maintenance est de type "corrective", THE Event_Card SHALL afficher une icône ou badge différent
3. THE Calendar_Component SHALL permettre le filtrage par type de maintenance via des boutons toggle
4. WHEN un filtre est actif, THE Calendar_Component SHALL masquer les événements ne correspondant pas au type sélectionné

### Requirement 10: Gestion des Statuts de Maintenance

**User Story:** En tant qu'utilisateur, je veux voir l'état d'avancement de chaque maintenance, afin de suivre leur progression.

#### Acceptance Criteria

1. THE Event_Card SHALL afficher le statut textuel (Planifié, En cours, Terminé)
2. WHEN le statut est "Planifié", THE Event_Card SHALL utiliser une couleur de fond bleue (classes Tailwind: bg-blue-500/20, border-blue-500)
3. WHEN le statut est "En cours", THE Event_Card SHALL utiliser une couleur de fond orange (classes Tailwind: bg-orange-500/20, border-orange-500)
4. WHEN le statut est "Terminé", THE Event_Card SHALL utiliser une couleur de fond verte (classes Tailwind: bg-green-500/20, border-green-500)
5. THE Calendar_Component SHALL permettre le filtrage par statut via un menu déroulant ou boutons

### Requirement 11: Installation et Configuration de GSAP

**User Story:** En tant que développeur, je veux que GSAP soit correctement installé et configuré, afin de pouvoir utiliser les animations dans le composant.

#### Acceptance Criteria

1. THE Maintenance_System SHALL inclure GSAP dans les dépendances du package.json frontend
2. THE Calendar_Component SHALL importer GSAP via `import gsap from 'gsap'`
3. THE GSAP_Engine SHALL être utilisé dans le lifecycle hook `onMounted` pour les animations initiales
4. THE Calendar_Component SHALL nettoyer les animations GSAP dans `onUnmounted` pour éviter les fuites mémoire

### Requirement 12: Configuration Tailwind CSS (si nécessaire)

**User Story:** En tant que développeur, je veux utiliser Tailwind CSS pour le styling, afin de créer rapidement le design moderne Bento et Glassmorphism.

#### Acceptance Criteria

1. IF Tailwind CSS n'est pas installé, THEN THE Maintenance_System SHALL l'ajouter aux dépendances et configurer vite.config
2. THE Calendar_Component SHALL utiliser les classes utilitaires Tailwind pour tous les styles
3. THE Calendar_Component SHALL définir des classes personnalisées pour l'effet Glassmorphism (backdrop-blur, bg-opacity)
4. WHERE PrimeVue est déjà utilisé, THE User_Interface SHALL combiner harmonieusement Tailwind et PrimeVue sans conflit

### Requirement 13: Données Mock pour Développement Initial

**User Story:** En tant que développeur, je veux pouvoir tester le composant avec des données fictives, afin de développer l'interface avant que le backend soit complet.

#### Acceptance Criteria

1. THE Calendar_Component SHALL pouvoir fonctionner avec un array mock défini localement via `ref()`
2. THE Calendar_Component SHALL contenir 5 fausses interventions avec des dates, types, statuts, et équipements variés
3. WHEN le mode développement est actif, THE Calendar_Component SHALL utiliser les données mock au lieu de l'API
4. THE Calendar_Component SHALL permettre un switch facile entre mode mock et mode API via une variable d'environnement ou toggle

### Requirement 14: Responsivité Mobile et Desktop

**User Story:** En tant qu'utilisateur mobile, je veux que le calendrier s'adapte à mon écran, afin de consulter les maintenances sur n'importe quel appareil.

#### Acceptance Criteria

1. WHEN l'écran est inférieur à 768px, THE Calendar_Component SHALL afficher une vue liste ou compacter la grille
2. THE Calendar_Component SHALL utiliser les breakpoints Tailwind (sm, md, lg, xl) pour adapter la mise en page
3. THE Modal_Details SHALL occuper toute la hauteur de l'écran sur mobile avec défilement vertical
4. THE Event_Card SHALL avoir une taille de police lisible sur tous les écrans (minimum 14px)
5. THE Calendar_Component SHALL permettre le swipe horizontal pour changer de mois sur mobile

### Requirement 15: Gestion des Erreurs et États de Chargement

**User Story:** En tant qu'utilisateur, je veux voir un indicateur de chargement pendant la récupération des données, afin de savoir que le système traite ma demande.

#### Acceptance Criteria

1. WHEN les maintenances sont en cours de chargement, THE Calendar_Component SHALL afficher un spinner ou skeleton loader
2. IF une erreur API survient, THEN THE Calendar_Component SHALL afficher un message d'erreur convivial avec option de réessayer
3. THE Pinia_Store SHALL exposer un état `loading` et `error` accessibles par le composant
4. WHEN aucune maintenance n'existe pour le mois sélectionné, THE Calendar_Component SHALL afficher un message "Aucune maintenance prévue ce mois"

### Requirement 16: Parser et Pretty Printer pour Dates de Maintenance

**User Story:** En tant que développeur, je veux parser et formater les dates de maintenance correctement, afin d'assurer l'interopérabilité entre backend et frontend.

#### Acceptance Criteria

1. THE API_Layer SHALL parser les dates ISO8601 reçues du backend en objets Date JavaScript
2. THE Calendar_Component SHALL formater les dates pour affichage avec une locale française (format: "JJ/MM/YYYY" ou "JJ mois YYYY")
3. THE Calendar_Component SHALL fournir un Pretty_Printer pour formater les heures (format: "HH:MM")
4. FOR ALL dates valides parsées puis formatées puis reparsées, THE Calendar_Component SHALL produire un objet Date équivalent (round-trip property)
5. WHEN une date est invalide, THE Calendar_Component SHALL afficher "Date invalide" plutôt que de crasher

### Requirement 17: Intégration avec le Système d'Authentification Existant

**User Story:** En tant qu'utilisateur authentifié, je veux que le calendrier respecte mes permissions et mon scope d'agence, afin de voir uniquement les maintenances autorisées.

#### Acceptance Criteria

1. THE Calendar_Component SHALL vérifier que l'utilisateur est authentifié avant d'afficher les données
2. THE Backend_API SHALL appliquer le middleware `auth:sanctum` et `agence.scope` aux endpoints de maintenance
3. THE Backend_API SHALL filtrer les maintenances selon la policy MaintenancePolicy existante
4. WHEN l'utilisateur n'a pas les permissions requises, THE Backend_API SHALL retourner une erreur HTTP 403

### Requirement 18: Routes et Navigation

**User Story:** En tant qu'utilisateur, je veux accéder au calendrier de maintenance via une route dédiée, afin de naviguer facilement dans l'application.

#### Acceptance Criteria

1. THE Maintenance_System SHALL définir une route `/maintenances/calendrier` pointant vers MaintenanceCalendarView.vue
2. THE Maintenance_System SHALL ajouter un lien vers le calendrier dans le menu de navigation principal
3. THE Calendar_Component SHALL afficher le titre "Calendrier de Maintenance" dans l'en-tête
4. THE Maintenance_System SHALL protéger la route avec le guard d'authentification

### Requirement 19: Performance et Optimisation

**User Story:** En tant qu'utilisateur, je veux que le calendrier se charge rapidement, afin de ne pas perdre de temps à attendre.

#### Acceptance Criteria

1. THE Calendar_Component SHALL utiliser `computed` pour les calculs de grille afin d'éviter les recalculs inutiles
2. THE Calendar_Component SHALL utiliser `v-memo` ou `v-once` pour les cellules statiques
3. THE Pinia_Store SHALL éviter de recharger les maintenances si elles sont déjà en cache pour le mois courant
4. THE Backend_API SHALL utiliser l'eager loading pour charger les relations et éviter le problème N+1
5. THE Calendar_Component SHALL limiter le nombre de rerenders en utilisant des refs granulaires

### Requirement 20: Accessibilité et UX

**User Story:** En tant qu'utilisateur avec des besoins d'accessibilité, je veux pouvoir naviguer dans le calendrier au clavier, afin d'utiliser l'application sans souris.

#### Acceptance Criteria

1. THE Calendar_Component SHALL permettre la navigation au clavier (Tab, Enter, Échap) dans la grille et la modale
2. THE Event_Card SHALL avoir un attribut `role="button"` et être focusable avec `tabindex="0"`
3. THE Modal_Details SHALL capturer le focus et le restaurer à l'Event_Card à la fermeture
4. THE Calendar_Component SHALL fournir des labels ARIA appropriés pour les lecteurs d'écran
5. THE Event_Card SHALL avoir un contraste de couleur suffisant (ratio WCAG AA minimum)
