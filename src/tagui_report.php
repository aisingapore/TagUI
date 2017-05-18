<?php

/* REPORT SCRIPT FOR TAGUI FRAMEWORK ~ TEBEL.ORG */

// verify automation log file exists and can be opened
$script = $argv[1]; if ($script=="") die("ERROR - specify flow filename for report\n");
if (!file_exists($script . '.log')) die("ERROR - cannot find " . $script . '.log' . "\n");
$log_file = fopen($script . '.log','r') or die("ERROR - cannot open " . $script . '.log' . "\n"); fclose($log_file);
$rep_file = fopen($script . '.html','w') or die("ERROR - cannot open " . $script . '.html' . "\n"); fclose($rep_file);

// below start conversion of text log file to html file

// add html <br> tag to newline for proper line breaks
$log_content = file_get_contents($script . '.log'); $log_content = str_replace("\n","<br>\n",$log_content);

// remove ansi color codes (appear as garbage in html) using regex
$log_content = preg_replace("/\x{001B}\\[[;\\d]*m/u","",$log_content);

// change automation start message to accent color
$start_pos = strpos($log_content,"START - automation started - "); $end_pos = strpos($log_content,"<br>",$start_pos);
$log_content = substr($log_content,0,$start_pos) . "<span style=\"color: rgb(30,130,201);\">" . 
substr($log_content,$start_pos,$end_pos-$start_pos) . "</span>" .  substr($log_content,$end_pos);

// add html definition, font family, size (h1-h6), width, margin
$log_content = "<!DOCTYPE html>\n<html><head>" . 
"<link href='http://fonts.googleapis.com/css?family=Source+Sans+Pro:600' rel='stylesheet'>\n" . 
"<style>body {font-family: 'Source Sans Pro', sans-serif; width: 90%; margin: auto;}</style>" . 
"</head><body><h3>\n" . $log_content . "</h3></body></html>"; file_put_contents($script . '.html',$log_content);

?>
