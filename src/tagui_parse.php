<?php

/* PARSER SCRIPT FOR TA.GUI FRAMEWORK ~ TEBEL.SG */

// check flow filename for .gui or .txt or no extension
$script = $argv[1]; if ($script=="") die("ERROR - specify flow filename as first parameter\n"); if (strpos($script, '.') !== false)
if ((pathinfo($script, PATHINFO_EXTENSION)!="gui") and (pathinfo($script, PATHINFO_EXTENSION)!="txt"))
die("ERROR - use .gui or .txt or no extension for flow filename\n");

// make sure required files are available and can be opened
if (!file_exists($script)) die("ERROR - cannot find " . $script . "\n");
$input_file = fopen($script,'r') or die("ERROR - cannot open " . $script . "\n");
$output_file = fopen($script . '.js','w') or die("ERROR - cannot open " . $script . '.js' . "\n");
$header_file = fopen('tagui_header.js','r') or die("ERROR - cannot open tagui_header.js" . "\n");
$footer_file = fopen('tagui_footer.js','r') or die("ERROR - cannot open tagui_footer.js" . "\n");
$inside_frame = 0; $line_number = 0; $url_provided = false; // to detect if url is provided in user-script

// loops to create casperjs script from header, user-script, footer files
while(!feof($header_file)) {fwrite($output_file,fgets($header_file));} fclose($header_file);
while(!feof($input_file)) {fwrite($output_file,parse_intent(fgets($input_file)));} fclose($input_file);
while(!feof($footer_file)) {fwrite($output_file,fgets($footer_file));} fclose($footer_file); fclose($output_file);
chmod ($script . '.js',0600); if (!$url_provided) echo "ERROR - [OTHERS] first line of " . $script . " not URL\n";

function current_line() {return "[LINE " . $GLOBALS['line_number'] . "]";}
function parse_intent($script_line) {$GLOBALS['line_number']++;
$script_line = trim($script_line); if ($script_line=="") return "";

// check intent of step for interpretation into casperjs code
switch (get_intent($script_line)) {
case "url": return url_intent($script_line); break;
case "tap": return tap_intent($script_line); break;
case "hover": return hover_intent($script_line); break;
case "type": return type_intent($script_line); break;
case "read": return read_intent($script_line); break;
case "show": return show_intent($script_line); break;
case "down": return down_intent($script_line); break;
case "file": return file_intent($script_line); break;
case "echo": return echo_intent($script_line); break;
case "save": return save_intent($script_line); break;
case "dump": return dump_intent($script_line); break;
case "snap": return snap_intent($script_line); break;
case "wait": return wait_intent($script_line); break;
case "test": return test_intent($script_line); break;
case "frame": return frame_intent($script_line); break;
case "js": return js_intent($script_line); break;
case "code": return $script_line . "\n"; break;
default: echo "ERROR - " . current_line() . " cannot understand step " . $script_line . "\n";}}

