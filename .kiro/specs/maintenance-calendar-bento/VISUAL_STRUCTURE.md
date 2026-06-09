# 🎨 Structure Visuelle - Calendrier de Maintenance

## 🖼️ Vue d'Ensemble de l'Interface

```
╔══════════════════════════════════════════════════════════════════════╗
║                      GESTPARK - LAYOUT PRINCIPAL                     ║
╠═══════════╦══════════════════════════════════════════════════════════╣
║           ║                                                          ║
║  SIDEBAR  ║                    MAIN CONTENT                          ║
║           ║                                                          ║
║  ┌─────┐  ║  ╔═══════════════════════════════════════════════════╗  ║
║  │ 🏠  │  ║  ║         TOP BAR                                   ║  ║
║  └─────┘  ║  ║  ☰  Page Title                           🔔      ║  ║
║  Dashboard║  ╚═══════════════════════════════════════════════════╝  ║
║           ║                                                          ║
║  ┌─────┐  ║  ┌────────────────────────────────────────────────┐    ║
║  │ 🏢  │  ║  │                                                │    ║
║  └─────┘  ║  │                                                │    ║
║  Agences  ║  │           CONTENU DE LA PAGE                   │    ║
║           ║  │                                                │    ║
║  ┌─────┐  ║  │         (Maintenances Hub OU Calendrier)       │    ║
║  │ 👥  │  ║  │                                                │    ║
║  └─────┘  ║  │                                                │    ║
║  Agents   ║  │                                                │    ║
║           ║  │                                                │    ║
║  ┌─────┐  ║  └────────────────────────────────────────────────┘    ║
║  │ 🔧  │←─╬──→ MAINTENANCES (Clic ici pour page hub)              ║
║  └─────┘  ║                                                          ║
║  Mainten. ║                                                          ║
║           ║                                                          ║
║  ┌─────┐  ║                                                          ║
║  │ 📅  │←─╬──→ CALENDRIER (Clic ici pour accès direct)             ║
║  └─────┘  ║                                                          ║
║  Calendar ║                                                          ║
║           ║                                                          ║
║  ┌─────┐  ║                                                          ║
║  │ ...  │  ║                                                          ║
║  └─────┘  ║                                                          ║
║           ║                                                          ║
╚═══════════╩══════════════════════════════════════════════════════════╝
```

---

## 📄 Page 1 : Maintenances Hub (`/maintenances`)

```
┌────────────────────────────────────────────────────────────────┐
│  ← Menu Latéral                                        🔔       │
├────────────────────────────────────────────────────────────────┤
│                                                                │
│  🔧 Gestion des Maintenances                                   │
│  Planifiez, suivez et gérez toutes vos maintenances           │
│                                                                │
│  ╔═══════════════╗  ╔═══════════════╗  ╔═══════════════╗     │
│  ║   📅          ║  ║   📋          ║  ║   ➕          ║     │
│  ║ CALENDRIER    ║  ║   LISTE       ║  ║  NOUVELLE     ║     │
│  ║               ║  ║               ║  ║               ║     │
│  ║ Vue mensuelle ║  ║ Tableau       ║  ║ Planifier     ║     │
│  ║ avec filtres  ║  ║ détaillé      ║  ║ maintenance   ║     │
│  ║               ║  ║               ║  ║               ║     │
│  ║ [OUVRIR] →    ║  ║ Prochainement ║  ║ Prochainement ║     │
│  ╚═══════════════╝  ╚═══════════════╝  ╚═══════════════╝     │
│       ↓ Clic                                                   │
│                                                                │
│  📊 Statistiques Rapides                                       │
│  ┌─────────┐  ┌─────────┐  ┌─────────┐  ┌─────────┐        │
│  │ 🔵  -   │  │ 🟠  -   │  │ 🟢  -   │  │ 🟣  -   │        │
│  │Planif.  │  │En cours │  │Terminé  │  │ Total   │        │
│  └─────────┘  └─────────┘  └─────────┘  └─────────┘        │
│                                                                │
│  ⚡ Accès Rapide                                               │
│  [ 📅 Voir le Calendrier ]  [ 📊 Export ]  [ 📈 Rapports ]   │
│       ↓ Clic                                                   │
└────────────────────────────────────────────────────────────────┘
```

---

## 📅 Page 2 : Calendrier (`/maintenances/calendrier`)

