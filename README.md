# TA.Gui
TA.Gui is a tool for non-developers and business users to automate web apps

![Sample Automation](https://github.com/tebelorg/TA.Gui/raw/master/sample.png)

# Why This
Automate repetitive parts of your work - use cases include data acquisition, process and test automations.

TA.Gui converts automation flows in simple natural language into lines of JavaScript code for CasperJS & PhantomJS to perform their web automation magic. For example, TA.Gui will instantly convert the flow below into 120+ lines of working JavaScript code and perform the series of steps to download a Typeform report automatically.

The flow can be triggered from scheduling, command line, REST API, URL, email etc. Everything happens in the background without seeing any web browser, so that you can continue to use the computer or server uninterrupted. Running on a visible web browser is also supported, using Firefox and SlimerJS (see firefox option below).

```
https://www.typeform.com
click login
type username as user@gmail.com
type password as 12345678
click btnlogin
hover Test Event
click action results tooltip
click section_results
download https://admin.typeform.com/form/2592751/analyze/csv to report.csv
```
If you already know JavaScript coding and want to be more expressive, you can even mix code directly in the flow.

# Set Up
1. install CasperJS (navigation/testing for PhantomJS) - http://casperjs.org
2. install PhantomJS (headless scriptable web browser) - http://phantomjs.org
3. install TA.Gui - https://github.com/tebelorg/TA.Gui/archive/master.zip

# To Use
```
./tagui flow_filename option(s)
```
Option|Purpose
:----:|:------
firefox|run on visible Firefox browser (need to install Firefox, and SlimerJS - https://slimerjs.org)
debug|show run-time backend messages from PhantomJS for detailed tracing or logging
- Flow filename extension can be no extension, .gui or .txt
- Browser settings can be configured in tagui_config.txt

# Pipeline
Feature|Purpose
:-----:|:------
Chrome Extension|facilitate creation of automation flows
Test Automation|wrapper/helper for CasperJS assertions
Enhancements|add keywords and object repository
Active I/O|triggering and actioning from email/API
Passive I/O|xls/csv datatables and web-based results
Parallel Run|develop concurrent automation runs
One Package|evaluate packaging in CasperJS/PhantomJS
Microsoft Friendly|reduce friction for running on Windows
Health Check|self-test and self-healing of dependencies

# Cheat Sheet
Step|Parameters (and separator in bold)|Purpose
:---|:-----------|:------
tap / click|element to click|click on an element
hover / move|element to hover|move cursor to element
type / enter|element to type ***as*** text to type|enter element as text
read / fetch|element to read ***to*** variable name|fetch text to variable
show / print|element to read |print element text to output
download|url to download ***to*** filename to save|download url to file
receive|url keyword to watch ***to*** filename to save|receive resource to file
echo|text (in quotation marks) and variables|print text/variables to output
save|element to read ***to*** optional filename|save text to file
dump|variable name ***to*** optional filename|save variable to file
snap|element (page = webpage) ***to*** optional filename|save screenshot to file
wait|time in milliseconds|wait for some time
check|condition to check **&#124;** text if true **&#124;** text if false|check condition and print result
frame|frame name **&#124;** subframe name if any|next step in frame/subframe

- XPath is robust for identification and used to check for a particular webpage element
- XPath is checked in following order of priority full-xpath, id, name, class, title, text()

<br>

Condition (in natural language)|JavaScript
:------------------------------|:---------
example 1 - if A more than B and C not equal to D | if (A > B) && (C != D)
example 2 - while cupcakes equal to 12| while (cupcakes == 12)
more than or equal to / greater than or equal to / higher than or equal to|>=
less than or equal to / lesser than or equal to / lower than or equal to|<=
more than / greater than / higher than|>
less than / lesser than / lower than|<
not equal to|!=
equal to|==
and|&&
or|&#124;&#124;

- JavaScript code can be used (in CasperJS's context); if/for/while applies to next step
- conditions can be expressed using natural language or JavaScript; brackets optional

# License
TA.Gui is open-source software released under the MIT license
