Reference
======================
Use this section to look up information on steps, helper functions and run options you can use.


.. _step-reference:

Steps
------------------
The steps you can use in TagUI are listed here.


Mouse and Keyboard
********************

.. _click:

click
##################
Left clicks on the identifier.

.. code-block:: none
  
  click [identifier]


rclick
##################
Left clicks on the identifier.

.. code-block:: none
  
  click [identifier]


dclick
##################
Double left clicks on the identifier.

.. code-block:: none
  
  dclick [identifier]


hover
##################
Move mouse cursor to the identifier.

.. code-block:: none
  
  hover [identifier]


type
##################
Types into a web input.

.. code-block:: none

  type [web identifier] as some-text
  type [web identifier] as [clear]some-text[enter]


keyboard
##################
Enters keystrokes.

.. code-block:: none

  keyboard [ctrl][home]
  keyboard [win]run[enter]
  keyboard [printscreen]
  keyboard [ctrl]c
  keyboard [tab][tab][tab][enter]

  keyboard [cmd][space]
  keyboard safari[enter]
  keyboard [cmd]c

You can use the following special keys:

[shift] [ctrl] [alt] [cmd] [win] [meta] [clear] [space] [enter] [backspace] [tab] [esc] [up] [down] [left] [right] [pageup] [pagedown] [delete] [home] [end] [insert] [f1] .. [f15] [printscreen] [scrolllock] [pause] [capslock] [numlock]


mouse
####################
Explicitly sends a mouse event to the screen.

In most cases, you want you use :ref:`click <click>`.

.. code-block:: none

  mouse down
  mouse up


Web
*******************

visit
#####################
Visits the provided URL.

.. code-block:: none
  
  https://somewebsite.com


select
#####################
Selects a dropdown option in a web input.

.. code-block:: none

  select [web identifier] as some-option-value


table
#####################
Saves html table data to a csv file.

.. code-block:: none

  table [XPath] to some-filename.csv


popup
#####################
Modifies the next steps to be run in a new tab.

.. code-block:: none

  popup some-part-of-popup-url
  {
    some steps
  }


frame
#####################
Modifies the next steps to be run in a frame or subframe.

.. code-block:: none

  frame some-frame-name
  frame some-frame-name | some-subframe-name


download
#####################
Downloads and saves a file at a URL.

.. code-block:: none

  download some-file-url to some-filename.pdf


upload
#####################
Uploads file to a website.

.. code-block:: none

  upload some-web-identifier-for-file-input as some-filename.csv


receive
#####################
Receives a resource to file

.. code-block:: none

  receive some-url-keyword to some-filename.pdf


api
#####################
Call a web API and save the response to the variable ``api_result``.

.. code-block:: none

  api https://some-api-url


Using Variables
********************

read
###################
Gets some text or value and stores it in a variable.

.. code-block:: none

  read [X-Y region coordinates] to some-variable
  read [XPath/@some-attribute] to some-variable


assign
###################
Saves text to a variable.

.. code-block:: none

  some-variable = "some-text" + some-variable


File Saving/Loading 
***********************

write
#####################
Saves a new line of text to an existing file.

.. code-block:: none

  write "some-text" to some-filename.csv
  write some-variable to some-filename.csv


dump
#####################
Saves text to a new file.

.. code-block:: none

  dump "some-text" to some-filename.csv
  dump some-variable to some-filename.csv


load
#####################
Loads file content to a variable.

.. code-block:: none

  some-filename to some-variable


snap
######################
Saves a screenshot of the whole page, an element or a region.

.. code-block:: none

  snap [XPath] to some-filename.png
  snap [X-Y region coordinates] to some-filename.png
  snap page to some-filename.png
  snap page to some-filename.pdf


Showing output
********************

echo
###################
Shows some output on the command line.

.. code-block:: none

  echo "some-text"
  echo some-variable


show
###################
Shows element text directly on the command line.

.. code-block:: none

  show [identifier]
  show page


check
###################
Shows some output on the command line based on a condition.

