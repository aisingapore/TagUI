// This flow demonstrates the use of object repositories, ie. storing values in tagui_local.csv

// Visit the web page
https://vk.com/kensoh

// Look in tagui_local.csv in the flow's folder for a value for 'email' and finds that element
// if that doesn't exist, then it looks into tagui_global.csv in the tagui/src folder
// and tries the same thing, so when the flow is run, it will become 
// type quick_email as mickey_mouse@disney.com
type `email` as `user_email`

type `password` as `user_password` 
