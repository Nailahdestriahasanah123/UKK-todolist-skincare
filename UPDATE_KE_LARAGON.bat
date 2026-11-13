@echo off
title Update Proyek ke Laragon - GlowTask 🌸
color 0D
echo ========================================================
echo   🌸 SINKRONISASI PROYEK GLOWTASK KE LARAGON WWW 🌸
echo ========================================================
echo.
echo Sedang menyalin file-file terbaru ke folder Laragon...
echo.

set SOURCE_DIR=%~dp0
set DEST_DIR=C:\laragon600\laragon600\www\ToDoList-skincare

if not exist "%DEST_DIR%" (
    echo [INFO] Membuat folder tujuan di Laragon...
    mkdir "%DEST_DIR%"
)

xcopy "%SOURCE_DIR%*" "%DEST_DIR%\" /E /Y /I /Q

echo.
echo ========================================================
echo   ✨ BERHASIL! SEMUA FILE SUDAH TERSINKRONISASI! ✨
echo ========================================================
echo.
echo Sekarang buka browsermu di:
echo 👉 http://localhost/ToDoList-skincare/
echo.
echo (Tekan Ctrl + F5 di browser untuk melihat perubahannya yaa!)
echo.
pause
