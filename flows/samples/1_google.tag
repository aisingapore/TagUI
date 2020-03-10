// This flow makes a search on google, clicks the first result and screenshots the page

// First, visit google.com
https://www.google.com/

// Looks on the web page for an element with 'search-box' in their 
// text, id or name (or some other attributes) and then types 'github' into it
type q as latest movies[enter]

// Click the first result using XPath
// Learn XPath: https://www.w3schools.com/xml/xpath_intro.asp
click (//div[@class="r"]/a)[1]

// Wait 3 seconds so the page can load
wait 3

// Save a screenshot of the web page as results.png
snap page to results.png
