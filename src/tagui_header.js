
// xpath for object identification
var x = require('casper').selectXPath;

// assign parameters to p1-p9 variables
var p1 = casper.cli.raw.get(0); var p2 = casper.cli.raw.get(1); var p3 = casper.cli.raw.get(2);
var p4 = casper.cli.raw.get(3); var p5 = casper.cli.raw.get(4); var p6 = casper.cli.raw.get(5);
var p7 = casper.cli.raw.get(6); var p8 = casper.cli.raw.get(7); var p9 = casper.cli.raw.get(8);

// save start time to measure execution time
var automation_start_time = Date.now(); casper.echo('\nSTART - automation started - ' + Date().toLocaleString());

// initialise other global variables
var save_text_count = 0; var snap_image_count = 0;

// muting echo in test automation scripts
function dummy_echo(muted_string) {return;}

// saving text information to file
function save_text(file_name,info_text) {
if (!file_name) {save_text_count++; file_name = flow_path + '/' + 'text' + save_text_count.toString() + '.txt';}
var fs = require('fs'); fs.write(file_name, info_text, 'w');}

// saving snapshots of website to file
function snap_image() {snap_image_count++; return (flow_path + '/' + 'snap' + snap_image_count.toString() + '.png');}

// adding synchronous suspend capability
function sleep(delay_in_ms) {var start_time = new Date().getTime();
for (var sleep_count = 0; sleep_count < 1e7; sleep_count++)
{if ((new Date().getTime() - start_time) > delay_in_ms) break;}}

// checking if selector is css selector
function is_css_selector(selector) {
if (selector.length == 0) return false; if (selector.charAt(0) == '[') return true;
if (selector.charAt(0) == '.') return true; if (selector.charAt(0) == '#') return true;
if (selector.substring(0,4) == 'div ') return true; if (selector.substring(0,4) == 'div:') return true;
if (selector.substring(0,4) == 'div[') return true; if (selector.substring(0,2) == 'p ') return true;
if (selector.substring(0,2) == 'p:') return true; if (selector.substring(0,2) == 'p[') return true;
if (selector.substring(0,6) == 'input ') return true; if (selector.substring(0,6) == 'input:') return true;
if (selector.substring(0,6) == 'input[') return true; if (selector.substring(0,7) == 'button ') return true;
if (selector.substring(0,7) == 'button:') return true; if (selector.substring(0,7) == 'button[') return true;
if (selector.substring(0,3) == 'li ') return true; if (selector.substring(0,3) == 'li:') return true;
if (selector.substring(0,3) == 'li[') return true; if (selector.substring(0,3) == 'tr ') return true;
if (selector.substring(0,3) == 'tr:') return true; if (selector.substring(0,3) == 'tr[') return true;
if (selector.substring(0,5) == 'span ') return true; if (selector.substring(0,5) == 'span:') return true;
if (selector.substring(0,5) == 'span[') return true;
if (selector.substring(0,3) == 'h1 ') return true; if (selector.substring(0,3) == 'h1:') return true;
if (selector.substring(0,3) == 'h1[') return true; if (selector.substring(0,3) == 'h2 ') return true;
if (selector.substring(0,3) == 'h2:') return true; if (selector.substring(0,3) == 'h2[') return true;
if (selector.substring(0,3) == 'h3 ') return true; if (selector.substring(0,3) == 'h3:') return true;
if (selector.substring(0,3) == 'h3[') return true; if (selector.substring(0,3) == 'h4 ') return true;
if (selector.substring(0,3) == 'h4:') return true; if (selector.substring(0,3) == 'h4[') return true;
if (selector.substring(0,3) == 'h5 ') return true; if (selector.substring(0,3) == 'h5:') return true;
if (selector.substring(0,3) == 'h5[') return true; if (selector.substring(0,3) == 'h6 ') return true;
if (selector.substring(0,3) == 'h6:') return true; if (selector.substring(0,3) == 'h6[') return true;
if (selector.substring(0,7) == 'center ') return true;
return false;}

// checking if selector is xpath selector
function is_xpath_selector(selector) {if (selector.length == 0) return false;
if ((selector.indexOf('/') >= 0) || (selector.indexOf('(') >= 0)) return true; return false;}

// finding best match for given locator
function tx(locator) {
if (is_xpath_selector(locator)) return x(locator);
// experimental change for #12, skip css syntax checks totally
// if (is_css_selector(locator)) return locator; //old logic
if (casper.exists(locator)) return locator; // new logic
if (casper.exists(x('//*[contains(@id,"'+locator+'")]'))) return x('//*[contains(@id,"'+locator+'")]');
if (casper.exists(x('//*[contains(@name,"'+locator+'")]'))) return x('//*[contains(@name,"'+locator+'")]');
if (casper.exists(x('//*[contains(@class,"'+locator+'")]'))) return x('//*[contains(@class,"'+locator+'")]');
if (casper.exists(x('//*[contains(@title,"'+locator+'")]'))) return x('//*[contains(@title,"'+locator+'")]');
if (casper.exists(x('//*[contains(text(),"'+locator+'")]'))) return x('//*[contains(text(),"'+locator+'")]');
return x('/html');}

// checking if given locator is found
function check_tx(locator) {
if (is_xpath_selector(locator)) {if (casper.exists(x(locator))) return true; else return false;}
// experimental change for #12, skip css syntax checks totally
// if (is_css_selector(locator)) {if (casper.exists(locator)) return true; else return false;} // old logic
if (casper.exists(locator)) return true; // new logic
if (casper.exists(x('//*[contains(@id,"'+locator+'")]'))) return true;
if (casper.exists(x('//*[contains(@name,"'+locator+'")]'))) return true;
if (casper.exists(x('//*[contains(@class,"'+locator+'")]'))) return true;
if (casper.exists(x('//*[contains(@title,"'+locator+'")]'))) return true;
if (casper.exists(x('//*[contains(text(),"'+locator+'")]'))) return true;
return false;}

// calling rest api url synchronously
function call_api(rest_url) {
var xhttp = new XMLHttpRequest(); xhttp.open("GET", rest_url, false);
xhttp.send(); return xhttp.statusText + ' - ' + xhttp.responseText;}

// flow path for save_text and snap_image
