`user_url`
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

// when running TagUI, specify datatable csv to use, eg tagui 6B_datatables url_data.csv
// TagUI loops through each row to run automation using the data from different rows
// for example, echo "URL - `user_url`" in your flow shows URL - https://github.com/s0b0lev
// echo '`[iteration]`' can be used in your flow to show the current iteration number
// a file tagui_datatable.csv will be created in tagui/src folder for TagUI internal use

// contents of url_data.csv // with 3 random Telegram GitHub userids as learning examples
// #,user_url
// 1,https://github.com/s0b0lev
// 2,https://github.com/zongUMR
// 3,https://github.com/jkmartindale

// now here's the actual flow, it goes to their profile URLs and grabs their contact information

// JavaScript can be used as needed to create more complex automation flows

// define blank variables so errors won't be thrown for profiles with missing info
fullname = ""; username = ""; email = ""; url = ""; country = ""; bio = "";

// variables can also be set without ; by using variable_name = "value" on a single line
// to assign text to a variable use quotes (" or '), for numbers quotes are not needed

// read User Full Name and GitHub ID to variables
read fullname to fullname
read username to username

// read User Email to variable if information exists
if present('//*[@itemprop="email"]')
read //*[@itemprop="email"] to email

// read User Website to variable if information exists
if present('//*[@itemprop="url"]')
read //*[@itemprop="url"] to url

// read User Location to variable if information exists
if present('//*[@itemprop="homeLocation"]')
read //*[@itemprop="homeLocation"] to country

// read User Biography to variable if information exists
if present('user-profile-bio')
read user-profile-bio to bio

// show the information retrieved to screen and log file
echo fullname "," username "," email "," url "," country "," bio

// write the information received into a csv spreadsheet file
// set file to appropriate location, especially if using Windows
var fs = require('fs'); fs.write("/tmp/contacts.csv", "\""+fullname+"\",\""+username+"\",\""+email+"\",\""+url+"\",\""+country+"\",\""+bio+"\"\n", 'a');
