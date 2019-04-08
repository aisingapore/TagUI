https://ca.finance.yahoo.com
// automation flow files usually start with an URL to tell TagUI where to go
// files can also start with // for comments, or no URL if it's not web-related

// this flow shows how to use check step assertions for CI/CD integration
// for issues or questions, kindly feedback on GitHub or ksoh@aisingapore.org
// see cheatsheet for steps, conditions, finding element identifiers, etc
// https://github.com/kelaberetiv/TagUI#cheat-sheet

// use check step for testing of conditions (text in quotation marks, either " or ' works)
read uh-tb-home to header_home_text
check header_home_text equals to "Home" | "header text is correct" | "header text is wrong"

// when TagUI is run with test option, check step automatically performs test assertions
// test assertions are used to track if conditions to be met in a test scenario are all met
// the test assertions will give CasperJS the details needed to output a XUnit XML file,
// which is compatible with continuous integration tools such as Jenkins (for CI/CD)

// check whether the search button exists
check present('//*[@id="search-button"]') | 'search button exists' | 'search button does not exist'

// check whether the search box is visible, using its @aria-label attribute - Search
// something can exists on webpage but set as hidden or not showing in its property
check visible('Search') | 'search box is visible' | 'search box is not visible'

// check whether the header home text is correct
// similar to earlier example above but with output of header home text
read uh-tb-home to header_home_text
check header_home_text equals to 'Home' | 'text is correct - ' header_home_text | 'text is wrong - ' header_home_text

// check that the page url has yahoo in it (\' can be used to escape the single quote)
check url() contains 'yahoo' | 'url contains \'yahoo\'' | 'url does not contain \'yahoo\''

// check that the page title has Finance in it (\' can be used to escape the single quote)
check title() contains 'Finance' | 'title contains \'Finance\'' | 'title does not contain \'Finance\''

// check that the page has the text My Portfolio (double quotes can also be used for text)
check text() contains "My Portfolio" | "page text contains My Portfolio" | "page text does not contain My Portfolio"

// check that number of header menu items are more than or equals to 6
// spaces between check step separator | is optional and up to you
check count('uh-tb-') more than or equals to 6 | 'header menu items >= 6' | 'header menu items < 6'

// you can also assign to a variable for use or further processing
count_number = count('uh-tb-')
echo 'count_number = ' count_number

// helper functions can also be used in if conditions for decision-making
// for eg - present(), visible(), count(), url(), title(), text(), timer()
// csv_row() is useful for writing csv data, see HELPER FUNCTIONS in readme

// use timer() as a stopwatch to track elapsed time between calls
start = timer()

// make some navigational steps, by searching for microsoft
type Search as microsoft
click //*[@id="search-button"]

// use timer() as a stopwatch to track elapsed time between calls
time_taken = timer()
echo 'time taken - ' time_taken ' seconds'

// by using timer(), the time taken for some actions can also be used as testing criteria
check time_taken < 60 | 'step takes lesser than 60 seconds' | 'step takes more than 60 seconds'

// delay for many seconds in case network is slow for yahoo finance search result to return
// default timeout is 10 seconds in tagui_config.txt (can also be changed using timeout step)
wait 13.57 seconds

// after the delay, negative test case to check that the quote is APPLE
read //*[@id="quote-header-info"]//h1 to stock_quote
echo 'intentional negative test case, to see if quote returned contains APPLE'
check stock_quote contains "APPLE" | 'quote is correct - ' stock_quote | 'quote is wrong - ' stock_quote

// CasperJS will output a XUnit XML, which is compatible with continuous integration tools such as Jenkins 
// running together with the report option outputs a web version of the test execution log for easy sharing
// you can use datatables for batch testing using different data sets to test comprehensively for regression
// check datatables section of cheatsheet or automation flow sample /src/samples/6_datatables/6C_datatables
