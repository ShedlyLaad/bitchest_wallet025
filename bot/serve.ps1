# Démarrage stable (sans --reload) : évite les tracebacks CancelledError sous Windows.
Set-Location $PSScriptRoot
if (-not (Test-Path ".\venv\Scripts\uvicorn.exe")) {
    Write-Error "venv introuvable. Lance: py -3.12 -m venv venv puis pip install -r requirements.txt"
    exit 1
}
& .\venv\Scripts\uvicorn.exe bot_server:app --host 0.0.0.0 --port 8001
