
// xpath for object identification
var x = require('casper').selectXPath;

// assign parameters to p1-p9 variables
var p1 = casper.cli.raw.get(0); var p2 = casper.cli.raw.get(1); var p3 = casper.cli.raw.get(2);
var p4 = casper.cli.raw.get(3); var p5 = casper.cli.raw.get(4); var p6 = casper.cli.raw.get(5);
var p7 = casper.cli.raw.get(6); var p8 = casper.cli.raw.get(7); var p9 = casper.cli.raw.get(8);

// save start time to measure execution time
var automation_start_time = Date.now(); casper.echo('\nSTART - automation started - ' + Date().toLocaleString());

// initialise time for timer() function
var timer_start_time = Date.now();

// initialise default global variables
var quiet_mode = false; var save_text_count = 0; var snap_image_count = 0;

// counters for tracking messages in sikuli and chrome integrations
var sikuli_count = 0; var chrome_id = 0;

// chrome context for frame handling and targetid for popup handling
var chrome_context = 'document'; var chrome_targetid = '';

// JSON variable to pass variables into browser DOM
var dom_json = {}; var dom_result = '';

// variable for advance usage of api step
var api_config = {method:'GET', header:[], body:{}};

// techo function for handling quiet option
function techo(echo_string) {if (!quiet_mode) {if (tagui_language.toLowerCase() == 'english') casper.echo(echo_string);
else {var translated_string = translate(echo_string,'to',tagui_language.toLowerCase()); casper.echo(translated_string);
if (translated_string.indexOf('ERROR - translation engine') !== -1) casper.exit();}} return;}

// for muting echo in test automation scripts
function dummy_echo(muted_string) {return;}

// for saving text information to file
function save_text(file_name,info_text) {var ds; if (flow_path.indexOf('/') !== -1) ds = '/'; else ds = '\\';
if (!file_name) {save_text_count++; file_name = flow_path + ds + 'text' + save_text_count.toString() + '.txt';}
var fs = require('fs'); fs.write(file_name, info_text, 'w');}

// for appending text information to file
function append_text(file_name,info_text) {var ds; if (flow_path.indexOf('/') !== -1) ds = '/'; else ds = '\\';
if (!file_name) {if (save_text_count==0) save_text_count++; // increment if 0, else use same count to append
file_name = flow_path + ds + 'text' + save_text_count.toString() + '.txt';}
var fs = require('fs'); fs.write(file_name, info_text + '\r\n', 'a');}

// for saving snapshots of website to file
function snap_image() {var ds; if (flow_path.indexOf('/') !== -1) ds = '/'; else ds = '\\';
snap_image_count++; return (flow_path + ds + 'snap' + snap_image_count.toString() + '.png');}

// for saving table from website to file
function save_table(file_name,selector) {var ds; if (flow_path.indexOf('/') !== -1) ds = '/'; else ds = '\\';
if (!file_name) {save_text_count++; file_name = flow_path + ds + 'table' + save_text_count.toString() + '.csv';}
var row_data = ""; var table_cell = ""; var fs = require('fs'); fs.write(file_name, '', 'w'); // always reset file
if (!casper.exists(selector) || (selector.toString().indexOf('xpath selector: ')==-1)) return false; // exit if invalid
if (selector.toString().length == 16) selector = ''; else selector = selector.toString().substring(16); // get xpath
for (table_row=1; table_row<=1024; table_row++) {row_data = ""; for (table_col=1; table_col<=1024; table_col++) {
table_cell = '(((' + selector + '//tr)[' + table_row + ']//th)' + '|'; // build cell xpath selector to include
table_cell += '((' + selector + '//tr)[' + table_row + ']//td))[' + table_col + ']'; // both td and td elements
if (casper.exists(x(table_cell))) row_data = row_data + '","' + casper.fetchText(x(table_cell)).trim();
else break;} // if searching for table cells (th and td) is not successful,  means end of row is reached
if (row_data.substr(0,2) == '",') {row_data = row_data.substr(2); row_data += '"'; append_text(file_name,row_data);}
else return true;}} // if '",' is not found, means end of table is reached as there is no cell found in row

// for translating multi-language flows (comments in translate.php)
function translate(script_line,direction,language) {var start_keywords = 
'|click|tap|move|hover|type|enter|select|choose|read|fetch|show|print|save|echo|dump|write|snap|table|'+
'wait|live|download|upload|load|receive|frame|popup|timeout|api|dom|js|else if|else|if|for|while|check|';
var to_separator_keywords = '|read|fetch|save|load|dump|write|snap|table|download|receive|for|'
var as_separator_keywords = '|type|enter|select|choose|upload|'; var forloop_keywords = '|from|';
var start_conditions_keywords = '|else if|if|for|while|check|'; var start_helper_keywords = '|echo|dump|write|';
var conditions_keywords = '|more than or equals to|more than or equal to|greater than or equals to|greater than or equal to|higher than or equals to|higher than or equal to|less than or equals to|less than or equal to|lesser than or equals to|lesser than or equal to|lower than or equals to|lower than or equal to|more than|greater than|higher than|less than|lesser than|lower than|not equals to|not equal to|equals to|equal to|not contains|not contain|contains|contain|and|or|';
var helper_keywords = '|title()|url()|text()|timer()|count()|present()|visible()|';
var seconds_keywords = '|seconds|second|'; var start_seconds_keywords = '|wait|timeout|';
if (!script_line || script_line == '') return '';
if (!direction || direction == '') return 'ERROR - translation engine direction parameter missing';
if (!language || language == '') return 'ERROR - translation engine language parameter missing';
if (script_line == '' || script_line == '\r\n' || script_line == '\n') return script_line;
var front_script_line_return = ''; if (script_line.charAt(0) == '\n') front_script_line_return = '\n';
var back_script_line_return = ''; if (script_line.substr(-1) == '\n') back_script_line_return = '\n';
direction = direction.toLowerCase();
if (direction !== 'to' && direction !== 'from') return 'ERROR - translation engine direction must be to or from'; 
if (direction == 'from') {var column_from = 1; var column_to = 0;} else {var column_from = 0; var column_to = 1;}
language = language.toLowerCase(); var language_count = 0;
var ds; if (flow_path.indexOf('/') !== -1) ds = '/'; else ds = '\\';
var language_file = 'languages' + ds + language + '.csv'; var fs = require('fs');
if (!fs.exists(language_file)) return 'ERROR - translation engine ' + language + '.csv file missing'; else {
var language_raw = fs.read(language_file); if (language_raw.indexOf('\r\n') !== -1)
var language_data = language_raw.split(/\r\n/).map(function(line) {return line.split(',');});
else var language_data = language_raw.split(/\n/).map(function(line) {return line.split(',');});
language_count = language_data.length-1;
if (!language_data[language_count][0] || language_data[language_count][0] == '') language_count--;}
script_line = '[START_OF_LINE]'+script_line.trim()+'[END_OF_LINE]'; var start_word = '[NOT_ASSIGNED]';
for (language_check = 1; language_check <= language_count; language_check++) {
if (start_keywords.indexOf('|'+language_data[language_check][0]+'|') !== -1) {
if (start_word !== '[NOT_ASSIGNED]') continue;
if ((script_line.indexOf('[START_OF_LINE]'+language_data[language_check][column_from]+' ') !== -1) || 
(script_line.indexOf('[START_OF_LINE]'+language_data[language_check][column_from]+'[END_OF_LINE]') !== -1))
start_word = language_data[language_check][0];
var regex = new RegExp('\\[START_OF_LINE\\]'+language_data[language_check][column_from]+' ','g');
script_line = script_line.replace(regex,'[START_OF_LINE]'+language_data[language_check][column_to]+' ');
var regex = new RegExp('\\[START_OF_LINE\\]'+language_data[language_check][column_from]+'\\[END_OF_LINE\\]','g');
script_line = script_line.replace(regex,'[START_OF_LINE]'+language_data[language_check][column_to]+'[END_OF_LINE]');}
else if (conditions_keywords.indexOf('|'+language_data[language_check][0]+'|') !== -1) {
if (start_word == 'check') {var array_script_line = script_line.split('|');
var regex = new RegExp(' '+language_data[language_check][column_from]+' ','g');
array_script_line[0] = array_script_line[0].replace(regex,' '+language_data[language_check][column_to]+' ');
script_line = array_script_line.join('|');}
else if ((start_word !== '[NOT_ASSIGNED]') && (start_conditions_keywords.indexOf('|'+start_word+'|') !== -1))
{var regex = new RegExp(' '+language_data[language_check][column_from]+' ','g');
script_line = script_line.replace(regex,' '+language_data[language_check][column_to]+' ');}}
else if (seconds_keywords.indexOf('|'+language_data[language_check][0]+'|') !== -1) {
if ((start_word !== '[NOT_ASSIGNED]') && (start_seconds_keywords.indexOf('|'+start_word+'|') !== -1))
{var regex = new RegExp(' '+language_data[language_check][column_from]+'\\[END_OF_LINE\\]','g');
script_line = script_line.replace(regex,' '+language_data[language_check][column_to]+'[END_OF_LINE]');}}
else if (forloop_keywords.indexOf('|'+language_data[language_check][0]+'|') !== -1) {
if (start_word == 'for')
{var regex = new RegExp(' '+language_data[language_check][column_from]+' ','g');
script_line = script_line.replace(regex,' '+language_data[language_check][column_to]+' ');}}
else if (language_data[language_check][0] == 'to') {
if ((start_word !== '[NOT_ASSIGNED]') && (to_separator_keywords.indexOf('|'+start_word+'|') !== -1))
{var regex = new RegExp(' '+language_data[language_check][column_from]+' ','g');
script_line = script_line.replace(regex,' '+language_data[language_check][column_to]+' ');}}
else if (language_data[language_check][0] == 'as') {
if ((start_word !== '[NOT_ASSIGNED]') && (as_separator_keywords.indexOf('|'+start_word+'|') !== -1))
{var regex = new RegExp(' '+language_data[language_check][column_from]+' ','g');
script_line = script_line.replace(regex,' '+language_data[language_check][column_to]+' ');}}
else if (helper_keywords.indexOf('|'+language_data[language_check][0]+'|') !== -1) {
if (((start_word !== '[NOT_ASSIGNED]') && (start_conditions_keywords.indexOf('|'+start_word+'|') !== -1))
|| ((start_word !== '[NOT_ASSIGNED]') && (start_helper_keywords.indexOf('|'+start_word+'|') !== -1))
|| (script_line.indexOf('=') !== -1))
{var regex = new RegExp((' '+language_data[language_check][column_from]).replace('(','\\(').replace(')',''),'g');
script_line = script_line.replace(regex,(' '+language_data[language_check][column_to]).replace(')',''));}}}
script_line = script_line.replace('[START_OF_LINE]','').replace('[END_OF_LINE]','');
return front_script_line_return+script_line.trim()+back_script_line_return;}

