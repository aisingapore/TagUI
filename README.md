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
# Language
Word|Parameter(s)|Purpose
----|----------|-------
tap / click|element to click|click on an element
hover / move|element to hover|move cursor to element
type / enter|element to type, text to type|enter text in element
read / fetch||element to read, variable name|read text into variable
show / print||element to show|print text to screen and log file
download||url to download, file to save to|download from url
file||
echo||
save||
dump||
snap||
wait||
test||
frame||
js||

# Pipeline
Feature|Details
-------|-------
Logic Engine|for supporting rule-based decision making
Chrome Extension|facilitates creation of automation flows
Enhancements|configuration, keywords, object repository
Active I/O|triggering and actioning from email/API
Passive I/O|xls/csv datatables and web-based reports

# License
TA.Gui is open-source software released under the MIT license
