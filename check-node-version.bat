@echo off
echo ========================================
echo   Node.js Version Check
echo ========================================
echo.

echo Current Node.js version:
node --version
echo.

echo Required Node.js version: 22.12.0 or higher
echo.

echo ========================================
echo   Quick Install Options
echo ========================================
echo.
echo Option 1: Direct Install
echo    Download: https://nodejs.org/dist/v22.12.0/node-v22.12.0-x64.msi
echo.
echo Option 2: NVM for Windows
echo    Download: https://github.com/coreybutler/nvm-windows/releases/latest
echo    Then run: nvm install 22.12.0 && nvm use 22.12.0
echo.

pause
