# Dev avec rechargement auto. Sur Windows, Ctrl+C ou une sauvegarde peut afficher ERROR: CancelledError — c'est normal.
Set-Location $PSScriptRoot
& .\venv\Scripts\uvicorn.exe bot_server:app --host 0.0.0.0 --port 8001 --reload
