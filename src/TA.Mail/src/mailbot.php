#!/usr/local/bin/php -q
<?php

/* MAILBOT SCRIPT FOR TA.MAIL TO AUTO-RESPOND TO EMAILS ~ TEBEL.SG */

$logdate = date('Y-m-d H:i:s') . "\n"; // for tracking mailbot processing date and start time
$logfile = fopen('/full_path_on_your_server/mailbot.log', 'a'); fwrite($logfile, $logdate); fclose($logfile);

// read entire email stream into variable for processing
$iostream = fopen("php://stdin", 'r'); $email = "";
while (!feof($iostream)) $email .= fread($iostream, 1024); fclose($iostream);

// extract items from email and call processing function
$logentry = get_from($email) . ", " . get_to($email) . ", " . get_subject($email) . "\n";
$logentry .= get_message($email) . "\n" . process_email($email) . "\n\n";

// save details of incoming email and outcome to logfile
$logfile = fopen('/full_path_on_your_server/mailbot.log', 'a'); fwrite($logfile, $logentry); fclose($logfile);

/* PROCESS EMAIL - logic to parse email intention before setting service parameters */
function process_email($email_content) {
$parsed_content = strtoupper(str_replace(" ","",get_subject($email_content)));
$parsed_content .= strtoupper(str_replace(" ","",get_message($email_content)));

if (strpos($parsed_content, "FOODNEARBY") !== false)
        set_service("FOODNEARBY",$email_content);

else if (strpos($parsed_content, "DELIVEROO") !== false)
        set_service("DELIVEROO",$email_content);

else if (strpos($parsed_content, "RESTAPI") !== false)
        set_service("RESTAPI",$email_content);

else set_service("NOACTION",$email_content);}

/* SET SERVICE - set parameters before using call_service() to trigger runner */
function set_service($service_intent,$service_request) {switch ($service_intent) {
case "FOODNEARBY":
        if (ctype_digit(substr(strtoupper(str_replace(" ","",get_subject($service_request))),-6)))
        $_GET['POSTAL']=substr(strtoupper(str_replace(" ","",get_subject($service_request))),-6);
        $_GET['SERVICE']="FOODNEARBY"; return call_service(); break;

case "DELIVEROO":
        $_GET['MESSAGE']="Ordering food from Deliveroo is switched off.";
        $_GET['SERVICE']="SENDMAIL"; return call_service(); break;

case "RESTAPI":
        $_GET['RESTURL']= str_replace("restapi ","",str_replace("RESTAPI ","",get_subject($service_request)));
        $_GET['SERVICE']="RESTAPI"; return call_service(); break;

case "NOACTION":
        $_GET['MESSAGE']="Your email has no actionable instruction.";
        $_GET['SERVICE']="SENDMAIL"; return call_service(); break;}}

/* CALL SERVICE */
function call_service() { // service runner to act on service parameters
        ob_start(); include('/full_path_on_your_server/service.php');
	$php_result = ob_get_contents(); ob_end_clean(); return $php_result;
}

/* GET EMAIL FROM */
function get_from($email_content) {
	$from1 = explode ("\nFrom: ", $email_content);
	$from2 = explode ("\n", $from1[1]);
	if(strpos ($from2[0], '<') !== false)
	{
    		$from3 = explode ('<', $from2[0]);
    		$from4 = explode ('>', $from3[1]);
    		$from = $from4[0];
	}
	else
	{
    		$from = $from2[0];
	}
        return $from;
}

/* GET EMAIL TO */
function get_to($email_content) {
	$to1 = explode ("\nTo: ", $email_content);
	$to2 = explode ("\n", $to1[1]);
	$to = str_replace ('>', '', str_replace('<', '', $to2[0]));
	return $to;
}

/* GET EMAIL SUBJECT */
function get_subject($email_content) {
        $subject1 = explode ("\nSubject: ", $email_content);
        $subject2 = explode ("\n", $subject1[1]);
        $subject = $subject2[0];
        return $subject;
}

/* GET EMAIL MESSAGE */
function get_message($email_content) {
	$message1 = explode ("\n\n", $email_content);
	$start = count ($message1) - 3;
	if ($start < 1) $start = 1;
	$message2 = explode ("\n\n", $message1[$start]);
	$message = $message2[0];
	return $message;
}

?>
