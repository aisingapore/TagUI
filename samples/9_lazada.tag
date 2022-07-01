// This flow makes a search on Lazada and saves the top 40 results to a CSV file

// visit www.lazada.sg (www.lazada.sg without https:// works as well)
https://www.lazada.sg/

// Look on the web page for an element with 'q' in its text, id or name
// (or some other attributes), then type 'oat milk' into the textbox
type q as oat milk

// Click on the search icon (that web element contains the text SEARCH)
click SEARCH

// Use dump step to create a blank CSV file with just the header
dump #,Item,Price,Link to Oat Milk.csv

// Use a for loop to repeat for the 40 records on search results
for n from 1 to 40
    // Read the item description, price and URL link to variables
    // Learn XPath: https://www.w3schools.com/xml/xpath_intro.asp
    // XPath Cheatsheet: https://www.linkedin.com/posts/kensoh_xpath-rpa-tagui-activity-6829673864633704448-Iw-D
    read (//*[@data-qa-locator="product-item"])[`n`]//img/@alt to item
    read (//*[@data-qa-locator="product-item"])[`n`]//*[contains(text(),"$")] to price
    read (//*[@data-qa-locator="product-item"])[`n`]//a/@href to partial_link

    // Add the 'https:' prefix to the incomplete links extracted
    link = 'https:' + partial_link

    // Show the values of the current record using echo step
    echo `n`,`item`,`price`,`link`

    // Use write step to append current record to CSV file
    write `csv_row([n, item, price, link])` to Oat Milk.csv
