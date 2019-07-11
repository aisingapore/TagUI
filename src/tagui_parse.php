<?php

/* PARSER SCRIPT FOR TAGUI FRAMEWORK ~ TEBEL.ORG */

// Q1. Why is formatting for this file so messed up? - it's created on the road
// If you want to know more - https://github.com/kelaberetiv/TagUI/issues/490

// Q2. Is there a beautified version for easier viewing or editing? - yes snapshot below
// https://github.com/kelaberetiv/TagUI/blob/master/src/media/snapshots/tagui_parse.md

// check flow filename for .tagui or .js or .txt or no extension
$script = $argv[1]; if ($script=="") die("ERROR - specify flow filename as first parameter\n");
if (strpos(pathinfo($script, PATHINFO_BASENAME), '.') !== false) // check if file has extension
if ((pathinfo($script, PATHINFO_EXTENSION)!="gui") and (pathinfo($script, PATHINFO_EXTENSION)!="txt") and (pathinfo($script, PATHINFO_EXTENSION)!="js") and (pathinfo($script, PATHINFO_EXTENSION)!="tagui"))
die("ERROR - use .tagui .js .txt or no extension for flow filename\n");

// make sure required files are available and can be opened
if (!file_exists($script)) die("ERROR - cannot find " . $script . "\n");
$input_file = fopen($script,'r') or die("ERROR - cannot open " . $script . "\n");
$output_file = fopen($script . '.js','w') or die("ERROR - cannot open " . $script . '.js' . "\n");
$config_file = fopen('tagui_config.txt','r') or die("ERROR - cannot open tagui_config.txt" . "\n");
$header_file = fopen('tagui_header.js','r') or die("ERROR - cannot open tagui_header.js" . "\n");
$footer_file = fopen('tagui_footer.js','r') or die("ERROR - cannot open tagui_footer.js" . "\n");

$repo_count = 0; if (file_exists(getenv('custom_csv_file'))) { // load datatable or legacy datatable / repository
$repo_file = fopen(getenv('custom_csv_file'),'r') or die("ERROR - cannot open " . getenv('custom_csv_file') . "\n");
while (!feof($repo_file)) {$repo_data[$repo_count] = fgetcsv($repo_file);
if (count($repo_data[$repo_count]) == 0) die("ERROR - empty row found in " . getenv('custom_csv_file') . "\n");
$repo_count++;} fclose($repo_file); $repo_count-=1; //-1 for header, for EOF need to check flexibly using below line
if (count($repo_data[$repo_count]) == 1) $repo_count-=1;} //-1 for EOF (Windows files don't end with newline character)

$local_repo_location = str_replace("\\","/",dirname($script)) . '/tagui_local.csv';
if (file_exists($local_repo_location)) { // load local repository file if it exists for objects and keywords
$local_repo_file = fopen($local_repo_location,'r') or die("ERROR - cannot open " . 'tagui_local.csv' . "\n");
if ($repo_count != 0) $repo_count++; fgetcsv($local_repo_file); // +1 if array has data, discard header record
while (!feof($local_repo_file)) {$repo_data[$repo_count] = fgetcsv($local_repo_file);
if (count($repo_data[$repo_count]) == 0) die("ERROR - empty row found in " . 'tagui_local.csv' . "\n");
if (count($repo_data[$repo_count]) != 1) // pad the empty columns when local repository is used with datatable
{$repo_data[$repo_count] = array_pad($repo_data[$repo_count], count($repo_data[0]), $repo_data[$repo_count][1]);}
$repo_count++;} fclose($local_repo_file); $repo_count-=1; if (count($repo_data[$repo_count]) == 1) $repo_count-=1;}

if (file_exists('tagui_global.csv')) { // load global repository file if it exists for objects and keywords
$global_repo_file = fopen('tagui_global.csv','r') or die("ERROR - cannot open " . 'tagui_global.csv' . "\n");
if ($repo_count != 0) $repo_count++; fgetcsv($global_repo_file); // +1 if array has data, discard header record
while (!feof($global_repo_file)) {$repo_data[$repo_count] = fgetcsv($global_repo_file);
if (count($repo_data[$repo_count]) == 0) die("ERROR - empty row found in " . 'tagui_global.csv' . "\n");
if (count($repo_data[$repo_count]) != 1) // pad the empty columns when global repository is used with datatable
{$repo_data[$repo_count] = array_pad($repo_data[$repo_count], count($repo_data[0]), $repo_data[$repo_count][1]);}
$repo_count++;} fclose($global_repo_file); $repo_count-=1; if (count($repo_data[$repo_count]) == 1) $repo_count-=1;}

$tagui_web_browser = "this"; // set the web browser to be used base on tagui_web_browser environment variable
if ((getenv('tagui_web_browser')=='headless') or (getenv('tagui_web_browser')=='chrome')) $tagui_web_browser = 'chrome';

$inside_code_block = 0; // track if step or code is inside user-defined code block
$inside_while_loop = 0; // track if step is in while loop and avoid async wait
$for_loop_tracker = ""; // track for loop to implement IIFE pattern
$code_block_tracker = ""; // track code blocks defined in flow
$integration_block_body = ""; // track body of integration block
$line_number = 0; // track flow line number for error message
$real_line_number = 0; // line number excluding comments and blank lines
$test_automation = 0; // to determine casperjs script structure
$url_provided = false; // to detect if url is provided in user-script

// track begin-finish blocks for integrations eg - py, r, run, vision, js, dom
$inside_py_block = 0; $inside_r_block = 0; $inside_run_block = 0;
$inside_vision_block = 0; $inside_js_block = 0; $inside_dom_block = 0;

// series of loops to create casperjs script from header, user flow, footer files

// create header of casperjs script using tagui config and header template
fwrite($output_file,"/* OUTPUT CASPERJS SCRIPT FOR TAGUI FRAMEWORK ~ TEBEL.ORG */\n\n");
fwrite($output_file,"var casper = require('casper').create();\n"); // opening lines
while(!feof($config_file)) {fwrite($output_file,fgets($config_file));} fclose($config_file);
while(!feof($header_file)) {fwrite($output_file,fgets($header_file));} fclose($header_file);

// append global functions file created by user if the file exists
if (file_exists('tagui_global.js')) {
$global_functions_file = fopen('tagui_global.js','r') or die("ERROR - cannot open tagui_global.js" . "\n");
while(!feof($global_functions_file)) {fwrite($output_file,fgets($global_functions_file));}
fwrite($output_file,"\n"); fclose($global_functions_file);}

// append local functions file created by user if the file exists
$local_functions_location = str_replace("\\","/",dirname($script)) . '/tagui_local.js';
if (file_exists($local_functions_location)) {
$local_functions_file = fopen($local_functions_location,'r') or die("ERROR - cannot open tagui_local.js" . "\n");
while(!feof($local_functions_file)) {fwrite($output_file,fgets($local_functions_file));}
fwrite($output_file,"\n"); fclose($local_functions_file);}

// append comment on flow path variable in casperjs script
fwrite($output_file,"// flow path for save_text and snap_image\n");

// save flow path in casperjs script to be used by save_text and snap_image
// casperjs/phantomjs do not seem to support \ for windows paths, replace with / to work
// below marker for appending url_intent when flow file does not start with comments or url
$marker_for_opening_url = "var flow_path = '" . str_replace("\\","/",dirname($script)) . "';\n\n";
fwrite($output_file,$marker_for_opening_url);

// section to handle calling of other TagUI automation scripts for reusability
$temp_output_file = fopen($script . '.raw','w') or die("ERROR - cannot open " . $script . '.raw' . "\n");
while(!feof($input_file)) {fwrite($temp_output_file,expand_intent(fgets($input_file)));} fclose($input_file);
fclose($temp_output_file); // generate temp output file of expanded intents (if any) before reopening as input
$input_file = fopen($script . '.raw','r') or die("ERROR - cannot open " . $script . '.raw' . "\n");

if (strpos(strtolower(file_get_contents('tagui_config.txt')),"var tagui_language = 'english';")==false)
{ // section which includes translation engine for handling flows in other languages
$temp_argv1 = $argv[1]; $temp_argv2 = $argv[2]; $temp_argv3 = $argv[3];
$argv[1] = 'tagui_parse.php'; $argv[2] = 'from';
$temp_tagui_config = strtolower(file_get_contents('tagui_config.txt'));
$temp_tagui_config_start = strpos($temp_tagui_config,'var tagui_language');
$temp_tagui_config_end = strpos($temp_tagui_config,"\n",$temp_tagui_config_start);
$temp_tagui_config = substr($temp_tagui_config,$temp_tagui_config_start,$temp_tagui_config_end-$temp_tagui_config_start);
$temp_tagui_config = str_replace('var tagui_language','',$temp_tagui_config);
$temp_tagui_config = str_replace('"','',$temp_tagui_config); $temp_tagui_config = str_replace("'",'',$temp_tagui_config);
$temp_tagui_config = str_replace('=','',$temp_tagui_config); $temp_tagui_config = str_replace(';','',$temp_tagui_config);
$argv[3] = trim($temp_tagui_config); require 'translate.php'; // set parameters to load translation engine
$argv[1] = $temp_argv1; $argv[2] = $temp_argv2; $argv[3] = $temp_argv3; $translated_raw_flow = "";
while(!feof($input_file)) {$translated_raw_flow .= translate_intent(fgets($input_file));} fclose($input_file);
file_put_contents($script . '.raw', $translated_raw_flow); // save translated output before reopening as input
$input_file = fopen($script . '.raw','r') or die("ERROR - cannot open " . $script . '.raw' . "\n");}

