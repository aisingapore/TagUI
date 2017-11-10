<?php

/* MULTI-LANGUAGE TRANSLATION SCRIPT FOR TAGUI FRAMEWORK ~ TEBEL.ORG */

// experimental entry point
$source_flow = $argv[1]; if ($source_flow=="") die("ERROR - specify flow filename as first parameter\n");
$direction = $argv[2]; if ($direction=="") die("ERROR - specify to or from as third parameter\n");
$language = $argv[3]; if ($language=="") die("ERROR - specify language as third parameter\n");

// set language definition array columns base on direction of translation
$direction = strtolower($direction);
if (($direction != 'to') and ($direction != 'from')) die("ERROR - specify to or from as third parameter\n");
if ($direction == 'from') {$column_from = 1; $column_to = 0;} else {$column_from = 0; $column_to = 1;}

// load desired language definition file into array for use in translation
$language = strtolower($language); $language_count = 0; if (file_exists('languages/' . $language . '.csv')) {
$language_file = fopen('languages/' . $language . '.csv','r') or die("ERROR - cannot open " . $language . '.csv' . "\n");
while (!feof($language_file)) {$language_data[$language_count] = fgetcsv($language_file);
if (count($language_data[$language_count]) == 0) die("ERROR - empty row found in " . $language . '.csv' . "\n");
$language_count++;} fclose($language_file); $language_count-=2;} //-1 for header, -1 for EOF

// load automation flow file and perform translation using language definition
$target_flow = $source_flow . '_translated'; // add translated postfix to target flow name
$source_file = fopen($source_flow,'r') or die("ERROR - cannot open " . $source_flow . "\n");
$target_file = fopen($target_flow,'w') or die("ERROR - cannot open " . $target_flow . "\n");
while(!feof($source_file)) {fwrite($target_file,translate_intent(fgets($source_file)));}
fclose($source_file); fclose($target_file);

function translate_intent($script_line) {
$script_line = ' '.trim($script_line).' '; // use padding to prevent false replacement when a match happens mid-string
for ($language_check = 1; $language_check <= $GLOBALS['language_count']; $language_check++)
$script_line = str_replace(' '.$GLOBALS['language_data'][$language_check][$GLOBALS['column_from']].' ',' '.$GLOBALS['language_data'][$language_check][$GLOBALS['column_to']].' ',$script_line); return trim($script_line)."\n";
}

?>
