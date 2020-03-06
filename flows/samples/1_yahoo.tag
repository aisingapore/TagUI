https://ca.yahoo.com
// visits the webpage

type search-box as github
// looks on the web page for an element with 'search-box' in their 
// text, id or name (or some other attributes) and then types 'github' into it

click search-button
// clicks the 'search-button' element

wait 2.5
// waits 2.5 seconds so the page can load

snap page to results.png
// saves a screenshot of the web page 

snap logo to logo.png
// saves a screenshot of the 'logo' element 