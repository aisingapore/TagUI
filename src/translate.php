<?php

/* MULTI-LANGUAGE TRANSLATION SCRIPT FOR TAGUI FRAMEWORK ~ TEBEL.ORG */

// Q1. Why is formatting for this file so messed up? - it's created on the road
// If you want to know more - https://github.com/kelaberetiv/TagUI/issues/490

// Q2. Is there a beautified version for easier viewing or editing?
// Use this - https://loilo.github.io/prettier-php-playground

// english is used as reference language, define keywords for contextual translation

// list of keywords that are supposed to be at the start of a flow statement
$start_keywords = '|click|rclick|dclick|tap|move|hover|type|enter|select|choose|read|fetch|show|print|save|echo|dump|write|snap|ask|table|mouse|keyboard|';
$start_keywords.='wait|live|download|upload|load|receive|frame|popup|timeout|api|dom|js|vision|else if|else|if|for|while|check|';

// list of keywords at start of flow statement for valid to and as separators
$to_separator_keywords = '|read|fetch|save|load|dump|write|snap|table|download|receive|for|';
$as_separator_keywords = '|type|enter|select|choose|upload|';

// list of keywords that are supposed to happen specifically after for loop
$forloop_keywords = '|from|';

// list of keywords at start of flow statement for valid conditions
$start_conditions_keywords = '|else if|if|for|while|check|';

// list of keywords that are supposed to happen in conditions (after - else if, if, for, while, check)
$conditions_keywords = '|more than or equals to|more than or equal to|greater than or equals to|greater than or equal to|higher than or equals to|higher than or equal to|less than or equals to|less than or equal to|lesser than or equals to|lesser than or equal to|lower than or equals to|lower than or equal to|more than|greater than|higher than|less than|lesser than|lower than|not equals to|not equal to|equals to|equal to|not contains|not contain|contains|contain|and|or|';

// list of helper functions that are supposed to happen in conditions, some steps, or assignments
$helper_keywords = '|title()|url()|text()|timer()|count()|present()|visible()|mouse_xy()|mouse_x()|mouse_y()|';
$start_helper_keywords = '|echo|dump|write|'; // other steps not relevant / safe to include

// list of seconds keywords that are supposed to happen after wait and timeout steps
$seconds_keywords = '|seconds|second|'; $start_seconds_keywords = '|wait|timeout|';

// experimental entry point
$source_flow = $argv[1]; if ($source_flow=="") die("ERROR - specify flow filename as first parameter\n");
$direction = $argv[2]; if ($direction=="") die("ERROR - specify to or from as second parameter\n");
$language = $argv[3]; if ($language=="") die("ERROR - specify language as third parameter\n");

// set language definition array columns base on direction of translation
$direction = strtolower($direction);
if (($direction != 'to') and ($direction != 'from')) die("ERROR - specify to or from as second parameter\n");
if ($direction == 'from') {$column_from = 1; $column_to = 0;} else {$column_from = 0; $column_to = 1;}

// load desired language definition file into array for use in translation
$language = strtolower($language); $language_count = 0; if (file_exists('languages/' . $language . '.csv')) {
$language_file = fopen('languages/' . $language . '.csv','r') or die("ERROR - cannot open " . $language . '.csv' . "\n");
while (!feof($language_file)) {$language_data[$language_count] = fgetcsv($language_file);
if (@count((array)$language_data[$language_count]) == 0) die("ERROR - empty row found in " . $language . '.csv' . "\n");
$language_count++;} fclose($language_file); $language_count-=1; // -1 for header
if ($language_data[$language_count][0] == '') $language_count-=1;} // -1 for EOF
else die("ERROR - missing language file " . $language . '.csv' . "\n");

if ($source_flow != 'tagui_parse.php') { // skip processing if internal call
// load automation flow file and perform translation using language definition
$target_flow = $source_flow . '_translated'; // add translated postfix to target flow name
if (!file_exists($source_flow)) die("ERROR - cannot open " . $source_flow . "\n");
$source_file = fopen($source_flow,'r') or die("ERROR - cannot open " . $source_flow . "\n");
$target_file = fopen($target_flow,'w') or die("ERROR - cannot open " . $target_flow . "\n");
while(!feof($source_file)) {fwrite($target_file,translate_intent(fgets($source_file)));}
fclose($source_file); fclose($target_file);}

// log translations to log file for easier checking in case of issues
fclose(fopen('translate.log','w')); chmod('translate.log',0600);

