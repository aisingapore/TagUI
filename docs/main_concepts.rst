Main concepts
====================

Flows
--------------
TagUI automates your actions by running *flows*, which are text files with ``.tag`` file extension.

You can run a flow in the :ref:`Command Prompt/Terminal<how-to-use-command-prompt>` like this:: 

    tagui my_flow.tag

.. raw:: html

    <video playsinline autoplay muted loop width="100%">
        <source src="./_static/run-a-flow.mp4" type="video/mp4">
        Your browser does not support the video tag.
    </video>
|
TagUI looks for ``my_flow.tag`` in your current working directory. You can also provide the full path::

    tagui c:\tagui\samples\1_google.tag

You can also :ref:`run flows on a fixed schedule <run-on-schedule>`.


Run by double-click
**********************
You can create a shortcut file with ``-deploy``::

  tagui my_flow.tag -deploy

or using the shortcut ``-d``::

  tagui my_flow.tag -d

This creates a shortcut (my_flow.cmd) to run your flow just by double clicking the shortcut. The shortcut is in the same folder as your flow, but you can move it to your desktop or anywhere else.

.. raw:: html

    <video playsinline autoplay muted loop width="100%">
        <source src="./_static/deploy-a-flow.mp4" type="video/mp4">
        Your browser does not support the video tag.
    </video>
|
If you want to create the shortcut with options like ``-headless`` (``-h``), you can include them::

  tagui my_flow.tag -h -d

.. note:: If you move your flow file to another folder, you will need to create a new shortcut file.


Run from a URL
**********************
You can also run a flow directly from a URL::

    tagui https://raw.githubusercontent.com/kelaberetiv/TagUI/master/flows/samples/1_google.tag


Hide the browser
**********************
You can run web flows without showing web browser by running TagUI with ``-headless`` option. ::

    tagui my_flow.tag -headless

or using the shortcut ``-h``::

  tagui my_flow.tag -h

This allows you to continue using your desktop normally while the flow is running, but it will not work if your flow uses visual automation, because it reads and clicks what is on your screen.

.. raw:: html

    <video playsinline autoplay muted loop width="100%">
        <source src="./_static/run-headless-flow.mp4" type="video/mp4">
        Your browser does not support the video tag.
    </video>

|
Steps
---------
Flows are made out of *steps*. Below are some common steps.

You can see the full list of steps in the :ref:`steps reference <step-reference>`.


click
**********
One of the most common steps is click. You can use it to click on a web element:

.. code-block:: none

  click Getting started

This command tells TagUI to try to click on any element which has “Getting started” as its “id”, “name”, “class” or “title” attributes (:ref:`How to find an element’s attributes <element_attributes>`), or as a last resort, has “Getting started” in its text.

This method usually works for targeting what you want, but you can be more explicit by providing an XPath. XPath is a powerful way to choose which web element you want to target. For example:

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


assign
**********
You can assign values into variables. This makes them easier to reference and work with.

This example uses the ``count()`` :ref:`helper function <helper-functions>`, counts the number of elements found with id/name/text with ‘row’ in them and assigns it to a variable ``row_count`` for later use:

.. code-block:: none

  row_count = count('row')


Identifiers
---------------
You have probably noticed that different steps have different ways that they target elements, called **identifiers**. Let's look at the different types of identifiers.

.. note:: The DOM and XPath identifiers only work for Chrome/Edge. To automate other browsers, use the Point/Region and Image identifiers.


.. _dom:

DOM
**********
.. code-block:: none

  click Getting started

This matches an element in the DOM (Document Object Model) of the web page, matching either the :ref:`id, name, class attributes <element_attributes>` or the text of the element itself.


.. _xpath:

XPath
**********
.. code-block:: none

  click //body/div[1]/nav/div/div[1]/a

This matches the :ref:`XPath <find-xpath>` of an element in the web page.

It is a more explicit and powerful way of targeting web elements.

.. note:: You can use CSS selectors too in place of XPath, but XPath is preferred.


.. _point:

Point
**********
.. code-block:: none

  click (200,500)

This matches the point on the screen 200 pixels from the left of the screen and 500 pixels from the top of the screen. This uses *visual automation*.


.. _region:

Region
**********
.. code-block:: none

  read (300,400)-(500,550) to some-variable

