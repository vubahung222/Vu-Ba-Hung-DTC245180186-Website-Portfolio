@echo off
REM =============================================
REM Portfolio - Quick Setup Script (Windows)
REM Chạy lần đầu để khởi động toàn bộ stack
REM =============================================

echo.
echo ========================================
echo   PORTFOLIO STACK - QUICK SETUP
echo ========================================
echo.

REM 1. Kiểm tra .env
if not exist .env (
    echo [1/5] Tao file .env tu .env.example...
    copy .env.example .env
    echo.
    echo *** QUAN TRONG: Mo file .env va dien mat khau thuc! ***
    echo     Sau do chay lai script nay.
    echo.
    pause
    exit /b 1
) else (
    echo [1/5] File .env da ton tai. OK.
)

REM 2. Tạo SSL certificate (cần OpenSSL)
if not exist nginx\ssl\server.crt (
    echo [2/5] Tao SSL certificate tu ky...
    if exist "C:\Program Files\Git\usr\bin\openssl.exe" (
        "C:\Program Files\Git\usr\bin\openssl.exe" req -x509 -nodes -days 365 -newkey rsa:2048 ^
            -keyout nginx\ssl\server.key ^
            -out nginx\ssl\server.crt ^
            -subj "/C=VN/ST=HCM/L=HCM/O=Portfolio/CN=localhost" 2>nul
        echo    SSL certificate da tao.
    ) else (
        echo    CANH BAO: OpenSSL khong tim thay. Chay scripts\generate-ssl.sh trong WSL/Git Bash.
        echo    Tao file gia de tiep tuc...
        echo placeholder > nginx\ssl\server.crt
        echo placeholder > nginx\ssl\server.key
    )
) else (
    echo [2/5] SSL certificate da co. OK.
)

REM 3. Validate docker compose config
echo [3/5] Kiem tra Docker Compose config...
docker compose config --quiet 2>nul
if %errorlevel% neq 0 (
    echo    LOI: docker compose config that bai. Kiem tra lai docker-compose.yml va .env
    exit /b 1
)
echo    Config hop le. OK.

REM 4. Build va start services
echo [4/5] Build va khoi dong services...
docker compose up -d --build
if %errorlevel% neq 0 (
    echo    LOI: docker compose up that bai.
    exit /b 1
)

REM 5. Tạo admin user
echo [5/5] Cho MySQL khoi dong (30s)...
timeout /t 30 /nobreak >nul
echo    Tao tai khoan admin...
docker compose exec php php /var/www/html/scripts/create-admin.php
echo.

echo ========================================
echo   HOAN TAT! Cac URL truy cap:
echo ========================================
echo   Website:    https://localhost
echo   Admin:      https://localhost/admin/login
echo   phpMyAdmin: http://localhost:8080
echo   Grafana:    http://localhost:3000
echo   Prometheus: http://localhost:9090
echo   Loki:       http://localhost:3100
echo ========================================
echo.
pause
