# Configuration Tailwind CSS - Calendrier de Maintenance

## Vue d'ensemble

Tailwind CSS v3 a été installé et configuré dans le projet pour supporter le design moderne Bento avec des effets Glassmorphism.

## Installation

Les packages suivants ont été installés :
- `tailwindcss@^3` - Framework CSS utilitaire
- `postcss` - Outil de transformation CSS
- `autoprefixer` - Ajout automatique des préfixes CSS pour la compatibilité navigateur

## Fichiers de configuration

### 1. `tailwind.config.js`
Configuration principale de Tailwind avec extensions personnalisées pour le Glassmorphism.

**Extensions personnalisées :**
- `backdropBlur.xs: '2px'` - Flou d'arrière-plan extra-small
- `backgroundColor.glass: 'rgba(255, 255, 255, 0.1)'` - Fond transparent clair
- `backgroundColor.glass-dark: 'rgba(0, 0, 0, 0.1)'` - Fond transparent sombre
- `borderColor.glass: 'rgba(255, 255, 255, 0.2)'` - Bordure transparente claire
- `borderColor.glass-dark: 'rgba(0, 0, 0, 0.2)'` - Bordure transparente sombre

### 2. `postcss.config.js`
Configuration PostCSS pour intégrer Tailwind et Autoprefixer.

### 3. `src/assets/main.css`
Fichier CSS principal contenant :
- Les directives Tailwind (`@tailwind base`, `@tailwind components`, `@tailwind utilities`)
- Les classes personnalisées Glassmorphism dans `@layer utilities`

### 4. `tsconfig.node.json`
Mis à jour pour inclure les fichiers de configuration Tailwind et PostCSS.

## Classes Glassmorphism disponibles

### Classes Tailwind natives

#### Effet de flou d'arrière-plan
- `.backdrop-blur-lg` - Flou d'arrière-plan large (16px)

#### Transparence
- `.bg-white/30` - Fond blanc à 30% d'opacité
- `.bg-white/20` - Fond blanc à 20% d'opacité
- `.bg-white/10` - Fond blanc à 10% d'opacité

#### Bordures translucides
- `.border-white/20` - Bordure blanche à 20% d'opacité
- `.border-white/30` - Bordure blanche à 30% d'opacité

### Classes personnalisées

#### `.glass`
Effet Glassmorphism de base avec flou et transparence.

**Composition :**
```css
backdrop-blur-lg
bg-white/30
border border-white/20
```

**Usage :**
```vue
<div class="glass p-4 rounded-lg">
  Contenu avec effet verre
</div>
```

#### `.glass-card`
Carte avec effet Glassmorphism et ombre portée.

**Composition :**
```css
backdrop-blur-lg
bg-white/10
border border-white/20
shadow-lg
rounded-lg
```

**Usage :**
```vue
<div class="glass-card p-4">
  Carte avec effet verre et ombre
</div>
```

#### `.glass-dark`
Variante sombre de l'effet Glassmorphism.

**Composition :**
```css
backdrop-blur-lg
bg-black/30
border border-black/20
```

#### `.glass-card-hover`
Classes de transition pour effet hover sur les cartes.

**Composition :**
```css
transition-all duration-300
hover:bg-white/20
hover:border-white/30
```

## Classes de statut pour le calendrier

### Statut "Planifié" (Bleu)
```vue
<div class="bg-blue-500/20 border-blue-500 text-blue-700">
  Maintenance planifiée
</div>
```

### Statut "En cours" (Orange)
```vue
<div class="bg-orange-500/20 border-orange-500 text-orange-700">
  Maintenance en cours
</div>
```

### Statut "Terminé" (Vert)
```vue
<div class="bg-green-500/20 border-green-500 text-green-700">
  Maintenance terminée
</div>
```

## Exemples d'utilisation

### Event Card avec Glassmorphism
```vue
<template>
  <div class="glass-card p-4 hover:glass-card-hover cursor-pointer">
    <div class="flex items-center justify-between">
      <span class="text-sm font-semibold">Maintenance préventive</span>
      <span class="px-2 py-1 rounded bg-blue-500/20 border-blue-500 text-blue-700 text-xs">
        Planifié
      </span>
    </div>
    <p class="text-xs mt-2 text-gray-300">EQ-2024-001</p>
    <p class="text-xs text-gray-400">15:00</p>
  </div>
</template>
```

### Modale avec backdrop Glassmorphism
```vue
<template>
  <div class="fixed inset-0 backdrop-blur-sm bg-black/50 flex items-center justify-center">
    <div class="glass-card max-w-2xl w-full p-6 m-4">
      <h2 class="text-xl font-bold mb-4">Détails de la maintenance</h2>
      <!-- Contenu de la modale -->
    </div>
  </div>
</template>
```

## Intégration avec PrimeVue

Tailwind CSS coexiste harmonieusement avec PrimeVue :
- Les classes Tailwind peuvent être utilisées librement sur les composants PrimeVue
- PrimeVue continue d'utiliser son propre système de thème (Aura)
- Les deux systèmes ne se chevauchent pas et peuvent être combinés

## Build et production

Le build de production est fonctionnel :
- Tailwind purge automatiquement les classes inutilisées
- Seules les classes utilisées dans les composants sont incluses dans le bundle final
- La taille du CSS reste optimisée

## Vérification

Pour vérifier que Tailwind fonctionne correctement :

```bash
npm run build-only
```

Le fichier CSS généré doit contenir les classes Tailwind et Glassmorphism.

## Dépannage

### Problème : Classes Tailwind non appliquées
**Solution :** Vérifier que `src/assets/main.css` est importé dans `src/main.js`

### Problème : Erreur PostCSS
**Solution :** Vérifier que les fichiers `tailwind.config.js` et `postcss.config.js` existent à la racine du frontend

### Problème : Classes personnalisées non générées
**Solution :** S'assurer que les classes sont utilisées au moins une fois dans un composant Vue, ou utiliser la directive `safelist` dans `tailwind.config.js`

## Conformité avec les exigences

Cette configuration satisfait les critères d'acceptation du **Requirement 12** :

✅ **12.1** - Tailwind CSS ajouté aux dépendances et configuré dans vite.config  
✅ **12.2** - Classes utilitaires Tailwind utilisables dans tous les composants  
✅ **12.3** - Classes personnalisées Glassmorphism définies (backdrop-blur, bg-opacity)  
✅ **12.4** - Tailwind et PrimeVue coexistent harmonieusement sans conflit

## Prochaines étapes

Les composants du calendrier de maintenance peuvent maintenant utiliser ces classes pour implémenter le design Bento moderne avec effet Glassmorphism.
