// IMPORTANT: run this sample with 'form_data.csv' behind, 
// like 'tagui 7-datatables form-data.csv'
// tagui runs this flow once for each data row in 'form_data.csv', using
// the variable values in that row.

https://www.w3schools.com/html/html_forms.asp
// visits the web page

type firstname as [clear]`firstname`
type lastname as [clear]`lastname`
// clears the input and then types the 'firstname'/'lastname'
// from the right row in 'form_data.csv'

click submit 