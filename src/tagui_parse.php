<?php

/* PARSER SCRIPT FOR TA.GUI FRAMEWORK ~ TEBEL.ORG */

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

$inside_loop = 0; // track if step is in loop and avoid async wait
$inside_frame = 0; $line_number = 0; // track html frame, flow lines
$test_automation = 0; // to determine casperjs script structure
$url_provided = false; // to detect if url is provided in user-script

// series of loops to create casperjs script from header, user flow, footer files

// create header of casperjs script using tagui config and header template
fwrite($output_file,"/* OUTPUT CASPERJS SCRIPT FOR TA.GUI FRAMEWORK ~ TEBEL.ORG */\n\n");
fwrite($output_file,"var casper = require('casper').create();\n"); // opening lines
while(!feof($config_file)) {fwrite($output_file,fgets($config_file));} fclose($config_file);
while(!feof($header_file)) {fwrite($output_file,fgets($header_file));} fclose($header_file);

// save flow path in casperjs script to be used by save_text and snap_image
// casperjs/phantomjs do not seem to support \ for windows paths, replace with / to work
fwrite($output_file,"var flow_path = '" . str_replace("\\","/",dirname($script)) . "';\n\n");

// main loop to parse intents in flow file for conversion into javascript code
while(!feof($input_file)) {fwrite($output_file,parse_intent(fgets($input_file)));} fclose($input_file);

// create footer of casperjs script using footer template and do post-processing 
while(!feof($footer_file)) {fwrite($output_file,fgets($footer_file));} fclose($footer_file); fclose($output_file);
chmod ($script . '.js',0600); if (!$url_provided) echo "ERROR - first line of " . $script . " not URL\n";

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
$script_content = str_replace("if (!quiet_mode) casper.echo(echo_string);",
"if (!quiet_mode) casper.test.comment(echo_string);",$script_content); // change echo to test comment in techo
$script_content = str_replace("\\n'","'",str_replace("'\\n","'",$script_content)); // compact test output
// casperjs testing does not allow creation of casper object as it is already created by test engine
$script_content = str_replace("var casper = require(","// var casper = require(",$script_content);
// following help to define the script structure required by casperjs for test automation purpose
$script_content = str_replace("casper.start(","casper.test.begin('" . str_replace("\\","\\\\",$script) . "', " . 
$test_automation.", function(test) {\ncasper.start(",$script_content); // define required casperjs test structure
$script_content = str_replace("casper.run();","casper.run(function(){test.done();});});",$script_content);
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
case "down": return down_intent($script_line); break;
case "receive": return receive_intent($script_line); break;
case "echo": return echo_intent($script_line); break;
case "save": return save_intent($script_line); break;
case "dump": return dump_intent($script_line); break;
case "snap": return snap_intent($script_line); break;
case "wait": return wait_intent($script_line); break;
case "live": return live_intent($script_line); break;
case "check": return check_intent($script_line); break;
case "test": return test_intent($script_line); break;
case "frame": return frame_intent($script_line); break;
case "api": return api_intent($script_line); break;
case "js": return js_intent($script_line); break;
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
if ((substr($lc_raw_intent,0,5)=="down ") or (substr($lc_raw_intent,4,5)=="load ")) return "down";
if (substr($lc_raw_intent,0,8)=="receive ") return "receive";
if (substr($lc_raw_intent,0,5)=="echo ") return "echo";
if (substr($lc_raw_intent,0,5)=="save ") return "save";
if (substr($lc_raw_intent,0,5)=="dump ") return "dump";
if (substr($lc_raw_intent,0,5)=="snap ") return "snap";
if (substr($lc_raw_intent,0,5)=="wait ") return "wait";
if (substr($lc_raw_intent,0,5)=="live ") return "live";
if (substr($lc_raw_intent,0,6)=="check ") return "check";
if (substr($lc_raw_intent,0,5)=="test ") return "test";
if (substr($lc_raw_intent,0,6)=="frame ") return "frame";
if (substr($lc_raw_intent,0,4)=="api ") return "api";
if (substr($lc_raw_intent,0,3)=="js ") return "js";