This matches the rectangle formed between the two points (300,400) and (500,550). See :ref:`Point <point>`. This uses *visual automation*.


.. _image:

Image
**********
.. code-block:: none

  click button.png

This matches any area on the screen that looks similar to an image file ``button.png`` (in the folder of the flow). You first need to take a screenshot of ``button.png``. This uses *visual automation*.

.. code-block:: none

  click image/button.png

This allows you to look for ``button.png`` within the ``image`` folder.


.. _live-mode:

Live mode
---------------
We recommend using live mode when you want to write your own flows or try out some step. In :ref:`Command Prompt/Terminal <how-to-use-command-prompt>`::

  tagui live

This starts up a live session, where you can run steps one line at a time and immediately see the output. Within a TagUI flow, you can also use ``live`` step to pause execution and enter live mode.

.. raw:: html

    <video playsinline autoplay muted loop width="100%">
        <source src="./_static/live-mode.mp4" type="video/mp4">
        Your browser does not support the video tag.
    </video>

|
.. _if-conditions:

If conditions
---------------
You may want your flow to do something different depending on some factors. You can use an if condition to do this.

For example, if the URL contains the word “success”, then we want to click some buttons:

.. code-block:: none

  if url() contains "success"
    click button1.png
    click button2.png

``url()`` is a :ref:`helper function <helper-functions>` that gets the url of the current webpage. Note that the other lines are *indented*, ie. there are spaces (or tabs) in front of them. This means that they are in the *if block*. The steps in the *if block* will only be run if the condition is met, ie. the url contains the word “success”.

.. note:: 
  Before v6, you need to use ``{`` and ``}`` to surround your *if block*.

  From v6 onwards, the curly braces ``{}`` are optional.

Another common case is to check if some element exists. Here, we say that “if some-element doesn’t appear after waiting for the timeout, then visit this webpage”.

.. code-block:: none

  if !exist('some-element')
    https://tagui.readthedocs.io/

The ! negates the condition and comes from JavaScript, which TagUI code eventually translates to.

In below example, we check if a variable row_count, which we assigned a value earlier, is equal to 5:

.. code-block:: none

  if row_count equals to 5
    some steps

Here’s how we check if it is more than or less than 5:

.. code-block:: none

  if row_count more than 5
    some steps

.. code-block:: none

  if row_count less than 5
    some steps

.. _for-loops:

For loops
-----------
You can use loops to do the same thing many times within the same flow. In order to run one flow many times with different variables, the standard way is to use :ref:`datatables <datatables>`.

In this example, we repeat the steps within the block for a total of 20 times:

.. code-block:: none

  for n from 1 to 20
    some step to take
    some other step
    some more step

.. _helper-functions:


Helper functions
---------------------
Helper functions are useful JavaScript functions which can get values to use in your steps.

Each helper function is followed by brackets ``()``. Some helper functions take inputs within these brackets. You can see the full list of helper functions in the :ref:`helper functions reference <helper-functions-reference>`.


csv_row()
*********************
Turns some variables into csv text for writing to a csv file. It takes variables as its input, surrounded by square brackets ``[]`` (which is actually a JavaScript array).

.. code-block:: none

  read name_element to name
  read price_element to price
  read details_element to details
  write `csv_row([name, price, details])` to product_list.csv


clipboard()
*********************
Gets text from the clipboard::

  dclick pdf_document.png
  wait 3 seconds
  keyboard [ctrl]a
  keyboard [ctrl]c
  text_contents = clipboard()

You can also give it an input, which puts the input *onto* the clipboard instead. This can be useful for pasting large amounts of text directly, which is faster than using the **type** step::

  long_text = "This is a very long text which takes a long time to type"
  clipboard(long_text)
  click text_input
  keyboard [ctrl]v
  keyboard [enter]
  

mouse_x(), mouse_y() 
*********************
Gets the mouse's x or y coordinates. 

This is useful for modifying x or y coordinates with numbers for using in steps like ``read`` and ``click``. The example below clicks 200 pixels to the right of ``element.png``::

  hover element.png
  x = mouse_x() + 200
  y = mouse_y()
  click (`x`,`y`)


mouse_xy() 
*********************
In live mode, you can use find out the coordinates of your mouse using ``echo `mouse_xy()``` so that you can use the coordinates in your flows::

  echo `mouse_xy()`
