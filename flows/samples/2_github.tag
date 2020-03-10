// This flow visits a github page, reads some info from the page and makes a download

// Visit the webpage
https://github.com/kelaberetiv/TagUI

// Save the text from the element using XPath and calls it 'license_type', then 
// show it on the console
// Learn XPath: https://www.w3schools.com/xml/xpath_intro.asp
read (//*[@class="numbers-summary"]/li)[5] to license_type
echo `license_type`

// Look on the web page for an element with 'Clone or download'/'Download ZIP' in their text and click it
// This triggers a download to the current flow's folder
click Clone or download
click Download ZIP

// Wait for a default 5 seconds to give the download time to complete 
wait
