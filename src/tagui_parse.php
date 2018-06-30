<?php

/* PARSER SCRIPT FOR TAGUI FRAMEWORK ~ TEBEL.ORG */

// check flow filename for .gui or .txt or no extension
$script = $argv[1]; if ($script=="") die("ERROR - specify flow filename as first parameter\n");
if (strpos(pathinfo($script, PATHINFO_BASENAME), '.') !== false) // check if file has extension
if ((pathinfo($script, PATHINFO_EXTENSION)!="gui") and (pathinfo($script, PATHINFO_EXTENSION)!="txt"))
die("ERROR - use .gui or .txt or no extension for flow filename\n");

// make sure required files are available and can be opened
if (!file_exists($script)) die("ERROR - cannot find " . $script . "\n");
$input_file = fopen($script,'r') or die("ERROR - cannot open " . $script . "\n");
$output_file = fopen($script . '.js','w') or die("ERROR - cannot open " . $script . '.js' . "\n");
$config_file = fopen('tagui_config.txt','r') or die("ERROR - cannot open tagui_config.txt" . "\n");
$header_file = fopen('tagui_header.js','r') or die("ERROR - cannot open tagui_header.js" . "\n");
$footer_file = fopen('tagui_footer.js','r') or die("ERROR - cannot open tagui_footer.js" . "\n");

$repo_count = 0; if (file_exists($script . '.csv')) { // load repository file for objects and keywords
$repo_file = fopen($script . '.csv','r') or die("ERROR - cannot open " . $script . '.csv' . "\n");
while (!feof($repo_file)) {$repo_data[$repo_count] = fgetcsv($repo_file);
if (count($repo_data[$repo_count]) == 0) die("ERROR - empty row found in " . $script . '.csv' . "\n");
$repo_count++;} fclose($repo_file); $repo_count-=2;} //-1 for header, -1 for EOF

$tagui_web_browser = "this"; // set the web browser to be used base on tagui_web_browser environment variable
if ((getenv('tagui_web_browser')=='headless') or (getenv('tagui_web_browser')=='chrome')) $tagui_web_browser = 'chrome';

$inside_code_block = 0; // track if step or code is inside user-defined code block
$inside_while_loop = 0; // track if step is in while loop and avoid async wait
$inside_frame = 0; $inside_popup = 0; // track html frame and popup step
$for_loop_tracker = ""; // track for loop to implement IIFE pattern
$line_number = 0; // track flow line number for error message
$test_automation = 0; // to determine casperjs script structure
$url_provided = false; // to detect if url is provided in user-script

// series of loops to create casperjs script from header, user flow, footer files

// create header of casperjs script using tagui config and header template
fwrite($output_file,"/* OUTPUT CASPERJS SCRIPT FOR TAGUI FRAMEWORK ~ TEBEL.ORG */\n\n");
fwrite($output_file,"var casper = require('casper').create();\n"); // opening lines
while(!feof($config_file)) {fwrite($output_file,fgets($config_file));} fclose($config_file);
while(!feof($header_file)) {fwrite($output_file,fgets($header_file));} fclose($header_file);

// save flow path in casperjs script to be used by save_text and snap_image
// casperjs/phantomjs do not seem to support \ for windows paths, replace with / to work
fwrite($output_file,"var flow_path = '" . str_replace("\\","/",dirname($script)) . "';\n\n");

if (strpos(strtolower(file_get_contents('tagui_config.txt')),"var tagui_language = 'english';")!==false)
{ // main loop without translation to parse intents in flow file for conversion into javascript code
while(!feof($input_file)) {fwrite($output_file,parse_intent(fgets($input_file)));} fclose($input_file);}
else { // section and main loop which includes translation engine for handling flows in other languages
$temp_argv1 = $argv[1]; $temp_argv2 = $argv[2]; $temp_argv3 = $argv[3];
$argv[1] = 'tagui_parse.php'; $argv[2] = 'from';
$temp_tagui_config = strtolower(file_get_contents('tagui_config.txt'));
$temp_tagui_config_start = strpos($temp_tagui_config,'var tagui_language');
$temp_tagui_config_end = strpos($temp_tagui_config,"\n",$temp_tagui_config_start);
$temp_tagui_config = substr($temp_tagui_config,$temp_tagui_config_start,$temp_tagui_config_end-$temp_tagui_config_start);
$temp_tagui_config = str_replace('var tagui_language','',$temp_tagui_config);
$temp_tagui_config = str_replace('"','',$temp_tagui_config); $temp_tagui_config = str_replace("'",'',$temp_tagui_config);
$temp_tagui_config = str_replace('=','',$temp_tagui_config); $temp_tagui_config = str_replace(';','',$temp_tagui_config); 
$argv[3] = trim($temp_tagui_config);
require 'translate.php'; // set parameters to load translation engine
$argv[1] = $temp_argv1; $argv[2] = $temp_argv2; $argv[3] = $temp_argv3;
while(!feof($input_file)) {fwrite($output_file,parse_intent(translate_intent(fgets($input_file))));} fclose($input_file);}

// create footer of casperjs script using footer template and do post-processing 
while(!feof($footer_file)) {fwrite($output_file,fgets($footer_file));} fclose($footer_file); fclose($output_file);
chmod ($script . '.js',0600); if (!$url_provided) echo "ERROR - first line of " . $script . " not URL\n";
if ($inside_code_block != 0) echo "ERROR - number of step { does not tally with with }\n";

