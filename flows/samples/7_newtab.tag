// This flow shows how to interact with webpages which open in a new tab when you visit a link

// Visit the web page
http://tebel.org

// Clicking the ABOUT footer here opens a new tab window
click ABOUT

// about_tebel appears in the url of the new tab, so "popup about_tebel" tells TagUI 
// to do this step (ie. look for "file-about_tebel-LC2") within the new tab
popup about_tebel
	show file-about_tebel-LC2
