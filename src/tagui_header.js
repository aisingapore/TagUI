
// xpath for object identification
var x = require('casper').selectXPath;

// assign parameters to p1-p9 variables
var p1 = casper.cli.raw.get(0); var p2 = casper.cli.raw.get(1); var p3 = casper.cli.raw.get(2);
var p4 = casper.cli.raw.get(3); var p5 = casper.cli.raw.get(4); var p6 = casper.cli.raw.get(5);
var p7 = casper.cli.raw.get(6); var p8 = casper.cli.raw.get(7); var p9 = casper.cli.raw.get(8);

// save start time to measure execution time
var automation_start_time = Date.now(); casper.echo('\nSTART - automation started - ' + Date().toLocaleString());

// initialise default global variables
var quiet_mode = false; var save_text_count = 0; var snap_image_count = 0; var sikuli_count = 0; var chrome_id = 0;

// variable for advance usage of api step
var api_config = {method:'GET', header:[], body:{}};

// techo function for handling quiet option
function techo(echo_string) {if (!quiet_mode) casper.echo(echo_string); return;}

// for muting echo in test automation scripts
function dummy_echo(muted_string) {return;}

// for saving text information to file
function save_text(file_name,info_text) {var ds; if (flow_path.indexOf('/') !== -1) ds = '/'; else ds = '\\';
if (!file_name) {save_text_count++; file_name = flow_path + ds + 'text' + save_text_count.toString() + '.txt';}
var fs = require('fs'); fs.write(file_name, info_text, 'w');}

// for saving snapshots of website to file
function snap_image() {var ds; if (flow_path.indexOf('/') !== -1) ds = '/'; else ds = '\\';
snap_image_count++; return (flow_path + ds + 'snap' + snap_image_count.toString() + '.png');}

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

function sleep(ms) { // helper to add delay during loops
var start = new Date().getTime(); var end = start;
while(end < start + ms) {end = new Date().getTime();}}

// for initialising integration with sikuli visual automation
function sikuli_handshake() {techo('waiting for sikuli');
var ds; if (flow_path.indexOf('/') !== -1) ds = '/'; else ds = '\\';
var fs = require('fs'); fs.write('tagui.sikuli'+ds+'tagui_sikuli.in','','w'); var sikuli_handshake = '';
if (!fs.exists('tagui.sikuli'+ds+'tagui_sikuli.out')) fs.write('tagui.sikuli'+ds+'tagui_sikuli.out','','w');
do {sleep(1000); sikuli_handshake = fs.read('tagui.sikuli'+ds+'tagui_sikuli.out').trim();}
while (sikuli_handshake !== '[0] START');
techo('connected to sikuli');}

// for using sikuli visual automation instead of casperjs
function sikuli_step(sikuli_intent) {sikuli_count++;
if (sikuli_count == 1) sikuli_handshake(); // handshake on first call
var ds; if (flow_path.indexOf('/') !== -1) ds = '/'; else ds = '\\';
var fs = require('fs'); fs.write('tagui.sikuli'+ds+'tagui_sikuli.in','['+sikuli_count.toString()+'] '+sikuli_intent,'w');
var sikuli_result = ''; do {sleep(1000); sikuli_result = fs.read('tagui.sikuli'+ds+'tagui_sikuli.out').trim();}
while (sikuli_result.indexOf('['+sikuli_count.toString()+'] ') == -1);
if (sikuli_result.indexOf('SUCCESS') !== -1) return true; else return false;}

