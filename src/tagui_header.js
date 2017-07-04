
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
var time_now = new Date().getTime(); var time_end = time_now + ms;
while(time_now < time_end) {time_now = new Date().getTime();}}

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
do {sleep(100); chrome_handshake = fs.read('tagui_chrome.out').trim();}
while (chrome_handshake !== '[0] START'); //techo('connected to chrome');
}

// send websocket message to chrome browser using chrome debugging protocol
// php helper process tagui_chrome.php running to handle this concurrently
function chrome_step(method,params) {chrome_id++;
if (chrome_id == 1) chrome_handshake(); // handshake on first call
var chrome_intent = JSON.stringify({'id': chrome_id, 'method': method, 'params': params});
var fs = require('fs'); fs.write('tagui_chrome.in','['+chrome_id.toString()+'] '+chrome_intent,'w');
var chrome_result = ''; do {sleep(100); chrome_result = fs.read('tagui_chrome.out').trim();}
while (chrome_result.indexOf('['+chrome_id.toString()+'] ') == -1);
return chrome_result.substring(chrome_result.indexOf('] ')+2);}

// chrome object for handling integration with chrome or headless chrome
var chrome = new Object(); chrome.mouse = new Object();

// chrome methods as casper methods replacement for chrome integration
chrome.exists = function(selector) { // different handling for xpath and css
if ((selector.toString().length >= 16) && (selector.toString().substr(0,16) == 'xpath selector: '))
{if (selector.toString().length == 16) selector = ''; else selector = selector.toString().substring(16);
var ws_message = chrome_step('Runtime.evaluate',{expression: 'document.evaluate(\''+selector+'\',document,null,XPathResult.ORDERED_NODE_SNAPSHOT_TYPE,null).snapshotLength'});}
else var ws_message = chrome_step('Runtime.evaluate',{expression: 'document.querySelectorAll(\''+selector+'\').length'});
try {var ws_json = JSON.parse(ws_message); if (ws_json.result.result.value > 0) return true; else return false;}
catch(e) {return false;}};

chrome.click = function(selector) { // click by sending direct click event instead of mouse down/up/click
if ((selector.toString().length >= 16) && (selector.toString().substr(0,16) == 'xpath selector: '))
{if (selector.toString().length == 16) selector = ''; else selector = selector.toString().substring(16);
chrome_step('Runtime.evaluate',{expression: 'document.evaluate(\''+selector+'\',document,null,XPathResult.ORDERED_NODE_SNAPSHOT_TYPE,null).snapshotItem(0).click()'});}
else chrome_step('Runtime.evaluate',{expression: 'document.querySelector(\''+selector+'\').click()'});};

chrome.mouse.action = function(type,x,y,button,clickCount) { // helper function to send various mouse events
chrome_step('Input.dispatchMouseEvent',{type: type, x: x, y: y, button: button, clickCount: clickCount});};

chrome.mouse.getXY = function(selector) { // helper function to get xy center coordinates of selector
if ((selector.toString().length >= 16) && (selector.toString().substr(0,16) == 'xpath selector: '))
{if (selector.toString().length == 16) selector = ''; else selector = selector.toString().substring(16);
var ws_message = chrome_step('Runtime.evaluate',{expression: 'var result_bounds = document.evaluate(\''+selector+'\',document,null,XPathResult.ORDERED_NODE_SNAPSHOT_TYPE,null).snapshotItem(0).getBoundingClientRect(); var result_xy = {x: Math.round(result_bounds.left + result_bounds.width / 2), y: Math.round(result_bounds.top + result_bounds.height / 2)}; result_xy', returnByValue: true});}
else var ws_message = chrome_step('Runtime.evaluate',{expression: 'var result_bounds = document.querySelector(\''+selector+'\').getBoundingClientRect(); var result_xy = {x: Math.round(result_bounds.left + result_bounds.width / 2), y: Math.round(result_bounds.top + result_bounds.height / 2)}; result_xy', returnByValue: true});
try {var ws_json = JSON.parse(ws_message); if (ws_json.result.result.value.x > 0 && ws_json.result.result.value.y > 0)
return ws_json.result.result.value; else return {x: 0, y: 0};} catch(e) {return {x: 0, y: 0};}};

