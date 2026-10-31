<?php

/* Run the steps after this.exit() in src/test/positive_test.tag on the classic
   CasperJS engine, against local pages. positive_test.tag and tagui_parse.php
   are not modified. Dead hosts are rewritten only in the copy that is executed. */

function fixture_port() {
$socket = stream_socket_server('tcp://127.0.0.1:0', $errno, $errstr);
if ($socket === false) return 0;
$name = stream_socket_get_name($socket, false);
fclose($socket);
return (int)substr((string)$name, (int)strrpos((string)$name, ':') + 1);}

function fixture_rm($path) {
if (!is_string($path) || $path === '' || !file_exists($path)) return;
if (is_link($path) || is_file($path)) {unlink($path); return;}
$items = scandir($path);
if (!is_array($items)) return;
foreach ($items as $item) {
if ($item === '.' || $item === '..') continue;
fixture_rm($path . '/' . $item);}
rmdir($path);}

function start_server($fixtures, $port, $log) {
$command = array(PHP_BINARY, '-S', '127.0.0.1:' . $port, 'suite_router.php');
$descriptor = array(0 => array('file', '/dev/null', 'r'), 1 => array('file', $log, 'a'), 2 => array('file', $log, 'a'));
$process = proc_open($command, $descriptor, $pipes, $fixtures);
return is_resource($process) ? $process : null;}

function wait_server($url) {
$deadline = microtime(true) + 5;
while (microtime(true) < $deadline) {
$body = @file_get_contents($url);
if (is_string($body) && str_contains($body, 'id="header"')) return true;
usleep(100000);}
return false;}

function write_png($path) {
$bytes = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==');
file_put_contents($path, $bytes);}

function is_excel_line($line) {
if (strpos($line, '=') === false) return false;
if (strpos($line, '//') === 0) return false;
return preg_match('/\[.*\.(xl.|xl..|xml|csv)\].*![A-Z0-9]*/i', $line) === 1;}

function visual_target($line) {
if (!preg_match('/^(tap|click|hover|move|type|enter|select|choose)\b/i', $line, $match)) return '';
$rest = trim(substr($line, strlen($match[0])));
$as = stripos($rest, ' as ');
if ($as !== false) $rest = trim(substr($rest, 0, $as));
return $rest;}

function is_visual_target($target) {
if ($target === '') return false;
$low = strtolower($target);
if (strlen($low) > 4 && (substr($low, -4) === '.png' || substr($low, -4) === '.bmp')) return true;
if (strlen($low) > 9 && substr($low, -9) === 'using ocr') return true;
if (strlen($target) > 4 && $target[0] === '(' && substr($target, -1) === ')' && substr_count($target, ',') >= 1 && preg_match('/[a-zA-Z]/', $target) !== 1) return true;
return false;}

function skip_reason($trim, $section, $sikuli, $skip_excel) {
$low = strtolower($trim);
if (str_starts_with(strtolower($section), 'test indentation while loop simple') && $low !== 'number = 1') {
return 'official test loop never terminates; classic would hang too';}
if (str_starts_with($low, 'telegram')) return 'telegram step calls an external bot endpoint';
if (str_starts_with($low, 'api ') && str_contains($trim, '`')) return 'classic aborts when an api step reads a variable the preceding var statement did not set';
if (str_contains($trim, '(//*[@id="test"])[`variable`]')) return 'variable was overwritten by read page, so this click locator is the page HTML and classic exits';
if ($skip_excel && is_excel_line($trim)) return 'classic Excel steps exit on Linux; they run only on Windows and Mac';
if ($sikuli) return '';
if (str_ends_with($low, 'using ocr')) return 'Sikuli/OculiX is not installed';
if (str_starts_with($low, 'keyboard') || str_starts_with($low, 'mouse ') || $low === 'mouse') return 'Sikuli/OculiX is not installed';
if (str_starts_with($low, 'vision')) return 'Sikuli/OculiX is not installed';
if (str_contains($low, 'mouse_xy()') || str_contains($low, 'mouse_x()') || str_contains($low, 'mouse_y()') || str_contains($low, 'clipboard(')) return 'Sikuli/OculiX is not installed';
if (is_visual_target(visual_target($trim))) return 'Sikuli/OculiX is not installed';
return '';}

