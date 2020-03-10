// This flow demonstrates the usage of conditions

// Assign the string 'dog, cat' to the variable 'animals' 
// Assign the number 12 to the variable 'cupcakes'
animals = 'dog, cat'
cupcakes = 12

// If the condition is true, then show 'animals' value in the command line output, or else do nothing
if animals equals to 'dog, cat'
	echo animals

if animals contains 'dog'
	echo contains dog
	echo another instruction

// If the condition is true, then show 'some instructions' in the command line output
// or else check if the second condition is true, then show 'other instructions'
// or else show 'yet other instructions'
some_number = 1
if some_number equals to 1
	echo some instructions
else if some_number equals to 2
	echo other instructions
else
	echo yet other instructions

// Other examples of comparisons below work as you expect
// not equals to
// lesser than
// greater than or equals to 