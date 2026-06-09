#!/bin/bash

echo "🔧 Seeding des maintenances de test pour juin 2026..."
php artisan db:seed --class=MaintenanceSeeder
echo "✅ Terminé ! Vous pouvez maintenant voir les maintenances dans le calendrier."
