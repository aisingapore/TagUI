<?php

/* MASSMAIL SCRIPT FOR TA.MAIL TO MASS-SEND EMAILS ~ TEBEL.SG */

// check for first parameter - file containing email recipients, one recipient per line
if ($argv[1]=="") die("ERROR - Recipient list file missing for first parameter\n"); $recipient_list = $argv[1];
if (!file_exists($recipient_list)) die("ERROR - cannot find " . $recipient_list . "\n");
$recipient_list_file = fopen($recipient_list,'r') or die("ERROR - cannot open " . $recipient_list . "\n");

// check for second parameter - file containing email message body, can be in text or html 
// for html email template check https://github.com/leemunroe/responsive-html-email-template
if ($argv[2]=="") die("ERROR - Email message file missing for second parameter\n"); $email_message = $argv[2];
if (!file_exists($email_message)) die("ERROR - cannot find " . $email_message . "\n");
$email_message_file = fopen($email_message,'r') or die("ERROR - cannot open " . $email_message . "\n");
fclose($email_message_file); $email_body = file_get_contents($email_message); // below for text-file handling
if ($email_body == strip_tags($email_body)) $email_body = str_replace("\n","<br>",$email_body);

// check for 3rd parameter - attachment filename to be attached and sent with the email
if ($argv[3]!="") {$email_attachment = $argv[3];
if (!file_exists($email_attachment)) die("ERROR - cannot find " . $email_attachment . "\n");
$email_attachment_file = fopen($email_attachment,'r') or die("ERROR - cannot open " . $email_attachment . "\n");
fclose($email_attachment_file); $_GET['ATTACHMENT'] = $email_attachment;}

// loop to send email message to each recipient in the recipient list, one by one
// for sophisticated open-source newsletter management consider https://www.phplist.org
while (!feof($recipient_list_file)) send(trim(fgets($recipient_list_file)),$email_body); fclose($recipient_list_file);

// function to assign email parameters and call sendmail_service to send email
function send($recipient, $message) {
	if ($recipient=="") return; // skip sending if blank recipient line

	// static assignments kept within recursion for future dynamic use
	$_GET['SENDFROM'] = "Your Name <your_email@gmail.com>"; // set from
	$_GET['SUBJECT'] = "Email Service"; // set email subject to be used 
	$_GET['SENDNAME'] = ""; // set addressee name or leave as blank
	$_GET['OUTPUT'] = "TEXT"; // set result output as TEXT OR HTML

	// non-static assignments to be kept within recursive function
	$_GET['SENDTO'] = $recipient; $_GET['MESSAGE'] = $message; sendmail_service();
}

/* SENDMAIL SERVICE */
function sendmail_service() { // call mailer REST API to send email
	ob_start(); include('/full_path_on_your_server/mailer.php');
	$php_result = ob_get_contents(); ob_end_clean(); echo $php_result;
}

?>