// for checking if selector is xpath selector
function is_xpath_selector(selector) {if (selector.length == 0) return false;
if ((selector.indexOf('/') == 0) || (selector.indexOf('(') == 0)) return true; return false;}

// for finding best match for given locator
function tx(locator) {if (is_xpath_selector(locator)) return x(locator);
if (casper.exists(locator)) return locator; // check for css locator
// first check for exact match then check for containing string
if (casper.exists(x('//*[@id="'+locator+'"]'))) return x('//*[@id="'+locator+'"]');
if (casper.exists(x('//*[contains(@id,"'+locator+'")]'))) return x('//*[contains(@id,"'+locator+'")]');
if (casper.exists(x('//*[@name="'+locator+'"]'))) return x('//*[@name="'+locator+'"]');
if (casper.exists(x('//*[contains(@name,"'+locator+'")]'))) return x('//*[contains(@name,"'+locator+'")]');
if (casper.exists(x('//*[@class="'+locator+'"]'))) return x('//*[@class="'+locator+'"]');
if (casper.exists(x('//*[contains(@class,"'+locator+'")]'))) return x('//*[contains(@class,"'+locator+'")]');
if (casper.exists(x('//*[@title="'+locator+'"]'))) return x('//*[@title="'+locator+'"]');
if (casper.exists(x('//*[contains(@title,"'+locator+'")]'))) return x('//*[contains(@title,"'+locator+'")]');
if (casper.exists(x('//*[@aria-label="'+locator+'"]'))) return x('//*[@aria-label="'+locator+'"]');
if (casper.exists(x('//*[contains(@aria-label,"'+locator+'")]'))) return x('//*[contains(@aria-label,"'+locator+'")]');
if (casper.exists(x('//*[text()="'+locator+'"]'))) return x('//*[text()="'+locator+'"]');
if (casper.exists(x('//*[contains(text(),"'+locator+'")]'))) return x('//*[contains(text(),"'+locator+'")]');
if (casper.exists(x('//*[@href="'+locator+'"]'))) return x('//*[@href="'+locator+'"]');
if (casper.exists(x('//*[contains(@href,"'+locator+'")]'))) return x('//*[contains(@href,"'+locator+'")]');
return x('/html');}

// for checking if given locator is found
function check_tx(locator) {if (is_xpath_selector(locator))
{if (casper.exists(x(locator))) return true; else return false;}
if (casper.exists(locator)) return true; // check for css locator
// first check for exact match then check for containing string
if (casper.exists(x('//*[@id="'+locator+'"]'))) return true;
if (casper.exists(x('//*[contains(@id,"'+locator+'")]'))) return true;
if (casper.exists(x('//*[@name="'+locator+'"]'))) return true;
if (casper.exists(x('//*[contains(@name,"'+locator+'")]'))) return true;
if (casper.exists(x('//*[@class="'+locator+'"]'))) return true;
if (casper.exists(x('//*[contains(@class,"'+locator+'")]'))) return true;
if (casper.exists(x('//*[@title="'+locator+'"]'))) return true;
if (casper.exists(x('//*[contains(@title,"'+locator+'")]'))) return true;
if (casper.exists(x('//*[@aria-label="'+locator+'"]'))) return true;
if (casper.exists(x('//*[contains(@aria-label,"'+locator+'")]'))) return true;
if (casper.exists(x('//*[text()="'+locator+'"]'))) return true;
if (casper.exists(x('//*[contains(text(),"'+locator+'")]'))) return true;
if (casper.exists(x('//*[@href="'+locator+'"]'))) return true;
if (casper.exists(x('//*[contains(@href,"'+locator+'")]'))) return true;
return false;}

// friendlier name to use check_tx() in if condition in flow
function present(element_locator) {if (!element_locator) return false; else return check_tx(element_locator);}

// friendlier name to check element visibility using elementVisible()
function visible(element_locator) {if (!element_locator) return false;
var element_located = tx(element_locator); var element_visible = casper.elementVisible(element_located);
// if tx() returns x('/html') means that the element is not found, so set element_visible to false
if (element_located.toString() == x('/html').toString()) element_visible = false; return element_visible;}

// friendlier name to count elements using countElements()
function count(element_locator) {if (!element_locator) return 0;
var element_located = tx(element_locator); var element_count = casper.countElements(element_located);
// if tx() returns x('/html') means that the element is not found, so set element_count to 0
if (element_located.toString() == x('/html').toString()) element_count = 0; return element_count;}

// friendlier name to get web page title using getTitle()
function title() {return casper.getTitle();}

// friendlier name to get web page url using getCurrentUrl() 
function url() {return casper.getCurrentUrl();}

// friendlier name to get web page text content using evaluate()
function text() {return casper.evaluate(function() {return document.body.innerText || document.body.textContent;});}

function timer() {// return time elapsed in seconds between calls
var time_elapsed = ((Date.now()-timer_start_time)/1000); timer_start_time = Date.now(); return time_elapsed;}

function sleep(ms) { // helper to add delay during loops
var time_now = new Date().getTime(); var time_end = time_now + ms;
while(time_now < time_end) {time_now = new Date().getTime();}}

// for initialising integration with sikuli visual automation
function sikuli_handshake() { // techo('[connecting to sikuli process]');
var ds; if (flow_path.indexOf('/') !== -1) ds = '/'; else ds = '\\';
var fs = require('fs'); fs.write('tagui.sikuli'+ds+'tagui_sikuli.in','','w'); var sikuli_handshake = '';
if (!fs.exists('tagui.sikuli'+ds+'tagui_sikuli.out')) fs.write('tagui.sikuli'+ds+'tagui_sikuli.out','','w');
do {sleep(500); sikuli_handshake = fs.read('tagui.sikuli'+ds+'tagui_sikuli.out').trim();}
while (sikuli_handshake !== '[0] START'); // techo('[connected to sikuli process]');
}

// for using sikuli visual automation instead of casperjs
function sikuli_step(sikuli_intent) {sikuli_count++;
if (sikuli_count == 1) sikuli_handshake(); // handshake on first call
var ds; if (flow_path.indexOf('/') !== -1) ds = '/'; else ds = '\\';
var fs = require('fs'); fs.write('tagui.sikuli'+ds+'tagui_sikuli.in','['+sikuli_count.toString()+'] '+sikuli_intent,'w');
var sikuli_result = ''; do {sleep(500); sikuli_result = fs.read('tagui.sikuli'+ds+'tagui_sikuli.out').trim();}
while (sikuli_result.indexOf('['+sikuli_count.toString()+'] ') == -1);
if (sikuli_result.indexOf('SUCCESS') !== -1) return true; else return false;}