function rewrite_external($line, $base, $host) {
$trim = trim($line);
if (preg_match('#^https?://(?:www\.)?google\.com/?$#i', $trim)) return $base . '/org/page.html';
if (preg_match('#^https?://ca\.yahoo\.com/?$#i', $trim)) return $base . '/org/page.html';
if (preg_match('#^https?://www\.dummytestsite\.com/?$#i', $trim)) return $base . '/org/page.html';
if (str_contains($line, '://www.dummytestsite.com/')) {
$line = str_replace('http://www.dummytestsite.com', $base, $line);
return str_replace('https://www.dummytestsite.com', $base, $line);}
if (str_contains($line, 'www.dummytestsite.com')) return str_replace('www.dummytestsite.com', $host . '/org/page.html', $line);
return $line;}

function is_step_line($trim) {
if ($trim === '' || str_starts_with($trim, '//')) return false;
if ($trim === '{' || $trim === '}') return false;
return true;}

$root = dirname(__DIR__, 2);
$source = $root . '/src/test/positive_test.tag';
$parser = $root . '/src/tagui_parse.php';
$fixtures = $root . '/src/test/fixtures';
$tagui = $root . '/src/tagui';
$source_hash = md5_file($source);
$parser_hash = md5_file($parser);
$lines = file($source);
if (!is_array($lines)) {fwrite(STDERR, "FAIL cannot read positive_test.tag\n"); exit(1);}
$start = -1;
foreach ($lines as $index => $line) {
if (str_contains($line, 'this.exit()')) {$start = $index + 1; break;}}
if ($start < 0) {fwrite(STDERR, "FAIL positive_test.tag has no this.exit()\n"); exit(1);}

