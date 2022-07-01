// This flow visits a GitHub page, reads some info from the page and makes a download

// Visit the webpage
https://github.com/kelaberetiv/TagUI

// Save text from the element using XPath into variable license_type,
// then show the variable value on the console using echo step
// Learn XPath: https://www.w3schools.com/xml/xpath_intro.asp
// XPath Cheatsheet: https://www.linkedin.com/posts/kensoh_xpath-rpa-tagui-activity-6829673864633704448-Iw-D
read (//*[@class="Link--muted"])[3] to license_type
echo `license_type`

// Look for a web element with provided XPath, CSS or attributes
// Then click to download the file to the folder of current flow
click //get-repo 
click //*[contains(@href, "master.zip")]

// Wait 15 seconds to give the download time to complete on slow networks 
wait 15
