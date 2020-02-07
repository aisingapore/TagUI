https://medium.com/tebelorg
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

// when running TagUI, specify datatable csv to use, eg tagui 6C_datatables user_data.csv
// TagUI loops through each row to run automation using the data from different rows
// for example, echo "GitHub ID - `GITHUB_ID`" in your flow shows GitHub ID - ironman
// echo '`[iteration]`' can be used in your flow to show the current iteration number
// a file tagui_datatable.csv will be created in tagui/src folder for TagUI internal use

// contents of user_data.csv
// #,GITHUB_ID,USER_EMAIL
// 1,ironman,tony@stark.org
// 2,batman,bruce@wayne.org
// 3,superman,clarke@kent.org 

// now here's the actual flow, it uses part of the information from 6B to email using API

// JavaScript can be used as needed to create more complex automation flows

// set respective User IDs and User Emails and display to screen and log file
// ; or no ; after assignment of variable is up to you, both works for TagUI
github_id = "`GITHUB_ID`"
user_email = "`USER_EMAIL`";
echo github_id " - " user_email

// only display the api call to screen, we are not going to actually email the superheroes
this.echo('api your_website_url/mailer.php?APIKEY=unique_key&SUBJECT=Hi @'+github_id+'&SENDTO='+user_email+'&SENDFROM=Your Name <your_email@gmail.com>&MESSAGE=Your message line 1.<br><br>Your message line 2.');

// how api step is used to send email with 1 line, in this case using Tmail (another open-source project from TA)
// api your_website_url/mailer.php?APIKEY=unique_key&SUBJECT=Hi @`github_id`&SENDTO=`user_email`&SENDFROM=Your Name <your_email@gmail.com>&MESSAGE=Your message line 1.<br><br>Your message line 2.

// response and result from the api call will be printed to screen and log, below is sample response from Tmail
// OK - Hi @ironman mail sent successfully to tony@stark.org

// wait a few seconds to reduce risk of network police flagging your mail server as bad actors and blocking you
wait 3 seconds
