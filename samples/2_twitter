https://twitter.com/tebelorg
// automation flow files usually start with an URL to tell TagUI where to go
// files can also start with // for comments, or no URL if it's not web-related

// this flow goes to a Twitter page and saves some profile information
// for issues or questions, kindly feedback on GitHub or ksoh@aisingapore.org
// see cheatsheet for steps, conditions, finding element identifiers, etc
// https://github.com/kelaberetiv/TagUI#cheat-sheet

// use hover step to hover cursor over a webpage element
// below step hovers over profile image to test a pop-up label
hover ProfileAvatar

// wait a few seconds for pop-up label to verify hover works
// default wait is 5 seconds and you can also use decimal
// wait 7.5 seconds or 3s or 5sec or 10 secs will work 
wait 2.5

// use read step to fetch webpage element text to variable 
read ProfileHeaderCard-nameLink to profile_name
read ProfileHeaderCard-screennameLink to profile_id

// use echo step to print text (in quotation marks) and variables to output
echo "Profile Name - " profile_name " (using double quotes for text)"

// either double or single quotes can be used to denote text from variables
echo 'Profile Name - ' profile_name ' (using single quotes for text)'

// if you are a JavaScript person, you can also use + or write directly in JS
echo "Profile Name - " + profile_name + " (using + as separator instead of only space)"
this.echo("Profile Name - "+profile_name+" (directly write using JavaScript code)");

// combining multiple variables and text together into a single line
echo "Profile Name - " profile_name ", Profile ID - " profile_id " (using multiple variables)" 

// use dump step to save text (in quotation marks) and variables to a file 
// it is similar to echo step except that output is to a file not screen
dump "Profile Name - " profile_name ", Profile ID - " profile_id " (using multiple variables)"

// data dumps are automatically named textXXXX.txt
// unless you provide a filename for the data dump
dump "Profile Name - " profile_name ", Profile ID - " profile_id " (using multiple variables)" to profile.txt

// use save step if you want to save text from an element to file without further handling
save ProfileHeaderCard-screennameLink

// saved texts are automatically named textXXXX.txt
// unless you provide a filename for the saved text
save ProfileHeaderCard-screennameLink to profile_id.txt

// write step appends to file instead of overwriting file (eg dump step)
write 'line 1' to text_test.txt
line_number = 2
write 'line ' line_number to text_test.txt

// load step loads file content into specified variable
load text_test.txt to file_content
echo 'showing file content of text_test.txt'
echo file_content
