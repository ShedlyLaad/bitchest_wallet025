@echo off
chcp 65001 > nul
echo ========================================
echo   Arrêt de Redis
echo ========================================
echo.

cd /d %~dp0

docker-compose stop redis

if %ERRORLEVEL% EQU 0 (
    echo ✅ Redis arrêté avec succès
) else (
    echo ❌ Erreur lors de l'arrêt
)

echo.
pause
