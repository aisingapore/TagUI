// This flow visits a GitHub page, reads some info from the page and makes a download

// Visit the webpage
https://github.com/kelaberetiv/TagUI

// Save the text from the element using XPath and calls it 'license_type',
// then show it on the console
// Learn XPath: https://www.w3schools.com/xml/xpath_intro.asp
read (//*[@class="mt-3"])[3] to license_type
echo `license_type`

// Look on the web page for an element with unique attributes provided
// Then click to download a file to the current flow's folder
click octicon-download
click octicon-file-zip

// Wait for a default 5 seconds to give the download time to complete 
wait