// section to do required pre-processing on expanded .raw flow file
$padded_raw_flow = ""; $previous_line_is_condition = false; $reference_indentation = "";
while(!feof($input_file)) {$padded_raw_flow_line = fgets($input_file);
$indentation_tracker = str_replace(ltrim($padded_raw_flow_line),'',$padded_raw_flow_line);
$indentation_tracker = substr($indentation_tracker,strlen($reference_indentation));
// above line handles py and vision blocks that begin indented (eg in if or loops)
$padded_raw_flow_line = ltrim($padded_raw_flow_line);

// track whether line is inside integrations begin-finish code blocks
if (strtolower(trim($padded_raw_flow_line)) == "js begin") $inside_js_block = 1;
else if (strtolower(trim($padded_raw_flow_line)) == "js finish") $inside_js_block = 0;
else if (strtolower(trim($padded_raw_flow_line)) == "py begin")
{$inside_py_block = 1; $reference_indentation = $indentation_tracker;}
else if (strtolower(trim($padded_raw_flow_line)) == "py finish")
{$inside_py_block = 0; $reference_indentation = "";} // reset reference indentation
else if (strtolower(trim($padded_raw_flow_line)) == "r begin") $inside_r_block = 1; 
else if (strtolower(trim($padded_raw_flow_line)) == "r finish") $inside_r_block = 0;
else if (strtolower(trim($padded_raw_flow_line)) == "dom begin") $inside_dom_block = 1; 
else if (strtolower(trim($padded_raw_flow_line)) == "dom finish") $inside_dom_block = 0;
else if (strtolower(trim($padded_raw_flow_line)) == "run begin") $inside_run_block = 1; 
else if (strtolower(trim($padded_raw_flow_line)) == "run finish") $inside_run_block = 0;
else if (strtolower(trim($padded_raw_flow_line)) == "vision begin")
{$inside_vision_block = 1; $reference_indentation = $indentation_tracker;} 
else if (strtolower(trim($padded_raw_flow_line)) == "vision finish")
{$inside_vision_block = 0; $reference_indentation = "";} // reset reference indentation

// auto-padding not relevant in integrations code blocks
if (($inside_js_block + $inside_py_block + $inside_r_block + 
$inside_dom_block + $inside_run_block + $inside_vision_block) > 0)
{$padded_raw_flow .= $indentation_tracker . $padded_raw_flow_line; continue;}

// rewrite JS function definitions to work in scope within CasperJS blocks
if ((substr($padded_raw_flow_line,0,9)=="function ") or (substr($padded_raw_flow_line,0,12)=="js function "))
if (strpos($padded_raw_flow_line,"(")!==false) {$js_function_name_startpos = strpos($padded_raw_flow_line,"function ")+9;
$js_function_name_endpos = strpos($padded_raw_flow_line,"(",$js_function_name_startpos);
$padded_raw_flow_line = trim(substr($padded_raw_flow_line,$js_function_name_startpos,$js_function_name_endpos -
$js_function_name_startpos)) . ' = function ' . trim(substr($padded_raw_flow_line,$js_function_name_endpos))."\n";}
else die("ERROR - missing brackets () for ".$padded_raw_flow_line);
// pad { and } blocks for conditions, to keep JavaScript syntax correct
if ((substr($padded_raw_flow_line,0,3)=="if ") or (substr($padded_raw_flow_line,0,8)=="else if ")
or (substr($padded_raw_flow_line,0,4)=="for ") or (substr($padded_raw_flow_line,0,6)=="while ") or
(substr($padded_raw_flow_line,0,6)=="popup ") or (substr($padded_raw_flow_line,0,6)=="frame ") or
(trim($padded_raw_flow_line)=="else")) $current_line_is_condition = true; else $current_line_is_condition = false;
if (($previous_line_is_condition == true) and ($current_line_is_condition == true))
die("ERROR - for nested conditions, loops, popup, frame, set { and } explicitly\n".
"ERROR - add { before this line and add } accordingly - ".$padded_raw_flow_line);
if (($previous_line_is_condition == true) and (substr($padded_raw_flow_line,0,1)!="{"))
$padded_raw_flow .= "{\n".trim($padded_raw_flow_line)."\n}\n"; else $padded_raw_flow .= $padded_raw_flow_line;
$previous_line_is_condition = $current_line_is_condition; // prepare for next line
} fclose($input_file); file_put_contents($script . '.raw', $padded_raw_flow);
// generate temp output file with padded { and } (if any) before reopening as input
$input_file = fopen($script . '.raw','r') or die("ERROR - cannot open " . $script . '.raw' . "\n");
// re-initialize trackers for begin-finish blocks of integrations
$inside_py_block = 0; $inside_r_block = 0; $inside_run_block = 0;
$inside_vision_block = 0; $inside_js_block = 0; $inside_dom_block = 0;

// main loop to parse intents in flow file for conversion into javascript code
while(!feof($input_file)) {fwrite($output_file,parse_intent(fgets($input_file)));} fclose($input_file);

// create footer of casperjs script using footer template and do post-processing 
while(!feof($footer_file)) {fwrite($output_file,fgets($footer_file));} fclose($footer_file); fclose($output_file);
chmod ($script . '.js',0600); // append url opening block below instead of throwing error
if (!$url_provided) { // echo "ERROR - first line of " . $script . " not URL or comments\n";
$GLOBALS['real_line_number'] = 1; $generated_js_file_contents = file_get_contents($script . '.js');
$generated_js_file_contents = str_replace($marker_for_opening_url, $marker_for_opening_url . 
url_intent('about:blank'), $generated_js_file_contents); file_put_contents($script . '.js',$generated_js_file_contents);}
if ($inside_code_block != 0) echo "ERROR - number of step { does not tally with with }\n";

// post-processing to clean up artifacts from translating human language to JavaScript
$script_content = file_get_contents($script . '.js');
$script_content = str_replace("});\n\ncasper.then(function() {else\n","\nelse\n\n",$script_content);
$script_content = str_replace("});\n\ncasper.then(function() {else if ","\nelse if ",$script_content);
$script_content = str_replace("}); // end of JS code\n\ncasper.then(function() { // start of JS code\n",
"",$script_content); // above collapse separate js step CasperJS blocks into one continuous block
$script_content = preg_replace('/^casper\.then\(function\(\) {{(.*) \/\/ beg_tx while loop marker/m',
'$1',$script_content); // above regular expression replace lines having beg_tx while loop markers
$script_content = preg_replace('/^casper\.then\(function\(\) {(.*) \/\/ end_fi while loop marker(.*)}\);/m',
'$1$2',$script_content); // above regular expression replace lines having end_fi while loop markers
$script_content = preg_replace('/^casper\.then\(function\(\) {(.*)\n(.*) \/\/ end_fi while loop marker(.*)}\);/m',
"$1\n$2$3",$script_content); // above regular expression replace lines having end_fi while loop markers
file_put_contents($script . '.js',$script_content);

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

// if mouse_xy(), mouse_x(), mouse_y() helper functions are used, invoke sikuli visual automation
if ((strpos($script_content,'mouse_xy()')!==false) or 
(strpos($script_content,'mouse_x()')!==false) or (strpos($script_content,'mouse_y()')!==false)) {
if (!touch('tagui.sikuli/tagui_sikuli.in')) die("ERROR - cannot initialise tagui_sikuli.in\n");
if (!touch('tagui.sikuli/tagui_sikuli.out')) die("ERROR - cannot initialise tagui_sikuli.out\n");}

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

function expand_intent($script_line) { // function to handle calling of other TagUI automation scripts for reusability
if ((strpos(strtolower(trim($script_line)),'tagui ') === 0) or (strtolower(trim($script_line)) == 'tagui')) {
$params = trim(substr(trim($script_line)." ",1+strpos(trim($script_line)." "," "))); if ($params == "")
die("ERROR - filename missing for step " . trim($script_line) . "\n");
else if (!file_exists(abs_file($params)))
die("ERROR - file not found for step " . trim($script_line) . "\n");
else {$expanded_intent = ""; $temp_input_file = fopen(abs_file($params),'r'); if ($temp_input_file == false)
die("ERROR - cannot open file for step " . trim($script_line) . "\n");
while(!feof($temp_input_file)) {$expanded_intent .= expand_intent(fgets($temp_input_file));} fclose($temp_input_file);
return $expanded_intent;}} else return rtrim($script_line) . "\n";}

function current_line() {return "[LINE " . $GLOBALS['line_number'] . "]";}