// second set of conditions check for valid keywords with missing parameters
if (($lc_raw_intent=="tap") or ($lc_raw_intent=="click")) return "tap";
if (($lc_raw_intent=="hover") or ($lc_raw_intent=="move")) return "hover";
if (($lc_raw_intent=="type") or ($lc_raw_intent=="enter")) return "type";
if (($lc_raw_intent=="select") or ($lc_raw_intent=="choose")) return "select";
if (($lc_raw_intent=="read") or ($lc_raw_intent=="fetch")) return "read";
if (($lc_raw_intent=="show") or ($lc_raw_intent=="print")) return "show";
if (($lc_raw_intent=="down") or ($lc_raw_intent=="download")) return "down";
if ($lc_raw_intent=="receive") return "receive";
if ($lc_raw_intent=="echo") return "echo";
if ($lc_raw_intent=="save") return "save";
if ($lc_raw_intent=="dump") return "dump";
if ($lc_raw_intent=="snap") return "snap";
if ($lc_raw_intent=="wait") return "wait";
if ($lc_raw_intent=="live") return "live";
if ($lc_raw_intent=="check") return "check";
if ($lc_raw_intent=="test") return "test";
if ($lc_raw_intent=="frame") return "frame";
if ($lc_raw_intent=="api") return "api";
if ($lc_raw_intent=="js") return "js";

// final check for recognized code before returning error 
if (is_code($raw_intent)) return "code"; else return "error";}

function is_code($raw_intent) {
// due to asynchronous waiting for element, if/for/while can work for parsing single step
// other scenarios can be assumed to behave as unparsed javascript in casperjs context
if ((substr($raw_intent,0,4)=="var ") or (substr($raw_intent,0,3)=="do ")) return true;
if ((substr($raw_intent,0,1)=="{") or (substr($raw_intent,0,1)=="}")) return true;
if ((substr($raw_intent,0,3)=="if ") or (substr($raw_intent,0,4)=="else")) return true;
if ((substr($raw_intent,0,4)=="for ") or (substr($raw_intent,0,6)=="while ")) return true;
if ((substr($raw_intent,0,7)=="switch ") or (substr($raw_intent,0,5)=="case ")) return true;
if ((substr($raw_intent,0,6)=="break;") or (substr($raw_intent,0,9)=="function ")) return true;
if ((substr($raw_intent,0,7)=="casper.") or (substr($raw_intent,0,5)=="this.")) return true;
if (substr($raw_intent,0,5)=="test.") {$GLOBALS['test_automation']++; return true;}
if ((substr($raw_intent,0,2)=="//") or (substr($raw_intent,-1)==";")) return true; return false;}

function abs_file($filename) { // helper function to return absolute filename
if ($filename == "") return ""; $flow_script = $GLOBALS['script']; // get flow filename
if (substr($filename,0,1)=="/") return $filename; // return mac/linux absolute filename directly
if (substr($filename,1,1)==":") return str_replace("\\","/",$filename); // return windows absolute filename directly
$flow_path = str_replace("\\","/",dirname($flow_script)); // otherwise use flow script path to build absolute filename
// above str_replace is because casperjs/phantomjs do not seem to support \ for windows paths, replace with / to work
if (strpos($flow_path,"/")!==false) return $flow_path . '/' . $filename; else return $flow_path . '\\' . $filename;} 

function beg_tx($locator) { // helper function to return beginning string for handling locators
if ($GLOBALS['inside_loop'] == 0)
return "\ncasper.waitFor(function check() {return check_tx('".$locator."');},\nfunction then() {"; else return "\n";}

function end_tx($locator) { // helper function to return ending string for handling locators
if ($GLOBALS['inside_loop'] == 0)
return "},\nfunction timeout() {this.echo('ERROR - cannot find ".
$locator."').exit();});}".end_fi()."});\n\ncasper.then(function() {\n";
else {$GLOBALS['inside_loop'] = 0; return "}});\n\ncasper.then(function() {\n";}}

function end_fi() { // helper function to end frame_intent by closing parsed step block
if ($GLOBALS['inside_frame'] == 1) {$GLOBALS['inside_frame']=0; return " });} ";}
else if ($GLOBALS['inside_frame'] == 2) {$GLOBALS['inside_frame']=0; return " });});} ";}}

