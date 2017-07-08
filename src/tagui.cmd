@echo off
rem # SCRIPT FOR RUNNING TAGUI FRAMEWORK ~ TEBEL.ORG #

rem configure command to launch chrome for Windows
set chrome_command=C:\Program Files (x86)\Google\Chrome\Application\chrome.exe

rem enable windows for loop advanced flow control
setlocal enableextensions enabledelayedexpansion

if "%~1"=="" (
echo tagui v2.0: use following syntax and below options to run - tagui flow_filename option^(s^)
echo.
echo headless - run on invisible Chrome web browser instead of default PhantomJS ^(first install Chrome^)
echo chrome   - run on visible Chrome web browser instead of invisible PhantomJS ^(first install Chrome^)
echo firefox  - run on visible Firefox web browser instead of invisible browser ^(first install Firefox^)
echo upload   - upload automation flow and result to hastebin.com ^(expires 30 days after last view^)
echo report   - web report for sharing of run results on webserver ^(default is only a text log file^)
echo debug    - show run-time backend messages from PhantomJS for detailed tracing and logging
echo quiet    - run without output except for explicit output ^(echo / show / check / errors etc^)
echo test     - professional testing using CasperJS assertions ^(TagUI smart tx^('selector'^) usable^)
echo input^(s^) - add your own parameter^(s^) to be used in your automation flow as variables p1 to p9
echo.
echo TagUI is a general purpose tool for automating web interactions ~ http://tebel.org
echo.
exit /b 1
)

rem download file if first parameter is online resource
set arg1=%~1
if "%arg1:~0,4%"=="http" (
	if exist "%~dp0unx\curl.exe" set "path=%~dp0unx;%path%"
	set arg1=%arg1:/= %
	for %%a in (!arg1!) do set "online_flowname=%%a"
	curl -k -s -o !online_flowname! "%~1"
	for %%i in ("!online_flowname!") do set "flow_file=%%~dpnxi"

	if exist "!online_flowname!" (
		find /i /c "404" "!online_flowname!" > nul || find /i /c "400" "!online_flowname!" > nul
		if not errorlevel 1 del "!online_flowname!"
	)
	if exist "!online_flowname!" (
		for %%a in (!arg1!) do set "online_reponame=%%a.csv"		
		curl -k -s -o !online_reponame! "%~1.csv"
		if exist "!online_reponame!" (
			find /i /c "404" "!online_reponame!" > nul || find /i /c "400" "!online_reponame!" > nul
			if not errorlevel 1 del "!online_reponame!"
		)
	)
)

rem check whether specified automation flow file exists
if "%flow_file%"=="" if not exist "%~1" (
	echo ERROR - cannot find %~1
	exit /b 1
)
if not "%flow_file%"=="" if not exist "%online_flowname%" (
	echo ERROR - cannot find %online_flowname%
	exit /b 1
)

rem get and set absolute filename of automation flow file
if "%flow_file%"=="" set "flow_file=%~dpnx1"

rem save location of initial directory where tagui is called
set "initial_dir=%cd%"

rem change current directory to TAGUI directory
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

rem set default web browser to be used to phantomjs
set tagui_web_browser=phantomjs

rem check chrome parameter to run on in-built integration with visible chrome
if "%arg2%"=="chrome" (
	set arg2=
	set tagui_web_browser=chrome
)
if "%arg3%"=="chrome" (
	set arg3=
	set tagui_web_browser=chrome
)
if "%arg4%"=="chrome" (
	set arg4=
	set tagui_web_browser=chrome
)
if "%arg5%"=="chrome" (
	set arg5=
	set tagui_web_browser=chrome
)
if "%arg6%"=="chrome" (
	set arg6=
	set tagui_web_browser=chrome
)
if "%arg7%"=="chrome" (
	set arg7=
	set tagui_web_browser=chrome
)
if "%arg8%"=="chrome" (
	set arg8=
	set tagui_web_browser=chrome
)
if "%arg9%"=="chrome" (
	set arg9=
	set tagui_web_browser=chrome
)

rem check headless parameter to run on in-built integration with headless chrome
if "%arg2%"=="headless" (
	set arg2=
	set tagui_web_browser=headless
)
if "%arg3%"=="headless" (
	set arg3=
	set tagui_web_browser=headless
)
if "%arg4%"=="headless" (
	set arg4=
	set tagui_web_browser=headless
)
if "%arg5%"=="headless" (
	set arg5=
	set tagui_web_browser=headless
)
if "%arg6%"=="headless" (
	set arg6=
	set tagui_web_browser=headless
)
if "%arg7%"=="headless" (
	set arg7=
	set tagui_web_browser=headless
)
if "%arg8%"=="headless" (
	set arg8=
	set tagui_web_browser=headless
)
if "%arg9%"=="headless" (
	set arg9=
	set tagui_web_browser=headless
)