function get_intent($raw_intent) {
if ((strtolower(substr($raw_intent,0,7))=="http://") or (strtolower(substr($raw_intent,0,8))=="https://")) return "url";

// first set of conditions check for valid keywords with their parameters
else if ((strtolower(substr($raw_intent,0,4))=="tap ") or (strtolower(substr($raw_intent,0,6))=="click ")) return "tap"; 
else if ((strtolower(substr($raw_intent,0,6))=="hover ")or(strtolower(substr($raw_intent,0,5))=="move ")) return "hover";
else if ((strtolower(substr($raw_intent,0,5))=="type ") or (strtolower(substr($raw_intent,0,6))=="enter ")) return "type";
else if ((strtolower(substr($raw_intent,0,5))=="read ") or (strtolower(substr($raw_intent,0,6))=="fetch ")) return "read";
else if ((strtolower(substr($raw_intent,0,5))=="show ") or (strtolower(substr($raw_intent,0,6))=="print ")) return "show";
else if ((strtolower(substr($raw_intent,0,5))=="down ") or (strtolower(substr($raw_intent,4,5))=="load ")) return "down";
else if (strtolower(substr($raw_intent,0,5))=="file ") return "file";
else if (strtolower(substr($raw_intent,0,5))=="echo ") return "echo";
else if (strtolower(substr($raw_intent,0,5))=="save ") return "save";
else if (strtolower(substr($raw_intent,0,5))=="dump ") return "dump";
else if (strtolower(substr($raw_intent,0,5))=="snap ") return "snap";
else if (strtolower(substr($raw_intent,0,5))=="wait ") return "wait";
else if (strtolower(substr($raw_intent,0,5))=="test ") return "test";
else if (strtolower(substr($raw_intent,0,6))=="frame ") return "frame";
else if (strtolower(substr($raw_intent,0,3))=="js ") return "js";

// second set of conditions check for valid keywords with missing parameters
else if ((strtolower($raw_intent)=="tap") or (strtolower($raw_intent)=="click")) return "tap";
else if ((strtolower($raw_intent)=="hover") or (strtolower($raw_intent)=="move")) return "hover";
else if ((strtolower($raw_intent)=="type") or (strtolower($raw_intent)=="enter")) return "type";
else if ((strtolower($raw_intent)=="read") or (strtolower($raw_intent)=="fetch")) return "read";
else if ((strtolower($raw_intent)=="show") or (strtolower($raw_intent)=="print")) return "show";
else if ((strtolower($raw_intent)=="down") or (strtolower($raw_intent)=="download")) return "down";
else if (strtolower($raw_intent)=="file") return "file";
else if (strtolower($raw_intent)=="echo") return "echo";
else if (strtolower($raw_intent)=="save") return "save";
else if (strtolower($raw_intent)=="dump") return "dump";
else if (strtolower($raw_intent)=="snap") return "snap";
else if (strtolower($raw_intent)=="wait") return "wait";
else if (strtolower($raw_intent)=="test") return "test";
else if (strtolower($raw_intent)=="frame") return "frame";
else if (strtolower($raw_intent)=="js") return "js";

// final check for recognized code before returning error 
else if (is_code($raw_intent)) return "code"; else return "error";}

function is_code($raw_intent) {
// due to asynchronous waiting for element, if/for/while can work for parsing single step
// other scenarios can be assumed to behave as unparsed javascript in casperjs context
if ((substr($raw_intent,0,4)=="var ") or (substr($raw_intent,0,3)=="do ")) return true;
if ((substr($raw_intent,0,1)=="{") or (substr($raw_intent,0,1)=="}")) return true;
if ((substr($raw_intent,0,3)=="if ") or (substr($raw_intent,0,5)=="else ")) return true;
if ((substr($raw_intent,0,4)=="for ") or (substr($raw_intent,0,6)=="while ")) return true;
if ((substr($raw_intent,0,7)=="switch ") or (substr($raw_intent,0,5)=="case ")) return true;
if ((substr($raw_intent,0,6)=="break;") or (substr($raw_intent,0,9)=="function ")) return true;
if ((substr($raw_intent,0,7)=="casper.") or (substr($raw_intent,0,5)=="this.")) return true;
if ((substr($raw_intent,0,2)=="//") or (substr($raw_intent,-1)==";")) return true; return false;}

function beg_tx($locator) { // helper function to return beginning string for handling locators
return "\ncasper.waitFor(function check() {return check_tx('".$locator."');},\nfunction then() {";}

function end_tx($locator) { // helper function to return ending string for handling locators
return "},\nfunction timeout() {this.echo('ERROR - cannot find ".
$locator."').exit();});}".end_fi()."});\n\ncasper.then(function() {\n";}

function end_fi() { // helper function to end frame_intent by closing parsed step block
if ($GLOBALS['inside_frame'] == 1) {$GLOBALS['inside_frame']=0; return " });} ";}
else if ($GLOBALS['inside_frame'] == 2) {$GLOBALS['inside_frame']=0; return " });});} ";}}

// set of functions to interpret steps into corresponding casperjs code
function url_intent($raw_intent) {
if (filter_var($raw_intent, FILTER_VALIDATE_URL) == false) 
echo "ERROR - " . current_line() . " invalid URL " . $raw_intent . "\n"; else
if ($GLOBALS['line_number'] == 1) {$GLOBALS['url_provided'] = true; return "casper.start('".$raw_intent.
"', function() {\nthis.echo('".$raw_intent."' + ' - ' + this.getTitle() + '\\n');});\n\ncasper.then(function() {\n";}
else return "});casper.thenOpen('".$raw_intent."', function() {\nthis.echo('".
$raw_intent."' + ' - ' + this.getTitle());});\n\ncasper.then(function() {\n";}

