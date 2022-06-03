Reference
======================
Use this section to look up information on steps, helper functions and run options you can use. You can explore using the navigation headers.


.. _step-reference:

Steps
------------------
The steps you can use in TagUI are listed here.


Mouse and keyboard
********************

.. _click:

click
##################
| Left clicks on the identifier.
| Can use :ref:`DOM <dom>`, :ref:`XPath <xpath>`, :ref:`Point <point>`, :ref:`Image <image>` identifiers.

.. code-block:: none
  
  click [DOM/XPath/Point/Image]

*Examples*

.. code-block:: none
  
  click Main concepts
  click //nav/div/div[2]/ul/li[4]/ul/li[1]/a
  click (500,200)
  click button.png


rclick
##################
| Right clicks on the identifier.
| Can use :ref:`DOM <dom>`, :ref:`XPath <xpath>`, :ref:`Point <point>`, :ref:`Image <image>` identifiers. 

.. code-block:: none
  
  rclick [DOM/XPath/Point/Image]

See :ref:`click <click>` for examples.


dclick
##################
| Double left clicks on the identifier.
| Can use :ref:`DOM <dom>`, :ref:`XPath <xpath>`, :ref:`Point <point>`, :ref:`Image <image>` identifiers. 

.. code-block:: none
  
  dclick [DOM/XPath/Point/Image]

See :ref:`click <click>` for examples.


hover
##################
| Moves mouse cursor to the identifier.
| Can use :ref:`DOM <dom>`, :ref:`XPath <xpath>`, :ref:`Point <point>`, :ref:`Image <image>` identifiers. 

.. code-block:: none
  
  hover [DOM/XPath/Point/Image]

See :ref:`click <click>` for examples.


type
##################
| Types into a web input. You can use [clear] to clear the field and [enter] to hit the Enter key. 
| Can use :ref:`DOM <dom>`, :ref:`XPath <xpath>`, :ref:`Point <point>`, :ref:`Image <image>` identifiers. 

.. code-block:: none

  type [DOM/XPath/Point/Image] as [text to type]

*Examples*

.. code-block:: none
  
  type search-term as John Wick
  type //input[@name="search"] as John Wick
  type (500,200) as John Wick
  type input_field.png as John Wick

  type search-term as [clear]John Wick[enter]
  type //input[@name="search"] as [clear]John Wick[enter]
  type (500,200) as [clear]John Wick[enter]
  type input_field.png as [clear]John Wick[enter]


keyboard
##################
Enters keystrokes directly.

.. code-block:: none

  keyboard [keys]

You can use the following special keys:

- [shift] [ctrl] [alt] [win] [cmd] [enter]
- [space] [tab] [esc] [backspace] [delete] [clear]
- [up] [down] [left] [right] [pageup] [pagedown] 
- [home] [end] [insert] [f1] .. [f15] 
- [printscreen] [scrolllock] [pause] [capslock] [numlock]

*Examples*

.. code-block:: none
  
  keyboard [win]run[enter]
  keyboard [printscreen]
  keyboard [ctrl]c
  keyboard [tab][tab][tab][enter]

  keyboard [cmd][space]
  keyboard safari[enter]
  keyboard [cmd]c


mouse
####################
| Explicitly sends a mouse event at the current mouse position.
| In most cases, you want you use :ref:`click <click>` instead.

.. code-block:: none

  mouse down
  mouse up


Web
*******************

visit
#####################
Visits the provided URL.

.. code-block:: none
  
  [URL]

*Examples*

.. code-block:: none
  
  https://google.com

select
#####################
| Selects a dropdown option in a web input.
| Can use :ref:`DOM <dom>`, :ref:`XPath <xpath>` identifiers. 

.. code-block:: none

  select [DOM/XPath of select input element] as [option value or text]

*Examples*

.. code-block:: none
  
  select variant as blue


table
#####################
Saves table data to a csv file, base on the table number on webpage or its :ref:`XPath <xpath>` identifier.

.. code-block:: none

  table [table number] to [filename.csv]
  table [XPath] to [filename.csv]

*Examples*

