<?php

/* SERVICE API RUNNER SCRIPT FOR TAGUI FRAMEWORK ~ TEBEL.ORG */

// verify that both service queue files are accessible
$service_in_file = fopen('tagui_service.in','a') or die("ERROR - cannot open " . 'tagui_service.in' . "\n");
fclose($service_in_file);  chmod ('tagui_service.in',0600);
$service_out_file = fopen('tagui_service.out','a') or die("ERROR - cannot open " . 'tagui_service.out' . "\n");
fclose($service_out_file); chmod ('tagui_service.out',0600);

// retrieve last service count from each queue file
$service_in_count = get_last_service_count('tagui_service.in');
$service_out_count = get_last_service_count('tagui_service.out'); 

// create empty service action script with header
if ($service_out_count < $service_in_count) {
$service_act_file = fopen('tagui_service.act','w') or die("ERROR - cannot open " . 'tagui_service.act' . "\n");
fwrite($service_act_file,"#!/usr/bin/env bash"."\n"); fclose($service_act_file); chmod ('tagui_service.act',0700);}

// loop until service out count matches service in count
while ($service_out_count < $service_in_count) {

// retrieve next service request from in file and write to out file
$service_in_content = file_get_contents('tagui_service.in');
$start_pos = strrpos($service_in_content,"[".(intval($service_out_count)+1)."]");
$end_pos = strpos($service_in_content,"\n",$start_pos);
$service_out_file = fopen('tagui_service.out','a') or die("ERROR - cannot open " . 'tagui_service.out' . "\n");
fwrite($service_out_file,substr($service_in_content,$start_pos,$end_pos-$start_pos) . "\n");
echo substr($service_in_content,$start_pos,$end_pos-$start_pos) . " retrieved from queue" . "\n";
fclose($service_out_file); chmod ('tagui_service.out',0600);

// add tagui automation command for the service request to service action script
$start_pos += strlen("[".(intval($service_out_count)+1)."]")+1;
$tagui_command = substr($service_in_content,$start_pos,$end_pos-$start_pos);
$service_act_file = fopen('tagui_service.act','a') or die("ERROR - cannot open " . 'tagui_service.act' . "\n");
fwrite($service_act_file,$tagui_command."\n"); fclose($service_act_file); chmod ('tagui_service.act',0700);

// refresh last service counts to ensure latest numbers are used
$service_in_count = get_last_service_count('tagui_service.in'); 
$service_out_count = get_last_service_count('tagui_service.out');

}

// get last service count, or return 0 if there is no entry in queue
function get_last_service_count($service_file) {$service_in_content = file_get_contents($service_file);
$start_pos = strrpos($service_in_content,"["); $end_pos = strrpos($service_in_content,"]");
if (($start_pos!==false) and ($end_pos!==false))
return intval(substr($service_in_content,$start_pos+1,$end_pos-$start_pos-1));else return 0;}

?>
