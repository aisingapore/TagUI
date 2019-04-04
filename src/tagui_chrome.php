<?php

/* CHROME INTERFACE FOR TAGUI FRAMEWORK ~ TEBEL.ORG */
// overall design of this php helper is based on tagui sikuli integration helper

// it uses below project to allow concurrent websocket communications with chrome
// instead of relying on a single javascript thread without await/promise support
// php chosen instead of node.js/python as it does not add additional dependencies
// https://github.com/Textalk/websocket-php

// manually include project files instead of composer dependency (php package manager)
require('ws/Base.php'); require('ws/Client.php'); require('ws/Exception.php'); require('ws/BadOpcodeException.php');
require('ws/BadUriException.php'); require('ws/ConnectionException.php'); use WebSocket\Client; // project namespace

// delay in microseconds between scanning for inputs
$scan_period = 100000;

// counter to track current tagui chrome step
$tagui_count = '0';

if ($argv[1]=="") die("[tagui] ERROR  - specify web socket URL as parameter\n"); 
$client = new Client($argv[1]);

// write to interface out-file to signal ready for inputs
file_put_contents('tagui_chrome.out','[0] START');

// initialise interface in-file before starting main loop
file_put_contents('tagui_chrome.in','');

// main loop to scan inputs from automation flow
echo "[tagui] START  - listening for inputs\n\n"; while (true) {

// scan input from run-time interface in-file
$tagui_intent = trim(file_get_contents('tagui_chrome.in'));

// quit if finish signal received, initialise and repeat loop if blank
if ($tagui_intent == 'finish') break; else if ($tagui_intent == '')
{$tagui_count = '0'; file_put_contents('tagui_chrome.out','[0] START'); usleep($scan_period); continue;}

// get count and repeat loop if same count as last iteration
$temp_count = trim(substr($tagui_intent,1,strpos($tagui_intent,'] ')-1));
$tagui_intent = trim(substr($tagui_intent,strpos($tagui_intent,'] ')+2));
if ($tagui_count == $temp_count) {usleep($scan_period); continue;} else $tagui_count = $temp_count;

// otherwise send input intent to chrome websocket
echo "[tagui] INPUT  - \n" . "[" . $tagui_count . "] " . $tagui_intent . "\n";
$client->send($tagui_intent); $intent_result_string = ""; // keep reading until chrome replies with message 
while ($intent_result_string == "") {try {$intent_result_string = trim($client->receive());} catch (Exception $e) {}}

// retrieve message a second time for some Target methods as the real message is the second incoming message
if (strpos($tagui_intent,'Target.sendMessageToTarget') !== false) $intent_result_string = trim($client->receive());
else if (strpos($tagui_intent,'Target.attachToTarget') !== false) $intent_result_string = trim($client->receive());
else if (strpos($tagui_intent,'Target.detachFromTarget') !== false) $intent_result_string = trim($client->receive());

// ignore irrelevant DOM.setChildNodes events received when using DOM.querySelector to get NodeId for upload step
// and ignore DOM. events received after using DOM.setFileInputFiles in upload step, before DOM.disable kicks in
while (strpos($intent_result_string,'{"method":"DOM.') !== false) {$intent_result_string = trim($client->receive());}

// save intent_result to interface out-file
echo "[tagui] OUTPUT - \n" . "[" . $tagui_count . "] " . $intent_result_string . "\n\n";
file_put_contents('tagui_chrome.out',"[" . $tagui_count . "] " . $intent_result_string); usleep($scan_period);} 

// write to interface out-file to signal finish listening
echo "[tagui] FINISH - stopped listening\n";
file_put_contents('tagui_chrome.out','[0] FINISH');

?>
