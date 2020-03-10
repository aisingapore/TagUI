<img src="https://raw.githubusercontent.com/kelaberetiv/TagUI/master/src/media/tagui_logo.png" height="111" align="right">

# TagUI

**TagUI is a command-line tool for digital process automation (RPA).**

### [Download TagUI v6.0.0](https://tagui.readthedocs.io/en/latest/setup.html)

### [Visit the documentation](https://tagui.readthedocs.io/en/latest/index.html)

Write flows (scripts) in simple TagUI language and automate your web, mouse and keyboard interactions on the desktop.

TagUI is free to use and open-source. It's easy to setup and use and works on Windows, macOS and Linux.

In TagUI language, you use _steps_ like `click` and `type` and interact with _identifiers_, which includes images, screen coordinates, or even text using OCR:

```
https://www.typeform.com

click login
type username as user@gmail.com
type password as 12345678
click btnlogin

download https://admin.typeform.com/xxx to report.csv
```

### v6 Features

#### TagUI live mode
You can run TagUI live mode directly for faster development by running `tagui live` on the command line.


#### Click using OCR
TagUI can now click on the screen with visual automation just using text input, using OCR.

```
click v6 Features using ocr
```

#### Deploy flows to run when double clicked
You can now create a shortcut for a flow, which can be moved to your desktop and double-clicked to run the flow. The flow will be run with all the options used when creating the shortcut.

```
$ tagui my_flow.tag -deploy
OR
$ tagui my_flow.tag -d
```

#### Running flows with options can be done with abbreviations
You can now do ``tagui my_flow.tag -h`` instead of ``tagui my_flow.tag -headless``. This applies to all run options.


### Migrating to v6

#### Mandatory .tag extension
All flow files must have a .tag extension.

#### Options must be used with a leading hyphen(-)
When running a flow with options, prepend a - to the options.

Before v6:
```
$ tagui my_flow.tag headless
```

v6:
```
$ tagui my_flow.tag -headless
OR
$ tagui my_flow.tag -h
```

#### echo, dump, write steps
The echo, dump and write steps are now consistent with the other steps. They no longer require quotes surrounding the string input. Variables need to be surrounded by backticks.

Before v6:
```
echo 'This works!' some_text_variable
```

v6:
```
echo This works! `some_text_variable`
```

#### If and loop code blocks can use indentation instead of curly braces {}
This increases readability and ease of use. Just indent your code within the if and loop code blocks (similar to in Python). 

Before v6:
```
if some_condition
{
  do_some_step_A
  do_some_step_B
}
```

v6:
```
if some_condition
  do_some_step_A
  do_some_step_B
```

#### [Visit the older TagUI v5.11 documentation/release page](https://github.com/kelaberetiv/TagUI/tree/pre_v6)

This project is supported by the National Research Foundation, Singapore under its AI Singapore Programme (AISG-RP-2019-050).