chrome.getRect = function(selector) { // helper function to get rectangle coordinates of selector
if ((selector.toString().length >= 16) && (selector.toString().substr(0,16) == 'xpath selector: '))
{if (selector.toString().length == 16) selector = ''; else selector = selector.toString().substring(16);
var ws_message = chrome_step('Runtime.evaluate',{expression: 'var result_bounds = document.evaluate(\''+selector+'\',document,null,XPathResult.ORDERED_NODE_SNAPSHOT_TYPE,null).snapshotItem(0).getBoundingClientRect(); var result_rect = {top: Math.round(result_bounds.top), left: Math.round(result_bounds.left), width: Math.round(result_bounds.width), height: Math.round(result_bounds.height)}; result_rect', returnByValue: true});}
else var ws_message = chrome_step('Runtime.evaluate',{expression: 'var result_bounds = document.querySelector(\''+selector+'\').getBoundingClientRect(); var result_rect = {top: Math.round(result_bounds.top), left: Math.round(result_bounds.left), width: Math.round(result_bounds.width), height: Math.round(result_bounds.height)}; result_rect', returnByValue: true});
try {var ws_json = JSON.parse(ws_message);
if (ws_json.result.result.value.width > 0 && ws_json.result.result.value.height > 0) return ws_json.result.result.value;
else return {left: 0, top: 0, width: 0, height: 0};} catch(e) {return {left: 0, top: 0, width: 0, height: 0};}};

chrome.mouse.move = function(selector) { // move mouse pointer to center of specified selector
var xy = chrome.mouse.getXY(selector); chrome.mouse.action('mouseMoved',xy.x,xy.y,'none',0);};

chrome.mouse.click = function(selector) { // press and release on center of specfied selector
var xy = chrome.mouse.getXY(selector); // get coordinates to use from returned object containing x and y
chrome.mouse.action('mousePressed',xy.x,xy.y,'left',1); chrome.mouse.action('mouseReleased',xy.x,xy.y,'left',1);};

chrome.mouse.doubleclick = function(selector) { // double press and release on center of selector
var xy = chrome.mouse.getXY(selector); // get coordinates to use from returned object containing x and y
chrome.mouse.action('mousePressed',xy.x,xy.y,'left',1); chrome.mouse.action('mouseReleased',xy.x,xy.y,'left',1);
chrome.mouse.action('mousePressed',xy.x,xy.y,'left',1); chrome.mouse.action('mouseReleased',xy.x,xy.y,'left',1);};

chrome.mouse.rightclick = function(selector) { // right click press and release on center of selector 
var xy = chrome.mouse.getXY(selector); // get coordinates to use from returned object containing x and y
chrome.mouse.action('mousePressed',xy.x,xy.y,'right',1); chrome.mouse.action('mouseReleased',xy.x,xy.y,'right',1);};

chrome.mouse.down = function(selector) { // left press on center of specified selector
var xy = chrome.mouse.getXY(selector); chrome.mouse.action('mousePressed',xy.x,xy.y,'left',1);};

chrome.mouse.up = function(selector) { // left release on center of specified selector
var xy = chrome.mouse.getXY(selector); chrome.mouse.action('mouseReleased',xy.x,xy.y,'left',1);};

chrome.sendKeys = function(selector,value,options) { // send key strokes to specified selector
if (value == casper.page.event.key.Enter) value = '\r';
if ((selector.toString().length >= 16) && (selector.toString().substr(0,16) == 'xpath selector: '))
{if (selector.toString().length == 16) selector = ''; else selector = selector.toString().substring(16);
chrome_step('Runtime.evaluate',{expression: 'document.evaluate(\''+selector+'\',document,null,XPathResult.ORDERED_NODE_SNAPSHOT_TYPE,null).snapshotItem(0).focus()'});}
else chrome_step('Runtime.evaluate',{expression: 'document.querySelector(\''+selector+'\').focus()'});
for (var character = 0, length = value.length; character < length; character++) {
chrome_step('Input.dispatchKeyEvent',{type: 'char', text: value[character]});}};

chrome.selectOptionByValue = function(selector,valueToMatch) { // select dropdown option (base on casperjs issue #1390)
chrome.evaluate('function() {var selector = \''+selector+'\'; var valueToMatch = \''+valueToMatch+'\'; var found = false; if ((selector.indexOf(\'/\') == 0) || (selector.indexOf(\'(\') == 0)) var select = document.evaluate(selector,document,null,XPathResult.ORDERED_NODE_SNAPSHOT_TYPE,null).snapshotItem(0); else var select = document.querySelector(selector); Array.prototype.forEach.call(select.children, function(opt, i) {if (!found && opt.value.indexOf(valueToMatch) !== -1) {select.selectedIndex = i; found = true;}}); var evt = document.createEvent("UIEvents"); evt.initUIEvent("change", true, true); select.dispatchEvent(evt);}');};

