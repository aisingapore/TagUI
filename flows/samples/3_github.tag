https://github.com/kelaberetiv/TagUI
// visits the webpage

read (//*[@class="numbers-summary"]/li)[5] to license_type
// saves the text from the element using XPath and calls it 'license_type'
// https://www.w3schools.com/xml/xpath_intro.asp

click Clone or download
click Download ZIP
// looks on the web page for an element with 'Clone or download'/'Download ZIP' in their text and click it
// this triggers a download to the current flow's folder

wait
// wait for a default 5 seconds to give the download time to complete 