if (chrome_id > 0) { // super large if block to load chrome related functions if chrome or headless option is used
chrome_id = 0; // reset chrome_id from 1 back to 0 to prepare for initial call of chrome_step

// for initialising integration with chrome web browser
function chrome_handshake() { // techo('[connecting to chrome websocket]');
var fs = require('fs'); fs.write('tagui_chrome.in','','w'); var chrome_handshake = '';
if (!fs.exists('tagui_chrome.out')) fs.write('tagui_chrome.out','','w');
do {sleep(100); chrome_handshake = fs.read('tagui_chrome.out').trim();}
while (chrome_handshake !== '[0] START'); // techo('[connected to chrome websocket]');
}

// send websocket message to chrome browser using chrome devtools protocol
// php helper process tagui_chrome.php running to handle this concurrently
function chrome_step(method,params) {chrome_id++;
if (chrome_id == 1) chrome_handshake(); // handshake on first call
var chrome_intent = JSON.stringify({'id': chrome_id, 'method': method, 'params': params});
if (chrome_targetid !== '') chrome_intent = JSON.stringify({'id': chrome_id, 'method': 'Target.sendMessageToTarget', 'params': {'sessionId': chrome_targetid, 'message': chrome_intent}}); // send as message to target if context is popup
var fs = require('fs'); fs.write('tagui_chrome.in','['+chrome_id.toString()+'] '+chrome_intent,'w');
var chrome_result = ''; do {sleep(100); chrome_result = fs.read('tagui_chrome.out').trim();}
while (chrome_result.indexOf('['+chrome_id.toString()+'] ') == -1);
if (chrome_targetid == '') return chrome_result.substring(chrome_result.indexOf('] ')+2); // below for handling popup
else {try {var raw_json_string = JSON.stringify(JSON.parse(chrome_result.substring(chrome_result.indexOf('] ')+2)).params.message); return raw_json_string.slice(1,-1).replace(/\\"/g,'"').replace(/\\\\n/g,"\\n");} catch(e) {return '';}}}

// chrome object for handling integration with chrome or headless chrome
var chrome = new Object(); chrome.mouse = new Object();

// chrome methods as casper methods replacement for chrome integration
chrome.exists = function(selector) { // different handling for xpath and css to support both
if ((selector.toString().length >= 16) && (selector.toString().substr(0,16) == 'xpath selector: '))
{if (selector.toString().length == 16) selector = ''; else selector = selector.toString().substring(16);
var ws_message = chrome_step('Runtime.evaluate',{expression: 'document.evaluate(\''+selector+'\','+chrome_context+',null,XPathResult.ORDERED_NODE_SNAPSHOT_TYPE,null).snapshotLength'});}
else var ws_message = chrome_step('Runtime.evaluate',{expression: chrome_context+'.querySelectorAll(\''+selector+'\').length'});
try {var ws_json = JSON.parse(ws_message); if (ws_json.result.result.value > 0) return true; else return false;}
catch(e) {return false;}};

chrome.elementVisible = function(selector) { // same as chrome.exists, except for checking visibility
if ((selector.toString().length >= 16) && (selector.toString().substr(0,16) == 'xpath selector: '))
{if (selector.toString().length == 16) selector = ''; else selector = selector.toString().substring(16);
var ws_message = chrome_step('Runtime.evaluate',{expression: 'var e = document.evaluate(\''+selector+'\','+chrome_context+',null,XPathResult.ORDERED_NODE_SNAPSHOT_TYPE,null).snapshotItem(0); var visible = false; if (!e) visible = false; else {var style = window.getComputedStyle(e); visible = style && style.display !== \'none\' && style.visibility !== \'hidden\' && style.opacity !== \'0\';}; visible'});}
else var ws_message = chrome_step('Runtime.evaluate',{expression: 'var e = '+chrome_context+'.querySelector(\''+selector+'\'); var visible = false; if (!e) visible = false; else {var style = window.getComputedStyle(e); visible = style && style.display !== \'none\' && style.visibility !== \'hidden\' && style.opacity !== \'0\';}; visible'});
try {var ws_json = JSON.parse(ws_message); if (ws_json.result.result.value == true)
return ws_json.result.result.value; else return false;}
catch(e) {return false;}};

chrome.countElements = function(selector) { // same as chrome.exists, except element count is returned
if ((selector.toString().length >= 16) && (selector.toString().substr(0,16) == 'xpath selector: '))
{if (selector.toString().length == 16) selector = ''; else selector = selector.toString().substring(16);
var ws_message = chrome_step('Runtime.evaluate',{expression: 'document.evaluate(\''+selector+'\','+chrome_context+',null,XPathResult.ORDERED_NODE_SNAPSHOT_TYPE,null).snapshotLength'});}
else var ws_message = chrome_step('Runtime.evaluate',{expression: chrome_context+'.querySelectorAll(\''+selector+'\').length'});
try {var ws_json = JSON.parse(ws_message); if (ws_json.result.result.value > 0)
return ws_json.result.result.value; else return 0;}
catch(e) {return 0;}};

chrome.click = function(selector) { // click by sending click event instead of mouse down/up/click, then focus on element
if ((selector.toString().length >= 16) && (selector.toString().substr(0,16) == 'xpath selector: '))
{if (selector.toString().length == 16) selector = ''; else selector = selector.toString().substring(16);
chrome_step('Runtime.evaluate',{expression: 'document.evaluate(\''+selector+'\','+chrome_context+',null,XPathResult.ORDERED_NODE_SNAPSHOT_TYPE,null).snapshotItem(0).click()'}); chrome_step('Runtime.evaluate',{expression: 'document.evaluate(\''+selector+'\','+chrome_context+',null,XPathResult.ORDERED_NODE_SNAPSHOT_TYPE,null).snapshotItem(0).focus()'});}
else {chrome_step('Runtime.evaluate',{expression: chrome_context+'.querySelector(\''+selector+'\').click()'});
chrome_step('Runtime.evaluate',{expression: chrome_context+'.querySelector(\''+selector+'\').focus()'});}};

chrome.mouse.action = function(type,x,y,button,clickCount) { // helper function to send various mouse events
chrome_step('Input.dispatchMouseEvent',{type: type, x: x, y: y, button: button, clickCount: clickCount});};

chrome.mouse.getXY = function(selector) { // helper function to get xy center coordinates of selector
if ((selector.toString().length >= 16) && (selector.toString().substr(0,16) == 'xpath selector: '))
{if (selector.toString().length == 16) selector = ''; else selector = selector.toString().substring(16);
var ws_message = chrome_step('Runtime.evaluate',{expression: 'var result_bounds = document.evaluate(\''+selector+'\','+chrome_context+',null,XPathResult.ORDERED_NODE_SNAPSHOT_TYPE,null).snapshotItem(0).getBoundingClientRect(); var result_xy = {x: Math.round(result_bounds.left + result_bounds.width / 2), y: Math.round(result_bounds.top + result_bounds.height / 2)}; result_xy', returnByValue: true});}
else var ws_message = chrome_step('Runtime.evaluate',{expression: 'var result_bounds = '+chrome_context+'.querySelector(\''+selector+'\').getBoundingClientRect(); var result_xy = {x: Math.round(result_bounds.left + result_bounds.width / 2), y: Math.round(result_bounds.top + result_bounds.height / 2)}; result_xy', returnByValue: true});
try {var ws_json = JSON.parse(ws_message); if (ws_json.result.result.value.x > 0 && ws_json.result.result.value.y > 0)
return ws_json.result.result.value; else return {x: 0, y: 0};} catch(e) {return {x: 0, y: 0};}};

chrome.getRect = function(selector) { // helper function to get rectangle boundary coordinates of selector
if ((selector.toString().length >= 16) && (selector.toString().substr(0,16) == 'xpath selector: '))
{if (selector.toString().length == 16) selector = ''; else selector = selector.toString().substring(16);
var ws_message = chrome_step('Runtime.evaluate',{expression: 'var result_bounds = document.evaluate(\''+selector+'\','+chrome_context+',null,XPathResult.ORDERED_NODE_SNAPSHOT_TYPE,null).snapshotItem(0).getBoundingClientRect(); var result_rect = {top: Math.round(result_bounds.top), left: Math.round(result_bounds.left), width: Math.round(result_bounds.width), height: Math.round(result_bounds.height)}; result_rect', returnByValue: true});}
else var ws_message = chrome_step('Runtime.evaluate',{expression: 'var result_bounds = '+chrome_context+'.querySelector(\''+selector+'\').getBoundingClientRect(); var result_rect = {top: Math.round(result_bounds.top), left: Math.round(result_bounds.left), width: Math.round(result_bounds.width), height: Math.round(result_bounds.height)}; result_rect', returnByValue: true}); try {var ws_json = JSON.parse(ws_message); // check if width and height are valid before returning coordinates
if (ws_json.result.result.value.width > 0 && ws_json.result.result.value.height > 0) return ws_json.result.result.value;
else return {left: 0, top: 0, width: 0, height: 0};} catch(e) {return {left: 0, top: 0, width: 0, height: 0};}};

chrome.mouse.move = function(selector,y) { // move mouse pointer to center of specified selector or point 
if (!y) var xy = chrome.mouse.getXY(selector); else var xy = {x: selector, y: y}; // get coordinates accordingly
chrome.mouse.action('mouseMoved',xy.x,xy.y,'none',0);};

chrome.mouse.click = function(selector,y) { // press and release on center of specfied selector or point
if (!y) var xy = chrome.mouse.getXY(selector); else var xy = {x: selector, y: y}; // get coordinates accordingly
chrome.mouse.action('mousePressed',xy.x,xy.y,'left',1); chrome.mouse.action('mouseReleased',xy.x,xy.y,'left',1);};

chrome.mouse.doubleclick = function(selector,y) { // double press and release on center of selector or point
if (!y) var xy = chrome.mouse.getXY(selector); else var xy = {x: selector, y: y}; // get coordinates accordingly
chrome.mouse.action('mousePressed',xy.x,xy.y,'left',1); chrome.mouse.action('mouseReleased',xy.x,xy.y,'left',1);
chrome.mouse.action('mousePressed',xy.x,xy.y,'left',1); chrome.mouse.action('mouseReleased',xy.x,xy.y,'left',1);};

chrome.mouse.rightclick = function(selector,y) { // right click press and release on center of selector or point
if (!y) var xy = chrome.mouse.getXY(selector); else var xy = {x: selector, y: y}; // get coordinates accordingly
chrome.mouse.action('mousePressed',xy.x,xy.y,'right',1); chrome.mouse.action('mouseReleased',xy.x,xy.y,'right',1);};

chrome.mouse.down = function(selector,y) { // left press on center of specified selector or point
if (!y) var xy = chrome.mouse.getXY(selector); else var xy = {x: selector, y: y}; // get coordinates accordingly
chrome.mouse.action('mousePressed',xy.x,xy.y,'left',1);};

chrome.mouse.up = function(selector,y) { // left release on center of specified selector or point
if (!y) var xy = chrome.mouse.getXY(selector); else var xy = {x: selector, y: y}; // get coordinates accordingly
chrome.mouse.action('mouseReleased',xy.x,xy.y,'left',1);};

chrome.sendKeys = function(selector,value,options) { // send key strokes to selector, options not implemented
if (value == casper.page.event.key.Enter) value = '\r';
if ((selector.toString().length >= 16) && (selector.toString().substr(0,16) == 'xpath selector: '))
{if (selector.toString().length == 16) selector = ''; else selector = selector.toString().substring(16);
chrome_step('Runtime.evaluate',{expression: 'document.evaluate(\''+selector+'\','+chrome_context+',null,XPathResult.ORDERED_NODE_SNAPSHOT_TYPE,null).snapshotItem(0).focus()'});}
else chrome_step('Runtime.evaluate',{expression: chrome_context+'.querySelector(\''+selector+'\').focus()'});
if (options && options.reset == true) // handling for clearing field by checking options.reset
{if ((selector.indexOf('/') == 0) || (selector.indexOf('(') == 0)) // check for xpath selector and handle accordingly
{chrome_step('Runtime.evaluate',{expression: 'var sendKeys_field = document.evaluate(\''+selector+'\','+chrome_context+',null,XPathResult.ORDERED_NODE_SNAPSHOT_TYPE,null).snapshotItem(0); sendKeys_field.value = \'\'; var evt = document.createEvent(\'UIEvents\'); evt.initUIEvent(\'change\', true, true); sendKeys_field.dispatchEvent(evt);'});}
else chrome_step('Runtime.evaluate',{expression: 'var sendKeys_field = '+chrome_context+'.querySelector(\''+selector+'\'); sendKeys_field.value = \'\'; var evt = document.createEvent(\'UIEvents\'); evt.initUIEvent(\'change\', true, true); sendKeys_field.dispatchEvent(evt);'});}
for (var character = 0, length = value.length; character < length; character++) {
chrome_step('Input.dispatchKeyEvent',{type: 'char', text: value[character]});}};

chrome.selectOptionByValue = function(selector,valueToMatch) { // select dropdown option (base on casperjs issue #1390)
chrome.evaluate('function() {var selector = \''+selector+'\'; var valueToMatch = \''+valueToMatch+'\'; var found = false; if ((selector.indexOf(\'/\') == 0) || (selector.indexOf(\'(\') == 0)) var select = document.evaluate(selector,'+chrome_context+',null,XPathResult.ORDERED_NODE_SNAPSHOT_TYPE,null).snapshotItem(0); else var select = '+chrome_context+'.querySelector(selector); if (valueToMatch == \'[clear]\') valueToMatch = \'\'; Array.prototype.forEach.call(select.children, function(opt, i) {if (!found && opt.value.indexOf(valueToMatch) !== -1) {select.selectedIndex = i; found = true;}}); var evt = document.createEvent("UIEvents"); evt.initUIEvent("change", true, true); select.dispatchEvent(evt);}');};

chrome.fetchText = function(selector) { // grab text from selector following casperjs logic, but grab only first match
if ((selector.toString().length >= 16) && (selector.toString().substr(0,16) == 'xpath selector: '))
{if (selector.toString().length == 16) selector = ''; else selector = selector.toString().substring(16);
var ws_message = chrome_step('Runtime.evaluate',{expression: 'document.evaluate(\''+selector+'\','+chrome_context+',null,XPathResult.ORDERED_NODE_SNAPSHOT_TYPE,null).snapshotItem(0).textContent || document.evaluate(\''+selector+'\','+chrome_context+',null,XPathResult.ORDERED_NODE_SNAPSHOT_TYPE,null).snapshotItem(0).innerText || document.evaluate(\''+selector+'\','+chrome_context+',null,XPathResult.ORDERED_NODE_SNAPSHOT_TYPE,null).snapshotItem(0).value || \'\''});}
else var ws_message = chrome_step('Runtime.evaluate',{expression: chrome_context+'.querySelector(\''+selector+'\').textContent || '+chrome_context+'.querySelector(\''+selector+'\').innerText || '+chrome_context+'.querySelector(\''+selector+'\').value || \'\''});
try {var ws_json = JSON.parse(ws_message); if (ws_json.result.result.value)
return ws_json.result.result.value; else return '';} catch(e) {return '';}};

chrome.decode = function(str) { // funtion to convert base64 data to binary string
// used in https://github.com/casperjs/casperjs/blob/master/modules/clientutils.js
if (!str) return ''; // return empty string if somehow null value is passed in
var BASE64_DECODE_CHARS = [
-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,
-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,62,-1,-1,-1,63,52,53,54,55,56,57,58,59,60,61,-1,-1,-1,-1,-1,-1,
-1, 0, 1, 2, 3, 4, 5, 6, 7, 8, 9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,-1,-1,-1,-1,-1,
-1,26,27,28,29,30,31,32,33,34,35,36,37,38,39,40,41,42,43,44,45,46,47,48,49,50,51,-1,-1,-1,-1,-1];
var c1, c2, c3, c4, i = 0, len = str.length, out = ""; while (i < len) {
do {c1 = BASE64_DECODE_CHARS[str.charCodeAt(i++) & 0xff];} while (i < len && c1 === -1); if (c1 === -1) {break;}
do {c2 = BASE64_DECODE_CHARS[str.charCodeAt(i++) & 0xff];} while (i < len && c2 === -1); if (c2 === -1) {break;}
out += String.fromCharCode(c1 << 2 | (c2 & 0x30) >> 4);
do {c3 = str.charCodeAt(i++) & 0xff; if (c3 === 61) {return out;} c3 = BASE64_DECODE_CHARS[c3];}
while (i < len && c3 === -1); if (c3 === -1) {break;} out += String.fromCharCode((c2 & 0XF) << 4 | (c3 & 0x3C) >> 2);
do {c4 = str.charCodeAt(i++) & 0xff; if (c4 === 61) {return out;} c4 = BASE64_DECODE_CHARS[c4];}
while (i < len && c4 === -1); if (c4 === -1) {break;} out += String.fromCharCode((c3 & 0x03) << 6 | c4);} return out;};

chrome.capture = function(filename) { // capture screenshot of webpage to file in png/jpg/jpeg format
// having pdf extension saves to a pdf file instead. only works in headless mode, visible mode errors out
var format = 'png'; var quality = 80; var fromSurface = true; var screenshot_data = ''; // options not implemented
if ((filename.substr(-3).toLowerCase() == 'jpg') || (filename.substr(-4).toLowerCase() == 'jpeg')) format = 'jpeg';
if (filename.substr(-3).toLowerCase() == 'pdf') var ws_message = chrome_step('Page.printToPDF',{printBackground: true});
else var ws_message = chrome_step('Page.captureScreenshot',{format: format, quality: quality, fromSurface: fromSurface});
try {var ws_json = JSON.parse(ws_message); screenshot_data = ws_json.result.data;} catch(e) {screenshot_data = '';}
var fs = require('fs'); fs.write(filename,chrome.decode(screenshot_data),'wb');};

chrome.captureSelector = function(filename,selector) { // capture screenshot of selector to png/jpg/jpeg format
// first capture entire screen, then use casperjs / phantomjs browser to crop image base on selector dimensions
chrome.capture(filename); var selector_rect = chrome.getRect(selector); // so that there is no extra dependency
if (selector_rect.width > 0 && selector_rect.height > 0) // from using other libraries or creating html canvas 
casper.thenOpen(filename, function() {casper . capture(filename, // spaces around . intentional to avoid replacing 
{top: selector_rect.top, left: selector_rect.left, width: selector_rect.width, height: selector_rect.height});
casper.thenOpen('about:blank');});}; // reset phantomjs browser state

chrome.upload = function(selector,filename) { // upload function to upload file to provided selector
if ((selector.toString().length >= 16) && (selector.toString().substr(0,16) == 'xpath selector: '))
{casper.echo('ERROR - upload step is only implemented for CSS selector and not XPath selector');
casper.echo('ERROR - for consistency with PhantomJS as it only supports upload with CSS selector');}
else try {var ws_message = ""; var ws_json = {};
ws_message = chrome_step('DOM.getDocument',{});
ws_json = JSON.parse(ws_message);
ws_message = chrome_step('DOM.querySelector',{nodeId: ws_json.result.root.nodeId, selector: selector});
ws_json = JSON.parse(ws_message);
ws_message = chrome_step('DOM.setFileInputFiles',{files: [filename], nodeId: ws_json.result.nodeId});
} catch(e) {casper.echo('ERROR - unable to upload ' + selector + ' as ' + filename);}};

chrome.download = function(url,filename) { // download function for downloading url resource to file
// casper download cannot be used for urls which requires login as casperjs engine can't access chrome
// the chromium issue 696481 is moving well, else an alternative may be to inject casper clientutils.js 
// TagUI by default auto-sets to allow downloads for headless Chrome (otherwise it prevents downloads)
casper.echo('ERROR - for headless and visible Chrome, download file using normal webpage interaction');};

chrome.evaluate = function(fn_statement,eval_json) { // evaluate expression in browser dom context
// chrome runtime.evaluate is different from casperjs evaluate, do some processing to reduce gap
var statement = fn_statement.toString(); if (!eval_json)
{statement = statement.slice(statement.indexOf('{')+1,statement.lastIndexOf('}'));
statement = statement.replace(/return /g,'');} // defining function() with return keyword is invalid for chrome
else statement = '(' + statement + ')' + '(' + JSON.stringify(eval_json) + ')'; // unless variable is passed into fx
var ws_message = chrome_step('Runtime.evaluate',{expression: statement}); // statements can be separated by ;
try {var ws_json = JSON.parse(ws_message); if (ws_json.result.result.value)
return ws_json.result.result.value; else return null;} catch(e) {return null;}};

chrome.withFrame = function(frameInfo,then) { // replace casperjs frame for switching frame context
var new_context = ''; if (chrome_context == 'document') new_context = 'mainframe_context';
else if (chrome_context == 'mainframe_context') new_context = 'subframe_context';
casper.then(function _step() {chrome_step('Runtime.evaluate',{expression: new_context+' = document.evaluate(\'(//frame|//iframe)[@name="'+frameInfo+'" or @id="'+frameInfo+'"]\','+chrome_context+',null,XPathResult.ORDERED_NODE_SNAPSHOT_TYPE,null).snapshotItem(0).contentDocument'}); chrome_context = new_context;}); // set mainframe_context/subframe_context in dom
casper.then(then); casper.then(function _step() {if (chrome_context == 'subframe_context') {chrome_step('Runtime.evaluate',{expression: 'subframe_context = null'}); chrome_context = 'mainframe_context';} else if (chrome_context == 'mainframe_context') {chrome_step('Runtime.evaluate',{expression: 'mainframe_context = null'}); chrome_context = 'document';}});};

chrome.waitForPopup = function(popupInfo,then,onTimeout) { // replace casperjs waitforpopup for checking popup window
casper.waitFor(function check() { // use similar logic as chrome withpopup to scan through list of browser targets
var found_popup = false; var chrome_targets = []; var ws_message = chrome_step('Target.getTargets',{});
try {var ws_json = JSON.parse(ws_message); if (ws_json.result.targetInfos) chrome_targets = ws_json.result.targetInfos;
else chrome_targets = [];} catch(e) {chrome_targets = [];} // following line scan through targets to find match
chrome_targets.forEach(function(target) {if (target.url.search(popupInfo) !== -1) found_popup = true;});
return found_popup;},then,onTimeout);};

chrome.withPopup = function(popupInfo,then) { // replace casperjs withpopup for handling popup window
casper.then(function _step() { // get list of targets, find a match, attach to the target and set chrome_targetid
var found_targetid = ''; var chrome_targets = []; var ws_message = chrome_step('Target.getTargets',{});
try {var ws_json = JSON.parse(ws_message); if (ws_json.result.targetInfos) chrome_targets = ws_json.result.targetInfos;
else chrome_targets = [];} catch(e) {chrome_targets = [];} // following line scan through targets to find match
chrome_targets.forEach(function(target) {if (target.url.search(popupInfo) !== -1) found_targetid = target.targetId;});
if (found_targetid !== '') {var ws_message = chrome_step('Target.attachToTarget',{targetId: found_targetid});
try {var ws_json = JSON.parse(ws_message); if (ws_json.result.sessionId.indexOf(found_targetid) > -1)
found_targetid = ws_json.result.sessionId; else found_targetid = '';} catch(e) {found_targetid = ''};}
chrome_targetid = found_targetid;}); // set chrome_targetid only after attaching to found target successfully
casper.then(then); casper.then(function _step() {if (chrome_targetid !== '') // detach from target after running then
{var found_targetid = chrome_targetid; chrome_targetid = ''; chrome_step('Target.detachFromTarget',{sessionId: found_targetid});}});};

chrome.getHTML = function() { // get raw html of current webpage
var ws_message = chrome_step('Runtime.evaluate',{expression: 'document.documentElement.outerHTML'});
try {var ws_json = JSON.parse(ws_message); if (ws_json.result.result.value)
return ws_json.result.result.value; else return '';} catch(e) {return '';}};

chrome.getTitle = function() { // get title of current webpage
var ws_message = chrome_step('Runtime.evaluate',{expression: 'document.title'});
try {var ws_json = JSON.parse(ws_message); if (ws_json.result.result.value)
return ws_json.result.result.value; else return '';} catch(e) {return '';}};

chrome.getCurrentUrl = function() { // get url of current webpage
var ws_message = chrome_step('Runtime.evaluate',{expression: 'document.location.href'});
try {var ws_json = JSON.parse(ws_message); // chrome returns below value on empty dead url
if (ws_json.result.result.value == 'data:text/html,chromewebdata') ws_json.result.result.value = 'about:blank';
if (ws_json.result.result.value) return ws_json.result.result.value; else return '';} catch(e) {return '';}};

chrome.debugHTML = function() {casper.echo(chrome.getHTML());}; // print raw html of current webpage

chrome.reload = function() { // reload the current webpage, then not implemented
var ws_message = chrome_step('Runtime.evaluate',{expression: 'document.location.reload()'});
try {var ws_json = JSON.parse(ws_message); if (ws_json.result.result.value)
return ws_json.result.result.value; else return '';} catch(e) {return '';}};

chrome.back = function() { // move back a step in browser history
var ws_message = chrome_step('Runtime.evaluate',{expression: 'window.history.back()'});
try {var ws_json = JSON.parse(ws_message); if (ws_json.result.result.value)
return ws_json.result.result.value; else return '';} catch(e) {return '';}};

chrome.forward = function() { // move forward a step in browser history
var ws_message = chrome_step('Runtime.evaluate',{expression: 'window.history.forward()'});
try {var ws_json = JSON.parse(ws_message); if (ws_json.result.result.value)
return ws_json.result.result.value; else return '';} catch(e) {return '';}};

chrome.echo = function(value) {casper.echo(value);}; // use casper echo to print output

chrome.on = function(value,statement) {casper.on(value,statement);}; // use casper event system

} // end of super large if block to load chrome related functions if chrome or headless option is used

// for live mode simple parsing of tagui steps into js code
function tagui_parse(raw_input) {if (tagui_language.toLowerCase() == 'english') return parse_intent(raw_input);
else {var translated_string = translate(raw_input,'from',tagui_language.toLowerCase());
if (translated_string.indexOf('ERROR - translation engine') !== -1) casper.echo(translated_string).exit();
else return parse_intent(translated_string);}}

// for live mode interpretation of step into casperjs code
function parse_intent(live_line) {
live_line = live_line.trim(); if (live_line == '') return '';
switch (get_intent(live_line)) {
case 'url': return url_intent(live_line); break;
case 'tap': return tap_intent(live_line); break;
case 'hover': return hover_intent(live_line); break;
case 'type': return type_intent(live_line); break;
case 'select': return select_intent(live_line); break;
case 'read': return read_intent(live_line); break;
case 'show': return show_intent(live_line); break;
case 'upload': return upload_intent(live_line); break;
case 'down': return down_intent(live_line); break;
case 'receive': return receive_intent(live_line); break;
case 'echo': return echo_intent(live_line); break;
case 'save': return save_intent(live_line); break;
case 'dump': return dump_intent(live_line); break;
case 'write': return write_intent(live_line); break;
case 'load': return load_intent(live_line); break;
case 'snap': return snap_intent(live_line); break;
case 'table': return table_intent(live_line); break;
case 'wait': return wait_intent(live_line); break;
case 'live': return live_intent(live_line); break;
case 'check': return check_intent(live_line); break;
case 'test': return test_intent(live_line); break;
case 'frame': return frame_intent(live_line); break;
case 'popup': return popup_intent(live_line); break;
case 'api': return api_intent(live_line); break;
case 'run': return run_intent(live_line); break;
case 'dom': return dom_intent(live_line); break;
case 'js': return js_intent(live_line); break;
case 'timeout': return timeout_intent(live_line); break;
case 'code': return code_intent(live_line); break;
default: return "this.echo('ERROR - cannot understand step " + live_line.replace(/'/g,'\\\'') + "')";}}

// for live mode understanding intent of line entered
function get_intent(raw_intent) {var lc_raw_intent = raw_intent.toLowerCase();
if (lc_raw_intent.substr(0,7) == 'http://' || lc_raw_intent.substr(0,8) == 'https://') return 'url';

// first set of conditions check for valid keywords with their parameters
if ((lc_raw_intent.substr(0,4) == 'tap ') || (lc_raw_intent.substr(0,6) == 'click ')) return 'tap';
if ((lc_raw_intent.substr(0,6) == 'hover ') || (lc_raw_intent.substr(0,5) == 'move ')) return 'hover';
if ((lc_raw_intent.substr(0,5) == 'type ') || (lc_raw_intent.substr(0,6) == 'enter ')) return 'type';
if ((lc_raw_intent.substr(0,7) == 'select ') || (lc_raw_intent.substr(0,7) == 'choose ')) return 'select';
if ((lc_raw_intent.substr(0,5) == 'read ') || (lc_raw_intent.substr(0,6) == 'fetch ')) return 'read';
if ((lc_raw_intent.substr(0,5) == 'show ') || (lc_raw_intent.substr(0,6) == 'print ')) return 'show';
if ((lc_raw_intent.substr(0,3) == 'up ') || (lc_raw_intent.substr(0,7) == 'upload ')) return 'upload';
if ((lc_raw_intent.substr(0,5) == 'down ') || (lc_raw_intent.substr(0,9) == 'download ')) return 'down';
if (lc_raw_intent.substr(0,8) == 'receive ') return 'receive';
if (lc_raw_intent.substr(0,5) == 'echo ') return 'echo';
if (lc_raw_intent.substr(0,5) == 'save ') return 'save';
if (lc_raw_intent.substr(0,5) == 'dump ') return 'dump';
if (lc_raw_intent.substr(0,6) == 'write ') return 'write';
if (lc_raw_intent.substr(0,5) == 'load ') return 'load';
if (lc_raw_intent.substr(0,5) == 'snap ') return 'snap';
if (lc_raw_intent.substr(0,6) == 'table ') return 'table';
if (lc_raw_intent.substr(0,5) == 'wait ') return 'wait';
if (lc_raw_intent.substr(0,5) == 'live ') return 'live';
if (lc_raw_intent.substr(0,6) == 'check ') return 'check';
if (lc_raw_intent.substr(0,5) == 'test ') return 'test';
if (lc_raw_intent.substr(0,6) == 'frame ') return 'frame';
if (lc_raw_intent.substr(0,6) == 'popup ') return 'popup';
if (lc_raw_intent.substr(0,4) == 'api ') return 'api';
if (lc_raw_intent.substr(0,4) == 'run ') return 'run';
if (lc_raw_intent.substr(0,4) == 'dom ') return 'dom';
if (lc_raw_intent.substr(0,3) == 'js ') return 'js';
if (lc_raw_intent.substr(0,8) == 'timeout ') return 'timeout';

// second set of conditions check for valid keywords with missing parameters
if ((lc_raw_intent == 'tap') || (lc_raw_intent == 'click')) return 'tap';
if ((lc_raw_intent == 'hover') || (lc_raw_intent == 'move')) return 'hover';
if ((lc_raw_intent == 'type') || (lc_raw_intent == 'enter')) return 'type';
if ((lc_raw_intent == 'select') || (lc_raw_intent == 'choose')) return 'select';
if ((lc_raw_intent == 'read') || (lc_raw_intent == 'fetch')) return 'read';
if ((lc_raw_intent == 'show') || (lc_raw_intent == 'print')) return 'show';
if ((lc_raw_intent == 'up') || (lc_raw_intent == 'upload')) return 'upload';
if ((lc_raw_intent == 'down') || (lc_raw_intent == 'download')) return 'down';
if (lc_raw_intent == 'receive') return 'receive';
if (lc_raw_intent == 'echo') return 'echo';
if (lc_raw_intent == 'save') return 'save';
if (lc_raw_intent == 'dump') return 'dump';
if (lc_raw_intent == 'write') return 'write';
if (lc_raw_intent == 'load') return 'load';
if (lc_raw_intent == 'snap') return 'snap';
if (lc_raw_intent == 'table') return 'table';
if (lc_raw_intent == 'wait') return 'wait';
if (lc_raw_intent == 'live') return 'live';
if (lc_raw_intent == 'check') return 'check';
if (lc_raw_intent == 'test') return 'test';
if (lc_raw_intent == 'frame') return 'frame';
if (lc_raw_intent == 'popup') return 'popup';
if (lc_raw_intent == 'api') return 'api';
if (lc_raw_intent == 'run') return 'run';
if (lc_raw_intent == 'dom') return 'dom';
if (lc_raw_intent == 'js') return 'js';
if (lc_raw_intent == 'timeout') return 'timeout';

// final check for recognized code before returning error
if (is_code(raw_intent)) return 'code'; else return 'error';}

function is_code(raw_intent) {
// due to asynchronous waiting for element, if/for/while can work for parsing single step
// other scenarios can be assumed to behave as unparsed javascript in casperjs context
// to let if/for/while handle multiple steps/code use the { and } steps to define block
if ((raw_intent.substr(0,4) == 'var ') || (raw_intent.substr(0,3) == 'do ')) return true;
if ((raw_intent.substr(0,1) == '{') || (raw_intent.substr(0,1) == '}')) return true;
if ((raw_intent.charAt(raw_intent.length-1) == '{') || (raw_intent.charAt(raw_intent.length-1) == '}')) return true;
if ((raw_intent.substr(0,3) == 'if ') || (raw_intent.substr(0,4) == 'else')) return true;
if ((raw_intent.substr(0,4) == 'for ') || (raw_intent.substr(0,6) == 'while ')) return true;
if ((raw_intent.substr(0,7) == 'switch ') || (raw_intent.substr(0,5) == 'case ')) return true;
if ((raw_intent.substr(0,6) == 'break;') || (raw_intent.substr(0,9) == 'function ')) return true;
if ((raw_intent.substr(0,7) == 'casper.') || (raw_intent.substr(0,5) == 'this.')) return true;
if (raw_intent.substr(0,7) == 'chrome.') return true; // chrome object for chrome integration
if (raw_intent.substr(0,5) == ('test'+'.')) return true; // avoid replacement with test option
if ((raw_intent.substr(0,2) == '//') || (raw_intent.charAt(raw_intent.length-1) == ';')) return true;
// assume = is assignment statement, kinda acceptable as this is checked at the very end
if (raw_intent.indexOf('=') > -1) return true; return false;}

function abs_file(filename) { // helper function to return absolute filename
if (filename == '') return ''; // unlike tagui_parse.php not deriving path from script variable
if (filename.substr(0,1) == '/') return filename; // return mac/linux absolute filename directly
if (filename.substr(1,1) == ':') return filename.replace(/\\/g,'/'); // return windows absolute filename directly
var tmp_flow_path = flow_path; // otherwise use flow_path defined in generated script to build absolute filename
// above str_replace is because casperjs/phantomjs do not seem to support \ for windows paths, replace with / to work
if (tmp_flow_path.indexOf('/') > -1) return tmp_flow_path + '/' + filename; else return tmp_flow_path + '\\' + filename;}

function add_concat(source_string) { // parse string and add missing + concatenator
if ((source_string.indexOf("'") > -1) && (source_string.indexOf('"') > -1))
return "'ERROR - inconsistent quotes in text'";
else if (source_string.indexOf("'") > -1) var quote_type = "'"; // derive quote type used
else if (source_string.indexOf('"') > -1) var quote_type = '"'; else var quote_type = "none";
var within_quote = false; source_string = source_string.trim(); // trim for future proof
for (srcpos = 0; srcpos < source_string.length; srcpos++) {
if (source_string.charAt(srcpos) == quote_type) within_quote = !(within_quote);
if ((within_quote == false) && (source_string.charAt(srcpos)==" "))
source_string = source_string.substring(0,srcpos) + "+" + source_string.substring(srcpos+1);}
source_string = source_string.replace(/\+\+\+\+\+/g,'+'); source_string = source_string.replace(/\+\+\+\+/g,'+');
source_string = source_string.replace(/\+\+\+/g,'+'); source_string = source_string.replace(/\+\+/g,'+');
return source_string;} // replacing multiple variations of + to handle user typos of double spaces etc

function is_sikuli(input_params) { // helper function to check if input is meant for sikuli visual automation
if (input_params.length > 4 && input_params.substr(-4).toLowerCase() == '.png') return true; // support png and bmp
else if (input_params.length > 4 && input_params.substr(-4).toLowerCase() == '.bmp') return true; else return false;}

function call_sikuli(input_intent,input_params) { // helper function to use sikuli visual automation
var fs = require('fs'); // use phantomjs fs file system module to access files and directories
fs.write('tagui.sikuli/tagui_sikuli.in', '', 'w'); fs.write('tagui.sikuli/tagui_sikuli.out', '', 'w');
if (!fs.exists('tagui.sikuli/tagui_sikuli.in')) return "this.echo('ERROR - cannot initialise tagui_sikuli.in')";
if (!fs.exists('tagui.sikuli/tagui_sikuli.out')) return "this.echo('ERROR - cannot initialise tagui_sikuli.out')";
return "var fs = require('fs'); if (!sikuli_step('"+input_intent+"')) if (!fs.exists('"+input_params+"')) " +
"this.echo('ERROR - cannot find image file "+input_params+"'); " +
"else this.echo('ERROR - cannot find "+input_params+" on screen');";}

function url_intent(raw_intent) {
if (chrome_id == 0) return "this.echo('ERROR - step only supported in live mode using Chrome browser')";
else return "this.evaluate(function() {window.location.href = \"" + raw_intent + "\"})";}

function tap_intent(raw_intent) {
var params = ((raw_intent + ' ').substr(1+(raw_intent + ' ').indexOf(' '))).trim();
if (is_sikuli(params)) {var abs_params = abs_file(params); var abs_intent = raw_intent.replace(params,abs_params);
return call_sikuli(abs_intent,abs_params);} // use sikuli visual automation as needed
if (params == '') return "this.echo('ERROR - target missing for " + raw_intent + "')";
else if (check_tx(params)) return "this.click(tx('" + params + "'))";
else return "this.echo('ERROR - cannot find " + params + "')";}

function hover_intent(raw_intent) {
var params = ((raw_intent + ' ').substr(1+(raw_intent + ' ').indexOf(' '))).trim();
if (is_sikuli(params)) {var abs_params = abs_file(params); var abs_intent = raw_intent.replace(params,abs_params);
return call_sikuli(abs_intent,abs_params);} // use sikuli visual automation as needed
if (params == '') return "this.echo('ERROR - target missing for " + raw_intent + "')";
else if (check_tx(params)) return "this.mouse.move(tx('" + params + "'))";
else return "this.echo('ERROR - cannot find " + params + "')";}

function type_intent(raw_intent) {
var params = ((raw_intent + ' ').substr(1+(raw_intent + ' ').indexOf(' '))).trim();
var param1 = (params.substr(0,params.indexOf(' as '))).trim();
var param2 = (params.substr(4+params.indexOf(' as '))).trim();
if (is_sikuli(param1) && param2 !== '') {
var abs_param1 = abs_file(param1); var abs_intent = raw_intent.replace(param1,abs_param1);
return call_sikuli(abs_intent,abs_param1);} // use sikuli visual automation as needed
if ((param1 == '') || (param2 == '')) return "this.echo('ERROR - target/text missing for " + raw_intent + "')";
else if (check_tx(param1)) 
{if (param2.indexOf('[clear]') == 0) {if (param2.length>7) param2 = param2.substr(7); else param2 = "";
clear_field = "this.sendKeys(tx('" + param1 + "'),'',{reset: true}); ";} else clear_field = "";
if (param2.indexOf('[enter]') == -1) return clear_field + "this.sendKeys(tx('" + param1 + "'),'" + param2 + "')";
else // special handling to send enter key events
{param2 = param2.replace(/\[enter\]/g,"',{keepFocus: true}); this.sendKeys(tx('" + param1 + "'),casper.page.event.key.Enter,{keepFocus: true}); this.sendKeys(tx('" + param1 + "'),'");
return clear_field + "this.sendKeys(tx('" + param1 + "'),'" + param2 + "',{keepFocus: true});";}}
else return "this.echo('ERROR - cannot find " + param1 + "')";}

function select_intent(raw_intent) {
var params = ((raw_intent + ' ').substr(1+(raw_intent + ' ').indexOf(' '))).trim();
var param1 = (params.substr(0,params.indexOf(' as '))).trim();
var param2 = (params.substr(4+params.indexOf(' as '))).trim();
if (is_sikuli(param1) && is_sikuli(param2)) {
var abs_param1 = abs_file(param1); var abs_intent = raw_intent.replace(param1,abs_param1);
var abs_param2 = abs_file(param2); abs_intent = abs_intent.replace(param2,abs_param2);
return call_sikuli(abs_intent,abs_param1);} // use sikuli visual automation as needed
if ((param1 == '') || (param2 == '')) return "this.echo('ERROR - target/option missing for " + raw_intent + "')";
else if (check_tx(param1)) return "var select_locator = tx('" + param1 + "'); if (is_xpath_selector(select_locator.toString().replace('xpath selector: ',''))) select_locator = select_locator.toString().substring(16); this.selectOptionByValue(select_locator,'" + param2 + "');";
else return "this.echo('ERROR - cannot find " + param1 + "')";}

function read_intent(raw_intent) {
var params = ((raw_intent + ' ').substr(1+(raw_intent + ' ').indexOf(' '))).trim();
var param1 = (params.substr(0,params.indexOf(' to '))).trim();
var param2 = (params.substr(4+params.indexOf(' to '))).trim();
if ((param1.toLowerCase() == 'page') && (param2 !== '')) return param2 + " = this.getHTML()";
if ((param1 == '') || (param2 == '')) return "this.echo('ERROR - target/variable missing for " + raw_intent + "')";
else if (check_tx(param1)) return param2 + " =  this.fetchText(tx('" + param1 + "')).trim()";
else return "this.echo('ERROR - cannot find " + param1 + "')";}

function show_intent(raw_intent) {
var params = ((raw_intent + ' ').substr(1+(raw_intent + ' ').indexOf(' '))).trim();
if (params.toLowerCase() == 'page') return "this.echo('" + raw_intent + "' + ' - ' + '\\n' + this.getHTML())";
if (params == '') return "this.echo('ERROR - target missing for " + raw_intent + "')";
else if (check_tx(params)) return "this.echo(this.fetchText(tx('" + params + "')).trim())";else return "this.echo('ERROR - cannot find " + params + "')";}

function upload_intent(raw_intent) {
var params = ((raw_intent + ' ').substr(1+(raw_intent + ' ').indexOf(' '))).trim();
var param1 = (params.substr(0,params.indexOf(' as '))).trim();
var param2 = (params.substr(4+params.indexOf(' as '))).trim();
if ((param1 == '') || (param2 == '')) return "this.echo('ERROR - filename missing for " + raw_intent + "')";
else if (check_tx(param1)) return "this.page.uploadFile(tx('" + param1 + "'),'" + abs_file(param2) + "')";
else return "this.echo('ERROR - cannot find " + param1 + "')";}

function down_intent(raw_intent) {
var params = ((raw_intent + ' ').substr(1+(raw_intent + ' ').indexOf(' '))).trim();
var param1 = (params.substr(0,params.indexOf(' to '))).trim();
var param2 = (params.substr(4+params.indexOf(' to '))).trim();
if ((param1 == '') || (param2 == '')) return "this.echo('ERROR - url/filename missing for " + raw_intent + "')";
else return "this.download('" + param1 + "','" + abs_file(param2) + "')";}

function receive_intent(raw_intent) {
return "this.echo('ERROR - step not supported in live mode, it requires creating CasperJS event')";}

function echo_intent(raw_intent) {
var params = ((raw_intent + ' ').substr(1+(raw_intent + ' ').indexOf(' '))).trim();
if (params == '') return "this.echo('ERROR - text missing for " + raw_intent + "')";
else return "this.echo(" + add_concat(params) + ")";}

function save_intent(raw_intent) {
var params = ((raw_intent + ' ').substr(1+(raw_intent + ' ').indexOf(' '))).trim();
var param1 = (params.substr(0,params.indexOf(' to '))).trim();
var param2 = (params.substr(4+params.indexOf(' to '))).trim();
if ((params.toLowerCase() == 'page') || (param1.toLowerCase() == 'page')) {
if (params.indexOf(' to ') > -1) return "save_text('" + abs_file(param2) + "',this.getHTML())";
else return "save_text('',this.getHTML())";}
if (params == '') return "this.echo('ERROR - target missing for " + raw_intent + "')";
else if (params.indexOf(' to ') > -1)
{if (check_tx(param1)) return "save_text('" + abs_file(param2) + "',this.fetchText(tx('" + param1 + "')).trim())";
else return "this.echo('ERROR - cannot find " + param1 + "')";}
else {if (check_tx(params)) return "save_text('',this.fetchText(tx('" + params + "')).trim())";
else return "this.echo('ERROR - cannot find " + params + "')";}}

function dump_intent(raw_intent) {
var params = ((raw_intent + ' ').substr(1+(raw_intent + ' ').indexOf(' '))).trim();
var param1 = (params.substr(0,params.indexOf(' to '))).trim();
var param2 = (params.substr(4+params.indexOf(' to '))).trim();
if (params == '') return "this.echo('ERROR - variable missing for " + raw_intent + "')";
else if (params.indexOf(' to ') > -1)
return "save_text('" + abs_file(param2) + "'," + add_concat(param1) + ")"; else
return "save_text(''," + add_concat(params) + ")";}

function write_intent(raw_intent) {
var params = ((raw_intent + ' ').substr(1+(raw_intent + ' ').indexOf(' '))).trim();
var param1 = (params.substr(0,params.indexOf(' to '))).trim();
var param2 = (params.substr(4+params.indexOf(' to '))).trim();
if (params == '') return "this.echo('ERROR - variable missing for " + raw_intent + "')";
else if (params.indexOf(' to ') > -1)
return "append_text('" + abs_file(param2) + "'," + add_concat(param1) + ")"; else
return "append_text(''," + add_concat(params) + ")";}

function load_intent(raw_intent) {
var params = ((raw_intent + ' ').substr(1+(raw_intent + ' ').indexOf(' '))).trim();
var param1 = (params.substr(0,params.indexOf(' to '))).trim();
var param2 = (params.substr(4+params.indexOf(' to '))).trim();
if (params == '') return "this.echo('ERROR - filename missing for " + raw_intent + "')";
else if (params.indexOf(' to ') > -1)
return "var fs = require('fs'); " + param2 + " = ''; if (fs.exists('" + abs_file(param1) + "')) " + param2 +  " = fs.read('" + abs_file(param1) + "').trim(); else this.echo('ERROR - cannot find file " + param1 + "')"; else
return "this.echo('ERROR - variable missing for " + raw_intent + "')";}

function snap_intent(raw_intent) {
var params = ((raw_intent + ' ').substr(1+(raw_intent + ' ').indexOf(' '))).trim();
var param1 = (params.substr(0,params.indexOf(' to '))).trim();
var param2 = (params.substr(4+params.indexOf(' to '))).trim();
if ((params.toLowerCase() == 'page') || (param1.toLowerCase() == 'page')) {
if (params.indexOf(' to ') > -1) return "this.capture('" + abs_file(param2) + "')";
else return "this.capture(snap_image())";}
if (params == '') return "this.echo('ERROR - target missing for " + raw_intent + "')";
else if (params.indexOf(' to ') > -1)
{if (check_tx(param1)) return "this.captureSelector('" + abs_file(param2) + "',tx('" + param1 + "'))"; 
else return "this.echo('ERROR - cannot find " + param1 + "')";}
else {if (check_tx(params)) return "this.captureSelector(snap_image(),tx('" + params + "'))";
else return "this.echo('ERROR - cannot find " + params + "')";}}

function table_intent(raw_intent) {
var params = ((raw_intent + ' ').substr(1+(raw_intent + ' ').indexOf(' '))).trim();
var param1 = (params.substr(0,params.indexOf(' to '))).trim();
var param2 = (params.substr(4+params.indexOf(' to '))).trim();
if (params == '') return "this.echo('ERROR - target missing for " + raw_intent + "')";
else if (params.indexOf(' to ') > -1)
{if (check_tx(param1)) return "save_table('" + abs_file(param2) + "',tx('" + param1 + "'))";
else return "this.echo('ERROR - cannot find " + param1 + "')";}
else {if (check_tx(params)) return "save_table('',tx('" + params + "'))";
else return "this.echo('ERROR - cannot find " + params + "')";}}

function wait_intent(raw_intent) {
return "this.echo('ERROR - waiting for some time is not relevant in live mode')";}

function live_intent(raw_intent) {
return "this.echo('ERROR - you are already in live mode, type done to quit live mode')";}

function check_intent(raw_intent) {
return "this.echo('ERROR - step not supported in live mode, there is no conditions language parser')";}

function test_intent(raw_intent) {
return "this.echo('ERROR - use CasperJS tester module to professionally " + raw_intent + "\\nERROR - info at http://docs.casperjs.org/en/latest/modules/tester.html\\nERROR - support CSS selector or tx(\\'selector\\') for XPath algo by TagUI')";}

function frame_intent(raw_intent) {
return "this.echo('ERROR - step not supported in live mode, it is meant for trying single steps')";}

function popup_intent(raw_intent) {
return "this.echo('ERROR - step not supported in live mode, it is meant for trying single steps')";}

function api_intent(raw_intent) {
var params = ((raw_intent + ' ').substr(1+(raw_intent + ' ').indexOf(' '))).trim();
if (params == '') return "this.echo('ERROR - API URL missing for " + raw_intent + "')";
else return "api_result = call_api('" + params + "'); " +
"try {api_json = JSON.parse(api_result);} catch(e) {api_json = JSON.parse('null');}";}

function run_intent(raw_intent) {
return "this.echo('ERROR - step not supported in live mode, as run output cannot be retrieved')";}

function dom_intent(raw_intent) {
var params = ((raw_intent + ' ').substr(1+(raw_intent + ' ').indexOf(' '))).trim();
if (params == '') return "this.echo('ERROR - statement missing for " + raw_intent + "')";
else return "dom_result = this.evaluate(function(dom_json) {" + params + "}, dom_json)";}

function js_intent(raw_intent) {
var params = ((raw_intent + ' ').substr(1+(raw_intent + ' ').indexOf(' '))).trim();
if (params == '') return "this.echo('ERROR - statement missing for " + raw_intent + "')";
else return check_chrome_context(params);}

function timeout_intent(raw_intent) {
var params = ((raw_intent + ' ').substr(1+(raw_intent + ' ').indexOf(' '))).trim();
if (params == '') return "this.echo('ERROR - time in seconds missing for " + raw_intent + "')";
else return check_chrome_context("casper.options.waitTimeout = " + (parseFloat(params)*1000).toString() + ";");}

function code_intent(raw_intent) {
return check_chrome_context(raw_intent);}

function check_chrome_context(source_code) { // function to convert javascript code to chrome context
// specifically for live mode, as statements in flow file are already converted by tagui_parse.php
if (chrome_id == 0) return source_code; // if chrome or headless option is used, chrome_id will be > 0
source_code = source_code.replace(/casper\.exists/g,'chrome.exists').replace(/this\.exists/g,'chrome.exists');
source_code = source_code.replace(/casper\.click/g,'chrome.click').replace(/this\.click/g,'chrome.click');
source_code = source_code.replace(/casper\.mouse/g,'chrome.mouse').replace(/this\.mouse/g,'chrome.mouse');
source_code = source_code.replace(/casper\.sendKeys/g,'chrome.sendKeys').replace(/this\.sendKeys/g,'chrome.sendKeys');
source_code = source_code.replace(/casper\.selectOptionByValue/g,'chrome.selectOptionByValue').replace(/this\.selectOptionByValue/g,'chrome.selectOptionByValue');
source_code = source_code.replace(/casper\.countElements/g,'chrome.countElements').replace(/this\.countElements/g,'chrome.countElements');
source_code = source_code.replace(/casper\.elementVisible/g,'chrome.elementVisible').replace(/this\.elementVisible/g,'chrome.elementVisible');
source_code = source_code.replace(/casper\.fetchText/g,'chrome.fetchText').replace(/this\.fetchText/g,'chrome.fetchText');
source_code = source_code.replace(/casper\.capture/g,'chrome.capture').replace(/this\.capture/g,'chrome.capture');
source_code = source_code.replace(/casper\.captureSelector/g,'chrome.captureSelector').replace(/this\.captureSelector/g,'chrome.captureSelector');
source_code = source_code.replace(/chrome\.page\.uploadFile/g,'chrome.upload').replace(/casper\.page\.uploadFile/g,'chrome.upload').replace(/this\.page\.uploadFile/g,'chrome.upload');
source_code = source_code.replace(/casper\.download/g,'chrome.download').replace(/this\.download/g,'chrome.download');
source_code = source_code.replace(/casper\.evaluate/g,'chrome.evaluate').replace(/this\.evaluate/g,'chrome.evaluate');
source_code = source_code.replace(/casper\.getHTML/g,'chrome.getHTML').replace(/this\.getHTML/g,'chrome.getHTML');
source_code = source_code.replace(/casper\.getTitle/g,'chrome.getTitle').replace(/this\.getTitle/g,'chrome.getTitle');
source_code = source_code.replace(/casper\.getCurrentUrl/g,'chrome.getCurrentUrl').replace(/this\.getCurrentUrl/g,'chrome.getCurrentUrl');
source_code = source_code.replace(/casper\.debugHTML/g,'chrome.debugHTML').replace(/this\.debugHTML/g,'chrome.debugHTML');
source_code = source_code.replace(/casper\.reload/g,'chrome.reload').replace(/this\.reload/g,'chrome.reload');
source_code = source_code.replace(/casper\.back/g,'chrome.back').replace(/this\.back/g,'chrome.back');
source_code = source_code.replace(/casper\.forward/g,'chrome.forward').replace(/this\.forward/g,'chrome.forward');
return source_code;};

// for calling rest api url synchronously
function call_api(rest_url) { // advance users can define api_config for advance calls
// the api_config variable defaults to {method:'GET', header:[], body:{}}
var xhttp = new XMLHttpRequest(); xhttp.open(api_config.method, rest_url, false);
for (var item=0;item<api_config.header.length;item++) { // process headers
if (api_config.header[item] == '') continue; // skip if header is not defined
var header_value_pair = api_config.header[item].split(':'); // format is 'Header_name: header_value'
xhttp.setRequestHeader(header_value_pair[0].trim(),header_value_pair[1].trim());}
xhttp.send(JSON.stringify(api_config.body)); return xhttp.responseText;}

// custom function to handle dropdown option
casper.selectOptionByValue = function(selector, valueToMatch) { // solution posted in casperjs issue #1390
this.evaluate(function(selector, valueToMatch) {var found = false; // modified to allow xpath / css locators
if ((selector.indexOf('/') == 0) || (selector.indexOf('(') == 0)) var select = __utils__.getElementByXPath(selector);
else var select = document.querySelector(selector); // auto-select xpath or query css method to get element
if (valueToMatch == '[clear]') valueToMatch = ''; // [clear] keyword to allow selecting empty / blank option
Array.prototype.forEach.call(select.children, function(opt, i) { // loop through list to select option
if (!found && opt.value.indexOf(valueToMatch) !== -1) {select.selectedIndex = i; found = true;}});
var evt = document.createEvent("UIEvents"); // dispatch change event in case there is validation
evt.initUIEvent("change", true, true); select.dispatchEvent(evt);}, selector, valueToMatch);};

// custom function to return element visibility
casper.elementVisible = function(selector) {return casper.visible(selector);} // use casperjs to maximize compatibility

// custom function to return element count
casper.countElements = function(selector) { // use casperjs in-built function to maximize compatibility
var count_result = casper.evaluate(function(selector) {return __utils__.findAll(selector).length;},selector);
try {if (count_result > 0) return count_result; else return 0;} catch(e) {return 0;}};

// custom function from casperjs v1.1.5 (not yet released)
casper.waitForExec = function (command, parameters, then, onTimeout, timeout) {
    "use strict";
    var killTimeout;
    var utils = require('utils'); var f = utils.format; // custom added line from casper.js global
    if (utils.isArray(timeout)) {
        killTimeout = utils.isNumber(timeout[1]) ? getTimeoutAndCheckNextStepFunction(timeout[1], then, 'waitForExec', this.options.waitTimeout) : getTimeoutAndCheckNextStepFunction(timeout[0], then, 'waitForExec', this.options.waitTimeout);
        timeout = getTimeoutAndCheckNextStepFunction(timeout[0], then, 'waitForExec', this.options.waitTimeout);
    } else {
        timeout = getTimeoutAndCheckNextStepFunction(timeout, then, 'waitForExec', this.options.waitTimeout);
        killTimeout = timeout;
    }

    if ( (!utils.isString(command)) && (!utils.isArray(parameters))  ) {
        throw new CasperError("waitForExec() needs an command string as program and parameters separated by space to run or an array of parameters. if program is falsy or not a string, it uses default system shell");
    }
    if (utils.isFalsy(command) || !utils.isString(command)) {
        var system = require('system');
        command = (system.env.SHELL || system.env.ComSpec); // SHELL for UNIX(?), ComSpec for Windows(?)
        this.log('Casper.waitForExec()  is going to use default system shell ' + JSON.stringify(command) + ' - command is falsy or is not a string', "warning");
    }
    if (utils.isFalsy(parameters) || !utils.isArray(parameters)) {
        parameters = [];
    }

    // add use of a escape char like '\'??? (e.g.: '/bin/bash -c {\ ls\ /\ &&\ ls\ /home\ }' becomes ['/bin/bash', '-c', '{ ls / && ls /home }']
    command = command.split(' ');
    parameters = command.splice(-1,(command.length-1)).concat(parameters);
    command = command[0];
    var fs = require('fs');
    if (!fs.isExecutable(command)) {
        this.log('Casper.waitForExec() is going to call non executable file ' + JSON.stringify(command) + ' - maybe runs if is in PATH', "warning");
    }

    var spawn = require("child_process").spawn;
    var stdout = ''; // VARIABLE TO STORE PROGRAM STDOUT
    var stderr = ''; // VARIABLE TO STORE PROGRAM STDERR
    var exitCode = null; // VARIABLE TO STORE PROGRAM EXIT CODE
    var realPid = null; // VARIABLE TO STORE PROGRAM REAL PID
    var elapsedTime = null; // VARIABLE TO STORE PROGRAM DURATION

    var childStartTime = new Date().getTime();
    var child = spawn(command, parameters);
    realPid = child.pid;

    child.stdout.on("data", function (standardOut) { // keeps stdout updated
        stdout += standardOut;
    });
    child.stderr.on("data", function (standardError) { // keeps stderr updated
        stderr += standardError;
    });
    child.on("exit", function (code) {
        elapsedTime = (new Date().getTime()) - childStartTime;
        exitCode = code;
    });

    function __details() {
        return {data: {command: command, parameters: parameters, pid: realPid, stdout: stdout, stderr: stderr, exitCode: exitCode, elapsedTime: elapsedTime, isChildNotFinished: child.pid }};
    }
    function __onTimeout(timeout, details) {
        var __onWaitTimeout = onTimeout ? onTimeout : this.options.onWaitTimeout;
        var signalToKill = "SIGTERM";
        child.kill(signalToKill);
        
        killTimeout = getTimeoutAndCheckNextStepFunction(killTimeout, __onWaitTimeout, 'killAndCallOnWaitTimeout', this.options.waitTimeout, false);
        (function killAndCallOnWaitTimeout() {
            // I don't know if it should return
            // return this.waitFor(function isProgramKilled() { // HAVE TO ADD waitFor() TO MAKE child.on("exit"... UPDATES exitCode AND TO child.pid BE UPDATED
            this.waitFor(function isProgramKilled() { // HAVE TO ADD waitFor() TO MAKE child.on("exit"... UPDATES exitCode AND TO child.pid BE UPDATED
                return !child.pid;
            }, function onProgramKilled() { 
                    this.log(f("waitForExec() has killed %s %s (PID %d) with %s", details.data.command, JSON.stringify(details.data.parameters), details.data.pid, signalToKill), "info");
                    // this.then(this.createStep(__onWaitTimeout, timeout, __details()));
                    __onWaitTimeout.call(this, timeout, __details());
            }, function onProgramNotKilled() {
                    this.log(f("waitForExec() has not killed %s %s (PID %d) with %s", details.data.command, JSON.stringify(details.data.parameters), details.data.pid, signalToKill), "warning");
                    signalToKill = (require('system').os.name !== "windows") ? "SIGKILL" : "WM_QUIT"; // "WM_QUIT" SEEMS TO BE THE WINDOWS EQUIVALENT TO UNIX SIGKILL
                    child.kill(signalToKill);
                    killTimeout = 1;
                    killAndCallOnWaitTimeout.call(this);
            }, killTimeout);
        }).call(this);
    }
    this.log(f("waitForExec() called %s (PID %d) with %s arguments", JSON.stringify(command), realPid, JSON.stringify(parameters)), "info");
    return this.waitFor(function isProgramFinished() {
        return !child.pid;
    }, function onProgramFinished() {
        this.then(this.createStep(then, __details()));
    }, __onTimeout, timeout, __details());
};

// dependency function of casper.waitForExec in v1.1.5 (not yet released)
function getTimeoutAndCheckNextStepFunction(timeout, then, methodName, defaultTimeout, isThenRequired) {
    var utils = require('utils'); // custom added line from casper.js global
    if (isThenRequired || then) {
        var isFunction = utils.isFunction(then); // Optimization to perform "isFunction" check only once.

        if (isThenRequired && !isFunction) {
            throw new CasperError(methodName + "() needs a step function");
        } else if (then && !isFunction) {
            throw new CasperError(methodName + "() next step definition must be a function");
        }
    }

    timeout = ~~timeout || ~~defaultTimeout;
    if (timeout < 0) {
        throw new CasperError(methodName + "() only accepts an integer >= 0 as a timeout value");
    }

    return timeout;
}

// flow path for save_text and snap_image
