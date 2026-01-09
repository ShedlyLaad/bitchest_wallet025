@echo off
chcp 65001 > nul
echo ========================================
echo   Initialisation des prix dans Redis
echo ========================================
echo.

cd /d %~dp0

echo [1/2] Vérification Redis...
docker exec bitchest-redis redis-cli ping > nul 2>&1
if %ERRORLEVEL% NEQ 0 (
    echo ❌ Redis n'est pas accessible
    echo    Démarrez Redis avec: start-redis.bat
    pause
    exit /b 1
)
echo ✅ Redis accessible
echo.

echo [2/2] Initialisation depuis la base de données...
php artisan redis:init

if %ERRORLEVEL% EQU 0 (
    echo.
    echo ✅ Initialisation réussie!
) else (
    echo.
    echo ❌ Erreur lors de l'initialisation
    echo    Vérifiez les logs: storage\logs\laravel.log
)

echo.
pause
