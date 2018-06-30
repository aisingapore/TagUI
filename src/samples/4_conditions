https://www.google.com.sg
// automation flow files usually start with an URL to tell TagUI where to go
// files can also start with // for comments, or no URL if it's not web-related

// this flow goes through examples of using conditions in natural language
// for issues or questions, kindly feedback on GitHub or ksoh@aisingapore.org
// see cheatsheet for steps, conditions, finding element identifiers, etc
// https://github.com/kelaberetiv/TagUI#cheat-sheet

// conditions involve comparing of variables and values to determine what to do next
// variables can be defined from read step, or self-declared / from files using JavaScript
// write text in quotation marks (either " or ' works) to differentiate from variable names

// declare some variables for use in this sample flow, text in quotation marks
menu = "salads fruits sandwiches"
cupcakes = 12

// conditions can be expressed in natural language (optional brackets) or JavaScript
// for conditions such as contain / equal, you can use with or without s behind
// the natural language can be further improved in future to understand context

if menu contains "fruits"
echo "Menu contains fruits. (condition and action in natural language)"

if (menu.indexOf("fruits")>-1)
echo "Menu contains fruits. (condition in JavaScript, action in natural language)"

if menu equals to "salads fruits sandwiches"
echo "This is perfect."

if menu not equals to "salads fruits sandwiches" and menu contains "salads"
echo "This is not too bad."

// combination of different conditions as a single condition to check
if menu contains "fruits" and menu not contains "meat"
echo "Menu contains fruits and no meat."

if menu contains "cheese" or menu contains "fish" or menu contains "meat"
echo "Menu is not vegan-friendly."

// some examples of working with numbers in conditions, see cheatsheet for more details  
// support fairly expressive ways to express numeric conditions in natural language

if cupcakes lesser than 6
echo "Time to restock cupcakes."

if cupcakes greater than or equals to 12
echo "That is a lot of cupcakes on the table."

if cupcakes higher than or equal to 24
echo "That is really a lot of cupcakes on the table."

if cupcakes equals to 12
echo "Just nice to fit in a box."

if cupcakes more than 1 and cupcakes lesser than 3
echo "Just nice for you and me."

// this part used to be click icon but sometimes there is captcha
// change example to capturing snapshot of the webpage instead

// natural language version of a for loop
for n from 1 to 3
{
snap page
}

// JavaScript version for multiple actions
for (n=1; n<=3; n++)
{
snap page
}

// another example of repeating an action many times
for Product_ID from 2 to 5
echo "Current Product ID is - " Product_ID

// while condition is tricky and deprecated in TagUI v4.0
// to loop 'indefinitely' use this - for n from 1 to infinity
// this loops 1024 times as infinity variable is pre-set to 1024
// complex nested loops with if blocks inside are supported
// indentation or no indentation both work in loops

// use break to break out of the immediate loop
for n from 1 to infinity
{
	echo n

	if n greater than or equals to 5
	break
}

// use continue to continue to the next iteration
for n from 1 to 5
{
if n equals to 3
continue

echo n
}

// example of using if, else if, else to decide what to do base on conditions
// example does not interact with elements because facebook page might change

a = 1
if a equals to 1
{
	// do some step(s)
	snap page
}
else if a equals to 2
{
	// do some other step(s)
	snap page
}
else
{
	// do some other steps(s)
	snap page
}
