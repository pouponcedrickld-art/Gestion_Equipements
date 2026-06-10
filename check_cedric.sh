#!/bin/bash
RED='\033[0;31m'; GREEN='\033[0;32m'; YELLOW='\033[1;33m'; BLUE='\033[0;34m'; NC='\033[0m'
PASS=0; FAIL=0; WARN=0
check_file(){ local f="$1" d="$2" m="${3:-100}"; if [ -f "$f" ]; then local s=$(wc -c < "$f"); if [ "$s" -ge "$m" ]; then echo -e "${GREEN}✅${NC} $d"; ((PASS++)); else echo -e "${YELLOW}⚠️${NC} $d (petit: ${s}b)"; ((WARN++)); fi; else echo -e "${RED}❌${NC} $d (MANQUANT)"; ((FAIL++)); fi; }
check_route(){ if [ -f "$1" ] && grep -q "$2" "$1" 2>/dev/null; then echo -e "${GREEN}✅${NC} $3"; ((PASS++)); else echo -e "${RED}❌${NC} $3 (MANQUANT)"; ((FAIL++)); fi; }
check_vroute(){ if [ -f "$1" ] && grep -q "path.*$2" "$1" 2>/dev/null; then echo -e "${GREEN}✅${NC} $3"; ((PASS++)); else echo -e "${RED}❌${NC} $3 (MANQUANT)"; ((FAIL++)); fi; }

echo -e "${BLUE}═══ VERIFICATION MODULE CEDRIC ═══${NC}"

echo -e "\n${BLUE}BACKEND${NC}"
check_file "backend/app/Http/Controllers/Api/AuthController.php" "AuthController" 2000
check_file "backend/app/Http/Controllers/Api/UserController.php" "UserController" 2000
check_file "backend/app/Http/Controllers/Api/AgenceController.php" "AgenceController" 2000
check_file "backend/app/Http/Controllers/Api/DashboardController.php" "DashboardController" 2000
check_file "backend/app/Http/Middleware/CheckRole.php" "CheckRole" 500
check_file "backend/app/Http/Middleware/CheckAgenceScope.php" "CheckAgenceScope" 500
check_file "backend/app/Services/Auth2FAService.php" "Auth2FAService" 500
check_file "backend/app/Services/StockScopingService.php" "StockScopingService" 500
check_file "backend/app/Models/User.php" "Model User" 1000
check_file "backend/app/Models/Agence.php" "Model Agence" 1000

echo -e "\n${BLUE}ROUTES API${NC}"
check_file "backend/routes/api.php" "routes/api.php" 1000
check_route "backend/routes/api.php" "login" "Route /login"
check_route "backend/routes/api.php" "logout" "Route /logout"
check_route "backend/routes/api.php" "me" "Route /me"
check_route "backend/routes/api.php" "agences" "Route /agences"
check_route "backend/routes/api.php" "users" "Route /users"
check_route "backend/routes/api.php" "dashboard" "Route /dashboard"

echo -e "\n${BLUE}MIGRATIONS & SEEDERS${NC}"
check_file "backend/database/seeders/RoleSeeder.php" "RoleSeeder" 500
check_file "backend/database/seeders/UserSeeder.php" "UserSeeder" 500
check_file "backend/database/seeders/AgenceSeeder.php" "AgenceSeeder" 500

echo -e "\n${BLUE}FRONTEND - VUES${NC}"
check_file "frontend/src/views/auth/LoginView.vue" "LoginView" 2000
check_file "frontend/src/views/dashboard/DashboardView.vue" "DashboardView" 2000
check_file "frontend/src/views/agences/AgencesView.vue" "AgencesView" 2000
check_file "frontend/src/views/agences/AgenceFormView.vue" "AgenceFormView" 1500
check_file "frontend/src/views/users/UsersView.vue" "UsersView" 2000
check_file "frontend/src/views/users/UserFormView.vue" "UserFormView" 1500

echo -e "\n${BLUE}FRONTEND - STORES & ROUTER${NC}"
check_file "frontend/src/stores/authStore.js" "authStore" 1000
check_file "frontend/src/stores/agenceStore.js" "agenceStore" 1000
check_file "frontend/src/stores/userStore.js" "userStore" 1000
check_file "frontend/src/layouts/MainLayout.vue" "MainLayout" 2000
check_file "frontend/src/router/index.js" "Router" 1000
check_vroute "frontend/src/router/index.js" "/agences" "Vue-route /agences"
check_vroute "frontend/src/router/index.js" "/users" "Vue-route /users"
check_vroute "frontend/src/router/index.js" "/login" "Vue-route /login"
check_vroute "frontend/src/router/index.js" "/dashboard" "Vue-route /dashboard"

echo -e "\n${BLUE}PERMISSIONS & MENU${NC}"
check_file "frontend/src/utils/permissions.js" "permissions.js" 500
check_route "frontend/src/utils/permissions.js" "getMenuItems" "getMenuItems()"
check_route "frontend/src/utils/permissions.js" "/agences" "Menu /agences"
check_route "frontend/src/utils/permissions.js" "/users" "Menu /users"

echo -e "\n${BLUE}═══ RÉSULTAT ═══${NC}"
TOTAL=$((PASS+WARN+FAIL)); SCORE=$((PASS*100/TOTAL))
echo -e "Passés: ${GREEN}$PASS${NC} | Avertis: ${YELLOW}$WARN${NC} | Manquants: ${RED}$FAIL${NC}"
echo -e "Score: ${SCORE}%"
[ "$FAIL" -eq 0 ] && echo -e "${GREEN}🎉 MODULE COMPLET${NC}" || echo -e "${RED}❌ INCOMPLET - $FAIL manquant(s)${NC}"
