Getting started
==================

.. raw:: html

    <video playsinline autoplay muted loop height="200">
        <source src="./_static/testonly.mp4" type="video/mp4">
        Your browser does not support the video tag.
    </video>

Run a flow
-------------
To run a flow, first open Command Prompt, Terminal or some other shell. When you are at the command line, you will have a current working directory. :ref:`Change your working directory <change-working-directory>` to where your flow is located. For Windows, you can do this to get to the sample flows::

    cd c:\tagui\samples

Then run the following::

    tagui 1_yahoo.tag chrome

You could also provide the full path to your flow::

    tagui c:\tagui\samples\1_yahoo chrome

You can also run a flow directly from a URL::

    tagui https://raw.githubusercontent.com/kelaberetiv/TagUI/master/src/samples/1_yahoo chrome


Write a web flow
-----------------


Write a visual flow
---------------------