// function to perform translation of automation flow by processing each flow line
function translate_intent($script_line) {if ($script_line == "") return ""; // avoid next line character if none

// track indentation to add back at the end and preserve indentation for conditions etc
$indentation_tracker = str_replace(ltrim($script_line, " \t"),'',$script_line);

// use special padding to reduce mistakes by preventing false replacement when a match happens mid-string
$script_line = '[START_OF_LINE]'.trim($script_line).'[END_OF_LINE]'; // special padding to be removed later
$start_word = '[NOT_ASSIGNED]'; // used for tracking which start keyword the flow statement starts with 

for ($language_check = 1; $language_check <= $GLOBALS['language_count']; $language_check++) {

if (strpos($GLOBALS['start_keywords'],'|'.$GLOBALS['language_data'][$language_check][0].'|')!==false)
{if ($start_word != '[NOT_ASSIGNED]') continue; // skip processing for start keyword if one is already found
$script_line = str_replace('[START_OF_LINE]'.$GLOBALS['language_data'][$language_check][$GLOBALS['column_from']].' ','[START_OF_LINE]'.$GLOBALS['language_data'][$language_check][$GLOBALS['column_to']].' ',$script_line,$replace_count);
if ($replace_count > 0) $start_word = $GLOBALS['language_data'][$language_check][0];
else {$script_line = str_replace('[START_OF_LINE]'.$GLOBALS['language_data'][$language_check][$GLOBALS['column_from']].'[END_OF_LINE]','[START_OF_LINE]'.$GLOBALS['language_data'][$language_check][$GLOBALS['column_to']].'[END_OF_LINE]',$script_line,$replace_count); if ($replace_count > 0) $start_word = $GLOBALS['language_data'][$language_check][0];}}

else if (strpos($GLOBALS['conditions_keywords'],'|'.$GLOBALS['language_data'][$language_check][0].'|')!==false) {
if ($start_word == 'check') {$array_script_line = explode('|',$script_line); $array_script_line[0] = str_replace(' '.$GLOBALS['language_data'][$language_check][$GLOBALS['column_from']].' ',' '.$GLOBALS['language_data'][$language_check][$GLOBALS['column_to']].' ',$array_script_line[0]); $script_line = implode('|',$array_script_line);}
else if (($start_word != '[NOT_ASSIGNED]') and (strpos($GLOBALS['start_conditions_keywords'],'|'.$start_word.'|')!==false)) $script_line = str_replace(' '.$GLOBALS['language_data'][$language_check][$GLOBALS['column_from']].' ',' '.$GLOBALS['language_data'][$language_check][$GLOBALS['column_to']].' ',$script_line);}

else if (strpos($GLOBALS['seconds_keywords'],'|'.$GLOBALS['language_data'][$language_check][0].'|')!==false) {
if (($start_word != '[NOT_ASSIGNED]') and (strpos($GLOBALS['start_seconds_keywords'],'|'.$start_word.'|')!==false))
$script_line = str_replace(' '.$GLOBALS['language_data'][$language_check][$GLOBALS['column_from']].'[END_OF_LINE]',' '.$GLOBALS['language_data'][$language_check][$GLOBALS['column_to']].'[END_OF_LINE]',$script_line);}

else if (strpos($GLOBALS['forloop_keywords'],'|'.$GLOBALS['language_data'][$language_check][0].'|')!==false) {
if ($start_word == 'for') $script_line = str_replace(' '.$GLOBALS['language_data'][$language_check][$GLOBALS['column_from']].' ',' '.$GLOBALS['language_data'][$language_check][$GLOBALS['column_to']].' ',$script_line);}

else if ($GLOBALS['language_data'][$language_check][0]=='to') {
if (($start_word != '[NOT_ASSIGNED]') and (strpos($GLOBALS['to_separator_keywords'],'|'.$start_word.'|')!==false))
$script_line = str_replace(' '.$GLOBALS['language_data'][$language_check][$GLOBALS['column_from']].' ',' '.$GLOBALS['language_data'][$language_check][$GLOBALS['column_to']].' ',$script_line);}

else if ($GLOBALS['language_data'][$language_check][0]=='as') {
if (($start_word != '[NOT_ASSIGNED]') and (strpos($GLOBALS['as_separator_keywords'],'|'.$start_word.'|')!==false))
$script_line = str_replace(' '.$GLOBALS['language_data'][$language_check][$GLOBALS['column_from']].' ',' '.$GLOBALS['language_data'][$language_check][$GLOBALS['column_to']].' ',$script_line);}

else if (strpos($GLOBALS['helper_keywords'],'|'.$GLOBALS['language_data'][$language_check][0].'|')!==false) {
if ((($start_word != '[NOT_ASSIGNED]') and (strpos($GLOBALS['start_conditions_keywords'],'|'.$start_word.'|')!==false)) 
or (($start_word != '[NOT_ASSIGNED]') and (strpos($GLOBALS['start_helper_keywords'],'|'.$start_word.'|')!==false))
or (strpos($script_line,'=')!==false))
$script_line = str_replace(' '.str_replace(')','',$GLOBALS['language_data'][$language_check][$GLOBALS['column_from']]),' '.str_replace(')','',$GLOBALS['language_data'][$language_check][$GLOBALS['column_to']]),$script_line);}}

$script_line = str_replace('[START_OF_LINE]',$indentation_tracker,str_replace('[END_OF_LINE]','',$script_line));
$translate_log = fopen('translate.log','a'); fwrite($translate_log, $script_line."\n"); fclose($translate_log);
return $script_line."\n";}

?>
