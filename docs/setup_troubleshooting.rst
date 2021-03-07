.. _troubleshooting-windows:

Troubleshooting Windows Setup
=====================================

- If you see ``MSVCR110.dll is missing``: install :download:`Visual C++ Redistributable <./_static/vcredist_x86.exe>`

- If you cannot unzip to ``C:\``, unzip to your desktop (avoid space in folder names)

.. _troubleshooting-macos-linux:

Troubleshooting macOS/Linux Setup
=====================================

- If you see ``dyld: Library not loaded``: overwrite tagui/src/phantomjs with `contents of this zip <https://bitbucket.org/ariya/phantomjs/downloads/phantomjs-2.1.1-macosx.zip>`_

- macOS now has tighter security controls, see solutions for the `PhantomJS <https://github.com/kelaberetiv/TagUI/issues/601>`_ and `Java popups <https://github.com/kelaberetiv/TagUI/issues/598>`_

- You may need to install PHP separately on Linux, eg Ubuntu does not come with PHP
