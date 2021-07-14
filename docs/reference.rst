Reference
======================
Use this section to look up information on steps, helper functions and run options you can use. You can explore using the navigation headers.


.. _step-reference:

Steps
------------------
The steps you can use in TagUI are listed here.


Mouse and Keyboard
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
| Can use :ref:`DOM <dom>`, :ref:`XPath <xpath>` identifiers. 

.. code-block:: none

  upload [DOM/XPath of upload input element] as [filename]

*Examples*

.. code-block:: none
  
  upload //input[@name="attach"] as report.csv


api
#####################
Call a web API and save the response to the variable ``api_result``.

.. code-block:: none

  api https://some-api-url

*Examples*

.. code-block:: none
  
  api https://api.github.com/repos/kelaberetiv/TagUI/releases
  js obj = JSON.parse(api_result)
  js author = obj[0].author.login


Using Variables
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

| When using text in the value, surround the text in quotes, like "some text". This is actually treated by TagUI as JavaScript, so you can assign numbers to variables or use other JavaScript functions. The variable name needs to be a single word and cannot start with a number.

*Examples*

.. code-block:: none

  count = 5
  username = "johncleese"
  fullname = firstname + lastname


File Saving/Loading 
***********************

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

.. _dump:

dump
#####################
Saves text to a new file.

.. code-block:: none

  dump [text] to [filename]
  dump [`variable`] to [filename]

See :ref:`dump <dump>` for examples.


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
Runs code in the browser dom and saves the stdout to the variable ``dom_result``.

.. code-block:: none

  dom [JavaScript statement to run in the DOM]

  dom begin
  [JavaScript statements to run in the DOM]
  dom finish

*Examples*

.. code-block:: none

  dom intro = document.getElementById("intro")


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
Adds a comment.

.. code-block:: none

  // [comment]

*Examples*

.. code-block:: none

  // updates the forex rates


telegram
###################
Sends a Telegram notification, for example, to update on automation completion or exception.

First, message `@taguibot <https://t.me/taguibot>`_ to authorise it to send messages to your Telegram app. Then in TagUI:

.. code-block:: none

  telegram id message

Note that telegram step requires an internet connection. This feature is being hosted at https://tebel.org, but the `source code <https://github.com/kelaberetiv/TagUI/tree/master/src/telegram>`_ is on GitHub if you wish to host this feature on your own cloud or server. The implementation is in pure PHP without any dependencies.

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


-quiet or -q
********************
Runs without output to command prompt except for explicit output (echo, show, check steps and errors etc).

-edge or -e
********************
Runs using Microsoft Edge web browser instead of Google Chrome (can be used with -headless option).

my_datatable.csv
********************
Uses the specified csv file as the datatable for batch automation. See :ref:`datatables <datatables>`.


input(s)
********************
Add your own parameter(s) to be used in your automation flow as variables p1 to p8.


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