function parse_intent($script_line) {$GLOBALS['line_number']++; $GLOBALS['real_line_number']++;
$indentation_tracker = str_replace(ltrim($script_line),'',$script_line); // tracking for py and vision
$script_line = trim($script_line); if ($script_line=="") {$GLOBALS['real_line_number']--; return "";}
$script_line = parse_backticks($script_line); // below check again after replacing repository definitions
$script_line = trim($script_line); if ($script_line=="") {$GLOBALS['real_line_number']--; return "";}
// below use buffer to handle integration code blocks if inside integration code block
$intent_type = get_intent($script_line); if ($intent_type == "integration_block")
{$GLOBALS['integration_block_body'] .= $indentation_tracker . $script_line ."[END_OF_LINE]"; return "";}
else {$script_line = parse_closure($script_line); return process_intent($intent_type, $script_line);}}

function parse_backticks($script_line) {
// check existence of objects or keywords by searching for `object or keyword name`, then expand from repository
// check for even number of ` to reduce false-positive because backtick syntax is supposed to be matching pairs
if ((substr_count($script_line,'`') > 1) and (!(substr_count($script_line,'`') & 1))) {
	if ($GLOBALS['repo_count'] == 0) {
		$script_line = parse_variables($script_line);
	} else {
		if (getenv('tagui_data_set')!==false) {
			$data_set = intval(getenv('tagui_data_set'));
		} else {
			$data_set = 1;
		}
		$escaped_line_ends = ["\n" => "\\n", "\r" => "\\r"];
		// loop through repository data to search and replace definitions, do it twice to handle objects within keywords
		for ($repo_check = 0; $repo_check <= $GLOBALS['repo_count']; $repo_check++) {
			$repo_keyword = "`".$GLOBALS['repo_data'][$repo_check][0]."`";
			$repo_data_value = strtr($GLOBALS['repo_data'][$repo_check][$data_set], $escaped_line_ends);
			$script_line = str_replace($repo_keyword, $repo_data_value, $script_line);
		}
		for ($repo_check = 0; $repo_check <= $GLOBALS['repo_count']; $repo_check++) {
			$repo_keyword = "`".$GLOBALS['repo_data'][$repo_check][0]."`";
			$repo_data_value = strtr($GLOBALS['repo_data'][$repo_check][$data_set], $escaped_line_ends);
			$script_line = str_replace($repo_keyword, $repo_data_value, $script_line);
		}
		if (strpos($script_line,'`')!==false) {
			$script_line = parse_variables($script_line);
		}
	}
} return $script_line;}

function parse_variables($script_line) { // `variable` --> '+variable+'
$quote_token = "'+"; // token to alternate replacements for '+variable+'
for ($char_counter = 0; $char_counter < strlen($script_line); $char_counter++) {
	if (substr($script_line,$char_counter,1) == "`") {
		$script_line = substr_replace($script_line,$quote_token,$char_counter,1);
		if ($quote_token == "'+") $quote_token = "+'"; else $quote_token = "'+";
	}
} return $script_line;}

function parse_closure($script_line) {switch($script_line) {
// \\n is needed for py, r, vision as multi-line string needs to have \n escaped to work in javascript
// replacement code for [END_OF_LINE] custom token to denote line break is done at py, r, vision intents
case "py finish":
{$script_line = substr($GLOBALS['integration_block_body'],0,-13); $GLOBALS['inside_py_block'] = 0; break;}
case "r finish":
{$script_line = substr($GLOBALS['integration_block_body'],0,-13); $GLOBALS['inside_r_block'] = 0;
$script_line = str_replace(";; ","; ",str_replace("[END_OF_LINE]","; ",$GLOBALS['integration_block_body'])); break;}
case "vision finish":
{$script_line = substr($GLOBALS['integration_block_body'],0,-13); $GLOBALS['inside_vision_block'] = 0; break;}
case "js finish":
{$script_line = str_replace("[END_OF_LINE]", "\n", $GLOBALS['integration_block_body']); $GLOBALS['inside_js_block'] = 0; break;}
case "dom finish":
{$script_line = str_replace("[END_OF_LINE]", "\n", $GLOBALS['integration_block_body']); $GLOBALS['inside_dom_block'] = 0; break;}}
return $script_line;}

function process_intent($intent_type, $script_line) {
// check intent of step for interpretation into casperjs code
switch ($intent_type) {
case "url": return url_intent($script_line); break;
case "tap": return tap_intent($script_line); break;
case "rtap": return rtap_intent($script_line); break;
case "dtap": return dtap_intent($script_line); break;
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
case "ask": return ask_intent($script_line); break;
case "keyboard": return keyboard_intent($script_line); break;
case "mouse": return mouse_intent($script_line); break;
case "check": return check_intent($script_line); break;
case "test": return test_intent($script_line); break;
case "frame": return frame_intent($script_line); break;
case "popup": return popup_intent($script_line); break;
case "api": return api_intent($script_line); break;
case "run": return run_intent($script_line); break;
case "dom": return dom_intent($script_line); break;
case "js": return js_intent($script_line); break;
case "r": return r_intent($script_line); break;
case "py": return py_intent($script_line); break;	
case "vision": return vision_intent($script_line); break;
case "timeout": return timeout_intent($script_line); break;
case "code": return code_intent($script_line); break;
default: echo "ERROR - " . current_line() . " cannot understand step " . $script_line . "\n";}}

function get_intent($raw_intent) {$lc_raw_intent = strtolower($raw_intent); 
//check for a finish command and return, so we don't accidentally count it as a block intent
if ($lc_raw_intent == "py finish") return "py"; if ($lc_raw_intent == "r finish") return "r";
if ($lc_raw_intent == "vision finish") return "vision"; if ($lc_raw_intent == "js finish") return "js";
if ($lc_raw_intent == "dom finish") return "dom";

if ($GLOBALS['inside_py_block'] != 0 || $GLOBALS['inside_r_block'] != 0 || 
$GLOBALS['inside_vision_block'] != 0 || $GLOBALS['inside_js_block'] != 0 || 
$GLOBALS['inside_dom_block'] != 0) return "integration_block";
if ($GLOBALS['inside_run_block'] != 0) return "run";

if ((substr($lc_raw_intent,0,7)=="http://") or (substr($lc_raw_intent,0,8)=="https://") or (substr($lc_raw_intent,0,11)=="about:blank")) return "url"; // recognizing about:blank as valid URL as it is part of HTML5 standard

// first set of conditions check for valid keywords with their parameters
if ((substr($lc_raw_intent,0,4)=="tap ") or (substr($lc_raw_intent,0,6)=="click ")) return "tap";
if ((substr($lc_raw_intent,0,5)=="rtap ") or (substr($lc_raw_intent,0,7)=="rclick ")) return "rtap";
if ((substr($lc_raw_intent,0,5)=="dtap ") or (substr($lc_raw_intent,0,7)=="dclick ")) return "dtap";
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
if (substr($lc_raw_intent,0,4)=="ask ") return "ask";
if (substr($lc_raw_intent,0,9)=="keyboard ") return "keyboard";
if (substr($lc_raw_intent,0,6)=="mouse ") return "mouse";
if (substr($lc_raw_intent,0,6)=="check ") {$GLOBALS['test_automation']++; return "check";}
if (substr($lc_raw_intent,0,5)=="test ") return "test";
if (substr($lc_raw_intent,0,6)=="frame ") return "frame";
if (substr($lc_raw_intent,0,6)=="popup ") return "popup";
if (substr($lc_raw_intent,0,4)=="api ") return "api";
if (substr($lc_raw_intent,0,4)=="run ") return "run";
if (substr($lc_raw_intent,0,4)=="dom ") return "dom";
if (substr($lc_raw_intent,0,3)=="js ") return "js";
if (substr($lc_raw_intent,0,2)=="r ") return "r";
if (substr($lc_raw_intent,0,3)=="py ") return "py";
if (substr($lc_raw_intent,0,7)=="vision ") return "vision";
if (substr($lc_raw_intent,0,8)=="timeout ") return "timeout";

// second set of conditions check for valid keywords with missing parameters
if (($lc_raw_intent=="tap") or ($lc_raw_intent=="click")) return "tap";
if (($lc_raw_intent=="rtap") or ($lc_raw_intent=="rclick")) return "rtap";
if (($lc_raw_intent=="dtap") or ($lc_raw_intent=="dclick")) return "dtap";
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
if ($lc_raw_intent=="ask") return "ask";
if ($lc_raw_intent=="keyboard") return "keyboard";
if ($lc_raw_intent=="mouse") return "mouse";
if ($lc_raw_intent=="check") {$GLOBALS['test_automation']++; return "check";}
if ($lc_raw_intent=="test") return "test";
if ($lc_raw_intent=="frame") return "frame";
if ($lc_raw_intent=="popup") return "popup";
if ($lc_raw_intent=="api") return "api";
if ($lc_raw_intent=="run") return "run";
if ($lc_raw_intent=="dom") return "dom";
if ($lc_raw_intent=="js") return "js";
if ($lc_raw_intent=="r") return "r";
if ($lc_raw_intent=="py") return "py";
if ($lc_raw_intent=="vision") return "vision";
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
if ((substr($raw_intent,0,6)=="break;") or ($raw_intent=="break")) return true;
if ((substr($raw_intent,0,9)=="continue;") or ($raw_intent=="continue")) return true;
if ((substr($raw_intent,0,7)=="casper.") or (substr($raw_intent,0,5)=="this.")) return true;
if (substr($raw_intent,0,7)=="chrome.") return true; // chrome object for chrome integration
if (substr($raw_intent,0,5)=="test.") {$GLOBALS['test_automation']++; return true;}
if (substr($raw_intent,0,2)=="//") {$GLOBALS['real_line_number']--; return true;} 
if (substr($raw_intent,-1)==";") return true; if (substr($raw_intent,0,9)=="function ") return true;
// assume = is assignment statement, kinda acceptable as this is checked at the very end
if (strpos($raw_intent,"=")!==false) return true; return false;}

