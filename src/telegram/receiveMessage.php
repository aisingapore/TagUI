<?php

/* SERVICE API SCRIPT FOR TAGUI TELEGRAM BOT ~ https://github.com/kelaberetiv/TagUI/issues/1073 */

// grab api_token from token parameter
$api_token = $_GET['token'];

// validate that call is from telegram
if ($api_token != "<token>")
    die("ERROR - invalid telegram bot api token provided");

// read raw data from the post request 
$json_data = file_get_contents('php://input');

// convert data into a PHP object
$post_data = json_decode($json_data, true);

// retrieve chat_id and message
try {
    $chat_id = $post_data['message']['chat']['id'];
    $message = $post_data['message']['text'];
} catch (Exception $e) {
    $chat_id = ""; $message = "";
    die("ERROR - cannot find both chat_id and message");
}

// set to local timezone for date time stamp in log file
// see list - https://www.php.net/manual/en/timezones.php
date_default_timezone_set('see_above_for_your_time_zone');

// log chat_id and message length to prevent abuse
$log_entry = "[" . date('d-m-Y H:i:s') . "][" . $chat_id . "][" . strval(strlen($message)) . "]\n";

// log at existing folder outside of public access
file_put_contents("../../telegram/receiveMessage.log", $log_entry, FILE_APPEND);

// form welcome message with chat_id and example
$message = "Your Telegram ID is `" . $chat_id . "`\n\nTo send message in TagUI RPA \-\n\n`telegram id message`";

// call sendMessage.php to send chat_id to user
$_GET['chat_id'] = $chat_id; $_GET['text'] = $message; require 'sendMessage.php';

?>