// special handling if chrome or headless chrome is used as browser for automation
// replacement of this.method already happens in step intents, this is mostly to handle user inserted casperjs code 
if ($tagui_web_browser == 'chrome') {$script_content = file_get_contents($script . '.js'); // read generated script
$script_content = str_replace("var chrome_id = 0;","var chrome_id = 1;",$script_content); // websocket message id
$script_content = str_replace("casper.exists","chrome.exists",$script_content); // change locator check to chrome
$script_content = str_replace("this.exists","chrome.exists",$script_content); // change this.exists call as well
$script_content = str_replace("casper.click","chrome.click",$script_content); // change click method to chrome
$script_content = str_replace("this.click","chrome.click",$script_content); // change this.click call as well
$script_content = str_replace("casper.mouse","chrome.mouse",$script_content); // change mouse object to chrome
$script_content = str_replace("this.mouse","chrome.mouse",$script_content); // change this.mouse call as well
$script_content = str_replace("casper.sendKeys","chrome.sendKeys",$script_content); // change sendKeys method to chrome
$script_content = str_replace("this.sendKeys","chrome.sendKeys",$script_content); // change this.sendKeys call as well
// for selectOptionByValue check for '(' in order to only overwrite calls and not the custom defined function
$script_content = str_replace("casper.selectOptionByValue(","chrome.selectOptionByValue(",$script_content); // select
$script_content = str_replace("this.selectOptionByValue(","chrome.selectOptionByValue(",$script_content); // select
// for countElements check for '(' in order to only overwrite calls and not the custom function casper.countElements()
$script_content = str_replace("casper.countElements(","chrome.countElements(",$script_content); // count elements
$script_content = str_replace("this.countElements(","chrome.countElements(",$script_content); // count elements
// for elementVisible check for '(' in order to only overwrite calls and not the custom function casper.elementVisible()
$script_content = str_replace("casper.elementVisible(","chrome.elementVisible(",$script_content); // check element visible
$script_content = str_replace("this.elementVisible(","chrome.elementVisible(",$script_content); // check element visible
$script_content = str_replace("casper.fetchText","chrome.fetchText",$script_content); // change fetchText method to chrome
$script_content = str_replace("this.fetchText","chrome.fetchText",$script_content); // change this.fetchText call as well
$script_content = str_replace("casper.capture","chrome.capture",$script_content); // change capture method to chrome
$script_content = str_replace("this.capture","chrome.capture",$script_content); // change this.capture call as well
$script_content = str_replace("casper.captureSelector","chrome.captureSelector",$script_content); // capture selector
$script_content = str_replace("this.captureSelector","chrome.captureSelector",$script_content); // capture selector
$script_content = str_replace("chrome.page.uploadFile","chrome.upload",$script_content); // change upload method to chrome
$script_content = str_replace("casper.page.uploadFile","chrome.upload",$script_content); // change upload method to chrome
$script_content = str_replace("this.page.uploadFile","chrome.upload",$script_content); // change this.upload call as well
$script_content = str_replace("casper.download","chrome.download",$script_content); // change download method to chrome
$script_content = str_replace("this.download","chrome.download",$script_content); // change this.download call as well
$script_content = str_replace("casper.evaluate","chrome.evaluate",$script_content); // change evaluate method to chrome
$script_content = str_replace("this.evaluate","chrome.evaluate",$script_content); // change this.evaluate call as well
$script_content = str_replace("casper.withFrame","chrome.withFrame",$script_content); // change withFrame method to chrome
$script_content = str_replace("this.withFrame","chrome.withFrame",$script_content); // change this.withFrame call as well
$script_content = str_replace("casper.waitForPopup","chrome.waitForPopup",$script_content); // change waitForPopup method
$script_content = str_replace("this.waitForPopup","chrome.waitForPopup",$script_content); // change this.waitForPopup call
$script_content = str_replace("casper.withPopup","chrome.withPopup",$script_content); // change withPopup method to chrome
$script_content = str_replace("this.withPopup","chrome.withPopup",$script_content); // change this.withPopup call as well
$script_content = str_replace("casper.getHTML","chrome.getHTML",$script_content); // change getHTML method to chrome
$script_content = str_replace("this.getHTML","chrome.getHTML",$script_content); // change this.getHTML call as well
$script_content = str_replace("casper.getTitle","chrome.getTitle",$script_content); // change getTitle method to chrome
$script_content = str_replace("this.getTitle","chrome.getTitle",$script_content); // change this.getTitle call as well
$script_content = str_replace("casper.getCurrentUrl","chrome.getCurrentUrl",$script_content); // get current url
$script_content = str_replace("this.getCurrentUrl","chrome.getCurrentUrl",$script_content); // get current url
$script_content = str_replace("casper.debugHTML","chrome.debugHTML",$script_content); // change debugHTML method to chrome
$script_content = str_replace("this.debugHTML","chrome.debugHTML",$script_content); // change this.debugHTML call as well
$script_content = str_replace("casper.reload","chrome.reload",$script_content); // change reload method to chrome
$script_content = str_replace("this.reload","chrome.reload",$script_content); // change this.reload call as well
$script_content = str_replace("casper.back","chrome.back",$script_content); // change back method to chrome
$script_content = str_replace("this.back","chrome.back",$script_content); // change this.back call as well
$script_content = str_replace("casper.forward","chrome.forward",$script_content); // change forward method to chrome
$script_content = str_replace("this.forward","chrome.forward",$script_content); // change this.forward call as well
file_put_contents($script . '.js',$script_content); // below initialise chrome integration files
if (!touch('tagui_chrome.in')) die("ERROR - cannot initialise tagui_chrome.in\n");
if (!touch('tagui_chrome.out')) die("ERROR - cannot initialise tagui_chrome.out\n");}

// check quiet parameter to run flow quietly by only showing explicit output
if (getenv('tagui_quiet_mode') == 'true') {$script_content = file_get_contents($script . '.js'); // read generated script
$script_content = str_replace("var quiet_mode = false;","var quiet_mode = true;",$script_content); // set quiet_mode
$script_content = str_replace("casper.echo('\\nSTART - automation started - ","dummy_echo('",$script_content);
file_put_contents($script . '.js',$script_content);}

// convert casperjs script into test script structure if test option is used 
if (getenv('tagui_test_mode') == 'true') {$script_content = file_get_contents($script . '.js'); // read generated script
$script_content = str_replace("casper.echo('\\nSTART - automation started - ","casper.echo('",$script_content); // date
$script_content = str_replace("techo('FINISH - automation","dummy_echo('FINISH - test",$script_content); // silent
$script_content = str_replace("this.echo(","test.comment(",$script_content); // change echo to test comment
$script_content = str_replace("test.comment('ERROR","test.fail('ERROR",$script_content); // error comment to fail
// change echo to test comment in techo to show output correctly as test comments
$script_content = str_replace("casper.echo(echo_string);","casper.test.comment(echo_string);",$script_content);
$script_content = str_replace("casper.echo(translated_string);","casper.test.comment(translated_string);",$script_content);
$script_content = str_replace("\\n'","'",str_replace("'\\n","'",$script_content)); // compact test output
// casperjs testing does not allow creation of casper object as it is already created by test engine
$script_content = str_replace("var casper = require(","// var casper = require(",$script_content);
// following help to define the script structure required by casperjs for test automation purpose
$script_content = str_replace("casper.start(","casper.test.begin('" . str_replace("\\","\\\\",$script) . "', " . 
$test_automation.", function(test) {\ncasper.start(",$script_content); // define required casperjs test structure
$script_content = str_replace("casper.run();","casper.run(function() {test.done();});});",$script_content);
file_put_contents($script . '.js',$script_content);} // save script after restructuring for testing

// otherwise prep for normal execution by commenting out test assertions as they will kill the script
else if ($test_automation > 0) {$script_content = file_get_contents($script . '.js'); // read generated script
$script_content = str_replace("test.","// test.",$script_content); file_put_contents($script . '.js',$script_content);}

