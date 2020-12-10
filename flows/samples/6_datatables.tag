// This flow demonstrates usage of datatables to run a flow multiple times, once per line in a csv
// IMPORTANT: run this sample with 'form_data.csv' behind, 
// like 'tagui 6_datatables.tag form_data.csv'

// TagUI runs this flow once for each data row in 'form_data.csv',
// using the variable values in that row.

// Visit the web page
https://www.w3schools.com/html/html_forms.asp

// Clear the input and then type the 'firstname'/'lastname'
// from the right row in 'form_data.csv'
type firstname as [clear]`firstname`
type lastname as [clear]`lastname`

click submit

// TIP: if you want to skip restarting of Chrome after each time,
// run with tagui 6_datatables.tag form_data.csv -speed
// or tagui 6_datatables.tag form_data.csv -s
