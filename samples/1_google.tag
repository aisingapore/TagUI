// This flow makes a search on Google, clicks the first result and screenshots the page

// First, visit google.com (www.google.com without https:// works as well)
https://www.google.com

// Look on the web page for an element with 'q' in its text, id or name
// (or some other attributes), then type 'latest movies' and enter
// type q as latest movies[enter]

// Use a more accurate identifier below instead because
// google.com webpage differs for different locations
type //*[@name="q"] as latest movies[enter]

// Click first result using XPath, an identification method
// Learn XPath: https://www.w3schools.com/xml/xpath_intro.asp
// XPath Cheatsheet: https://www.linkedin.com/posts/kensoh_xpath-rpa-tagui-activity-6829673864633704448-Iw-D
click (//*[@class="yuRUbf"])[1]//a

// Wait for 3 seconds so the page can load (wait 3 seconds works as well)
wait 3

// Save a screenshot of the web page to top_result.png
snap page to top_result.png
