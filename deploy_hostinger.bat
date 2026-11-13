@echo off
echo ========================================================
echo   SYNC ^& DEPLOY KE HOSTINGER (susiparsel.com)
echo ========================================================
echo.
echo [1/2] Melakukan push perubahan lokal ke GitHub...
git push origin main
if %ERRORLEVEL% NEQ 0 (
    echo [ERROR] Gagal melakukan git push ke GitHub.
    pause
    exit /b %ERRORLEVEL%
)

echo.
echo [2/2] Mengupdate Hostinger via SSH dan membersihkan cache...
ssh -i "%~dp0.deploy_key" -p 65002 -o StrictHostKeyChecking=no u200447225@153.92.8.204 "cd domains/susiparsel.com/public_html && git pull origin main && php artisan optimize:clear"
if %ERRORLEVEL% NEQ 0 (
    echo [ERROR] Gagal sinkronisasi ke Hostinger.
    pause
    exit /b %ERRORLEVEL%
)

echo.
echo ========================================================
echo   SUKSES! Aplikasi di https://susiparsel.com sudah terupdate.
echo ========================================================
pause