.. code-block:: none
  
  table 1 to regional_exchange_rates.csv
  table (//table)[2] to global_exchange_rates.csv
  table //table[@name='report'] to report.csv

popup
#####################
Modifies the next steps to be run in a new tab.

.. code-block:: none

  popup [unique part of new tab's URL]
    [steps]

*Examples*

.. code-block:: none
  
  popup confirm
    click Confirm


frame
#####################
Modifies the next steps to use the DOM or XPath in a frame or subframe.

.. code-block:: none

  frame [frame name]
    [steps]

  frame [frame name] | [subframe name]
    [steps]

*Examples*

.. code-block:: none
  
  frame navigation
    click Products

  frame main | register
    click Register


download to
#####################
| Specifies a location to store file downloads. The default location is the folder of the TagUI flow.

.. code-block:: none

  download to [folder location]

upload
#####################
| Uploads file to a website.
| Only :ref:`DOM <dom>` identifier can be used.

.. code-block:: none

  upload [DOM of upload element] as [filename]

*Examples*

.. code-block:: none
  
  upload #element_id as report.csv


api
#####################
Call a web API and save the raw response to the variable ``api_result``.

If the response is in JSON, ``api_json`` will automatically be created.

.. code-block:: none

  api https://some-api-url

*Examples*

.. code-block:: none
  
  api https://api.github.com/repos/kelaberetiv/TagUI/releases
  echo `api_result`
  author = api_json[0].author.login

For an advanced example of using api step, setting POST/GET method, header and body, `see this example from aito.ai <https://aito.document360.io/docs/tagui>`_ - a web-based machine learning solution for no-coders and RPA developers. In the example, ``api`` step is used to make a machine-learning inference to generate the account code of an invoice item, based on its description and price. aito.ai's free tier comes with 2000 API calls/month and it works perfectly with TagUI.

Excel
********************

Perform read, write, copy, delete actions on Excel files using standard Excel formula like this one ``[workbook]sheet!range``. This feature works with both Windows and Mac Excel apps. `See this link <https://github.com/kelaberetiv/TagUI/issues/1081#issuecomment-902058917>`_ for notes of passed test cases and known limitations for this feature. To access a password-protected Excel file, use ``excel_password = 'password'``.

variables
###################
You can use variables in your Excel formula, for eg ``range`` or ``sheet``. Various Excel file formats are supported, be sure to put the file's .extension as part of the formula so that TagUI can recognise that the instruction is an Excel step instead of some JavaScript code.

.. code-block:: none

  [`workbook`.xlsx]`sheet`!`range` = 123
  data = [`workbook`.xlsx]`sheet`!`range`

visibility
###################
By default, the Excel app will be opened and run in the background. If you want the automated actions on Excel to be in focus in foreground, you can set it with ``excel_focus = true`` in your workflow. Use ``excel_focus = false`` to set it off again in your workflow.

For some usage scenarios, you might not even want the Excel app to be visible in the background. In that case, you can set ``excel_visible = false`` in your workflow to run Excel invisibly. Use ``excel_visible = true`` to make Excel visible again in your RPA workflow.

read
###################
Read data from Excel files. Both relative and absolute file paths supported. Error will be shown if the specified file or sheet does not exist. In below line, range can be a cell or range in Excel.

.. code-block:: none

  variable = [workbook]sheet!range

Reading columns and rows can be done using standard Excel formula for range, for example A:A (column A), B:D (columns B to D), 2:2 (row 2), 3:5 (rows 3 to 5). There is no standard Excel formula for selecting the entire range of a sheet, so you will have to provide the actual range required.

*Examples*

.. code-block:: none

  top_profit = [Monthly Report.xlsx]August!E10
  top_salesman = [Monthly Report.xlsx]August!E11
  data_array = [Quarterly Metrics.xlsx]Main!B3:G100
  data_array = [C:\Reports\June.xls]Sheet1!A1:C2

  data_array = [C:\Reports\June.xls]Sheet1!A:A
  data_array = [C:\Reports\June.xls]Sheet1!B:D
  data_array = [C:\Reports\June.xls]Sheet1!2:2  
  data_array = [C:\Reports\June.xls]Sheet1!3:5

TagUI's backend language is JavaScript, thus data_array can be used just like a JavaScript array.

.. code-block:: none

  // to work on data in data_array cell by cell
  for row from 0 to data_array.length-1
    for col from 0 to data_array[row].length-1
      echo `data_array[row][col]` 

Note - There was a limitation on reading multiple rows and columns, for eg B:D and 3:5 (data array returned will be a 1 x N array instead of the correct row x column array). This is now fixed in v6.87. Get your copy with ``tagui update`` command or from MS Word Plug-in ``Update TagUI`` button.

write
###################
Write data to Excel files. Both relative and absolute file paths supported. If the specified file does not exist, a new file will be created. If the sheet does not exist, a new sheet will be created. If the data is an array, the given cell will be used as the top-left cell to write the range of cells.

.. code-block:: none

  [workbook]sheet!cell = variable

*Examples*

.. code-block:: none

  [Monthly Report.xlsx]August!E10 = 12345
  [Monthly Report.xlsx]August!E11 = "Alan"
  [Monthly Report.xlsx]August!E12 = variable
  [Quarterly Metrics.xlsx]Main!B3 = data_array

TagUI's backend language is JavaScript, thus range data can be defined just like a JavaScript array.

.. code-block:: none

  // to assign a set of range data with 2 rows of 3 columns
  [C:\Reports\June.xls]Sheet1!A1 = [[1, 2, 3], [4, 5, 6]]
  [C:\Reports\June.xls]Sheet1!A1 = [[variable_1, variable_2, variable_3], [4, 5, 6]]

  // example spreadsheet data with #, name and country
  [Participants.xlsx]Sheet1!A1 = [['1', 'John', 'USA'], [2, 'Jenny', 'Russia'], [3, 'Javier', 'Serbia']]

  // get the next row count for the example spreadsheet
  column_A = [Participants.xlsx]Sheet1!A:A
  next_row = column_A.length + 1

  // write a new row accordingly to example spreadsheet
  [Participants.xlsx]Sheet1!A`next_row` = [[next_row, 'Janice', 'Brazil']]

copy
###################
Copy data across Excel files. Both relative and absolute file paths supported. Error will be shown if the specified source file or sheet does not exist. If the specified destination file does not exist, a new file will be created. If the destination sheet does not exist, a new sheet will be created. If the data is an array, the given cell will be used as the top-left cell to write the range of cells.

.. code-block:: none

  [workbook]sheet!cell = [workbook]sheet!range 

*Examples*

.. code-block:: none

  [Monthly Report.xlsx]August!A1 = [Jennifer Report.xlsx]August!A1
  [Monthly Report.xlsx]August!A1 = [Jennifer Report.xlsx]August!A1:E200

delete
###################
Delete data in Excel files. Both relative and absolute file paths supported. Error will be shown if the specified file or sheet does not exist. Delete a range of cells by assigning an empty array to it.

.. code-block:: none

  [workbook]sheet!cell = ""

*Examples*

.. code-block:: none

  [Monthly Report.xlsx]August!E10 = ""
  [Quarterly Metrics.xlsx]Main!A1 = [["", "", ""], ["", "", ""]]

Word
********************

You can read the text contents of a Microsoft Word document simply by assigning its filename to a variable as follows. TagUI will automate Microsoft Word to copy out the text contents and assign to the variable. Note that you need to have Microsoft Word installed on your computer. This feature works for both Windows and Mac.

*Examples for Windows*

.. code-block:: none

  word_text = [Research Report.docx]
  word_text = [C:\Users\Jennifer\Desktop\Report.docx]
  word_text = [FY2021 Reports\Research Report.docx]

  filename = 'C:\\Users\\Jennifer\\Desktop\\Report'
  word_text = [`filename`.docx]
  filename = 'Research Report'
  word_text = [`filename`.docx]

*Examples for Mac*

.. code-block:: none

  word_text = [Research Report.docx]
  word_text = [/Users/jennifer/Desktop/Report.docx]
  word_text = [FY2021 Reports/Research Report.docx]

  filename = '/Users/jennifer/Desktop/Report'
  word_text = [`filename`.docx]
  filename = 'Research Report'
  word_text = [`filename`.docx]

After reading the text content into a variable, you can process it using TagUI's helper functions such as get_text() and del_chars() to retrieve specific information required for your RPA scenario. Standard JavaScript functions can also be used to do string processing, for more information google ``javascript how to xxxx``. After reading the text content from a Word document, TagUI will close Microsoft Word and continue with the rest of the automation steps.

PDF
********************

You can read the text contents of a PDF file simply by assigning its filename to a variable as follows. TagUI will automate the PDF viewer app to copy out the text contents and assign to the variable. On Windows, you will need the free `Adobe Acrobat Reader <https://get.adobe.com/reader/>`_ and set it as your default PDF viewer. On Mac, TagUI will use the default Preview app that can already view PDF files.

*Examples for Windows*

.. code-block:: none

  pdf_text = [Research Report.pdf]
  pdf_text = [C:\Users\Jennifer\Desktop\Report.pdf]
  pdf_text = [FY2021 Reports\Research Report.pdf]

  filename = 'C:\\Users\\Jennifer\\Desktop\\Report'
  pdf_text = [`filename`.pdf]
  filename = 'Research Report'
  pdf_text = [`filename`.pdf]

*Examples for Mac*

.. code-block:: none

  pdf_text = [Research Report.pdf]
  pdf_text = [/Users/jennifer/Desktop/Report.pdf]
  pdf_text = [FY2021 Reports/Research Report.pdf]

  filename = '/Users/jennifer/Desktop/Report'
  pdf_text = [`filename`.pdf]
  filename = 'Research Report'
  pdf_text = [`filename`.pdf]

After reading the text content into a variable, you can process it using TagUI's helper functions such as get_text() and del_chars() to retrieve specific information required for your RPA scenario. Standard JavaScript functions can also be used to do string processing, for more information google ``javascript how to xxxx``. After reading the text content from a PDF file, TagUI will close the PDF viewer and continue with the rest of the automation steps.

Using variables
********************

read
###################
| Gets some text or value and stores it in a variable.
| Can use :ref:`DOM <dom>`, :ref:`XPath <xpath>`, :ref:`Region <region>`, :ref:`Image <image>` identifiers. 

.. code-block:: none

  read [DOM/XPath/Region/Image] to [variable]

When you provide a Region or Image identifier, TagUI uses OCR (Optical Character Recognition) to read the characters from the screen.

*Examples*

.. code-block:: none

  read //p[@id="address"] to address

  read //p[@id="address"]/@class to address-class

  read (500,200)-(600,400) to id-number

  read frame.png to email


assign
###################
Saves text to a variable.

.. code-block:: none

  [variable] = [value]

| When using text in the value, surround the text in quotes, like "some text". This is actually treated by TagUI as JavaScript, so you can assign numbers to variables or use other JavaScript functions. The variable name needs to be a single word and cannot start with a number, and it is case sensitive.

*Examples*

.. code-block:: none

  count = 5
  username = "johncleese"
  fullname = firstname + lastname


access
###################

| To access the value of a variable in most steps, surround the variable in backticks, like `` `my_variable` ``. The following is an example of accessing a variable in the echo step.

*Examples*

.. code-block:: none

  my_variable = "hello world"
  echo `my_variable`
  // output: hello world

  
| However, in certain steps like `if conditions <https://tagui.readthedocs.io/en/latest/main_concepts.html#if-conditions>`_, `for loops <https://tagui.readthedocs.io/en/latest/main_concepts.html#for-loops>`_ and `helper functions <https://tagui.readthedocs.io/en/latest/main_concepts.html#helper-functions>`_, the variable can be accessed directly without backticks. For more information, see docs for the relevant step.

*Examples*

.. code-block:: none 
  
  a = "hello"
  b = "world" 
  if a equals to b
    echo same
    // output:
	
  my_variable = 3
  for n from 1 to my_variable
    echo `n`
    // output: 1
    // 2
    // 3

  my_variable = "some text to copy"
  clipboard(my_variable)


concatenation
###################
| To concatenate variables, the syntax varies depending on whether you are doing so within a step. The following examples show the difference between concatenating variables within and outside the echo step.

*Examples*

.. code-block:: none
  
  echo `a` `b`
  // output: hello world
  
  a = "hello"
  b = "world"
  c = a + " " + b
  echo `c`
  // output: hello world


File saving/loading 
***********************

.. _dump:

dump
#####################
Saves text to a new file.

.. code-block:: none

  dump [text] to [filename]
  dump [`variable`] to [filename]

.. code-block:: none

  // creates blank CSV file with header
  dump First Name,Last Name to names.csv

write
#####################
Saves a new line of text to an existing file.

.. code-block:: none

  write [text] to [filename]
  write [`variable`] to [filename]

*Examples*

.. code-block:: none

  write firstname,lastname to names.csv
  write `fullreport` to report.txt


load
#####################
Loads file content to a variable.

.. code-block:: none

  load [filename] to [variable]

*Examples*

.. code-block:: none
  
  load report.txt to report


snap
######################
| Saves a screenshot of the whole page, an element or a region.
| Can use :ref:`DOM <dom>`, :ref:`XPath <xpath>`, :ref:`Region <region>`, :ref:`Image <image>` identifiers. 

.. code-block:: none

  snap [DOM/XPath/Region/Image/page] to [filename]

If you use ``page`` as the identifier, it takes a screenshot of the whole webpage.

*Examples*

.. code-block:: none

  snap logo to logo.png

  snap page to webpage.png
  
  snap (0,0)-(100,100) to image.png


Showing output
********************

echo
###################
Shows some output on the command line.

.. code-block:: none

  echo [text]
  echo [`variable`]

*Examples*

.. code-block:: none

  echo Flow has started
  echo The user is `username`


show
###################
| Shows element text directly on the command line.
| Can use :ref:`DOM <dom>`, :ref:`XPath <xpath>` identifiers.

.. code-block:: none

  show [DOM/XPath]

*Examples*

.. code-block:: none

  show review-text


check
###################
Shows some output on the command line based on a :ref:`condition <if-statements>`.

.. code-block:: none

  check [condition] | [text if true] | [text if false]

*Examples*

.. code-block:: none

  check header_home_text equals to "Home" | "header text is correct" | "header text is wrong"


Custom code
********************

js
####################
Runs JavaScript code explicitly. TagUI has direct access to the JavaScript variables.

.. code-block:: none

  js [JavaScript statement]

  js begin
  [JavaScript statements]
  js finish

*Examples*

.. code-block:: none

  js obj = JSON.parse(api_result)
  dump `obj` to result.json

  js begin
  obj = JSON.parse(api_result)  
  randomInteger = Math.floor(Math.random() * Math.floor(5)) + 1
  js finish
  dump `obj` to result.json
  
  // declare and initilise variable to use it inside/outside js code block
  a = ""
  js begin
  a = "some string"
  js finish
  echo `a`

py
####################
Runs Python code and saves the stdout to the variable ``py_result`` as a string.

.. code-block:: none

  py [Python statement]

  py begin
  [Python statements]
  py finish

*Examples*

.. code-block:: none

  py result = 2 + 3
  py print(result)
  echo `py_result`

  py begin
  import random
  random_integer = random.randint(1,6)
  print(random_integer)
  py finish
  echo `py_result`

:ref:`See this link <python>` for more examples and usage patterns on running Python code.

run
####################
Runs a command in Command Prompt or Terminal and saves the stdout to the variable ``run_result``.

.. code-block:: none

  run [shell command]

*Examples*

.. code-block:: none

  run cmd /c mkdir new_directory


vision
####################
Runs Sikuli code.

.. code-block:: none

  vision [Sikuli statement] 

  vision begin
  [Sikuli statements]
  vision finish

*Examples*

.. code-block:: none

  vision click("button1.png")


dom
####################
Runs code in the browser dom and saves returned value to the variable ``dom_result``.

.. code-block:: none

  dom [JavaScript statement to run in the DOM]

  dom begin
  [JavaScript statements to run in the DOM]
  dom finish

*Examples*

.. code-block:: none

  // goes back to previous page
  dom window.history.back()

  // returns text of an element
  dom return document.querySelector('#some_id').textContent


r
####################
Runs R statements and saves the stdout to the variable ``r_result``.

.. code-block:: none

  r [R statement]

  r begin
  [R statements]
  r finish


Miscellaneous
********************

wait
####################
Explicitly wait for some time.

.. code-block:: none

  wait [seconds to wait]
  wait [seconds to wait] s
  wait [seconds to wait] seconds

*Examples*

.. code-block:: none

  wait 5.5
  wait 10 s
  wait 20 seconds


timeout
####################
Changes the auto-wait timeout when waiting for web elements to appear (default 10 seconds).

.. code-block:: none

  timeout [seconds to wait before timeout]

*Examples*

.. code-block:: none

  timeout 300


ask
####################
Prompts user for input and saves the input as the variable ``ask_result``.

.. code-block:: none

  ask [prompt]

*Examples*

.. code-block:: none

  ask What is the date of the receipt? (in DD-MM-YYYY)
  type search as `ask_result`


live
###################
Run steps interactively and immediately see the output. The user must enter "done" before the flow continues.

.. code-block:: none

  live


tagui
####################
Runs another TagUI flow. Checks the flow's folder.

.. code-block:: none

  tagui [flow file]
  tagui [folder/flow file]

*Examples*

.. code-block:: none

  tagui update-forex.tag
  tagui flows/update-forex.tag


comment
###################
Adds a comment. If you are inside a code block, for example an if condition or for loop, be sure to indent your comment accordingly to let TagUI run correctly after it converts into JavaScript code.

.. code-block:: none

  // [comment]

*Examples*

.. code-block:: none

  // updates the forex rates


telegram
###################
Sends a Telegram notification, for example, to update on automation completion or exception.

First, message `@taguibot <https://t.me/taguibot>`_ to authorise it to send messages to your Telegram. Then in TagUI:

.. code-block:: none

  telegram [id] [message]

*Examples*

.. code-block:: none

  // this line sends message to Telegram user with ID 1234567890, \n means a new line
  telegram 1234567890 Hello Alena,\n\nYour HR onboarding bot has completed successfully.

  // show telegram_result variable - 'success' means sent, 'fail' means sending failed
  echo Telegram message - `telegram_result`

  // if condition to check telegram_result 'success' or 'fail' and handle accordingly
  if telegram_result equals to 'success'
    echo Message sent successfully.
  else
    echo Message sending failed.

Note that the telegram step requires an internet connection. This feature is being hosted at https://tebel.org, but the `source code <https://github.com/kelaberetiv/TagUI/tree/master/src/telegram>`_ is on GitHub if you wish to host this feature on your own cloud or server. The implementation is in pure PHP without any dependencies.

The only info logged is chat_id, length of the message, datetime stamp (to prevent abuse). If you wish to host on your own, first read through this link to learn more about Telegram Bot API, creating your bot API token and setting up the webhook - https://core.telegram.org/bots


Run options
----------------------
You can use the below options when running ``tagui``. 

For example, the command below runs ``my_flow.tag`` without showing the web browser, while storing the flow run result in ``tagui_report.csv``. ::
    
    tagui my_flow.tag -headless -report

-deploy or -d
********************
Deploys a flow, creating a shortcut which can be double-clicked to run the flow. If the flow file is moved, a new shortcut must be created. The flow will be run with all the options used when creating the shortcut.

-headless or -h
********************
Runs the flow with an invisible Chrome web browser (does not work for visual automation).

-nobrowser or -n
********************
Runs without any web browser, for example to perform automation only with visual automation.

-report or -r
********************
Tracks flow run result in ``tagui/src/tagui_report.csv`` and saves html logs of flows execution.

-turbo or -t
********************
Run automation at 10X the speed of normal human user. Read caveats at Advanced concepts!

-quiet or -q
********************
Runs without output to command prompt except for explicit output (echo, show, check steps and errors etc). To have fine-grained control on showing and hiding output during execution (eg hiding password from showing up), use ``quiet_mode = true`` and ``quiet_mode = false`` in your flow.

-edge or -e
********************
Runs using Microsoft Edge browser instead of Chrome (can be used with -headless option).

my_datatable.csv
********************
Uses the specified csv file as the datatable for batch automation. See :ref:`datatables <datatables>`.

input(s)
********************
Add your own parameter(s) to be used in your automation flow as variables p1 to p8.

For example, from the command prompt, below line runs ``register_attendence.tag`` workflow using Microsoft Edge browser and with various student names as inputs. ::

    tagui register_attendence.tag -edge Jenny Jason John Joanne

Inside the workflow, the variables ``p1``, ``p2``, ``p3``, ``p4`` will be available for use as part of the automation, for example to fill up student names into a web form for recording attendence. The following lines in the workflow will output various student names given as inputs. ::

    echo `p1`
    echo `p2`
    echo `p3`
    echo `p4`

See :doc:`other deprecated options </dep_options>`.


.. _helper-functions-reference:

Helper functions
--------------------

csv_row()
********************
Formats an array for writing to csv file.

*Examples*

.. code-block:: none
  
  read name_element to name
  read price_element to price
  read details_element to details
  write `csv_row([name, price, details])` to product_list.csv


count()
********************
Gets the number of elements matching the identifier specified. Note that the identifier needs to be in single quotes ``''``.

*Examples*

.. code-block:: none
  
  rows = count('table-rows')


clipboard()
********************
Puts text onto the clipboard, or gets the clipboard text (if no input is given).

*Examples*

.. code-block:: none

  clipboard('some text')
  keyboard [ctrl]v

  keyboard [ctrl]c
  contents = clipboard()
  

url()
********************
Gets the URL of the current web page.

*Examples*

.. code-block:: none

  if url() contains 'success'
    click button1


title()
********************
Gets the title of the current web page.

*Examples*

.. code-block:: none

  if title() contains 'Confirmation'
    click button1


text()
********************
Gets all text content of the current web page.

*Examples*

.. code-block:: none

  if text() contains 'success'
    click button1


timer()
********************
Gets the time elapsed in seconds in between each running of this function.

*Examples*

.. code-block:: none

  timer()
  click button1
  click button2
  click button3
  echo `timer()`


.. _exist:

exist()
********************
| Waits until the timeout for an element to exist and returns a JavaScript ``true`` or ``false`` depending on whether it exists or not.
| Note that the identifier is surrounded by quotes.
| Can use :ref:`DOM <dom>`, :ref:`XPath <xpath>`, :ref:`Image <image>` identifiers.

.. code-block:: none
  
  exist('[DOM/XPath/Image]')

*Examples*

.. code-block:: none
  
  if exist('//table')
    click button1


present()
********************
| Same as :ref:`exist() <exist>` except that it does not wait until the timeout and immediately returns ``true`` or ``false``.
| Note that the identifier is surrounded by quotes.
| Can use :ref:`DOM <dom>`, :ref:`XPath <xpath>`, :ref:`Image <image>` identifiers.

*Examples*

.. code-block:: none
  
  if present('//table')
    click button1


mouse_xy()
********************
| Gets the x, y coordinates of the current mouse position.
| Particularly useful in :ref:`live mode <live-mode>`.

*Examples*

.. code-block:: none

  echo `mouse_xy()`


mouse_x()
********************
Gets the x coordinate of the current mouse position as a number, eg 200.

*Examples*

.. code-block:: none

  hover element.png
  x = mouse_x() + 200
  y = mouse_y()
  click (`x`,`y`)


mouse_y()
********************
Gets the y coordinate of the current mouse position as a number, eg 200.

*Examples*

.. code-block:: none

  hover element.png
  x = mouse_x() + 200
  y = mouse_y()
  click (`x`,`y`)

get_files()
********************
Returns an array of files and folders in a given folder. Both relative and absolute paths supported.

*Examples*

.. code-block:: none

  // list of files in the same folder as the flow file
  list = get_files('.')

  // list of files in the Desktop folder of user Alan
  // note double backslash because of JavaScript string
  list = get_files('C:\\Users\\Alan\\Desktop')

  // alternatively, use single forward slash instead
  list = get_files('C:/Users/Alan/Desktop')

  // showing the list of files after retrieving it
  // JavaScript array start from 0 for 1st element
  for n from 0 to list.length-1
    echo `list[n]`

  // checking to process a specific file extension
  for n from 0 to list.length-1
    if list[n] contains '.XLSX'
      echo `list[n]`

get_text()
********************
Extracts text between 2 provided anchors from given text. Optional 4th parameter for occurrence during multiple matches (for example 3 to tell the function to return the 3rd match found).

*Examples*

.. code-block:: none

  pdf_text = 'Name: John State: Texas City: Plano Contact: ...'

  name = get_text(pdf_text, 'Name:', 'State:')
  state = get_text(pdf_text, 'State:', 'City:')
  city = get_text(pdf_text, 'City:', 'Contact:')

  echo `name`, `state`, `city`


del_chars()
********************
Cleans data by removing provided character(s) from given text and returning the result.

*Examples*

.. code-block:: none

  pdf_text = 'Name: John\n State: Texas\t City: Plano\n Contact: ...'
  echo `del_chars(pdf_text, '\n\t:')`


get_env()
********************
Returns the value of given environment variable from the operating system.

*Examples*

.. code-block:: none

  // getting %USERPROFILE% variable for Windows
  echo `get_env('USERPROFILE')`
  home_dir = get_env('USERPROFILE')

  // getting $HOME variable for Mac or Linux
  echo `get_env('HOME')`
  home_dir = get_env('HOME')
