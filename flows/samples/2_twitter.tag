https://twitter.com/DalaiLama
// visits the webpage

hover ProfileAvatar
// hovers over the 'ProfileAvatar' element, so that a label appears

wait 2.5
// waits for 2.5 seconds so you can see the label

read ProfileHeaderCard-nameLink to profile_name
// saves the text from the 'ProfileHeaderCard-nameLink' element and calls it 'profile_name'

read ProfileHeaderCard-screennameLink to profile_id
// saves the text from the 'ProfileHeaderCard-screennameLink' element and calls it 'profile_id'

echo profile_name 's handle is ' profile_id
// shows the text in the command line output. Note how we use 'profile_name' and 'profile_id'
// we put "double quotes" or 'single quotes' around text we want to use directly

dump profile_name 's handle is ' profile_id '\n' to profile.txt
// dump saves the text to a file in your flow's folder
// it overwrites an existing file with the same name

https://twitter.com/RoyalFamily

read ProfileHeaderCard-nameLink to profile_name
read ProfileHeaderCard-screennameLink to profile_id

write profile_name 's handle is ' profile_id to profile.txt
// write is the same as dump but it appends to the file if the file already exists 