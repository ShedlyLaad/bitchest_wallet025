@echo off
chcp 65001 > nul
echo ========================================
echo   Démarrage Redis avec Docker
echo ========================================
echo.

cd /d %~dp0

echo [1/2] Vérification Docker...
docker --version > nul 2>&1
if %ERRORLEVEL% NEQ 0 (
    echo ❌ Docker n'est pas installé ou n'est pas dans le PATH
    echo    Veuillez installer Docker Desktop pour Windows
    pause
    exit /b 1
)
echo ✅ Docker détecté
echo.

echo [2/2] Démarrage Redis...
docker-compose up -d redis

if %ERRORLEVEL% EQU 0 (
    echo.
    echo ✅ Redis démarré avec succès!
    echo.
    echo Container: bitchest-redis
    echo Port: 6379
    echo.
    echo Vérification...
    timeout /t 2 /nobreak > nul
    docker exec bitchest-redis redis-cli ping
    echo.
    echo Redis est prêt à l'utilisation!
) else (
    echo.
    echo ❌ Erreur lors du démarrage de Redis
    echo.
    echo Vérifiez:
    echo   1. Docker Desktop est démarré
    echo   2. Port 6379 n'est pas utilisé par une autre application
    echo.
)

echo.
pause
