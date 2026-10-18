<?php

/* Local stand-in for the external pages src/test/positive_test.tag names.
   Started by src/test/test_classic_fixtures.php. The classic parser is not involved. */

$map = array(
'/org/page.html' => 'suite_page.html',
'/frame/about.html' => 'suite_frame_about.html',
'/frame/detail.html' => 'suite_frame_detail.html',
'/frame/main.html' => 'suite_frame_main.html',
'/frame/sub.html' => 'suite_frame_sub.html',
'/popup/about.html' => 'suite_popup_about.html',
'/popup/url_keyword.html' => 'suite_popup_keyword.html',
'/visual.html' => 'visual.html');

$uri = isset($_SERVER['REQUEST_URI']) ? (string)$_SERVER['REQUEST_URI'] : '/';
$path = parse_url($uri, PHP_URL_PATH);
if (!is_string($path) || $path === '') $path = '/';

if ($path === '/report/month.txt') {
header('Content-Type: text/plain; charset=UTF-8');
echo "month-report\n";
return true;}

if ($path === '/api') {
header('Content-Type: application/json');
echo json_encode($_GET);
return true;}

if (isset($map[$path])) {
$file = __DIR__ . '/' . $map[$path];
header('Content-Type: text/html; charset=UTF-8');
readfile($file);
return true;}

http_response_code(404);
header('Content-Type: text/plain; charset=UTF-8');
echo "not found\n";
return true;
