// does the instructions within the curly braces {} 3 times
// the first time, 'n' has a value of 1, the second time, 'n' is 2, the third time, 'n' is 3
for n from 1 to 3
{
	https://www.ebay.com.sg/b/Consumer-Electronics-Mixed-Lots/259701/bn_7203680?_pgn=`n`
	// visits the url with a different 'n' value each time
	// we need to use backticks(`) here like `n` to tell tagui to use the variable 'n'

	read (//img[@class="s-item__image-img"])[1]/@src to out
	// saves the src attribute from the element (ie. the image link) using XPath and calls it 'out'

	echo out
	// show 'out' value in the command line output
} 