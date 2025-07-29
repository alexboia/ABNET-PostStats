@echo off
setlocal enabledelayedexpansion

echo ABNet Post Stats - Quick Plugin Packager
echo =========================================

set PLUGIN_NAME=abnet-post-stats
set VERSION=1.0.0
set OUTPUT_DIR=.\dist

if not exist "%OUTPUT_DIR%" mkdir "%OUTPUT_DIR%"

echo Creating plugin package...

powershell.exe -ExecutionPolicy Bypass -File ".\package-plugin.ps1" -PluginName "%PLUGIN_NAME%" -Version "%VERSION%" -OutputPath "%OUTPUT_DIR%" -Verbose

if %ERRORLEVEL% EQU 0 (
    echo.
    echo Package created successfully!
    echo Location: %OUTPUT_DIR%\%PLUGIN_NAME%-v%VERSION%.zip
    echo.
    pause
) else (
    echo.
    echo Package creation failed!
    echo.
    pause
    exit /b 1
)