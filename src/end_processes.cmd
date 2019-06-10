@echo off
rem # TO KILL INTEGRATION PROCESSES IF CTRL+C WAS USED TO QUIT TAGUI PREMATURELY ~ TEBEL.ORG #

rem checking for existence of files before sending finish signal is important
rem otherwise for ongoing tagui iterations, all integrations will be invoked

if exist "tagui_r\tagui_r.in" echo finish > tagui_r\tagui_r.in
if exist "tagui_py\tagui_py.in" echo finish > tagui_py\tagui_py.in
if exist "tagui.sikuli\tagui_sikuli.in" echo finish > tagui.sikuli\tagui_sikuli.in
if exist "tagui_chrome.in" echo finish > tagui_chrome.in
