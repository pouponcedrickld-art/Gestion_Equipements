# Requirements Document - Gestion Pannes Controller

## Introduction

Ce document définit les exigences pour l'implémentation du **PanneController** dans le système de gestion de parc matériel multi-agences. Le controller permet la gestion complète du workflow des pannes d'équipements, depuis leur déclaration jusqu'à leur résolution, en passant par la transmission à la maintenance et le diagnostic technique.

Le système est basé sur Laravel 11 et doit s'intégrer avec le frontend Vue 3 existant. Le modèle `Panne`, les migrations, les routes API et les vues frontend sont déjà en place mais non fonctionnels.

## Glossary

- **PanneController**: Contrôleur Laravel responsable de la gestion des pannes d'équipements
- **Panne**: Incident déclaré sur un équipement nécessitant une intervention
- **Agent**: Utilisateur de l'agence pouvant déclarer des pannes
- **GestionnaireStock**: Utilisateur avec permissions pour gérer les pannes de son agence
- **TechnicienMaintenance**: Utilisateur responsable du diagnostic et de la résolution des pannes
- **Equipement**: Matériel pouvant faire l'objet d'une panne
- **Maintenance**: Intervention technique créée suite à une panne
- **NiveauGravite**: Classification de la panne (mineure, majeure, critique)
- **StatutPanne**: État de la panne (declaree, en_cours, en_maintenance, resolue, irrecuperable)
- **Workflow**: Séquence d'états et d'actions pour traiter une panne
- **AgenceScope**: Filtre de sécurité limitant l'accès aux données de l'agence de l'utilisateur
- **MouvementService**: Service responsable de l'enregistrement des mouvements d'équipements
- **NotificationService**: Service responsable de l'envoi de notifications
- **PanneWorkflowService**: Service gérant la logique métier du workflow des pannes
- **ValidationRequest**: Classe de validation Laravel pour les requêtes HTTP

## Requirements

### Requirement 1: Lister les pannes avec filtres

**User Story:** En tant que gestionnaire stock ou technicien maintenance, je veux lister toutes les pannes avec des filtres, afin de suivre l'état du parc et prioriser les interventions.

#### Acceptance Criteria

1. WHEN le endpoint `GET /api/pannes` est appelé, THE PanneController SHALL retourner la liste paginée des pannes avec leurs relations (equipement, agent, gestionnaire, technicien)
2. WHERE le paramètre `statut` est fourni, THE PanneController SHALL filtrer les pannes par statut
3. WHERE le paramètre `niveau_gravite` est fourni, THE PanneController SHALL filtrer les pannes par niveau de gravité
4. WHERE le paramètre `equipement_id` est fourni, THE PanneController SHALL filtrer les pannes pour l'équipement spécifié
5. WHERE les paramètres `date_debut` et `date_fin` sont fournis, THE PanneController SHALL filtrer les pannes déclarées dans cette période
6. WHILE l'utilisateur a le rôle GestionnaireStock, THE PanneController SHALL appliquer le AgenceScope pour limiter aux pannes de son agence
7. WHILE l'utilisateur a le rôle TechnicienMaintenance, THE PanneController SHALL retourner toutes les pannes sans restriction d'agence
8. THE PanneController SHALL ordonner les résultats par date de déclaration décroissante
9. THE PanneController SHALL paginer les résultats à 20 pannes par page
10. THE PanneController SHALL retourner un code HTTP 200 avec format JSON standardisé

### Requirement 2: Déclarer une nouvelle panne

**User Story:** En tant qu'agent ou gestionnaire stock, je veux déclarer une panne sur un équipement, afin de signaler un dysfonctionnement et déclencher le processus de résolution.

#### Acceptance Criteria

