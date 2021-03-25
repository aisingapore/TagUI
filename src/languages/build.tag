https://translate.google.com/

// this TagUI automation flow 'self-builds' language definitions given as parameter 1 using Google Translate
// more details on ISO-639-1 Code and Google Translate here - https://cloud.google.com/translate/docs/languages

// define constants of vocabulary list
vocab_list = '|click|tap|move|hover|type|enter|select|choose|read|fetch|show|print|save|echo|dump|write|snap|table|mouse|keyboard|live|download|upload|load|receive|frame|popup|wait|timeout|seconds|second|api|dom|js|vision|else if|else|if|for|while|check|more than or equals to|more than or equal to|greater than or equals to|greater than or equal to|higher than or equals to|higher than or equal to|less than or equals to|less than or equal to|lesser than or equals to|lesser than or equal to|lower than or equals to|lower than or equal to|more than|greater than|higher than|less than|lesser than|lower than|not equals to|not equal to|equals to|equal to|not contains|not contain|contains|contain|and|or|from|to|as|title()|url()|text()|timer()|count()|present()|visible()|mouse'+'_xy()|mouse'+'_x()|mouse'+'_y()|'

// convert vocabulary list into array
vocab_array = vocab_list.split('|')

// set ISO-639-1 Code from parameter 1
ic = p1

//show info (useful when quiet option is used)
echo PROCESSING NOW - `ic`

// create language .csv header
dump EN,`ic.toUpperCase()`\r\n to `ic.toLowerCase()`.csv

echo `vocab_array.length-2`
// loop and process array
for number from 1 to vocab_array.length-2
	https://translate.google.com.sg/#en/`ic`/`vocab_array[number]`
	// time delay to slow down translation speed, adjust accordingly
	wait 2 seconds
	read //*[@lang="`ic`"]/span/span to translation
	echo `vocab_array[number]` - `translation.toLowerCase().replace(' (','(')`
	write `vocab_array[number]`,`translation.toLowerCase().replace(' (','(')` to `ic.toLowerCase()`.csv

//show info (useful when quiet option is used)
echo PROCESSING DONE - `ic`\n