function current_line() {return "[LINE " . $GLOBALS['line_number'] . "]";}
function parse_intent($script_line) {$GLOBALS['line_number']++;
$script_line = trim($script_line); if ($script_line=="") return "";

// check existence of objects or keywords by searching for `object or keyword name`, then expand from repository
if ((substr_count($script_line,'`') > 1) and (!(substr_count($script_line,'`') & 1))) { // check for even number of `
if ($GLOBALS['repo_count'] == 0) echo "ERROR - ".current_line()." no repository data for ".$script_line."\n";
// loop through repository data to search and replace definitions, do it twice to handle objects within keywords
else {if (getenv('tagui_data_set')!==false) $data_set = intval(getenv('tagui_data_set')); else $data_set = 1;
for ($repo_check = 1; $repo_check <= $GLOBALS['repo_count']; $repo_check++) $script_line = 
str_replace("`".$GLOBALS['repo_data'][$repo_check][0]."`",$GLOBALS['repo_data'][$repo_check][$data_set],$script_line);
for ($repo_check = 1; $repo_check <= $GLOBALS['repo_count']; $repo_check++) $script_line =
str_replace("`".$GLOBALS['repo_data'][$repo_check][0]."`",$GLOBALS['repo_data'][$repo_check][$data_set],$script_line);
if (strpos($script_line,'`')!==false) echo "ERROR - ".current_line()." no repository data for ".$script_line."\n";}}

// trim and check again after replacing definitions from repository
$script_line = trim($script_line); if ($script_line=="") return "";

// check intent of step for interpretation into casperjs code
switch (get_intent($script_line)) {
case "url": return url_intent($script_line); break;
case "tap": return tap_intent($script_line); break;
case "hover": return hover_intent($script_line); break;
case "type": return type_intent($script_line); break;
case "select": return select_intent($script_line); break;
case "read": return read_intent($script_line); break;
case "show": return show_intent($script_line); break;
case "upload": return upload_intent($script_line); break;
case "down": return down_intent($script_line); break;
case "receive": return receive_intent($script_line); break;
case "echo": return echo_intent($script_line); break;
case "save": return save_intent($script_line); break;
case "dump": return dump_intent($script_line); break;
case "write": return write_intent($script_line); break;
case "load": return load_intent($script_line); break;
case "snap": return snap_intent($script_line); break;
case "table": return table_intent($script_line); break;
case "wait": return wait_intent($script_line); break;
case "live": return live_intent($script_line); break;
case "check": return check_intent($script_line); break;
case "test": return test_intent($script_line); break;
case "frame": return frame_intent($script_line); break;
case "popup": return popup_intent($script_line); break;
case "api": return api_intent($script_line); break;
case "run": return run_intent($script_line); break;
case "dom": return dom_intent($script_line); break;
case "js": return js_intent($script_line); break;
case "timeout": return timeout_intent($script_line); break;
case "code": return code_intent($script_line); break;
default: echo "ERROR - " . current_line() . " cannot understand step " . $script_line . "\n";}}

function get_intent($raw_intent) {$lc_raw_intent = strtolower($raw_intent); 
if ((substr($lc_raw_intent,0,7)=="http://") or (substr($lc_raw_intent,0,8)=="https://")) return "url";

// first set of conditions check for valid keywords with their parameters
if ((substr($lc_raw_intent,0,4)=="tap ") or (substr($lc_raw_intent,0,6)=="click ")) return "tap"; 
if ((substr($lc_raw_intent,0,6)=="hover ")or(substr($lc_raw_intent,0,5)=="move ")) return "hover";
if ((substr($lc_raw_intent,0,5)=="type ") or (substr($lc_raw_intent,0,6)=="enter ")) return "type";
if ((substr($lc_raw_intent,0,7)=="select ") or (substr($lc_raw_intent,0,7)=="choose ")) return "select";
if ((substr($lc_raw_intent,0,5)=="read ") or (substr($lc_raw_intent,0,6)=="fetch ")) return "read";
if ((substr($lc_raw_intent,0,5)=="show ") or (substr($lc_raw_intent,0,6)=="print ")) return "show";
if ((substr($lc_raw_intent,0,3)=="up ") or (substr($lc_raw_intent,0,7)=="upload ")) return "upload";
if ((substr($lc_raw_intent,0,5)=="down ") or (substr($lc_raw_intent,0,9)=="download ")) return "down";
if (substr($lc_raw_intent,0,8)=="receive ") return "receive";
if (substr($lc_raw_intent,0,5)=="echo ") return "echo";
if (substr($lc_raw_intent,0,5)=="save ") return "save";
if (substr($lc_raw_intent,0,5)=="dump ") return "dump";
if (substr($lc_raw_intent,0,6)=="write ") return "write";
if (substr($lc_raw_intent,0,5)=="load ") return "load";
if (substr($lc_raw_intent,0,5)=="snap ") return "snap";
if (substr($lc_raw_intent,0,6)=="table ") return "table";
if (substr($lc_raw_intent,0,5)=="wait ") return "wait";
if (substr($lc_raw_intent,0,5)=="live ") return "live";
if (substr($lc_raw_intent,0,6)=="check ") {$GLOBALS['test_automation']++; return "check";}
if (substr($lc_raw_intent,0,5)=="test ") return "test";
if (substr($lc_raw_intent,0,6)=="frame ") return "frame";
if (substr($lc_raw_intent,0,6)=="popup ") return "popup";
if (substr($lc_raw_intent,0,4)=="api ") return "api";
if (substr($lc_raw_intent,0,4)=="run ") return "run";
if (substr($lc_raw_intent,0,4)=="dom ") return "dom";
if (substr($lc_raw_intent,0,3)=="js ") return "js";
if (substr($lc_raw_intent,0,8)=="timeout ") return "timeout";

// second set of conditions check for valid keywords with missing parameters
if (($lc_raw_intent=="tap") or ($lc_raw_intent=="click")) return "tap";
if (($lc_raw_intent=="hover") or ($lc_raw_intent=="move")) return "hover";
if (($lc_raw_intent=="type") or ($lc_raw_intent=="enter")) return "type";
if (($lc_raw_intent=="select") or ($lc_raw_intent=="choose")) return "select";
if (($lc_raw_intent=="read") or ($lc_raw_intent=="fetch")) return "read";
if (($lc_raw_intent=="show") or ($lc_raw_intent=="print")) return "show";
if (($lc_raw_intent=="up") or ($lc_raw_intent=="upload")) return "upload";
if (($lc_raw_intent=="down") or ($lc_raw_intent=="download")) return "down";
if ($lc_raw_intent=="receive") return "receive";
if ($lc_raw_intent=="echo") return "echo";
if ($lc_raw_intent=="save") return "save";
if ($lc_raw_intent=="dump") return "dump";
if ($lc_raw_intent=="write") return "write";
if ($lc_raw_intent=="load") return "load";
if ($lc_raw_intent=="snap") return "snap";
if ($lc_raw_intent=="table") return "table";
if ($lc_raw_intent=="wait") return "wait";
if ($lc_raw_intent=="live") return "live";
if ($lc_raw_intent=="check") {$GLOBALS['test_automation']++; return "check";}
if ($lc_raw_intent=="test") return "test";
if ($lc_raw_intent=="frame") return "frame";
if ($lc_raw_intent=="popup") return "popup";
if ($lc_raw_intent=="api") return "api";
if ($lc_raw_intent=="run") return "run";
if ($lc_raw_intent=="dom") return "dom";
if ($lc_raw_intent=="js") return "js";
if ($lc_raw_intent=="timeout") return "timeout";

// final check for recognized code before returning error 
if (is_code($raw_intent)) return "code"; else return "error";}

function is_code($raw_intent) {
// due to asynchronous waiting for element, if/for/while can work for parsing single step
// other scenarios can be assumed to behave as unparsed javascript in casperjs context
// to let if/for/while handle multiple steps/code use the { and } steps to define block
if ((substr($raw_intent,0,4)=="var ") or (substr($raw_intent,0,3)=="do ")) return true;
if ((substr($raw_intent,0,1)=="{") or (substr($raw_intent,0,1)=="}")) return true;
if ((substr($raw_intent,-1)=="{") or (substr($raw_intent,-1)=="}")) return true;
if ((substr($raw_intent,0,3)=="if ") or (substr($raw_intent,0,4)=="else")) return true;
if ((substr($raw_intent,0,4)=="for ") or (substr($raw_intent,0,6)=="while ")) return true;
if ((substr($raw_intent,0,7)=="switch ") or (substr($raw_intent,0,5)=="case ")) return true;
if ((substr($raw_intent,0,6)=="break;") or (substr($raw_intent,0,9)=="function ")) return true;
if ((substr($raw_intent,0,7)=="casper.") or (substr($raw_intent,0,5)=="this.")) return true;
if (substr($raw_intent,0,7)=="chrome.") return true; // chrome object for chrome integration
if (substr($raw_intent,0,5)=="test.") {$GLOBALS['test_automation']++; return true;}
if ((substr($raw_intent,0,2)=="//") or (substr($raw_intent,-1)==";")) return true;
// assume = is assignment statement, kinda acceptable as this is checked at the very end
if (strpos($raw_intent,"=")!==false) return true; return false;}