rem check firefox parameter to run on visible firefox browser through slimerjs
if "%arg2%"=="firefox" (
	set arg2=--engine=slimerjs
	set tagui_web_browser=firefox
)
if "%arg3%"=="firefox" (
	set arg3=--engine=slimerjs
	set tagui_web_browser=firefox
)
if "%arg4%"=="firefox" (
	set arg4=--engine=slimerjs
	set tagui_web_browser=firefox
)
if "%arg5%"=="firefox" (
	set arg5=--engine=slimerjs
	set tagui_web_browser=firefox
)
if "%arg6%"=="firefox" (
	set arg6=--engine=slimerjs
	set tagui_web_browser=firefox
)
if "%arg7%"=="firefox" (
	set arg7=--engine=slimerjs
	set tagui_web_browser=firefox
)
if "%arg8%"=="firefox" (
	set arg8=--engine=slimerjs
	set tagui_web_browser=firefox
)
if "%arg9%"=="firefox" (
	set arg9=--engine=slimerjs
	set tagui_web_browser=firefox
)

rem export web browser variable not needed for windows batch file

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

set tagui_upload_result=false
rem check upload parameter to upload flow result to online storage
if "%arg2%"=="upload" (
	set arg2=
	set tagui_upload_result=true
)
if "%arg3%"=="upload" (
	set arg3=
	set tagui_upload_result=true
)
if "%arg4%"=="upload" (
	set arg4=
	set tagui_upload_result=true
)
if "%arg5%"=="upload" (
	set arg5=
	set tagui_upload_result=true
)
if "%arg6%"=="upload" (
	set arg6=
	set tagui_upload_result=true
)
if "%arg7%"=="upload" (
	set arg7=
	set tagui_upload_result=true
)
if "%arg8%"=="upload" (
	set arg8=
	set tagui_upload_result=true
)
if "%arg9%"=="upload" (
	set arg9=
	set tagui_upload_result=true
)

rem concatenate parameters in order to fix issue when calling casperjs test
rem $1 left out - filenames with spaces have issue when casperjs $params
set params=%arg2% %arg3% %arg4% %arg5% %arg6% %arg7% %arg8% %arg9%

rem check if api call is made in automation flow file to set appropriate setting for phantomjs to work
set api=
if exist "%flow_file%" (
	find /i /c "api http" "%flow_file%" > nul
	if not errorlevel 1 set api= --web-security=false
)

rem initialise log file and set permissions to protect user data
rem skip permissions setting for windows, only done for macos and linux
type nul > "%flow_file%.log"
type nul > "tagui.sikuli\tagui.log"
type nul > "tagui_chrome.log"

rem delete sikuli visual automation integration files if they exist
if exist "tagui.sikuli\tagui_sikuli.in" del "tagui.sikuli\tagui_sikuli.in" 
if exist "tagui.sikuli\tagui_sikuli.out" del "tagui.sikuli\tagui_sikuli.out"

rem delete chrome / headless chrome integration files if they exist 
if exist "tagui_chrome.in" del "tagui_chrome.in"
if exist "tagui_chrome.out" del "tagui_chrome.out"

rem default exit code 0 to mean no error parsing automation flow file
set tagui_error_code=0

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

