<img src="https://raw.githubusercontent.com/kelaberetiv/TagUI/master/src/media/tagui_logo.png" height="111" align="right">

# TagUI

**Free RPA tool by [AI Singapore](https://www.aisingapore.org), a government-funded programme to accelerate AI. For any support issues or questions, [please tell us here](https://github.com/kelaberetiv/TagUI/issues) and we'll try to reply [within 24 hours](https://youtu.be/gZiTPjpD-gQ).**

**[Download TagUI v6.14](https://tagui.readthedocs.io/en/latest/setup.html)&ensp;|&ensp;[Documentation](https://tagui.readthedocs.io/en/latest/index.html)&ensp;|&ensp;[Demos](https://github.com/aimakerspace/TagUI-Bricks)&ensp;|&ensp;[Samples](https://github.com/kelaberetiv/TagUI/tree/master/flows/samples)&ensp;|&ensp;[Slides](https://drive.google.com/file/d/1pltAMzr0MZsttgg1w2ORH3ontR6Q51W9/view?usp=sharing)&ensp;|&ensp;[Feedback](https://forms.gle/mieY66xTN4NNm5Gq5)&ensp;|&ensp;[Live Q&A](https://github.com/kelaberetiv/TagUI/issues/914)**

---

Write flows in simple TagUI language and automate your web, mouse and keyboard interactions on the screen.

TagUI is free and open-source. It's easy to setup and use, and works on Windows, macOS and Linux. Besides English, flows can also be written in [20 other languages](https://github.com/kelaberetiv/TagUI/tree/master/src/languages), so go ahead and do RPA using your [native language](https://github.com/kelaberetiv/TagUI/blob/master/flows/samples/8_chineseflow.tag).

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

[Visit v5.11 homepage](https://github.com/kelaberetiv/TagUI/tree/pre_v6) for technical details of TagUI, such as its architecture diagram and codebase structure

# Credits
- [TagUI v3.0](https://github.com/kensoh/TagUI/tree/before_aisg) - Ken Soh from Singapore
- [SikuliX](http://sikulix.com) - Raimund Hocke from Germany
- [CasperJS](http://casperjs.org) - Nicolas Perriault from France
- [PhantomJS](https://github.com/ariya/phantomjs) - Ariya Hidayat from Indonesia
- [SlimerJS](https://slimerjs.org) - Laurent Jouanneau from France

# Sponsor
This project  is supported by the [National Research Foundation](https://www.nrf.gov.sg), Singapore under its AI Singapore Programme (AISG-RP-2019-050). Any opinions, findings and conclusions or recommendations expressed in this material are those of the author(s) and do not reflect the views of National Research Foundation, Singapore.
