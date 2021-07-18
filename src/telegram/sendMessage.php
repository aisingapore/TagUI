<?php

/* SERVICE API SCRIPT FOR TAGUI TELEGRAM BOT ~ https://github.com/kelaberetiv/TagUI/issues/1073 */

// grab chat_id from chat_id parameter
$chat_id = $_GET['chat_id'];

// grab message from text parameter
$message = $_GET['text'];

// error handling on validating parameters
if ($chat_id == "")
    die("ERROR - chat_id parameter is not provided");
else if (!is_numeric($chat_id))
    die("ERROR - chat_id parameter must be a number");
else if ($message == "")
    die("ERROR - text parameter is not provided");

// set to local timezone for date time stamp in log file
// see list - https://www.php.net/manual/en/timezones.php
date_default_timezone_set('see_above_for_your_time_zone');

// log chat_id and message length to prevent abuse
$log_entry = "[" . date('d-m-Y H:i:s') . "][" . $chat_id . "][" . strval(strlen($message)) . "]\n";

// log at existing folder outside of public access
file_put_contents("../../telegram/sendMessage.log", $log_entry, FILE_APPEND);

// build URL query string using MarkdownV2 only for bot welcome message, otherwise complicated escaping needed for users
if ((strpos($message, "Your Telegram ID is") !== false) and (strpos($message, "To send message in TagUI RPA") !== false))
    $post_data = http_build_query(
        array(
            'chat_id' => $chat_id,
            'text' => $message,
            'parse_mode' => 'MarkdownV2'
        )
    );
else
    $post_data = http_build_query(
        array(
            'chat_id' => $chat_id,
            'text' => $message,
        )
    );

// build the URL query options
$post_options = array('http' =>
    array(
        'method'  => 'POST',
        'header'  => 'Content-Type: application/x-www-form-urlencoded',
        'content' => $post_data,
        'ignore_errors' => true
    )
);

// provide telegram bot api token to be used
$url_target = "https://api.telegram.org/bot<token>/sendMessage";

// generate the full URL REST query object
$url_context = stream_context_create($post_options);

// make a call to telegram api and return response
// require allow_url_fopen to be enabled in php.ini
echo file_get_contents($url_target, false, $url_context);

?>