function add_concat($source_string) { // parse string and add missing + concatenator 
if ((strpos($source_string,"'")!==false) and (strpos($source_string,"\"")!==false))
{echo "ERROR - " . current_line() . " inconsistent quotes in " . $source_string . "\n";}
else if (strpos($source_string,"'")!==false) $quote_type = "'"; // derive quote type used
else if (strpos($source_string,"\"")!==false) $quote_type = "\""; else $quote_type = "none";
$within_quote = false; $source_string = trim($source_string); // trim for future proof
for ($srcpos=0; $srcpos<strlen($source_string); $srcpos++){
if ($source_string[$srcpos] == $quote_type) $within_quote = !$within_quote; 
if (($within_quote == false) and ($source_string[$srcpos]==" ")) $source_string[$srcpos] = "+";}
$source_string = str_replace("+++++","+",$source_string); $source_string = str_replace("++++","+",$source_string);
$source_string = str_replace("+++","+",$source_string); $source_string = str_replace("++","+",$source_string);
return $source_string;} // replacing multiple variations of + to handle user typos of double spaces etc 

// set of functions to interpret steps into corresponding casperjs code
function url_intent($raw_intent) {
if (filter_var($raw_intent, FILTER_VALIDATE_URL) == false) 
echo "ERROR - " . current_line() . " invalid URL " . $raw_intent . "\n"; else
if ($GLOBALS['line_number'] == 1) {$GLOBALS['url_provided'] = true; return "casper.start('".$raw_intent.
"', function() {\ntecho('".$raw_intent."' + ' - ' + this.getTitle() + '\\n');});\n\ncasper.then(function() {\n";}
else return "});casper.thenOpen('".$raw_intent."', function() {\ntecho('".
$raw_intent."' + ' - ' + this.getTitle());});\n\ncasper.then(function() {\n";}

function tap_intent($raw_intent) {
$params = trim(substr($raw_intent." ",1+strpos($raw_intent." "," "))); 
if ($params == "") echo "ERROR - " . current_line() . " target missing for " . $raw_intent . "\n"; else
return "{techo('".$raw_intent."');".beg_tx($params)."this.click(tx('" . $params . "'));".end_tx($params);}

function hover_intent($raw_intent) {
$params = trim(substr($raw_intent." ",1+strpos($raw_intent." "," "))); 
if ($params == "") echo "ERROR - " . current_line() . " target missing for " . $raw_intent . "\n"; else
return "{techo('".$raw_intent."');".beg_tx($params)."this.mouse.move(tx('" . $params . "'));".end_tx($params);}

function type_intent($raw_intent) {
$params = trim(substr($raw_intent." ",1+strpos($raw_intent." "," ")));
$param1 = trim(substr($params,0,strpos($params," as "))); $param2 = trim(substr($params,4+strpos($params," as ")));
if (($param1 == "") or ($param2 == "")) 
echo "ERROR - " . current_line() . " target/text missing for " . $raw_intent . "\n"; else 
return "{techo('".$raw_intent."');".beg_tx($param1)."this.sendKeys(tx('".$param1."'),'".$param2."');".end_tx($param1);}

function select_intent($raw_intent) {
$params = trim(substr($raw_intent." ",1+strpos($raw_intent." "," ")));
$param1 = trim(substr($params,0,strpos($params," as "))); $param2 = trim(substr($params,4+strpos($params," as ")));
if (($param1 == "") or ($param2 == ""))
echo "ERROR - " . current_line() . " target/option missing for " . $raw_intent . "\n"; else
return "{techo('".$raw_intent."');".beg_tx($param1)."var select_locator = tx('".$param1."');\n".
"if (is_xpath_selector(select_locator.toString().replace('xpath selector: ','')))\n".
"select_locator = select_locator.toString().substring(16);\n".
"this.selectOptionByValue(select_locator,'".$param2."');".end_tx($param1);}

function read_intent($raw_intent) {
$params = trim(substr($raw_intent." ",1+strpos($raw_intent." "," ")));
$param1 = trim(substr($params,0,strpos($params," to "))); $param2 = trim(substr($params,4+strpos($params," to ")));
if ((strtolower($param1) == "page") and ($param2 != ""))
return "{techo('".$raw_intent."');\n".$param2." = this.getHTML();}".end_fi()."\n";
if (($param1 == "") or ($param2 == "")) 
echo "ERROR - " . current_line() . " target/variable missing for " . $raw_intent . "\n"; else
return "{techo('".$raw_intent."');".beg_tx($param1).$param2." = this.fetchText(tx('".$param1."')).trim();".end_tx($param1);}