function abs_file($filename) { // helper function to return absolute filename
if ($filename == "") return ""; $flow_script = $GLOBALS['script']; // get flow filename
if (substr($filename,0,1)=="/") return $filename; // return mac/linux absolute filename directly
if (substr($filename,1,1)==":") return str_replace("\\","/",$filename); // return windows absolute filename directly
if (strpos($filename,"'+")!==false and strpos($filename,"+'")!==false)
return "'+abs_file('" . $filename . "')+'"; // throw to runtime abs_file function if dynamic filename is given
if (is_coordinates($filename)) return $filename; // to handle when sikuli (x,y) coordinates locator is provided
$flow_path = str_replace("\\","/",dirname($flow_script)); // otherwise use flow script path to build absolute filename
// above str_replace is because casperjs/phantomjs do not seem to support \ for windows paths, replace with / to work
if (strpos($flow_path,"/")!==false) return str_replace("\\","/",$flow_path . '/' . $filename);
else return $flow_path . '\\' . $filename;} 

function beg_tx($locator) { // helper function to return beginning string for handling locators
if ($GLOBALS['inside_while_loop'] == 0)
return "\ncasper.waitFor(function check() {return check_tx('".$locator."');},\nfunction then() {";
else return " // beg_tx while loop marker\n";}

function end_tx($locator) { // helper function to return ending string for handling locators
if ($GLOBALS['inside_while_loop'] == 0)
return "},\nfunction timeout() {this.echo('ERROR - cannot find ".$locator."').exit();});}"."});".end_fi()."\n\n";
else if ($GLOBALS['inside_code_block']==0)
{$GLOBALS['inside_while_loop'] = 0; // reset inside_while_loop if not inside block
$GLOBALS['for_loop_tracker'] = ""; // reset for_loop_tracker if not inside block
return "\n";} else return "\n";}

function end_fi() { // used to be a helper function primarily to end frame_intent by closing parsed step block
if ($GLOBALS['inside_code_block'] == 0) $GLOBALS['inside_while_loop'] = 0; // reset inside_while_loop if not inside block
if ($GLOBALS['inside_code_block'] == 0) $GLOBALS['for_loop_tracker'] = ""; // reset for_loop_tracker if not inside block
if ($GLOBALS['inside_while_loop'] == 1) return " // end_fi while loop marker"; return "";}

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

function is_coordinates($input_params) { // helper function to check if string is (x,y) coordinates
if (strlen($input_params)>4 and substr($input_params,0,1)=='(' and substr($input_params,-1)==')' 
and (substr_count($input_params,',')==1 or substr_count($input_params,',')==2) 
and ((strpos($input_params,"'+")!==false and strpos($input_params,"+'")!==false) 
or !preg_match('/[a-zA-Z]/',$input_params))) return true; else return false;}

function is_sikuli($input_params) { // helper function to check if input is meant for sikuli visual automation
if (strlen($input_params)>4 and strtolower(substr($input_params,-4))=='.png') return true; // support png and bmp
else if (strlen($input_params)>4 and strtolower(substr($input_params,-4))=='.bmp') return true;
else if (is_coordinates($input_params)) return true; else return false;}

function call_sikuli($input_intent,$input_params,$other_actions = '') { // helper function to use sikuli visual automation
if (!touch('tagui.sikuli/tagui_sikuli.in')) die("ERROR - cannot initialise tagui_sikuli.in\n");
if (!touch('tagui.sikuli/tagui_sikuli.out')) die("ERROR - cannot initialise tagui_sikuli.out\n");
if ($other_actions != '') $other_actions = "\n" . $other_actions;
return "{techo('".str_replace(' to snap_image()','',$input_intent)."'); var fs = require('fs');\n" .
"if (!sikuli_step('".$input_intent."')) if (!fs.exists('".$input_params."'))\n" .
"this.echo('ERROR - cannot find image file ".$input_params."').exit(); else\n" . 
"this.echo('ERROR - cannot find " . $input_params." on screen').exit(); this.wait(0);" . $other_actions. "}" .
end_fi()."});\n\n";}

function call_r($input_intent) { // helper function to use R integration for data analytics and machine learning
if (!touch('tagui_r/tagui_r.in')) die("ERROR - cannot initialise tagui_r.in\n");
if (!touch('tagui_r/tagui_r.out')) die("ERROR - cannot initialise tagui_r.out\n");
return "{techo('".$input_intent."');\n" . "r_result = ''; if (!r_step('".$input_intent."'))\n" .
"this.echo('ERROR - cannot execute R command(s)').exit(); this.wait(0);\n" .
"r_result = fetch_r_text(); clear_r_text();\n" .
"try {r_json = JSON.parse(r_result);} catch(e) {r_json = JSON.parse('null');}}" .
end_fi()."});\n\n";}

function call_py($input_intent) { // helper function to use Python integration for data analytics and machine learning
if (!touch('tagui_py/tagui_py.in')) die("ERROR - cannot initialise tagui_py.in\n");
if (!touch('tagui_py/tagui_py.out')) die("ERROR - cannot initialise tagui_py.out\n");
return "{techo('".$input_intent."');\n" . "py_result = ''; if (!py_step('".$input_intent."'))\n" .
"this.echo('ERROR - cannot execute Python command(s)').exit(); this.wait(0);\n" .
"py_result = fetch_py_text(); clear_py_text();\n" .
"try {py_json = JSON.parse(py_result);} catch(e) {py_json = JSON.parse('null');}}" .
end_fi()."});\n\n";}

// set of functions to interpret steps into corresponding casperjs code
function url_intent($raw_intent) {$twb = $GLOBALS['tagui_web_browser']; $casper_url = $raw_intent; $chrome_call = '';
if ($twb == 'chrome') {$chrome_call = "var download_path = flow_path; // to set path correctly for Windows\n" .
"if (download_path.indexOf(':')>0) download_path = download_path.replace(/\//g,'\\\\').replace(/\\\\/g,'\\\\');\n" .
"chrome_step('Page.setDownloadBehavior',{behavior: 'allow', downloadPath: download_path});\n";
$casper_url = 'about:blank'; $chrome_call .= "chrome_step('Page.navigate',{url: '".$raw_intent."'}); sleep(1000);\n";}
if (strpos($raw_intent,"'+")!==false and strpos($raw_intent,"+'")!==false) // check if dynamic url is used
// wrap step within casper context if variable (casper context) is used in url, in order to access variable
{$dynamic_header = "casper.then(function() {"; $dynamic_footer = "}); // end of dynamic url block";}
else {$dynamic_header = ""; $dynamic_footer = ""; // else casper.start/thenOpen can be outside casper context
if (filter_var($raw_intent, FILTER_VALIDATE_URL) == false) // do url validation only for raw text url string
if ($raw_intent != 'about:blank') echo "ERROR - " . current_line() . " invalid URL " . $raw_intent . "\n";}
if ($GLOBALS['real_line_number'] == 1) { // use casper.start for first URL call and casper.thenOpen for subsequent calls
$GLOBALS['url_provided']=true; return $dynamic_header."casper.start('".$casper_url."', function() {\n".$chrome_call.
"techo('".$raw_intent."' + ' - ' + ".$twb.".getTitle() + '\\n');});".$dynamic_footer."\n\n";}
else return $dynamic_header."casper.thenOpen('".$casper_url."', function() {\n".$chrome_call."techo('".
$raw_intent."' + ' - ' + ".$twb.".getTitle());});".$dynamic_footer."\n\n";}

function tap_intent($raw_intent) {$twb = $GLOBALS['tagui_web_browser'];
$params = trim(substr($raw_intent." ",1+strpos($raw_intent." "," "))); 
if (is_sikuli($params)) {$abs_params = abs_file($params); $abs_intent = str_replace($params,$abs_params,$raw_intent);
return "casper.then(function() {".call_sikuli($abs_intent,$abs_params);} // use sikuli visual automation as needed
if ($params == "") echo "ERROR - " . current_line() . " target missing for " . $raw_intent . "\n"; else
return "casper.then(function() {"."{techo('".$raw_intent."');".beg_tx($params).$twb.".click(tx('" . $params . "'));".end_tx($params);}

function rtap_intent($raw_intent) {$twb = $GLOBALS['tagui_web_browser'];
$params = trim(substr($raw_intent." ",1+strpos($raw_intent." "," ")));
if (is_sikuli($params)) {$abs_params = abs_file($params); $abs_intent = str_replace($params,$abs_params,$raw_intent);
return "casper.then(function() {".call_sikuli($abs_intent,$abs_params);} // use sikuli visual automation as needed
if ($params == "") echo "ERROR - " . current_line() . " target missing for " . $raw_intent . "\n"; else
return "casper.then(function() {"."{techo('".$raw_intent."');".beg_tx($params).$twb.".mouse.rightclick(tx('" . $params . "'));".end_tx($params);}

