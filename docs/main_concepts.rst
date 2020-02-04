Main concepts
====================

Flows
--------------
TagUI automates your actions by running *flows*, which are just text files with the ``.tag`` file extension.

You can run a flow in the :ref:`Command Prompt/Terminal<how-to-use-command-prompt>` like this:: 

    tagui my_flow.tag

.. raw:: html

    <video playsinline autoplay muted loop width="100%">
        <source src="./_static/run-a-flow.mp4" type="video/mp4">
        Your browser does not support the video tag.
    </video>

TagUI looks for ``my_flow.tag`` in your current working directory. You can also provide the full path to your flow::

    tagui c:\tagui\samples\1_yahoo.tag chrome

You can also :ref:`run flows on a fixed schedule <run-on-schedule>`.


Run by double-click
**********************
You can create a shortcut file with::

  tagui my_flow.tag deploy

This creates a shortcut to run your flow just by double clicking the shortcut. The shortcut is in the same folder as your flow.

.. raw:: html

    <video playsinline autoplay muted loop width="100%">
        <source src="./_static/deploy-a-flow.mp4" type="video/mp4">
        Your browser does not support the video tag.
    </video>


Run from a URL
**********************
You can also run a flow directly from a URL::

    tagui https://raw.githubusercontent.com/kelaberetiv/TagUI/master/src/samples/1_yahoo chrome


Hide the browser
**********************
You can run web flows without showing the web browser by running TagUI with the ``headless`` option. ::

    tagui my_flow.tag headless

This allows you to continue using your desktop normally while the flow is running, but it will not work if your flow uses visual automation, because visual automation reads and clicks what is on your screen.

.. raw:: html

    <video playsinline autoplay muted loop width="100%">
        <source src="./_static/run-headless-flow.mp4" type="video/mp4">
        Your browser does not support the video tag.
    </video>


Steps
---------
Flows are made out of *steps*. Below are some common steps. You can see all the steps in the :ref:`step reference <step-reference>`.


click
**********
One of the most common steps is click. You can use it to click on a web element:

.. code-block:: none

  click Getting started

This command tells TagUI to try to click on any element which has “Getting started” as it’s “id”, “name”, “class” or “title” attributes (:ref:`How to find an element’s attributes <element_attributes>`), or as a last resort, has “Getting started” in its text.

This method usually works for targeting what you want, but you can be more explicit by providing an XPath. XPath is a powerful way to choose which web element you want to target. Use it like this:

.. code-block:: none

  click //a[@class="icon icon-home"]

You can also click on a certain point on your screen:

.. code-block:: none

  click (500,300)

Here, 500 and 300 are x-y coordinates. This command clicks on a point which is 500 pixels from the left of your screen and 300 pixels from the top of your screen. A good way to discover which coordinates to input is to use the ``mouse_xy()`` :ref:`helper function <helper-functions>` in live mode.

Lastly, you can use visual automation to click where it matches a previously saved image. This command looks for button.png in the same folder as your flow, then looks for a similar image on your screen, and clicks it:

.. code-block:: none

  click button.png

It’s often a good idea to keep your flows and images organised. You can create a folder (eg. named images) for your images and use the image like this instead:

.. code-block:: none

  click image/button.png


visit
**********
You can visit a webpage simply by entering the url:

.. code-block:: none

  https://somewebsite.com


type
**********
You can type into web inputs. This command finds the element “some-input” in the same way as for the **click** step and types “some-text” into it:

.. code-block:: none

  type some-input as some-text

You can use [clear] to clear the input and [enter] to hit the Enter key:

.. code-block:: none

  type some-input as [clear]some-text[enter]

You can also use an image as the target, just like with the **click** step:

.. code-block:: none

  type some-input.png as some-text


assign
**********
You can assign values into variables. This makes them easier to reference and work with.

This example uses the ``count()`` :ref:`helper function <helper-functions>`, counts the number of elements found with id/name/text with ‘row’ in them and assigns it to a variable ``row_count`` for later use:

.. code-block:: none

  row_count = count('row')


read
**********
The **read** step allows you to save text from web elements or from the screen into a variable.

This command finds the element “some-element” and saves its value into a variable called “some-variable”:

.. code-block:: none

  read some-element to some-variable

**read** can also use visual automation and OCR to read text from a region of your screen. The output from this may not be completely accurate as it relies on OCR.

This command reads all the text in the rectangle formed between the points (300,400) and (500,550):

.. code-block:: none

  read (300,400)-(500,550) to some-variable

You can also use XPath to read some attribute values from web elements. This command reads the id attribute from the element:

.. code-block:: none

  read //some-element/@some-attribute to some-variable


If statements
---------------
You may want your flow to do something different depending on some factors. You can use an if statement to do this.

For example, if the URL contains the word “success”, then we want to click some buttons:

.. code-block:: none

  if url() contains "success"
  {
    click button1.png
    click button2.png
  }

``url()`` is a :ref:`helper function <helper-functions>` that gets the url of the current webpage. Note the use of ``{`` and ``}``. The steps within these curly braces will only be run if the condition is met, ie. the url contains the word “success”.

Another common case is to check if some element exists. Here, we say that “if some-element doesn’t appear, then visit this webpage”.

.. code-block:: none

  if !present('some-element')
  {
    https://tagui.readthedocs.io/
  }

The ! negates the condition and comes from JavaScript, which TagUI code eventually translates to.

In this next example, we check if a variable row_count, which we assigned a value earlier, is equal to 5:

.. code-block:: none

  if row_count equals 5
  {
    some steps
  }

Here’s how we check if it is more than or less than 5:

.. code-block:: none

  if row_count is more than 5
  {
    some steps
  }

.. code-block:: none

  if row_count is less than 5
  {
    some steps
  }


Loops
-----------
You can use loops to do the same thing many times within the same flow. In order to run one flow many times with different variables, the standard way is to use :ref:`datatables <datatables>`.

In this example, we repeat the steps within the curly braces ``{`` and ``}`` a total of 20 times:

.. code-block:: none

  for n from 1 to 20
  {
    some steps
  }

.. _helper-functions:


Helper functions
---------------------
Helper functions are useful JavaScript functions which can get values to use in your steps.

Each helper function is followed by brackets ``()``. Some helper functions take inputs within these brackets.

You can see all the helper functions in the :ref:`reference <helper-functions-reference>`.


csv_row()
*********************
Turns some variables into csv text for writing to a csv file. It takes variables as its input, surrounded by square brackets ``[]`` (which is actually a JavaScript array).

.. code-block:: none

  read name_element to name
  read price_element to price
  read details_element to details
  write csv_row([name, price, details]) to product_list.csv


clipboard()
*********************
Gets text from the clipboard::

  dclick pdf_document.png
  wait 3 seconds
  keyboard [ctrl]a
  keyboard [ctrl]c
  text_contents = clipboard()


mouse_x(), mouse_y() 
*********************
Gets the mouse's x or y coordinates. 

This is useful for modifying x or y coordinates with numbers for using in steps like ``read`` and ``click``. 

The example below clicks 200 pixels to the right of ``element.png``::

  hover element.png
  x = mouse_x() + 200
  y = mouse_y()
  click (`x`,`y`)


mouse_xy() 
*********************
In live mode, you can use find out the coordinates of your mouse using ``echo mouse_xy()`` so that you can use the coordinates in your flows.

  echo mouse_xy()