```
┌────────────────────────────────────────────────────────────────┐
│  ← Menu Latéral                                        🔔       │
├────────────────────────────────────────────────────────────────┤
│                                                                │
│  ← Retour aux Maintenances                                     │
│       ↑ Clic pour revenir                                      │
│                                                                │
│  📅 Calendrier de Maintenance                                  │
│  Vue mensuelle des maintenances planifiées                     │
│                                                                │
│  ┌──────────────────────────────────────────────────────────┐ │
│  │  ◀  JUIN 2026  ▶         Aujourd'hui                     │ │
│  │                                                           │ │
│  │  [ Type: Tous ▼ ]  [ Statut: Tous ▼ ]                   │ │
│  └──────────────────────────────────────────────────────────┘ │
│                                                                │
│  ╔══════════════════════════════════════════════════════════╗ │
│  ║  Lun    Mar    Mer    Jeu    Ven    Sam    Dim          ║ │
│  ╠══════════════════════════════════════════════════════════╣ │
│  ║       1      2      3      4      5      6      7        ║ │
│  ║                    🔵M1                                   ║ │
│  ║────────────────────────────────────────────────────────  ║ │
│  ║   8      9     10     11     12     13     14            ║ │
│  ║  🔵M2         🟠M3         🟢M4                          ║ │
│  ║        ↓ Clic sur carte                                  ║ │
│  ║────────────────────────────────────────────────────────  ║ │
│  ║  15     16     17     18     19     20     21            ║ │
│  ║ 🔵M5         🟠M6  🔵M7 +2 autres                        ║ │
│  ║                            ↑ Indicateur                  ║ │
│  ║────────────────────────────────────────────────────────  ║ │
│  ║  22     23     24     25     26     27     28            ║ │
│  ║        🟢M8         🔵M9                                 ║ │
│  ║────────────────────────────────────────────────────────  ║ │
│  ║  29     30                                               ║ │
│  ║ 🟠M10                                                    ║ │
│  ╚══════════════════════════════════════════════════════════╝ │
│                                                                │
│  Légende:                                                      │
│  🔵 Planifié  🟠 En cours  🟢 Terminé                         │
│                                                                │
└────────────────────────────────────────────────────────────────┘
```

---

## 🔲 Modal : Détails Maintenance (Overlay)

```
┌────────────────────────────────────────────────────────────────┐
│                    BACKDROP (Semi-transparent)                 │
│                                                                │
│      ╔═══════════════════════════════════════════════╗        │
│      ║  Détails de la Maintenance              ❌   ║        │
│      ║                                         Close  ║        │
│      ╠═══════════════════════════════════════════════╣        │
│      ║                                               ║        │
│      ║  🔧 Maintenance Préventive                    ║        │
│      ║  🔵 Planifié                                 ║        │
│      ║                                               ║        │
│      ║  ┌─────────────────────────────────────────┐ ║        │
│      ║  │ 📅 DATES                                │ ║        │
│      ║  │ Date prévue : 15/06/2026 à 10:00       │ ║        │
│      ║  │ Durée estimée : 2 heures                │ ║        │
│      ║  └─────────────────────────────────────────┘ ║        │
│      ║                                               ║        │
│      ║  ┌─────────────────────────────────────────┐ ║        │
│      ║  │ 👥 RESPONSABLES                         │ ║        │
│      ║  │ Responsable : Jean Dupont               │ ║        │
│      ║  └─────────────────────────────────────────┘ ║        │
│      ║                                               ║        │
│      ║  ┌─────────────────────────────────────────┐ ║        │
│      ║  │ 📦 ÉQUIPEMENT                           │ ║        │
│      ║  │ Référence : EQ-2024-001                 │ ║        │
│      ║  │ Marque : HP | Modèle : ProBook 450      │ ║        │
│      ║  └─────────────────────────────────────────┘ ║        │
│      ║                                               ║        │
│      ║  ┌─────────────────────────────────────────┐ ║        │
│      ║  │ 💰 COÛT                                 │ ║        │
│      ║  │ 150,00 XOF                              │ ║        │
│      ║  └─────────────────────────────────────────┘ ║        │
│      ║                                               ║        │
│      ║  ┌─────────────────────────────────────────┐ ║        │
│      ║  │ 📝 OBSERVATIONS                         │ ║        │
│      ║  │ Maintenance préventive annuelle         │ ║        │
│      ║  └─────────────────────────────────────────┘ ║        │
│      ║                                               ║        │
│      ║            [ Fermer ]                         ║        │
│      ╚═══════════════════════════════════════════════╝        │
│                                                                │
│  Clic backdrop ou Esc → Ferme la modal                        │
└────────────────────────────────────────────────────────────────┘
```

