@echo off
chcp 65001 > nul
echo ========================================
echo   Rafraîchissement de la Base de Données
echo ========================================
echo.
echo ⚠️  ATTENTION : Cette opération va supprimer toutes les données
echo    SAUF les utilisateurs (qui seront conservés)
echo.
set /p confirm="Êtes-vous sûr ? (oui/non): "
if /i not "%confirm%"=="oui" (
    echo Opération annulée
    pause
    exit /b
)

cd /d %~dp0

echo.
echo [1/6] Sauvegarde des utilisateurs...
php artisan tinker --execute="if (file_exists('storage/app')) { \$users = \App\Models\User::all(); file_put_contents('storage/app/users_backup.json', json_encode(\$users->toArray(), JSON_PRETTY_PRINT)); echo 'Sauvegardé: ' . \$users->count() . ' utilisateurs'; } else { echo 'Répertoire storage/app non trouvé'; }"

echo.
echo [2/6] Nettoyage des données (cryptos, prix, transactions, portfolios, notifications)...
php artisan tinker --execute="try { \App\Models\CryptoPriceRecord::truncate(); \App\Models\Transaction::truncate(); \App\Models\Portfolio::truncate(); \App\Models\Notification::truncate(); \App\Models\CryptoCurrency::truncate(); echo 'Données nettoyées'; } catch (Exception \$e) { echo 'Erreur: ' . \$e->getMessage(); }"

echo.
echo [3/6] Réexécution des seeders pour les cryptos...
php artisan db:seed --class=CryptoCurrencySeeder
php artisan db:seed --class=CryptoAndPricesSeeder

echo.
echo [4/6] Réinitialisation Redis...
php artisan redis:init --force

echo.
echo [5/6] Mise à jour depuis Coinbase API...
php artisan crypto:update-prices

echo.
echo [6/6] Vérification finale...
php artisan tinker --execute="echo 'Utilisateurs: ' . \App\Models\User::count() . PHP_EOL; echo 'Cryptos: ' . \App\Models\CryptoCurrency::count() . PHP_EOL; echo 'Prix: ' . \App\Models\CryptoPriceRecord::count();"

echo.
echo ========================================
echo   ✅ Rafraîchissement terminé!
echo ========================================
echo.
echo Résumé:
echo   - Utilisateurs: CONSERVÉS
echo   - Cryptos: RÉINITIALISÉES
echo   - Prix: MIS À JOUR depuis Coinbase
echo   - Redis: INITIALISÉ
echo.
pause
