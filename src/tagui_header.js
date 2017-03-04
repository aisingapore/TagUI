
// xpath for object identification
var x = require('casper').selectXPath;

// assign parameters to p1-p9 variables
var p1 = casper.cli.raw.get(0); var p2 = casper.cli.raw.get(1); var p3 = casper.cli.raw.get(2);
var p4 = casper.cli.raw.get(3); var p5 = casper.cli.raw.get(4); var p6 = casper.cli.raw.get(5);
var p7 = casper.cli.raw.get(6); var p8 = casper.cli.raw.get(7); var p9 = casper.cli.raw.get(8);

// save start time to measure execution time
var automation_start_time = Date.now(); casper.echo('\nSTART - automation started - ' + Date().toLocaleString());

// initialise other global variables
var quiet_mode = false; var save_text_count = 0; var snap_image_count = 0

// techo function to handle quiet mode
function techo(echo_string) {if (!quiet_mode) casper.echo(echo_string); return;}

// muting echo in test automation scripts
function dummy_echo(muted_string) {return;}

// saving text information to file
function save_text(file_name,info_text) {
if (!file_name) {save_text_count++; file_name = flow_path + '/' + 'text' + save_text_count.toString() + '.txt';}
var fs = require('fs'); fs.write(file_name, info_text, 'w');}

// saving snapshots of website to file
function snap_image() {snap_image_count++; return (flow_path + '/' + 'snap' + snap_image_count.toString() + '.png');}

// checking if selector is xpath selector
function is_xpath_selector(selector) {if (selector.length == 0) return false;
if ((selector.indexOf('/') >= 0) || (selector.indexOf('(') >= 0)) return true; return false;}

// finding best match for given locator
function tx(locator) {
if (is_xpath_selector(locator)) return x(locator);
if (casper.exists(locator)) return locator; // check for css locator
if (casper.exists(x('//*[contains(@id,"'+locator+'")]'))) return x('//*[contains(@id,"'+locator+'")]');
if (casper.exists(x('//*[contains(@name,"'+locator+'")]'))) return x('//*[contains(@name,"'+locator+'")]');
if (casper.exists(x('//*[contains(@class,"'+locator+'")]'))) return x('//*[contains(@class,"'+locator+'")]');
if (casper.exists(x('//*[contains(@title,"'+locator+'")]'))) return x('//*[contains(@title,"'+locator+'")]');
if (casper.exists(x('//*[contains(text(),"'+locator+'")]'))) return x('//*[contains(text(),"'+locator+'")]');
if (casper.exists(x('//*[contains(@aria-label,"'+locator+'")]'))) return x('//*[contains(@aria-label,"'+locator+'")]');
return x('/html');}

// checking if given locator is found
function check_tx(locator) {
if (is_xpath_selector(locator)) {if (casper.exists(x(locator))) return true; else return false;}
if (casper.exists(locator)) return true; // check for css locator
if (casper.exists(x('//*[contains(@id,"'+locator+'")]'))) return true;
if (casper.exists(x('//*[contains(@name,"'+locator+'")]'))) return true;
if (casper.exists(x('//*[contains(@class,"'+locator+'")]'))) return true;
if (casper.exists(x('//*[contains(@title,"'+locator+'")]'))) return true;
if (casper.exists(x('//*[contains(text(),"'+locator+'")]'))) return true;
if (casper.exists(x('//*[contains(@aria-label,"'+locator+'")]'))) return true;
return false;}

// calling rest api url synchronously
function call_api(rest_url) {
var xhttp = new XMLHttpRequest(); xhttp.open("GET", rest_url, false);
xhttp.send(); return xhttp.statusText + ' - ' + xhttp.responseText;}

// flow path for save_text and snap_image
