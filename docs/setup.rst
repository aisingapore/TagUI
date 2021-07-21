Installation
===================

Windows
-------------------------------

You can use `TagUI Windows Installer <https://github.com/kelaberetiv/TagUI/releases/download/v6.46.0/TagUI_Windows.exe>`_ or the steps below. If you use TagUI Windows Installer, please specify an installation folder which you have write permission to, for example ``C:\RPA`` or a ``RPA`` folder on your Windows Deskop. After installation, you can continue from step 6.

To install TagUI Microsoft Word Plug-in (a full-featured RPA studio to make RPA very accessible), download the `MS Word Plug-in installer <https://github.com/kelaberetiv/TagUI/releases/download/v6.64.0/TagUIWordAddInSetupV3.09.zip>`_, unzip the file and double-click Setup.exe. Download `MS Excel Plug-in installer <https://github.com/kelaberetiv/TagUI/releases/download/v6.64.0/TagUIExcelAddInSetupV3.05.zip>_` to configure RPA data and parameters in Excel and run TagUI from Excel.

1. `Download TagUI v6.46 for Windows <https://github.com/kelaberetiv/TagUI/releases/download/v6.46.0/TagUI_Windows.zip>`_

2. Unzip the contents to ``C:\``

3. `Install OpenJDK for Windows <https://corretto.aws/downloads/latest/amazon-corretto-8-x64-windows-jdk.msi>`_

4. `Install Chrome web browser <https://www.google.com/chrome/>`_

5. `Add c:\\tagui\\src to start of path <https://www.c-sharpcorner.com/article/add-a-directory-to-path-environment-variable-in-windows-10/>`_

6. Open :ref:`Command Prompt <how-to-use-command-prompt>`

7. Copy, paste and run this command: 

.. code-block:: bat

    tagui c:\tagui\flows\samples\1_google.tag

8. :ref:`Having problems? Click here.<troubleshooting-windows>` You have run your first TagUI flow! 🎉

9. Optionally run ``tagui update`` to get the `latest features and bug fixes <https://github.com/kelaberetiv/TagUI/issues?q=is%3Aissue+is%3Aopen+in%3Atitle+fixed+OR+done+>`_

macOS / Linux
-----------------------------------
1. Download TagUI v6.46 for `macOS <https://github.com/kelaberetiv/TagUI/releases/download/v6.46.0/TagUI_macOS.zip>`_ or `Linux <https://github.com/kelaberetiv/TagUI/releases/download/v6.46.0/TagUI_Linux.zip>`_

2. Unzip the contents to your desktop on macOS, or ``/home/your_userid`` on Linux

3. Install OpenJDK for `macOS <https://corretto.aws/downloads/latest/amazon-corretto-8-x64-macos-jdk.pkg>`_ or `Linux <https://corretto.aws/downloads/latest/amazon-corretto-8-x64-linux-jdk.tar.gz>`_

4. `Install Chrome web browser <https://www.google.com/chrome/>`_

5. Open :ref:`Terminal <how-to-use-terminal>`

6. Copy, paste and run these commands, replacing ``your_tagui_path`` accordingly:

.. code-block:: bash

    sudo ln -sf /your_tagui_path/tagui/src/tagui /usr/local/bin/tagui

    tagui /your_tagui_path/tagui/flows/samples/1_google.tag

7. :ref:`Having problems? Click here.<troubleshooting-macos-linux>` You have run your first TagUI flow! 🎉

8. Optionally run ``tagui update`` to get the `latest features and bug fixes <https://github.com/kelaberetiv/TagUI/issues?q=is%3Aissue+is%3Aopen+in%3Atitle+fixed+OR+done+>`_
