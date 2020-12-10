// This flow makes a search on Google, clicks the first result and screenshots the page

// First, visit google.com
https://www.google.com

// Look on the web page for an element with 'q' in its text, id or name
// (or some other attributes), then type 'latest movies' and enter
type q as latest movies[enter]

// Click the first result using XPath
// Learn XPath: https://www.w3schools.com/xml/xpath_intro.asp
click (//*[@class="g"])[1]//a

// Wait 3 seconds so the page can load
wait 3

// Save a screenshot of the web page to top_result.png
snap page to top_result.png
