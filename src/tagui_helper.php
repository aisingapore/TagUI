<?php
 
/* HELPER PARSER SCRIPT FOR TAGUI FRAMEWORK ~ TEBEL.ORG */

// configure directory to look for automation flow files - default location is set to tagui/flows
// avoid spaces in folder and filename, issue with CasperJS and TagUI can't tell where options start
$helper_flow_directory = "../flows";

// concatenate all parameters into one string for processing
$raw_intent = $argv[1] . " " . $argv[2] . " " . $argv[3] . " " . $argv[4] . " " .
$argv[5] . " " . $argv[6] . " " . $argv[7] . " " . $argv[8] . " " . $argv[9];
$raw_intent = trim($raw_intent);

// initialise components required for forming the final output command
$helper_flow_context = ""; $helper_flow_action = ""; $helper_flow_filename = "";
$helper_flow_options = ""; $helper_flow_browser = ""; $helper_flow_found = false; 

// remove filler words that have limited impact on meaning of intention
$raw_intent = str_replace(" is "," ",$raw_intent); $raw_intent = str_replace(" are "," ",$raw_intent);
$raw_intent = str_replace(" was "," ",$raw_intent); $raw_intent = str_replace(" were "," ",$raw_intent);
$raw_intent = str_replace(" my "," ",$raw_intent); $raw_intent = str_replace(" me "," ",$raw_intent);
$raw_intent = trim($raw_intent);

// check for using browser string at end of intention to pass browser type option
// important to concatenate at end of options in order to not disrupt p1-p9 parameters
if ((strpos($raw_intent,"using chrome")!==false) and 
(strrpos($raw_intent,"using chrome") == (strlen($raw_intent) - strlen("using chrome"))))
{$helper_flow_browser = " chrome"; $raw_intent = substr($raw_intent,0,strrpos($raw_intent,"using chrome"));}
else if ((strpos($raw_intent,"using headless")!==false) and 
(strrpos($raw_intent,"using headless") == (strlen($raw_intent) - strlen("using headless"))))
{$helper_flow_browser = " headless"; $raw_intent = substr($raw_intent,0,strrpos($raw_intent,"using headless"));}
else if ((strpos($raw_intent,"using firefox")!==false) and 
(strrpos($raw_intent,"using firefox") == (strlen($raw_intent) - strlen("using firefox"))))
{$helper_flow_browser = " firefox"; $raw_intent = substr($raw_intent,0,strrpos($raw_intent,"using firefox"));}
else if ((strpos($raw_intent,"with chrome")!==false) and 
(strrpos($raw_intent,"with chrome") == (strlen($raw_intent) - strlen("with chrome"))))
{$helper_flow_browser = " chrome"; $raw_intent = substr($raw_intent,0,strrpos($raw_intent,"with chrome"));}
else if ((strpos($raw_intent,"with headless")!==false) and 
(strrpos($raw_intent,"with headless") == (strlen($raw_intent) - strlen("with headless"))))
{$helper_flow_browser = " headless"; $raw_intent = substr($raw_intent,0,strrpos($raw_intent,"with headless"));}
else if ((strpos($raw_intent,"with firefox")!==false) and 
(strrpos($raw_intent,"with firefox") == (strlen($raw_intent) - strlen("with firefox"))))
{$helper_flow_browser = " firefox"; $raw_intent = substr($raw_intent,0,strrpos($raw_intent,"with firefox"));}
$raw_intent = trim($raw_intent);

// throw error message if there are no minimum of an action + 1-word context
if (strpos($raw_intent," ")==false) die("cannot find action and context in your instruction" . "\n");

// loop through intention after filtering away fillers and browser option
$token = explode(" ",$raw_intent); $helper_flow_action = $token[0]; // split into tokens separated by space
for ($count = 1; $count < sizeof($token); $count++) {$helper_flow_context = ""; $helper_flow_options = "";
for ($context_count = 1 + $count; $context_count <= sizeof($token); $context_count++) {
$helper_flow_context .= $token[$context_count - 1] . " ";}
$helper_flow_context = str_replace(" ","_",trim($helper_flow_context));
for ($options_count = 1 ; $options_count < $count; $options_count++) {
$helper_flow_options .= $token[$options_count] . " ";}
$helper_flow_options = trim($helper_flow_options);
$helper_flow_filename = $helper_flow_directory . "/" . trim($helper_flow_action . "_" . $helper_flow_context);
// echo "./tagui " . $helper_flow_filename . " quiet " . trim($helper_flow_options . $helper_flow_browser) . "\n";
if (file_exists($helper_flow_filename)) {$helper_flow_found = true; break;}}

// generate flow options taking into account browser to be used
$helper_flow_options = trim($helper_flow_options . $helper_flow_browser);

// throw error message if a match for flow filename is not found
if (!$helper_flow_found) die("cannot find automation to run for your instruction" . "\n");

// write interpreted command and flow to run to output runner
$output_file = fopen('tagui_helper','w') or die("cannot open tagui_helper, raise an issue on TagUI GitHub page" . "\n");
fwrite($output_file,"#!/usr/bin/env bash" . "\n");
fwrite($output_file,"# GENERATED HELPER ASSISTANT SCRIPT FOR RUNNING TAGUI FRAMEWORK ~ TEBEL.ORG #" . "\n");
fwrite($output_file,"./tagui " . $helper_flow_filename . " quiet " . $helper_flow_options . "\n");
fclose($output_file); chmod ('tagui_helper',0700);
$output_file = fopen('tagui_helper.cmd','w') or die("cannot open tagui_helper.cmd, raise an issue on TagUI GitHub page" . "\n");
fwrite($output_file,"@echo off" . "\r\n");
fwrite($output_file,"rem # GENERATED HELPER ASSISTANT SCRIPT FOR RUNNING TAGUI FRAMEWORK ~ TEBEL.ORG #" . "\r\n");
fwrite($output_file,"tagui " . $helper_flow_filename . " quiet " . $helper_flow_options . "\r\n");
fclose($output_file); chmod ('tagui_helper.cmd',0700);

?>
