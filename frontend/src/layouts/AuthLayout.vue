<template>
  <!-- =============================================
  FICHIER : AuthLayout.vue
  RÔLE : Mise en page pour les pages de connexion (login, 2FA)
  Affiche un grand cadre avec un logo à gauche et le formulaire à droite
============================================== -->
  <!-- Conteneur principal : prend toute la hauteur de la page -->
  <div class="auth-layout">
    <!-- Conteneur du cadre de connexion (max 900px de large) -->
    <div class="auth-container">
      <!-- Partie GAUCHE : le logo et le nom de l'app (sidebar bleue) -->
      <div class="auth-sidebar">
        <div class="auth-logo">
          <!-- Icône de boîte (pour matériel) -->
          <i class="pi pi-box" style="font-size: 2rem; color: #3b82f6;"></i>
          <!-- Nom de l'app : GestPark -->
          <h1>GestPark</h1>
          <!-- Sous-titre : Gestion de matériel -->
          <p>Gestion de matériel</p>
        </div>
      </div>
      <!-- Partie DROITE : la page actuelle (login ou 2FA) -->
      <div class="auth-content">
        <router-view /> <!-- Le routeur mettra ici LoginView.vue ou TwoFactorView.vue -->
      </div>
    </div>
  </div>
</template>

<script setup>
// Pas de script ici, c'est juste un layout visuel !
</script>

<style scoped>
/* Style du conteneur principal */
.auth-layout {
  min-height: 100vh; /* Prend toute la hauteur de la fenêtre */
  background-color: var(--bg-app); /* Couleur de fond (définie dans theme.css) */
  display: flex; /* Utilise Flexbox pour centrer le contenu */
  align-items: center; /* Centre verticalement */
  justify-content: center; /* Centre horizontalement */
  padding: 20px; /* Ajoute un peu d'espace autour sur petits écrans */
}

/* Style du cadre de connexion */
.auth-container {
  width: 100%; /* Prend toute la largeur disponible */
  max-width: 900px; /* Mais pas plus de 900px */
  background: var(--bg-card); /* Couleur de fond blanche (définie dans theme.css) */
  border-radius: var(--radius-xl); /* Coins très arrondis */
  box-shadow: var(--shadow-lg); /* Ombre douce pour faire ressortir */
  display: grid; /* Utilise CSS Grid pour séparer en 2 colonnes */
  grid-template-columns: 1fr 1fr; /* 2 colonnes de même taille */
  overflow: hidden; /* Cache ce qui dépasse (pour les coins arrondis) */
}

/* Style de la sidebar bleue à gauche */
.auth-sidebar {
  background-color: var(--primary); /* Couleur bleue principale */
  padding: 60px 40px; /* Espacement intérieur */
  display: flex; /* Flexbox pour centrer le contenu */
  flex-direction: column; /* Aligne les éléments en colonne (icône, titre, sous-titre) */
  justify-content: center; /* Centre verticalement */
  align-items: center; /* Centre horizontalement */
  text-align: center; /* Texte centré */
}

/* Style de l'icône dans le logo */
.auth-logo i {
  font-size: 3.5rem !important; /* Très grande icône */
  color: var(--text-dark) !important; /* Couleur de texte foncée */
  margin-bottom: 20px; /* Espace entre l'icône et le titre */
}

/* Style du titre "GestPark" */
.auth-logo h1 {
  margin: 10px 0 5px; /* Espacement autour du titre */
  color: var(--text-dark); /* Couleur foncée */
  font-size: 2.5rem; /* Très grand */
  font-weight: 800; /* Très gras */
  letter-spacing: 0.1em; /* Espace entre les lettres */
}

/* Style du sous-titre "Gestion de matériel" */
.auth-logo p {
  color: var(--text-dark); /* Couleur foncée */
  opacity: 0.7; /* Un peu transparent */
  margin: 0; /* Pas de marge */
  font-weight: 600; /* Gras */
  text-transform: uppercase; /* Tout en majuscules */
  letter-spacing: 0.05em; /* Espace entre les lettres */
}

/* Style de la partie droite (où est le formulaire) */
.auth-content {
  padding: 60px 50px; /* Espacement intérieur */
  display: flex; /* Flexbox pour centrer le formulaire */
  align-items: center; /* Centre verticalement */
  justify-content: center; /* Centre horizontalement */
  background: var(--bg-card); /* Couleur blanche */
}

/* Pour les écrans petits (téléphones) */
@media (max-width: 768px) {
  .auth-container {
    grid-template-columns: 1fr; /* Plus qu'une seule colonne */
  }
  .auth-sidebar {
    display: none; /* Cache la sidebar bleue à gauche, pas besoin sur téléphone */
  }
}
</style>
