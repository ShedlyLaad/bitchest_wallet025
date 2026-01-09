@echo off
chcp 65001 > nul
echo ========================================
echo   Configuration Complète BitChest + Redis
echo ========================================
echo.
echo Ce script va configurer votre projet avec Redis
echo pour un affichage ultra-rapide des prix crypto
echo.
echo Étapes:
echo   1. Installation Predis
echo   2. Configuration .env
echo   3. Démarrage Redis
echo   4. Rafraîchissement DB (garde les users)
echo   5. Initialisation Redis
echo   6. Mise à jour depuis Coinbase
echo.
pause

cd /d %~dp0

echo.
echo [1/6] Installation Predis...
composer require predis/predis
if %ERRORLEVEL% NEQ 0 (
    echo ❌ Erreur lors de l'installation de Predis
    pause
    exit /b 1
)
echo ✅ Predis installé
echo.

echo [2/6] Configuration .env...
echo Vérifiez que votre .env contient:
echo   REDIS_CLIENT=predis
echo   REDIS_HOST=127.0.0.1
echo   REDIS_PORT=6379
echo.
php artisan config:clear
echo ✅ Configuration nettoyée
echo.

echo [3/6] Démarrage Redis avec Docker...
docker-compose up -d redis
if %ERRORLEVEL% NEQ 0 (
    echo ❌ Erreur lors du démarrage de Redis
    echo    Vérifiez que Docker Desktop est démarré
    pause
    exit /b 1
)
echo ✅ Redis démarré
timeout /t 3 /nobreak > nul
docker exec bitchest-redis redis-cli ping
echo.

echo [4/6] Rafraîchissement de la base de données...
echo ⚠️  Cette étape va nettoyer les données mais garder les utilisateurs
set /p confirm="Continuer ? (oui/non): "
if /i not "%confirm%"=="oui" (
    echo Étape annulée
) else (
    php artisan tinker --execute="
    \App\Models\CryptoPriceRecord::truncate();
    \App\Models\Transaction::truncate();
    \App\Models\Portfolio::truncate();
    \App\Models\Notification::truncate();
    \App\Models\CryptoCurrency::truncate();
    echo 'Données nettoyées (users conservés)';
    "
    php artisan db:seed --class=CryptoCurrencySeeder
    php artisan db:seed --class=CryptoAndPricesSeeder
    echo ✅ Base de données rafraîchie
)
echo.

echo [5/6] Initialisation Redis depuis la base de données...
php artisan redis:init --force
echo.

echo [6/6] Mise à jour des prix depuis Coinbase API...
php artisan crypto:update-prices
echo.

echo ========================================
echo   ✅ Configuration terminée!
echo ========================================
echo.
echo Prochaines étapes:
echo   1. Tester l'API: curl http://localhost/api/public/market
echo   2. Démarrer le scheduler: run-scheduler.bat
echo.
echo Documentation:
echo   - START_HERE.md - Guide rapide
echo   - COMMANDES_FINALES.md - Toutes les commandes
echo.
pause
