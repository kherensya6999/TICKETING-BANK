@echo off
echo ====================================
echo IT SECURITY TICKETING SYSTEM SETUP
echo ====================================
echo.

echo [1/6] Installing Laravel dependencies...
call composer install
if %errorlevel% neq 0 (
    echo ERROR: Composer install failed!
    pause
    exit /b 1
)

echo.
echo [2/6] Copying .env file...
if not exist .env (
    copy .env.example .env
    echo .env file created!
) else (
    echo .env file already exists, skipping...
)

echo.
echo [3/6] Generating application key...
call php artisan key:generate
if %errorlevel% neq 0 (
    echo ERROR: Key generation failed!
    pause
    exit /b 1
)

echo.
echo [4/6] Installing Frontend dependencies...
call npm install
if %errorlevel% neq 0 (
    echo ERROR: npm install failed!
    pause
    exit /b 1
)

echo.
echo ====================================
echo SETUP COMPLETE!
echo ====================================
echo.
echo NEXT STEPS:
echo 1. Create database 'ticketing_db' in phpMyAdmin
echo 2. Edit .env file with database credentials
echo 3. Run: php artisan migrate
echo 4. Run: php artisan db:seed
echo 5. Run: php artisan storage:link
echo.
echo Then start servers:
echo - Terminal 1: php artisan serve
echo - Terminal 2: php artisan queue:work
echo - Terminal 3: npm run dev
echo.
echo Open browser: http://localhost:3000
echo.
pause
