@echo off
echo Copying Metronic assets to resources/admin/...

REM Copy CSS
xcopy "design\dist\assets\css\styles.css" "resources\admin\css\" /Y

REM Copy JS
xcopy "design\dist\assets\js\*" "resources\admin\js\" /E /Y

REM Copy Keenicons
xcopy "design\dist\assets\vendors\keenicons" "resources\admin\vendors\keenicons\" /E /Y

REM Copy Images for backgrounds
xcopy "design\dist\assets\media\images\*" "resources\admin\media\images\" /E /Y

REM Copy App media (logos, favicons)
xcopy "design\dist\assets\media\app\*" "resources\admin\media\app\" /E /Y

REM Copy brand logos
xcopy "design\dist\assets\media\brand-logos" "resources\admin\media\brand-logos\" /E /Y

echo.
echo Done! Admin assets copied successfully.
pause