1. WHEN le endpoint `POST /api/pannes` est appelé avec des données valides, THE PanneController SHALL créer une nouvelle panne avec statut 'declaree'
2. THE PanneController SHALL valider les champs obligatoires: equipement_id, description, niveau_gravite
3. THE PanneController SHALL définir automatiquement la date_declaration à l'instant actuel
4. THE PanneController SHALL enregistrer l'agent déclarant via agent_id basé sur l'utilisateur authentifié
5. THE PanneController SHALL enregistrer le gestionnaire_stock_id basé sur l'agence de l'équipement
6. WHERE des photos sont fournies, THE PanneController SHALL valider le format JSON array et sauvegarder les chemins
7. WHEN la panne est créée avec niveau_gravite 'critique', THE PanneController SHALL déclencher une notification automatique au gestionnaire stock
8. WHEN la panne est créée, THE PanneController SHALL enregistrer un mouvement via MouvementService avec type 'panne_declaree'
9. WHEN la panne est créée, THE PanneController SHALL dispatcher l'événement PanneDeclaree
10. IF la validation échoue, THEN THE PanneController SHALL retourner un code HTTP 422 avec les erreurs détaillées
11. THE PanneController SHALL retourner un code HTTP 201 avec la panne créée incluant ses relations

### Requirement 3: Afficher les détails d'une panne

**User Story:** En tant qu'utilisateur autorisé, je veux consulter les détails complets d'une panne, afin d'analyser le problème et son historique.

#### Acceptance Criteria

1. WHEN le endpoint `GET /api/pannes/{id}` est appelé, THE PanneController SHALL retourner les détails de la panne avec toutes ses relations
2. THE PanneController SHALL charger en eager loading: equipement, agent, gestionnaireStock, technicien, maintenances
3. THE PanneController SHALL vérifier l'autorisation via PannePolicy->view
4. IF la panne n'existe pas, THEN THE PanneController SHALL retourner un code HTTP 404
5. IF l'utilisateur n'a pas les permissions, THEN THE PanneController SHALL retourner un code HTTP 403
6. THE PanneController SHALL retourner un code HTTP 200 avec format JSON standardisé

### Requirement 4: Mettre à jour une panne

**User Story:** En tant que gestionnaire stock, je veux modifier les informations d'une panne, afin de corriger ou compléter la déclaration.

#### Acceptance Criteria

1. WHEN le endpoint `PUT /api/pannes/{id}` est appelé avec des données valides, THE PanneController SHALL mettre à jour la panne
2. THE PanneController SHALL permettre la modification de: description, niveau_gravite, photos
3. THE PanneController SHALL interdire la modification de: equipement_id, agent_id, date_declaration, statut
4. THE PanneController SHALL vérifier l'autorisation via PannePolicy avant la modification
5. WHEN la panne a le statut 'resolue' ou 'irrecuperable', THE PanneController SHALL interdire toute modification
6. IF la validation échoue, THEN THE PanneController SHALL retourner un code HTTP 422 avec les erreurs
7. IF la panne n'existe pas, THEN THE PanneController SHALL retourner un code HTTP 404
8. THE PanneController SHALL retourner un code HTTP 200 avec la panne mise à jour

### Requirement 5: Supprimer une panne

**User Story:** En tant que gestionnaire stock, je veux supprimer une panne incorrectement déclarée, afin de maintenir la qualité des données.

#### Acceptance Criteria

1. WHEN le endpoint `DELETE /api/pannes/{id}` est appelé, THE PanneController SHALL supprimer la panne de manière logique (soft delete)
2. THE PanneController SHALL vérifier l'autorisation via PannePolicy avant la suppression
3. WHEN la panne a le statut 'en_maintenance', THE PanneController SHALL interdire la suppression
4. WHEN la panne a des maintenances associées, THE PanneController SHALL interdire la suppression
5. IF la panne n'existe pas, THEN THE PanneController SHALL retourner un code HTTP 404
6. THE PanneController SHALL retourner un code HTTP 200 avec message de confirmation

### Requirement 6: Transmettre une panne à la maintenance