function dtap_intent($raw_intent) {$twb = $GLOBALS['tagui_web_browser'];
$params = trim(substr($raw_intent." ",1+strpos($raw_intent." "," ")));
if (is_sikuli($params)) {$abs_params = abs_file($params); $abs_intent = str_replace($params,$abs_params,$raw_intent);
return "casper.then(function() {".call_sikuli($abs_intent,$abs_params);} // use sikuli visual automation as needed
if ($params == "") echo "ERROR - " . current_line() . " target missing for " . $raw_intent . "\n"; else
return "casper.then(function() {"."{techo('".$raw_intent."');".beg_tx($params).$twb.".mouse.doubleclick(tx('" . $params . "'));".end_tx($params);}

function hover_intent($raw_intent) {$twb = $GLOBALS['tagui_web_browser'];
$params = trim(substr($raw_intent." ",1+strpos($raw_intent." "," "))); 
if (is_sikuli($params)) {$abs_params = abs_file($params); $abs_intent = str_replace($params,$abs_params,$raw_intent);
return "casper.then(function() {".call_sikuli($abs_intent,$abs_params);} // use sikuli visual automation as needed
if ($params == "") echo "ERROR - " . current_line() . " target missing for " . $raw_intent . "\n"; else
return "casper.then(function() {"."{techo('".$raw_intent."');".beg_tx($params).$twb.".mouse.move(tx('" . $params . "'));".end_tx($params);}

function type_intent($raw_intent) {$twb = $GLOBALS['tagui_web_browser'];
$params = trim(substr($raw_intent." ",1+strpos($raw_intent." "," ")));
$param1 = trim(substr($params,0,strpos($params," as "))); $param2 = trim(substr($params,4+strpos($params," as ")));
if (is_sikuli($param1) and $param2 != "") {
$abs_param1 = abs_file($param1); $abs_intent = str_replace($param1,$abs_param1,$raw_intent);
return "casper.then(function() {".call_sikuli($abs_intent,$abs_param1);} // use sikuli visual automation as needed
if (($param1 == "") or ($param2 == ""))
echo "ERROR - " . current_line() . " target/text missing for " . $raw_intent . "\n"; else {
// special handling for [clear] keyword to clear text field by doing an extra step to clear the field
if (substr($param2,0,7)=="[clear]") {if (strlen($param2)>7) $param2 = substr($param2,7); else $param2 = "";
$clear_field = $twb.".sendKeys(tx('".$param1."'),'',{reset: true});\n";} else $clear_field = "";
if (strpos($param2,"[enter]")===false) return "casper.then(function() {"."{techo('".$raw_intent."');".
beg_tx($param1).$clear_field.$twb.".sendKeys(tx('".$param1."'),'".$param2."');".end_tx($param1);
else // special handling for [enter] keyword to send enter key events
{$param2 = str_replace("[enter]","',{keepFocus: true});\n" .
$twb.".sendKeys(tx('".$param1."'),casper.page.event.key.Enter,{keepFocus: true});\n" .
$twb.".sendKeys(tx('".$param1."'),'",$param2); return "casper.then(function() {"."{techo('".$raw_intent."');".
beg_tx($param1).$clear_field.$twb.".sendKeys(tx('".$param1."'),'".$param2."',{keepFocus: true});".end_tx($param1);}}}

function select_intent($raw_intent) {$twb = $GLOBALS['tagui_web_browser'];
$params = trim(substr($raw_intent." ",1+strpos($raw_intent." "," ")));
$param1 = trim(substr($params,0,strpos($params," as "))); $param2 = trim(substr($params,4+strpos($params," as ")));
if (is_sikuli($param1) and is_sikuli($param2)) {
$abs_param1 = abs_file($param1); $abs_intent = str_replace($param1,$abs_param1,$raw_intent);
$abs_param2 = abs_file($param2); $abs_intent = str_replace($param2,$abs_param2,$abs_intent);
return "casper.then(function() {".call_sikuli($abs_intent,$abs_param1);} // use sikuli visual automation as needed
if (($param1 == "") or ($param2 == ""))
echo "ERROR - " . current_line() . " target/option missing for " . $raw_intent . "\n"; else
return "casper.then(function() {"."{techo('".$raw_intent."');".beg_tx($param1)."var select_locator = tx('".$param1."');\n"
."if (is_xpath_selector(select_locator.toString().replace('xpath selector: ','')))\n".
"select_locator = select_locator.toString().substring(16);\n".
$twb.".selectOptionByValue(select_locator,'".$param2."');".end_tx($param1);}

function read_intent($raw_intent) {$twb = $GLOBALS['tagui_web_browser'];
$params = trim(substr($raw_intent." ",1+strpos($raw_intent." "," ")));
$param1 = trim(substr($params,0,strpos($params," to "))); $param2 = trim(substr($params,4+strpos($params," to ")));
if (is_sikuli($param1) and (strpos($params," to ")!==false)) { // use sikuli visual automation as needed
$abs_param1 = abs_file($param1); $abs_intent = str_replace($param1,$abs_param1,$raw_intent);
return "casper.then(function() {".
call_sikuli($abs_intent,$abs_param1,$param2." = fetch_sikuli_text(); clear_sikuli_text();");}
if ((strtolower($param1) == "page") and ($param2 != ""))
return "casper.then(function() {"."{techo('".$raw_intent."');\n".$param2." = ".$twb.".getHTML();}".end_fi()."});"."\n\n";
if (($param1 == "") or ($param2 == "")) 
echo "ERROR - " . current_line() . " target/variable missing for " . $raw_intent . "\n"; else
return "casper.then(function() {".
"{techo('".$raw_intent."');".beg_tx($param1).$param2." = ".$twb.".fetchText(tx('".$param1."')).trim();".end_tx($param1);}

function show_intent($raw_intent) {$twb = $GLOBALS['tagui_web_browser'];
$params = trim(substr($raw_intent." ",1+strpos($raw_intent." "," ")));
if (is_sikuli($params)) { // use sikuli visual automation as needed
$abs_params = abs_file($params); $abs_intent = str_replace($params,$abs_params,$raw_intent);
return "casper.then(function() {".
call_sikuli($abs_intent,$abs_params,"this.echo(fetch_sikuli_text()); clear_sikuli_text();");}
if (strtolower($params) == "page") return "casper.then(function() {".
"this.echo('".$raw_intent."' + ' - \\n' + ".$twb.".getHTML());".end_fi()."});"."\n\n";
if ($params == "") echo "ERROR - " . current_line() . " target missing for " . $raw_intent . "\n"; else
return "casper.then(function() {"."{techo('".$raw_intent."');".beg_tx($params).
"this.echo(".$twb.".fetchText(tx('" . $params . "')).trim());".end_tx($params);}

function upload_intent($raw_intent) {$twb = $GLOBALS['tagui_web_browser'];
$params = trim(substr($raw_intent." ",1+strpos($raw_intent." "," ")));
$param1 = trim(substr($params,0,strpos($params," as "))); $param2 = trim(substr($params,4+strpos($params," as ")));
if (($param1 == "") or ($param2 == ""))
echo "ERROR - " . current_line() . " filename missing for " . $raw_intent . "\n"; else
return "casper.then(function() {"."{techo('".$raw_intent."');".beg_tx($param1).
$twb.".page.uploadFile(tx('".$param1."'),'".abs_file($param2)."');".end_tx($param1);}

function down_intent($raw_intent) {$twb = $GLOBALS['tagui_web_browser'];
$params = trim(substr($raw_intent." ",1+strpos($raw_intent." "," ")));
$param1 = trim(substr($params,0,strpos($params," to "))); $param2 = trim(substr($params,4+strpos($params," to ")));
if (($param1 == "") or ($param2 == "")) 
echo "ERROR - " . current_line() . " url/filename missing for " . $raw_intent . "\n"; else
return "casper.then(function() {"."{techo('".$raw_intent."');\n".$twb.".download('".$param1."','".abs_file($param2)."');}".end_fi()."});"."\n\n";}

function receive_intent($raw_intent) {$twb = $GLOBALS['tagui_web_browser'];
$params = trim(substr($raw_intent." ",1+strpos($raw_intent." "," ")));
$param1 = trim(substr($params,0,strpos($params," to "))); $param2 = trim(substr($params,4+strpos($params," to ")));
if (($param1 == "") or ($param2 == "")) 
echo "ERROR - " . current_line() . " keyword/filename missing for " . $raw_intent . "\n"; else
return "casper.then(function() {"."{techo('".$raw_intent."');\n".
"casper.on('resource.received', function(resource) {if (resource.stage !== 'end') return;\n".
"if (resource.url.indexOf('".$param1."') > -1) ".$twb.".download(resource.url, '".abs_file($param2)."');});}".end_fi()."});"."\n\n";}

function echo_intent($raw_intent) {
$params = trim(substr($raw_intent." ",1+strpos($raw_intent." "," ")));
if ($params == "") echo "ERROR - " . current_line() . " text missing for " . $raw_intent . "\n"; else 
return "casper.then(function() {"."this.echo(".add_concat($params).");".end_fi()."});"."\n\n";}

