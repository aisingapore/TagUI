https://ca.yahoo.com
// same as 1_yahoo example but written in chinese

// to run this, edit tagui/src/tagui_config.txt
// change the following line - 
// var tagui_language = 'english';
// to below, to choose input language as chinese
// var tagui_language = 'chinese';

// during automation, the tagui_language variable can
// be changed to show output in different languages
tagui_language = 'chinese'

// try setting output to other languages, full list here
// https://github.com/kelaberetiv/TagUI#native-languages
// tagui_language = 'english'
// tagui_language = 'french'

输入 search-box 为 github
显示 search-box
点击 search-button

等待 6.6

number = 1
如果 number 多过或等于 1
快照 page

echo ''
echo '~ changing output language to english ~'
tagui_language = 'english'

让 n 从 1 到 3
快照 logo

text = 'abcde'
如果 text 包括 'bcd'
快照 page 到 results.png

echo ''
echo '~ changing output language to french ~'
tagui_language = 'french'

https://duckduckgo.com

输入 search_form_input_homepage 为 The search engine that doesn\'t track you.
快照 page 到 duckduckgo.png

echo ''
echo '~ changing output language to japanese ~'
tagui_language = 'japanese'

等待 4.4 秒