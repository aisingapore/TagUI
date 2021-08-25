Advanced concepts
===================

Saving flow run results
-------------------------
You can save an html log of the run and the flow run results to ``tagui/src/tagui_report.csv`` with the ``-report`` option (shortcut ``-r``). ::

    tagui my_flow.tag -report

The CSV file will show one line for each run, when it started, how long it took to complete, any error message during run, the link to the log file for that run, and the user's workgroup\\userid.


Handling exceptions and errors
--------------------------------
There are 3 ways to handle exceptions in TagUI when things do not go as planned.

The first way is **local error handling**. This means using if conditions to check specifically for certain scenarios and handling the scenarios accordingly. For example, check if some UI element is missing, then do xyz steps. Using this way, a workflow can have multiple fine-grain exception handling.

The second way is **workflow error handling**. A workflow can be chained as follows to handle error or success accordingly. The workflow error.tag will run only if flow.tag errors out. The workflow success.tag will run only if flow.tag runs successfully. TagUI will automatically throw error when it detects an expected UI element missing (and autosave screenshot) or some other unknown errors.

Windows example from the command prompt::

  call tagui flow.tag || tagui error.tag
  call tagui flow.tag && tagui success.tag

macOS / Linux example from the terminal::

  tagui flow.tag || tagui error.tag
  tagui flow.tag && tagui success.tag

The third way is **global error handling**. Configuration can be done for TagUI such that after every run, special handling is done to send data or files generated from the report option to some target folder or API endpoint for error / success handling. For example, syncing all automation runs to central storage for auditing purpose. The special handling applies to all TagUI flows that are run.


.. _datatables:

Datatables
------------
Datatables are :ref:`csv files <what-are-csv-files>` which can be used to run your flows multiple times with different inputs.

A datatable (``trade_data.csv``) could look like this:

= ============ ============= ======== ====== ====== =========
# trade        username      password pair   size   direction
- ------------ ------------- -------- ------ ------ ---------
1 Trade USDSGD test_account  12345678 USDSGD 10000  BUY
2 Trade USDSGD test_account  12345678 USDJPY 1000   SELL
3 Trade EURUSD test_account  12345678 EURUSD 100000 BUY
= ============ ============= ======== ====== ====== =========

To use it, you run your flow with ``tagui my_flow.tag trade_data.csv``. TagUI will run ``my_flow.tag`` once for each row in the datatable (except the header). Within the flow, TagUI can use the variables ``trade``, ``username``, ``password``, etc as if they were in the :ref:`local object repository <object-repository>` and the values will be from that run's row. To know which iteration your flow is in you can use the ``iteration`` variable.


.. _object-repository:

Object repositories
------------------------
Object repositories are optional :ref:`csv files <what-are-csv-files>` which can store variables for use in flows. They help to separate your flows from your personal data (like login information for web flows), and allow you to share common information between multiple flows for easy updating.

Each flow has a **local object repository** and all flows share the **global object repository**. The local object repository is the ``tagui_local.csv`` in the same folder as the flow. The global object repository is the ``tagui_global.csv`` in the ``tagui/src/`` folder.

An object repository could look like this:

============== =================================
object         definition
-------------- ---------------------------------
email          user-email-textbox
create account btn btn--green btn-xl signup-btn
============== =================================

Within the flow, TagUI can use the objects ``email``, ``create account`` as variables and they will be replaced directly by the definitions before it is run. Local definitions take precedence over global definitions.

If ``user-email-textbox`` was the identifier for some web text input, then you could use the following in your flow::

  type `email` as my_email@email.com


Running other flows within a flow
-----------------------------------
A flow can run another flow, like this::

  tagui login_crm.tag

Variables in the parent flow are accessible in the child flow and vice versa. 


Visual automation tricks
------------------------------------
For many steps, you can end the step with ``using ocr`` or ``using OCR`` to tell TagUI to interact on some UI element on the screen using OCR (optical character recognition). See the examples below. Steps which this can be done: click, rclick, dclick, hover, type, select, read, snap, exist(), present().

.. code-block:: none

  click Submit using ocr

  select Dress Color as Dark Blue using OCR
  
  if exist('Special Offer using ocr')
    click Add To Cart using OCR

If you make the background of a UI element in a ``.png`` file 100% transparent using an image editor, TagUI will be able to target the element regardless of its background. 

Conversely, you can also remove the foreground content near some anchor element like a frame, to allow you to OCR varying content in the empty area using the **read** step.


Writing Python within flows
--------------------------------
You can write Python code in TagUI flows. Python needs to be `installed separately <https://www.python.org/downloads/>`_. 

The ``py`` step can be used to run commands in Python (TagUI will call ``python`` on the command line). You can pass string values back to TagUI with `print()`. The ``stdout`` will be stored in the ``py_result`` variable in TagUI.

.. code-block:: none

  py a=1
  py b=2
  py c=a+b
  py print(c)
  echo `py_result`

You can also use ``py begin`` and ``py finish`` before and after a Python code block::

  py begin
  a=1
  b=2
  c=a+b
  print(c)
  py finish
  echo `py_result`

You can pass a variable to Python like this::

  phone = 1234567
  py_step('phone = ' + phone)
  py print(phone)
  echo `py_result`


Create log files for debugging
---------------------------------
To do advanced debugging, you can create log files when running flows by creating an empty ``tagui_logging`` file in ``tagui/src/``.

- ``my_flow.log`` stores step-by-step output of the execution. 
- ``my_flow.js`` is the generated JavaScript file that was run.
- ``my_flow.raw`` is the expanded flow after parsing modules.