function abs_file($filename) { // helper function to return absolute filename
if ($filename == "") return ""; $flow_script = $GLOBALS['script']; // get flow filename
if (substr($filename,0,1)=="/") return $filename; // return mac/linux absolute filename directly
if (substr($filename,1,1)==":") return str_replace("\\","/",$filename); // return windows absolute filename directly
$flow_path = str_replace("\\","/",dirname($flow_script)); // otherwise use flow script path to build absolute filename
// above str_replace is because casperjs/phantomjs do not seem to support \ for windows paths, replace with / to work
if (strpos($flow_path,"/")!==false) return $flow_path . '/' . $filename; else return $flow_path . '\\' . $filename;} 

function beg_tx($locator) { // helper function to return beginning string for handling locators
if ($GLOBALS['inside_while_loop'] == 0)
return "\ncasper.waitFor(function check() {return check_tx('".$locator."');},\nfunction then() {"; else return "\n";}

function end_tx($locator) { // helper function to return ending string for handling locators
if ($GLOBALS['inside_while_loop'] == 0)
return "},\nfunction timeout() {this.echo('ERROR - cannot find ".
$locator."').exit();});}".end_fi()."});\n\ncasper.then(function() {\n";
else if ($GLOBALS['inside_code_block']==0)
{$GLOBALS['inside_while_loop'] = 0; // reset inside_while_loop if not inside block
$GLOBALS['for_loop_tracker'] = ""; // reset for_loop_tracker if not inside block
return "}});\n\ncasper.then(function() {\n";} else return "}\n";}

function end_fi() { $end_step = ""; // helper function to end frame_intent and popup_intent by closing parsed step block
if ($GLOBALS['inside_code_block'] == 0) $GLOBALS['inside_while_loop'] = 0; // reset inside_while_loop if not inside block
if ($GLOBALS['inside_code_block'] == 0) $GLOBALS['for_loop_tracker'] = ""; // reset for_loop_tracker if not inside block
if (($GLOBALS['inside_code_block'] > 0) and ($GLOBALS['for_loop_tracker'] == "") and ($GLOBALS['inside_while_loop'] == 0)) return ""; // check for code block for frame or popup then exit, don't support frame/popup code blocks within loops
if (($GLOBALS['inside_popup'] == 1) or ($GLOBALS['inside_frame'] != 0)) $end_step = "});\n\ncasper.then(function() {";
if ($GLOBALS['inside_popup'] == 1) {$GLOBALS['inside_popup']=0; $popup_exit = " });} ";} else $popup_exit = "";
if ($GLOBALS['inside_frame'] == 0) {return "".$popup_exit.$end_step;} // form exit brackets for frame and popup
else if ($GLOBALS['inside_frame'] == 1) {$GLOBALS['inside_frame']=0; return " });} ".$popup_exit.$end_step;}
else if ($GLOBALS['inside_frame'] == 2) {$GLOBALS['inside_frame']=0; return " });});} ".$popup_exit.$end_step;}}

function add_concat($source_string) { // parse string and add missing + concatenator 
if ((strpos($source_string,"'")!==false) and (strpos($source_string,"\"")!==false))
{echo "ERROR - " . current_line() . " inconsistent quotes in " . $source_string . "\n";}
else if (strpos($source_string,"'")!==false) $quote_type = "'"; // derive quote type used
else if (strpos($source_string,"\"")!==false) $quote_type = "\""; else $quote_type = "none";
$within_quote = false; $source_string = trim($source_string); // trim for future proof
for ($srcpos=0; $srcpos<strlen($source_string); $srcpos++) {
if ($source_string[$srcpos] == $quote_type) $within_quote = !$within_quote; 
if (($within_quote == false) and ($source_string[$srcpos]==" ")) $source_string[$srcpos] = "+";}
$source_string = str_replace("+++++","+",$source_string); $source_string = str_replace("++++","+",$source_string);
$source_string = str_replace("+++","+",$source_string); $source_string = str_replace("++","+",$source_string);
return $source_string;} // replacing multiple variations of + to handle user typos of double spaces etc 

function is_sikuli($input_params) { // helper function to check if input is meant for sikuli visual automation
if (strlen($input_params)>4 and strtolower(substr($input_params,-4))=='.png') return true; // support png and bmp
else if (strlen($input_params)>4 and strtolower(substr($input_params,-4))=='.bmp') return true; else return false;}

function call_sikuli($input_intent,$input_params) { // helper function to use sikuli visual automation
if (!touch('tagui.sikuli/tagui_sikuli.in')) die("ERROR - cannot initialise tagui_sikuli.in\n");
if (!touch('tagui.sikuli/tagui_sikuli.out')) die("ERROR - cannot initialise tagui_sikuli.out\n");
return "{techo('".$input_intent."'); var fs = require('fs');\n" .
"if (!sikuli_step('".$input_intent."')) if (!fs.exists('".$input_params."'))\n" .
"this.echo('ERROR - cannot find image file ".$input_params."').exit(); else\n" . 
"this.echo('ERROR - cannot find " . $input_params." on screen').exit(); this.wait(0);}" .
end_fi()."});\n\ncasper.then(function() {\n";}

// set of functions to interpret steps into corresponding casperjs code
function url_intent($raw_intent) {$twb = $GLOBALS['tagui_web_browser']; $casper_url = $raw_intent; $chrome_call = '';
if ($twb == 'chrome')
{$chrome_call = "chrome_step('Page.setDownloadBehavior',{behavior: 'allow', downloadPath: flow_path});\n";
$casper_url = 'about:blank'; $chrome_call .= "chrome_step('Page.navigate',{url: '".$raw_intent."'}); sleep(1000);\n";}
if (strpos($raw_intent,"'+")!==false and strpos($raw_intent,"+'")!==false) // check if dynamic url is used
// wrap step within casper context if variable (casper context) is used in url, in order to access variable
{$dynamic_header = "\n{casper.then(function() {\n"; $dynamic_footer = "})} // end of dynamic url block\n";}
else {$dynamic_header = ""; $dynamic_footer = ""; // else casper.start/thenOpen can be outside casper context
if (filter_var($raw_intent, FILTER_VALIDATE_URL) == false) // do url validation only for raw text url string
echo "ERROR - " . current_line() . " invalid URL " . $raw_intent . "\n";}
if ($GLOBALS['line_number'] == 1) { // use casper.start for first URL call and casper.thenOpen for subsequent calls
$GLOBALS['url_provided']=true; return $dynamic_header."casper.start('".$casper_url."', function() {\n".$chrome_call.
"techo('".$raw_intent."' + ' - ' + ".$twb.".getTitle() + '\\n');});\n\ncasper.then(function() {\n".$dynamic_footer;}
else return $dynamic_header."});casper.thenOpen('".$casper_url."', function() {\n".$chrome_call."techo('".
$raw_intent."' + ' - ' + ".$twb.".getTitle());});\n\ncasper.then(function() {\n".$dynamic_footer;}

