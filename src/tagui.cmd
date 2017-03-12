@echo off	
rem # SCRIPT FOR RUNNING TA.GUI FRAMEWORK ~ TEBEL.ORG #

rem enable windows for loop advanced flow control
setlocal enableextensions enabledelayedexpansion

if "%~1"=="" (
echo tagui: missing parameter^(s^) - use this syntax and below options to run ./tagui flow_filename option^(s^)
echo.
echo firefox - run on visible Firefox browser ^(first install Firefox, and SlimerJS - https://slimerjs.org^)
echo report - generate a web report for easy sharing of run results ^(default is only a text log file^)
echo debug - show run-time backend messages from PhantomJS for detailed tracing and logging
echo quiet - run without output except for explicit output ^(echo / show / check / api / errors etc^)
echo test - professional testing using CasperJS assertions ^(TA.Gui dynamic tx^('selector'^) usable^)
echo.
echo TA.Gui is a tool for non-developers and business users to automate web apps - http://tebel.org
echo.
exit /b 1
)

rem check whether specified automation flow file exists
if not exist "%~1" (
echo ERROR - cannot find %~1
exit /b 1
)

rem get and set absolute filename of automation flow file
set "flow_file=%~dpnx1"

rem save location of initial directory where tagui is called
set "initial_dir=%cd%"

rem change current directory to TA.GUI directory
cd /d "%~dp0"

rem set local path to casperjs and environment path to phantomjs
set CASPERJS_EXECUTABLE="C:\TA.Gui\casperjs"
set PHANTOMJS_EXECUTABLE="C:\TA.Gui\phantomjs"

rem check firefox parameter to run on visible firefox browser through slimerjs


rem check debug parameter to show run-time backend messages from phantomjs


rem check test parameter to run flow as casperjs test automation script


rem check quiet parameter to run flow quietly by only showing explicit output


rem check report parameter to generate html formatted automation log


rem concatenate parameters in order to fix issue when calling casperjs test
rem $1 left out - filenames with spaces have issue when casperjs $params


rem initialise log file and set permissions to protect user data
type nul > "%flow_file%.log"

rem check if api call is made in automation flow file to set appropriate setting for phantomjs to work


rem check datatable csv file for batch automation


rem loop for managing multiple data sets in datatable


rem default exit code 0 to mean no error parsing automation flow file
set tagui_error_code=0

rem parse automation flow file and check for initial parse error before calling casperjs
php -q tagui_parse.php "%flow_file%"

rem check report option to generate html automation log


rem change back to initial directory where tagui is called
cd /d "%initial_dir%"

rem returns 0 if no error parsing flow file, otherwise return 1
exit /b %tagui_error_code%