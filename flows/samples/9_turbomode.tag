// This flow shows running command line commands to tweak TagUI to turbo mode
// To enable turbo mode run this - tagui 9_turbomode.tag -n -q on
// To revert back to production - tagui 9_turbomode.tag -n -q off
// More info - https://github.com/kelaberetiv/TagUI/issues/1093

if !p1
    echo [USAGE] provide on / off as parameter

else if p1 equals to 'on'
    echo [TAGUI] updating to tournament edition
    fs = require('fs'); fs.remove('tagui_header.js'); fs.remove('tagui_chrome.php');
    run curl -k -s -L -o tagui_header.js https://github.com/kelaberetiv/TagUI/files/6935769/tagui_header_core.txt
    run curl -k -s -L -o tagui_chrome.php https://github.com/kelaberetiv/TagUI/files/6935768/tagui_chrome_core.txt
    echo [TAGUI] done, turbo mode activated

else if p1 equals to 'off'
    echo [TAGUI] reverting to production edition
    fs = require('fs'); fs.remove('tagui_header.js'); fs.remove('tagui_chrome.php');
    run curl -k -s -L -o tagui_header.js https://raw.githubusercontent.com/kelaberetiv/TagUI/master/src/tagui_header.js
    run curl -k -s -L -o tagui_chrome.php https://raw.githubusercontent.com/kelaberetiv/TagUI/master/src/tagui_chrome.php
    echo [TAGUI] done, turbo mode deactivated

else
    echo [USAGE] provide on / off as parameter