---

## 🔔 NotificationCenter (Slide-over Panel)

```
┌────────────────────────────────────────────────────────────────┐
│  Menu                                               🔔(2)       │
│                                                     ↓ Clic      │
├────────────────────────────────────────────────────┬───────────┤
│                                                    │           │
│  Contenu Principal                                 │╔═════════╗│
│                                                    │║ NOTIF.  ║│
│                                                    │║         ║│
│                                                    │║  ❌     ║│
│                                                    │║         ║│
│                                                    │║ ┌─────┐ ║│
│                                                    │║ │🔧   │ ║│
│                                                    │║ │Main.│ ║│
│                                                    │║ │prév.│ ║│
│                                                    │║ └─────┘ ║│
│                                                    │║         ║│
│                                                    │║ ┌─────┐ ║│
│                                                    │║ │⚠️   │ ║│
│                                                    │║ │Panne│ ║│
│                                                    │║ │     │ ║│
│                                                    │║ └─────┘ ║│
│                                                    │║         ║│
│                                                    │║ ┌─────┐ ║│
│                                                    │║ │✉️   │ ║│
│                                                    │║ │Tran.│ ║│
│                                                    │║ │     │ ║│
│                                                    │║ └─────┘ ║│
│                                                    │╚═════════╝│
│                                                    │           │
└────────────────────────────────────────────────────┴───────────┘
                                                     Slide-in GSAP
```

---

## 🎨 Carte d'Événement (Event Card)

```
┌─────────────────────────────────┐
│█                                │  ← Barre colorée statut
│█  🔧 10:00                      │
│█                                │
│█  EQ-2024-001                   │
│█                                │
│█  🔵 Planifié    préventif      │
└─────────────────────────────────┘
     ↑ Hover → Scale 1.02
```

### Variantes selon Statut

```
PLANIFIÉ                EN COURS              TERMINÉ
┌──────────┐           ┌──────────┐          ┌──────────┐
│█ 🔧      │           │█ 🔧      │          │█ 🔧      │
│█ Bleu    │           │█ Orange  │          │█ Vert    │
│█ 🔵      │           │█ 🟠      │          │█ 🟢      │
└──────────┘           └──────────┘          └──────────┘
```

---

## 🗂️ Hiérarchie des Composants

```
MaintenanceCalendarView.vue (Container)
│
├─ Navigation Controls
│  ├─ Breadcrumb (← Retour)
│  ├─ Month Navigation (◀ ▶)
│  └─ Filters (Type, Statut)
│
├─ Loading State (si loading)
│  └─ Spinner + Message
│
├─ Error State (si error)
│  └─ Icon + Message + Retry
│
├─ CalendarGrid.vue (Presentational)
│  ├─ Days Header (Lun-Dim)
│  └─ Weeks Grid
│     └─ CalendarDay.vue × 35-42 (Presentational)
│        ├─ Day Number
│        └─ Events Container
│           ├─ MaintenanceEventCard.vue (max 3)
│           │  ├─ Status Bar
│           │  ├─ Time
│           │  ├─ Equipment Ref
│           │  └─ Status Badge
│           └─ "+N autres" indicator
│
└─ MaintenanceDetailsModal.vue (Overlay)
   ├─ Backdrop
   └─ Panel
      ├─ Header (Close button)
      ├─ Sections (Dates, Responsables, ...)
      └─ Footer (Close button)
```

---

## 📱 Vue Mobile (< 768px)

```
┌──────────────────┐
│ ☰  GESTPARK  🔔 │
├──────────────────┤
│                  │
│ ← Retour         │
│                  │
│ 📅 Calendrier    │
│                  │
│ ◀ JUIN 26 ▶     │
│                  │
│ [Filtres ▼]     │
│                  │
│ Lun Mar Mer ...  │
│ ┌──┬──┬──┬──┐  │
│ │1 │2 │3 │4 │  │
│ ├──┼──┼──┼──┤  │
│ │8 │9 │10│11│  │
│ │🔵│  │🟠│  │  │
│ └──┴──┴──┴──┘  │
│                  │
│ Clic → Modal    │
│ plein écran      │
│                  │
└──────────────────┘
```

