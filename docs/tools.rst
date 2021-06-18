Tools
====================
These are separate apps which help you in writing TagUI flows.


TagUI Chrome Extension
--------------------------
The TagUI Chrome extension (`download <https://chrome.google.com/webstore/detail/tagui-web-automation/egdllmehgfgjebhlkjmcnhiocfcidnjk/>`_) helps you write web flows.

It records steps such as page navigation, clicking of web elements and entering information. It then displays the steps for you to paste into your flow.


Usage
*************************
1. Go to the website URL you want to start the automation at.
2. Click the TagUI icon, then **Start**.
3. Carry out the steps you want to automate, or right click on elements to record other steps.
4. Click the TagUI icon, then **Stop**.
5. Click **Export** to view the generated TagUI steps.

The recording isn't foolproof (for example, the underlying recording engine cannot capture frames, popup windows or tab key input). It's meant to simplify flow creation with some edits, instead of typing everything manually. 

`See this video <https://www.youtube.com/watch?v=bFvsc4a8hWQ>`_ for an example of recording a sequence of steps, editing for adjustments and playing back the automation.


TagUI Writer, Screenshoter & Editor
----------------------------------------
TagUI Writer is a Windows app helps write TagUI flows. When pressing Ctrl + Left-click, a popup menu appears with the list of TagUI steps for you to paste into your text editor. 

TagUI Screenshoter app helps in capturing screenshots for TagUI visual automation. 

TagUI Editor allows you to edit and run TagUI scripts via AutoHotKey.

`Download these here. <https://github.com/adegard/tagui_scripts>`_. These third-party tools are created by Arnaud Degardin `@adegard <https://github.com/adegard>`_.

.. image:: https://raw.githubusercontent.com/adegard/tagui_scripts/master/TagUI_Editor.gif


RPA for Python (for Python users)
---------------------------------------
RPA for Python is a Python package (``pip install rpa`` to install) which allows you to use TagUI through a Python API. Check out `the documentation <https://github.com/tebelorg/RPA-Python>`_. It is based on a fork of TagUI optimised for use by the Python package. Created and maintained by TagUI's creator Ken Soh `@kensoh <https://github.com/kensoh>`_.

.. image:: https://raw.githubusercontent.com/tebelorg/Tump/master/tagui_python.gif