rem big loop for managing multiple data sets in datatable
for /l %%n in (1,1,%tagui_data_set_size%) do (
set tagui_data_set=%%n

rem parse automation flow file, check for initial parse error, check sikuli and chrome, before calling casperjs
php -q tagui_parse.php "%flow_file%" | tee -a "%flow_file%.log"
for /f "usebackq" %%f in ('%flow_file%.log') do set file_size=%%~zf
if !file_size! gtr 0 (
	if !tagui_data_set! equ 1 (
		echo ERROR - automation aborted due to above | tee -a "%flow_file%.log"
		set tagui_error_code=1
		goto break_for_loop
	)
)

rem start sikuli process if integration file is created during parsing
if exist "tagui.sikuli\tagui_sikuli.in" (
	echo [starting sikuli process] | tee -a "%flow_file%.log"
	start /min cmd /c tagui.sikuli\runsikulix -r tagui.sikuli ^| tee -a tagui.sikuli\tagui.log
)

rem start chrome processes if integration file is created during parsing
set chrome_started=
if exist "tagui_chrome.in" (
	rem echo [starting chrome websocket] | tee -a "%flow_file%.log"

	rem get window size from tagui_config.txt to set for chrome, estimating into account the height of chrome title bar
	for /f "tokens=* usebackq" %%w in (`grep width tagui_config.txt ^| cut -d" " -f 2`) do set width=%%w
	for /f "tokens=* usebackq" %%h in (`grep height tagui_config.txt ^| cut -d" " -f 2`) do set height=%%h
	if "%tagui_web_browser%"=="chrome" set /a height=!height!+74
	set window_size=--window-size=!width!!height!

	rem setting to run chrome in headless mode if headless option is used
	set headless_switch=
	if "%tagui_web_browser%"=="headless" set headless_switch=--headless --disable-gpu

	rem check for which operating system and launch chrome accordingly
	taskkill /IM chrome.exe /T /F > nul 2>&1
	set chrome_started=Windows
	set chrome_switches=--remote-debugging-port=9222 about:blank
	start "" "%chrome_command%" !chrome_switches! !window_size! !headless_switch!

	:scan_ws_again
	rem wait until chrome is ready with websocket url for php thread
	for /f "tokens=* usebackq" %%u in (`curl -s localhost:9222/json ^| grep -A 1 "\"url\": \"about:blank\"" ^| grep webSocketDebuggerUrl ^| cut -d" " -f 5`) do set ws_url=%%u
	if "!ws_url!"=="" goto scan_ws_again

	rem launch php process to manage chrome websocket communications
	start /min cmd /c php -q tagui_chrome.php !ws_url! ^| tee -a tagui_chrome.log

rem end of if block to start chrome processes
)

rem check if test mode is enabled and run casperjs accordingly, before sending finish signal if integrations are active
if %tagui_test_mode%==false (
	casperjs "%flow_file%.js" %params%%api% | tee -a "%flow_file%.log"
) else (
	casperjs test "%flow_file%.js" %params%%api% --xunit="%flow_file%.xml" | tee -a "%flow_file%.log"
)
rem checking for existence of files is important, otherwise in loops integrations will run even without enabling
if exist "tagui.sikuli\tagui_sikuli.in" echo finish > tagui.sikuli\tagui_sikuli.in
if exist "tagui_chrome.in" echo finish > tagui_chrome.in

rem kill chrome processes by checking which os the processes are started on
if not "!chrome_started!"=="" taskkill /IM chrome.exe /T /F > nul 2>&1

rem end of big loop for managing multiple data sets in datatable
)
:break_for_loop

rem additional windows section to convert unix to windows file format
gawk "sub(\"$\", \"\")" "%flow_file%.js" > "%flow_file%.js.tmp"
move "%flow_file%.js.tmp" "%flow_file%.js" > nul
gawk "sub(\"$\", \"\")" "%flow_file%.log" > "%flow_file%.log.tmp"
move "%flow_file%.log.tmp" "%flow_file%.log" > nul
gawk "sub(\"$\", \"\")" "tagui.sikuli\tagui.log" > "tagui.sikuli\tagui.log.tmp"
move "tagui.sikuli\tagui.log.tmp" "tagui.sikuli\tagui.log" > nul
gawk "sub(\"$\", \"\")" "tagui_chrome.log" > "tagui_chrome.log.tmp"
move "tagui_chrome.log.tmp" "tagui_chrome.log" > nul

rem check report option to generate html automation log
for /f "usebackq" %%f in ('%flow_file%.log') do set file_size=%%~zf
if %file_size% gtr 0 if %tagui_html_report%==true (
	php -q tagui_report.php "%flow_file%"
	gawk "sub(\"$\", \"\")" "%flow_file%.html" > "%flow_file%.html.tmp"
	move "%flow_file%.html.tmp" "%flow_file%.html" > nul
)

rem check upload option to upload result to hastebin.com
for /f "usebackq" %%f in ('%flow_file%.log') do set file_size=%%~zf
if %file_size% gtr 0 if %tagui_upload_result%==true (
rem set flow_file to blank or the variable will break that tagui call
	set "tmp_flow_file=%flow_file%"
	set flow_file=
	tagui samples\8_hastebin quiet "!tmp_flow_file!"
)

rem change back to initial directory where tagui is called
cd /d "%initial_dir%"

rem returns 0 if no error parsing flow file, otherwise return 1
exit /b %tagui_error_code%
