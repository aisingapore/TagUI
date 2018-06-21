***TagUI is a CLI tool for digital process automation. This branch of automation is commercially known as RPA (robotic process automation), and primarily aims to reproduce user interactions with computer applications - for example mouse clicks and keyboard entries. For more information on TagUI, visit its [repository page](https://github.com/kelaberetiv/TagUI).***

# [Setup](https://github.com/kelaberetiv/TagUI#set-up)
*In this section, we'll download and install TagUI on your computer. For Windows, unzip the tagui folder to c:\ and for macOS, unzip the tagui folder to your desktop. For Linux, unzip the tagui folder to a convenient folder on your laptop.*

### INSTALLATION (10 minutes)
TagUI is easy to use right away - no setup is needed, in most environments all required dependencies are packaged in. Avoid spaces in the folder path as some components of TagUI don't work well with spaces in folder and file names.

Platform|macOS|Linux|Windows|Node.js (macOS/Linux)
:------:|:---:|:---:|:-----:|:-------------------:
Package|[unzip and run](https://raw.githubusercontent.com/tebelorg/Tump/master/TagUI_macOS.zip)|[unzip and run](https://raw.githubusercontent.com/tebelorg/Tump/master/TagUI_Linux.zip)|[unzip and run](https://raw.githubusercontent.com/tebelorg/Tump/master/TagUI_Windows.zip)|[npm install tagui](https://www.npmjs.com/package/tagui)

***Potential exceptions*** - On some Windows computers, if you see 'MSVCR110.dll is missing' error, install [this from Microsoft website](https://www.microsoft.com/en-us/download/details.aspx?id=30679) (choose vcredist_x86.exe) - this file is required to run the PHP engine. On some newer macOS versions, if you get a 'dyld: Library not loaded' error, [install OpenSSL in this way](https://github.com/kelaberetiv/TagUI/issues/86#issuecomment-372045221). For some flavours of Linux (Ubuntu for example), which do not have PHP pre-installed, google how to install PHP accordingly (Ubuntu for example, apt-get install php).

Optional - configure web browser settings in tagui_config.txt, such as browser resolution, step timeout of 10s etc

![TagUI Flowchart](https://raw.githubusercontent.com/kelaberetiv/TagUI/master/src/media/flowchart.png)

# [Using it (guided)](https://github.com/kelaberetiv/TagUI#to-use)
*In this section, we'll have a guided walkthrough on running TagUI, using its Chrome extension, and some examples.*

### COMMAND LINE (10 minutes)
```
./tagui flow_filename option(s) for macOS/Linux, tagui flow_filename option(s) for Windows
```
- Flow filename can be a local file or the URL of an online file
- Filename can have no extension, .txt or .js or .tagui extension
- See below for details on options or running tagui from any folder

Following steps will run a script to perform a search on Yahoo website and capture a screenshot of the results.

**Windows** - unzip the tagui folder to c:\\. Open command prompt with Start Menu -> Run -> cmd and enter the following

```
c:
cd c:\tagui\src
tagui samples\1_yahoo
```

**macOS** - unzip the tagui folder to your desktop. Open terminal from Apps -> Utilities -> Terminal and enter the following

```
cd /Users/your_id/Desktop/tagui/src
./tagui samples/1_yahoo
```

**Linux** - unzip the tagui folder to a convenient folder on your laptop for example /home/your_id and enter the following

```
cd /home/your_id/tagui/src
./tagui samples/1_yahoo
```

Now try the same workflow with Chrome browser by putting chrome as option (eg tagui samples\1_yahoo chrome). The automation will now run in the foreground instead, so you'll be able to see the navigation on Yahoo and DuckDuckGo websites. TagUI can also be run from desktop icons, scheduled tasks, or REST API calls.

<details>
  <summary>
    Click to show the command line options supported by TagUI and their purposes
  </summary>
  
  Option|Purpose
  :----:|:------
  headless|run on invisible Chrome web browser instead of default PhantomJS (first install [Chrome](https://www.google.com/chrome/))
  chrome|run on visible Chrome web browser instead of invisible PhantomJS (first install Chrome)
  firefox|run on visible Firefox web browser instead of invisible browser (first install [Firefox](https://www.mozilla.org/en-US/firefox/new/))
  upload|upload automation flow and result to [hastebin.com](https://hastebin.com) (expires 30 days after last view)
  report|web report for sharing of run results on webserver (default is only a text log file)
  debug|show run-time backend messages from PhantomJS for detailed tracing and logging
  quiet|run without output except for explicit output (echo / show / check / errors etc)
  speed|skip 3-second delay between datatable iterations (and skip restarting of Chrome)
  test|testing with check step test assertions for CI/CD integration (output XUnit XML file)
  baseline|output execution log and relative-path output files to a separate baseline directory
  input(s)|add your own parameter(s) to be used in your automation flow as variables p1 to p9
  data.csv|specify a csv file to be used as the datatable for batch automation of many records

</details>

<details>
  <summary>
    Click to show info on automation log files, and how to run tagui from any directory
  </summary>
  
  After each automation run, a .log file will be created to store output of the execution, a .js file is the generated JavaScript file, a .raw is the expanded flow after reading in any module sub-scripts that are called in that flow. These files are for user reference purpose and can be helpful in debugging or troubleshooting the automation flow.

  Tip - to run tagui from anywhere in macOS/Linux, use ln -sf /full_path/tagui/src/tagui /usr/local/bin/tagui to create symbolic link. To run tagui from anywhere in Windows, add tagui/src [folder to path](http://lmgtfy.com/?q=add+to+path+in+windows). Then tagui will be accessible from any folder. If you have issue running visible automation using Firefox/SlimerJS [check this setting](https://docs.slimerjs.org/current/installation.html#using-unstable-version-or-very-old-versions-of-firefox).
  
</details>

<details>
  <summary>
    Click to show how to run TagUI scripts by double-clicking as desktop icons
  </summary>
  
  To do that on Windows, create a .cmd or .bat file with contents like the following, which goes to the directory where you want to run the automation, and run tagui command on the file with your specified options. You can also [associate .tagui files directly](https://www.digitaltrends.com/computing/how-to-set-default-programs-and-file-types-in-windows-10) to be opened by tagui\src\tagui.cmd command. Double-clicking the .cmd or .bat file will start automation.

  ```
  @echo off
  c:
  cd c:\folder
  tagui filename quiet speed chrome
  ```

  To do that on macOS / Linux, create a file with contents like the following, which goes to the directory where you want to run the automation, and run tagui command on the file with your specified options. You will need to use the command chmod 700 on the file to give it execute permissions, so that it can be run by double-clicking on it.

  ```
  cd /Users/username/folder
  tagui filename quiet speed chrome
  ```

</details>

### BY SCHEDULING (FYI ONLY)
To schedule an automation flow in crontab (macOS/Linux), for example at 8am daily
```
0 8 * * * /full_path_on_your_server/tagui flow_filename option(s)
```

Tip - for Windows, use Task Scheduler instead (search schedule from Start Menu)

### CHROME EXTENSION (10 minutes)
Download from [Chrome Web Store](https://chrome.google.com/webstore/detail/tagui-web-automation/egdllmehgfgjebhlkjmcnhiocfcidnjk/) to use TagUI Chrome web browser extension for recording automation flows. TagUI Chrome extension is based on [Resurrectio tool](https://github.com/ebrehault/resurrectio) and records steps such as page navigation, clicking web elements and entering information. To start recording automation flows, click TagUI icon on your Chrome toolbar. Right-click for shortcuts to some TagUI steps, such as capturing webpage screenshot or to show the element identifier.

The recording is not foolproof (for example, the underlying recording engine cannot capture frames, popup windows or tab key input). It is meant to simplify flow creation with some edits, instead of typing everything manually. [See this video](https://www.youtube.com/watch?v=bFvsc4a8hWQ) for an example of recording sequence of steps, editing for adjustments and playing back the automation.

### TAGUI WRITER & SCREENSHOTER (FYI ONLY)
TagUI Writer is a Windows app created by [@adegard](https://github.com/adegard) which makes it easy to write TagUI scripts. By pressing Ctrl + Left-click, a popup menu will appear with the list of TagUI steps for you to paste into your text editor. Arnaud also created a ScreenShoter app which makes it easy to capture snaphots for TagUI visual automation. [Download them here](https://github.com/adegard/tagui_scripts).

<details>
  <summary>
    Click to show a preview of how TagUI Writer looks like (it works across different editors)
  </summary>

![TagUI Writer](https://raw.githubusercontent.com/kelaberetiv/TagUI/master/src/media/tagui_writer.png)

</details>

### FLOW SAMPLES (15 minutes)
Following automation flow samples ([tagui/src/samples folder](https://github.com/kelaberetiv/TagUI/tree/master/src/samples)) are included with TagUI

Flow Sample |Purpose
:-----------|:------
[1_yahoo](https://github.com/kelaberetiv/TagUI/blob/master/src/samples/1_yahoo)|searches github on Yahoo and captures screenshot of results
[2_twitter](https://github.com/kelaberetiv/TagUI/blob/master/src/samples/2_twitter)|goes to a Twitter page and saves some profile information
[3_github](https://github.com/kelaberetiv/TagUI/blob/master/src/samples/3_github)|goes to a GitHub page and downloads the repository file
[4_conditions](https://github.com/kelaberetiv/TagUI/blob/master/src/samples/4_conditions)|goes through examples of using conditions in natural language
[5_repositories](https://github.com/kelaberetiv/TagUI/blob/master/src/samples/5_repositories)|shows using repositories on Russian social media site VK.com
[6_datatables](https://github.com/kelaberetiv/TagUI/tree/master/src/samples/6_datatables)|set of flows uses datatables to retrieve and act on GitHub info
[7_testing](https://github.com/kelaberetiv/TagUI/blob/master/src/samples/7_testing)|shows how to use check step assertions for CI/CD integration
[8_hastebin](https://github.com/kelaberetiv/TagUI/blob/master/src/samples/8_hastebin)|used by upload option to upload flow result to hastebin.com
[9_misc](https://github.com/kelaberetiv/TagUI/blob/master/src/samples/9_misc)|shows how to use steps popup, frame, dom, js, { and } block
[a_facedetect](https://github.com/kelaberetiv/TagUI/blob/master/src/samples/a_facedetect)|uses face recognition to detect profile images on webpages

# [Using it (flexible)](https://github.com/kelaberetiv/TagUI#to-use)
*In this section, we'll spend some time exploring a particular feature of TagUI that you'll like to try out.*

### OPTION 1 - STEPS DESCRIPTION (15 minutes)
- TagUI auto-waits for a webpage element to appear and interacts with it as soon as it appears
- Element identifier can be auto-recorded using TagUI Chrome extension, or [found from web browser](https://help.surveygizmo.com/help/how-to-find-element-ids-to-use-with-javascript)
- Identifiers help to pinpoint which webpage elements you want to interact with ([examples in flow samples](https://github.com/kelaberetiv/TagUI#flow-samples))
- TagUI auto-selects provided identifier in this order - xpath, css, id, name, class, title, aria-label, text(), href

<details>
  <summary>
    Click to show the steps that can be used in TagUI automation flows and what the steps are used for
  </summary>
  
  Basic Step|Parameters (separator in bold)|Purpose
  :---------|:-----------------------------|:------
  http(s)://|just enter full url of webpage ('+variable+' for variable)|go to specified webpage
  click|element to click|click on an element
  rclick|element to right-click|right-click on an element
  dclick|element to double-click|double-click on an element
  hover|element to hover|move cursor to element
  type|element ***as*** text ([enter] = enter, [clear] = clear field)|enter element as text
  select|element to select ***as*** option value ([clear] = clear selection)|choose dropdown option
  read|element to read (page = webpage) ***to*** variable name|fetch element text to variable
  show|element to read (page = webpage, ie raw html) |print element text to output
  save|element (page = webpage) ***to*** optional filename|save element text to file
  load|filename ***to*** variable name|load file content to variable
  echo|text (in quotation marks) and variables|print text/variables to output
  dump|text (in quotation marks) and variables ***to*** optional filename|save text/variables to file
  write|text (in quotation marks) and variables ***to*** optional filename|append text/variables to file
  variable_name| = value (for text, put in quotes, use + to concat)|define variable variable_name
  // (on new line)|user comments (ignored during execution)|add user comments
  tagui|relative or absolute filename (see MODULES section)|run another tagui flow
  ask|question or instruction for user (reply stored in ask_result)|ask user for input
  live|try steps or code interactively for Chrome / visual automation|enter live mode ([Firefox not yet](https://github.com/laurentj/slimerjs/issues/639))

  Tip - to use variables where text is expected, '+variable+' can be used. XPath is an expressive way to identify web elements. If you know xpath and use xpath for element identifier, use double quotes for text //\*[@title="Login"]

  Pro Step|Parameters (separator in bold)|Purpose
  :-------|:-----------------------------|:------
  snap|element (page = webpage) ***to*** optional filename|save screenshot to file
  snap (pdf)|page ***to*** filename.pdf (headless Chrome / PhantomJS)|save webpage to basic pdf
  table|element (XPath selector only) ***to*** optional filename.csv|save basic html table to csv
  wait|optional time in seconds (default is 5 seconds)|explicitly wait for some time
  check|condition **&#124;** text (in quotes) if true **&#124;** text (in quotes) if false|check condition and print result
  upload|element (CSS selector only) ***as*** filename to upload|upload file to website
  download|url to download ***to*** filename to save|download from url to file
  receive|url keyword to watch ***to*** filename to save|receive resource to file
  frame|frame name **&#124;** subframe name if any|next step or block in frame/subframe
  popup|url keyword of new tab window to look for|next step or block in new tab window
  { and }|use { to start block and } to end block (on new line)|define block of steps and code
  api|full url (including parameters) of api call|call api & save response to api_result
  run|OS shell command including parameters|run OS command & save to run_result
  dom|javascript code for document object model|run code in dom & save to dom_result
  js|javascript statements (skip auto-detection)|treat as JS code explicitly
  r|R statements for big data and machine learning|run R statements & save to r_result
  py|python code for big data and machine learning|run python code & save to py_result
  vision|custom visual automation commands|run custom sikuli commands
  timeout|time in seconds before step errors out|change auto-wait timeout

  Tip - for headless and visible Chrome, file downloads can be done using normal webpage interaction or specifying the URL as a navigation flow step. For Firefox and PhantomJS, the download and receive step can be used. As TagUI default execution context is local, to run javascript on webpage dom (eg document.querySelector) use dom step. Set dom_json variable to pass a variable for use in dom step. Or dom_json = {tmp_number: phone, tmp_text: name} to pass multiple variables for use in dom step (dom_json.tmp_number and dom_json.tmp_text). On Windows, snap step requires display magnification to be set at 100% to work properly.

  For steps run, dom, js, r, py, vision, instead of typing the step and the command, you can use something like py begin followed by many lines of py code, and end with py finish to denote an entire code block. This saves typing the step repeatedly for a large integration code block. For steps r, py, vision, the helper functions r_step(), py_step(), vision_step() can be used to pass dynamic variables to those integrations. Below is an example for py step for passing dynamically generated varibles from TagUI to Python integration. Indentation of Python code within py begin-finish and vision begin-finish blocks is supported, for example in conditions or loops.

  ```
  phone = 1234567
  name = 'donald duck'
  py_step('phone = ' + phone)
  py_step('name = "' + name + '"')

  py print(name)
  echo py_result
  py print(phone)
  echo py_result
  ```

</details>

### OPTION 2 - NATIVE LANGUAGES (15 minutes)
To run TagUI flows in native languages or output flow execution in other languages ([see demo run](https://github.com/kelaberetiv/TagUI/issues/68#issuecomment-344380657))
1. set your default flow language with [tagui_language variable](https://github.com/kelaberetiv/TagUI/blob/master/src/tagui_config.txt) in tagui_config.txt
2. write automation flow in native language base on [language definition .csv files](https://github.com/kelaberetiv/TagUI/tree/master/src/languages)
3. optionally set tagui_language in flow to any other languages as output language

<details>
  <summary>
    Click to show the 20+ human languages supported by TagUI and how to self-build language definitions
  </summary>
  
  Tip - as Windows Unicode support is not as straightforward as macOS/Linux, doing this in Windows may require changing system locale, using chcp command, and selecting a font to display native language correctly ([more info](http://www.walkernews.net/2013/05/19/how-to-get-windows-command-prompt-displays-chinese-characters/))
  
  TagUI language engine supports over 20 languages and can be modified or extended easily by users to improve accuracy or add more languages. The languages are Bengali, Chinese, English, French, German, Hindi, Hungarian, Indonesian, Italian, Japanese, Korean, Polish, Portuguese, Romanian, Russian, Serbian, Spanish, Tagalog, Tamil, Thai, Vietnamese. This starting set is partly chosen base on the [list of most commonly used languages](https://www.babbel.com/en/magazine/the-10-most-spoken-languages-in-the-world), partly from the countries around where I'm from (Singapore), and partly from countries with a lot of developers.

  If your native language is not in the above list, you can also automate building a new native language definition by using this language [build automation flow](https://github.com/kelaberetiv/TagUI/blob/master/src/languages/build) (src/languages/build) that self-builds the vocabulary set using Google Translate. To do that, update [build.csv](https://github.com/kelaberetiv/TagUI/blob/master/src/languages/build.csv) with the languages that you want to build and run `tagui build using chrome` in src/languages folder. Use quiet option to hide the verbose automation output. The generated files are named as their 2-character language codes to prevent overwriting existing language definitions by accident. To use the generated .csv files, rename them to their full language names. See [full list of languages possible](https://cloud.google.com/translate/docs/languages) to be generated by Google Translate.

  Most of the language definitions are automatically self-built using Google Translate (except english.csv and chinese.csv), and would be wrong without understanding vocabulary used in UI interaction context. Native language users can update the language definition csv themselves and are welcome to submit PRs with correct words to be used. Some languages are very different from English structure (for eg, written from right to left, different order of adjective and noun) and would be impossible to use correctly in TagUI.

</details>

### OPTION 3 - VISUAL AUTOMATION (15 minutes)
TagUI has built-in integration with [Sikuli (base on OpenCV)](http://sikulix.com) to allow identifying web elements and desktop user interface elements for interaction. Steps that support visual automation are click, hover, type, select, read, show, save, snap. Simply specify an image filename (.png or .bmp format) of what to look for visually, in place of the element identifier, to use visual automation alongside your usual automation steps. Also, by using vision step, you can send custom Sikuli commands to do things such as [typing complex keystroke sequences](https://github.com/kelaberetiv/TagUI/issues/155#issuecomment-397403024).

<details>
  <summary>
    Click to show where to download and install Sikuli, additional usage details and a demo GIF
  </summary>
  
  Sikuli is excluded from TagUI packaged installation due to complex dependencies that are handled by its installer. First, make sure [Java JDK v8](http://www.oracle.com/technetwork/java/javase/downloads/jdk8-downloads-2133151.html) is installed. [Download Sikuli](http://sikulix.com/quickstart/) to tagui/src/tagui.sikuli folder and setup (choose option 1 - Pack1). If you have download error messages during setup, [unzip contents of this file](https://raw.githubusercontent.com/tebelorg/Tump/master/Sikuli-1.1.3.zip) to tagui/src/tagui.sikuli folder, right-click sikulixsetup-1.1.3.jar and open or run as administrator. On Windows, make sure display magnification is set to 100%.

  To type onto the screen instead of a particular element, use `type page.png as text` or `type page.bmp as text`. To do a snapshot or an OCR of the whole screen, use `page.png` or `page.bmp` as the element identifier for steps snap or read respectively. The usual helper functions visible() / present() can also be used to check whether an image is visible on the screen. Relative paths are supported for image filenames (eg pc.png, images/button.bmp). A screen (real or Xvfb) is needed for visual automation. [Tesseract OCR](https://github.com/tesseract-ocr/tesseract) (optical character recognition) is used for visually retrieving text.

  ![Sample Visual Automation](https://raw.githubusercontent.com/tebelorg/Tump/master/visual_flow.gif)

</details>

### OPTION 4 - PYTHON INTEGRATION (15 minutes)
TagUI has built-in integration with Python (works out of the box for both v2 & v3) - a programming language with many popular frameworks for big data and machine learning. The py step can be used to run commands in Python and retrieve the output of those commands. To use Python integration in TagUI, first [download Python for your OS](https://www.python.org/). macOS and Linux normally come pre-installed with Python. Make sure that python command is accessible from command prompt.

<details>
  <summary>
    Click to show how to use py step in your automation flow to send and receive data from Python frameworks
  </summary>

  In your automation flow, use the py step followed by the Python commands to be executed, separated by `;`. You can then use the `print` command in Python to output the result you want to be accessible in your automation flow as `py_result` variable. For a super basic example, below steps in your TagUI automation flow will output 3. If the result is JSON data, the JSON object `py_json` will be created for easy access to JSON data elements. If not, `py_json` will be null.

  ```
  // using py step to denote Python code, and getting back output from py_result
  py a=1;b=2
  py c=a+b
  py print(c)
  echo py_result
  
  // alternatively, you can use py begin and py finish to denote a Python code block
  // indentation of Python code is also supported, for example in conditions or loops
  py begin
  a=1;b=2
  c=a+b
  print(c)
  py finish
  echo py_result
  
  // an example of passing dynamically generated variables to Python integraton
  phone = 1234567
  name = 'donald duck'
  py_step('phone = ' + phone)
  py_step('name = "' + name + '"')

  py print(name)
  echo py_result
  py print(phone)
  echo py_result
  ```
  You can also use the `execfile()` command in Python v2.X to run Python scripts. Or use `exec(open('filename').read())` in [Python v3.X to run Python scripts](https://stackoverflow.com/questions/436198/what-is-an-alternative-to-execfile-in-python-3). For examples of using Python for machine learning, check out this [essentials of machine learning algorithms](https://www.analyticsvidhya.com/blog/2017/09/common-machine-learning-algorithms/) article or this article on [Python deep learning frameworks](https://www.kdnuggets.com/2017/02/python-deep-learning-frameworks-overview.html).
  
</details>

### OPTION 5 - R INTEGRATION (15 minutes)
TagUI has built-in integration with R - an open-source software environment for statistical computing and graphics. R can be used for big data and machine learning. The r step can be used to run commands in R and retrieve the output of those commands. To use R integration in TagUI, first [download R software for your OS](https://www.r-project.org/). Make sure that Rscript command is accessible from your command prompt (added to path or symbolically linked).

<details>
  <summary>
    Click to show how to use r step in your automation flow to send and receive data from R frameworks
  </summary>
  
  In your automation flow, use the r step followed by the R commands to be executed, separated by `;`. You can then use the `cat()` command in R to output the result you want to be accessible in your automation flow as `r_result` variable. For a super basic example, below steps in your TagUI automation flow will output 3. If the result is JSON data, the JSON object `r_json` will be created for easy access to JSON data elements. If not, `r_json` will be null.
  
  ```
  // using r step to denote R code, and getting back output from r_result
  r a=1;b=2
  r c=a+b
  r cat(c)
  echo r_result
  
  // alternatively, you can use r begin and r finish to denote a R code block
  r begin
  a=1;b=2
  c=a+b
  cat(c)
  r finish
  echo r_result
  
  // an example of passing dynamically generated variables to R integraton
  phone = 1234567
  name = 'donald duck'
  r_step('phone = ' + phone)
  r_step('name = "' + name + '"')

  r cat(name)
  echo r_result
  r cat(phone)
  echo r_result
  ```

  You can also use the `source()` command in R to run R scripts. For examples of using R for machine learning, check out this [essentials of machine learning algorithms](https://www.analyticsvidhya.com/blog/2017/09/common-machine-learning-algorithms/) article or this [guerilla guide to machine learning](https://www.kdnuggets.com/2017/05/guerrilla-guide-machine-learning-r.html) video series.

</details>

### OPTION 6 - CLI ASSISTANT (15 minutes)
TagUI scripts are already in natural-language-like syntax to convert to JavaScript code. What's even better is having natural-language-like syntax on the command line. Instead of typing `tagui download_bank_report june creditcard` to run the automation flow download_bank_report with parameters june creditcard, you can type `erina download my june creditcard bank report`. This may be more intuitive than recalling which automation filename you saved to run. For a demo of the CLI (command line interface) assistant in action, [see this video](https://www.youtube.com/watch?v=Sm4WNQ89gRA).

<details>
  <summary>
    Click to show details on how you can rename your CLI assistant and the syntax used to invoke automations
  </summary>
  
  The commands erina (macOS/Linux) and erina.cmd (Windows) can be renamed to some other name you like. The commands can be set up in the same way as the tagui / tagui.cmd above to be accessible from any folder. The command basically interprets this general syntax `erina single-word-action fillers options/parameters fillers single-or-multi-word-context` to call run the corresponding automation flow `action_context` with `options/parameters`.

  Also, adding `using chrome` / `using headless` / `using firefox` at the end will let it run using the respective browsers. The default location where automation flows are searched for is in tagui/flows folder and can be changed in tagui_helper.php. Filler words (is, are, was, were, my, me) are ignored as they don't convey important information ([more design info](https://github.com/kelaberetiv/TagUI/issues/44#issuecomment-321108786)).

</details>

# [Scripting Reference](https://github.com/kelaberetiv/TagUI#cheat-sheet)
*Click above link to see the list of TagUI steps and other advanced features.*

# [Developers Reference](https://github.com/kelaberetiv/TagUI#developers-reference)
*Click above link to see information on APIs and summary of various TagUI files.*
