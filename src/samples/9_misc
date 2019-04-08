http://tebel.org/index_mobile.php
// automation flow files usually start with an URL to tell TagUI where to go
// files can also start with // for comments, or no URL if it's not web-related

// this flow shows how to use steps popup, frame, dom, js, { and } block
// for issues or questions, kindly feedback on GitHub or ksoh@aisingapore.org
// see cheatsheet for steps, conditions, finding element identifiers, etc
// https://github.com/kelaberetiv/TagUI#cheat-sheet

// click ABOUT footer to open a new tab window
click ABOUT

// the execution context is still at the original window
// in order to interact with the new tab window use popup step
// the popup step looks for a keyword in the urls within the
// list of tabs that you have opened in this session of tagui
// after the next step, context returns to original window
// indentation within the popup step block is optional
popup about_tebel
{
	show file-about_tebel-LC2
	snap file-about_tebel-LC2
}

// for frame step it is used to access webpage implemented as a frame within another webpage
// the usage is similar to popup step, except that the parameter is the frame name
// if there is a subframe within the mainframe, use frame mainframe | subframe

// variables can be created using read step or manually defined
dummy_variable = 'original'
dummy_number = 123456789

// adding a blank line for output clarity, you can use either " or ' for text
echo ''
echo 'returning to ' dummy_variable ' window context'

// after the steps in popup context are completed, context returns to original window
show (//*[@id="header"]//a)[1]
// above is XPath selector, an expressive way of identifying web elements

// default execution context is local, webpage dom (document object model) is insulated
// to use JavaScript code to access webpage dom for direct manipulation, use dom step
dom document.querySelector('#header p').focus()
// above is CSS selector. TagUI supports both CSS and XPath selectors

// by using return in the step, result can be returned to dom_result variable
dom return document.querySelector('#header p').innerText
echo 'dom_result from 1 JavaScript statement - ' dom_result

// multiple lines can be combined into a single statement before returning value
dom document.querySelector('#header p').blur(); return document.querySelector('#header p').innerText
echo 'dom_result from multiple statements - ' dom_result

// passing 1 or more variables to dom step can be done using dom_json variable
// this is used instead of `variable` due to dom communication constraints
// example of passing 1 variable to dom step for use in dom
dom_json = '#header p'
dom return document.querySelector(dom_json).innerText
echo 'dom_result using 1 dom_json variable - ' dom_result

// example of passing multiple variables to dom step for use in dom
element1 = '#header p'
element2 = '#footer'
// JavaScript syntax to create object and assign variables to children
dom_json = {element1: element1, element2: element2}
dom return document.querySelector(dom_json.element1).innerText
echo 'dom_result using multiple dom_json variables - ' dom_result
dom return document.querySelector(dom_json.element2).innerText
echo 'dom_result using multiple dom_json variables - ' dom_result
echo ''

// TagUI recognizes lines of JavaScript code automatically
// if some code is not recognized, use step js to explicitly indicate
// alternatively, modify logic in tagui_parse.php or raise an issue
test_number = 123
echo 'test_number - ' test_number
js test_number = 456
echo 'test_number - ' test_number
echo ''

// the step { and } helps to define step/code block to group step/code together
// earlier above it has been used for the popup step, it can also be used for frame step

// go to another web page with a table to practice for loop
https://github.com/tebelorg/TLE

// you can choose to have ; or don't have ; after variable assignment
test_string = "ABC";
echo 'test_string - ' test_string

// for loop can be expressed in natural language or JavaScript
for column from 1 to 6
{
	// `variable_name` can be used where text is expected
	show (//table)[3]//td[`column`]
	// JavaScript code can also be used in the loop
	test_string = "DEF";
}

echo 'test_string - ' test_string

