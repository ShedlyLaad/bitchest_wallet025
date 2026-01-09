@echo off
chcp 65001 > nul
echo ========================================
echo   Mise à jour des prix depuis Coinbase
echo ========================================
echo.

cd /d %~dp0

php artisan crypto:update-prices

if %ERRORLEVEL% EQU 0 (
    echo.
    echo ✅ Mise à jour réussie!
) else (
    echo.
    echo ❌ Erreur lors de la mise à jour
    echo    Vérifiez les logs: storage\logs\laravel.log
)

echo.
pause
