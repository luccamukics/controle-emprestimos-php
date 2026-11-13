@echo off
cd /d "%~dp0"
where php >nul 2>nul
if errorlevel 1 (
 echo PHP nao encontrado no PATH. Configure o PHP antes de iniciar.
 pause
 exit /b 1
)
if not exist config\database.php (
 echo Copie config\database.example.php para config\database.php e configure o MySQL.
 echo Consulte LEIA_PRIMEIRO.md.
 pause
 exit /b 1
)
echo Abra http://localhost:8000 no navegador.
echo Mantenha esta janela aberta. Ctrl+C encerra o servidor.
php -S localhost:8000 -t public
pause
