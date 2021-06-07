<img src="https://raw.githubusercontent.com/kelaberetiv/TagUI/master/src/media/tagui_logo.png" height="111" align="right">

# TagUI

**Free RPA tool by [AI Singapore](https://www.aisingapore.org), a government-funded programme to accelerate AI. To start, click the download link below. Join the community or ask any questions at [our Telegram](https://t.me/rpa_chat).**

**[Download TagUI v6.14](https://tagui.readthedocs.io/en/latest/setup.html)&ensp;|&ensp;[Usage Guide](https://tagui.readthedocs.io/en/latest/index.html)&ensp;|&ensp;[Demos](https://github.com/aimakerspace/TagUI-Bricks)&ensp;|&ensp;[Samples](https://github.com/kelaberetiv/TagUI/tree/master/flows/samples)&ensp;|&ensp;[Video](https://youtu.be/hKc4eNBhMws)&ensp;|&ensp;[Slides](https://drive.google.com/file/d/1pltAMzr0MZsttgg1w2ORH3ontR6Q51W9/view?usp=sharing)&ensp;|&ensp;[Zoom Q&A](https://github.com/kelaberetiv/TagUI/issues/914)**

---

![TagUI Users](https://raw.githubusercontent.com/kelaberetiv/TagUI/master/src/media/tagui_users.png)

Write flows in simple TagUI language and automate away repetitive time-consuming tasks on your computer. The TagUI project is [open-source and free forever](https://www.linkedin.com/posts/kensoh_sneak-preview-of-tagui-ms-word-plug-in-v3-activity-6796860165338595328-02wD). It's easy to setup and use, and works on Windows, macOS and Linux. Besides English, flows can be written in [20 other languages](https://github.com/kelaberetiv/TagUI/tree/master/src/languages), so you can do RPA using your [native language](https://github.com/kelaberetiv/TagUI/blob/master/flows/samples/8_chineseflow.tag).

You can use TagUI [MS Office Plug-ins](https://github.com/kelaberetiv/TagUI/tree/master/src/office) ([video demo](https://www.linkedin.com/posts/kensoh_rpa-tagui-activity-6775824220200017920-bxhA)) to easily create and deploy Word doc as RPA robots, and set up RPA data parameters using Excel. See below preview of upcoming MS Word Plug-in v3 - there will be a toolbar to add steps and a snapshot button to create image snapshots for computer vision RPA. You can also run TagUI easily on your phone web browser using [Google's free cloud](https://github.com/kelaberetiv/TagUI/issues/913). VS Code users, install this [TagUI language extension](https://www.linkedin.com/posts/kensoh_hi-vs-code-folks-who-love-rpa-now-you-can-activity-6805445134034042880--PWT).

![Word v3 Preview](https://raw.githubusercontent.com/kelaberetiv/TagUI/master/src/office/word/word_v3_preview.png)

TagUI has a bustling user community, and extended community champions create new RPA tools for their own communities, based on TagUI. Python users will [pip install rpa](https://github.com/tebelorg/RPA-Python) for the #1 Python RPA package. TagUI for [C# .NET](https://www.nuget.org/packages/tagui), [Java](https://www.linkedin.com/posts/kensoh_hi-c-net-folks-interested-in-rpa-happy-activity-6800991305251078144-Gx4T), and [Go](https://www.linkedin.com/posts/kensoh_hi-fans-of-go-programming-language-would-activity-6804658389772324864-_OgH) programming languages are being built. For event-driven RPA with thousands of connectors, check out TagUI [module for Node-RED](https://flows.nodered.org/node/node-red-contrib-tagui), a popular free and open-source workflow automation tool.

For Microsoft [Power Automate Desktop](https://flow.microsoft.com/en-us/desktop/) users, you'll be happy to know that there's [2-way integration](https://www.linkedin.com/posts/kensoh_tagui-activity-6773236538596831232-1aFu) with TagUI out of the box (for business continuity if you switch between the 2 apps). Do also check out other leading open-source RPA tools, to see if they meet your needs better - [OpenRPA](https://github.com/open-rpa/openrpa) & [OpenFlow](https://github.com/open-rpa/openflow), [OpenBots](https://www.linkedin.com/posts/openbots_openbots-studio-demo-support-for-tag-ui-activity-6788174021964943361-RrUD), [Robocorp](https://youtu.be/HAfQpNZVbKI). All of them support orchestrating and running TagUI robots from their orchestrator.

![TagUI Roadmap](https://raw.githubusercontent.com/kelaberetiv/TagUI/master/src/media/roadmap.png)

In TagUI language, you use steps like `click` and `type` to interact with identifiers, which include web identifiers, image snapshots, screen coordinates, or even text using OCR. Below is an example to login to Xero accounting:

```
https://login.xero.com/identity/user/login

type email as user@gmail.com
type password as 12345678
click Log in
```
```
// besides web identifiers, images of UI elements can be used

type email_box.png as user@gmail.com
type password_box.png as 12345678
click login_button.png
```
```
// (x,y) coordinates of user-interface elements can also be used

type (720,400) as user@gmail.com
type (720,440) as 12345678
click (720,500)
```

To get started, see this [installation guide](https://tagui.readthedocs.io/en/latest/setup.html). Join the community or ask any questions at [our Telegram chat group](https://t.me/rpa_chat).

# v6 Features

### TagUI live mode
You can run live mode directly for faster development by running `tagui live` on the command line.

### Click text using OCR
TagUI can now click on the screen with visual automation just using text input, by using OCR technology.

```
click v6 Features using ocr
```

### Deploy flows to run when double clicked
You can now create a shortcut for a flow, which can be moved to your desktop and double-clicked to run the flow. The flow will be run with all the options used when creating the shortcut.

```
$ tagui my_flow.tag -deploy
OR
$ tagui my_flow.tag -d
```

### Running flows with options can be done with abbreviations
For example, you can now do ``tagui my_flow.tag -h`` instead of ``tagui my_flow.tag -headless``.

# Migrating to v6

### Mandatory .tag file extension
All flow files must have a .tag extension.

### Options must be used with a leading hyphen (-)
When running a flow with options, prefix a - to the options.

Before v6:
```
$ tagui my_flow.tag headless
```

After v6:
```
$ tagui my_flow.tag -headless
OR
$ tagui my_flow.tag -h
```

### Change in syntax for echo, dump, write steps
The echo, dump and write steps are now consistent with the other steps. They no longer require quotes surrounding the string input. Instead, variables now need to be surrounded by backticks.

Before v6:
```
echo 'This works!' some_text_variable
```

After v6:
```
echo This works! `some_text_variable`
```

### If and loop code blocks can use indentation instead of curly braces {}
This increases readability and ease of use. Just indent your code within the if and loop code blocks. 

Before v6:
```
if some_condition
{
do_some_step_A
do_some_step_B
}
```

After v6:
```
if some_condition
  do_some_step_A
  do_some_step_B
```

# TagUI v5.11
[See the old homepage](https://github.com/kelaberetiv/TagUI/tree/pre_v6) for technical details of TagUI, such as architecture diagram and codebase structure

# Credits
- [TagUI v3.0](https://github.com/kensoh/TagUI/tree/before_aisg) - Ken Soh from Singapore
- [SikuliX](http://sikulix.com) - Raimund Hocke from Germany
- [CasperJS](http://casperjs.org) - Nicolas Perriault from France
- [PhantomJS](https://github.com/ariya/phantomjs) - Ariya Hidayat from Indonesia
- [SlimerJS](https://slimerjs.org) - Laurent Jouanneau from France

# Sponsor
This project  is supported by the [National Research Foundation](https://www.nrf.gov.sg), Singapore under its AI Singapore Programme (AISG-RP-2019-050). Any opinions, findings and conclusions or recommendations expressed in this material are those of the author(s) and do not reflect the views of National Research Foundation, Singapore.

