# 🐛 RAPPORT D'INCOHÉRENCES - BUGS TROUVÉS

## 1. 🔴 CRITICAL - Type Maintenance (RÉSOLU)

### Problème
- **Migration**: `enum('type_maintenance', ['preventive', 'corrective'])` (ANGLAIS)
- **MaintenanceRequest**: valide `in:préventif,correctif` (FRANÇAIS)
- **Seeder**: utilisait `'préventif'` et `'correctif'` (FRANÇAIS) ❌

### Impact
**Erreur:** `Data truncated for column 'type_maintenance'`

### Solution ✅
- ✅ Seeder corrigé → `'preventive'` et `'corrective'`
- ✅ MaintenanceRequest à corriger
- ✅ Tests à mettre à jour

---

## 2. 🔴 CRITICAL - Urgence DemandeMateriel

### Problème
**Migration:**
```php
$table->enum('urgence', ['Basse', 'Moyenne', 'Haute'])
```
(Majuscule française)

**Seeder:**
À vérifier s'il utilise les bonnes valeurs

### Impact
Mêmes risques de truncation/validation que maintenance

---

## 3. 🔴 CRITICAL - Statut Demande Matériel

### Problème
**Migration:**
```php
$table->enum('statut', ['en attente', 'approuvé', 'rejeté', 'expédié'])
```
(Français avec espace)

**Seeder:**
À vérifier

### Impact
Erreurs si seeder utilise différentes valeurs

---

## 4. 🟠 HIGH - Valeurs Enum Incohérentes

### Demandes Matériel
- **Migration:** `['en attente', 'approuvé', 'rejeté', 'expédié']`
- **FormRequest:** À vérifier la validation
- **Seeder:** À vérifier les valeurs

### Transferts
- **Migration:** `['demande', 'approuve', 'expedie', 'recu', 'refuse']`
- **FormRequest:** À vérifier
- **Seeder:** À vérifier

### Pannes
- **Migration:** Gravité `['mineure', 'majeure', 'critique']`
- **Migration:** Statut `['declaree', 'en_cours', 'en_maintenance', 'resolue', 'irrecuperable']`
- **FormRequest:** À vérifier
- **Seeder:** À vérifier

### Affectations
- **Migration:** `etat_retour: ['bon', 'abime', 'non_fonctionnel']`

### Pertes
- **Migration:** Type `['perte', 'vol', 'casse']`
- **Migration:** Statut `['declaree', 'validee', 'cloturee']`

### Catégories
- **Migration:** Statut `['actif', 'inactif', 'archive']`

### Agences
- **Migration:** Type `['generale', 'sous_agence']`
- **Migration:** Statut `['active', 'inactive']`

---

## 5. 🟠 HIGH - Cohérence Français/Anglais

**PROBLÈME MAJEUR:** Le projet mélange anglais et français dans les enums!

| Table | Champ | Valeurs | Langue |
|-------|-------|---------|--------|
| maintenances | type_maintenance | preventive, corrective | EN |
| pannes | niveau_gravite | mineure, majeure, critique | FR |
| pannes | statut | declaree, en_cours, resolue | FR |
| pertes | type | perte, vol, casse | FR |
| affectations | etat_retour | bon, abime, non_fonctionnel | FR |
| transferts | statut | demande, approuve, expedie | FR |
| demandes_materiel | urgence | Basse, Moyenne, Haute | FR Majuscule |
| demandes_materiel | statut | en attente, approuvé, rejeté | FR avec accents |

### Recommandation
**CHOISIR:** Soit **ANGLAIS PARTOUT** soit **FRANÇAIS PARTOUT**

---

## 6. 🟡 MEDIUM - FormRequest Validations

**MaintenanceRequest:**
```php
'type_maintenance' => 'required|in:préventif,correctif',
```
❌ Devrait être `in:preventive,corrective`

**À Vérifier:**
- DemandeMaterielRequest
- TransfertRequest
- PanneRequest
- AffectationRequest
- PerteRequest

---

## 7. 🟡 MEDIUM - Tests Unitaires

**MaintenanceRequestTest (line 60):**
```php
'type_maintenance' => 'préventif',  // ❌ FRANÇAIS
```
Devrait être `preventive`

**MaintenanceRequestTest (line 129):**
```php
'in:préventif,correctif'  // ❌ Test mauvais
```
Devrait valider `in:preventive,corrective`

---

## 8. 🔴 CRITICAL - Session Driver

**Configuration .env:**
```
SESSION_DRIVER=database
```

**Problème:** Session stockée en DB mais SESSION_DRIVER devrait être `cookie` si pas besoin de session persistante en DB.

**Solution:** Changer en `SESSION_DRIVER=cookie` ou s'assurer que `sessions` table existe.

---

## 9. 🟠 HIGH - Models Fillable Incohérence

**Maintenance Model:**
```php
protected $fillable = [
    'panne_id',           // ← Existe pas dans migration!
    'equipement_id',
    'technicien_id',
    'type_maintenance',
    ...
    'technicien',         // ← Enum dans migration, champ string ailleurs
];
```

**Migration a `technicien` (string)** mais aussi **`technicien_id` (foreignKey)**
= Confusion possible

---

## 10. 🟠 HIGH - PostCSS Tailwind Configuration

**Problème existant (déjà adressé):**
PostCSS devrait avoir UNIQUEMENT `autoprefixer`, pas `@tailwindcss/postcss`

---

## RÉSUMÉ DES ACTIONS REQUISES

### 🔴 URGENT (Bloquant)
- [ ] Fixer MaintenanceRequest: `'type_maintenance' => 'required|in:preventive,corrective'`
- [ ] Vérifier tous les seeders pour enums
- [ ] Fixer MaintenanceRequestTest

### 🟠 IMPORTANT
- [ ] Standardiser: FRANCAIS OU ANGLAIS pour les enums (recommandation: ANGLAIS)
- [ ] Mettre à jour toutes les FormRequests
- [ ] Nettoyer Maintenance model (panne_id ou technicien)
- [ ] Fixer SessionDriver en .env

### 🟡 SOUHAITABLE
- [ ] Ajouter validation enum dans Models avec casts
- [ ] Tests sur tous les seeders
- [ ] Documentation des valeurs enum valides