function tap_intent($raw_intent) {$twb = $GLOBALS['tagui_web_browser'];
$params = trim(substr($raw_intent." ",1+strpos($raw_intent." "," "))); 
if (is_sikuli($params)) {$abs_params = abs_file($params); $abs_intent = str_replace($params,$abs_params,$raw_intent);
return call_sikuli($abs_intent,$abs_params);} // use sikuli visual automation as needed
if ($params == "") echo "ERROR - " . current_line() . " target missing for " . $raw_intent . "\n"; else
return "{techo('".$raw_intent."');".beg_tx($params).$twb.".click(tx('" . $params . "'));".end_tx($params);}

function hover_intent($raw_intent) {$twb = $GLOBALS['tagui_web_browser'];
$params = trim(substr($raw_intent." ",1+strpos($raw_intent." "," "))); 
if (is_sikuli($params)) {$abs_params = abs_file($params); $abs_intent = str_replace($params,$abs_params,$raw_intent);
return call_sikuli($abs_intent,$abs_params);} // use sikuli visual automation as needed
if ($params == "") echo "ERROR - " . current_line() . " target missing for " . $raw_intent . "\n"; else
return "{techo('".$raw_intent."');".beg_tx($params).$twb.".mouse.move(tx('" . $params . "'));".end_tx($params);}

function type_intent($raw_intent) {$twb = $GLOBALS['tagui_web_browser'];
$params = trim(substr($raw_intent." ",1+strpos($raw_intent." "," ")));
$param1 = trim(substr($params,0,strpos($params," as "))); $param2 = trim(substr($params,4+strpos($params," as ")));
if (is_sikuli($param1) and $param2 != "") {
$abs_param1 = abs_file($param1); $abs_intent = str_replace($param1,$abs_param1,$raw_intent);
return call_sikuli($abs_intent,$abs_param1);} // use sikuli visual automation as needed
if (($param1 == "") or ($param2 == ""))
echo "ERROR - " . current_line() . " target/text missing for " . $raw_intent . "\n"; else {
// special handling for [clear] keyword to clear text field by doing an extra step to clear the field
if (substr($param2,0,7)=="[clear]") {if (strlen($param2)>7) $param2 = substr($param2,7); else $param2 = "";
$clear_field = $twb.".sendKeys(tx('".$param1."'),'',{reset: true});\n";} else $clear_field = "";
if (strpos($param2,"[enter]")===false) return "{techo('".$raw_intent."');".beg_tx($param1).$clear_field.
$twb.".sendKeys(tx('".$param1."'),'".$param2."');".end_tx($param1);
else // special handling for [enter] keyword to send enter key events
{$param2 = str_replace("[enter]","',{keepFocus: true});\n" .
$twb.".sendKeys(tx('".$param1."'),casper.page.event.key.Enter,{keepFocus: true});\n" .
$twb.".sendKeys(tx('".$param1."'),'",$param2); return "{techo('".$raw_intent."');".beg_tx($param1).$clear_field.
$twb.".sendKeys(tx('".$param1."'),'".$param2."',{keepFocus: true});".end_tx($param1);}}}

function select_intent($raw_intent) {$twb = $GLOBALS['tagui_web_browser'];
$params = trim(substr($raw_intent." ",1+strpos($raw_intent." "," ")));
$param1 = trim(substr($params,0,strpos($params," as "))); $param2 = trim(substr($params,4+strpos($params," as ")));
if (is_sikuli($param1) and is_sikuli($param2)) {
$abs_param1 = abs_file($param1); $abs_intent = str_replace($param1,$abs_param1,$raw_intent);
$abs_param2 = abs_file($param2); $abs_intent = str_replace($param2,$abs_param2,$abs_intent);
return call_sikuli($abs_intent,$abs_param1);} // use sikuli visual automation as needed
if (($param1 == "") or ($param2 == ""))
echo "ERROR - " . current_line() . " target/option missing for " . $raw_intent . "\n"; else
return "{techo('".$raw_intent."');".beg_tx($param1)."var select_locator = tx('".$param1."');\n".
"if (is_xpath_selector(select_locator.toString().replace('xpath selector: ','')))\n".
"select_locator = select_locator.toString().substring(16);\n".
$twb.".selectOptionByValue(select_locator,'".$param2."');".end_tx($param1);}

function read_intent($raw_intent) {$twb = $GLOBALS['tagui_web_browser'];
$params = trim(substr($raw_intent." ",1+strpos($raw_intent." "," ")));
$param1 = trim(substr($params,0,strpos($params," to "))); $param2 = trim(substr($params,4+strpos($params," to ")));
if ((strtolower($param1) == "page") and ($param2 != ""))
return "{techo('".$raw_intent."');\n".$param2." = ".$twb.".getHTML();}".end_fi()."\n";
if (($param1 == "") or ($param2 == "")) 
echo "ERROR - " . current_line() . " target/variable missing for " . $raw_intent . "\n"; else
return "{techo('".$raw_intent."');".beg_tx($param1).$param2." = ".$twb.".fetchText(tx('".$param1."')).trim();".end_tx($param1);}

function show_intent($raw_intent) {$twb = $GLOBALS['tagui_web_browser'];
$params = trim(substr($raw_intent." ",1+strpos($raw_intent." "," ")));
if (strtolower($params) == "page") return "this.echo('".$raw_intent."' + ' - \\n' + ".$twb.".getHTML());".end_fi()."\n";
if ($params == "") echo "ERROR - " . current_line() . " target missing for " . $raw_intent . "\n"; else
return "{techo('".$raw_intent."');".beg_tx($params).
"this.echo(".$twb.".fetchText(tx('" . $params . "')).trim());".end_tx($params);}

function upload_intent($raw_intent) {$twb = $GLOBALS['tagui_web_browser'];
$params = trim(substr($raw_intent." ",1+strpos($raw_intent." "," ")));
$param1 = trim(substr($params,0,strpos($params," as "))); $param2 = trim(substr($params,4+strpos($params," as ")));
if (($param1 == "") or ($param2 == ""))
echo "ERROR - " . current_line() . " filename missing for " . $raw_intent . "\n"; else
return "{techo('".$raw_intent."');".beg_tx($param1).
$twb.".page.uploadFile(tx('".$param1."'),'".abs_file($param2)."');".end_tx($param1);}

function down_intent($raw_intent) {$twb = $GLOBALS['tagui_web_browser'];
$params = trim(substr($raw_intent." ",1+strpos($raw_intent." "," ")));
$param1 = trim(substr($params,0,strpos($params," to "))); $param2 = trim(substr($params,4+strpos($params," to ")));
if (($param1 == "") or ($param2 == "")) 
echo "ERROR - " . current_line() . " url/filename missing for " . $raw_intent . "\n"; else
return "{techo('".$raw_intent."');\n".$twb.".download('".$param1."','".abs_file($param2)."');}".end_fi()."\n";}

function receive_intent($raw_intent) {$twb = $GLOBALS['tagui_web_browser'];
$params = trim(substr($raw_intent." ",1+strpos($raw_intent." "," ")));
$param1 = trim(substr($params,0,strpos($params," to "))); $param2 = trim(substr($params,4+strpos($params," to ")));
if (($param1 == "") or ($param2 == "")) 
echo "ERROR - " . current_line() . " keyword/filename missing for " . $raw_intent . "\n"; else
return "{techo('".$raw_intent."');\n".
"casper.on('resource.received', function(resource) {if (resource.stage !== 'end') return;\n".
"if (resource.url.indexOf('".$param1."') > -1) ".$twb.".download(resource.url, '".abs_file($param2)."');});}".end_fi()."\n";}

