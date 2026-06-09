#!/bin/bash
echo "═══════════════════════════════════════════════════════════════"
echo "  STRUCTURE COMPLÈTE DU FRONTEND"
echo "═══════════════════════════════════════════════════════════════"

echo ""
echo "📁 ARBORESCENCE DES DOSSIERS ET FICHIERS :"
echo "───────────────────────────────────────────────────────────────"
find frontend -type f | sort

echo ""
echo ""
echo "═══════════════════════════════════════════════════════════════"
echo "  CONTENU DES VUES (structure <template>, <script>, <style>)"
echo "═══════════════════════════════════════════════════════════════"

for f in $(find frontend/src/views -name "*.vue" | sort); do
    echo ""
    echo "▶ FICHIER: $f"
    echo "───────────────────────────────────────────────────────────────"
    grep -n "<template\|<script\|<style\|export default\|defineComponent\|import\|components:\|computed\|methods\|data()\|props\|emits\|setup\|ref\|reactive\|watch\|mounted\|created\|beforeMount\|beforeUnmount\|beforeDestroy\|destroyed\|unmounted" "$f" 2>/dev/null | head -30 | sed 's/^[[:space:]]*/    /'
done

echo ""
echo ""
echo "═══════════════════════════════════════════════════════════════"
echo "  ROUTER (toutes les routes définies)"
echo "═══════════════════════════════════════════════════════════════"
cat frontend/src/router/index.js 2>/dev/null | sed 's/^[[:space:]]*/    /'

echo ""
echo ""
echo "═══════════════════════════════════════════════════════════════"
echo "  STORES PINIA (tous les stores)"
echo "═══════════════════════════════════════════════════════════════"

for f in $(find frontend/src/stores -name "*.js" -o -name "*.ts" 2>/dev/null | sort); do
    echo ""
    echo "▶ STORE: $(basename $f)"
    echo "───────────────────────────────────────────────────────────────"
    grep -n "export const use\|state\|actions\|getters\|defineStore\|pinia\|import\|return" "$f" 2>/dev/null | head -20 | sed 's/^[[:space:]]*/    /'
done

echo ""
echo ""
echo "═══════════════════════════════════════════════════════════════"
echo "  LAYOUTS (tous les layouts)"
echo "═══════════════════════════════════════════════════════════════"
find frontend/src -name "*[Ll]ayout*.vue" -o -name "*[Ll]ayout*.js" 2>/dev/null | sort | sed 's/^[[:space:]]*/    /'

echo ""
echo ""
echo "═══════════════════════════════════════════════════════════════"
echo "  COMPOSANTS (tous les composants réutilisables)"
echo "═══════════════════════════════════════════════════════════════"
find frontend/src/components -type f 2>/dev/null | sort | sed 's/^[[:space:]]*/    /'

echo ""
echo ""
echo "═══════════════════════════════════════════════════════════════"
echo "  MAIN.JS (point d'entrée)"
echo "═══════════════════════════════════════════════════════════════"
cat frontend/src/main.js 2>/dev/null | sed 's/^[[:space:]]*/    /'

echo ""
echo ""
echo "═══════════════════════════════════════════════════════════════"
echo "  APP.VUE (composant racine)"
echo "═══════════════════════════════════════════════════════════════"
cat frontend/src/App.vue 2>/dev/null | sed 's/^[[:space:]]*/    /'

echo ""
echo "═══════════════════════════════════════════════════════════════"
echo "  SCAN TERMINÉ"
echo "═══════════════════════════════════════════════════════════════"