function tap_intent($raw_intent) {
$params = trim(substr($raw_intent." ",1+strpos($raw_intent." "," "))); 
if ($params == "") echo "ERROR - " . current_line() . " target missing for " . $raw_intent . "\n"; else
return "{this.echo('".$raw_intent."');".beg_tx($params)."this.click(tx('" . $params . "'));".end_tx($params);}

function hover_intent($raw_intent) {
$params = trim(substr($raw_intent." ",1+strpos($raw_intent." "," "))); 
if ($params == "") echo "ERROR - " . current_line() . " target missing for " . $raw_intent . "\n"; else
return "{this.echo('".$raw_intent."');".beg_tx($params)."this.mouse.move(tx('" . $params . "'));".end_tx($params);}

function type_intent($raw_intent) {
$params = trim(substr($raw_intent." ",1+strpos($raw_intent." "," ")));
$param1 = trim(substr($params,0,strpos($params,"|"))); $param2 = trim(substr($params,1+strpos($params,"|")));
if (($param1 == "") or ($param2 == "")) 
echo "ERROR - " . current_line() . " target/text missing for " . $raw_intent . "\n"; else 
return "{this.echo('".$raw_intent."');".beg_tx($param1)."this.sendKeys(tx('".$param1."'),'".$param2."');".end_tx($param1);}

function read_intent($raw_intent) {
$params = trim(substr($raw_intent." ",1+strpos($raw_intent." "," ")));
$param1 = trim(substr($params,0,strpos($params,"|"))); $param2 = trim(substr($params,1+strpos($params,"|")));
if (($param1 == "") or ($param2 == "")) 
echo "ERROR - " . current_line() . " target/variable missing for " . $raw_intent . "\n"; else
return "{this.echo('".$raw_intent."');".beg_tx($param1).$param2." = this.fetchText(tx('".$param1."'));".end_tx($param1);}

function show_intent($raw_intent) {
$params = trim(substr($raw_intent." ",1+strpos($raw_intent." "," ")));
if ($params == "") echo "ERROR - " . current_line() . " target missing for " . $raw_intent . "\n"; else
return "{// nothing to do on this line".beg_tx($params).
"this.echo('".$raw_intent."' + ' ' + this.fetchText(tx('" . $params . "')));".end_tx($params);}

function down_intent($raw_intent) {
$params = trim(substr($raw_intent." ",1+strpos($raw_intent." "," ")));
$param1 = trim(substr($params,0,strpos($params,"|"))); $param2 = trim(substr($params,1+strpos($params,"|")));
if (($param1 == "") or ($param2 == "")) 
echo "ERROR - " . current_line() . " url/filename missing for " . $raw_intent . "\n"; else
return "{this.echo('".$raw_intent."');\nthis.download('".$param1."','".$param2."');}".end_fi()."\n";}

function file_intent($raw_intent) {
$params = trim(substr($raw_intent." ",1+strpos($raw_intent." "," ")));
$param1 = trim(substr($params,0,strpos($params,"|"))); $param2 = trim(substr($params,1+strpos($params,"|")));
if (($param1 == "") or ($param2 == "")) 
echo "ERROR - " . current_line() . " source/target missing for " . $raw_intent . "\n"; else
return "{this.echo('".$raw_intent."');\n".
"casper.on('resource.received', function(resource) {if (resource.stage !== 'end') return;\n".
"if (resource.url.indexOf('".$param1."') > -1) this.download(resource.url, '".$param2."');});}".end_fi()."\n";}

function echo_intent($raw_intent) {
$params = trim(substr($raw_intent." ",1+strpos($raw_intent." "," ")));
if ($params == "") echo "ERROR - " . current_line() . " text missing for " . $raw_intent . "\n"; else 
return "this.echo(".$params.");".end_fi()."\n";}

function save_intent($raw_intent) {
$params = trim(substr($raw_intent." ",1+strpos($raw_intent." "," ")));
$param1 = trim(substr($params,0,strpos($params,"|"))); $param2 = trim(substr($params,1+strpos($params,"|")));
if ($params == "") echo "ERROR - " . current_line() . " target missing for " . $raw_intent . "\n"; 
else if (strpos($params,"|")!==false)
return "{this.echo('".$raw_intent."');".beg_tx($param1).
	"save_text('".$param2."',this.fetchText(tx('".$param1."')));".end_tx($param1); else
return "{this.echo('".$raw_intent."');".beg_tx($params).
	"save_text('',this.fetchText(tx('" . $params . "')));".end_tx($params);}

