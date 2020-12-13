@echo off
rem # TO MANUALLY KILL TAGUI PROCESSES, EG IF CTRL+C WAS USED TO QUIT TAGUI ~ TEBEL.ORG #

rem aggressive failsafe for tagui and friends that have not exited gracefully
rem works by scanning processes for tagui keywords and killing them one by one

rem set path to unix utilities for windows command prompt
if exist "%~dp0unx\gawk.exe" set "path=%~dp0unx;%path%"

:repeat_kill_php
for /f "tokens=* usebackq" %%p in (`wmic process where "caption like '%%php.exe%%' and commandline like '%%tagui_chrome.php%%'" get processid 2^>nul ^| cut -d" " -f 1 ^| sort -nur ^| head -n 1`) do set php_process_id=%%p
if not "%php_process_id%"=="" (
    taskkill /PID %php_process_id% /T /F > nul 2>&1
    goto repeat_kill_php
)

:repeat_kill_chrome
for /f "tokens=* usebackq" %%p in (`wmic process where "caption like '%%chrome.exe%%' and commandline like '%%tagui_user_profile_ --remote-debugging-port=9222%%'" get processid 2^>nul ^| cut -d" " -f 1 ^| sort -nur ^| head -n 1`) do set chrome_process_id=%%p
if not "%chrome_process_id%"=="" (
    taskkill /PID %chrome_process_id% /T /F > nul 2>&1
    goto repeat_kill_chrome
)

:repeat_kill_sikuli
for /f "tokens=* usebackq" %%p in (`wmic process where "commandline like '%%tagui.sikuli%%' and not caption like '%%wmic%%' and not caption like '%%cmd.exe%%'" get processid 2^>nul ^| cut -d" " -f 1 ^| sort -nur ^| head -n 1`) do set sikuli_process_id=%%p
if not "%sikuli_process_id%"=="" (
    taskkill /PID %sikuli_process_id% /T /F > nul 2>&1
    goto repeat_kill_sikuli
)

:repeat_kill_python
for /f "tokens=* usebackq" %%p in (`wmic process where "commandline like '%%tagui_py.py%%' and not caption like '%%wmic%%' and not caption like '%%cmd.exe%%'" get processid 2^>nul ^| cut -d" " -f 1 ^| sort -nur ^| head -n 1`) do set python_process_id=%%p
if not "%python_process_id%"=="" (
    taskkill /PID %python_process_id% /T /F > nul 2>&1
    goto repeat_kill_python
)

:repeat_kill_r
for /f "tokens=* usebackq" %%p in (`wmic process where "commandline like '%%tagui_r.R%%' and not caption like '%%wmic%%' and not caption like '%%cmd.exe%%'" get processid 2^>nul ^| cut -d" " -f 1 ^| sort -nur ^| head -n 1`) do set r_process_id=%%p
if not "%r_process_id%"=="" (
    taskkill /PID %r_process_id% /T /F > nul 2>&1
    goto repeat_kill_r
)

:repeat_kill_tagui
for /f "tokens=* usebackq" %%p in (`wmic process where "executablepath like '%%\\tagui\\src\\%%' and not caption like '%%cut.exe%%' and not caption like '%%sort.exe%%' and not caption like '%%head.exe%%'" get processid 2^>nul ^| cut -d" " -f 1 ^| sort -nur ^| head -n 1`) do set tagui_process_id=%%p
if not "%tagui_process_id%"=="" (
    taskkill /PID %tagui_process_id% /T /F > nul 2>&1
    goto repeat_kill_tagui
)