function save_intent($raw_intent) {$twb = $GLOBALS['tagui_web_browser'];
$params = trim(substr($raw_intent." ",1+strpos($raw_intent." "," ")));
$param1 = trim(substr($params,0,strpos($params," to "))); $param2 = trim(substr($params,4+strpos($params," to ")));
if (is_sikuli($param1) and (strpos($params," to ")!==false)) { // use sikuli visual automation as needed
$abs_param1 = abs_file($param1); $abs_intent = str_replace($param1,$abs_param1,$raw_intent);
return "casper.then(function() {".
call_sikuli($abs_intent,$abs_param1,"save_text('".abs_file($param2)."',fetch_sikuli_text()); clear_sikuli_text();");}
else if (is_sikuli($params) and (strpos($params," to ")==false)) {
$abs_params = abs_file($params); $abs_intent = str_replace($params,$abs_params,$raw_intent);
return "casper.then(function() {".
call_sikuli($abs_intent,$abs_param1,"save_text('',fetch_sikuli_text()); clear_sikuli_text();");}
if ((strtolower($params) == "page") or (strtolower($param1) == "page")) {if (strpos($params," to ")!==false)
return "casper.then(function() {".
"{techo('".$raw_intent."');\nsave_text('".abs_file($param2)."',".$twb.".getHTML());}".end_fi()."});"."\n\n";
else return "casper.then(function() {"."{techo('".$raw_intent."');\nsave_text('',".$twb.".getHTML());}".end_fi()."});"."\n\n";}
if ($params == "") echo "ERROR - " . current_line() . " target missing for " . $raw_intent . "\n"; 
else if (strpos($params," to ")!==false)
return "casper.then(function() {"."{techo('".$raw_intent."');".beg_tx($param1).
	"save_text('".abs_file($param2)."',".$twb.".fetchText(tx('".$param1."')).trim());".end_tx($param1); else
return "casper.then(function() {"."{techo('".$raw_intent."');".beg_tx($params).
	"save_text('',".$twb.".fetchText(tx('" . $params . "')).trim());".end_tx($params);}

function dump_intent($raw_intent) {
$safe_intent = str_replace("'","\'",$raw_intent); // avoid breaking echo below when single quote is used
$params = trim(substr($raw_intent." ",1+strpos($raw_intent." "," ")));
$param1 = trim(substr($params,0,strpos($params," to "))); $param2 = trim(substr($params,4+strpos($params," to ")));
if ($params == "") echo "ERROR - " . current_line() . " variable missing for " . $raw_intent . "\n"; 
else if (strpos($params," to ")!==false)
return "casper.then(function() {".
"{techo('".$safe_intent."');\nsave_text('".abs_file($param2)."',".add_concat($param1).");}".end_fi()."});"."\n\n";
else return "casper.then(function() {".
"{techo('".$safe_intent."');\nsave_text(''," . add_concat($params) . ");}".end_fi()."});"."\n\n";}

function write_intent($raw_intent) {
$safe_intent = str_replace("'","\'",$raw_intent); // avoid breaking echo below when single quote is used
$params = trim(substr($raw_intent." ",1+strpos($raw_intent." "," ")));
$param1 = trim(substr($params,0,strpos($params," to "))); $param2 = trim(substr($params,4+strpos($params," to ")));
if ($params == "") echo "ERROR - " . current_line() . " variable missing for " . $raw_intent . "\n";
else if (strpos($params," to ")!==false)
return "casper.then(function() {".
"{techo('".$safe_intent."');\nappend_text('".abs_file($param2)."',".add_concat($param1).");}".end_fi()."});"."\n\n";
else return "casper.then(function() {".
"{techo('".$safe_intent."');\nappend_text(''," . add_concat($params) . ");}".end_fi()."});"."\n\n";}

function load_intent($raw_intent) {
$safe_intent = str_replace("'","\'",$raw_intent); // avoid breaking echo below when single quote is used
$params = trim(substr($raw_intent." ",1+strpos($raw_intent." "," ")));
$param1 = trim(substr($params,0,strpos($params," to "))); $param2 = trim(substr($params,4+strpos($params," to ")));
if ($params == "") echo "ERROR - " . current_line() . " filename missing for " . $raw_intent . "\n";
else if (strpos($params," to ")!==false)
return "casper.then(function() {"."{techo('".$safe_intent."');\nvar fs = require('fs'); ".$param2." = '';\n".
	"if (fs.exists('".abs_file($param1)."'))\n".$param2." = fs.read('".abs_file($param1)."').trim();\n".
	"else this.echo('ERROR - cannot find file ".$param1."').exit();}".end_fi()."});"."\n\n";
else echo "ERROR - " . current_line() . " variable missing for " . $raw_intent . "\n";}

function snap_intent($raw_intent) {$twb = $GLOBALS['tagui_web_browser'];
$params = trim(substr($raw_intent." ",1+strpos($raw_intent." "," ")));
$param1 = trim(substr($params,0,strpos($params," to "))); $param2 = trim(substr($params,4+strpos($params," to ")));
if (is_sikuli($param1) and (strpos($params," to ")!==false)) {
$abs_param1 = abs_file($param1); $abs_intent = str_replace($param1,$abs_param1,$raw_intent);
$abs_param2 = abs_file($param2); $abs_intent = str_replace($param2,$abs_param2,$abs_intent);
return "casper.then(function() {".call_sikuli($abs_intent,$abs_param1);} // use sikuli visual automation as needed
else if (is_sikuli($params) and (strpos($params," to ")==false)) {
$abs_params = abs_file($params); $abs_intent = str_replace($params,$abs_params,$raw_intent);
return "casper.then(function() {".call_sikuli($abs_intent.' to snap_image()',$abs_params);} // handle no output filename
if ((strtolower($params) == "page") or (strtolower($param1) == "page")) {if (strpos($params," to ")!==false)
return "casper.then(function() {".
"{techo('".$raw_intent."');\n".$twb.".capture('".abs_file($param2)."');}".end_fi()."});"."\n\n";
else return "casper.then(function() {".
"{techo('".$raw_intent."');\n".$twb.".capture(snap_image());}".end_fi()."});"."\n\n";}
if ($params == "") echo "ERROR - " . current_line() . " target missing for " . $raw_intent . "\n"; 
else if (strpos($params," to ")!==false)
return "casper.then(function() {"."{techo('".$raw_intent."');".beg_tx($param1).
	$twb.".captureSelector('".abs_file($param2)."',tx('".$param1."'));".end_tx($param1); else
return "casper.then(function() {"."{techo('".$raw_intent."');".beg_tx($params).
	$twb.".captureSelector(snap_image(),tx('".$params."'));".end_tx($params);}

function table_intent($raw_intent) {$twb = $GLOBALS['tagui_web_browser'];
$params = trim(substr($raw_intent." ",1+strpos($raw_intent." "," ")));
$param1 = trim(substr($params,0,strpos($params," to "))); $param2 = trim(substr($params,4+strpos($params," to ")));
if ($params == "") echo "ERROR - " . current_line() . " target missing for " . $raw_intent . "\n";
else if (strpos($params," to ")!==false)
return "casper.then(function() {"."{techo('".$raw_intent."');".beg_tx($param1).
	"save_table('".abs_file($param2)."',tx('".$param1."'));".end_tx($param1); else
return "casper.then(function() {"."{techo('".$raw_intent."');".beg_tx($params).
        "save_table('',tx('".$params."'));".end_tx($params);}

function wait_intent($raw_intent) {
$params = trim(substr($raw_intent." ",1+strpos($raw_intent." "," "))); if ($params == "") $params = "5";
if (strpos($params,"'+")!==false and strpos($params,"+'")!==false) // handling for dynamic time
return "casper.then(function() {".
"techo('".$raw_intent."');});\ncasper.then(function() {casper.wait((parseFloat('".$params."')*1000), function() {"."});});"."\n\n";
else return "casper.then(function() {".
"techo('".$raw_intent."');});\ncasper.then(function() {casper.wait(" . (floatval($params)*1000) . ", function() {"."});});"."\n\n";}

function live_intent($raw_intent) { // live mode to interactively test tagui steps and js code (casperjs context)
return "casper.then(function() {".
"{var live_input = '';\nvar sys = require('system'); sys.stdout.write('LIVE MODE - type done to quit\\n \\b');\n".
"while (true) {live_input = sys.stdin.readLine(); // evaluate input in casperjs context until done is entered\n".
"if (live_input.indexOf('done') == 0) break; try {eval(tagui_parse(live_input));}
catch(e) {this.echo('ERROR - ' + e.message.charAt(0).toLowerCase() + e.message.slice(1));}}}".end_fi()."});"."\n\n";}