**User Story:** En tant que gestionnaire stock, je veux transmettre une panne au service maintenance, afin qu'un technicien puisse diagnostiquer et intervenir.

#### Acceptance Criteria

1. WHEN le endpoint `POST /api/pannes/{id}/transmettre-maintenance` est appelé, THE PanneController SHALL déléguer l'opération au PanneWorkflowService
2. THE PanneController SHALL vérifier que l'utilisateur a le rôle GestionnaireStock
3. THE PanneController SHALL vérifier l'autorisation via PannePolicy->transmettreMaintenance
4. THE PanneWorkflowService SHALL valider que la panne a le statut 'declaree' ou 'en_cours'
5. THE PanneWorkflowService SHALL changer le statut de la panne à 'en_maintenance'
6. THE PanneWorkflowService SHALL créer un enregistrement Maintenance avec type 'corrective' et panne_id associé
7. THE PanneWorkflowService SHALL copier equipement_id de la panne vers la maintenance
8. WHERE un technicien_id est fourni dans la requête, THE PanneWorkflowService SHALL l'assigner à la maintenance
9. WHERE une date_prevue est fournie dans la requête, THE PanneWorkflowService SHALL la définir sur la maintenance
10. THE PanneWorkflowService SHALL enregistrer un mouvement via MouvementService avec type 'panne_transmise_maintenance'
11. THE PanneWorkflowService SHALL notifier le technicien assigné via NotificationService
12. IF la panne n'a pas le bon statut, THEN THE PanneController SHALL retourner un code HTTP 422 avec message explicatif
13. THE PanneController SHALL retourner un code HTTP 200 avec la panne et la maintenance créée

### Requirement 7: Diagnostiquer une panne (technicien)

**User Story:** En tant que technicien maintenance, je veux ajouter un diagnostic et une action réalisée sur une panne, afin de documenter mon intervention et finaliser la résolution.

#### Acceptance Criteria

1. WHEN le endpoint `POST /api/pannes/{id}/diagnostiquer` est appelé, THE PanneController SHALL déléguer l'opération au PanneWorkflowService
2. THE PanneController SHALL vérifier que l'utilisateur a le rôle TechnicienMaintenance
3. THE PanneController SHALL vérifier l'autorisation via PannePolicy->diagnostiquer
4. THE PanneWorkflowService SHALL valider que la panne a le statut 'en_maintenance'
5. THE PanneWorkflowService SHALL valider les champs obligatoires: diagnostic_technicien, action_realisee, decision_finale
6. THE PanneWorkflowService SHALL enregistrer diagnostic_technicien, action_realisee, cout_reparation sur la panne
7. WHEN decision_finale est 'resolue', THE PanneWorkflowService SHALL changer le statut à 'resolue' et définir date_resolution
8. WHEN decision_finale est 'irrecuperable', THE PanneWorkflowService SHALL changer le statut à 'irrecuperable' et définir date_resolution
9. THE PanneWorkflowService SHALL enregistrer technicien_id avec l'utilisateur authentifié
10. THE PanneWorkflowService SHALL mettre à jour le statut de la maintenance associée à 'terminee'
11. THE PanneWorkflowService SHALL enregistrer un mouvement via MouvementService avec type 'panne_diagnostiquee'
12. WHEN le statut devient 'resolue', THE PanneWorkflowService SHALL notifier le gestionnaire stock via NotificationService
13. WHEN le statut devient 'irrecuperable', THE PanneWorkflowService SHALL notifier le gestionnaire stock pour décision sur l'équipement
14. IF la panne n'a pas le bon statut, THEN THE PanneController SHALL retourner un code HTTP 422 avec message explicatif
15. THE PanneController SHALL retourner un code HTTP 200 avec la panne diagnostiquée

### Requirement 8: Validation des données de déclaration

**User Story:** En tant que système, je veux valider toutes les données de déclaration de panne, afin de garantir l'intégrité et la cohérence des informations.

