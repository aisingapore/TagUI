@echo off	
rem # SCRIPT FOR RUNNING TA.GUI FRAMEWORK ~ TEBEL.ORG #



rem check whether specified automation flow file exists
if not exist "%1" (
echo ERROR - cannot find %1

exit /b
)


rem get and set absolute filename of automation flow file
set "flow_file=%~dpnx1"



rem save location of initial directory where tagui is called

set "initial_dir=%cd%"



rem change current directory to TA.GUI directory
cd %~dp0



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
type nul > %flow_file%.log
rem CACLS files /e /p {USERNAME}:{PERMISSION}

rem check if api call is made in automation flow file to set appropriate setting for phantomjs to work





rem check datatable csv file for batch automation


rem loop for managing multiple data sets in datatable





rem parse automation flow file and check for initial parse error before calling casperjs





rem check report option to generate html automation log





rem change back to initial directory where tagui is called

chdir /d %initial_dir%