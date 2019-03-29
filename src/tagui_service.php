<?php

/* SERVICE API SCRIPT FOR TAGUI FRAMEWORK ~ TEBEL.ORG */

// grab tagui automation settings from SETTINGS parameter
$service_settings = $_GET['SETTINGS'];

// bunch of checks to validate input and quotation marks
if ($service_settings == "")
	die("ERROR - SETTINGS parameter not provided" . "\n");
else
{
	if (substr($service_settings,0,1)=="\"") 
	{
		if (substr($service_settings,-1)=="\"")
			$service_settings = substr($service_settings,1,strlen($service_settings)-2);
		else
			die("ERROR - no closing \" in SETTINGS parameter" . "\n");
	}
	else if (substr($service_settings,0,1)=="'")
	{
                if (substr($service_settings,-1)=="'")
                        $service_settings = substr($service_settings,1,strlen($service_settings)-2);
                else
                        die("ERROR - no closing ' in SETTINGS parameter" . "\n");
	}
	else if (substr($service_settings,-1)=="\"") die("ERROR - no opening \" in SETTINGS parameter" . "\n");
	else if (substr($service_settings,-1)=="'") die("ERROR - no opening ' in SETTINGS parameter" . "\n");

	// following line disables by default webservice execution of online flows
	else if ((strpos($service_settings,'http:')!== false) or (strpos($service_settings,'https:')!== false))
	die("ERROR - webservice execution of online flow disabled by default" . "\n");

	// perform some sanity check to filter shell escaping characters for code injection
	if ((strpos($service_settings,'|')!== false) or (strpos($service_settings,';')!== false)
	or (strpos($service_settings,'&')!== false) or (strpos($service_settings,'>')!== false))
	die("ERROR - illegal character found that poses command injection risk");

	// add service request into queue file tagui_service.in
	$service_in_file = fopen('tagui_service.in','a') or die("ERROR - cannot open " . 'tagui_service.in' . "\n");
	$service_count = get_next_service_count('tagui_service.in');
	fwrite($service_in_file,"[".$service_count."]" . ' ./tagui ' . $service_settings . "\n");
	echo "[".$service_count."]" . ' ./tagui ' . $service_settings . " added into queue" . "\n";
	fclose($service_in_file); chmod ('tagui_service.in',0600); // change permissions for security

	// log service request into log file tagui_service.log 
	$service_log_file = fopen('tagui_service.log','a') or die("ERROR - cannot open " . 'tagui_service.log' . "\n");
	fwrite($service_log_file,"[".$service_count."]" . ' ./tagui ' . $service_settings . " added into queue" . "\n");
	fclose($service_log_file); chmod ('tagui_service.log',0600); // change permissions for security
}

// get last service count and increment by 1, or return 1 if it is first entry in queue
function get_next_service_count($service_file) {$service_in_content = file_get_contents($service_file);
$start_pos = strrpos($service_in_content,"["); $end_pos = strrpos($service_in_content,"]");
if (($start_pos!==false) and ($end_pos!==false)) 
return intval(substr($service_in_content,$start_pos+1,$end_pos-$start_pos-1))+1;else return 1;}

?>
