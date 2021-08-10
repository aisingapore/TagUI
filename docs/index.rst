TagUI
=================================

.. image:: _static/tagui-logo.png
    :width: 400px

A free, open-source, cross-platform RPA tool that helps you automate your desktop, web, mouse and keyboard actions easily. Here's what a simple TagUI flow looks like:

.. code-block:: none

    // below is an example to login to Xero accounting website
    https://login.xero.com/identity/user/login

    type email as user@gmail.com
    type password as 12345678
    click Log in

.. code-block:: none

    // besides web identifiers, images of UI elements can be used

    type email_box.png as user@gmail.com
    type password_box.png as 12345678
    click login_button.png

.. code-block:: none

    // (x,y) coordinates of user-interface elements can also be used

    type (720,400) as user@gmail.com
    type (720,440) as 12345678
    click (720,500)

.. toctree::
   :maxdepth: 1
   :caption: Contents:
   :hidden:

   Installation <setup>
   Main concepts <main_concepts>
   Advanced concepts <advanced>
   Reference <reference>
   Tools <tools>
   FAQ <faq>
   
:doc:`Download TagUI v6.46 here </setup>`.
