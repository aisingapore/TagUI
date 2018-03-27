TagUI is a general purpose tool for automating user interactions. This branch of automation is commercially known as RPA (robotic process automation), and primarily aims to reproduce user interactions with computer applications - for example mouse clicks and keyboard entries.

For more information on TagUI, visit its [repository page](https://github.com/kelaberetiv/TagUI). TagUI is now maintained by [AI Singapore](https://www.aisingapore.org), a Singapore government-funded initiative to build local artificial intelligence capabilities. The intention is to add AI capabilities to TagUI while keeping it open-source and free to use.

# [Setup](https://github.com/kelaberetiv/TagUI#set-up)
In this section, we'll download and install TagUI on your computer.

### INSTALLATION (10 minutes)
No setup is needed, in most environments all required dependencies are packaged in.

Platform|macOS|Linux|Windows|Node.js (macOS/Linux)
:------:|:---:|:---:|:-----:|:-------------------:
Package|[unzip and run](https://raw.githubusercontent.com/tebelorg/Tump/master/TagUI_macOS.zip)|[unzip and run](https://raw.githubusercontent.com/tebelorg/Tump/master/TagUI_Linux.zip)|[unzip and run](https://raw.githubusercontent.com/tebelorg/Tump/master/TagUI_Windows.zip)|[npm install tagui](https://www.npmjs.com/package/tagui)

Potential exceptions - On some Windows computers, if you see 'MSVCR110.dll is missing' error, install [this from Microsoft website](https://www.microsoft.com/en-us/download/details.aspx?id=30679) (choose vcredist_x86.exe) - this file is required to run the PHP engine. On some newer macOS versions, if you get a 'dyld: Library not loaded' error, [install OpenSSL in this way](https://github.com/kelaberetiv/TagUI/issues/86#issuecomment-372045221). For some flavours of Linux (Ubuntu for example), which do not have PHP pre-installed, google how to install PHP accordingly (Ubuntu for example, apt-get install php).

![TagUI Flowchart](https://raw.githubusercontent.com/tebelorg/TagUI/master/src/media/flowchart.png)

# [Using it (guided)](https://github.com/kelaberetiv/TagUI#to-use)
In this section, we'll have a guided walkthrough on running TagUI, using its Chrome extension, and some examples.

### COMMAND LINE (10 minutes)
```
./tagui flow_filename option(s) for macOS/Linux, tagui flow_filename option(s) for Windows
```
- Flow filename (and its .csv) can be a local file or the URL of an online file
- Automation flow filename can have no extension, .gui or .txt file extension
- Objects, keywords, datatables can be defined in flow_filename.csv (optional)

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

Now try the same workflow with Chrome browser by putting chrome as option (eg tagui samples\1_yahoo chrome). **Be sure to save your work and close all your existing tabs as Chrome will be restarted to establishe connection with TagUI.** The automation will now run in the foreground instead, so you'll be able to see the navigation on Yahoo and DuckDuckGo websites. TagUI can also be run from desktop icons, scheduled tasks, or REST API calls.

Tip - to run tagui from anywhere in macOS/Linux, use ln -sf /full_path/tagui/src/tagui /usr/local/bin/tagui to create symbolic link. to run tagui from anywhere in Windows, add tagui/src [folder to path](http://lmgtfy.com/?q=add+to+path+in+windows). then tagui will be accessible from any folder.

### CHROME EXTENSION (10 minutes)
Download from [Chrome Web Store](https://chrome.google.com/webstore/detail/tagui-web-automation/egdllmehgfgjebhlkjmcnhiocfcidnjk/) to use TagUI Chrome web browser extension for recording automation flows. TagUI Chrome extension is based on [Resurrectio tool](https://github.com/ebrehault/resurrectio) and records steps such as page navigation, clicking web elements and entering information. To start recording automation flows, click TagUI icon on your Chrome toolbar. Right-click for shortcuts to some TagUI steps, such as capturing webpage screenshot or to note down element identifier.

The recording is not foolproof (for example, the underlying recording engine cannot capture frames, popup windows or tab key input). It is meant to simplify flow creation with some edits, instead of typing everything manually. [See this video](https://www.youtube.com/watch?v=bFvsc4a8hWQ) for an example of recording sequence of steps, editing for adjustments and playing back the automation.

Sample website to try out the Chrome extension - https://www.m1.com.sg/personal/mobile/plans/reserveanumber

### FLOW SAMPLES (15 minutes)
Following automation flow samples ([tagui/src/samples folder](https://github.com/tebelorg/TagUI/tree/master/src/samples)) are included with TagUI

Flow Sample |Purpose
:-----------|:------
[1_yahoo](https://github.com/tebelorg/TagUI/blob/master/src/samples/1_yahoo)|searches github on Yahoo and captures screenshot of results
[2_twitter](https://github.com/tebelorg/TagUI/blob/master/src/samples/2_twitter)|goes to a Twitter page and saves some profile information
[3_github](https://github.com/tebelorg/TagUI/blob/master/src/samples/3_github)|goes to a GitHub page and downloads the repository file
[4_conditions](https://github.com/tebelorg/TagUI/blob/master/src/samples/4_conditions)|goes through examples of using conditions in natural language
[5_repositories](https://github.com/tebelorg/TagUI/blob/master/src/samples/5_repositories)|shows using repositories on Russian social media site VK.com
[6_datatables](https://github.com/tebelorg/TagUI/tree/master/src/samples/6_datatables)|set of flows uses datatables to retrieve and act on GitHub info
[7_testing](https://github.com/tebelorg/TagUI/blob/master/src/samples/7_testing)|shows how to use check step assertions for CI/CD integration
[8_hastebin](https://github.com/tebelorg/TagUI/blob/master/src/samples/8_hastebin)|used by upload option to upload flow result to hastebin.com
[9_misc](https://github.com/tebelorg/TagUI/blob/master/src/samples/9_misc)|shows how to use steps popup, frame, dom, js, { and } block

# [Using it (flexible)](https://github.com/kelaberetiv/TagUI#to-use)
In this section, we'll spend some time exploring a particular feature of TagUI that you'll like to try out.

### OPTION 1 - VISUAL AUTOMATION (15 minutes)
TagUI has built-in integration with [Sikuli (base on OpenCV)](http://sikulix.com) to allow identifying web elements and desktop user interface elements for interaction. Steps that support visual automation are tap / click, hover / move, type / enter, select / choose, read / fetch, show / print, save, snap. Simply specify an image filename (.png or .bmp format) of what to look for visually, in place of the element identifier, to use visual automation alongside your usual automation steps.

Sikuli is excluded from TagUI packaged installation due to complex dependencies that are handled by its installer. [Download Sikuli](http://sikulix.com/quickstart/) to tagui/src/tagui.sikuli folder and setup (choose option 1 - Pack1 and option 3 Tesseract based OCR). Relative paths are supported for image filenames (eg pc.png, images/button.bmp). A screen (real or Xvfb) is needed for visual automation. [Tesseract OCR](https://github.com/tesseract-ocr/tesseract) (optical character recognition) is used for visually retrieving text.

![Sample Visual Automation](https://raw.githubusercontent.com/tebelorg/Tump/master/visual_flow.gif)

### OPTION 2 - R INTEGRATION (15 minutes)
TagUI has built-in integration with R - an open-source software environment for statistical computing and graphics. R can be used for big data and machine learning. The r step can be used to run commands in R and retrieve the output of those commands. To use R integration in TagUI, first [download R software for your OS](https://www.r-project.org/).

In your automation flow, use the r step followed by the R commands to be executed, separated by `;`. You can then use the `cat()` command in R to output the result you want to be accessible in your automation flow as `r_result` variable. For a super basic example, below steps in your TagUI automation flow will output 3. If the result is JSON data, the JSON object `r_json` will be created for easy access to JSON data elements. If not, `r_json` will be null.

```
r a=1;b=2
r c=a+b
r cat(c)
echo r_result
```

You can also use the `source()` command in R to run R scripts. For examples of using R for machine learning, check out this [essentials of machine learning algorithms](https://www.analyticsvidhya.com/blog/2017/09/common-machine-learning-algorithms/) article or this [guerilla guide to machine learning](https://www.kdnuggets.com/2017/05/guerrilla-guide-machine-learning-r.html) video series.

### OPTION 3 - PYTHON INTEGRATION (15 minutes)
TagUI has built-in integration with Python v2.7 - a programming language with many popular frameworks for big data and machine learning. The py step can be used to run commands in Python and retrieve the output of those commands. To use Python integration in TagUI, first [download Python for your OS](https://www.python.org/). macOS and Linux normally come pre-installed with Python. Note that Python v3 is not backward-compatible with Python v2.7, the current and more widely used version.

In your automation flow, use the py step followed by the Python commands to be executed, separated by `;`. You can then use the `print` command in Python to output the result you want to be accessible in your automation flow as `py_result` variable. For a super basic example, below steps in your TagUI automation flow will output 3. If the result is JSON data, the JSON object `py_json` will be created for easy access to JSON data elements. If not, `py_json` will be null.

```
py a=1;b=2
py c=a+b
py print c
echo py_result
```
You can also use the `execfile()` command in Python to run Python scripts. For examples of using Python for machine learning, check out this [essentials of machine learning algorithms](https://www.analyticsvidhya.com/blog/2017/09/common-machine-learning-algorithms/) article or this article on [Python deep learning frameworks](https://www.kdnuggets.com/2017/02/python-deep-learning-frameworks-overview.html).

### OPTION 4 - NATIVE LANGUAGES (15 minutes)
To run TagUI flows in native languages or output flow execution in other languages ([see demo run](https://github.com/tebelorg/TagUI/issues/68#issuecomment-344380657))
1. set your default flow language with [tagui_language variable](https://github.com/tebelorg/TagUI/blob/master/src/tagui_config.txt) in tagui_config.txt
2. write automation flow in native language base on [language definition .csv files](https://github.com/tebelorg/TagUI/tree/master/src/languages)
3. optionally set tagui_language in flow to any other languages as output language

Tip - as Windows Unicode support is not as straightforward as macOS/Linux, doing this in Windows may require changing system locale, using chcp command, and selecting a font to display native language correctly ([more info](http://www.walkernews.net/2013/05/19/how-to-get-windows-command-prompt-displays-chinese-characters/))

TagUI language engine supports over 20 languages and can be modified or extended easily by users to improve accuracy or add more languages. The languages are Bengali, Chinese, English, French, German, Hindi, Hungarian, Indonesian, Italian, Japanese, Korean, Polish, Portuguese, Romanian, Russian, Serbian, Spanish, Tagalog, Tamil, Thai, Vietnamese. This starting set is partly chosen base on the [list of most commonly used languages](https://www.babbel.com/en/magazine/the-10-most-spoken-languages-in-the-world), partly from the countries around where I'm from (Singapore), and partly from countries with a lot of developers.

If your native language is not in the above list, you can also automate building a new native language definition by using this language [build automation flow](https://github.com/tebelorg/TagUI/blob/master/src/languages/build) (src/languages/build) that self-builds the vocabulary set using Google Translate. To do that, update [build.csv](https://github.com/tebelorg/TagUI/blob/master/src/languages/build.csv) with the languages that you want to build and run `tagui build using chrome` in src/languages folder. Use quiet option to hide the verbose automation output. The generated files are named as their 2-character language codes to prevent overwriting existing language definitions by accident. To use the generated .csv files, rename them to their full language names. See [full list of languages possible](https://cloud.google.com/translate/docs/languages) to be generated by Google Translate.

Most of the language definitions are automatically self-built using Google Translate (except english.csv and chinese.csv), and would be wrong without understanding vocabulary used in UI interaction context. Native language users can update the language definition csv themselves and are welcome to submit PRs with correct words to be used. Some languages are very different from English structure (for eg, written from right to left, different order of adjective and noun) and would be impossible to use correctly in TagUI.

### OPTION 5 - CLI ASSISTANT (15 minutes)
TagUI scripts are already in natural-language-like syntax to convert to JavaScript code. What's even better is having natural-language-like syntax on the command line. Instead of typing `tagui download_bank_report june creditcard` to run the automation flow download_bank_report with parameters june creditcard, you can type `erina download my june creditcard bank report`. This may be more intuitive than recalling which automation filename you saved to run. For a demo of the CLI (command line interface) assistant in action, [see this video](https://www.youtube.com/watch?v=Sm4WNQ89gRA).

The commands erina (macOS/Linux) and erina.cmd (Windows) can be renamed to some other name you like. The commands can be set up in the same way as the tagui / tagui.cmd above to be accessible from any folder. The command basically interprets this general syntax `erina single-word-action fillers options/parameters fillers single-or-multi-word-context` to call run the corresponding automation flow `action_context` with `options/parameters`.

Also, adding `using chrome` / `using headless` / `using firefox` at the end will let it run using the respective browsers. The default location where automation flows are searched for is in tagui/flows folder and can be changed in tagui_helper.php. Filler words (is, are, was, were, my, me) are ignored as they don't convey important information ([more design info](https://github.com/tebelorg/TagUI/issues/44#issuecomment-321108786)).

# [Scripting Reference](https://github.com/kelaberetiv/TagUI#cheat-sheet)
Click above link to see the list of TagUI steps and other advanced features.

# [Developers Reference](https://github.com/kelaberetiv/TagUI#developers-reference)
Click above link to see information on APIs and summary of various TagUI files.
