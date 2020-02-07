https://vk.com/kensoh
// automation flow files usually start with an URL to tell TagUI where to go
// files can also start with // for comments, or no URL if it's not web-related

// this flow shows using repositories on Russian social media site VK.com
// for issues or questions, kindly feedback on GitHub or ksoh@aisingapore.org
// see cheatsheet for steps, conditions, finding element identifiers, etc
// https://github.com/kelaberetiv/TagUI#cheat-sheet

// please note that in these comments, the symbol ` is replaced with '
// otherwise TagUI will try to interpret it as repository definition

// repositories help to make objects or steps reusable and improve readability
// TagUI will look for a local repository file tagui_local.csv in the same folder of flow
// TagUI will also look for a global repository file tagui_global.csv in tagui/src folder
// repositories can be used with csv datatable, which is specified as TagUI run option

// using 'object' in your flow replaces it with its definition (which can contain objects)
// for example during execution, 'click link' becomes click //*[@id="profile_full"]//a
// if you have watched Inception movie, this is inception level 2 (capped at max 2 for now)
// don't worry about mistakes, TagUI is usually able to tell you which line has what error

// contents of tagui_local.csv
// OBJECT,DEFINITION
// email,quick_email
// password,quick_pass
// user_email,mickey_mouse@disney.com
// user_password,iloveminnie4eva
// show info,Show full information
// click link,click 'facebook_link'
// facebook_link,//*[@id="profile_full"]//a

// enough intro, let's go for it
type `email` as `user_email`
type `password` as `user_password`

// but we are not going to click Sign up and spam VK
// so let's use some more repository features and go to FB

// hover and click on the show info link, adding wait to see effect of action
hover `show info`
wait 2.5
click `show info`
wait 2.5

// hover and click on the facebook link, adding wait to see effect of action
hover `facebook_link`
wait 2.5
`click link`
wait

// flow execution output is saved to 5_repositories.log for reference / processing
// for developers and tinkerers, the generated JavaScript file is 5_repositories.js 