function echo_intent($raw_intent) {
$params = trim(substr($raw_intent." ",1+strpos($raw_intent." "," ")));
if ($params == "") echo "ERROR - " . current_line() . " text missing for " . $raw_intent . "\n"; else 
return "this.echo(".add_concat($params).");".end_fi()."\n";}

function save_intent($raw_intent) {$twb = $GLOBALS['tagui_web_browser'];
$params = trim(substr($raw_intent." ",1+strpos($raw_intent." "," ")));
$param1 = trim(substr($params,0,strpos($params," to "))); $param2 = trim(substr($params,4+strpos($params," to ")));
if ((strtolower($params) == "page") or (strtolower($param1) == "page")) {if (strpos($params," to ")!==false)
return "{techo('".$raw_intent."');\nsave_text('".abs_file($param2)."',".$twb.".getHTML());}".end_fi()."\n";
else return "{techo('".$raw_intent."');\nsave_text('',".$twb.".getHTML());}".end_fi()."\n";}
if ($params == "") echo "ERROR - " . current_line() . " target missing for " . $raw_intent . "\n"; 
else if (strpos($params," to ")!==false)
return "{techo('".$raw_intent."');".beg_tx($param1).
	"save_text('".abs_file($param2)."',".$twb.".fetchText(tx('".$param1."')).trim());".end_tx($param1); else
return "{techo('".$raw_intent."');".beg_tx($params).
	"save_text('',".$twb.".fetchText(tx('" . $params . "')).trim());".end_tx($params);}

function dump_intent($raw_intent) {
$safe_intent = str_replace("'","\'",$raw_intent); // avoid breaking echo below when single quote is used
$params = trim(substr($raw_intent." ",1+strpos($raw_intent." "," ")));
$param1 = trim(substr($params,0,strpos($params," to "))); $param2 = trim(substr($params,4+strpos($params," to ")));
if ($params == "") echo "ERROR - " . current_line() . " variable missing for " . $raw_intent . "\n"; 
else if (strpos($params," to ")!==false)
return "{techo('".$safe_intent."');\nsave_text('".abs_file($param2)."',".add_concat($param1).");}".end_fi()."\n";
else return "{techo('".$safe_intent."');\nsave_text(''," . add_concat($params) . ");}".end_fi()."\n";}

function write_intent($raw_intent) {
$safe_intent = str_replace("'","\'",$raw_intent); // avoid breaking echo below when single quote is used
$params = trim(substr($raw_intent." ",1+strpos($raw_intent." "," ")));
$param1 = trim(substr($params,0,strpos($params," to "))); $param2 = trim(substr($params,4+strpos($params," to ")));
if ($params == "") echo "ERROR - " . current_line() . " variable missing for " . $raw_intent . "\n";
else if (strpos($params," to ")!==false)
return "{techo('".$safe_intent."');\nappend_text('".abs_file($param2)."',".add_concat($param1).");}".end_fi()."\n";
else return "{techo('".$safe_intent."');\nappend_text(''," . add_concat($params) . ");}".end_fi()."\n";}

function load_intent($raw_intent) {
$safe_intent = str_replace("'","\'",$raw_intent); // avoid breaking echo below when single quote is used
$params = trim(substr($raw_intent." ",1+strpos($raw_intent." "," ")));
$param1 = trim(substr($params,0,strpos($params," to "))); $param2 = trim(substr($params,4+strpos($params," to ")));
if ($params == "") echo "ERROR - " . current_line() . " filename missing for " . $raw_intent . "\n";
else if (strpos($params," to ")!==false)
return "{techo('".$safe_intent."');\nvar fs = require('fs'); ".$param2." = '';\n".
	"if (fs.exists('".abs_file($param1)."'))\n".$param2." = fs.read('".abs_file($param1)."').trim();\n".
	"else this.echo('ERROR - cannot find file ".$param1."').exit();}".end_fi()."\n";
else echo "ERROR - " . current_line() . " variable missing for " . $raw_intent . "\n";}

function snap_intent($raw_intent) {$twb = $GLOBALS['tagui_web_browser'];
$params = trim(substr($raw_intent." ",1+strpos($raw_intent." "," ")));
$param1 = trim(substr($params,0,strpos($params," to "))); $param2 = trim(substr($params,4+strpos($params," to ")));
if ((strtolower($params) == "page") or (strtolower($param1) == "page")) {if (strpos($params," to ")!==false)
return "{techo('".$raw_intent."');\n".$twb.".capture('".abs_file($param2)."');}".end_fi()."\n";
else return "{techo('".$raw_intent."');\n".$twb.".capture(snap_image());}".end_fi()."\n";}
if ($params == "") echo "ERROR - " . current_line() . " target missing for " . $raw_intent . "\n"; 
else if (strpos($params," to ")!==false)
return "{techo('".$raw_intent."');".beg_tx($param1).
	$twb.".captureSelector('".abs_file($param2)."',tx('".$param1."'));".end_tx($param1); else
return "{techo('".$raw_intent."');".beg_tx($params).
	$twb.".captureSelector(snap_image(),tx('".$params."'));".end_tx($params);}

function table_intent($raw_intent) {$twb = $GLOBALS['tagui_web_browser'];
$params = trim(substr($raw_intent." ",1+strpos($raw_intent." "," ")));
$param1 = trim(substr($params,0,strpos($params," to "))); $param2 = trim(substr($params,4+strpos($params," to ")));
if ($params == "") echo "ERROR - " . current_line() . " target missing for " . $raw_intent . "\n";
else if (strpos($params," to ")!==false)
return "{techo('".$raw_intent."');".beg_tx($param1).
	"save_table('".abs_file($param2)."',tx('".$param1."'));".end_tx($param1); else
return "{techo('".$raw_intent."');".beg_tx($params).
        "save_table('',tx('".$params."'));".end_tx($params);}

function wait_intent($raw_intent) { // wait is a new block, invalid to use after frame, thus skip end_fi()
$params = trim(substr($raw_intent." ",1+strpos($raw_intent." "," "))); if ($params == "") $params = "5"; 
if ($GLOBALS['inside_frame']!=0) echo "ERROR - " . current_line() . " invalid after frame - " . $raw_intent . "\n";
else if ($GLOBALS['inside_popup']!=0) echo "ERROR - " . current_line() . " invalid after popup - " . $raw_intent . "\n";
else return "techo('".$raw_intent."');});\n\ncasper.wait(" . (floatval($params)*1000) . ", function() {\n";}

function live_intent($raw_intent) { // live mode to interactively test tagui steps and js code (casperjs context)
return "{var live_input = ''; var sys = require('system'); sys.stdout.write('LIVE MODE - type done to quit\\n \\b');\n".
"while (true) {live_input = sys.stdin.readLine(); // evaluate input in casperjs context until done is entered\n".
"if (live_input.indexOf('done') == 0) break; eval(tagui_parse(live_input));}}".end_fi()."\n";}