function dump_intent($raw_intent) {
$params = trim(substr($raw_intent." ",1+strpos($raw_intent." "," ")));
$param1 = trim(substr($params,0,strpos($params,"|"))); $param2 = trim(substr($params,1+strpos($params,"|")));
if ($params == "") echo "ERROR - " . current_line() . " variable missing for " . $raw_intent . "\n"; 
else if (strpos($params,"|")!==false)
return "{this.echo('".$raw_intent."');\nsave_text('".$param2."',".$param1.");}".end_fi()."\n";
else return "{this.echo('".$raw_intent."');\nsave_text(''," . $params . ");}".end_fi()."\n";}

function snap_intent($raw_intent) {
$params = trim(substr($raw_intent." ",1+strpos($raw_intent." "," ")));
$param1 = trim(substr($params,0,strpos($params,"|"))); $param2 = trim(substr($params,1+strpos($params,"|")));
if ((strtolower($params) == "page") or (strtolower($param1) == "page")) {if (strpos($params,"|")!==false)
return "{this.echo('".$raw_intent."');\nthis.capture('".$param2."');}".end_fi()."\n";
else return "{this.echo('".$raw_intent."');\nthis.capture(snap_image());}".end_fi()."\n";}
if ($params == "") echo "ERROR - " . current_line() . " target missing for " . $raw_intent . "\n"; 
else if (strpos($params,"|")!==false)
return "{this.echo('".$raw_intent."');".beg_tx($param1).
	"this.captureSelector('".$param2."',tx('".$param1."'));".end_tx($param1); else
return "{this.echo('".$raw_intent."');".beg_tx($params).
	"this.captureSelector(snap_image(),tx('".$params."'));".end_tx($params);}

function wait_intent($raw_intent) {
$params = trim(substr($raw_intent." ",1+strpos($raw_intent." "," ")));
if ($params == "") echo "ERROR - " . current_line() . " duration missing for " . $raw_intent . "\n"; else 
return "this.echo('".$raw_intent."');});\n\ncasper.wait(" . $params . ", function() {\n";}

function test_intent($raw_intent) {
$params = trim(substr($raw_intent." ",1+strpos($raw_intent." "," ")));
$params = str_replace("||"," JAVASCRIPT_OR ",$params); // to handle conflict with "|" delimiter 
$param1 = trim(substr($params,0,strpos($params,"|"))); $param2 = trim(substr($params,1+strpos($params,"|")));
$param3 = trim(substr($param2,1+strpos($param2,"|"))); $param2 = trim(substr($param2,0,strpos($param2,"|")));
$param1 = str_replace(" JAVASCRIPT_OR ","||",$param1); // to restore back "||" that were replaced
$param2 = str_replace(" JAVASCRIPT_OR ","||",$param2); $param3 = str_replace(" JAVASCRIPT_OR ","||",$param3);
if (substr_count($params,"|")!=2) 
echo "ERROR - " . current_line() . " if/true/false missing for " . $raw_intent . "\n"; else
return "{if (".$param1.")\nthis.echo(".$param2.");\nelse this.echo(".$param3.");}".end_fi()."\n";}

function frame_intent($raw_intent) {
if ($GLOBALS['inside_frame'] != 0) 
{echo "ERROR - " . current_line() . " frame called consecutively " . $raw_intent . "\n"; return;}
$params = trim(substr($raw_intent." ",1+strpos($raw_intent." "," ")));
$param1 = trim(substr($params,0,strpos($params,"|"))); $param2 = trim(substr($params,1+strpos($params,"|")));
if ($params == "") echo "ERROR - " . current_line() . " name missing for " . $raw_intent . "\n"; 
else if (strpos($params,"|")!==false) 
{$GLOBALS['inside_frame']=2; return "{this.echo('".$raw_intent."');\ncasper.withFrame('".
$param1."', function() {casper.withFrame('".$param2."', function() {\n";} else
{$GLOBALS['inside_frame']=1; return "{this.echo('".$raw_intent."');\ncasper.withFrame('".$params."', function() {\n";}}

function js_intent($raw_intent) {
$params = trim(substr($raw_intent." ",1+strpos($raw_intent." "," ")));
if ($params == "") echo "ERROR - " . current_line() . " statement missing for " . $raw_intent . "\n";
else return $params.end_fi()."\n";}

?>
