/* OUTPUT CASPERJS SCRIPT FOR TA.GUI FRAMEWORK ~ TEBEL.SG */

// casperjs (phantomjs) browser settings
var x = require('casper').selectXPath;
var casper = require('casper').create({
verbose: false, logLevel: 'debug',
waitTimeout: 10000,
viewportSize: {width: 1366, height: 768},
pageSettings: {loadImages: true, loadPlugins: true,
localToRemoteUrlAccessEnabled: false, webSecurityEnabled: true, ignoreSslErrors: false}});

// assign parameters to p1-p9 variables
var p1 = casper.cli.raw.get(0); var p2 = casper.cli.raw.get(1); var p3 = casper.cli.raw.get(2);
var p4 = casper.cli.raw.get(3); var p5 = casper.cli.raw.get(4); var p6 = casper.cli.raw.get(5);
var p7 = casper.cli.raw.get(6); var p8 = casper.cli.raw.get(7); var p9 = casper.cli.raw.get(8);

// save start time to measure execution time
var automation_start_time = Date.now(); casper.echo('\nSTART - automation started - ' + Date().toLocaleString());

// initialise other global variables
var save_text_count = 0; var snap_image_count = 0;

// for saving text information to file
function save_text(file_name,info_text) {
if (!file_name) {save_text_count++; file_name = 'text' + save_text_count.toString() + '.txt';}
var fs = require('fs'); fs.write(file_name, info_text, 'w');}

// for saving snapshots of website to file
function snap_image() {snap_image_count++; return ('image' + snap_image_count.toString() + '.png');}

// for adding synchronous suspend capability
function sleep(delay_in_ms) {var start_time = new Date().getTime();
for (var sleep_count = 0; sleep_count < 1e7; sleep_count++)
{if ((new Date().getTime() - start_time) > delay_in_ms) break;}}
 
// finding best match for given locator
function tx(locator) {
if (casper.exists(x(locator))) return x(locator);
if (casper.exists(x('//*[@id="'+locator+'"]'))) return x('//*[@id="'+locator+'"]');
if (casper.exists(x('//*[@name="'+locator+'"]'))) return x('//*[@name="'+locator+'"]');
if (casper.exists(x('//*[@class="'+locator+'"]'))) return x('//*[@class="'+locator+'"]');
if (casper.exists(x('//*[@title="'+locator+'"]'))) return x('//*[@title="'+locator+'"]');
if (casper.exists(x('//*[text()="'+locator+'"]'))) return x('//*[text()="'+locator+'"]');
if (casper.exists(x('//*[contains(@id,"'+locator+'")]'))) return x('//*[contains(@id,"'+locator+'")]');
if (casper.exists(x('//*[contains(@name,"'+locator+'")]'))) return x('//*[contains(@name,"'+locator+'")]');
if (casper.exists(x('//*[contains(@class,"'+locator+'")]'))) return x('//*[contains(@class,"'+locator+'")]');
if (casper.exists(x('//*[contains(@title,"'+locator+'")]'))) return x('//*[contains(@title,"'+locator+'")]');
if (casper.exists(x('//*[contains(text(),"'+locator+'")]'))) return x('//*[contains(text(),"'+locator+'")]');
else return x('/html');}

// checking if given locator is found
function check_tx(locator) {
if (casper.exists(x(locator))) return true; 
if (casper.exists(x('//*[@id="'+locator+'"]'))) return true;
if (casper.exists(x('//*[@name="'+locator+'"]'))) return true;
if (casper.exists(x('//*[@class="'+locator+'"]'))) return true;
if (casper.exists(x('//*[@title="'+locator+'"]'))) return true;
if (casper.exists(x('//*[text()="'+locator+'"]'))) return true;
if (casper.exists(x('//*[contains(@id,"'+locator+'")]'))) return true;
if (casper.exists(x('//*[contains(@name,"'+locator+'")]'))) return true;
if (casper.exists(x('//*[contains(@class,"'+locator+'")]'))) return true;
if (casper.exists(x('//*[contains(@title,"'+locator+'")]'))) return true;
if (casper.exists(x('//*[contains(text(),"'+locator+'")]'))) return true;
else return false;}

