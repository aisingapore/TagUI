@echo off
rem # SCRIPT FOR RUNNING TAGUI FRAMEWORK ~ TEBEL.ORG #

rem configure command to launch chrome for Windows
set chrome_command=C:\Program Files (x86)\Google\Chrome\Application\chrome.exe

rem enable windows for loop advanced flow control
setlocal enableextensions enabledelayedexpansion

if "%~1"=="" (
echo tagui v5.7: use following options and this syntax to run - tagui flow_filename option^(s^)
echo.
echo chrome   - run on visible Chrome web browser instead of invisible PhantomJS ^(first install Chrome^)
echo headless - run on invisible Chrome web browser instead of default PhantomJS ^(first install Chrome^)
echo firefox  - run on visible Firefox web browser instead of invisible browser ^(first install Firefox^)
echo report   - track run result in tagui\src\tagui_report.csv and save html log of automation execution
echo upload   - upload automation flow and result to hastebin.com ^(expires 30 days after last view^)
echo speed    - skip 3-second delay between datatable iterations ^(and skip restarting of Chrome^)
echo quiet    - run without output except for explicit output ^(echo / show / check / errors etc^)
echo debug    - show run-time backend messages from PhantomJS mode for detailed tracing and logging
echo test     - testing with check step test assertions for CI/CD integration ^(output XUnit XML file^)
echo baseline - output execution log and relative-path output files to a separate baseline directory
echo input^(s^) - add your own parameter^(s^) to be used in your automation flow as variables p1 to p9
echo data.csv - specify a csv file to be used as the datatable for batch automation of many records
echo.
echo TagUI is a command-line tool for digital process automation ^(RPA^) - for more info, google tagui
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

rem additional windows section for parameters handling using windows way
set arg2=%2
set arg3=%3
set arg4=%4
set arg5=%5
set arg6=%6
set arg7=%7
set arg8=%8
set arg9=%9

set tagui_baseline_mode=false
rem check baseline parameter to output files to baseline directory
if "%arg2%"=="baseline" (
	set arg2=
	set tagui_baseline_mode=true
)
if "%arg3%"=="baseline" (
	set arg3=
	set tagui_baseline_mode=true
)
if "%arg4%"=="baseline" (
	set arg4=
	set tagui_baseline_mode=true
)
if "%arg5%"=="baseline" (
	set arg5=
	set tagui_baseline_mode=true
)
if "%arg6%"=="baseline" (
	set arg6=
	set tagui_baseline_mode=true
)
if "%arg7%"=="baseline" (
	set arg7=
	set tagui_baseline_mode=true
)
if "%arg8%"=="baseline" (
	set arg8=
	set tagui_baseline_mode=true
)
if "%arg9%"=="baseline" (
	set arg9=
	set tagui_baseline_mode=true
)

rem check baseline option, get and set absolute filename of automation flow file
if %tagui_baseline_mode%==false (
	if "%flow_file%"=="" set "flow_file=%~dpnx1"
) else (
	if "%flow_file%"=="" set "flow_file=%~dpnx1"
	for %%i in ("!flow_file!") do set "flow_folder=%%~dpi"
	for %%i in ("!flow_file!") do set "flow_filename=%%~nxi"
	if not exist "!flow_folder!baseline" mkdir "!flow_folder!baseline"
	copy /Y "!flow_file!" "!flow_folder!baseline\." > nul
	set "flow_file=!flow_folder!baseline\!flow_filename!"
)

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

set tagui_speed_mode=false
rem check speed parameter to skip delay and chrome restart between iterations
if "%arg2%"=="speed" (
	set arg2=
	set tagui_speed_mode=true
)
if "%arg3%"=="speed" (
	set arg3=
	set tagui_speed_mode=true
)
if "%arg4%"=="speed" (
	set arg4=
	set tagui_speed_mode=true
)
if "%arg5%"=="speed" (
	set arg5=
	set tagui_speed_mode=true
)
if "%arg6%"=="speed" (
	set arg6=
	set tagui_speed_mode=true
)
if "%arg7%"=="speed" (
	set arg7=
	set tagui_speed_mode=true
)
if "%arg8%"=="speed" (
	set arg8=
	set tagui_speed_mode=true
)
if "%arg9%"=="speed" (
	set arg9=
	set tagui_speed_mode=true
)

rem concatenate parameters in order to fix issue when calling casperjs test
rem $1 left out - filenames with spaces have issue when casperjs $params
set params=%arg2% %arg3% %arg4% %arg5% %arg6% %arg7% %arg8% %arg9%

rem initialise log file and set permissions to protect user data
rem skip permissions setting for windows, only done for macos and linux
type nul > "%flow_file%.log"
type nul > "tagui_r\tagui_r.log"
type nul > "tagui_r\tagui_r_windows.log"
type nul > "tagui_py\tagui_py.log"
type nul > "tagui_py\tagui_py_windows.log"
type nul > "tagui.sikuli\tagui.log"
type nul > "tagui.sikuli\tagui_windows.log"
type nul > "tagui_chrome.log"

rem delete R integration files if they exist
if exist "tagui_r\tagui_r.in" del "tagui_r\tagui_r.in"
if exist "tagui_r\tagui_r.out" del "tagui_r\tagui_r.out"

rem delete python integration files if they exist
if exist "tagui_py\tagui_py.in" del "tagui_py\tagui_py.in"
if exist "tagui_py\tagui_py.out" del "tagui_py\tagui_py.out"

rem delete sikuli visual automation integration files if they exist
if exist "tagui.sikuli\tagui_sikuli.in" del "tagui.sikuli\tagui_sikuli.in" 
if exist "tagui.sikuli\tagui_sikuli.out" del "tagui.sikuli\tagui_sikuli.out"

rem delete chrome / headless chrome integration files if they exist 
if exist "tagui_chrome.in" del "tagui_chrome.in"
if exist "tagui_chrome.out" del "tagui_chrome.out"

rem default exit code 0 to mean no error parsing automation flow file
set tagui_error_code=0

rem transpose datatable csv file if file to be transposed exists
if exist "%flow_file%_transpose.csv" php -q transpose.php "%flow_file%_transpose.csv" | tee -a "%flow_file%.log"

cd /d "%initial_dir%"
set "custom_csv_file=NO_CUSTOM_CSV_FILE"
rem check if custom csv file is provided to be used as datatable
if "%arg2:~-4%"==".csv" (
        set "custom_csv_file=%~dpnx2"
)
if "%arg3:~-4%"==".csv" (
        set "custom_csv_file=%~dpnx3"
)
if "%arg4:~-4%"==".csv" (
        set "custom_csv_file=%~dpnx4"
)
if "%arg5:~-4%"==".csv" (
        set "custom_csv_file=%~dpnx5"
)
if "%arg6:~-4%"==".csv" (
        set "custom_csv_file=%~dpnx6"
)
if "%arg7:~-4%"==".csv" (
        set "custom_csv_file=%~dpnx7"
)
if "%arg8:~-4%"==".csv" (
        set "custom_csv_file=%~dpnx8"
)
if "%arg9:~-4%"==".csv" (
        set "custom_csv_file=%~dpnx9"
)
cd /d "%~dp0"

if not "NO_CUSTOM_CSV_FILE" == "%custom_csv_file%" (
	if not exist "%custom_csv_file%" (
		echo ERROR - cannot find %custom_csv_file%
		exit /b 1
	) 
	copy /Y "%custom_csv_file%" "tagui_datatable_transpose.csv" > nul
	php -q transpose.php "tagui_datatable_transpose.csv" | tee -a "%flow_file%.log"
	set "custom_csv_file=%~dp0tagui_datatable.csv"
	del "tagui_datatable_transpose.csv"
)
if "NO_CUSTOM_CSV_FILE" == "%custom_csv_file%" set "custom_csv_file=%flow_file%.csv"

rem check datatable csv file for batch automation
set tagui_data_set_size=1 
if not exist "%custom_csv_file%" goto no_datatable
	rem for /f "tokens=* usebackq" %%c in (`gawk -F"," "{print NF}" "%custom_csv_file%" ^| sort -nu ^| head -n 1`) do set min_column=%%c
	rem for /f "tokens=* usebackq" %%c in (`gawk -F"," "{print NF}" "%custom_csv_file%" ^| sort -nu ^| tail -n 1`) do set max_column=%%c
	rem below counts the first row, otherwise edge cases will break this
	for /f "tokens=* usebackq" %%c in (`head -n 1 "%custom_csv_file%" ^| gawk -F"," "{print NF}"`) do set min_column=%%c

	if %min_column% lss 2 (
		echo ERROR - %custom_csv_file% has has lesser than 2 columns | tee -a "%flow_file%.log"
	) else (
		set /a tagui_data_set_size=%min_column% - 1
	)
:no_datatable

rem big loop for managing multiple data sets in datatable
for /l %%n in (1,1,%tagui_data_set_size%) do (
set tagui_data_set=%%n

rem add delay between repetitions to pace out iterations
if !tagui_data_set! neq 1 if %tagui_speed_mode%==false php -q sleep.php 3

rem parse automation flow file, check for initial parse error
rem check R, python, sikuli, chrome, before calling casperjs
php -q tagui_parse.php "%flow_file%" | tee -a "%flow_file%.log"
for /f "usebackq" %%f in ('%flow_file%.log') do set file_size=%%~zf
if !file_size! gtr 0 (
	if !tagui_data_set! equ 1 (
		echo ERROR - automation aborted due to above | tee -a "%flow_file%.log"
		set tagui_error_code=1
		goto break_for_loop
	)
)

rem check if api call is made in generated js file to set appropriate setting for phantomjs to work
set api=
if exist "%flow_file%.js" (
	find /i /c "api http" "%flow_file%.js" > nul
	if not errorlevel 1 set api= --web-security=false
)

rem start R process if integration file is created during parsing
if exist "tagui_r\tagui_r.in" (
        start /min cmd /c Rscript tagui_r\tagui_r.R 2^>^&1 ^| tee -a tagui_r\tagui_r.log
)

rem start python process if integration file is created during parsing
if exist "tagui_py\tagui_py.in" (
        start /min cmd /c python -u tagui_py\tagui_py.py 2^>^&1 ^| tee -a tagui_py\tagui_py.log
)

rem start sikuli process if integration file is created during parsing
if exist "tagui.sikuli\tagui_sikuli.in" (
	rem echo [starting sikuli process] | tee -a "%flow_file%.log"
	start /min cmd /c java -jar sikulix\sikulix.jar -r tagui.sikuli -d 3 2^>^&1 ^| tee -a tagui.sikuli\tagui.log
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

	rem skip restarting chrome in speed mode and resuse 1st websocket url	
	set or_result=F
	if !tagui_data_set! equ 1 set or_result=T
	if %tagui_speed_mode%==false set or_result=T
	if "!or_result!"=="T" (

	rem check for which operating system and launch chrome accordingly
	set chrome_started=Windows
	set chrome_switches=--user-data-dir=chrome\tagui_user_profile --remote-debugging-port=9222 about:blank
	if not exist "%chrome_command%" (
		echo ERROR - cannot find Chrome at "%chrome_command%"
		echo update chrome_command setting in tagui\src\tagui.cmd to your chrome.exe
		exit /b 1
	)
	for /f "tokens=* usebackq" %%p in (`wmic process where "caption like '%%chrome.exe%%' and commandline like '%%tagui_user_profile --remote-debugging-port=9222%%'" get processid 2^>nul ^| cut -d" " -f 1 ^| sort -nur ^| head -n 1`) do set chrome_process_id=%%p
	if not "!chrome_process_id!"=="" taskkill /PID !chrome_process_id! /T /F > nul 2>&1
	start "" "%chrome_command%" !chrome_switches! !window_size! !headless_switch!

	:scan_ws_again
	rem wait until chrome is ready with websocket url for php thread
	for /f "tokens=* usebackq" %%u in (`curl -s localhost:9222/json ^| grep -A 1 "\"url\": \"about:blank\"" ^| grep webSocketDebuggerUrl ^| cut -d" " -f 5`) do set ws_url=%%u
	if "!ws_url!"=="" goto scan_ws_again

	rem end of if block for restarting chrome process
	)

	rem launch php process to manage chrome websocket communications
	start /min cmd /c php -q tagui_chrome.php !ws_url! ^| tee -a tagui_chrome.log

rem end of if block to start chrome processes
)

rem check if test mode is enabled and run casperjs accordingly, before sending finish signal if integrations are active
if %tagui_test_mode%==false (
	casperjs "%flow_file%.js" %params%!api! | tee -a "%flow_file%.log"
) else (
	casperjs test "%flow_file%.js" !api! --xunit="%flow_file%.xml" | tee -a "%flow_file%.log"
)
rem checking for existence of files is important, otherwise in loops integrations will run even without enabling
if exist "tagui_r\tagui_r.in" echo finish > tagui_r\tagui_r.in
if exist "tagui_py\tagui_py.in" echo finish > tagui_py\tagui_py.in
if exist "tagui.sikuli\tagui_sikuli.in" echo finish > tagui.sikuli\tagui_sikuli.in
if exist "tagui_chrome.in" echo finish > tagui_chrome.in

rem kill chrome processes by checking which os the processes are started on
if not "!chrome_started!"=="" if %tagui_speed_mode%==false (
	for /f "tokens=* usebackq" %%p in (`wmic process where "caption like '%%chrome.exe%%' and commandline like '%%tagui_user_profile --remote-debugging-port=9222%%'" get processid 2^>nul ^| cut -d" " -f 1 ^| sort -nur ^| head -n 1`) do set chrome_process_id=%%p
	if not "!chrome_process_id!"=="" taskkill /PID !chrome_process_id! /T /F > nul 2>&1
)
rem end of big loop for managing multiple data sets in datatable
)
:break_for_loop

rem additional windows section to convert unix to windows file format
gawk "sub(\"$\", \"\")" "%flow_file%.raw" > "%flow_file%.raw.tmp"
move /Y "%flow_file%.raw.tmp" "%flow_file%.raw" > nul
gawk "sub(\"$\", \"\")" "%flow_file%.js" > "%flow_file%.js.tmp"
move /Y "%flow_file%.js.tmp" "%flow_file%.js" > nul
gawk "sub(\"$\", \"\")" "%flow_file%.log" > "%flow_file%.log.tmp"
move /Y "%flow_file%.log.tmp" "%flow_file%.log" > nul
gawk "sub(\"$\", \"\")" "tagui_chrome.log" > "tagui_chrome.log.tmp"
move /Y "tagui_chrome.log.tmp" "tagui_chrome.log" > nul
rem keep non-windows logs to help debug integrations when needed
rem and prevent file-still-locked issues while processes are exiting
gawk "sub(\"$\", \"\")" "tagui_r\tagui_r.log" > "tagui_r\tagui_r_windows.log"
gawk "sub(\"$\", \"\")" "tagui_py\tagui_py.log" > "tagui_py\tagui_py_windows.log"
gawk "sub(\"$\", \"\")" "tagui.sikuli\tagui.log" > "tagui.sikuli\tagui_windows.log"

rem check report option to generate html automation log
for /f "usebackq" %%f in ('%flow_file%.log') do set file_size=%%~zf
if %file_size% gtr 0 if %tagui_html_report%==true (
	php -q tagui_report.php "%flow_file%"
	gawk "sub(\"$\", \"\")" "%flow_file%.html" > "%flow_file%.html.tmp"
	move /Y "%flow_file%.html.tmp" "%flow_file%.html" > nul
)

rem check upload option to upload result to hastebin.com
for /f "usebackq" %%f in ('%flow_file%.log') do set file_size=%%~zf
if %file_size% gtr 0 if %tagui_upload_result%==true (
rem set flow_file to blank or the variable will break that tagui call
	set "tmp_flow_file=%flow_file%"
	set flow_file=
	tagui samples\8_hastebin quiet "!tmp_flow_file!"
)

rem remove logs if tagui_no_logging exists
if exist "tagui_no_logging" (
	if exist "%flow_file%.raw" del "%flow_file%.raw"
	if exist "%flow_file%.log" del "%flow_file%.log" 
	if exist "%flow_file%.js" del "%flow_file%.js"
)

rem change back to initial directory where tagui is called
cd /d "%initial_dir%"

rem returns 0 if no error parsing flow file, otherwise return 1
exit /b %tagui_error_code%