function ask_intent($raw_intent) { // ask user for input during automation and save to ask_result variable
$raw_intent = str_replace("\'","'",$raw_intent); $raw_intent = str_replace("'","\'",$raw_intent);
$params = trim(substr($raw_intent." ",1+strpos($raw_intent." "," ")));
if ($params == "") echo "ERROR - " . current_line() . " prompt missing for " . $raw_intent . "\n";
else if (getenv('tagui_web_browser')=='chrome') { // show a popup window if running on visible chrome mode
return "casper.then(function() {".
"{ask_result = ''; ask_result = chrome.evaluate(function() {\nreturn prompt('".$params."');}); ".
"if (ask_result == null) ask_result = '';}".end_fi()."});"."\n\n";}
else return "casper.then(function() {".
"{ask_result = ''; var sys = require('system');\nthis.echo('".$params." '); ".
"ask_result = sys.stdin.readLine();}".end_fi()."});"."\n\n";}

function keyboard_intent($raw_intent) {
$params = trim(substr($raw_intent." ",1+strpos($raw_intent." "," ")));
if ($params == "") echo "ERROR - " . current_line() . " keys to type missing for " . $raw_intent . "\n";
return "casper.then(function() {".call_sikuli($raw_intent,$params);}

function mouse_intent($raw_intent) {
$params = trim(substr($raw_intent." ",1+strpos($raw_intent." "," ")));
if ($params == "") echo "ERROR - " . current_line() . " down / up missing for " . $raw_intent . "\n";
else if (strtolower($params) == "down") return "casper.then(function() {".call_sikuli($raw_intent,"down");
else if (strtolower($params) == "up") return "casper.then(function() {".call_sikuli($raw_intent,"up");
else echo "ERROR - " . current_line() . " cannot understand step " . $raw_intent . "\n";}

// helper function as check_intent() adds an if block that immediately closes without going through closure handling
function check_intent_clear_injected_if_block() {$last_delimiter_pos = strrpos($GLOBALS['code_block_tracker'],"|");
$GLOBALS['code_block_tracker']=substr($GLOBALS['code_block_tracker'],0,$last_delimiter_pos); return "";}

function check_intent($raw_intent) {
$params = trim(substr($raw_intent." ",1+strpos($raw_intent." "," ")));
$params = str_replace("||"," JAVASCRIPT_OR ",$params); // to handle conflict with "|" delimiter 
$param1 = trim(substr($params,0,strpos($params,"|"))); $param2 = trim(substr($params,1+strpos($params,"|")));
$param3 = trim(substr($param2,1+strpos($param2,"|"))); $param2 = trim(substr($param2,0,strpos($param2,"|")));
$param1 = str_replace(" JAVASCRIPT_OR ","||",$param1); // to restore back "||" that were replaced
$param2 = str_replace(" JAVASCRIPT_OR ","||",$param2); $param3 = str_replace(" JAVASCRIPT_OR ","||",$param3);
if (substr_count($params,"|")!=2) 
echo "ERROR - " . current_line() . " if/true/false missing for " . $raw_intent . "\n";
else if (getenv('tagui_test_mode') == 'true') return "casper.then(function() {"."{".parse_condition("if ".$param1).
"\ntest.assert(true,".add_concat($param2).");\nelse test.assert(false,".add_concat($param3).");}".
check_intent_clear_injected_if_block().end_fi()."});"."\n\n";
else return "casper.then(function() {"."{".parse_condition("if ".$param1)."\nthis.echo(".add_concat($param2).
");\nelse this.echo(".add_concat($param3).");}".check_intent_clear_injected_if_block().end_fi()."});"."\n\n";}

function test_intent($raw_intent) {
echo "ERROR - " . current_line() . " use CasperJS tester module to professionally " . $raw_intent . "\n";
echo "ERROR - " . current_line() . " info at http://docs.casperjs.org/en/latest/modules/tester.html" . "\n";
echo "ERROR - " . current_line() . " support CSS selector or tx('selector') for XPath algo by TagUI" . "\n";}

function frame_intent($raw_intent) {
$params = trim(substr($raw_intent." ",1+strpos($raw_intent." "," ")));
$param1 = trim(substr($params,0,strpos($params,"|"))); $param2 = trim(substr($params,1+strpos($params,"|")));
if ($params == "") echo "ERROR - " . current_line() . " name missing for " . $raw_intent . "\n"; 
else if (strpos($params,"|")!==false)
{$GLOBALS['code_block_tracker'] .= "|" . 'dframe';
return "casper.then(function() {"."techo('".$raw_intent."');});\n".
"casper.withFrame('".$param1."', function() {casper.withFrame('".$param2."', function() {\n";} else
{$GLOBALS['code_block_tracker'] .= "|" . 'frame';
return "casper.then(function() {"."techo('".$raw_intent."');});\n"."casper.withFrame('".$params."', function() {\n";}}

function popup_intent($raw_intent) {
$params = trim(substr($raw_intent." ",1+strpos($raw_intent." "," ")));
if ($params == "") echo "ERROR - " . current_line() . " keyword missing for " . $raw_intent . "\n";
else {$GLOBALS['code_block_tracker'] .= "|" . 'popup';
return "casper.then(function() {"."techo('".$raw_intent."');"."});\n".
"casper.waitForPopup(/".preg_quote($params)."/, function then() {},\n".
"function timeout() {this.echo('ERROR - cannot find popup ".$params."').exit();});\n".
"casper.withPopup(/".preg_quote($params)."/, function() {\n";}}

function api_intent($raw_intent) {
$params = trim(substr($raw_intent." ",1+strpos($raw_intent." "," ")));
if ($params == "") echo "ERROR - " . current_line() . " API URL missing for " . $raw_intent . "\n"; else
return "casper.then(function() {"."{techo('".$raw_intent."');\napi_result = ''; api_result = call_api('".$params."');\n".
"try {api_json = JSON.parse(api_result);} catch(e) {api_json = JSON.parse('null');}}".end_fi()."});"."\n\n";}

function run_intent($raw_intent) {
$raw_intent = str_replace('\\','\\\\',$raw_intent); // to call paths correctly for Windows
if (strtolower($raw_intent) == "run begin") {$GLOBALS['inside_run_block'] = 1; return "";}
else if (strtolower($raw_intent) == "run finish") {$GLOBALS['inside_run_block'] = 0; return "";}
if ($GLOBALS['inside_run_block'] == 1) $raw_intent = "run " . $raw_intent;
$safe_intent = str_replace("'","\'",$raw_intent); // avoid breaking echo when single quote is used
$params = trim(substr($raw_intent." ",1+strpos($raw_intent." "," ")));
if ($params == "") echo "ERROR - " . current_line() . " command to run missing for " . $raw_intent . "\n"; else
return "casper.then(function() {"."techo('".$safe_intent."');});\ncasper.then(function() {".
"casper.waitForExec('".$params."', null, function(response) {run_result = '';\n" .
"run_result = (response.data.stdout.trim() || response.data.stderr.trim()); " .
"run_result = run_result.replace(/\\r\\n/g,'\\n');\nrun_json = response.data;}, function() {" .
"this.echo('ERROR - command to run exceeded '+(casper.options.waitTimeout/1000).toFixed(1)+'s timeout').exit();},".
"casper.options.waitTimeout);});\n\n";}

function dom_intent($raw_intent) {$twb = $GLOBALS['tagui_web_browser']; if (strtolower($raw_intent) == "dom begin")
{$GLOBALS['inside_dom_block'] = 1; $GLOBALS['integration_block_body'] = "dom "; return "";}
$params = trim(substr($raw_intent." ",1+strpos($raw_intent." "," ")));
if ($params == "") echo "ERROR - " . current_line() . " statement missing for " . $raw_intent . "\n";
else return "casper.then(function() {"."dom_result = ''; dom_result = ".$twb.".evaluate(function(dom_json) {".$params."}, dom_json);".end_fi()."});"."\n\n";}

function js_intent($raw_intent) {if (strtolower($raw_intent) == "js begin")
{$GLOBALS['inside_js_block'] = 1; $GLOBALS['integration_block_body'] = "js "; return "";}
$params = trim(substr($raw_intent." ",1+strpos($raw_intent." "," ")));
if ($params == "") echo "ERROR - " . current_line() . " statement missing for " . $raw_intent . "\n";
else return "casper.then(function() { // start of JS code\n".$params."\n}); // end of JS code"."\n\n";}

function r_intent($raw_intent) {if (strtolower($raw_intent) == "r begin")
{$GLOBALS['inside_r_block'] = 1; $GLOBALS['integration_block_body'] = "r "; return "";}
$raw_intent = str_replace('\\','\\\\',$raw_intent); // to send \ correctly over to integration 
$raw_intent = str_replace('[END_OF_LINE]','\\n',$raw_intent); // replace after above to prevent from escape
$safe_intent = str_replace("'","\'",$raw_intent); // avoid breaking echo when single quote is used
$params = trim(substr($raw_intent." ",1+strpos($raw_intent." "," ")));
if ($params == "") echo "ERROR - " . current_line() . " R command(s) missing for " . $raw_intent . "\n"; else
return "casper.then(function() {".call_r($safe_intent);}

