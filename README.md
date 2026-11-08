<img src="https://raw.githubusercontent.com/kelaberetiv/TagUI/master/src/media/tagui_logo.png" height="111" align="right">

# TagUI
>**NOTICE: AI Singapore has discontinued maintenance and support of TagUI, but the project continues to be supported by open-source contributors and the community.**

**Check out this [YouTube learning series](https://www.youtube.com/playlist?list=PL2gs9_mNDDvZSCPsJu4APZR6AaDzUY7R6). Take the [free course](https://learn.aisingapore.org/courses/learn-rpa-with-tagui-beginners-course/). Ask any questions at [our Telegram](https://t.me/rpa_chat).**

**[Download v7.0](https://tagui.readthedocs.io/en/latest/setup.html)&ensp;|&ensp;[Usage Guide](https://tagui.readthedocs.io/en/latest/index.html)&ensp;|&ensp;[Demos](https://github.com/aimakerspace/TagUI-Bricks)&ensp;|&ensp;[Samples](https://github.com/kelaberetiv/TagUI/tree/master/flows/samples)&ensp;|&ensp;[Slides](https://docs.google.com/presentation/d/1pltAMzr0MZsttgg1w2ORH3ontR6Q51W9/edit?usp=sharing&ouid=115132044557947023533&rtpof=true&sd=true)&ensp;|&ensp;[Podcast](https://botnirvana.org/podcast/tagui/)&ensp;|&ensp;[Video](https://www.youtube.com/watch?v=C5itbB3sCq0)&ensp;|&ensp;[中文](http://www.tagui.com.cn)**

---

Write flows in simple TagUI language and automate away repetitive time-consuming tasks on your computer. Tasks include those on websites (native support for Chrome and Edge), desktop apps, or the command line. The TagUI project is open-source and free forever. It's easy to setup and use, and works on Windows, macOS and Linux.

Besides English, flows can be written in [22 other languages](https://github.com/kelaberetiv/TagUI/tree/master/src/languages), so you can do RPA using your [native language](https://github.com/kelaberetiv/TagUI/blob/master/flows/samples/8_chineseflow.tag). Check out this [demo video automating data collection](https://www.youtube.com/watch?v=o2WMUt0298U) in 4 different languages. With the new TagUI turbo mode, you can even run your automation 10X faster than normal human speed!

# What's new on master

These changes are merged on master and are not in a release zip yet. The new engine and OculiX are opt-in; without those options, flows run on CasperJS and SikuliX as before.

- **Modern Chrome engine (opt-in).** Add `-modern` or `-m` to run a flow on Chrome through the DevTools Protocol instead of CasperJS and PhantomJS. Chrome is visible by default; add `-headless` to hide it. Firefox is not supported in this mode. On Windows it needs PHP 7.4 or newer; the current Windows zip ships PHP 5.6 in `tagui\src\php`, and will bundle PHP 8.3 once [#1461](https://github.com/aisingapore/TagUI/pull/1461) is released. See [docs/modern-engine.md](https://github.com/aisingapore/TagUI/blob/master/docs/modern-engine.md).
- **TagUI MCP server.** `src/tagui_mcp` lets an MCP client such as an AI assistant write, check and run TagUI flows and use live mode, over stdio or HTTP. File tools let a client collect files that a flow produced, such as downloads, from allowed folders only. See [src/tagui_mcp/README.md](https://github.com/aisingapore/TagUI/blob/master/src/tagui_mcp/README.md).
- **OculiX (opt-in).** Visual automation can run on OculiX, the maintained successor of SikuliX, with Java 17 or newer. Set `TAGUI_SIKULI_JAR` to the OculiX jar, and `TAGUI_JAVA` to a Java 17 binary if needed (macOS / Linux launcher and the modern engine). The bundled SikuliX stays the default.
- **Visual automation.** Sikuli steps read and write UTF-8, so non-English text and image names are passed correctly. When an image is not found, the log shows the best similarity seen, for example `best similarity 0.62`.
- **Fixes.** On macOS / Linux, a flow input with spaces or `*` now reaches the flow as one input, and a datatable `.csv` is found in any position. Datatable transpose file names are handled correctly. Ubuntu's `chromium` wrapper script is handled. PHP 8.4 and newer no longer abort flows with deprecation warnings.

### Using the latest version from master

Until the next release, you can get these changes from master:

1. Back up your `tagui` folder, including any changes you made to `tagui/src/tagui_config.txt`.
2. Download [master.zip](https://github.com/aisingapore/TagUI/archive/refs/heads/master.zip) and unzip it. It creates a `TagUI-master` folder.
3. Copy the contents of `TagUI-master/src` into your `tagui/src` folder and replace the existing files. Do not delete `tagui/src` first: it contains folders that are only in the release zip, such as `php`, `casperjs`, `phantomjs` and `sikulix`.
4. On macOS / Linux, make sure the launcher is executable: `chmod +x tagui/src/tagui`.

`tagui update` also downloads master and copies it over your install, but only when your local version number differs from master. If running `tagui` with no options shows v7.0, the update will report that you already have the latest version, so use the steps above.

# Language designed for RPA

In TagUI language, you use steps like `click` and `type` to interact with identifiers, which include web identifiers, image snapshots, screen coordinates, or [even text using OCR](https://tagui.readthedocs.io/en/latest/advanced.html#visual-automation-tricks). Below is an example to login to Xero accounting:

```
https://login.xero.com/identity/user/login
type email as user@gmail.com
type password as 12345678
click Log in
```
```
// besides web identifiers, images of UI elements can be used
type email_box.png as user@gmail.com
type password_box.png as 12345678
click login_button.png
```

Grabbing data from a table on a website can be [as easy as below](https://tagui.readthedocs.io/en/latest/reference.html#table) (where n is the nth table on the webpage)

```
table n to forex_rates.csv
```

Moving data between TagUI and Excel is as easy as using [standard Excel formula](https://tagui.readthedocs.io/en/latest/reference.html#excel) that you are familiar with
```
top_salesman = [Monthly Report.xlsx]August!E11
```

# Do RPA Any Way You Want

You can use TagUI [MS Office Plug-ins](https://github.com/kelaberetiv/TagUI/blob/master/src/office/README.md) ([sample doc](https://github.com/kelaberetiv/TagUI/files/7031942/Week.3.docx)) to easily create and deploy Word doc as RPA robots, and set up RPA data parameters using Excel. Enjoy a full-featured RPA IDE with toolbar of TagUI steps and tooltips, snapshot tool to automate using computer vision, task pane for settings and run output. 

You can also create and edit your RPA robots using commonly used text editors like Notepad, Notepad++, VS Code, Sublime, TextEdit, Vim, etc. For VS Code users, you can install [TagUI language extension](https://marketplace.visualstudio.com/items?itemName=TagUisupport.tagui-support). For Notepad++ users, you can download [TagUI plug-ins here](https://github.com/tilyanPristka/TagUI-Snippets-for-NotepadPP) for syntax highlighting, shortcuts and snippets.

Some users might find it interesting or useful to use AI and machine learning (in particular LLM large language models), to help generate a template script, then they make the fine-tuning accordingly. [See this issue](https://github.com/aisingapore/TagUI/issues/1388) on some questions that I asked Anthropic's Claude 3.5 Sonnet and its responses.

![Word Plug-in v3](https://raw.githubusercontent.com/kelaberetiv/TagUI/master/src/office/word/word_addin_v3.png)

# Ecosystem and Communities

TagUI has a bustling user community, and extended community champions create new RPA tools for their own communities, based on TagUI. Python users can [pip install rpa](https://github.com/tebelorg/RPA-Python) to use the #1 Python RPA package. There is also [TagUI for C# .NET](https://www.nuget.org/packages/tagui). For event-driven RPA with thousands of connectors, do check out [TagUI module for Node-RED](https://flows.nodered.org/node/node-red-contrib-tagui), a popular free and open-source workflow automation tool.

For Microsoft [Power Automate Desktop](https://flow.microsoft.com/en-us/desktop/) users, you'll be happy to know that there's 2-way integration with TagUI out of the box (for business continuity if you switch between the 2 apps). Also, do check out other leading open-source RPA tools, to see if they meet your needs better - [OpenRPA](https://github.com/open-rpa/openrpa) & [OpenFlow](https://github.com/open-rpa/openflow), [Robocorp](https://youtu.be/HAfQpNZVbKI). All of them support enterprise-grade orchestrating and running TagUI robots from their orchestrator.

# Enterprise Security by design

Security Considerations
- TagUI default implementation is an on-user-computer on-prem RPA tool that does not exist on any cloud
- TagUI is not a SaaS or software on the cloud running on vendor's cloud, it runs on actual users' computers
- Industry-specific certifications like PCI-DSS, HIPAA, SOX aren't applicable because TagUI doesn't store data
- In decentralised bottom-up RPA, not advisable and no need for bot credentials as users are held accountable

Data Considerations
- For data at rest, storage encryption would be on user's computer's OS-level as it is run on user's computer
- For data in use, recommend user to manually enter sensitive info like password before letting robot take over
- For data in motion, users' enterprise app websites are now https by default for secure data entry and retrieval

More Information
- [See this guide](https://github.com/kelaberetiv/TagUI/raw/master/src/media/TagUI%20Enterprise%20Setup%20v1.7.docx) on enterprise installation, including whitelisting details, TagUI architecture and dependencies
- With -report option, there is a summary and detailed logs of robots, with support for centralised reporting

# How to get started

Join the community and ask any questions at our [Telegram chat group](https://t.me/rpa_chat). Watch this [YouTube learning series](https://www.youtube.com/playlist?list=PL2gs9_mNDDvZSCPsJu4APZR6AaDzUY7R6). Take [TagUI free course](https://learn.aisingapore.org/courses/learn-rpa-with-tagui-beginners-course/) over one morning or afternoon, and start using the most popular open-source RPA software. Share this [TagUI slide deck](https://docs.google.com/presentation/d/1pltAMzr0MZsttgg1w2ORH3ontR6Q51W9/edit?usp=sharing&ouid=115132044557947023533&rtpof=true&sd=true) with your team or customers to win their buy-in to use TagUI.

If you are maintaining your own fork of TagUI (for eg tech leads, RPA consultants, individual developers), see this [maintainer training video series](https://www.youtube.com/watch?v=oq8HlJujraE) to understand how TagUI works behind the scenes, and for you to modify all aspects of TagUI and extend the software to your customers' exact needs, made-to-measure.

# TagUI v5.11
For technical details of TagUI, such as architecture diagram and codebase structure, [see the old homepage](https://github.com/kelaberetiv/TagUI/tree/pre_v6) 

# Credits

Open-source project|Maintainer|From|How does this contribute to TagUI project
:------------------|:---------|:---|:----------------------------------------
[TagUI for China](http://www.tagui.com.cn)|[报表哥](https://www.zhihu.com/people/baobiaoge)|China|usage guide and repository in Chinese
[TagUI for Notepad++](https://github.com/tilyanPristka/TagUI-Snippets-for-NotepadPP)|[Md Ardyansyah](https://www.linkedin.com/in/muhammad-ardyansyah/)|Indonesia|various TagUI plug-ins for Notepad++
[TagUI for VS Code](https://marketplace.visualstudio.com/items?itemName=TagUisupport.tagui-support)|[Subhas Malik](https://www.linkedin.com/in/subhasmalik/)|India|language extension for Visual Studio Code
[TagUI for Robocorp](https://youtu.be/HAfQpNZVbKI)|[Nived N](https://www.linkedin.com/in/nived-n-776470139/)|India|run TagUI in Robocorp or Robot Framework
[TagUI for Node-RED](https://flows.nodered.org/node/node-red-contrib-tagui)|[Allan Zimmermann](https://www.linkedin.com/in/skadefro/)|Denmark|low-code event-driven workflow automation
[TagUI for C# .NET](https://www.nuget.org/packages/tagui)|[Allan Zimmermann](https://www.linkedin.com/in/skadefro/)|Denmark|C# version of TagUI (Install-Package tagui)
[TagUI for Docker](https://hub.docker.com/r/openiap/nodered-tagui)|[Allan Zimmermann](https://www.linkedin.com/in/skadefro/)|Denmark|replicable RPA environment for everyone
[RPA for Python](https://github.com/tebelorg/RPA-Python)|[Ken Soh](https://github.com/kensoh)|Singapore|Python version of TagUI (pip install rpa)
[TagUI v3.0](https://github.com/kensoh/TagUI/tree/before_aisg)|[Ken Soh](https://github.com/kensoh)|Singapore|personal project before AI Singapore
[SikuliX](http://sikulix.com)|[Raimund Hocke](https://github.com/RaiMan/)|Germany|computer vision, OCR, input hardware
[OculiX](https://github.com/oculix-org/Oculix)|[Raimund Hocke](https://github.com/RaiMan/), [Julien Mer](https://github.com/julienmerconsulting)|Germany, France|continuation of SikuliX: computer vision, OCR, input
[CasperJS](http://casperjs.org)|[Nicolas Perriault](https://github.com/n1k0)|France|high-level JavaScript execution engine
[PhantomJS](https://github.com/ariya/phantomjs)|[Ariya Hidayat](https://github.com/ariya)|Indonesia|foundation JavaScript execution engine
[SlimerJS](https://slimerjs.org)|[Laurent Jouanneau](https://github.com/laurentj)|France|browser automation for FireFox <= v59

# Sponsor
This project was previously supported by the [National Research Foundation](https://www.nrf.gov.sg), Singapore under its AI Singapore Programme (AISG-RP-2019-050). Any opinions, findings and conclusions or recommendations expressed in this material are those of the author(s) and do not reflect the views of National Research Foundation, Singapore.
