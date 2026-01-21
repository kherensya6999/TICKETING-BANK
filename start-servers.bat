@echo off
echo ====================================
echo STARTING ALL SERVERS
echo ====================================
echo.

echo Starting Laravel Backend (Port 8000)...
start "Laravel Backend" cmd /k "php artisan serve"

timeout /t 2 /nobreak >nul

echo Starting Queue Worker...
start "Queue Worker" cmd /k "php artisan queue:work"

timeout /t 2 /nobreak >nul

echo Starting React Frontend (Port 3000)...
start "React Frontend" cmd /k "npm run dev"

echo.
echo ====================================
echo ALL SERVERS STARTED!
echo ====================================
echo.
echo Backend: http://localhost:8000
echo Frontend: http://localhost:3000
echo.
echo Open browser and go to: http://localhost:3000
echo.
echo Press any key to exit (servers will keep running)...
pause >nul