function py_intent($raw_intent) {if (strtolower($raw_intent) == "py begin")
{$GLOBALS['inside_py_block'] = 1; $GLOBALS['integration_block_body'] = "py "; return "";}
$raw_intent = str_replace('\\','\\\\',$raw_intent); // to send \ correctly over to integration 
$raw_intent = str_replace('[END_OF_LINE]','\\n',$raw_intent); // replace after above to prevent from escape
$safe_intent = str_replace("'","\'",$raw_intent); // avoid breaking echo when single quote is used
$params = trim(substr($raw_intent." ",1+strpos($raw_intent." "," ")));
if ($params == "") echo "ERROR - " . current_line() . " Python command(s) missing for " . $raw_intent . "\n"; else
return "casper.then(function() {".call_py($safe_intent);}

function vision_intent($raw_intent) {if (strtolower($raw_intent) == "vision begin")
{$GLOBALS['inside_vision_block'] = 1; $GLOBALS['integration_block_body'] = "vision "; return "";}
$raw_intent = str_replace('\\','\\\\',$raw_intent); // to send \ correctly over to integration
$raw_intent = str_replace('[END_OF_LINE]','\\n',$raw_intent); // replace after above to prevent from escape
$safe_intent = str_replace("'","\'",$raw_intent); // avoid breaking echo when single quote is used
$params = trim(substr($raw_intent." ",1+strpos($raw_intent." "," ")));
if ($params == "") echo "ERROR - " . current_line() . " Sikuli command(s) missing for " . $raw_intent . "\n"; else
return "casper.then(function() {".call_sikuli($safe_intent,'for vision step');} // use sikuli visual automation explicitly

function timeout_intent($raw_intent) {
$params = trim(substr($raw_intent." ",1+strpos($raw_intent." "," ")));
if ($params == "") echo "ERROR - " . current_line() . " time in seconds missing for " . $raw_intent . "\n";
else return "casper.then(function() {"."casper.options.waitTimeout = " . (floatval($params)*1000) . 
"; sikuli_timeout(" . floatval($params) . ");" . end_fi()."});"."\n\n";}

function code_intent($raw_intent) {
$params = parse_condition($raw_intent);
if (substr($raw_intent,0,2)=="//") return $params."\n"; // return comments directly without casper.then wrappers
if ((substr($raw_intent,0,1)=="{") or (substr($raw_intent,0,1)=="}")) return $params."\n"; // no wrappers for {}
if ((substr($params,0,3)=="if ") or (substr($params,0,8)=="else if ")
or (substr($params,0,4)=="for ") or (substr($params,0,6)=="while ") or
(trim($raw_intent)=="else")) return "casper.then(function() {".$params."\n";
else if ($GLOBALS['inside_while_loop'] == 1) return $params."\n";
else return "casper.then(function() { // start of JS code\n".$params."\n}); // end of JS code"."\n\n";}

function parse_condition($logic) { // natural language handling for conditions
$raw_logic = $logic; // store an original copy for use when showing error message
if (substr($logic,0,2)=="//") return $logic; // skip processing for comment

// section 1 - replace braces block {} with casperjs block to group steps or code
// take only lines starting with { or } as code blocks for processing, otherwise will break many valid javascript code
if (substr($logic,0,1) == "{") $GLOBALS['inside_code_block']++; // assume nothing on rest of the line except comment
if (substr($logic,0,1) == "}") $GLOBALS['inside_code_block']--; // assume nothing on rest of the line except comment
$code_block_header = ""; $code_block_footer = "";
$last_delimiter_pos = strrpos($GLOBALS['code_block_tracker'],"|");
$code_block_intent = substr($GLOBALS['code_block_tracker'],$last_delimiter_pos+1);

if (($GLOBALS['inside_code_block'] > substr_count($GLOBALS['code_block_tracker'],"|")) and (substr($logic,0,1) == "{"))
{$GLOBALS['code_block_tracker'] .= "|" . 'normal_bracket'; return $logic."\n";} // handle normal JavaScript brackets
else if ((substr($logic,0,1) == "}") and ($code_block_intent == "normal_bracket"))
{$GLOBALS['code_block_tracker']=substr($GLOBALS['code_block_tracker'],0,$last_delimiter_pos); return $logic."\n";}

if (($code_block_intent == "if") or ($code_block_intent == "else if") or ($code_block_intent == "else")
or ($code_block_intent == "popup") or ($code_block_intent == "frame")) {if (substr($logic,0,1) == "}")
$GLOBALS['code_block_tracker']=substr($GLOBALS['code_block_tracker'],0,$last_delimiter_pos);}

else if (($code_block_intent == "for") and (substr($logic,0,1) == "{")) {
$last_delimiter_pos = strrpos($GLOBALS['for_loop_tracker'],"|");
$for_loop_variable_name = substr($GLOBALS['for_loop_tracker'],$last_delimiter_pos+1);
$code_block_header = "{casper.then(function() {for_loop_signal = '[CONTINUE_SIGNAL][".$for_loop_variable_name."]';});\n".
"(function (" . $for_loop_variable_name . ") { // start of IIFE pattern\n";}

else if (($code_block_intent == "for") and (substr($logic,0,1) == "}")) {
$last_delimiter_pos = strrpos($GLOBALS['for_loop_tracker'],"|");
$for_loop_variable_name = substr($GLOBALS['for_loop_tracker'],$last_delimiter_pos+1);
$code_block_footer = "})(" . $for_loop_variable_name . "); // end of IIFE pattern, with dummy marker for break step\n".
"casper.then(function() {for_loop_signal = '[BREAK_SIGNAL][".$for_loop_variable_name."]';});}";
$last_delimiter_pos = strrpos($GLOBALS['code_block_tracker'],"|");
$GLOBALS['code_block_tracker']=substr($GLOBALS['code_block_tracker'],0,$last_delimiter_pos);
$last_delimiter_pos = strrpos($GLOBALS['for_loop_tracker'],"|");
$GLOBALS['for_loop_tracker']=substr($GLOBALS['for_loop_tracker'],0,$last_delimiter_pos);}

else if ($code_block_intent == "dframe") {if (substr($logic,0,1) == "}") {$code_block_footer = "});";
$GLOBALS['code_block_tracker']=substr($GLOBALS['code_block_tracker'],0,$last_delimiter_pos);}}

else if ($code_block_intent == "while") {if (substr($logic,0,1) == "}") {$GLOBALS['inside_while_loop'] = 0;
$GLOBALS['code_block_tracker']=substr($GLOBALS['code_block_tracker'],0,$last_delimiter_pos);}}

if (substr($logic,0,1) == "{") $logic = $code_block_header."{ // start of code block\n".substr($logic,1)."\n";
else if (substr($logic,0,1) == "}") $logic = "} // end of code block\n".substr($logic,1)."\n".$code_block_footer."});\n";
$logic = str_replace("\n\n","\n",$logic); // clean up empty lines from { and } processing

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
$GLOBALS['code_block_tracker'] .= "|" . 'for'; // append for loop marker to track upcoming code block
$GLOBALS['for_loop_tracker'] .= "|" . trim(substr($logic,strpos($logic,"(")+1,strpos($logic,"=")-strpos($logic,"(")-1));}

// add opening and closing brackets twice to handle no brackets, and, or cases
if (substr($logic,0,3)=="if ") {
$GLOBALS['code_block_tracker'] .= "|" . 'if'; $logic = "if ((" . trim(substr($logic,3)) . "))";
if (strpos($raw_logic,"{")!==false) echo "ERROR - " . current_line() . " put { to next line - " . $raw_logic . "\n";}
if (substr($logic,0,8)=="else if ") {
$GLOBALS['code_block_tracker'] .= "|" . 'else if'; $logic = "else if ((" . trim(substr($logic,8)) . "))";
if (strpos($raw_logic,"{")!==false) echo "ERROR - " . current_line() . " put { to next line - " . $raw_logic . "\n";}
if (substr($logic,0,6)=="while ") {
$GLOBALS['code_block_tracker'] .= "|" . 'while'; $logic = "while ((" . trim(substr($logic,6)) . "))";
if (strpos($raw_logic,"{")!==false) echo "ERROR - " . current_line() . " put { to next line - " . $raw_logic . "\n";}}

// special part for else because not in section 2 condition block
if (trim($logic)=="else") {$GLOBALS['code_block_tracker'] .= "|" . 'else'; $logic = "else";
if (strpos($raw_logic,"{")!==false) echo "ERROR - " . current_line() . " put { to next line - " . $raw_logic . "\n";}

// section 3 - track if next statement is going to be or still inside while loop,
// then avoid async wait (casper.then/waitFor/timeout will hang casperjs/phantomjs??) 
if (substr($logic,0,6)=="while ") $GLOBALS['inside_while_loop'] = 1; 

// section 4 - to handle break and continue steps in for loops 
if (($logic=="break") or ($logic=="break;"))
{$teleport_marker = str_replace("|","",substr($GLOBALS['for_loop_tracker'],strrpos($GLOBALS['for_loop_tracker'],"|")));
$logic = "casper.bypass(teleport_distance('[BREAK_SIGNAL][".$teleport_marker."]')); return;";}
if (($logic=="continue") or ($logic=="continue;"))
{$teleport_marker = str_replace("|","",substr($GLOBALS['for_loop_tracker'],strrpos($GLOBALS['for_loop_tracker'],"|")));
$logic = "casper.bypass(teleport_distance('[CONTINUE_SIGNAL][".$teleport_marker."]')); return;";}

// return code after all the parsing and special handling
return $logic;}

?>