if (chrome_id > 0) { // super large if block to load chrome related functions if chrome or headless option is used
chrome_id = 0; // reset chrome_id from 1 back to 0 to prepare for initial call of chrome_step

// for initialising integration with chrome web browser
function chrome_handshake() {// techo('waiting for chrome');
var fs = require('fs'); fs.write('tagui_chrome.in','','w'); var chrome_handshake = '';
if (!fs.exists('tagui_chrome.out')) fs.write('tagui_chrome.out','','w');
do {sleep(50); chrome_handshake = fs.read('tagui_chrome.out').trim();}
while (chrome_handshake !== '[0] START'); //techo('connected to chrome');
}

// send websocket message to chrome browser using chrome debugging protocol
// php helper process tagui_chrome.php running to handle this concurrently
function chrome_step(method,params) {chrome_id++;
if (chrome_id == 1) chrome_handshake(); // handshake on first call
var chrome_intent = JSON.stringify({'id': chrome_id, 'method': method, 'params': params});
var fs = require('fs'); fs.write('tagui_chrome.in','['+chrome_id.toString()+'] '+chrome_intent,'w');
var chrome_result = ''; do {sleep(50); chrome_result = fs.read('tagui_chrome.out').trim();}
while (chrome_result.indexOf('['+chrome_id.toString()+'] ') == -1);
return chrome_result.substring(chrome_result.indexOf('] ')+2);}

// chrome object for handling integration with chrome or headless chrome
var chrome = new Object(); chrome.mouse = new Object();

// chrome methods as casper methods replacement for chrome integration
chrome.exists = function(selector) { // different handling for xpath and css
if ((selector.toString().length >= 16) && (selector.toString().substr(0,16) == 'xpath selector: '))
{if (selector.toString().length == 16) selector = ''; else selector = selector.toString().substring(16);
var ws_message = chrome_step('Runtime.evaluate',{expression: 'document.evaluate(\''+selector+'\',document,null,XPathResult.ORDERED_NODE_SNAPSHOT_TYPE,null).snapshotLength'});
try {var ws_json = JSON.parse(ws_message); if (ws_json.result.result.value > 0) return true; else return false;}
catch(e) {return false;}}
else {var ws_message = chrome_step('Runtime.evaluate',{expression: 'document.querySelector(\''+selector+'\')'});
try {var ws_json = JSON.parse(ws_message); if (ws_json.result.result.subtype == 'node') return true; else return false;}
catch(e) {return false;}}};

chrome.click = function(selector) {
if ((selector.toString().length >= 16) && (selector.toString().substr(0,16) == 'xpath selector: '))
{if (selector.toString().length == 16) selector = ''; else selector = selector.toString().substring(16);
chrome_step('Runtime.evaluate',{expression: 'document.evaluate(\''+selector+'\',document,null,XPathResult.ORDERED_NODE_SNAPSHOT_TYPE,null).snapshotItem(0).click()'});}
else chrome_step('Runtime.evaluate',{expression: 'document.querySelector(\''+selector+'\').click()'});};

chrome.mouse.move = function(selector) {
};

chrome.mouse.click = function(selector) {
};

chrome.mouse.doubleclick = function(selector) {
};

chrome.mouse.rightclick = function(selector) {
};

chrome.mouse.down = function(selector) {
};

chrome.mouse.up = function(selector) {
};

chrome.sendKeys = function(selector,value) {
};

chrome.selectOptionByValue = function(selector,value) {
};

chrome.fetchText = function(selector) {
return '';
};

chrome.capture = function(filename) {
};

chrome.captureSelector = function(filename,selector) {
};

chrome.download = function(url,filename) {
};

chrome.evaluate = function(statement) {
};

chrome.getHTML = function() {
return '';
};

chrome.getTitle = function() {
var ws_message = chrome_step('Runtime.evaluate',{expression: 'document.title'});
try {var ws_json = JSON.parse(ws_message); return ws_json.result.result.value;} catch(e) {return '';}};

chrome.getCurrentUrl = function() {
var ws_message = chrome_step('Runtime.evaluate',{expression: 'document.location.href'});
try {var ws_json = JSON.parse(ws_message); return ws_json.result.result.value;} catch(e) {return '';}};

chrome.echo = function(value) {casper.echo(value);};

chrome.on = function(value,statement) {casper.on(value,statement);};

} // end of super large if block to load chrome related functions if chrome or headless option is used

