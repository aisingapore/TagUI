// This flow demonstrates the usage of loops

// Do the instructions within the indentation 3 times
// the first time, 'n' has a value of 1, the second time, 'n' is 2, the third time, 'n' is 3
// Visit the URL with a different 'n' value each time
// We need to use backticks(`) here like `n` to tell tagui to use the variable 'n'

for n from 1 to 3
	https://www.ebay.com.sg/b/Consumer-Electronics-Mixed-Lots/259701/bn_7203680?_pgn=`n`
	// Save the src attribute from the element (ie. the image link) using XPath and call it 'out'
	read (//img[@class="s-item__image-img"])[1]/@src to out
	// Show the 'out' value in the command line output
	echo `out`
