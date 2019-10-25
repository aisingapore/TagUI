<?php

/* REPORT SCRIPT FOR TAGUI FRAMEWORK ~ TEBEL.ORG */

// verify automation log file exists and can be opened
$script = $argv[1]; if ($script=="") die("ERROR - specify flow filename for report\n");
if (!file_exists($script . '.log')) die("ERROR - cannot find " . $script . '.log' . "\n");
$log_file = fopen($script . '.log','r') or die("ERROR - cannot open " . $script . '.log' . "\n"); fclose($log_file);
$rep_file = fopen($script . '.html','w') or die("ERROR - cannot open " . $script . '.html' . "\n"); fclose($rep_file);

// below log automation outcome to audit report csv database

$log_content = file_get_contents($script . '.log');
$start_timestamp = "NOT STARTED"; $timestamp_header = "START - automation started - ";
if (strpos($log_content, $timestamp_header) !== false) {
$startpos_timestamp = strpos($log_content, $timestamp_header) + strlen($timestamp_header);
$start_timestamp = substr($log_content, $startpos_timestamp); $endpos_timestamp = strpos($start_timestamp, "\n");
$start_timestamp = trim(substr($start_timestamp, 0, $endpos_timestamp));}

$finish_timestamp = "NOT FINISHED"; $timestamp_footer = "FINISH - automation finished - ";
if (strpos($log_content, $timestamp_footer) !== false) {
$startpos_timestamp = strpos($log_content, $timestamp_footer) + strlen($timestamp_footer);
$finish_timestamp = substr($log_content, $startpos_timestamp); $endpos_timestamp = strpos($finish_timestamp, "\n");
$finish_timestamp = substr(trim(substr($finish_timestamp, 0, $endpos_timestamp)),0,-1);}

$error_status = "SUCCESS"; $error_identifier = "ERROR - "; 
if (strpos($log_content, $error_identifier) !== false) {
$startpos_error = strpos($log_content, $error_identifier) + strlen($error_identifier);
$error_status = substr($log_content, $startpos_error); $endpos_error = strpos($error_status, "\n");
$error_status = trim(substr($error_status, 0, $endpos_error));}

// escape double quotes in the csv data values
$escaped_script = str_replace('"', '""', $script);
$start_timestamp = str_replace('"', '""', $start_timestamp);
$finish_timestamp = str_replace('"', '""', $finish_timestamp);
$error_status = str_replace('"', '""', $error_status);

$audit_output_header = '"#","AUTOMATION FLOW","START TIME","FINISH TIME","ERROR STATUS","LOG FILE"';
if (!file_exists('tagui_report.csv')) file_put_contents('tagui_report.csv', $audit_output_header . "\r\n");
$audit_output_count = @count(file('tagui_report.csv')) - 1 + 1; // to track and increment entry # in audit file
$html_log_file = $script . '_' . $audit_output_count . '.html'; // to allow log persistence without overwriting
$audit_output_line = '"' . $audit_output_count . '","' . $escaped_script . '","' . $start_timestamp . '","'; 
$audit_output_line .= $finish_timestamp . '","' . $error_status . '","' . $html_log_file . '"' . "\r\n";
$audit_output_file = fopen('tagui_report.csv','a') or die("ERROR - cannot open " . 'tagui_report.csv' . "\n");
fwrite($audit_output_file, $audit_output_line); fclose($audit_output_file);

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
file_put_contents($script . '_' . $audit_output_count . '.html',$log_content); // write another file that persists

?>