// for live mode simple parsing of tagui steps into js code
function tagui_parse(raw_input) {return parse_intent(raw_input);}

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
case 'down': return down_intent(live_line); break;
case 'receive': return receive_intent(live_line); break;
case 'echo': return echo_intent(live_line); break;
case 'save': return save_intent(live_line); break;
case 'dump': return dump_intent(live_line); break;
case 'snap': return snap_intent(live_line); break;
case 'wait': return wait_intent(live_line); break;
case 'live': return live_intent(live_line); break;
case 'check': return check_intent(live_line); break;
case 'test': return test_intent(live_line); break;
case 'frame': return frame_intent(live_line); break;
case 'popup': return popup_intent(live_line); break;
case 'api': return api_intent(live_line); break;
case 'dom': return dom_intent(live_line); break;
case 'js': return js_intent(live_line); break;
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
if ((lc_raw_intent.substr(0,5) == 'down ') || (lc_raw_intent.substr(4,5) == 'load ')) return 'down';
if (lc_raw_intent.substr(0,8) == 'receive ') return 'receive';
if (lc_raw_intent.substr(0,5) == 'echo ') return 'echo';
if (lc_raw_intent.substr(0,5) == 'save ') return 'save';
if (lc_raw_intent.substr(0,5) == 'dump ') return 'dump';
if (lc_raw_intent.substr(0,5) == 'snap ') return 'snap';
if (lc_raw_intent.substr(0,5) == 'wait ') return 'wait';
if (lc_raw_intent.substr(0,5) == 'live ') return 'live';
if (lc_raw_intent.substr(0,6) == 'check ') return 'check';
if (lc_raw_intent.substr(0,5) == 'test ') return 'test';
if (lc_raw_intent.substr(0,6) == 'frame ') return 'frame';
if (lc_raw_intent.substr(0,6) == 'popup ') return 'popup';
if (lc_raw_intent.substr(0,4) == 'api ') return 'api';
if (lc_raw_intent.substr(0,4) == 'dom ') return 'dom';
if (lc_raw_intent.substr(0,3) == 'js ') return 'js';

// second set of conditions check for valid keywords with missing parameters
if ((lc_raw_intent == 'tap') || (lc_raw_intent == 'click')) return 'tap';
if ((lc_raw_intent == 'hover') || (lc_raw_intent == 'move')) return 'hover';
if ((lc_raw_intent == 'type') || (lc_raw_intent == 'enter')) return 'type';
if ((lc_raw_intent == 'select') || (lc_raw_intent == 'choose')) return 'select';
if ((lc_raw_intent == 'read') || (lc_raw_intent == 'fetch')) return 'read';
if ((lc_raw_intent == 'show') || (lc_raw_intent == 'print')) return 'show';
if ((lc_raw_intent == 'down') || (lc_raw_intent == 'download')) return 'down';
if (lc_raw_intent == 'receive') return 'receive';
if (lc_raw_intent == 'echo') return 'echo';
if (lc_raw_intent == 'save') return 'save';
if (lc_raw_intent == 'dump') return 'dump';
if (lc_raw_intent == 'snap') return 'snap';
if (lc_raw_intent == 'wait') return 'wait';
if (lc_raw_intent == 'live') return 'live';
if (lc_raw_intent == 'check') return 'check';
if (lc_raw_intent == 'test') return 'test';
if (lc_raw_intent == 'frame') return 'frame';
if (lc_raw_intent == 'popup') return 'popup';
if (lc_raw_intent == 'api') return 'api';
if (lc_raw_intent == 'dom') return 'dom';
if (lc_raw_intent == 'js') return 'js';

// final check for recognized code before returning error
if (is_code(raw_intent)) return 'code'; else return 'error';}

