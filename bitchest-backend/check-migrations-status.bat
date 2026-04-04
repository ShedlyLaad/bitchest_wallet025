@echo off
echo ========================================
echo VERIFICATION DE L'ETAT DES MIGRATIONS
echo ========================================
echo.

cd /d "%~dp0"

echo [1/3] Verification de l'etat des migrations...
php artisan migrate:status

echo.
echo [2/3] Verification de la connexion a la base de donnees...
php artisan db:show

echo.
echo [3/3] Comptage des donnees existantes...
php -r "require 'vendor/autoload.php'; $app = require_once 'bootstrap/app.php'; $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap(); try { $users = DB::table('users')->count(); $portfolios = DB::table('portfolios')->count(); $transactions = DB::table('transactions')->count(); echo \"Users: $users\nPortfolios: $portfolios\nTransactions: $transactions\n\"; } catch (Exception $e) { echo \"Erreur: \" . $e->getMessage() . \"\n\"; }"

echo.
echo ========================================
echo VERIFICATION TERMINEE
echo ========================================
echo.
echo IMPORTANT:
echo - Si vous voyez des migrations avec "No", elles seront executees avec "php artisan migrate"
echo - La commande "php artisan migrate" NE SUPPRIME PAS vos donnees existantes
echo - Pour plus d'infos, consultez GUIDE_MIGRATIONS_SECURISE.md
echo.
pause