function check_intent($raw_intent) {
$params = trim(substr($raw_intent." ",1+strpos($raw_intent." "," ")));
$params = str_replace("||"," JAVASCRIPT_OR ",$params); // to handle conflict with "|" delimiter 
$param1 = trim(substr($params,0,strpos($params,"|"))); $param2 = trim(substr($params,1+strpos($params,"|")));
$param3 = trim(substr($param2,1+strpos($param2,"|"))); $param2 = trim(substr($param2,0,strpos($param2,"|")));
$param1 = str_replace(" JAVASCRIPT_OR ","||",$param1); // to restore back "||" that were replaced
$param2 = str_replace(" JAVASCRIPT_OR ","||",$param2); $param3 = str_replace(" JAVASCRIPT_OR ","||",$param3);
if (substr_count($params,"|")!=2) 
echo "ERROR - " . current_line() . " if/true/false missing for " . $raw_intent . "\n";
else if (getenv('tagui_test_mode') == 'true') return "{".parse_condition("if ".$param1).
"\ntest.assert(true,".add_concat($param2).");\nelse test.assert(false,".add_concat($param3).");}".end_fi()."\n";
else return "{".parse_condition("if ".$param1)."\nthis.echo(".add_concat($param2).");\nelse this.echo(".add_concat($param3).");}".end_fi()."\n";}

function test_intent($raw_intent) {
echo "ERROR - " . current_line() . " use CasperJS tester module to professionally " . $raw_intent . "\n";
echo "ERROR - " . current_line() . " info at http://docs.casperjs.org/en/latest/modules/tester.html" . "\n";
echo "ERROR - " . current_line() . " support CSS selector or tx('selector') for XPath algo by TagUI" . "\n";}

function frame_intent($raw_intent) {
if ($GLOBALS['inside_frame'] != 0) 
{echo "ERROR - " . current_line() . " frame called consecutively " . $raw_intent . "\n"; return;}
$params = trim(substr($raw_intent." ",1+strpos($raw_intent." "," ")));
$param1 = trim(substr($params,0,strpos($params,"|"))); $param2 = trim(substr($params,1+strpos($params,"|")));
if ($params == "") echo "ERROR - " . current_line() . " name missing for " . $raw_intent . "\n"; 
else if (strpos($params,"|")!==false) 
{$GLOBALS['inside_frame']=2; return "{techo('".$raw_intent."');\ncasper.withFrame('".
$param1."', function() {casper.withFrame('".$param2."', function() {\n";} else
{$GLOBALS['inside_frame']=1; return "{techo('".$raw_intent."');\ncasper.withFrame('".$params."', function() {\n";}}

function popup_intent($raw_intent) {
if ($GLOBALS['inside_popup'] != 0)
{echo "ERROR - " . current_line() . " popup called consecutively " . $raw_intent . "\n"; return;}
$params = trim(substr($raw_intent." ",1+strpos($raw_intent." "," ")));
if ($GLOBALS['inside_frame']!=0) echo "ERROR - " . current_line() . " invalid after frame - " . $raw_intent . "\n";
else if ($params == "") echo "ERROR - " . current_line() . " keyword missing for " . $raw_intent . "\n";
else {$GLOBALS['inside_popup']=1; // during execution check for popup before going into popup context
return "{techo('".$raw_intent."');\ncasper.waitForPopup(/".preg_quote($params)."/, function then() {},\n".
"function timeout() {this.echo('ERROR - cannot find popup ".$params."').exit();});\n".
"casper.withPopup(/".preg_quote($params)."/, function() {\n";}}

function api_intent($raw_intent) {
$params = trim(substr($raw_intent." ",1+strpos($raw_intent." "," ")));
if ($params == "") echo "ERROR - " . current_line() . " API URL missing for " . $raw_intent . "\n"; else
return "{techo('".$raw_intent."');\napi_result = call_api('".$params."');\n" . 
"try {api_json = JSON.parse(api_result);} catch(e) {api_json = JSON.parse('null');}}".end_fi()."\n";}

function run_intent($raw_intent) { // waitForExec is a new block, invalid to use after frame, thus skip end_fi()
$params = trim(substr($raw_intent." ",1+strpos($raw_intent." "," ")));
if ($GLOBALS['inside_frame']!=0) echo "ERROR - " . current_line() . " invalid after frame - " . $raw_intent . "\n";
else if ($GLOBALS['inside_popup']!=0) echo "ERROR - " . current_line() . " invalid after popup - " . $raw_intent . "\n";
else if ($params == "") echo "ERROR - " . current_line() . " command to run missing for " . $raw_intent . "\n"; else
return "techo('".$raw_intent."');});\n\ncasper.waitForExec('".$params."', null, function(response) {\n" .
"run_result = (response.data.stdout.trim() || response.data.stderr.trim());\nrun_json = response.data;\n";}

function dom_intent($raw_intent) {$twb = $GLOBALS['tagui_web_browser'];
$params = trim(substr($raw_intent." ",1+strpos($raw_intent." "," ")));
if ($params == "") echo "ERROR - " . current_line() . " statement missing for " . $raw_intent . "\n";
else return "dom_result = ".$twb.".evaluate(function(dom_json) {".$params."}, dom_json);".end_fi()."\n";}

function js_intent($raw_intent) {
$params = trim(substr($raw_intent." ",1+strpos($raw_intent." "," ")));
if ($params == "") echo "ERROR - " . current_line() . " statement missing for " . $raw_intent . "\n";
else return $params.end_fi()."\n";}

function timeout_intent($raw_intent) {
$params = trim(substr($raw_intent." ",1+strpos($raw_intent." "," ")));
if ($params == "") echo "ERROR - " . current_line() . " time in seconds missing for " . $raw_intent . "\n";
else return "casper.options.waitTimeout = " . (floatval($params)*1000) . ";" . end_fi()."\n";}

function code_intent($raw_intent) {
$params = parse_condition($raw_intent);
// not relevant to call end_fi for condition statement, will reset for and while loop tracker prematurely
if ((substr($params,0,3)=="if ") or (substr($params,0,8)=="else if ")
or (substr($params,0,4)=="for ") or (substr($params,0,6)=="while "))
return $params."\n"; else return $params.end_fi()."\n";}

