# TA.Gui
TA.Gui is a tool for non-developers and business users to automate web apps

# Why This
Insanely easy. Lightning fast. Open-source.

TA.Gui converts automation flows in simple natural language into lines of JavaScript code for CasperJS & PhantomJS to perform their web automation magic. For example, TA.Gui will instantly convert the flow below into ~100 lines of JavaScript code and perform the series of steps to download a Typeform report automatically. The flow can be triggered from a scheduler, command line, REST API, URL, email etc.

```
https://www.typeform.com
click login
type username|user@gmail.com
type password|12345678
click btnlogin
hover Test Event
click action results tooltip
click section_results
download https://admin.typeform.com/form/2592751/analyze/csv|report.csv
```

# Set Up
1. install CasperJS (navigation/testing for PhantomJS) - http://casperjs.org
2. install PhantomJS (headless scriptable web browser) - http://phantomjs.org
3. install TA.Gui - https://github.com/tebelorg/TA.Gui/archive/master.zip

# To Use
```
./tagui flow_filename parameters
```

# Pipeline
Feature|Description
TA.Mind|
Chrome Extension|
Enhancements|config, keywords, objects
Active I/O|AIO mail, url, script
Passive I/O|PIO xls, csv, reports

# License
TA.Gui is open-source software released under the MIT license