---

## 🎯 Points d'Interaction Clés

### 1. Menu Latéral
```
Clic "Maintenances" → /maintenances (Hub)
Clic "Calendrier" → /maintenances/calendrier (Direct)
```

### 2. Page Hub Maintenances
```
Clic Carte "Calendrier" → /maintenances/calendrier
Clic "Voir le Calendrier" → /maintenances/calendrier
```

### 3. Page Calendrier
```
Clic "← Retour" → /maintenances (Hub)
Clic "◀" → Mois précédent
Clic "▶" → Mois suivant
Clic "Aujourd'hui" → Mois actuel
Clic Filtre Type → Filtre maintenances
Clic Filtre Statut → Filtre maintenances
Clic Event Card → Ouvre modal
```

### 4. Modal Détails
```
Clic ❌ → Ferme modal
Clic Backdrop → Ferme modal
Touche Esc → Ferme modal
```

### 5. NotificationCenter
```
Clic 🔔 → Ouvre panel
Clic backdrop → Ferme panel
```

---

## 🌈 Palette de Couleurs

### Statuts
```
🔵 Bleu    #3b82f6  Planifié
🟠 Orange  #f97316  En cours
🟢 Vert    #22c55e  Terminé
```

### UI Elements
```
Primary    #3b82f6  Boutons, liens actifs
Success    #10b981  Actions positives
Warning    #f59e0b  Alertes
Danger     #ef4444  Erreurs, suppressions
Gray       #64748b  Textes secondaires
```

### Glassmorphism
```
Background   rgba(255,255,255,0.9)
Backdrop     blur(10px)
Border       rgba(255,255,255,0.3)
Shadow       0 4px 6px rgba(0,0,0,0.1)
```

---

## ✨ Animations GSAP

### Calendar Entry
```
Grid: opacity 0→1, y 20→0, duration 0.5s
Cells: stagger animation, scale 0.95→1
```

### Modal Open
```
Backdrop: opacity 0→1, duration 0.3s
Panel: opacity 0→1, y 30→0, scale 0.95→1, ease back.out
```

### Modal Close
```
Panel: opacity 1→0, y -20, scale 0.95, duration 0.3s
Backdrop: opacity 1→0, duration 0.2s
```

### Panel Slide
```
NotificationCenter: x 300→0, opacity 0→1, duration 0.4s
```

### Card Hover
```
Scale: 1→1.02
Shadow: increase
Duration: 0.2s
```

---

## 📐 Dimensions Responsives

### Breakpoints
```
sm:  640px   Petits mobiles
md:  768px   Tablettes
lg:  1024px  Desktop
xl:  1280px  Large desktop
```

### Grille Calendaire
```
Desktop:  7 colonnes fixes
Tablet:   7 colonnes compactées
Mobile:   7 colonnes mini (overflow scroll optionnel)
```

### Modal
```
Desktop:  max-width 600px, centré
Tablet:   max-width 90%, centré
Mobile:   100% width, 100% height (plein écran)
```

---

## 🎬 Flux Utilisateur Complet

```
CONNEXION
   ↓
DASHBOARD
   ↓
Menu Latéral → MAINTENANCES (ou CALENDRIER direct)
   ↓
╔════════════════════════╗
║  PAGE: MAINTENANCES    ║
║                        ║
║  [📅 Calendrier]       ║  → Clic
║  [📋 Liste]            ║
║  [➕ Nouvelle]         ║
╚════════════════════════╝
   ↓
╔════════════════════════╗
║  PAGE: CALENDRIER      ║
║                        ║
║  ← Retour              ║
║  ◀ JUIN 2026 ▶        ║
║                        ║
║  [Grille + Events]     ║  → Clic événement
╚════════════════════════╝
   ↓
╔════════════════════════╗
║  MODAL: DÉTAILS        ║
║                        ║
║  [Toutes les infos]    ║
║                        ║
║  [Fermer] ❌           ║  → Clic
╚════════════════════════╝
   ↓
RETOUR AU CALENDRIER
```

---

*🎨 Structure visuelle complète du calendrier de maintenance*