#### Acceptance Criteria

1. THE StorePanneRequest SHALL valider que equipement_id existe dans la table equipements
2. THE StorePanneRequest SHALL valider que equipement_id appartient à l'agence de l'utilisateur
3. THE StorePanneRequest SHALL valider que description contient au moins 10 caractères
4. THE StorePanneRequest SHALL valider que niveau_gravite est dans la liste: mineure, majeure, critique
5. WHERE photos est fourni, THE StorePanneRequest SHALL valider que c'est un tableau JSON
6. WHERE photos est fourni, THE StorePanneRequest SHALL valider que chaque photo a un chemin valide
7. WHERE photos est fourni, THE StorePanneRequest SHALL limiter à 5 photos maximum
8. THE StorePanneRequest SHALL autoriser la requête uniquement si l'utilisateur a le rôle Agent ou GestionnaireStock

### Requirement 9: Validation de transmission maintenance

**User Story:** En tant que système, je veux valider les données de transmission à la maintenance, afin d'assurer une prise en charge correcte.

#### Acceptance Criteria

1. THE UpdatePanneTransmettreMaintenanceRequest SHALL permettre les champs optionnels: technicien_id, date_prevue, observations
2. WHERE technicien_id est fourni, THE UpdatePanneTransmettreMaintenanceRequest SHALL valider qu'il existe et a le rôle TechnicienMaintenance
3. WHERE date_prevue est fournie, THE UpdatePanneTransmettreMaintenanceRequest SHALL valider que c'est une date future
4. THE UpdatePanneTransmettreMaintenanceRequest SHALL autoriser la requête uniquement si l'utilisateur a le rôle GestionnaireStock

### Requirement 10: Validation de diagnostic technicien

**User Story:** En tant que système, je veux valider les données de diagnostic, afin d'assurer la complétude de l'intervention technique.

#### Acceptance Criteria

1. THE UpdatePanneDiagnosticRequest SHALL valider les champs obligatoires: diagnostic_technicien, action_realisee, decision_finale
2. THE UpdatePanneDiagnosticRequest SHALL valider que diagnostic_technicien contient au moins 20 caractères
3. THE UpdatePanneDiagnosticRequest SHALL valider que action_realisee contient au moins 20 caractères
4. THE UpdatePanneDiagnosticRequest SHALL valider que decision_finale est dans la liste: resolue, irrecuperable
5. WHERE cout_reparation est fourni, THE UpdatePanneDiagnosticRequest SHALL valider que c'est un nombre décimal positif
6. THE UpdatePanneDiagnosticRequest SHALL autoriser la requête uniquement si l'utilisateur a le rôle TechnicienMaintenance

### Requirement 11: Gestion des permissions et autorisations

**User Story:** En tant que système, je veux appliquer les permissions appropriées pour chaque action, afin de garantir la sécurité et le contrôle d'accès.

#### Acceptance Criteria

1. THE PanneController SHALL vérifier les autorisations via PannePolicy avant chaque action
2. WHEN un Agent consulte les pannes, THE PanneController SHALL limiter aux pannes qu'il a déclarées
3. WHEN un GestionnaireStock consulte les pannes, THE PanneController SHALL limiter aux pannes de son agence
4. WHEN un TechnicienMaintenance consulte les pannes, THE PanneController SHALL autoriser l'accès à toutes les pannes
5. THE PanneController SHALL autoriser la déclaration aux utilisateurs avec rôles Agent ou GestionnaireStock
6. THE PanneController SHALL autoriser la transmission maintenance uniquement aux GestionnaireStock
7. THE PanneController SHALL autoriser le diagnostic uniquement aux TechnicienMaintenance
8. IF l'autorisation échoue, THEN THE PanneController SHALL retourner un code HTTP 403 avec message explicatif

### Requirement 12: Format des réponses API standardisé

