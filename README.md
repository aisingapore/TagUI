# TA.Gui
TA.Gui is a tool for non-developers and business users to automate web apps

![Sample Automation Flow](https://raw.githubusercontent.com/tebelorg/TA.Dump/master/tagui_flow.gif)

Above video shows a sample automation flow to automate mass account registrations - recording sequence of steps, editing to wait for a few seconds at the end, playing the automation flow (skip submission step in order not to spam)

# Why This
Automate repetitive parts of your work - use cases include data acquisition, process and test automations of web apps. TA.Gui is open-source software released under the MIT license, that means you can freely use, modify or share it.

### FEATURES
- natural language with JavaScript support
- Chrome extension for recording steps
- headless (invisible) and visible mode
- repositories for reusable objects
- datatables for batch automation
- auto-wait for element to interact
- dynamic XPath/CSS element selector
- run by schedule, command line, REST API
- support outgoing API calls to webservices
- cross-platform (macOS/Linux; Windows soon)

### HOW IT WORKS
TA.Gui converts your intentions in simple natural language into lines of working JavaScript code for CasperJS & PhantomJS to cast their web automation magic. As an example, TA.Gui will instantly convert the automation flow below into 100+ lines of JavaScript code and perform the sequence of steps to download a Typeform report automatically.

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

The automation flow can be triggered from scheduling, command line, URL, REST API, email etc. Everything happens headlessly in the background without seeing any web browser, so you can continue using the computer or server uninterrupted. Running on a visible web browser is supported, using Firefox and SlimerJS (see firefox option below). Outgoing API calls can be made with a single line to integrate with other downstream applications.

If you know JavaScript coding and want to be more expressive, you can even use JavaScript directly in the flow. If not, you will still enjoy friendly but powerful features such as repositories to store your reusable objects, datatables for batch automation, and a Chrome extension which creates automation flows by recording your actions.

Originally developed by a test automation engineer to avoid writing code to automate web-based interactions.

# Set Up
TA.Gui is in beta version (v0.6.0) and runs on macOS/Linux (coming to Windows soon)

1. install PhantomJS (headless scriptable web browser) - http://phantomjs.org
2. install CasperJS (navigation/testing for PhantomJS) - http://casperjs.org
3. install TA.Gui (friendly lightning fast automation tool) - https://git.io/vMCTZ

Optional - configure web browser settings in tagui_config.txt, such as browser resolution, step timeout of 10s etc

Tip - developers with Node.js set up might find it more convenient to npm install tagui

# To Use
### COMMAND LINE
```
./tagui flow_filename option(s)
```
- Automation flow filename can have no extension, .gui or .txt file extension
- Objects, keywords, datatables can be defined in flow_filename.csv (optional)

Option|Purpose
:----:|:------
firefox|run on visible Firefox browser (first install Firefox, and SlimerJS - https://slimerjs.org)
report|generate a web report for easy sharing of run results (default is only a text log file)
debug|show run-time backend messages from PhantomJS for detailed tracing and logging
test|professional testing using [CasperJS assertions](http://docs.casperjs.org/en/latest/modules/tester.html); TA.Gui XPath tx('selector') usable

Tip - if you have issue running visible automation using Firefox/SlimerJS [check this setting](https://docs.slimerjs.org/current/installation.html#using-unstable-version-or-very-old-versions-of-firefox)

### BY SCHEDULING
To schedule an automation flow in crontab, for example at 8am daily
```
0 8 * * * /full_path_on_your_server/tagui flow_filename option(s)
```

### CHROME EXTENSION
Prior to public release, to use TA.Gui Chrome web browser extension for recording automation flows, type chrome://extensions in Google Chrome, then drag and drop TA.Gui chrome folder into the browser. TA.Gui Chrome extension is based on [Resurrectio tool](https://github.com/ebrehault/resurrectio) and records steps such as page navigation, clicking web elements and entering information. To start recording and exporting your automation flows, simply click TA.Gui icon on your Chrome toolbar.

### TA.GUI WORKFLOW
What happens behind the scenes when you run an automation flow

![TA.Gui Flowchart](https://raw.githubusercontent.com/tebelorg/TA.Gui/master/src/media/flowchart.png)

# Pipeline
To feedback bugs or suggestions, kindly email support@tebel.org

Feature|Purpose
:-----:|:------
Chrome Extension|make it more expressive and powerful
Microsoft Friendly|support running on Microsoft Windows
Enhancements|feel free to review and suggest new features

# Cheat Sheet
### STEPS DESCRIPTION
- TA.Gui auto-waits for a webpage element to appear and interacts with it as soon as it appears
- Element identifier can be auto-recorded using TA.Gui Chrome extension, or found from [web browser](https://help.surveygizmo.com/help/how-to-find-element-ids-to-use-with-javascript)
- TA.Gui auto-selects the provided identifier in this order - full-xpath, full-css, id, name, class, title, text()

Step|Parameters (separator in bold)|Purpose
:---|:-----------------------------|:------
tap / click|element to click|click on an element
hover / move|element to hover|move cursor to element
type / enter|element to type ***as*** text to type|enter element as text
read / fetch|element to read ***to*** variable name|fetch element text to variable
show / print|element to read |print element text to output
save|element to read ***to*** optional filename|save element text to file
echo|text (in quotation marks) and variables|print text/variables to output
dump|text and variables ***to*** optional filename|save text/variables to file
snap|element (page = webpage) ***to*** optional filename|save screenshot to file
download|url to download ***to*** filename to save|download from url to file
receive|url keyword to watch ***to*** filename to save|receive resource to file
wait|optional time in seconds (default is 5 seconds)|explicitly wait for some time
check|condition **&#124;** text if true **&#124;** text if false (text in quotes)|check condition and print result
frame|frame name **&#124;** subframe name if any|next step in frame/subframe
api|full url (including parameters) of api call|call api and print response
//|user comments (ignored during execution)|add user comments

Tip - if you want to write full xpath, use double quotes for text //\*[@title="Login"]

### CONDITIONS EXAMPLES
- Conditions can be expressed in natural language (optional brackets) or [JavaScript](https://www.w3schools.com/js/)
- Write text in quotation marks (either " or ' works) to differentiate from variable names
- Conditions if / for / while apply to the next step and async wait auto-disables in loops

Condition (in natural language)|JavaScript
:------------------------------|:---------
example - if menu contains "fruits"| if (menu.indexOf("fruits")>-1)
example - if A more than B and C not equals to D | if ((A > B) && (C != D))
example - for n from 1 to 4 | for (n=1; n<=4; n++)
example - while cupcakes equal to 12| while (cupcakes == 12)
contain|.indexOf("text")>-1
not contain|.indexOf("text")\<0
equal to|==
not equal to|!=
more than / greater than / higher than|>
more than or equal to / greater than or equal to / higher than or equal to|>=
less than / lesser than / lower than|<
less than or equal to / lesser than or equal to / lower than or equal to|<=
and|&&
or|&#124;&#124;

Tip - when using contain / equal conditions, you can write with or without s behind

### REPOSITORIES
- Repositories help to make objects or steps reusable and improve readability
- Save repository file with same name as your flow filename and with .csv behind
- Repository must have 2 columns, for example below (headers up to you to name)
- Using \`object\` in your flow replaces it with its definition (which can contain objects)
- For example, \`type email\` becomes type user-email-textbox as user@gmail.com

OBJECT|DEFINITION
:-----|:---------
email|user-email-textbox
create account|btn btn--green btn-xl signup-btn
type email|type \`email\` as user@gmail.com
  
### DATATABLES
- Datatables extend the power of repositories files to manage batch automation
- Datatable has 2 or more columns, for example below (headers up to you to name)
- Data-centric approach with rows representing data fields (usually row = test case)
- TA.Gui loops through each column to automate using values from different datasets
- Eg, echo "TESTCASE - \`testname\`" in your flow shows TESTCASE - Trade USDSGD

TEST TRADES|TEST #1|TEST #2|TEST #3
:----------|:------|:------|:------
testname|Trade USDSGD|Trade USDJPY|Trade EURUSD
username|test_account|test_account|test_account
password|12345678|12345678|12345678
currency-pair|USDSGD|USDJPY|EURUSD
size|10000|1000|100000
direction|BUY|SELL|BUY

### TESTING (for QA folks and test automation engineers)
The step check allows simple testing of conditions. For professional test automation, CasperJS comes with a tester module for unit and functional testing purpose. To use the advanced testing features, run TA.Gui with the test option.

CasperJS test scripts are inherently different in structure and syntax from its usual automation scripts. With the test option, TA.Gui automatically converts your automation flow to work as a test script and output a XUnit XML file, which is compatible with continuous integration tools such as Jenkins. Running together with the report option outputs a web report of the test execution for easy sharing.

TA.Gui allows you to reuse the same flow for testing or automation by running it with or without the test option. Below are examples of CasperJS test assertions written in JavaScript code that can be used directly in your automation flow (after navigating and taking desired actions using usual flow steps etc).
```javascript
test.assertTextExists('About Tebel.Automation','Check for About Tebel.Automation text');
test.assertSelectorHasText(tx('header'), 'interface automation','Check for phrase in header element');
```

For the list of expressive test assertions built into CasperJS, [click here](http://docs.casperjs.org/en/latest/modules/tester.html). To know more about CasperJS testing framework, [click here](http://docs.casperjs.org/en/latest/testing.html). As TA.Gui allows you to write JavaScript code directly within the automation flow, advanced testing or coding techniques that can be implemented in CasperJS should be able to be work directly within your automation flow.

TA.Gui recognizes most JavaScript code. In the rare event you get an error saying that it cannot understand the step for your JavaScript line, kindly raise an issue or feel free to modify the source code (tagui_parse.php is where interpretation of natural language to CasperJS JavaScript code takes place). Alternatively, you can use the undocumented step js to explicitly declare that whatever follows on that line is JavaScript code.

### TA.GUI API (for developers and the curious ones)
Automation flows can also be triggered via REST API. TA.Gui has an API service and runner for managing a queue of incoming requests via API. To set up, add a crontab entry on your server with the desired frequency to check and process incoming service requests. For example, the following job will check every 15 minutes and run pending flows in the queue.
```
0,15,30,45 * * * * /full_path_on_your_server/tagui_crontab
```

To call an automation flow from your application or web browser, use below API syntax. Automation flows can also be triggered from emails using the API. For email integration, [install TA.Mail](https://github.com/tebelorg/TA.Mail). Its main job is an open-source mailbot to act on incoming emails or perform mass emailing; it also delivers emails through API as a part-time job.
```
your_website_url/tagui_service.php?SETTINGS="flow_filename option(s)"
```

Besides integrating with web applications, TA.Gui can be extended to integrate with hardware (for example Arduino or Raspberry Pi) for physical world interactions or machine learning service providers for fuzzy decision-making capacity.

For making outgoing API calls in your automation flow, to feed data somewhere or send emails etc, use the api step followed by full URL (including parameters) of the API call. Response from the API will be printed to output. For example using TA.Mail, emails with run-time variables can be sent directly from your flow with a single line.

File Reference |Purpose
:--------------|:------
tagui|main runner for TA.Gui web automation
tagui_config.txt|web browser settings used for automation
tagui_crontab|to run service request batch from crontab
tagui_footer.js|footer template for CasperJS code
tagui_header.js|header template for CasperJS code
tagui_parse.php|to interpret natural language into code
tagui_report.php|to generate html report from text log
tagui_runner.php|retrieving service requests from queue
tagui_service.php|receiving service requests into queue
tagui_service.in|log to track incoming service requests
tagui_service.out|log to track processed service requests
tagui_service.log|log to track service requests history
tagui_service.act|service request batch ready to execute
tagui_service.run|service request batch currently running
tagui_service.done|service request batch finished running

# License
TA.Gui is open-source software released under the MIT license
