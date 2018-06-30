https://ca.yahoo.com
// automation flow files usually start with an URL to tell TagUI where to go
// files can also start with // for comments, or no URL if it's not web-related

// this flow searches github on Yahoo and captures screenshot of results
// for issues or questions, kindly feedback on GitHub or ksoh@aisingapore.org
// see cheatsheet for steps, conditions, finding element identifiers, etc
// https://github.com/kelaberetiv/TagUI#cheat-sheet

echo 'NOTE - this sample may not work for EU users due to website changes for GDPR'
echo 'EU users may have to modify script to first click on announcement OK button'
echo ''

// lines which begin with // means user comments, which are ignored during execution

// use live step to try steps or code interactively for Chrome / visual automation
// this helps accelerate automation script development as you can test each step
// live

// use ask step to ask user a question for input, reply will be stored in ask_result
// after that you can use ask_result variable in other steps such as echo or type
// ask what is your user password?

// use type step to enter some text into a webpage element
// use show step to print text from webpage element to output
// below steps enter text into the search box and print out the value
type search-box as github
show search-box

// use click step to click on a webpage element
// use rclick step to right-click on element
// use dclick step to double-click on element
// below step clicks the search button
click search-button

// wait a few seconds before capturing whole screenshot
// default wait is 5 seconds and you can also use decimal
// wait 7.5 seconds or 3s or 5sec or 10 secs will work 
wait 6.6

// use snap to save screenshot of webpage or elements
// use snap page to save screen shot of entire page
// use snap element to save screen shot of element
// for snap step to work correctly on Windows,
// make sure display magnification is set to 100%
snap page
snap logo

// image snaps are automatically named snapXXXX.png
// unless you provide a filename for the image snap
snap page to results.png
snap logo to logo.png

// to go to another URL from your flow, simply provide the URL
https://duckduckgo.com

// and then continue the automation flow from there
type search_form_input_homepage as The search engine that doesn\'t track you.
snap page to duckduckgo.png

wait 4.4 seconds