**User Story:** En tant que développeur frontend, je veux recevoir des réponses API dans un format standardisé, afin de faciliter l'intégration et le traitement des données.

#### Acceptance Criteria

1. THE PanneController SHALL retourner toutes les réponses succès avec structure: `{success: true, data: {...}, message?: string}`
2. THE PanneController SHALL retourner toutes les réponses erreur avec structure: `{success: false, message: string, errors?: {...}}`
3. WHERE la réponse contient une liste, THE PanneController SHALL inclure un objet meta avec: total, per_page, current_page, last_page
4. THE PanneController SHALL retourner les dates au format ISO 8601
5. THE PanneController SHALL inclure les relations eager-loaded dans data pour éviter les requêtes N+1
6. THE PanneController SHALL utiliser les resource transformers Laravel pour formater les réponses

### Requirement 13: Enregistrement des mouvements d'historique

**User Story:** En tant qu'administrateur système, je veux tracer toutes les actions sur les pannes, afin de disposer d'un historique complet pour l'audit.

#### Acceptance Criteria

1. WHEN une panne est créée, THE PanneController SHALL enregistrer un mouvement avec type 'panne_declaree'
2. WHEN une panne est transmise à la maintenance, THE PanneWorkflowService SHALL enregistrer un mouvement avec type 'panne_transmise_maintenance'
3. WHEN une panne est diagnostiquée, THE PanneWorkflowService SHALL enregistrer un mouvement avec type 'panne_diagnostiquee'
4. THE MouvementService SHALL enregistrer pour chaque mouvement: equipement_id, user_id, type_mouvement, date_mouvement, details
5. THE MouvementService SHALL stocker les détails pertinents en JSON: statut_avant, statut_apres, technicien_assigne, cout_reparation

### Requirement 14: Gestion des notifications automatiques

**User Story:** En tant qu'utilisateur du système, je veux recevoir des notifications automatiques pour les événements critiques, afin de réagir rapidement.

#### Acceptance Criteria

1. WHEN une panne critique est déclarée, THE NotificationService SHALL notifier le GestionnaireStock de l'agence
2. WHEN une panne est transmise à la maintenance, THE NotificationService SHALL notifier le TechnicienMaintenance assigné
3. WHEN une panne est diagnostiquée comme résolue, THE NotificationService SHALL notifier le GestionnaireStock
4. WHEN une panne est diagnostiquée comme irrecuperable, THE NotificationService SHALL notifier le GestionnaireStock avec priorité haute
5. THE NotificationService SHALL utiliser le système de notifications Laravel
6. THE NotificationService SHALL enregistrer les notifications dans la table notifications pour consultation ultérieure

### Requirement 15: Gestion des erreurs et cas limites

**User Story:** En tant que système, je veux gérer correctement tous les cas d'erreur, afin de fournir des messages clairs et éviter les comportements inattendus.

#### Acceptance Criteria

1. IF un equipement_id invalide est fourni, THEN THE PanneController SHALL retourner un code HTTP 422 avec message "Équipement non trouvé"
2. IF l'équipement n'appartient pas à l'agence de l'utilisateur, THEN THE PanneController SHALL retourner un code HTTP 403 avec message "Accès non autorisé à cet équipement"
3. IF une panne est déjà en maintenance, THEN THE PanneController SHALL retourner un code HTTP 422 avec message "Cette panne est déjà en maintenance"
4. IF une panne n'est pas en maintenance lors du diagnostic, THEN THE PanneController SHALL retourner un code HTTP 422 avec message "Cette panne n'est pas en maintenance"
5. IF une exception inattendue survient, THEN THE PanneController SHALL logger l'erreur et retourner un code HTTP 500 avec message générique
6. THE PanneController SHALL utiliser try-catch pour gérer les exceptions Laravel (ModelNotFoundException, AuthorizationException)
7. THE PanneController SHALL valider les IDs numériques avant d'interroger la base de données

