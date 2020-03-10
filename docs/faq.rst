Frequently Asked Questions
=============================

How is TagUI licensed?
-------------------------
TagUI is open-source software released under the Apache 2.0 license.


.. _find-xpath:

How do I find the XPath of a web element?
---------------------------------------------
In Chrome, right-click on the element, click Inspect, right-click on the highlighted HTML, then:

.. image:: ./_static/find_xpath.png

For some web pages, the XPath of an element can change. To combat this, you can find a stable element in the web page and writing a custom XPath relative to that stable element. 

XPath is very powerful and can allow you to select web elements in many ways. Learn more about XPath at `w3schools <https://www.w3schools.com/xml/xpath_intro.asp>`_.


.. _how-to-use-command-prompt:

How do I use the Command Prompt?
----------------------------------------
Hold the Windows key and press R. Then type ``cmd`` and press Enter to enter the Command Prompt.

From here, you can run a command by typing it and pressing Enter.


.. _how-to-use-terminal:

How do I use the Terminal?
----------------------------------------
Hold Command and press spacebar, then type ``Terminal`` and press Enter.

From here, you can run a command by typing it and pressing Enter.


.. _element_attributes:

How do I find the id, name, class or other attributes of a web element?
----------------------------------------------------------------------------
In Chrome, right-click on the element, click Inspect. There will be some highlighted HTML, like this:

.. image:: ./_static/element_attributes.png

This highlighted element has a class attribute of "chat-line__body". It doesn't have any ``id`` or ``name`` attribute.


How do I use the cutting edge version of TagUI?
--------------------------------------------------
1. Download the latest stable version at :doc:`the installation page </setup>`.
2. `Download the cutting edge version <https://github.com/kelaberetiv/TagUI/archive/develop.zip>`_.
3. Unzip and overwrite the files in your ``tagui/src/`` folder.


.. _what-are-csv-files:

What are csv files?
-------------------------------------------------
CSV files are files which stores data in a table form. They can be opened with Microsoft Excel and Google Sheets.

Each line is a row of values. The values are split into different columns by commas ``,``, which is CSV stands for Comma Separated Values.


.. _run-on-schedule:

Running flows on a fixed schedule
--------------------------------------
It is often useful to run flows automatically on a fixed schedule: monthly; weekly; daily or even every 5 minutes.

On Windows, `use the Task Scheduler <https://www.digitalcitizen.life/how-create-task-basic-task-wizard>`_.

.. raw:: html

    <video playsinline autoplay muted loop width="100%">
        <source src="./_static/schedule-a-flow.mp4" type="video/mp4">
        Your browser does not support the video tag.
    </video>

On macOS/Linux, `use crontab <https://www.ostechnix.com/a-beginners-guide-to-cron-jobs/>`_.



Is TagUI safe to use?
-----------------------------
As TagUI and the foundation it's built on is open-source software, it means users can read the source code of TagUI and all its dependencies to check if there is a security flaw or malicious code. This is an advantage compared to using commercial software that is closed-source, as users cannot see what is the code behind the software.

Following are links to the source code for TagUI and its open-source dependencies. You can dig through the source code for the other open-source dependencies below, or make the fair assumption that security issues would have been spotted by users and fixed, as these projects are mature and have large user bases.

- TagUI - https://github.com/kelaberetiv/TagUI
- SikuliX - https://github.com/RaiMan/SikuliX1
- CasperJS - https://github.com/casperjs/casperjs
- PhantomJS - https://github.com/ariya/phantomjs
- SlimerJS - https://github.com/laurentj/slimerjs
- Python - https://github.com/python/cpython
- R - https://github.com/wch/r-source
- PHP - https://github.com/php/php-src


Does TagUI track what I automate?
---------------------------------------
No. TagUI does not send outgoing web traffic or outgoing data, other than what the user is automating on, for example visiting a website.

.. _visual-automation-troubleshooting:


Why doesn’t my visual automation work?
----------------------------------------
On macOS, it may be due to `how the image was captured <https://github.com/kelaberetiv/TagUI/issues/240#issuecomment-405030276>`_.

On Linux, you may need to `set up dependencies <https://sikulix-2014.readthedocs.io/en/latest/newslinux.html#version-1-1-4-special-for-linux-people>`_.