function is_code(raw_intent) {
// due to asynchronous waiting for element, if/for/while can work for parsing single step
// other scenarios can be assumed to behave as unparsed javascript in casperjs context
if ((raw_intent.substr(0,4) == 'var ') || (raw_intent.substr(0,3) == 'do ')) return true;
if ((raw_intent.substr(0,1) == '{') || (raw_intent.substr(0,1) == '}')) return true;
if ((raw_intent.charAt(raw_intent.length-1) == '{') || (raw_intent.charAt(raw_intent.length-1) == '}')) return true;
if ((raw_intent.substr(0,3) == 'if ') || (raw_intent.substr(0,4) == 'else')) return true;
if ((raw_intent.substr(0,4) == 'for ') || (raw_intent.substr(0,6) == 'while ')) return true;
if ((raw_intent.substr(0,7) == 'switch ') || (raw_intent.substr(0,5) == 'case ')) return true;
if ((raw_intent.substr(0,6) == 'break;') || (raw_intent.substr(0,9) == 'function ')) return true;
if ((raw_intent.substr(0,7) == 'casper.') || (raw_intent.substr(0,5) == 'this.')) return true;
if (raw_intent.substr(0,7) == 'chrome.') return true; // chrome object for chrome integration
if (raw_intent.substr(0,5) == 'test.') return true;
if ((raw_intent.substr(0,2) == '//') || (raw_intent.charAt(raw_intent.length-1) == ';')) return true; return false;}

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
return "this.echo('ERROR - step not supported in live mode')";}

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
{if (param2.indexOf('[enter]') == -1) return "this.sendKeys(tx('" + param1 + "'),'" + param2 + "')";
else // special handling to send enter key events
{param2 = param2.replace(/\[enter\]/g,"',{keepFocus: true}); this.sendKeys(tx('" + param1 + "'),casper.page.event.key.Enter,{keepFocus: true}); this.sendKeys(tx('" + param1 + "'),'");
return "this.sendKeys(tx('" + param1 + "'),'" + param2 + "',{keepFocus: true});";}}
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
else if (check_tx(params)) return "this.echo('" + raw_intent + "' + ' - ' + this.fetchText(tx('" + params + "')).trim())";else return "this.echo('ERROR - cannot find " + params + "')";}

function down_intent(raw_intent) {
var params = ((raw_intent + ' ').substr(1+(raw_intent + ' ').indexOf(' '))).trim();
var param1 = (params.substr(0,params.indexOf(' to '))).trim();
var param2 = (params.substr(4+params.indexOf(' to '))).trim();
if ((param1 == '') || (param2 == '')) return "this.echo('ERROR - url/filename missing for " + raw_intent + "')";
else return "this.download('" + param1 + "','" + abs_file(param2) + "')";}

function receive_intent(raw_intent) {
return "this.echo('ERROR - step not supported in live mode')";}

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

function wait_intent(raw_intent) {
return "this.echo('ERROR - step not supported in live mode')";}

function live_intent(raw_intent) {
return "this.echo('ERROR - step not supported in live mode')";}

function check_intent(raw_intent) {
return "this.echo('ERROR - step not supported in live mode')";}

function test_intent(raw_intent) {
return "this.echo('ERROR - use CasperJS tester module to professionally " + raw_intent + "\\nERROR - info at http://docs.casperjs.org/en/latest/modules/tester.html\\nERROR - support CSS selector or tx(\\'selector\\') for XPath algo by TagUI')";}

function frame_intent(raw_intent) {
return "this.echo('ERROR - step not supported in live mode')";}

function popup_intent(raw_intent) {
return "this.echo('ERROR - step not supported in live mode')";}

function api_intent(raw_intent) {
var params = ((raw_intent + ' ').substr(1+(raw_intent + ' ').indexOf(' '))).trim();
if (params == '') return "this.echo('ERROR - API URL missing for " + raw_intent + "')";
else return "api_result = call_api('" + params + "'); this.echo(api_result); " +
"try {api_json = JSON.parse(api_result);} catch(e) {api_json = JSON.parse('null');}";}

function dom_intent(raw_intent) {
var params = ((raw_intent + ' ').substr(1+(raw_intent + ' ').indexOf(' '))).trim();
if (params == '') return "this.echo('ERROR - statement missing for " + raw_intent + "')";
else return "this.evaluate(function() {" + params + "})";}

function js_intent(raw_intent) {
var params = ((raw_intent + ' ').substr(1+(raw_intent + ' ').indexOf(' '))).trim();
if (params == '') return "this.echo('ERROR - statement missing for " + raw_intent + "')";
else return params;}

function code_intent(raw_intent) {
return raw_intent;}

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
Array.prototype.forEach.call(select.children, function(opt, i) { // loop through list to select option
if (!found && opt.value.indexOf(valueToMatch) !== -1) {select.selectedIndex = i; found = true;}});
var evt = document.createEvent("UIEvents"); // dispatch change event in case there is validation
evt.initUIEvent("change", true, true); select.dispatchEvent(evt);}, selector, valueToMatch);};

// flow path for save_text and snap_image
