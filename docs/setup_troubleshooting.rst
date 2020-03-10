.. _troubleshooting-windows:

Troubleshooting Windows Setup
=====================================

- If you see ``MSVCR110.dll is missing``: Install :download:`Visual C++ Redistributable <./_static/vcredist_x86.exe>`.

- If you cannot unzip to ``C:\``, unzip to your desktop.

.. _troubleshooting-macos-linux:

Troubleshooting macOS/Linux Setup
=====================================

- If you see ``dyld: Library not loaded``: `install OpenSSL in this way <https://github.com/kelaberetiv/TagUI/issues/86>`_. macOS Catalina update has introduced tighter security controls, see solutions for the `PhantomJS <https://github.com/kelaberetiv/TagUI/issues/601>`_ and `Java popups <https://github.com/kelaberetiv/TagUI/issues/598>`_.

- You may need to install PHP separately on Linux.