function show_intent($raw_intent) {
$params = trim(substr($raw_intent." ",1+strpos($raw_intent." "," ")));
if (strtolower($params) == "page") return "this.echo(this.getHTML());".end_fi()."\n";
if ($params == "") echo "ERROR - " . current_line() . " target missing for " . $raw_intent . "\n"; else
return "{// nothing to do on this line".beg_tx($params).
"this.echo('".$raw_intent."' + ' - ' + this.fetchText(tx('" . $params . "')).trim());".end_tx($params);}

function down_intent($raw_intent) {
$params = trim(substr($raw_intent." ",1+strpos($raw_intent." "," ")));
$param1 = trim(substr($params,0,strpos($params," to "))); $param2 = trim(substr($params,4+strpos($params," to ")));
if (($param1 == "") or ($param2 == "")) 
echo "ERROR - " . current_line() . " url/filename missing for " . $raw_intent . "\n"; else
return "{techo('".$raw_intent."');\nthis.download('".$param1."','".abs_file($param2)."');}".end_fi()."\n";}

function receive_intent($raw_intent) {
$params = trim(substr($raw_intent." ",1+strpos($raw_intent." "," ")));
$param1 = trim(substr($params,0,strpos($params," to "))); $param2 = trim(substr($params,4+strpos($params," to ")));
if (($param1 == "") or ($param2 == "")) 
echo "ERROR - " . current_line() . " resource/filename missing for " . $raw_intent . "\n"; else
return "{techo('".$raw_intent."');\n".
"casper.on('resource.received', function(resource) {if (resource.stage !== 'end') return;\n".
"if (resource.url.indexOf('".$param1."') > -1) this.download(resource.url, '".abs_file($param2)."');});}".end_fi()."\n";}

function echo_intent($raw_intent) {
$params = trim(substr($raw_intent." ",1+strpos($raw_intent." "," ")));
if ($params == "") echo "ERROR - " . current_line() . " text missing for " . $raw_intent . "\n"; else 
return "this.echo(".add_concat($params).");".end_fi()."\n";}

function save_intent($raw_intent) {
$params = trim(substr($raw_intent." ",1+strpos($raw_intent." "," ")));
$param1 = trim(substr($params,0,strpos($params," to "))); $param2 = trim(substr($params,4+strpos($params," to ")));
if ((strtolower($params) == "page") or (strtolower($param1) == "page")) {if (strpos($params," to ")!==false)
return "{techo('".$raw_intent."');\nsave_text('".abs_file($param2)."',this.getHTML());}".end_fi()."\n";
else return "{techo('".$raw_intent."');\nsave_text('',this.getHTML());}".end_fi()."\n";}
if ($params == "") echo "ERROR - " . current_line() . " target missing for " . $raw_intent . "\n"; 
else if (strpos($params," to ")!==false)
return "{techo('".$raw_intent."');".beg_tx($param1).
	"save_text('".abs_file($param2)."',this.fetchText(tx('".$param1."')).trim());".end_tx($param1); else
return "{techo('".$raw_intent."');".beg_tx($params).
	"save_text('',this.fetchText(tx('" . $params . "')).trim());".end_tx($params);}

function dump_intent($raw_intent) {
$raw_intent = str_replace("'","\"",$raw_intent); // avoid breaking echo below when single quote is used
$params = trim(substr($raw_intent." ",1+strpos($raw_intent." "," ")));
$param1 = trim(substr($params,0,strpos($params," to "))); $param2 = trim(substr($params,4+strpos($params," to ")));
if ($params == "") echo "ERROR - " . current_line() . " variable missing for " . $raw_intent . "\n"; 
else if (strpos($params," to ")!==false)
return "{techo('".$raw_intent."');\nsave_text('".abs_file($param2)."',".add_concat($param1).");}".end_fi()."\n";
else return "{techo('".$raw_intent."');\nsave_text(''," . add_concat($params) . ");}".end_fi()."\n";}

