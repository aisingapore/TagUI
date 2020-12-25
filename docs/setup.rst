Installation
===================

Windows
-------------------------------

1. `Download TagUI v6.14 for Windows <https://github.com/kelaberetiv/TagUI/releases/download/v6.14.0/TagUI_Windows.zip>`_.

2. Unzip the contents to ``C:\``.

3. `Install OpenJDK for Windows <https://corretto.aws/downloads/latest/amazon-corretto-8-x64-windows-jdk.msi>`_.

4. `Install Chrome <https://www.google.com/chrome/>`_.

5. `Add c:\tagui\src to your path <https://www.c-sharpcorner.com/article/add-a-directory-to-path-environment-variable-in-windows-10/>`_.

6. Open :ref:`Command Prompt <how-to-use-command-prompt>`.

7. Copy, paste and run this command:

.. code-block:: bat

    tagui c:\tagui\flows\samples\1_google.tag

You have run your first TagUI flow! 🎉

:ref:`Having problems? <troubleshooting-windows>`

macOS/Linux
-----------------------------------
1. Download TagUI v6.14 (`macOS <https://github.com/kelaberetiv/TagUI/releases/download/v6.14.0/TagUI_macOS.zip>`_, `Linux <https://github.com/kelaberetiv/TagUI/releases/download/v6.14.0/TagUI_Linux.zip>`_).

2. Unzip the contents to your desktop on macOS, or ``/home/your_userid`` on Linux.

3. Install OpenJDK (`macOS <https://corretto.aws/downloads/latest/amazon-corretto-8-x64-macos-jdk.pkg>`_, `Linux <https://corretto.aws/downloads/latest/amazon-corretto-8-x64-linux-jdk.tar.gz>`_).

4. `Install Chrome <https://www.google.com/chrome/>`_.

5. Open :ref:`Terminal <how-to-use-terminal>`.

6. Copy, paste and run these commands, replacing ``your_tagui_path`` as needed:

.. code-block:: bash

    ln -sf /your_tagui_path/tagui/src/tagui /usr/local/bin/tagui

    tagui /your_tagui_path/tagui/flows/samples/1_google.tag

You have run your first TagUI flow! 🎉

:ref:`Having problems? <troubleshooting-macos-linux>`