function parse_condition($logic) { // natural language handling for conditions
$raw_logic = $logic; // store an original copy for use when showing error message
if (substr($logic,0,2)=="//") return $logic; // skip processing for comment

// section 1 - replace braces block {} with casperjs block to group steps or code
// take only lines starting with { or } as code blocks for processing, otherwise will break many valid javascript code
if (substr($logic,0,1) == "{") $GLOBALS['inside_code_block']++; // assume nothing on rest of the line except comment
if (substr($logic,0,1) == "}") $GLOBALS['inside_code_block']--; // assume nothing on rest of the line except comment
if ($GLOBALS['inside_while_loop']==0) { // while loop check as casper.then will hang while loop, recommend use for loop
$for_loop_header = ""; $for_loop_footer = ""; // add provision to implement IIFE pattern if inside for loop code block
if ($GLOBALS['for_loop_tracker']!="") { // otherwise the number variable used by for loop will be a wrong static number
$last_delimiter_pos = strrpos($GLOBALS['for_loop_tracker'],"|");
$for_loop_variable_name = substr($GLOBALS['for_loop_tracker'],$last_delimiter_pos+1);
$for_loop_header = "\n// start of IIFE pattern\n(function (" . $for_loop_variable_name . ") {";
$for_loop_footer = "})(" . $for_loop_variable_name . "); // end of IIFE pattern\n});\n\ncasper.then(function() {";
// pop for_loop_tracker only if for loop count tallies with the code block count (to support if popup frame in for loop)
if ((substr($logic,0,1) == "}") and (substr_count($GLOBALS['for_loop_tracker'],'|')==($GLOBALS['inside_code_block']+1)))
$GLOBALS['for_loop_tracker']=substr($GLOBALS['for_loop_tracker'],0,$last_delimiter_pos);}
if (substr($logic,0,1) == "{")
$logic = $for_loop_header."\n// start of code block\n{casper.then(function() {\n".substr($logic,1);
else if (substr($logic,0,1) == "}") $logic = "})} // end of code block\n".$for_loop_footer.substr($logic,1);}

// section 2 - natural language handling for conditions and loops 
if ((substr($logic,0,3)=="if ") or (substr($logic,0,8)=="else if ")
or (substr($logic,0,4)=="for ") or (substr($logic,0,6)=="while ")) {

$logic = str_replace(" more than or equals to "," >= ",$logic);
$logic = str_replace(" greater than or equals to "," >= ",$logic);
$logic = str_replace(" higher than or equals to "," >= ",$logic);
$logic = str_replace(" less than or equals to "," <= ",$logic);
$logic = str_replace(" lesser than or equals to "," <= ",$logic);
$logic = str_replace(" lower than or equals to "," <= ",$logic);
$logic = str_replace(" more than or equal to "," >= ",$logic);
$logic = str_replace(" greater than or equal to "," >= ",$logic);
$logic = str_replace(" higher than or equal to "," >= ",$logic);
$logic = str_replace(" less than or equal to "," <= ",$logic);
$logic = str_replace(" lesser than or equal to "," <= ",$logic);
$logic = str_replace(" lower than or equal to "," <= ",$logic);

$logic = str_replace(" more than "," > ",$logic); $logic = str_replace(" greater than "," > ",$logic);
$logic = str_replace(" higher than "," > ",$logic); $logic = str_replace(" less than "," < ",$logic);
$logic = str_replace(" lesser than "," < ",$logic); $logic = str_replace(" lower than "," < ",$logic);
$logic = str_replace(" not equals to "," != ",$logic); $logic = str_replace(" equals to "," == ",$logic);
$logic = str_replace(" not equal to "," != ",$logic); $logic = str_replace(" equal to "," == ",$logic);

// special handling to manage not contains, not contain, contains, contain conditions
$contain_list = array(" not contains ", " not contain ", " contains ", " contain ");
foreach ($contain_list as $contain_type) { // outer loop, iterate through 4 types of contain conditions
for ($condition_counter=1;$condition_counter<=5;$condition_counter++) { // inner loop, avoid while due to infinite loops
if (strpos($logic,$contain_type)==!false) {$pos_keyword = strpos($logic,$contain_type);
$pos_single_quote = strpos($logic,"'",$pos_keyword+strlen($contain_type)); // check type of quote used
if ($pos_single_quote == false) $pos_single_quote = 1024; // set to large number, for comparison later
$pos_double_quote = strpos($logic,"\"",$pos_keyword+strlen($contain_type)); // check type of quote used
if ($pos_double_quote == false) $pos_double_quote = 1024; // set to large number, for comparison later
if ($pos_double_quote < $pos_single_quote) {$pos_quote_start = $pos_double_quote; $quote_type = "\"";}
else if ($pos_single_quote < $pos_double_quote) {$pos_quote_start = $pos_single_quote; $quote_type = "'";}
else {echo "ERROR - " . current_line() . " no quoted text - " . $raw_logic . "\n"; $quote_type = "missing";}
if ($quote_type != "missing") {$pos_quote_end = strpos($logic,$quote_type,$pos_quote_start+1);
$pos_variable_start = strrpos($logic," ",$pos_keyword-strlen($logic)-2); $contain_operator = "<0";
if (($contain_type == " contains ") or ($contain_type == " contain ")) $contain_operator = ">-1"; 
$logic = substr($logic,0,$pos_variable_start+1)."(".
trim(substr($logic,$pos_variable_start,$pos_keyword-$pos_variable_start)).".indexOf(".
$quote_type.substr($logic,$pos_quote_start+1,$pos_quote_end-$pos_quote_start-1).
$quote_type.")".$contain_operator.")".substr($logic,$pos_quote_end+1);}}
else break;}}

// $logic = str_replace(" not "," ! ",$logic); // leaving not out until meaningful to implement
$logic = str_replace(" and ",") && (",$logic); $logic = str_replace(" or ",") || (",$logic);

// special handling to manage for loop in natural language 
if ((substr($logic,0,4)=="for ") and (strpos($logic,";")==false)) { // no ; means in natural language
if (strpos($raw_logic,"count(")!==false)
echo "ERROR - " . current_line() . " assign count() to variable before using - " . $raw_logic . "\n";
$logic = str_replace("(","",$logic); $logic = str_replace(")","",$logic); // remove brackets if present
$logic = str_replace("   "," ",$logic); $logic = str_replace("  "," ",$logic); // remove typo extra spaces
$token = explode(" ",$logic); // split into tokens for loop in natural language, eg - for cupcake from 1 to 4
if (strpos($raw_logic,"{")!==false) // show error to put { to  next line for parsing as { step
echo "ERROR - " . current_line() . " put { to next line - " . $raw_logic . "\n";
else if (count($token) != 6) echo "ERROR - " . current_line() . " invalid for loop - " . $raw_logic . "\n";
else $logic = $token[0]." (".$token[1]."=".$token[3]."; ".$token[1]."<=".$token[5]."; ".$token[1]."++)";}
else if ((substr($logic,0,4)=="for ") and (strpos($raw_logic,"{")!==false))
echo "ERROR - " . current_line() . " put { to next line - " . $raw_logic . "\n";

// add to tracker the for loop variable name, to implement IIFE pattern if step/code blocks are used
if (substr($logic,0,4)=="for ") { // get the variable name used in the for loop and append into tracker
$GLOBALS['for_loop_tracker'] .= "|" . trim(substr($logic,strpos($logic,"(")+1,strpos($logic,"=")-strpos($logic,"(")-1));}

// add opening and closing brackets twice to handle no brackets, and, or cases
if (substr($logic,0,3)=="if ") {$logic = "if ((" . trim(substr($logic,3)) . "))";
if (strpos($raw_logic,"{")!==false) echo "ERROR - " . current_line() . " put { to next line - " . $raw_logic . "\n";}
if (substr($logic,0,8)=="else if ") {$logic = "else if ((" . trim(substr($logic,8)) . "))";
if (strpos($raw_logic,"{")!==false) echo "ERROR - " . current_line() . " put { to next line - " . $raw_logic . "\n";}
if (substr($logic,0,6)=="while ") {$logic = "while ((" . trim(substr($logic,6)) . "))";
if (strpos($raw_logic,"{")!==false) echo "ERROR - " . current_line() . " put { to next line - " . $raw_logic . "\n";}}

// section 3 - track if next statement is going to be or still inside while loop,
// then avoid async wait (casper.then/waitFor/timeout will hang casperjs/phantomjs)   
if (substr($logic,0,6)=="while ") $GLOBALS['inside_while_loop'] = 1; 

// return code after all the parsing and special handling
return $logic;}

?>