.. code-block:: none

  check some-condition | text-if-condition-is-true | text-if-condition-is-false


Custom code
********************

js
####################
Runs JavaScript code explicitly.

.. code-block:: none

  js some JavaScript statement


py
####################
Runs Python code and saves the stdout to the variable ``py_result``.

.. code-block:: none

  python some Python statement


run
####################
Runs a command in Command Prompt or Terminal and saves the stdout to the variable ``run_result``.

.. code-block:: none

  run some shell command


vision
####################
Runs Sikuli code.

.. code-block:: none

  vision some Sikuli statement


dom
####################
Runs code in the browser dom and saves the stdout to the variable ``dom_result``.

.. code-block:: none

  dom some javascript code


r
####################
Runs R statements and saves the stdout to the variable ``r_result``.

.. code-block:: none

  r some R statement


Miscellaneous
********************

wait
####################
Explicitly wait for some time.

.. code-block:: none

  wait 10
  wait 15 s
  wait 20 sec


timeout
####################
Changes the auto-wait timeout when waiting for web elements to appear.

.. code-block:: none

  timeout 300


ask
####################
Prompts user for input and saves the input as the variable ``ask_result``

.. code-block:: none

  ask What is the date of the receipt? (in DD-MM-YYYY)


live
###################
Wait for user confirmation before continuing.

.. code-block:: none

  live


tagui
####################
Runs another TagUI flow.

.. code-block:: none

  tagui some-flow.tag
  tagui a-folder/some-flow.tag


comment
###################
Adds a comment.

.. code-block:: none

  // some comment


Run options
----------------------
You can use the below options when running ``tagui``. 

For example, the command below runs ``my_flow.tag`` without showing the web browser, while storing the flow run result in ``tagui_report.csv``. ::
    
    tagui my_flow.tag headless report

headless
********************
Runs the flow without a visible browser (does not work for visual automation).


report
********************
Tracks flow run result in ``tagui/src/tagui_report.csv`` and saves html logs of flows.


my_datatable.csv
********************
Uses the specified csv file as the datatable. See :ref:`datatables <datatables>`.


speed
********************
Runs a datatable flow, skipping the default 3s delay and restarting of Chrome between datatable iterations.

See :doc:`other deprecated options </dep_options>`.


.. _helper-functions-reference:

Helper functions
--------------------

csv_row()
********************
Formats an array for writing to csv file.

.. code-block:: none
  
  read name_element to name
  read price_element to price
  read details_element to details
  write csv_row([name, price, details]) to product_list.csv

count()
********************
Gets the number of elements matching the identifier specified. Note that the identifier needs to be in single quotes ``''``.

.. code-block:: none
  
  rows = count('table-rows')

clipboard()
********************
Puts text onto the clipboard, or gets the clipboard text (if no input is given).

.. code-block:: none

  clipboard('some text')
  keyboard [ctrl]v

.. code-block:: none

  keyboard [ctrl]c
  contents = clipboard()
  

url()
********************
Gets the URL of the current web page.

.. code-block:: none

  if url() contains 'success'
  {
    some steps
  }


title()
********************
Gets the title of the current web page.

.. code-block:: none

  if title() contains 'Confirmation'
  {
    some steps
  }


text()
********************
Gets all text content of the current web page.

.. code-block:: none

  if text() contains 'success'
  {
    some steps
  }


timer()
********************
Gets the time elapsed in seconds in between each running of this function.

.. code-block:: none

  timer()
  some steps
  echo timer()


mouse_xy()
********************
Gets the x, y coordinates of the current mouse position.

.. code-block:: none

  echo mouse_xy()


mouse_x()
********************
return x coordinate as integer number, for eg 200

.. code-block:: none

  hover element.png
  x = mouse_x() + 200
  y = mouse_y()
  click (`x`,`y`)

mouse_y()
********************
return y coordinate as integer number, for eg 200

.. code-block:: none

  hover element.png
  x = mouse_x() + 200
  y = mouse_y()
  click (`x`,`y`)

