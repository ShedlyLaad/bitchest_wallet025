@echo off
echo ========================================
echo MIGRATION SECURISEE - PRESERVATION DES DONNEES
echo ========================================
echo.

cd /d "%~dp0"

echo [ETAPE 1/4] Verification de l'etat actuel...
php artisan migrate:status

echo.
echo [ETAPE 2/4] Comptage des donnees avant migration...
php -r "require 'vendor/autoload.php'; $app = require_once 'bootstrap/app.php'; $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap(); try { $users = DB::table('users')->count(); $portfolios = DB::table('portfolios')->count(); $transactions = DB::table('transactions')->count(); echo \"AVANT MIGRATION:\nUsers: $users\nPortfolios: $portfolios\nTransactions: $transactions\n\"; } catch (Exception $e) { echo \"Erreur: \" . $e->getMessage() . \"\n\"; }"

echo.
echo [ETAPE 3/4] Application des migrations (SECURISE)...
echo Cette commande NE SUPPRIME PAS vos donnees existantes.
echo.
pause

php artisan migrate

echo.
echo [ETAPE 4/4] Verification des donnees apres migration...
php -r "require 'vendor/autoload.php'; $app = require_once 'bootstrap/app.php'; $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap(); try { $users = DB::table('users')->count(); $portfolios = DB::table('portfolios')->count(); $transactions = DB::table('transactions')->count(); echo \"APRES MIGRATION:\nUsers: $users\nPortfolios: $portfolios\nTransactions: $transactions\n\"; } catch (Exception $e) { echo \"Erreur: \" . $e->getMessage() . \"\n\"; }"

echo.
echo ========================================
echo MIGRATION TERMINEE
echo ========================================
echo.
echo Vos donnees ont ete preservees !
echo Pour plus d'infos, consultez GUIDE_MIGRATIONS_SECURISE.md
echo.
pause
