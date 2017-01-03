<?php

/* RUNNER SCRIPT FOR SERVING SERVICE REQUESTS ~ TEBEL.SG */

// call services accordingly to SERVICE parameter
if ($_GET['SERVICE']=="SENDMAIL") sendmail_service();
elseif ($_GET['SERVICE']=="RESTAPI") restapi_service();
elseif ($_GET['SERVICE']=="FOODNEARBY") foodnearby_service();
else invalid_service(); // throw invalid service message

/* INVALID SERVICE */
function invalid_service() {
if ($_GET['OUTPUT']=="TEXT") echo "Action to take is invalid.\n";
else echo "<h1><center><br><br><br><br><br><br><br><br>Action to take is invalid.</center></h1>";}

/* SENDMAIL SERVICE */
function sendmail_service() { // call mailer REST API to send email
	ob_start(); include('/full_path_on_your_server/mailer.php');
	$php_result = ob_get_contents(); ob_end_clean(); echo $php_result;
}

/* RESTAPI SERVICE */
function restapi_service() { // use json_encode and json_decode as needed
$restapi_response = get_data($_GET['RESTURL']);
$_GET['SENDTO'] = "your_email@gmail.com"; $_GET['SENDNAME'] = "Your Name";
$_GET['SUBJECT'] = "RESTAPI Response"; $_GET['MESSAGE'] = $restapi_response; sendmail_service();}

/* GET DATA FROM URL */
function get_data($url) {
        $ch = curl_init();
        $timeout = 30;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $headers = array("Cache-Control: no-cache","Pragma: no-cache");
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
        curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);

        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
}

/* FOOD NEARBY SERVICE */
function foodnearby_service() {

// sample service template using casperjs to check available food nearby from online food delivery
$exec_result = exec("PHANTOMJS_EXECUTABLE=/usr/local/bin/phantomjs /usr/local/bin/casperjs /full_path_on_your_server/waiter.js " . $_GET['POSTAL']);

if (($exec_result == "") || (!strpos($exec_result," (")))
	$exec_result = "Food nearby service not available at the moment.";
else
{
	$exec_result = file_get_contents("/full_path_on_your_server/waiter.txt");
	$exec_result = str_replace("\n",'<br>',$exec_result);
	$exec_result = substr($exec_result,0,-4);
}

// call sendmail service to email findings of checking food nearby
$_GET['SENDTO'] = "your_email@gmail.com"; $_GET['SENDNAME'] = "Your Name";
$_GET['SUBJECT'] = "Food Nearby"; $_GET['MESSAGE'] = $exec_result; sendmail_service();
}

?>
