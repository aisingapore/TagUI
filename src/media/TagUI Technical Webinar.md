# TagUI Technical Webinar

### Video recording - https://www.youtube.com/watch?v=C5itbB3sCq0

TagUI is a free and open-source RPA tool by [AI Singapore](https://aisingapore.org), a government-funded programme to accelerate AI. TagUI has a bustling user community with ~3k downloads monthly, and extended community champions create new RPA tools for their own communities, based on TagUI. So there's a flavour of the RPA tool which has a flat learning curve for you.

# Different flavours of TagUI

### Human languages
- TagUI in English - https://github.com/kelaberetiv/TagUI
- TagUI in other languages - https://github.com/kelaberetiv/TagUI/tree/master/src/languages
- TagUI in MS Word & Excel - https://github.com/kelaberetiv/TagUI/blob/master/src/office/README.md
- TagUI in VS Code - [how to install the extension](https://www.linkedin.com/posts/kensoh_hi-vs-code-folks-who-love-rpa-now-you-can-activity-6805445134034042880--PWT) and its [Marketplace homepage](https://marketplace.visualstudio.com/items?itemName=TagUisupport.tagui-support)
- TagUI in Node-RED - https://flows.nodered.org/node/node-red-contrib-tagui
- TagUI on cloud - https://colab.research.google.com/drive/1RimeT6-u6_nCyLAFJ0Aq9OvRaEg2UPrg?usp=sharing
- TagUI on Docker - https://hub.docker.com/r/openiap/nodered-tagui
- TagUI can be orchestrated in other open-source RPA tools - [OpenRPA](https://github.com/open-rpa/openrpa) & [OpenFlow](https://github.com/open-rpa/openflow), [OpenBots](https://www.linkedin.com/posts/openbots_openbots-studio-demo-support-for-tag-ui-activity-6788174021964943361-RrUD), [Robocorp](https://youtu.be/HAfQpNZVbKI)

### Programming languages
- TagUI in Python - https://github.com/tebelorg/RPA-Python
- Python on cloud - https://colab.research.google.com/drive/13bQO6G_hzE1teX35a3NZ4T5K-ICFFdB5?usp=sharing
- Python on Docker - https://hub.docker.com/r/openiap/nodered-tagui
- TagUI in C# .NET - https://www.nuget.org/packages/tagui
- TagUI in Go - [work-in-progress by Suhail Ahmed from Dubai](https://www.linkedin.com/posts/kensoh_hi-fans-of-go-programming-language-would-activity-6804658389772324864-_OgH)

# How to get started?

My team-mate Ruth is now working on free video courses for TagUI. For now, following are useful resources to get started. The Telegram group chat and weekly Zoom meeting are especially useful resources if you have any questions to ask. Folks from community will also share their experiences. The community forum is a great place to share your RPA workflows.

### Useful references
- Slide deck - https://docs.google.com/presentation/d/1pltAMzr0MZsttgg1w2ORH3ontR6Q51W9/edit?usp=sharing&ouid=115132044557947023533&rtpof=true&sd=true
- Intro webinar -  https://www.youtube.com/watch?v=hKc4eNBhMws
- Technical webinar - https://www.youtube.com/watch?v=C5itbB3sCq0
- Podcast - https://botnirvana.org/podcast/tagui/
- Usage guide -  https://tagui.readthedocs.io/en/latest/  
- Simple examples -  https://github.com/kelaberetiv/TagUI/tree/master/flows/samples
- Advance examples - https://github.com/aimakerspace/TagUI-Bricks

### Community channels
- Community forum - https://community.aisingapore.org/groups/tagui-rpa/forum
- Weekly Zoom Q&A - [Thursday 4-5pm SGT (UTC+8)](https://github.com/kelaberetiv/TagUI/issues/914)
- Telegram group chat -  https://t.me/rpa_chat

# Usage guide walkthrough
### Documentation - https://tagui.readthedocs.io/en/latest

# Some requested scenarios
### Auto-resize image snapshots
```
// for example when using an image which is double the size,
// the following will search for half that size on screen
// and the setting applies to the rest of the workflow
vision Settings.AlwaysResize = 0.5
```

### OCR using SikuliX in TagUI
```
// first, open the PDF file in one of the following ways
dclick pdf_icon.png (desktop)

run cmd /c start c:\folder\filename.pdf (Windows)

run open /Users/folder/filename.pdf (macOS)

keyboard step to search for the file and open

// second, apply OCR using one of the following ways 
read pdf_frame.png to ocr_text

read (200,200)-(600,600) to ocr_text

hover anchor.png
x = mouse_x()
y = mouse_y()
x1 = x + 100
y1 = y + 100
x2 = x + 500
y2 = y + 500
read (`x1`,`y1`)-(`x2`,`y2`) to ocr_text

// optionally do data cleaning or extraction
ocr_text = del_chars(ocr_text, '\t\r')
name = get_text(pdf_text, 'Name:', 'State:')

// finally, save result to a text file
dump `ocr_text` to ocr_result.txt

// note that for text PDF instead of scanned PDF,
// you can copy to clipboard instead of using OCR
keyboard [ctrl]a
keyboard [ctrl]c
ocr_text = clipboard()
```

### Passing variables to SikuliX
```
// by using vision_step() function to form the actual command in SikuliX
info_text = 'abc'
vision_step('info_text = "' + info_text + '"');

info_number = 123
vision_step('info_number = ' + info_number);

// saving and reading from a file is possible but need more lines of code
```

### Returning variables from SikuliX
```
// save variable to output file using
vision info_text = info_text + 'def'
vision output_sikuli_text(info_text)

// get variable from output file using
some_variable = fetch_sikuli_text()
echo `some_variable`
```

### Convert PDF table to Excel
```
// TagUI's Python integration lets you access Python packages
// check out this Python package that can do this very well
https://github.com/atlanhq/camelot

// you can run Python code in TagUI with the py step
py a = 1
py b = 2
py c = a + b
py print(c)
echo `py_result`

// or use py begin and py finish for Python code blocks
py begin
a = 1
b = 2
c = a + b
print(c)
py finish
echo `py_result`

// you can also run a certain Python script in 1 line
py exec(open("full path to Python file").read())

// alternatively, use Python version of TagUI directly
https://github.com/tebelorg/RPA-Python
```

### Enter Excel data to website
```
// see example in human language TagUI, this can also be done in MS Word and Excel
https://github.com/aimakerspace/TagUI-Bricks/blob/master/IMDA-ICMS-CITREP/README.md

// see example in Python version of TagUI, entering Excel data for RPA Challenge
https://github.com/tebelorg/RPA-Python/issues/120#issuecomment-610518196
```

### Shopee search to Telegram
```
// go to Shopee website
https://shopee.sg

// interact with the website using XPath, attributes or computer vision
// for XPath, it can be found easily using SelectorsHub Chrome extension

// search for item in search bar and click search button
type //input as gryphon osmanthus sencha
click //button[@type='button']

// grab and form the URL of first result from the search
read (//*[@class = "col-xs-2-4 shopee-search-item-result__item"])[1]//@href to sub_link
full_link = 'https://shopee.sg/' + sub_link

// send telegram notification message with link to result
telegram 1234567890 `full_link`
```
