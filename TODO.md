# TODO (BlackboxAI)

## Étape 1 — Notifications (done)
- [x] Analyser l’existant (Notification model/migration, NotificationController, Notification UI).

## Étape 2 — Calendrier maintenances FullCalendar
- [ ] Ajouter endpoint API « maintenances/events » (range start/end + filtres agence/technicien).
- [ ] Créer Vue calendrier FullCalendar (month/week/day) + couleurs statut + actions.

## Étape 3 — PDFs (DomPDF)
- [ ] RapportController / RapportService / DTO / validations / tests.
- [ ] Aperçu + génération + téléchargement.

## Étape 4 — Excel exports (Laravel Excel)
- [ ] Exports + service + controllers + routes + UI.

## Étape 5 — Dashboard analytique (Chart.js)
- [ ] API statistiques + cache + optimisations.
- [ ] UI + graphes + filtres agence.

## Étape 6 — Alertes garantie automatiques
- [ ] Job SendAlertesGarantieJob (90/60/30/7) + canaux email/in-app.
- [ ] Scheduler + tests.