function snap_intent($raw_intent) {
$params = trim(substr($raw_intent." ",1+strpos($raw_intent." "," ")));
$param1 = trim(substr($params,0,strpos($params," to "))); $param2 = trim(substr($params,4+strpos($params," to ")));
if ((strtolower($params) == "page") or (strtolower($param1) == "page")) {if (strpos($params," to ")!==false)
return "{techo('".$raw_intent."');\nthis.capture('".abs_file($param2)."');}".end_fi()."\n";
else return "{techo('".$raw_intent."');\nthis.capture(snap_image());}".end_fi()."\n";}
if ($params == "") echo "ERROR - " . current_line() . " target missing for " . $raw_intent . "\n"; 
else if (strpos($params," to ")!==false)
return "{techo('".$raw_intent."');".beg_tx($param1).
	"this.captureSelector('".abs_file($param2)."',tx('".$param1."'));".end_tx($param1); else
return "{techo('".$raw_intent."');".beg_tx($params).
	"this.captureSelector(snap_image(),tx('".$params."'));".end_tx($params);}

function wait_intent($raw_intent) { // wait is a new block, invalid to use after frame, thus skip end_fi()
$params = trim(substr($raw_intent." ",1+strpos($raw_intent." "," "))); if ($params == "") $params = "5"; 
if ($GLOBALS['inside_frame']!=0) echo "ERROR - " . current_line() . " invalid after frame - " . $raw_intent . "\n"; else
return "techo('".$raw_intent."');});\n\ncasper.wait(" . (floatval($params)*1000) . ", function() {\n";}

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
echo "ERROR - " . current_line() . " if/true/false missing for " . $raw_intent . "\n"; else
return "{".parse_condition("if ".$param1)."\nthis.echo(".$param2.");\nelse this.echo(".$param3.");}".end_fi()."\n";}

function test_intent($raw_intent) {
echo "ERROR - " . current_line() . " use CasperJS tester module to professionally " . $raw_intent . "\n";
echo "ERROR - " . current_line() . " info at http://docs.casperjs.org/en/latest/modules/tester.html" . "\n";
echo "ERROR - " . current_line() . " support CSS selector or tx('selector') for XPath algo by TA.Gui" . "\n";}

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

function api_intent($raw_intent) {
$params = trim(substr($raw_intent." ",1+strpos($raw_intent." "," ")));
if ($params == "") echo "ERROR - " . current_line() . " API URL missing for " . $raw_intent . "\n"; else
return "{techo('".$raw_intent."');\nthis.echo(call_api('".$params."'));}".end_fi()."\n";}

function js_intent($raw_intent) {
$params = trim(substr($raw_intent." ",1+strpos($raw_intent." "," ")));
if ($params == "") echo "ERROR - " . current_line() . " statement missing for " . $raw_intent . "\n";
else return $params.end_fi()."\n";}

function code_intent($raw_intent) {
$params = parse_condition($raw_intent); return $params.end_fi()."\n";}

function parse_condition($logic) { // natural language handling for conditions 
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
else {echo "ERROR - " . current_line() . " no quoted text - " . $logic . "\n"; $quote_type = "missing";}
if ($quote_type != "missing"){$pos_quote_end = strpos($logic,$quote_type,$pos_quote_start+1);
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
$logic = str_replace("(","",$logic); $logic = str_replace(")","",$logic); // remove brackets if present
$logic = str_replace("   "," ",$logic); $logic = str_replace("  "," ",$logic); // remove typo extra spaces
$token = explode(" ",$logic); // split into tokens for loop in natural language, eg - for cupcake from 1 to 4
if (count($token)!= 6) echo "ERROR - " . current_line() . " invalid for loop - " . $logic . "\n";
else $logic = $token[0]." (".$token[1]."=".$token[3]."; ".$token[1]."<=".$token[5]."; ".$token[1]."++)";}

// add opening and closing brackets twice to handle no brackets, and, or cases
if (substr($logic,0,3)=="if ") $logic = "if ((" . trim(substr($logic,3)) . "))";
if (substr($logic,0,8)=="else if ") $logic = "else if ((" . trim(substr($logic,8)) . "))";
if (substr($logic,0,6)=="while ") $logic = "while ((" . trim(substr($logic,6)) . "))";}

// track if next statement is inside a loop, then avoid async wait (will hang casperjs/phantomjs)   
if ((substr($logic,0,4)=="for ") or (substr($logic,0,6)=="while ")) $GLOBALS['inside_loop'] = 1; 
return $logic;}

?>
