<?php

/* SLEEP HELPER SCRIPT FOR TAGUI FRAMEWORK ~ TEBEL.ORG */

// Windows Batch environment does not have sleep command
// using ping.exe for sleep purpose adds extra dependency
// this script allows sleep without additional dependency

$sleep_delay = $argv[1]; if ($sleep_delay=="") die("ERROR - specify delay in seconds as first parameter\n");
usleep($sleep_delay * 1000000); // sleep does not allow decimals, use usleep to support fractions of second

?>
