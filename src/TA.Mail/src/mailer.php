<?php

/* MAILER REST API FOR TA.MAIL TO AUTO-SEND EMAILS ~ TEBEL.SG */

// address email recipient by name if recipient name provided
if ($_GET['SENDNAME']=="")
	$name = "";
else
	$name = "<p>Hi " . $_GET['SENDNAME'] . "," . "</p><p></p>";

// your catch-all email in case recipient email not provided
if ($_GET['SENDTO']=="")
	$to = "your_email@gmail.com";
else
	$to = $_GET['SENDTO'];

// your default send from email if send from email not provided
if ($_GET['SENDFROM']=="")
	$from = "Your Name <your_email@gmail.com>";
else
	$from = $_GET['SENDFROM'];

// set default email subject if email subject not provided
if ($_GET['SUBJECT']=="")
	$subject = "Email Service";
else
	$subject = $_GET['SUBJECT'];

// set message body to blank if email message not provided
if ($_GET['MESSAGE']=="")
	{$custom_message = "Message body is empty."; $message = " ";}
else
	{
// set your email footer below to be used when there is message body
$custom_message = $_GET['MESSAGE'];
$message = "
<html>
<head>
<title>" . $subject . "</title>
</head>
<body>
" . $name . "
<p>" . $custom_message . "<br>" .
"<p></p>
<pre><span style=\"font-family: arial, helvetica, sans-serif; font-size: small;\">Kind Regards,<br><span style=\"color: #000000;\"><strong><em>Your Name</em></strong></span></span></pre></p>
</body>
</html>";
	}

// OPTION 1 - FOR SUPPORTING ATTACHMENTS AND WINDOWS - https://github.com/PHPMailer/PHPMailer
// get from above URL PHPMailerAutoload.php, class.phpmailer.php, class.smtp.php, class.pop3.php
require_once('/full_path_on_your_server/PHPMailer/PHPMailerAutoload.php'); $mail = new PHPMailer;

// configure SMTP server settings and account credentials used for sending mails
$mail->Host = "your.mail.server"; $mail->Port = 26; $mail->SMTPAuth = true;
$mail->Username = "your_email@gmail.com"; $mail->Password = "your_password";

$mail->isHTML(true); $mail->isSMTP(); $mail->SMTPDebug = 0; // set to 2 for debugging
if ($_GET['OUTPUT']=="TEXT") $mail->Debugoutput = 'text'; else $mail->Debugoutput = 'html';

if (strpos($from, '<') !== false) {
	$from_name = trim(substr($from,0,strpos($from,'<')));
	$from_email = trim(substr($from,strpos($from,'<')+1));
	$from_email = trim(str_replace('>','',$from_email));
	$mail->setFrom($from_email,$from_name);}
else	$mail->setFrom(trim($from));

if (strpos($to, '<') !== false) {
	$to_name = trim(substr($to,0,strpos($to,'<')));
	$to_email = trim(substr($to,strpos($to,'<')+1));
	$to_email = trim(str_replace('>','',$to_email));
	$mail->addAddress($to_email,$to_name);}
else	$mail->addAddress(trim($to));

// for debugging above block to extract email and name eg: Name <name@gmail.com>
// echo trim($from) . "\n"; echo $from_email ."\n"; echo $from_name . "\n";
// echo trim($to) . "\n"; echo $to_email . "\n"; echo $to_name . "\n"; die ("");

$mail->Subject = $subject; $mail->msgHTML($message, dirname(__FILE__));
if ($_GET['ATTACHMENT']!="") $mail->addAttachment($_GET['ATTACHMENT']);

// customise result output below to show email success or failure
// first block is to show output as raw text, second block as html
if ($_GET['OUTPUT']=="TEXT")
        {
        if ($mail->send())
		echo $subject . " mail sent successfully to " . trim($to) . "\n";
        else
		echo $subject . " mail not sent through to " . trim($to) . " - " . $mail->ErrorInfo . "\n";
        }
else
        {
        if ($mail->send())
		echo "<h1><center><br><br><br><br><br><br><br><br>" . $subject . 
		" mail sent successfully to " . trim($to) . "</center></h1>";
        else
		echo "<h1><center><br><br><br><br><br><br><br><br>" . $subject . 
		" mail not sent through to " . trim($to) . " - " . $mail->ErrorInfo . "</center></h1>";
        }
// OPTION 1 - END OF BLOCK

/*
// OPTION 2 - FOR BASIC EMAIL WITHOUT ATTACHMENTS - SENDING USING PHP MAIL
$headers  = "MIME-Version: 1.0" . "\r\n";
$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
$headers .= 'From: ' . $from . "\r\n";

// customise result output below to show email success or failure
// first block is to show output as raw text, second block as html
if ($_GET['OUTPUT']=="TEXT")
        {
        if (mail($to,$subject,$message,$headers))
		echo $subject . " mail sent successfully to " . trim($to) . "\n";
        else
		echo $subject . " mail not sent through to " . trim($to) . "\n";
	}
else
	{
        if (mail($to,$subject,$message,$headers))
		echo "<h1><center><br><br><br><br><br><br><br><br>" . $subject . 
		" mail sent successfully to " . trim($to) . "</center></h1>";
        else
		echo "<h1><center><br><br><br><br><br><br><br><br>" . $subject . 
		" mail not sent through to " . trim($to) . "</center></h1>";
	}
// OPTION 2 - END OF BLOCK
*/

?>
