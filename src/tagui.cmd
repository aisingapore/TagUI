@echo off
rem # SCRIPT FOR RUNNING TA.GUI FRAMEWORK ~ TEBEL.ORG #

rem enable windows for loop advanced flow control
setlocal enableextensions enabledelayedexpansion

if "%~1"=="" (
echo tagui v1.0: use following syntax and below options to run ./tagui flow_filename option^(s^)
echo.
echo firefox - run on visible Firefox web browser instead of invisible browser ^(first install Firefox^)
echo report - generate a web report for easy sharing of run results ^(default is only a text log file^)
echo debug - show run-time backend messages from PhantomJS for detailed tracing and logging
echo quiet - run without output except for explicit output ^(echo / show / check / api / errors etc^)
echo test - professional testing using CasperJS assertions ^(TA.Gui dynamic tx^('selector'^) usable^)
echo input^(s^) - add your own parameter^(s^) to be used in your automation flow as variables p1 to p9
echo.
echo TA.Gui is a tool for non-developers and business users to automate web ~ http://tebel.org
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

rem below 3 blocks dynamically set up dependencies paths for different setups
rem first set dependencies to local symbolic links (for manual installation)
rem symbolic links not standard setup on windows, only for macos and linux

rem then set to node.js dependencies if they exist (for npm install tagui)
if exist "%~dp0..\..\casperjs\bin\casperjs" set "path=%~dp0..\..\casperjs\bin;%path%"
if exist "%~dp0..\..\phantomjs-prebuilt\lib\phantom\bin\phantomjs.exe" set "path=%~dp0..\..\phantomjs-prebuilt\lib\phantom\bin;%path%"
if exist "%~dp0..\..\slimerjs\src\slimerjs.bat" set "SLIMERJS_EXECUTABLE=%~dp0..\..\slimerjs\src\slimerjs.bat"

rem finally set to packaged dependencies if they exist (for packaged installation)
if exist "%~dp0casperjs\bin\casperjs" set "path=%~dp0casperjs\bin;%path%"
if exist "%~dp0phantomjs\bin\phantomjs.exe" set "path=%~dp0phantomjs\bin;%path%"
if exist "%~dp0slimerjs\slimerjs.bat" set "SLIMERJS_EXECUTABLE=%~dp0slimerjs\slimerjs.bat"
if exist "%~dp0php\php.exe" set "path=%~dp0php;%path%"
if exist "%~dp0unx\gawk.exe" set "path=%~dp0unx;%path%"

rem additional windows section for parameters handling using windows way
set arg2=%2
set arg3=%3
set arg4=%4
set arg5=%5
set arg6=%6
set arg7=%7
set arg8=%8
set arg9=%9

rem check firefox parameter to run on visible firefox browser through slimerjs
if "%arg2%"=="firefox" set arg2=--engine=slimerjs
if "%arg3%"=="firefox" set arg3=--engine=slimerjs
if "%arg4%"=="firefox" set arg4=--engine=slimerjs
if "%arg5%"=="firefox" set arg5=--engine=slimerjs
if "%arg6%"=="firefox" set arg6=--engine=slimerjs
if "%arg7%"=="firefox" set arg7=--engine=slimerjs
if "%arg8%"=="firefox" set arg8=--engine=slimerjs
if "%arg9%"=="firefox" set arg9=--engine=slimerjs

rem check debug parameter to show run-time backend messages from phantomjs
if "%arg2%"=="debug" set arg2=--verbose
if "%arg3%"=="debug" set arg3=--verbose
if "%arg4%"=="debug" set arg4=--verbose
if "%arg5%"=="debug" set arg5=--verbose
if "%arg6%"=="debug" set arg6=--verbose
if "%arg7%"=="debug" set arg7=--verbose
if "%arg8%"=="debug" set arg8=--verbose
if "%arg9%"=="debug" set arg9=--verbose

set tagui_test_mode=false
rem check test parameter to run flow as casperjs test automation script
if "%arg2%"=="test" (
	set arg2=
	set tagui_test_mode=true
)
if "%arg3%"=="test" (
	set arg3=
	set tagui_test_mode=true
)
if "%arg4%"=="test" (
	set arg4=
	set tagui_test_mode=true
)
if "%arg5%"=="test" (
	set arg5=
	set tagui_test_mode=true
)
if "%arg6%"=="test" (
	set arg6=
	set tagui_test_mode=true
)
if "%arg7%"=="test" (
	set arg7=
	set tagui_test_mode=true
)
if "%arg8%"=="test" (
	set arg8=
	set tagui_test_mode=true
)
if "%arg9%"=="test" (
	set arg9=
	set tagui_test_mode=true
)

set tagui_quiet_mode=false
rem check quiet parameter to run flow quietly by only showing explicit output
if "%arg2%"=="quiet" (
	set arg2=
	set tagui_quiet_mode=true
)
if "%arg3%"=="quiet" (
	set arg3=
	set tagui_quiet_mode=true
)
if "%arg4%"=="quiet" (
	set arg4=
	set tagui_quiet_mode=true
)
if "%arg5%"=="quiet" (
	set arg5=
	set tagui_quiet_mode=true
)
if "%arg6%"=="quiet" (
	set arg6=
	set tagui_quiet_mode=true
)
if "%arg7%"=="quiet" (
	set arg7=
	set tagui_quiet_mode=true
)
if "%arg8%"=="quiet" (
	set arg8=
	set tagui_quiet_mode=true
)
if "%arg9%"=="quiet" (
	set arg9=
	set tagui_quiet_mode=true
)

set tagui_html_report=false
rem check report parameter to generate html formatted automation log
if "%arg2%"=="report" (
	set arg2=
	set tagui_html_report=true
)
if "%arg3%"=="report" (
	set arg3=
	set tagui_html_report=true
)
if "%arg4%"=="report" (
	set arg4=
	set tagui_html_report=true
)
if "%arg5%"=="report" (
	set arg5=
	set tagui_html_report=true
)
if "%arg6%"=="report" (
	set arg6=
	set tagui_html_report=true
)
if "%arg7%"=="report" (
	set arg7=
	set tagui_html_report=true
)
if "%arg8%"=="report" (
	set arg8=
	set tagui_html_report=true
)
if "%arg9%"=="report" (
	set arg9=
	set tagui_html_report=true
)

rem concatenate parameters in order to fix issue when calling casperjs test
rem $1 left out - filenames with spaces have issue when casperjs $params
set params=%arg2% %arg3% %arg4% %arg5% %arg6% %arg7% %arg8% %arg9%

rem initialise log file and set permissions to protect user data
rem skip permissions setting for windows, only done for macos and linux
type nul > "%flow_file%.log"

rem check if api call is made in automation flow file to set appropriate setting for phantomjs to work
set api=
if exist "%flow_file%" (
	find /i /c "api http" "%flow_file%" > nul
	if not errorlevel 1 set api= --web-security=false
)

rem check datatable csv file for batch automation
set tagui_data_set_size=1 
if not exist "%flow_file%.csv" goto no_datatable
	for /f "tokens=* usebackq" %%c in (`gawk -F"," "{print NF}" "%flow_file%.csv" ^| sort -nu ^| head -n 1`) do set min_column=%%c
	for /f "tokens=* usebackq" %%c in (`gawk -F"," "{print NF}" "%flow_file%.csv" ^| sort -nu ^| tail -n 1`) do set max_column=%%c
	
	if %min_column% neq %max_column% (
		echo ERROR - %flow_file%.csv has inconsistent # of columns | tee -a "%flow_file%.log"
	) else if %min_column% lss 2 (
		echo ERROR - %flow_file%.csv has has lesser than 2 columns | tee -a "%flow_file%.log"
	) else (
		set /a tagui_data_set_size=%min_column% - 1
	)
:no_datatable

rem default exit code 0 to mean no error parsing automation flow file
set tagui_error_code=0

rem loop for managing multiple data sets in datatable
for /l %%n in (1,1,%tagui_data_set_size%) do (
set tagui_data_set=%%n

rem parse automation flow file and check for initial parse error before calling casperjs
php -q tagui_parse.php "%flow_file%" | tee -a "%flow_file%.log"
for /f "usebackq" %%f in ('%flow_file%.log') do set file_size=%%~zf
if !file_size! gtr 0 (
	if !tagui_data_set! equ 1 (
		echo ERROR - automation aborted due to above | tee -a "%flow_file%.log"
		set tagui_error_code=1
		goto break_for_loop
	)
)

if %tagui_test_mode%==false (
	casperjs "%flow_file%.js" %params%%api% | tee -a "%flow_file%.log"
) else (
	casperjs test "%flow_file%.js" %params%%api% --xunit="%flow_file%.xml" | tee -a "%flow_file%.log"
)

)
:break_for_loop

rem additional windows section to convert unix to windows file format
gawk "sub(\"$\", \"\")" "%flow_file%.js" > "%flow_file%.js.tmp"
move "%flow_file%.js.tmp" "%flow_file%.js" > nul
gawk "sub(\"$\", \"\")" "%flow_file%.log" > "%flow_file%.log.tmp"
move "%flow_file%.log.tmp" "%flow_file%.log" > nul

rem check report option to generate html automation log
for /f "usebackq" %%f in ('%flow_file%.log') do set file_size=%%~zf
if %file_size% gtr 0 if %tagui_html_report%==true (
	php -q tagui_report.php "%flow_file%"
	gawk "sub(\"$\", \"\")" "%flow_file%.html" > "%flow_file%.html.tmp"
	move "%flow_file%.html.tmp" "%flow_file%.html" > nul
)

rem change back to initial directory where tagui is called
cd /d "%initial_dir%"

rem returns 0 if no error parsing flow file, otherwise return 1
exit /b %tagui_error_code%