chrome.fetchText = function(selector) { // grab text from selector following casperjs logic
if ((selector.toString().length >= 16) && (selector.toString().substr(0,16) == 'xpath selector: '))
{if (selector.toString().length == 16) selector = ''; else selector = selector.toString().substring(16);
var ws_message = chrome_step('Runtime.evaluate',{expression: 'document.evaluate(\''+selector+'\',document,null,XPathResult.ORDERED_NODE_SNAPSHOT_TYPE,null).snapshotItem(0).textContent || document.evaluate(\''+selector+'\',document,null,XPathResult.ORDERED_NODE_SNAPSHOT_TYPE,null).snapshotItem(0).innerText || document.evaluate(\''+selector+'\',document,null,XPathResult.ORDERED_NODE_SNAPSHOT_TYPE,null).snapshotItem(0).value || \'\''});}
else var ws_message = chrome_step('Runtime.evaluate',{expression: 'document.querySelector(\''+selector+'\').textContent || document.querySelector(\''+selector+'\').innerText || document.querySelector(\''+selector+'\').value || \'\''});
try {var ws_json = JSON.parse(ws_message); if (ws_json.result.result.value)
return ws_json.result.result.value; else return '';} catch(e) {return '';}};

chrome.decode = function(str) { // funtion to convert base64 data to binary string
// used in https://github.com/casperjs/casperjs/blob/master/modules/clientutils.js
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
var format = 'png'; var quality = 80; var fromSurface = true; var screenshot_data = '';
if ((filename.substr(-3).toLowerCase() == 'jpg') || (filename.substr(-4).toLowerCase() == 'jpeg')) format = 'jpeg';
var ws_message = chrome_step('Page.captureScreenshot',{format: format, quality: quality, fromSurface: fromSurface});
try {var ws_json = JSON.parse(ws_message); screenshot_data = ws_json.result.data;} catch(e) {screenshot_data = '';}
var fs = require('fs'); fs.write(filename, chrome.decode(screenshot_data), 'wb');};

chrome.captureSelector = function(filename,selector) { // capture screenshot of selector to png/jpg/jpeg format
// first capture entire screen, then use casperjs / phantomjs browser to crop image base on selector dimensions
chrome.capture(filename); var selector_rect = chrome.getRect(selector); // so that there is no extra dependency
if (selector_rect.width > 0 && selector_rect.height > 0) // from using other libraries or creating html canvas 
casper.thenOpen(filename, function() {casper . capture(filename, // spaces around . intentional to avoid replacing 
{top: selector_rect.top, left: selector_rect.left, width: selector_rect.width, height: selector_rect.height});
casper . thenOpen('about:blank');});}; // reset phantomjs browser state, spaces intentional to avoid replacing

chrome.download = function(url,filename) { // download function for downloading url resource to file
// casper download cannot be used for url which requires login as casperjs context is not in chrome
// the chromium issue seems to be moving, otherwise another way may be to inject casper clientutils.js
casper.echo('ERROR - for visible Chrome, download file directly through normal webpage interaction');
casper.echo('ERROR - for headless Chrome, it prevents file download for now - Chromium issue 696481');};

chrome.evaluate = function(fn_statement) { // evaluate expression in browser dom context
// chrome runtime.evaluate is different from casperjs evaluate, do some processing to reduce gap
var statement = fn_statement.toString(); statement = statement.slice(statement.indexOf('{')+1,statement.lastIndexOf('}'));
statement = statement.replace(/return /g,''); // defining as function() and return is invalid for chrome
var ws_message = chrome_step('Runtime.evaluate',{expression: statement}); // statements can be separated by ;
try {var ws_json = JSON.parse(ws_message); if (ws_json.result.result.value)
return ws_json.result.result.value; else return '';} catch(e) {return '';}};

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
try {var ws_json = JSON.parse(ws_message); if (ws_json.result.result.value)
return ws_json.result.result.value; else return '';} catch(e) {return '';}};

chrome.debugHTML = function() {casper.echo(chrome.getHTML());}; // print raw html of current webpage

chrome.reload = function() { // reload the current webpage
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
else return "dom_result = this.evaluate(function() {" + params + "})";}

function js_intent(raw_intent) {
var params = ((raw_intent + ' ').substr(1+(raw_intent + ' ').indexOf(' '))).trim();
if (params == '') return "this.echo('ERROR - statement missing for " + raw_intent + "')";
else return check_chrome_context(params);}

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
source_code = source_code.replace(/casper\.fetchText/g,'chrome.fetchText').replace(/this\.fetchText/g,'chrome.fetchText');
source_code = source_code.replace(/casper\.capture/g,'chrome.capture').replace(/this\.capture/g,'chrome.capture');
source_code = source_code.replace(/casper\.captureSelector/g,'chrome.captureSelector').replace(/this\.captureSelector/g,'chrome.captureSelector');
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
Array.prototype.forEach.call(select.children, function(opt, i) { // loop through list to select option
if (!found && opt.value.indexOf(valueToMatch) !== -1) {select.selectedIndex = i; found = true;}});
var evt = document.createEvent("UIEvents"); // dispatch change event in case there is validation
evt.initUIEvent("change", true, true); select.dispatchEvent(evt);}, selector, valueToMatch);};

// flow path for save_text and snap_image
