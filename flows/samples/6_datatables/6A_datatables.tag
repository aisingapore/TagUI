https://github.com/telegramdesktop/tdesktop/stargazers?page=`page_no`
// automation flow files usually start with an URL to tell TagUI where to go
// files can also start with // for comments, or no URL if it's not web-related

// this set of flows uses datatables to retrieve/act on info from GitHub
// 6A - get URLs of Starrers, 6B - get contact info, 6C - email Starrers
// for issues or questions, kindly feedback on GitHub or ksoh@aisingapore.org
// see cheatsheet for steps, conditions, finding element identifiers, etc
// https://github.com/kelaberetiv/TagUI#cheat-sheet

// CONTEXT - When TagUI beta was released, someone shared to Hacker News
// and GitHub stars for the project jumped from 2 to 1.5k in a week or so.
// I used this to send a one-time thank you email to all TagUI starrers,
// but had since stopped using this as it is unsolicited and feels spammy.

// when running TagUI, specify datatable csv to use, eg tagui 6A_datatables page_data.csv
// TagUI loops through each row to run automation using the data from different rows
// for example, echo "PAGE NUMBER - `page_no`" in your flow shows PAGE NUMBER - 1
// echo '`[iteration]`' can be used in your flow to show the current iteration number
// a file tagui_datatable.csv will be created in tagui/src folder for TagUI internal use

// contents of page_data.csv
// #,page_no
// 1,1
// 2,2
// 3,3

// now here's the actual flow, it grabs URLs of first 3 pages of Telegram Starrers 

// first take a snapshot of repository name, concurrently making sure page is loaded
snap .public

// JavaScript can be used directly in the flow for more expressive and advanced automation
// default execution context for JavaScript is local, to work on webpage dom, use dom step
// following loop through directly in webpage dom to compile and return list of profile ids
dom id_list = ""; for (n=1;n<=51;n++) {id_list += document.querySelector('.follow-list-item:nth-child('+n+') a').href + '\n'}; return id_list; 
echo dom_result

// you can also use JavaScript to do post-processing and write to a file, or to a format directly usable by flow 6B
// var fs = require('fs'); fs.write("/tmp/urls.csv", dom_result + "\n", 'w');
