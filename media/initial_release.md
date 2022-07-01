# TA.Gui
TA.Gui is a tool for non-developers and business users to automate web apps ~ http://tebel.org

![Sample Automation Flow](https://raw.githubusercontent.com/tebelorg/TA.Dump/master/tagui_flow.gif)

Above video shows a sample automation flow to automate mass account registrations - 1. recording sequence of steps, 2. editing to wait for a few seconds, 3. playing the automation flow (skip submission step in order not to spam)

# Why This
Automate repetitive parts of your work - use cases include data acquisition, process and test automations of web apps. TA.Gui is open-source software released under MIT license, that means you can freely use, modify or share it.

### FEATURES
- natural language with JavaScript support
- Chrome extension for recording steps
- support repositories and datatables
- auto-wait XPath/CSS element selector
- run by schedule, command line, REST API
- support outgoing API calls to webservices

### HOW IT WORKS
TA.Gui converts your intentions in simple natural language into lines of working JavaScript code for CasperJS, PhantomJS & SlimerJS to cast their web automation magic. For example, TA.Gui will instantly convert the automation flow below into 100+ lines of JavaScript code and perform the steps to download a Typeform report automatically.  

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

The automation flow can be triggered from scheduling, command line, URL, REST API, email etc. Everything happens headlessly in the background without seeing any web browser, so you can continue using the computer or server uninterrupted. Running on a visible web browser is also supported, using Firefox browser and SlimerJS (see firefox option below). Outgoing API calls can be made with a single line to integrate with other downstream applications.

If you know JavaScript coding and want to be more expressive, you can even use JavaScript directly in the flow. If not, you will still enjoy friendly but powerful features such as repositories to store your reusable objects, datatables for batch automation, and a Chrome extension which creates automation flows by recording your actions.

Originally developed by a test automation engineer to avoid writing code to automate web interactions.

# Set Up
TA.Gui is in v1.0 and runs on macOS, Linux, Windows

### PACKAGED INSTALLATION
Easiest way to use TA.Gui - no setup is needed, all dependencies are packaged in

Platform|macOS|Linux|Windows|Node.js
:------:|:---:|:---:|:-----:|:-----:
Package|[unzip and run](https://raw.githubusercontent.com/tebelorg/TA.Dump/master/TA.Gui_macOS.zip)|[unzip and run](https://raw.githubusercontent.com/tebelorg/TA.Dump/master/TA.Gui_Linux.zip)|[unzip and run](https://raw.githubusercontent.com/tebelorg/TA.Dump/master/TA.Gui_Windows.zip)|[npm install tagui](https://www.npmjs.com/package/tagui)

Optional - configure web browser settings in tagui_config.txt, such as browser resolution, step timeout of 10s etc

### MANUAL INSTALLATION
If you prefer to download dependencies manually from respective websites

1. PhantomJS (headless scriptable web browser) - http://phantomjs.org
2. CasperJS (navigation/testing for PhantomJS) - http://casperjs.org
3. SlimerJS (scriptable web browser for Firefox) - https://slimerjs.org
4. TA.Gui (general purpose web automation tool) - https://git.io/vMCTZ
5. PHP (only required for manual Windows setup) - http://windows.php.net

Tip - for manual Windows setup, 1. set SLIMERJS_EXECUTABLE environment variable to point to slimerjs.bat, 2. place [GNU utilities](http://unxutils.sourceforge.net) (gawk/tee/sort/head/tail) in tagui\src\unx folder, 3. add phantomjs\bin, casperjs\bin, php folders to path

# To Use
### COMMAND LINE
```
./tagui flow_filename option(s) for macOS/Linux, tagui flow_filename option(s) for Windows
```
- Automation flow filename can have no extension, .gui or .txt file extension
- Objects, keywords, datatables can be defined in flow_filename.csv (optional)

Option|Purpose
:----:|:------
firefox|run on visible Firefox web browser instead of invisible browser (first install [Firefox](https://www.mozilla.org/en-US/firefox/new/))
report|generate a web report for easy sharing of run results (default is only a text log file)
debug|show run-time backend messages from PhantomJS for detailed tracing and logging
quiet|run without output except for explicit output (echo / show / check / api / errors etc)
test|professional testing using [CasperJS assertions](http://docs.casperjs.org/en/latest/modules/tester.html) (TA.Gui dynamic tx('selector') usable)
input(s)|add your own parameter(s) to be used in your automation flow as variables p1 to p9

Tip - if you have issue running visible automation using Firefox/SlimerJS [check this setting](https://docs.slimerjs.org/current/installation.html#using-unstable-version-or-very-old-versions-of-firefox)

### BY SCHEDULING
To schedule an automation flow in crontab (macOS/Linux), for example at 8am daily
```
0 8 * * * /full_path_on_your_server/tagui flow_filename option(s)
```

Tip - for Windows, use Task Scheduler instead (search schedule from Start Menu)

### CHROME EXTENSION
Download from [Chrome Web Store](https://chrome.google.com/webstore/detail/tagui-web-automation/egdllmehgfgjebhlkjmcnhiocfcidnjk/) to use TA.Gui Chrome web browser extension for recording automation flows. TA.Gui Chrome extension is based on [Resurrectio tool](https://github.com/ebrehault/resurrectio) and records steps such as page navigation, clicking web elements and entering information. To start recording your automation flows, simply click TA.Gui icon on your Chrome toolbar. Right-click for shortcuts to various TA.Gui steps, such as capturing webpage or element screenshot.

### AUTOMATION WORKFLOW
What happens behind the scenes when you run an automation flow

![TA.Gui Flowchart](https://raw.githubusercontent.com/tebelorg/TA.Gui/master/src/media/flowchart.png)

### FLOW SAMPLES
Following automation flow samples (/src/samples folder) are included as part of this repository. They demo different features of TA.Gui and are excellent reference material to use in addition to the cheatsheet. The samples can be browsed in sequence, starting from easy automation flows to more complex ones. They can also be browsed directly to learn a particular feature. They include plenty of notes for self-learning at your own pace.

If you run into any issue or questions, kindly post to [Gitter chat room](https://gitter.im/tebelorg/TA.Gui) or email support@tebel.org

Flow Sample |Purpose
:-----------|:------
1_yahoo|searches github on Yahoo and captures screenshot of results
2_twitter|goes to a Twitter page and saves some profile information
3_github|goes to a GitHub page and downloads the repo using 2 ways
4_conditions|goes through examples of using conditions in natural language
5_repositories|shows using repositories on Russian social media site VK.com
6_datatables|set of flows uses datatables to retrieve and act on GitHub info
7_testing|shows how to use check step and CasperJS test assertions

# Pipeline
The goal of making automation accessible to more people is somewhat met by TA.Gui v1.0. By recording and then editing in simple natural language, a streamline development workflow is now possible for rapid prototyping and deployment of automation. Whether someone knows programming or not.

The goal for TA.Gui v2.0 is to push the boundaries of what is possible to be done by an automation tool.

By reproducing increasingly complex cognitive interactions that represent our intentions, we can let computers work on repetitive or time-critical tasks that people need to get done. This helps free up people's time for higher-value activities, or simply for more leisure. Suggestions or pull requests that support this goal are certainly welcome.

To feedback bugs, suggestions or pull requests, kindly [raise an issue](https://github.com/tebelorg/TA.Gui/issues) or email support@tebel.org

Feature|Purpose
:-----:|:------
Enhancements|feel free to review and suggest new features

# Cheat Sheet
### STEPS DESCRIPTION
- TA.Gui auto-waits for a webpage element to appear and interacts with it as soon as it appears
- Element identifier can be auto-recorded using TA.Gui Chrome extension, or [found from web browser](https://help.surveygizmo.com/help/how-to-find-element-ids-to-use-with-javascript)
- TA.Gui auto-selects provided identifier in this order - xpath, css, id, name, class, title, aria-label, text(), href

Step|Parameters (separator in bold)|Purpose
:---|:-----------------------------|:------
tap / click|element to click|click on an element
hover / move|element to hover|move cursor to element
type / enter|element to type ***as*** text to type|enter element as text
select / choose|element to select ***as*** option to select|choose dropdown option
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

Tip - if you want to write xpath, use double quotes for text //\*[@title="Login"]

### CONDITIONS EXAMPLES
- Conditions can be expressed in natural language (optional brackets) or [JavaScript](https://www.w3schools.com/js/)
- Write text in quotation marks (either " or ' works) to differentiate from variable names
- Conditions if / for / while apply to the next step and async wait auto-disables in loops

Condition (in natural language)|JavaScript
:------------------------------|:---------
example - if day equals to "Friday"| if (day == "Friday")
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
or|**&#124;&#124;**

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

### TESTING (for QA, test automation engineers, etc)
The step check allows simple testing of conditions. For professional test automation, CasperJS comes with a tester module for unit and functional testing purpose. To use the advanced testing features, run TA.Gui with the test option.

CasperJS test scripts are inherently different in structure and syntax from its usual automation scripts. With the test option, TA.Gui automatically converts your automation flow to work as a test script. CasperJS will output a XUnit XML file, which is compatible with continuous integration tools such as Jenkins. Running together with the report option outputs a web report of the test execution for easy sharing.

TA.Gui allows you to reuse the same flow for testing or automation by running it with or without the test option. Below are examples of CasperJS test assertions written in JavaScript code that can be used directly in your automation flow (after navigating and taking desired actions using usual flow steps). As this is direct CasperJS code, there is no auto-wait. You can use the wait step to explicitly wait for a few seconds for steps which will take a long time for web-server to respond.
```javascript
test.assertTextExists('About Tebel','Check for About Tebel text');
test.assertSelectorHasText(tx('header'), 'Interface automation','Check for phrase in header element');
```

For the list of 30+ expressive test assertions built into CasperJS, [click here](http://docs.casperjs.org/en/latest/modules/tester.html). To know more about CasperJS testing framework, [click here](http://docs.casperjs.org/en/latest/testing.html). As TA.Gui allows you to write JavaScript code directly within the automation flow, advanced testing or coding techniques that can be implemented in CasperJS should work directly within your flow.

TA.Gui recognizes most JavaScript code. In the rare event you get an error saying that it cannot understand the step for your JavaScript line, kindly raise an issue or feel free to modify the source code (tagui_parse.php is where interpretation of natural language to CasperJS JavaScript code takes place). Alternatively, you can use the undocumented step js to explicitly declare that whatever follows on that line is JavaScript code.

### API (for developers, tinkerers, curious folks, etc)
Automation flows can also be triggered via REST API. TA.Gui has an API service and runner for managing a queue of incoming requests via API. To set up, add a crontab entry on your server with the desired frequency to check and process incoming service requests. Below example will check every 15 minutes and run pending flows in the queue. Most servers or development boxes using this should be on Linux or macOS, kindly email or raise an issue if otherwise.
```
0,15,30,45 * * * * /full_path_on_your_server/tagui_crontab
```

To call an automation flow from your application or web browser, use below API syntax. Custom input(s) supported. Automation flows can also be triggered from emails using the API. For email integration, [install TA.Mail](https://github.com/tebelorg/TA.Mail). Its main job is an open-source mailbot to act on incoming emails or perform mass emailing; its part-time job is delivering emails by API.
```
your_website_url/tagui_service.php?SETTINGS="flow_filename option(s)"
```

Besides integrating with web applications, TA.Gui can be extended to integrate with hardware (eg Arduino or Raspberry Pi) for physical world interactions or machine learning service providers for AI decision-making ability. Input parameters can be sent to an automation flow to be used as variables p1 to p9. Output parameters from an automation flow can be sent to your Arduino or application REST URL (see flow samples 3_github and 6C_datatables for examples).

For making outgoing API calls in your automation flow, to feed data somewhere or send emails etc, use the api step followed by full URL (including parameters) of the API call. Response from the API will be printed to output. For example using TA.Mail, emails with run-time variables can be sent directly from your flow with a single line.

File Reference |Purpose
:--------------|:------
tagui|main runner for TA.Gui web automation
tagui.cmd|main runner for Windows platform
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

# Be a Force for Good
Web automation is a tool. Like fire, it is neither kind nor evil but takes on the intentions of its bearer. Be a force for good and use web automation wisely. TA.Gui code is intentionally made honest and not hiding identity as an automated user.

# License
TA.Gui is open-source software released under the MIT license
