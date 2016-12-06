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
Feature|Details
-------|-------
Logic Engine|for supporting rule-based decision making
Chrome Extension|facilitates creation of automation flows
Enhancements|configuration, keywords, object repository
Active I/O|triggering and actioning from email/API
Passive I/O|xls/csv datatables and web-based reports

# Cheat Sheet
Word|Parameter(s)|Purpose
----|----------|-------
tap / click|element to click|click on an element
hover / move|element to hover|move cursor to element
type / enter|element to type&#124;text to type|enter text in element
read / fetch|element to read&#124;variable name|read text into variable
show / print|element to show|print text to screen and logfile
download|url to download&#124;filename to save to|download from url
file|url keyword&#124;filename to save to|download when resource received
echo|none|print text and variables to screen and logfile
save|element to save&#124;optional filename to save to|save element text to file
dump|variable name&#124;optional filename to save to|save variable to file
snap|element to snapshot (page for whole screen)&#124;optional filename to save to|save screenshot to file
wait|time in milliseconds|wait for some time
test|condition to test&#124;text if true&#124;text if false|test a condition and print result to screen and logfile
frame|frame name&#124;subframe name if any|specify that next step is within a HTML frame/subframe

1. Above words are case-insensitive to let users write flexibly in the way they want
2. Extra spaces between parameters are automatically trimmed; don't worry about them
3. JavaScript can be used and executed within CasperJS's context, if/for/while applies to next step

# License
TA.Gui is open-source software released under the MIT license