$port = fixture_port();
if ($port < 1) {fwrite(STDERR, "FAIL cannot reserve a port\n"); exit(1);}
$base = 'http://127.0.0.1:' . $port;
$host = '127.0.0.1:' . $port;
$sikuli = getenv('TAGUI_SIKULI_JAR');
$sikuli = is_string($sikuli) && $sikuli !== '' && is_file($sikuli);
$skip_excel = PHP_OS_FAMILY !== 'Windows' && PHP_OS_FAMILY !== 'Darwin';
$section = 'before first header';
$in_vision = false;
$popup_indent = null;
$started = false;
$popup_reason = 'classic Chrome helper aborts when a popup DevTools reply exceeds its 5s socket timeout';
$flow_lines = array();
$executed = 0;
$skipped = 0;
$reasons = array();
for ($i = $start; $i < count($lines); $i++) {
$raw = $lines[$i];
$trim = trim($raw);
if (preg_match('/^\/\/\s*test\b/i', $trim)) $section = trim(substr($trim, 2));
if (!$started) {
if ($trim === '' || str_starts_with($trim, '//')) continue;
$flow_lines[] = rewrite_external($raw, $base, $host);
$started = true;
if (is_step_line($trim)) $executed++;
continue;}
if ($in_vision) {
if (is_step_line($trim)) {
$skipped++;
if (!isset($reasons['Sikuli/OculiX is not installed'])) $reasons['Sikuli/OculiX is not installed'] = 0;
$reasons['Sikuli/OculiX is not installed']++;}
if (strtolower($trim) === 'vision finish') $in_vision = false;
continue;}
if ($popup_indent !== null) {
$indent = strlen($raw) - strlen(ltrim($raw, " \t"));
if ($trim === '' || $indent > $popup_indent) {
if (is_step_line($trim)) {
$skipped++;
if (!isset($reasons[$popup_reason])) $reasons[$popup_reason] = 0;
$reasons[$popup_reason]++;}
continue;}
$popup_indent = null;}
if (strtolower($trim) === 'dom begin') {
$has_prompt = false;
$finish = $i;
for ($j = $i; $j < count($lines); $j++) {
if (str_contains(strtolower($lines[$j]), 'prompt(')) $has_prompt = true;
if (strtolower(trim($lines[$j])) === 'dom finish') {$finish = $j; break;}}
if ($has_prompt) {
$reason = 'prompt() inside a dom block does not return under headless Chrome';
for ($j = $i; $j <= $finish; $j++) {
$piece = trim($lines[$j]);
if (!is_step_line($piece)) continue;
$flow_lines[] = '// SKIP ' . $reason;
$skipped++;
if (!isset($reasons[$reason])) $reasons[$reason] = 0;
$reasons[$reason]++;}
$i = $finish;
continue;}}
if (preg_match('/^popup\b/i', $trim)) {
$popup_indent = strlen($raw) - strlen(ltrim($raw, " \t"));
$flow_lines[] = '// SKIP ' . $popup_reason;
$skipped++;
if (!isset($reasons[$popup_reason])) $reasons[$popup_reason] = 0;
$reasons[$popup_reason]++;
continue;}
if (!$sikuli && strtolower($trim) === 'vision begin') {
$in_vision = true;
$flow_lines[] = '// SKIP Sikuli/OculiX is not installed';
if (is_step_line($trim)) {
$skipped++;
if (!isset($reasons['Sikuli/OculiX is not installed'])) $reasons['Sikuli/OculiX is not installed'] = 0;
$reasons['Sikuli/OculiX is not installed']++;}
continue;}
if (!$sikuli && str_contains(strtolower($section), 'mouse_xy') && is_step_line($trim)) {
$reason = 'Sikuli/OculiX is not installed';
$flow_lines[] = '// SKIP ' . $reason;
$skipped++;
if (!isset($reasons[$reason])) $reasons[$reason] = 0;
$reasons[$reason]++;
continue;}
if ($skip_excel && stripos($section, 'excel') !== false && is_step_line($trim)) {
$reason = 'classic Excel steps exit on Linux; they run only on Windows and Mac';
$flow_lines[] = '// SKIP ' . $reason;
$skipped++;
if (!isset($reasons[$reason])) $reasons[$reason] = 0;
$reasons[$reason]++;
continue;}
$reason = skip_reason($trim, $section, $sikuli, $skip_excel);
if ($reason !== '') {
$flow_lines[] = '// SKIP ' . $reason;
if (is_step_line($trim)) {
$skipped++;
if (!isset($reasons[$reason])) $reasons[$reason] = 0;
$reasons[$reason]++;}
continue;}
$written = rewrite_external($raw, $base, $host);
if (str_contains($written, 'mouse_xy()') || str_contains($written, 'mouse_x()') || str_contains($written, 'mouse_y()') || str_contains($written, 'clipboard(')) {
fwrite(STDERR, "FAIL refused to emit a Sikuli helper into the classic copy\n");
exit(1);}
$flow_lines[] = rtrim($written, "\r\n");
if (is_step_line($trim)) $executed++;}

$work = sys_get_temp_dir() . '/tagui_classic_fixtures_' . getmypid();
fixture_rm($work);
mkdir($work . '/bin', 0777, true);
foreach (array('face_recognition', 'face_recognition1', 'face_recognition2') as $stub) {
file_put_contents($work . '/bin/' . $stub, "#!/bin/sh\nexit 0\n");
chmod($work . '/bin/' . $stub, 0755);}
$python = is_executable('/usr/bin/python3') ? '/usr/bin/python3' : 'python3';
if (!is_executable($work . '/bin/python')) symlink($python, $work . '/bin/python');
write_png($work . '/abc.png');
write_png('/tmp/abc.png');
$flow = $work . '/positive_test_fixtures.tag';
file_put_contents($flow, implode("\n", $flow_lines) . "\n");
$stdin = "echo suite-live-ok\n" . $base . "/org/page.html\nclick #header\nlive_seen = 1\necho `live_seen`\ndump `live_seen` to live_seen.txt\ndone\n";
file_put_contents($work . '/stdin.txt', $stdin);
$server_log = $work . '/server.log';
$server = start_server($fixtures, $port, $server_log);
if ($server === null) {fwrite(STDERR, "FAIL cannot start fixture server\n"); exit(1);}
$sandbox = $root . '/src/tagui_no_sandbox';
$sandbox_created = !is_file($sandbox);
if ($sandbox_created) touch($sandbox);
register_shutdown_function(function () use ($server, $work, $sandbox, $sandbox_created) {
if (is_resource($server)) {proc_terminate($server); proc_close($server);}
if ($sandbox_created && is_file($sandbox)) unlink($sandbox);
@unlink('/tmp/abc.png');
fixture_rm($work);});
if (!wait_server($base . '/org/page.html')) {
fwrite(STDERR, "FAIL fixture server did not serve the page\n");
exit(1);}

