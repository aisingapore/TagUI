@echo off
rem # HELPER ASSISTANT SCRIPT FOR RUNNING TAGUI FRAMEWORK ~ TEBEL.ORG #

rem enable windows for loop advanced flow control
setlocal enableextensions enabledelayedexpansion

rem save location of initial directory where helper is called
set "initial_dir=%cd%"

rem change current directory to TAGUI directory
cd /d "%~dp0"

rem delete helper file if it exists to prevent running an old call
if exist "tagui_helper.cmd" del "tagui_helper.cmd"

rem call php helper to interpret parameters passed in to cli helper
if exist "%~dp0php\php.exe" set "path=%~dp0php;%path%"
php -q tagui_helper.php %1 %2 %3 %4 %5 %6 %7 %8 %9

rem run generated output from php helper to call automation flow
if exist "tagui_helper.cmd" tagui_helper

rem change back to initial directory where tagui is called
cd /d "%initial_dir%"
