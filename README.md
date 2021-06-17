<img src="https://raw.githubusercontent.com/kelaberetiv/TagUI/master/src/media/tagui_logo.png" height="111" align="right">

# TagUI

**Free RPA tool by [AI Singapore](https://aisingapore.org), a government-funded programme to accelerate AI. To start, click the download link below. Join the community or ask any questions at [our Telegram](https://t.me/rpa_chat).**

**[Download TagUI v6.46](https://tagui.readthedocs.io/en/latest/setup.html)&ensp;|&ensp;[Usage Guide](https://tagui.readthedocs.io/en/latest/index.html)&ensp;|&ensp;[Demos](https://github.com/aimakerspace/TagUI-Bricks)&ensp;|&ensp;[Samples](https://github.com/kelaberetiv/TagUI/tree/master/flows/samples)&ensp;|&ensp;[Video](https://youtu.be/hKc4eNBhMws)&ensp;|&ensp;[Slides](https://drive.google.com/file/d/1pltAMzr0MZsttgg1w2ORH3ontR6Q51W9/view?usp=sharing)&ensp;|&ensp;[Zoom Q&A](https://github.com/kelaberetiv/TagUI/issues/914)**

---

![TagUI Users](https://raw.githubusercontent.com/kelaberetiv/TagUI/master/src/media/tagui_users.png)

Write flows in simple TagUI language and automate away repetitive time-consuming tasks on your computer. The TagUI project is [open-source and free forever](https://www.linkedin.com/posts/kensoh_sneak-preview-of-tagui-ms-word-plug-in-v3-activity-6796860165338595328-02wD). It's easy to setup and use, and works on Windows, macOS and Linux. Besides English, flows can be written in [22 other languages](https://github.com/kelaberetiv/TagUI/tree/master/src/languages), so you can do RPA using your [native language](https://github.com/kelaberetiv/TagUI/blob/master/flows/samples/8_chineseflow.tag).

# Language designed for RPA

In TagUI language, you use steps like `click` and `type` to interact with identifiers, which include web identifiers, image snapshots, screen coordinates, or even text using OCR. Below is an example to login to Xero accounting:

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
```
// (x,y) coordinates of user-interface elements can also be used

type (720,400) as user@gmail.com
type (720,440) as 12345678
click (720,500)
```

# Do RPA with Microsoft Word

You can use TagUI [MS Office Plug-ins](https://github.com/kelaberetiv/TagUI/tree/master/src/office) ([intro video](https://www.linkedin.com/posts/kensoh_hi-folks-interested-in-rpa-and-digital-process-activity-6809713603554492416-JgNZ)) to easily create and deploy Word doc as RPA robots, and set up RPA data parameters using Excel. Enjoy a full-featured RPA IDE with toolbar of TagUI steps and tooltips, snapshot tool to automate using computer vision, task pane for settings and run output. You can also run TagUI on your phone browser using [free Google Cloud](https://github.com/kelaberetiv/TagUI/issues/913). For VS Code users, you can install this [language extension](https://www.linkedin.com/posts/kensoh_hi-vs-code-folks-who-love-rpa-now-you-can-activity-6805445134034042880--PWT).

![Word v3 Preview](https://raw.githubusercontent.com/kelaberetiv/TagUI/master/src/office/word/word_v3_preview.png)

# Ecosystem and Communities

TagUI has a bustling user community, and extended community champions create new RPA tools for their own communities, based on TagUI. Python users can [pip install rpa](https://github.com/tebelorg/RPA-Python) for the #1 Python RPA package. TagUI for [C# .NET](https://www.nuget.org/packages/tagui), [Java](https://www.linkedin.com/posts/kensoh_hi-c-net-folks-interested-in-rpa-happy-activity-6800991305251078144-Gx4T), and [Go](https://www.linkedin.com/posts/kensoh_hi-fans-of-go-programming-language-would-activity-6804658389772324864-_OgH) programming languages are being built. For event-driven RPA with thousands of connectors, check out this TagUI [module for Node-RED](https://flows.nodered.org/node/node-red-contrib-tagui), a popular free and open-source workflow automation tool.

For Microsoft [Power Automate Desktop](https://flow.microsoft.com/en-us/desktop/) users, you'll be happy to know that there's [2-way integration](https://www.linkedin.com/posts/kensoh_tagui-activity-6773236538596831232-1aFu) with TagUI out of the box (for business continuity if you switch between the 2 apps). Do also check out other leading open-source RPA tools, to see if they meet your needs better - [OpenRPA](https://github.com/open-rpa/openrpa) & [OpenFlow](https://github.com/open-rpa/openflow), [OpenBots](https://www.linkedin.com/posts/openbots_openbots-studio-demo-support-for-tag-ui-activity-6788174021964943361-RrUD), [Robocorp](https://youtu.be/HAfQpNZVbKI). All of them support enterprise-grade orchestrating and running TagUI robots from their orchestrator.

![TagUI Roadmap](https://raw.githubusercontent.com/kelaberetiv/TagUI/master/src/media/roadmap.png)

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
- With -report option, there is a summary and detailed logs of robots to be enhanced for [centralised reporting](https://github.com/kelaberetiv/TagUI/issues/956#issuecomment-850123072)
- For more info on TagUI architecture diagram, software components and security considerations, [visit this link](https://github.com/kelaberetiv/TagUI/tree/pre_v6#security)

# How to get started

Join the community and ask any questions at our [Telegram chat group](https://t.me/rpa_chat) or our [Zoom Q&A every Thursday](https://github.com/kelaberetiv/TagUI/issues/914)

# TagUI v5.11
For technical details of TagUI, such as architecture diagram and codebase structure, [see old homepage](https://github.com/kelaberetiv/TagUI/tree/pre_v6) 

# Credits

Open-source project|Maintainer|Where|How does this contribute to TagUI project
:------------------|:---------|:----|:----------------------------------------
[TagUI for VS Code](https://www.linkedin.com/posts/kensoh_hi-vs-code-folks-who-love-rpa-now-you-can-activity-6805445134034042880--PWT)|[Subhas Malik](https://www.linkedin.com/in/subhasmalik/)|India|language extension for Visual Studio Code
[TagUI for Robocorp](https://youtu.be/HAfQpNZVbKI)|[Nived N](https://www.linkedin.com/in/nived-n-776470139/)|India|run TagUI in Robocorp or Robot Framework
[TagUI for Node-RED](https://flows.nodered.org/node/node-red-contrib-tagui)|[Allan Zimmermann](https://www.linkedin.com/in/skadefro/)|Denmark|low-code event-driven workflow automation
[TagUI for C# .NET](https://www.nuget.org/packages/tagui)|[Allan Zimmermann](https://www.linkedin.com/in/skadefro/)|Denmark|C# version of TagUI (Install-Package tagui)
[RPA for Python](https://github.com/tebelorg/RPA-Python)|[Ken Soh](https://www.linkedin.com/in/kensoh/)|Singapore|Python version of TagUI (pip install rpa)
[TagUI v3.0](https://github.com/kensoh/TagUI/tree/before_aisg)|[Ken Soh](https://www.linkedin.com/in/kensoh/)|Singapore|personal project before AI Singapore
[SikuliX](http://sikulix.com)|[Raimund Hocke](https://github.com/RaiMan/)|Germany|computer vision, OCR, input hardware
[CasperJS](http://casperjs.org)|[Nicolas Perriault](https://github.com/n1k0)|France|high-level JavaScript execution engine
[PhantomJS](https://github.com/ariya/phantomjs)|[Ariya Hidayat](https://www.linkedin.com/in/ariyahidayat/)|Indonesia|foundation JavaScript execution engine
[SlimerJS](https://slimerjs.org)|[Laurent Jouanneau](https://github.com/laurentj)|France|browser automation for FireFox <= v59

# Sponsor
This project  is supported by the [National Research Foundation](https://www.nrf.gov.sg), Singapore under its AI Singapore Programme (AISG-RP-2019-050). Any opinions, findings and conclusions or recommendations expressed in this material are those of the author(s) and do not reflect the views of National Research Foundation, Singapore.