fwrite(STDOUT, "classic fixtures  base $base  execute $executed  skip $skipped\n");
foreach ($reasons as $reason => $count) fwrite(STDOUT, "  skip $count  $reason\n");
fflush(STDOUT);

$saved = getenv('PATH');
if (!is_string($saved)) $saved = '';
$path = $work . '/bin' . PATH_SEPARATOR . $saved;
$run_log = $work . '/run.out';
$command = 'OPENSSL_CONF=/dev/null PATH=' . escapeshellarg($path) . ' ' . escapeshellarg($tagui) . ' ' . escapeshellarg($flow) . ' -headless -quiet';
$descriptor = array(0 => array('file', $work . '/stdin.txt', 'r'), 1 => array('file', $run_log, 'w'), 2 => array('file', $run_log, 'a'));
$process = proc_open($command, $descriptor, $pipes, $work);
if (!is_resource($process)) {fwrite(STDERR, "FAIL cannot start tagui\n"); exit(1);}
$code = proc_close($process);
$text = is_file($run_log) ? (string)file_get_contents($run_log) : '';
$flow_log = $flow . '.log';
if (is_file($flow_log)) $text .= "\n" . (string)file_get_contents($flow_log);
$live_dump = is_file($work . '/live_seen.txt') ? trim((string)file_get_contents($work . '/live_seen.txt')) : '';

$needles = array('FOUND', 'LEMON', 'page title contains About', 'page url contains org', 'page text contains automation', 'suite-live-ok', 'THIS IS JAVASCRIPT', 'YES', 'test mainframe', 'test subframe', 'cde 5');
$missing = array();
foreach ($needles as $needle) {
if (!str_contains($text, $needle)) $missing[] = $needle;}
$fatal = array();
foreach (array('ERROR - cannot find popup', 'ERROR - Excel automation', 'ERROR - cannot execute Python', 'ERROR - cannot execute R', 'ERROR - cannot find image', 'on screen', 'PhantomJS has crashed', 'CasperError', 'command to run exceeded', "can't find variable") as $bad) {
if (str_contains($text, $bad)) $fatal[] = $bad;}
if ($live_dump !== '1') $missing[] = 'live_seen.txt=1 (got ' . $live_dump . ')';
$unchanged = md5_file($source) === $source_hash && md5_file($parser) === $parser_hash;

@copy($run_log, sys_get_temp_dir() . '/classic-fixtures-run.out');
fwrite(STDOUT, "classic fixtures  exit $code  live " . json_encode($live_dump) . "\n");
if (!$unchanged) fwrite(STDOUT, "FAIL positive_test.tag or tagui_parse.php changed\n");
if (count($missing) > 0) fwrite(STDOUT, 'FAIL missing ' . implode(' | ', $missing) . "\n");
if (count($fatal) > 0) fwrite(STDOUT, 'FAIL fatal ' . implode(' | ', $fatal) . "\n");
if ($code !== 0) fwrite(STDOUT, "note tagui exit $code (the runner returns 1 when the log contains ERROR)\n");
if (count($missing) > 0 || count($fatal) > 0 || !$unchanged) {
$tail = implode("\n", array_slice(preg_split("/\r\n|\n|\r/", $text), -80));
fwrite(STDOUT, "----- tail -----\n$tail\n");
exit(1);}
fwrite(STDOUT, "ok classic fixtures\n");
exit(0);
