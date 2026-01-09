@echo off
chcp 65001 > nul
echo ========================================
echo   Laravel Scheduler (en continu)
echo ========================================
echo.
echo Le scheduler s'exécute toutes les minutes
echo Appuyez sur Ctrl+C pour arrêter
echo.

cd /d %~dp0

:loop
echo [%date% %time%] Exécution du scheduler...
php artisan schedule:run
timeout /t 60 /nobreak > nul
goto loop
