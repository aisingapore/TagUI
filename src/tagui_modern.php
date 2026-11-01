<?php

/* MODERN CHROME ENGINE FOR TAGUI ~ proof of concept
   Drives Chrome or Edge directly with the DevTools Protocol.
   No CasperJS and no PhantomJS. The casperjs engine stays the default.
   Invoked by `tagui flow modern` (headless Chrome unless `chrome` is also set). */

if (!function_exists('str_starts_with')) {
function str_starts_with($haystack, $needle) {
return $needle === '' || strncmp($haystack, $needle, strlen($needle)) === 0;}
function str_ends_with($haystack, $needle) {
if ($needle === '') return true; $len = strlen($needle);
return substr($haystack, -$len) === $needle;}
function str_contains($haystack, $needle) {
return $needle === '' || strpos($haystack, $needle) !== false;}}

require_once __DIR__ . '/ws/Base.php';
require_once __DIR__ . '/ws/Client.php';
require_once __DIR__ . '/ws/Exception.php';
require_once __DIR__ . '/ws/BadOpcodeException.php';
require_once __DIR__ . '/ws/BadUriException.php';
require_once __DIR__ . '/ws/ConnectionException.php';
require_once __DIR__ . '/tagui_steps.php';

function tagui_is_elf($path) {
if (!is_string($path) || !is_file($path) || !is_executable($path)) return false;
$handle = fopen($path, 'rb');
if ($handle === false) return false;
$magic = fread($handle, 4);
fclose($handle);
return $magic === "\x7fELF";}

function tagui_unwrap_chromium($path) {
if (!is_string($path) || $path === '' || !is_file($path)) return $path;
$base = basename($path);
if ($base !== 'chromium' && $base !== 'chromium-browser') return $path;
if (tagui_is_elf($path)) return $path;
$dir = dirname($path);
$candidates = array(
$dir . '/../lib/chromium/chromium',
$dir . '/../lib/chromium-browser/chromium-browser',
'/usr/lib/chromium/chromium',
'/usr/lib/chromium-browser/chromium-browser');
foreach ($candidates as $candidate) {
$real = realpath($candidate);
if (is_string($real) && tagui_is_elf($real)) return $real;}
return $path;}

function tagui_key_event($type, $key, $code, $vk, $text = null, $modifiers = 0) {
$event = array(
'type' => $type,
'key' => $key,
'code' => $code,
'windowsVirtualKeyCode' => $vk,
'nativeVirtualKeyCode' => $vk);
if ($modifiers !== 0) $event['modifiers'] = $modifiers;
if ($text !== null) {
$event['text'] = $text;
$event['unmodifiedText'] = $text;}
return $event;}

function tagui_named_key($token) {
// Fourth field is keyDown text and unmodifiedText. Puppeteer's USKeyboardLayout
// sets text only on Enter ('\r') and Space (' '). The other named keys have none.
$table = array(
'enter' => array('Enter', 'Enter', 13, "\r"),
'tab' => array('Tab', 'Tab', 9, null),
'esc' => array('Escape', 'Escape', 27, null),
'escape' => array('Escape', 'Escape', 27, null),
'backspace' => array('Backspace', 'Backspace', 8, null),
'bs' => array('Backspace', 'Backspace', 8, null),
'delete' => array('Delete', 'Delete', 46, null),
'del' => array('Delete', 'Delete', 46, null),
'space' => array(' ', 'Space', 32, ' '),
'up' => array('ArrowUp', 'ArrowUp', 38, null),
'down' => array('ArrowDown', 'ArrowDown', 40, null),
'left' => array('ArrowLeft', 'ArrowLeft', 37, null),
'right' => array('ArrowRight', 'ArrowRight', 39, null),
'home' => array('Home', 'Home', 36, null),
'end' => array('End', 'End', 35, null),
'pageup' => array('PageUp', 'PageUp', 33, null),
'pagedown' => array('PageDown', 'PageDown', 34, null),
'insert' => array('Insert', 'Insert', 45, null));
$token = strtolower($token);
return isset($table[$token]) ? $table[$token] : null;}

function tagui_special_events($token) {
$named = tagui_named_key($token);
if ($named === null) return array();
$down = tagui_key_event('keyDown', $named[0], $named[1], $named[2], $named[3]);
$up = tagui_key_event('keyUp', $named[0], $named[1], $named[2], null);
return array($down, $up);}

function tagui_char_events($ch) {
if ($ch === ' ') return tagui_special_events('space');
$code = '';
$vk = 0;
$key = $ch;
if (strlen($ch) === 1) {
$ord = ord($ch);
if ($ord >= 65 && $ord <= 90) {$code = 'Key' . $ch; $vk = $ord;}
else if ($ord >= 97 && $ord <= 122) {$code = 'Key' . strtoupper($ch); $vk = $ord - 32;}
else if ($ord >= 48 && $ord <= 57) {$code = 'Digit' . $ch; $vk = $ord;}}
return array(
tagui_key_event('keyDown', $key, $code, $vk, $ch),
tagui_key_event('keyUp', $key, $code, $vk, null));}

function tagui_clear_events() {
$meta = defined('PHP_OS_FAMILY') && PHP_OS_FAMILY === 'Darwin';
$mod = $meta ? array('Meta', 'MetaLeft', 91, 4) : array('Control', 'ControlLeft', 17, 2);
$bit = $mod[3];
return array(
tagui_key_event('keyDown', $mod[0], $mod[1], $mod[2], null, $bit),
tagui_key_event('keyDown', 'a', 'KeyA', 65, null, $bit),
tagui_key_event('keyUp', 'a', 'KeyA', 65, null, $bit),
tagui_key_event('keyUp', $mod[0], $mod[1], $mod[2], null, 0),
tagui_key_event('keyDown', 'Backspace', 'Backspace', 8, null),
tagui_key_event('keyUp', 'Backspace', 'Backspace', 8, null));}

function tagui_type_events($text, $clear = false) {
$events = $clear ? tagui_clear_events() : array();
$names = implode('|', array_keys(array(
'enter' => 1, 'tab' => 1, 'space' => 1, 'esc' => 1, 'escape' => 1, 'backspace' => 1, 'bs' => 1,
'delete' => 1, 'del' => 1, 'up' => 1, 'down' => 1, 'left' => 1, 'right' => 1,
'home' => 1, 'end' => 1, 'pageup' => 1, 'pagedown' => 1, 'insert' => 1)));
$parts = preg_split('/(\[(?:' . $names . ')\])/i', (string)$text, -1, PREG_SPLIT_DELIM_CAPTURE);
if (!is_array($parts)) $parts = array((string)$text);
foreach ($parts as $part) {
if ($part === '') continue;
if (preg_match('/^\[(' . $names . ')\]$/i', $part, $match)) {
$events = array_merge($events, tagui_special_events($match[1]));
continue;}
$chars = preg_split('//u', $part, -1, PREG_SPLIT_NO_EMPTY);
if (!is_array($chars)) $chars = array($part);
foreach ($chars as $ch) {
if ($ch === "\r") continue;
if ($ch === "\n") {$events = array_merge($events, tagui_special_events('enter')); continue;}
$events = array_merge($events, tagui_char_events($ch));}}
return $events;}

require_once __DIR__ . '/tagui_runtime.php';

/* -------------------------------------------------------------------------- */
/* Chrome / Edge                                                              */
/* -------------------------------------------------------------------------- */

class TaguiChrome {
public $port = 0;
public $profile = '';
public $headless = true;
public $timeout = 10.0;
public $ws_url = '';
public $frame_dx = 0;
public $frame_dy = 0;
public $network_urls = array();
public $surface_shots = true;
public $download_path = '';
public $persistent_profile = false;
private $process = null;
private $client = null;
private $next_id = 0;
private $log_path = '';
private $frame_stack = array();
private $popup_stack = array();
private $released_targets = array();
private $expect_spawn = false;
private $following = false;

function __construct() {}

function official_profile_path() {
return dirname(__FILE__) . DIRECTORY_SEPARATOR . 'chrome' . DIRECTORY_SEPARATOR . 'tagui_user_profile';}

function launch_plan($binary, $port, $headless) {
$env_profile = getenv('TAGUI_CHROME_PROFILE');
$custom = is_string($env_profile) && trim($env_profile) !== '';
$isolated = getenv('TAGUI_CHROME_ISOLATED') === '1' && !$custom;
if ($isolated) {
$profile = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR .
'tagui_modern_profile_' . getmypid() . '_' . bin2hex(random_bytes(4));
return array('profile' => $profile, 'persistent' => false, 'args' => $this->isolated_args($binary, $port, $profile, $headless));}
$profile = $custom ? $env_profile : $this->official_profile_path();
$args = array(
$binary,
'--remote-debugging-port=' . $port,
'--user-data-dir=' . $profile,
'--window-size=1366,842');
if ($headless) {array_push($args, '--headless', '--disable-gpu');}
$args[] = 'about:blank';
return array('profile' => $profile, 'persistent' => true, 'args' => $args);}

function isolated_args($binary, $port, $profile, $headless) {
$args = array(
$binary,
'--remote-debugging-port=' . $port,
'--remote-allow-origins=*',
'--user-data-dir=' . $profile,
'--window-size=1366,768',
'--no-first-run',
'--no-default-browser-check',
'--disable-extensions',
'--disable-component-extensions-with-background-pages',
'--disable-default-apps',
'--disable-sync',
'--disable-background-networking',
'--disable-popup-blocking',
'--no-service-autorun',
'--metrics-recording-only',
'--hide-scrollbars',
'--allow-file-access-from-files');
$forced_scale = getenv('TAGUI_DEVICE_SCALE');
$scale_is_set = is_string($forced_scale) && is_numeric($forced_scale) && (float)$forced_scale > 0;
if ($headless) {
$args[] = '--force-device-scale-factor=' . ($scale_is_set ? $forced_scale : '1');}
else if ($scale_is_set) {$args[] = '--force-device-scale-factor=' . $forced_scale;}
$position = getenv('TAGUI_CHROME_POSITION');
if (is_string($position) && preg_match('/^-?\d+,-?\d+$/', $position) === 1) $args[] = '--window-position=' . $position;
if ($headless) {array_push($args, '--headless=new', '--disable-gpu');}
if (getenv('TAGUI_CHROME_SANDBOX') !== '1') {
array_push($args, '--no-sandbox', '--disable-dev-shm-usage');}
$args[] = 'about:blank';
return $args;}

function start($headless) {
$this->headless = $headless;
$binary = $this->find_binary();
if ($binary === '') {
throw new RuntimeException('cannot find Chrome or Edge. Set TAGUI_CHROME_BIN to the browser executable');}
$this->port = $this->free_port();
$plan = $this->launch_plan($binary, $this->port, $headless);
$this->profile = $plan['profile'];
$this->persistent_profile = $plan['persistent'];
if (!is_dir($this->profile) && !mkdir($this->profile, 0700, true) && !is_dir($this->profile)) {
throw new RuntimeException('cannot create Chrome profile ' . $this->profile);}
if ($this->persistent_profile) {
$this->log_path = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'tagui_chrome_' . getmypid() . '.log';}
else {$this->log_path = $this->profile . DIRECTORY_SEPARATOR . 'chrome.log';}
$args = $plan['args'];
$descriptor = array(
0 => array('file', '/dev/null', 'r'),
1 => array('file', $this->log_path, 'a'),
2 => array('file', $this->log_path, 'a'));
if (DIRECTORY_SEPARATOR === '\\') {$descriptor[0] = array('file', 'NUL', 'r');}
$this->process = proc_open($args, $descriptor, $pipes);
if (!is_resource($this->process)) {
throw new RuntimeException('cannot start ' . $binary);}
$ws = $this->wait_for_page_socket();
$this->ws_url = $ws;
$this->client = new WebSocket\Client($ws, array('timeout' => 30, 'fragment_size' => 65536));
$this->command('Page.enable', new stdClass());
$this->command('Runtime.enable', new stdClass());
try {$this->command('Network.enable', new stdClass());} catch (Exception $e) {}
try {$this->set_download_path(sys_get_temp_dir());} catch (Exception $e) {}
$this->command('Page.bringToFront', new stdClass());}

function stop() {
if ($this->client !== null) {
try {$this->client->close();} catch (Exception $e) {}
$this->client = null;}
if (is_resource($this->process)) {
$status = proc_get_status($this->process);
if (!empty($status['pid'])) {
if (function_exists('posix_kill')) {@posix_kill((int)$status['pid'], 15);}
else {proc_terminate($this->process);}
}
proc_close($this->process);
$this->process = null;}
if (!$this->persistent_profile && $this->profile !== '' && $this->profile !== '/' && str_contains($this->profile, 'tagui_modern_profile_')) {
if (DIRECTORY_SEPARATOR !== '\\') {
@exec('pkill -f ' . escapeshellarg($this->profile) . ' >/dev/null 2>&1');}
$this->remove_tree($this->profile);}
$this->profile = '';
$this->persistent_profile = false;}

function navigate($url) {
$this->follow_newest_page();
$this->command('Page.navigate', array('url' => $url));
$deadline = microtime(true) + max($this->timeout, 20);
$last = '';
while (microtime(true) < $deadline) {
$state = $this->evaluate('document.readyState');
$href = $this->evaluate('document.location.href');
$last = is_string($href) ? $href : '';
if ($state === 'complete' && str_starts_with($last, 'chrome-error://')) {
throw new RuntimeException('cannot open ' . $url . '. Chrome showed an error page. file:// paths are opened at their real path, so a macOS /tmp symlink becomes /private/tmp.');}
if ($state === 'complete' && $last !== '' && $last !== 'about:blank') {
usleep(150000);
return $last;}
usleep(100000);}
if ($last === '' || $last === 'about:blank') {
throw new RuntimeException('navigation timed out for ' . $url);}
return $last;}

function evaluate($expression) {
$this->follow_newest_page();
$result = $this->command('Runtime.evaluate', array(
'expression' => $expression,
'returnByValue' => true));
if (!empty($result['exceptionDetails'])) {
$detail = $result['exceptionDetails'];
$text = '';
if (isset($detail['exception']['description'])) $text = $detail['exception']['description'];
else if (isset($detail['text'])) $text = $detail['text'];
throw new RuntimeException('page script failed: ' . $text);}
if (!isset($result['result'])) return null;
if (array_key_exists('value', $result['result'])) return $result['result']['value'];
return null;}

function title() {
$value = $this->evaluate('document.title');
return is_string($value) ? $value : '';}

function url() {
$value = $this->evaluate('document.location.href');
if ($value === 'data:text/html,chromewebdata' || $value === null) return 'about:blank';
return is_string($value) ? $value : '';}

function body_text() {
$value = $this->evaluate('document.body ? (document.body.innerText || document.body.textContent || "") : ""');
return is_string($value) ? $value : '';}

function page_html() {
$value = $this->evaluate('document.documentElement ? document.documentElement.outerHTML : ""');
return is_string($value) ? $value : '';}

function locate($locator) {
$payload = json_encode($locator, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
$script = "(function(){\n" . tagui_page_finder_js() . "\nreturn taguiDescribe(" . $payload . ");})()";
$info = $this->evaluate($script);
if (!is_array($info) || empty($info['found'])) return null;
$info['x'] = (float)$info['x'] + $this->frame_dx;
$info['y'] = (float)$info['y'] + $this->frame_dy;
$info['left'] = (float)$info['left'] + $this->frame_dx;
$info['top'] = (float)$info['top'] + $this->frame_dy;
return $info;}

function find($locator, $live) {
$deadline = microtime(true) + ($live ? 0 : $this->timeout);
do {
$info = $this->locate($locator);
if ($info !== null) return $info;
if ($live) return null;
usleep(200000);
} while (microtime(true) < $deadline);
return null;}

function click_at($x, $y, $button, $clicks, $may_open = false) {
$this->command('Input.dispatchMouseEvent', array(
'type' => 'mouseMoved', 'x' => $x, 'y' => $y, 'button' => 'none', 'clickCount' => 0));
$this->command('Input.dispatchMouseEvent', array(
'type' => 'mousePressed', 'x' => $x, 'y' => $y, 'button' => $button, 'clickCount' => $clicks));
$this->command('Input.dispatchMouseEvent', array(
'type' => 'mouseReleased', 'x' => $x, 'y' => $y, 'button' => $button, 'clickCount' => $clicks));
if ($may_open) $this->expect_spawn = true;}

function hover_at($x, $y) {
$this->command('Input.dispatchMouseEvent', array(
'type' => 'mouseMoved', 'x' => $x, 'y' => $y, 'button' => 'none', 'clickCount' => 0));}

function type_into($locator, $text) {
$clear = false;
if (str_starts_with(strtolower($text), '[clear]')) {$clear = true; $text = substr($text, 7);}
$payload = json_encode($locator, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
$script = "(function(){\n" . tagui_page_finder_js() . "\nvar found = taguiElement(" . $payload . "); var el = found.el; if (!el) return null;"
. "try {el.scrollIntoView({block:'center', inline:'center'});} catch (e) {} el.focus();"
. "var r = el.getBoundingClientRect(); return {x: r.left + r.width / 2, y: r.top + r.height / 2};})()";
$point = $this->evaluate($script);
if (!is_array($point) || !isset($point['x']) || !isset($point['y'])) return false;
$this->click_at((float)$point['x'] + $this->frame_dx, (float)$point['y'] + $this->frame_dy, 'left', 1);
foreach (tagui_type_events($text, $clear) as $event) {
$this->command('Input.dispatchKeyEvent', $event);}
return true;}

function device_scale() {
$value = $this->evaluate('window.devicePixelRatio || 1');
$scale = is_numeric($value) ? (float)$value : 1.0;
if ($scale < 0.5) $scale = 0.5;
if ($scale > 4) $scale = 4.0;
return $scale;}

function screenshot($path, $clip) {
$this->follow_newest_page();
if (!$this->surface_shots) return $this->capture_png($path, $clip, false, 15);
try {return $this->capture_png($path, $clip, true, 8);}
catch (RuntimeException $e) {
if (!str_contains($e->getMessage(), 'Page.captureScreenshot')) throw $e;
$this->surface_shots = false;
try {$this->command('Page.bringToFront', new stdClass());} catch (Exception $ignored) {}
return $this->capture_png($path, $clip, false, 15);}}

private function capture_png($path, $clip, $from_surface, $timeout) {
$params = array('format' => 'png', 'fromSurface' => $from_surface);
if ($clip === null) $clip = $this->viewport_clip();
if (is_array($clip)) {
$clip['scale'] = 1;
$params['clip'] = $clip;}
$result = $this->command('Page.captureScreenshot', $params, $timeout);
if (empty($result['data'])) return false;
$bytes = base64_decode($result['data'], true);
if ($bytes === false) return false;
return file_put_contents($path, $bytes) !== false;}

private function viewport_clip() {
try {$metrics = $this->command('Page.getLayoutMetrics', new stdClass());}
catch (Exception $e) {return null;}
$viewport = null;
if (isset($metrics['cssVisualViewport']) && is_array($metrics['cssVisualViewport'])) $viewport = $metrics['cssVisualViewport'];
else if (isset($metrics['cssLayoutViewport']) && is_array($metrics['cssLayoutViewport'])) $viewport = $metrics['cssLayoutViewport'];
if (!is_array($viewport) || empty($viewport['clientWidth']) || empty($viewport['clientHeight'])) return null;
return array(
'x' => isset($viewport['pageX']) ? (float)$viewport['pageX'] : 0,
'y' => isset($viewport['pageY']) ? (float)$viewport['pageY'] : 0,
'width' => (float)$viewport['clientWidth'],
'height' => (float)$viewport['clientHeight']);}

function capture_tabs($directory) {
$directory = (string)$directory;
if ($directory === '' || !is_dir($directory)) return 0;
$saved = 0;
$n = 0;
foreach ($this->failure_pages() as $page) {
if ($n >= 8) break;
$n++;
$name = $n === 1 ? 'failure_tab.png' : ('failure_tab_' . $n . '.png');
$path = $directory . DIRECTORY_SEPARATOR . $name;
$ws = isset($page['webSocketDebuggerUrl']) ? (string)$page['webSocketDebuggerUrl'] : '';
try {if ($this->capture_socket_png($ws, $path)) $saved++;}
catch (Exception $e) {}}
if ($saved === 0 && $this->client !== null) {
try {if ($this->screenshot($directory . DIRECTORY_SEPARATOR . 'failure_tab.png', null)) $saved++;}
catch (Exception $e) {}}
return $saved;}

private function failure_pages() {
$active = (string)$this->ws_url;
$first = array();
$rest = array();
foreach ($this->list_pages() as $target) {
if (!is_array($target) || !isset($target['type']) || $target['type'] !== 'page') continue;
if (empty($target['webSocketDebuggerUrl'])) continue;
$url = isset($target['url']) ? (string)$target['url'] : '';
if (str_starts_with($url, 'chrome://') || str_starts_with($url, 'devtools://') || str_starts_with($url, 'chrome-extension://')) continue;
if ((string)$target['webSocketDebuggerUrl'] === $active) $first[] = $target;
else $rest[] = $target;}
return array_merge($first, $rest);}

private function capture_socket_png($ws, $path) {
if ($ws === '') return false;
$client = new WebSocket\Client($ws, array('timeout' => 8, 'fragment_size' => 65536));
try {
$this->socket_call($client, 1, 'Page.enable', new stdClass(), 4);
try {$shot = $this->socket_call($client, 2, 'Page.captureScreenshot', array('format' => 'png', 'fromSurface' => true), 6);}
catch (Exception $e) {
if (!str_contains($e->getMessage(), 'Page.captureScreenshot')) throw $e;
$shot = array();}
if (empty($shot['data'])) {
$shot = $this->socket_call($client, 3, 'Page.captureScreenshot', array('format' => 'png', 'fromSurface' => false), 8);}
if (empty($shot['data'])) return false;
$bytes = base64_decode($shot['data'], true);
if (!is_string($bytes) || strlen($bytes) < 32 || strncmp($bytes, "\x89PNG\r\n\x1a\n", 8) !== 0) return false;
return file_put_contents($path, $bytes) !== false;}
finally {try {$client->close();} catch (Exception $e) {}}}

private function socket_call($client, $id, $method, $params, $timeout) {
$body = json_encode(array('id' => $id, 'method' => $method, 'params' => $params));
$client->send($body);
if (method_exists($client, 'setTimeout')) $client->setTimeout((int)ceil(max(1, (float)$timeout)));
$deadline = microtime(true) + max(1, (float)$timeout);
while (microtime(true) < $deadline) {
try {$raw = $client->receive();}
catch (Exception $e) {continue;}
$message = json_decode($raw, true);
if (!is_array($message) || !isset($message['id']) || (int)$message['id'] !== (int)$id) continue;
if (isset($message['error'])) throw new RuntimeException($method . ' failed: ' . json_encode($message['error']));
return isset($message['result']) && is_array($message['result']) ? $message['result'] : array();}
throw new RuntimeException('timed out waiting for ' . $method);}

function set_download_path($dir) {
$dir = str_replace('\\', '/', (string)$dir);
$this->download_path = $dir;
$allow = array('behavior' => 'allow', 'downloadPath' => $dir);
try {$this->command('Browser.setDownloadBehavior', $allow); return;}
catch (Exception $e) {}
$this->command('Page.setDownloadBehavior', $allow);}

function command($method, $params, $timeout = 30) {
if ($params instanceof stdClass) $encoded_params = new stdClass();
else $encoded_params = $params;
$this->next_id++;
$id = $this->next_id;
$body = json_encode(array('id' => $id, 'method' => $method, 'params' => $encoded_params));
$this->client->send($body);
$wait = max(1, (float)$timeout);
if ($this->client !== null && method_exists($this->client, 'setTimeout')) $this->client->setTimeout((int)ceil($wait));
try {
$deadline = microtime(true) + $wait;
while (microtime(true) < $deadline) {
try {$raw = $this->client->receive();}
catch (Exception $e) {continue;}
$message = json_decode($raw, true);
if (is_array($message) && isset($message['method']) && $message['method'] === 'Network.responseReceived') {
$net_url = '';
if (isset($message['params']['response']['url'])) $net_url = (string)$message['params']['response']['url'];
if ($net_url !== '') {
$net_type = '';
if (isset($message['params']['type'])) $net_type = (string)$message['params']['type'];
$this->note_network($net_url, $net_type);}}
if (is_array($message) && isset($message['method']) && $message['method'] === 'Page.javascriptDialogOpening') {
$this->accept_dialog();
continue;}
if (!is_array($message) || !isset($message['id']) || (int)$message['id'] !== $id) continue;
if (isset($message['error'])) {
throw new RuntimeException($method . ' failed: ' . json_encode($message['error']));}
return isset($message['result']) ? $message['result'] : array();}
throw new RuntimeException('timed out waiting for ' . $method);}
finally {if ($this->client !== null && method_exists($this->client, 'setTimeout')) $this->client->setTimeout(30);}}

private function wait_for_page_socket() {
$deadline = microtime(true) + 20;
$previous_timeout = ini_get('default_socket_timeout');
ini_set('default_socket_timeout', '1');
while (microtime(true) < $deadline) {
$context = stream_context_create(array('http' => array('timeout' => 1)));
$raw = @file_get_contents('http://127.0.0.1:' . $this->port . '/json/list', false, $context);
if ($raw) {
$list = json_decode($raw, true);
$fallback = '';
if (is_array($list)) {
foreach ($list as $target) {
if (!isset($target['type']) || $target['type'] !== 'page' || empty($target['webSocketDebuggerUrl'])) continue;
if (isset($target['url']) && $target['url'] === 'about:blank') {
ini_set('default_socket_timeout', '30');
return $target['webSocketDebuggerUrl'];}
if ($fallback === '') $fallback = $target['webSocketDebuggerUrl'];}
if ($fallback !== '') {ini_set('default_socket_timeout', '30'); return $fallback;}}}
$status = is_resource($this->process) ? proc_get_status($this->process) : null;
if (is_array($status) && !$status['running']) break;
usleep(100000);}
ini_set('default_socket_timeout', (string)$previous_timeout);
$log = '';
if ($this->log_path !== '' && is_file($this->log_path)) $log = trim((string)file_get_contents($this->log_path));
if (strlen($log) > 500) $log = substr($log, -500);
throw new RuntimeException('Chrome did not open a DevTools socket on port ' . $this->port . ($log !== '' ? ' ' . $log : ''));}

private function find_binary() {
$from_env = getenv('TAGUI_CHROME_BIN');
if (is_string($from_env) && $from_env !== '' && is_file($from_env)) return tagui_unwrap_chromium($from_env);
$candidates = array(
'google-chrome-stable', '/opt/google/chrome/chrome',
'/usr/lib/chromium/chromium', '/usr/lib/chromium-browser/chromium-browser',
'chromium', 'chromium-browser',
'microsoft-edge-stable', 'microsoft-edge', 'google-chrome',
// App-bundle paths are the same on Apple Silicon and Intel macOS.
'/Applications/Google Chrome.app/Contents/MacOS/Google Chrome',
'/Applications/Google Chrome Canary.app/Contents/MacOS/Google Chrome Canary',
'/Applications/Microsoft Edge.app/Contents/MacOS/Microsoft Edge',
'/Applications/Microsoft Edge Canary.app/Contents/MacOS/Microsoft Edge Canary',
'/Applications/Chromium.app/Contents/MacOS/Chromium',
'/Applications/Brave Browser.app/Contents/MacOS/Brave Browser');
if (getenv('PROGRAMFILES')) {
$pf = getenv('PROGRAMFILES'); $pf86 = getenv('PROGRAMFILES(X86)'); $local = getenv('LOCALAPPDATA');
$candidates[] = $pf . '\\Google\\Chrome\\Application\\chrome.exe';
$candidates[] = $pf . '\\Microsoft\\Edge\\Application\\msedge.exe';
if ($pf86) {
$candidates[] = $pf86 . '\\Google\\Chrome\\Application\\chrome.exe';
$candidates[] = $pf86 . '\\Microsoft\\Edge\\Application\\msedge.exe';}
if ($local) {
$candidates[] = $local . '\\Google\\Chrome\\Application\\chrome.exe';
$candidates[] = $local . '\\Microsoft\\Edge\\Application\\msedge.exe';}}
foreach ($candidates as $candidate) {
if ($candidate === '') continue;
if (str_contains($candidate, DIRECTORY_SEPARATOR) || str_contains($candidate, '/')) {
if (is_file($candidate)) return tagui_unwrap_chromium($candidate);
continue;}
$found = $this->which($candidate);
if ($found !== '') return tagui_unwrap_chromium($found);}
return '';}

function push_frame($name) {
$payload = json_encode($name, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
$script = "(function(){window.__tagui_roots = window.__tagui_roots || [document];"
. "var root = window.__tagui_roots[window.__tagui_roots.length - 1];"
. "var frames = root.querySelectorAll('iframe,frame'); var frame = null; var wanted = " . $payload . ";"
. "for (var i = 0; i < frames.length; i++) {if (frames[i].getAttribute('name') === wanted || frames[i].id === wanted) {frame = frames[i]; break;}}"
. "if (!frame || !frame.contentDocument) return null; var rect = frame.getBoundingClientRect();"
. "window.__tagui_roots.push(frame.contentDocument); return {x: rect.left, y: rect.top};})()";
$deadline = microtime(true) + max((float)$this->timeout, 1);
$rect = null;
do {
$rect = $this->evaluate($script);
if (is_array($rect) && isset($rect['x'])) break;
$rect = null;
if (microtime(true) >= $deadline) break;
usleep(200000);
} while (true);
if (!is_array($rect)) return false;
$this->frame_stack[] = $rect;
$this->frame_dx += (float)$rect['x'];
$this->frame_dy += (float)$rect['y'];
return true;}

function pop_frame() {
$rect = array_pop($this->frame_stack);
if (!is_array($rect)) return;
$this->frame_dx -= (float)$rect['x'];
$this->frame_dy -= (float)$rect['y'];
$this->evaluate('if (window.__tagui_roots && window.__tagui_roots.length > 1) window.__tagui_roots.pop(); true');}

function push_popup($keyword, $timeout) {
$deadline = microtime(true) + max((float)$timeout, 1);
$match = null;
do {
$match = $this->find_popup($keyword);
if ($match !== null) break;
usleep(200000);
} while (microtime(true) < $deadline);
if ($match === null) return false;
$this->popup_stack[] = array(
'client' => $this->client, 'ws' => $this->ws_url, 'dx' => $this->frame_dx, 'dy' => $this->frame_dy, 'frames' => $this->frame_stack);
$this->frame_dx = 0; $this->frame_dy = 0; $this->frame_stack = array();
$this->ws_url = $match['webSocketDebuggerUrl'];
$this->client = new WebSocket\Client($this->ws_url, array('timeout' => 30, 'fragment_size' => 65536));
$this->command('Page.enable', new stdClass());
$this->command('Runtime.enable', new stdClass());
try {$this->command('Page.bringToFront', new stdClass());} catch (Exception $e) {}
return true;}

function pop_popup() {
$left = $this->page_target_id();
if ($left !== '') $this->released_targets[$left] = true;
$saved = array_pop($this->popup_stack);
if (!is_array($saved)) return;
try {if ($this->client !== null) $this->client->close();} catch (Exception $e) {}
$this->client = $saved['client'];
$this->ws_url = $saved['ws'];
$this->frame_dx = $saved['dx'];
$this->frame_dy = $saved['dy'];
$this->frame_stack = $saved['frames'];}

function set_file($locator, $filename) {
$payload = json_encode($locator, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
$script = "(function(){\n" . tagui_page_finder_js() . "\nreturn taguiElement(" . $payload . ").el || null;})()";
$result = $this->command('Runtime.evaluate', array('expression' => $script, 'returnByValue' => false));
if (empty($result['result']['objectId'])) return false;
try {$this->command('DOM.enable', new stdClass());} catch (Exception $e) {}
try {$this->command('DOM.getDocument', array('depth' => 0));} catch (Exception $e) {}
$node = $this->command('DOM.requestNode', array('objectId' => $result['result']['objectId']));
if (empty($node['nodeId'])) return false;
$this->command('DOM.setFileInputFiles', array('files' => array($filename), 'nodeId' => $node['nodeId']));
return true;}

function note_network($url, $type = 'Other') {
$url = (string)$url;
if ($url === '') return;
$this->network_urls[] = array('url' => $url, 'type' => (string)$type);
if (count($this->network_urls) > 200) array_shift($this->network_urls);}

function await_network_url($keyword, $timeout) {
$deadline = microtime(true) + max((float)$timeout, 1);
do {
$found = $this->match_network($keyword);
if ($found !== '') return $found;
try {$this->command('Runtime.evaluate', array('expression' => '1', 'returnByValue' => true));} catch (Exception $e) {}
usleep(100000);
} while (microtime(true) < $deadline);
return $this->match_network($keyword);}

private function match_network($keyword) {
$fallback = '';
for ($i = count($this->network_urls) - 1; $i >= 0; $i--) {
$entry = $this->network_urls[$i];
$url = is_array($entry) ? $entry['url'] : (string)$entry;
$type = is_array($entry) && isset($entry['type']) ? $entry['type'] : '';
if (stripos($url, $keyword) === false) continue;
if (strcasecmp($type, 'Document') === 0) return $url;
if ($fallback === '') $fallback = $url;}
return $fallback;}

function follow_newest_page() {
if (getenv('TAGUI_MODERN_FOLLOW_TABS') !== '1') return;
if ($this->following || $this->client === null) return;
$this->following = true;
try {
$current = $this->page_target_id();
$deadline = microtime(true) + ($this->expect_spawn ? 1.0 : 0);
$quiet = 0;
while (true) {
$leaf = $this->newest_spawn_id($this->target_infos(), $current);
if ($leaf !== '') {
$this->expect_spawn = false;
$ws = $this->page_socket($leaf);
if ($ws !== '' && $ws !== $this->ws_url) $this->switch_page($ws);
return;}
if (!$this->expect_spawn || microtime(true) >= $deadline || $quiet >= 2) {
$this->expect_spawn = false;
return;}
$quiet++;
usleep(150000);}}
finally {$this->following = false;}}

private function newest_spawn_id($infos, $current) {
$children = array();
foreach ($infos as $info) {
if (!is_array($info) || !isset($info['type']) || $info['type'] !== 'page') continue;
$id = isset($info['targetId']) ? (string)$info['targetId'] : '';
if ($id === '' || isset($this->released_targets[$id])) continue;
$url = isset($info['url']) ? (string)$info['url'] : '';
if (str_starts_with($url, 'chrome://') || str_starts_with($url, 'devtools://') || str_starts_with($url, 'chrome-extension://')) continue;
$opener = isset($info['openerId']) ? (string)$info['openerId'] : '';
if ($opener === '') continue;
if (!isset($children[$opener])) $children[$opener] = array();
$children[$opener][] = $id;}
$cursor = $current;
$guard = 0;
while ($guard < 20 && isset($children[$cursor])) {
$cursor = $children[$cursor][count($children[$cursor]) - 1];
$guard++;}
if ($cursor === $current) return '';
return $cursor;}

private function target_infos() {
try {$result = $this->command('Target.getTargets', new stdClass());}
catch (Exception $e) {return array();}
if (!is_array($result) || !isset($result['targetInfos']) || !is_array($result['targetInfos'])) return array();
return $result['targetInfos'];}

private function page_target_id() {
if (preg_match('#/([^/]+)$#', (string)$this->ws_url, $match)) return $match[1];
return '';}

private function page_socket($id) {
foreach ($this->list_pages() as $target) {
if (isset($target['id']) && (string)$target['id'] === $id && !empty($target['webSocketDebuggerUrl'])) {
return (string)$target['webSocketDebuggerUrl'];}}
return '';}

private function switch_page($ws) {
$old = $this->client;
$this->ws_url = $ws;
$this->frame_dx = 0;
$this->frame_dy = 0;
$this->frame_stack = array();
$this->client = new WebSocket\Client($ws, array('timeout' => 30, 'fragment_size' => 65536));
$this->command('Page.enable', new stdClass());
$this->command('Runtime.enable', new stdClass());
try {$this->command('Network.enable', new stdClass());} catch (Exception $e) {}
try {$this->command('Page.bringToFront', new stdClass());} catch (Exception $e) {}
$stacked = false;
foreach ($this->popup_stack as $saved) {
if (is_array($saved) && isset($saved['client']) && $saved['client'] === $old) {$stacked = true; break;}}
if (!$stacked && $old !== null) {try {$old->close();} catch (Exception $e) {}}}

private function find_popup($keyword) {
$list = $this->list_pages();
foreach ($list as $target) {
if (!isset($target['type']) || $target['type'] !== 'page' || empty($target['webSocketDebuggerUrl'])) continue;
if ($target['webSocketDebuggerUrl'] === $this->ws_url) continue;
$url = isset($target['url']) ? (string)$target['url'] : '';
if ($this->popup_url_matches($url, $keyword)) return $target;}
return null;}

private function popup_url_matches($url, $keyword) {
if ($url === '' || strcasecmp($url, 'about:blank') === 0) return false;
if (str_starts_with($url, 'chrome://') || str_starts_with($url, 'devtools://') || str_starts_with($url, 'chrome-extension://') || str_starts_with($url, 'chrome-error://')) return false;
return stripos($url, $keyword) !== false;}

private function accept_dialog() {
$this->next_id++;
$body = json_encode(array(
'id' => $this->next_id,
'method' => 'Page.handleJavaScriptDialog',
'params' => array('accept' => true, 'promptText' => '')));
try {$this->client->send($body);} catch (Exception $e) {}
}

private function list_pages() {
$context = stream_context_create(array('http' => array('timeout' => 2)));
$raw = @file_get_contents('http://127.0.0.1:' . $this->port . '/json/list', false, $context);
$list = json_decode((string)$raw, true);
return is_array($list) ? $list : array();}

private function which($name) {
$paths = explode(PATH_SEPARATOR, (string)getenv('PATH'));
foreach ($paths as $dir) {
$file = $dir . DIRECTORY_SEPARATOR . $name;
if (is_file($file) && is_executable($file)) return $file;}
return '';}

private function free_port() {
$server = stream_socket_server('tcp://127.0.0.1:0', $errno, $errstr);
if ($server === false) throw new RuntimeException('cannot reserve a debugging port');
$name = stream_socket_get_name($server, false);
fclose($server);
$port = (int)substr($name, strrpos($name, ':') + 1);
if ($port < 1) throw new RuntimeException('cannot reserve a debugging port');
return $port;}

private function remove_tree($dir) {
if (!is_dir($dir)) return;
$items = scandir($dir);
if ($items === false) return;
foreach ($items as $item) {
if ($item === '.' || $item === '..') continue;
$path = $dir . DIRECTORY_SEPARATOR . $item;
if (is_dir($path)) $this->remove_tree($path); else @unlink($path);}
@rmdir($dir);}}

/* -------------------------------------------------------------------------- */
/* Expressions                                                                */
/* -------------------------------------------------------------------------- */

class TaguiExprError extends RuntimeException {}

class TaguiExpr {
private $tokens = array();
private $index = 0;
private $vars;
private $calls;

private $missing = false;

function __construct($vars, $calls, $missing = false) {
$this->vars = $vars; $this->calls = $calls; $this->missing = $missing;}

function evaluate($source) {
$this->tokens = $this->tokenize($source);
$this->index = 0;
if (count($this->tokens) === 0) return '';
$value = $this->parse_or();
if ($this->index < count($this->tokens)) {
throw new TaguiExprError('cannot understand expression ' . $source);}
return $value;}

private function tokenize($source) {
$tokens = array(); $i = 0; $n = strlen($source);
while ($i < $n) {
$c = $source[$i];
if (ctype_space($c)) {$i++; continue;}
if ($c === '"' || $c === "'") {
$quote = $c; $i++; $buf = '';
while ($i < $n && $source[$i] !== $quote) {
if ($source[$i] === '\\' && $i + 1 < $n) {$buf .= $source[$i + 1]; $i += 2; continue;}
$buf .= $source[$i]; $i++;}
if ($i >= $n) throw new TaguiExprError('unterminated string');
$i++; $tokens[] = array('str', $buf); continue;}
if (ctype_digit($c) || ($c === '.' && $i + 1 < $n && ctype_digit($source[$i + 1]))) {
$j = $i; while ($j < $n && (ctype_digit($source[$j]) || $source[$j] === '.')) $j++;
$tokens[] = array('num', substr($source, $i, $j - $i)); $i = $j; continue;}
if (ctype_alpha($c) || $c === '_') {
$j = $i; while ($j < $n && (ctype_alnum($source[$j]) || $source[$j] === '_')) $j++;
$tokens[] = array('id', substr($source, $i, $j - $i)); $i = $j; continue;}
$two = substr($source, $i, 2);
if (in_array($two, array('==', '!=', '>=', '<=', '&&', '||'), true)) {
$tokens[] = array('op', $two); $i += 2; continue;}
if (str_contains('+-*/<>().,!', $c)) {
$kind = ($c === '(' || $c === ')' || $c === '.' || $c === ',') ? $c : 'op';
$tokens[] = array($kind, $c); $i++; continue;}
throw new TaguiExprError('unexpected character in expression');}
return $tokens;}

private function peek() {return $this->index < count($this->tokens) ? $this->tokens[$this->index] : null;}
private function pop() {$token = $this->peek(); if ($token === null) throw new TaguiExprError('unexpected end of expression'); $this->index++; return $token;}
private function is_op($op) {$token = $this->peek(); return $token !== null && $token[0] === 'op' && $token[1] === $op;}
private function eat_kind($kind) {
$token = $this->pop(); if ($token[0] !== $kind) throw new TaguiExprError('expected ' . $kind); return $token[1];}

private function parse_or() {
$value = $this->parse_and();
while ($this->is_op('||')) {$this->pop(); $right = $this->parse_and(); $value = $this->truthy($value) || $this->truthy($right);}
return $value;}
private function parse_and() {
$value = $this->parse_cmp();
while ($this->is_op('&&')) {$this->pop(); $right = $this->parse_cmp(); $value = $this->truthy($value) && $this->truthy($right);}
return $value;}
private function parse_cmp() {
$value = $this->parse_add();
while (true) {
$token = $this->peek();
if ($token === null || $token[0] !== 'op' || !in_array($token[1], array('==','!=','>=','<=','>','<'), true)) break;
$this->pop(); $right = $this->parse_add(); $value = $this->compare($token[1], $value, $right);}
return $value;}
private function parse_add() {
$value = $this->parse_mul();
while ($this->is_op('+') || $this->is_op('-')) {
$op = $this->pop()[1]; $right = $this->parse_mul();
if ($op === '+') $value = $this->plus($value, $right); else $value = $this->num($value) - $this->num($right);}
return $value;}
private function parse_mul() {
$value = $this->parse_unary();
while ($this->is_op('*') || $this->is_op('/')) {
$op = $this->pop()[1]; $right = $this->parse_unary();
$value = $op === '*' ? $this->num($value) * $this->num($right) : $this->num($value) / $this->num($right);}
return $value;}
private function parse_unary() {
if ($this->is_op('-')) {$this->pop(); return -$this->num($this->parse_unary());}
if ($this->is_op('+')) {$this->pop(); return $this->num($this->parse_unary());}
if ($this->is_op('!')) {$this->pop(); return !$this->truthy($this->parse_unary());}
return $this->parse_postfix();}
private function parse_postfix() {
$value = $this->parse_primary();
while (true) {
$token = $this->peek();
if ($token === null || $token[0] !== '.') break;
$this->pop(); $name = $this->eat_kind('id'); $this->eat_kind('('); $args = $this->parse_args();
$value = $this->method($value, $name, $args);}
return $value;}
private function parse_primary() {
$token = $this->pop();
if ($token[0] === 'str') return $token[1];
if ($token[0] === 'num') return str_contains($token[1], '.') ? (float)$token[1] : (int)$token[1];
if ($token[0] === '(') {$value = $this->parse_or(); $this->eat_kind(')'); return $value;}
if ($token[0] === 'id') {
$name = $token[1];
$next = $this->peek();
if ($next !== null && $next[0] === '(') {
$this->pop(); $args = $this->parse_args();
if (!isset($this->calls[$name])) throw new TaguiExprError('unknown function ' . $name);
return call_user_func($this->calls[$name], $args);}
if (is_array($this->vars) && array_key_exists($name, $this->vars)) return $this->vars[$name];
if ($name === 'true') return true; if ($name === 'false') return false; if ($name === 'null') return null;
if ($name === 'undefined') return new TaguiUndefined();
if ($this->missing) return new TaguiUndefined();
throw new TaguiExprError('variable ' . $name . ' is not defined');}
throw new TaguiExprError('unexpected token');}
private function parse_args() {
$args = array();
$next = $this->peek();
if ($next !== null && $next[0] === ')') {$this->pop(); return $args;}
while (true) {
$args[] = $this->parse_or();
$next = $this->peek();
if ($next !== null && $next[0] === ',') {$this->pop(); continue;}
$this->eat_kind(')'); break;}
return $args;}
private function method($value, $name, $args) {
if ($name === 'indexOf') {
$needle = isset($args[0]) ? (string)$this->stringish($args[0]) : '';
$pos = strpos((string)$this->stringish($value), $needle);
return $pos === false ? -1 : $pos;}
if ($name === 'toString') return $this->stringish($value);
if ($name === 'trim') return trim((string)$this->stringish($value));
if ($name === 'toLowerCase') return strtolower((string)$this->stringish($value));
throw new TaguiExprError('unknown method ' . $name);}
private function plus($left, $right) {
if ($left instanceof TaguiUndefined || $right instanceof TaguiUndefined) return NAN;
if (is_string($left) || is_string($right)) return $this->stringish($left) . $this->stringish($right);
if (is_bool($left) || is_bool($right)) return $this->stringish($left) . $this->stringish($right);
return $this->num($left) + $this->num($right);}
private function compare($op, $left, $right) {
if ($left instanceof TaguiUndefined || $right instanceof TaguiUndefined) {
$same = ($left instanceof TaguiUndefined || $left === null) && ($right instanceof TaguiUndefined || $right === null);
if ($op === '==') return $same; if ($op === '!=') return !$same;
return false;}
if (is_string($left) || is_string($right)) {$left = $this->stringish($left); $right = $this->stringish($right);}
if ($op === '==') return $left == $right; if ($op === '!=') return $left != $right;
if ($op === '>') return $left > $right; if ($op === '<') return $left < $right;
if ($op === '>=') return $left >= $right; return $left <= $right;}
function truthy($value) {
if ($value instanceof TaguiUndefined) return false;
if ($value === false || $value === null || $value === '' || $value === 0 || $value === 0.0) return false;
if (is_float($value) && is_nan($value)) return false;
return true;}
function stringish($value) {
if ($value instanceof TaguiUndefined) return 'undefined';
if ($value === true) return 'true'; if ($value === false) return 'false'; if ($value === null) return 'null';
return (string)$value;}
private function num($value) {
if ($value instanceof TaguiUndefined) return NAN;
if (is_bool($value)) return $value ? 1 : 0; return $value + 0;}}

class TaguiUndefined {}

/* -------------------------------------------------------------------------- */
/* Flow interpreter                                                           */
/* -------------------------------------------------------------------------- */

class TaguiModern {
use TaguiModernSteps;
public $chrome;
public $quiet = false;
public $capture = false;
public $captured = array();
public $vars = array();
public $flow_path = '';
public $live = false;
public $stopped = false;
public $jump = null;
public $repo = array();
public $download_dir = '';
public $include_stack = array();
public $inside_run = false;
public $run_buffer = '';
public $timeout = 10.0;
public $snap_count = 0;
public $text_count = 0;
public $started_at = 0;
public $steps_ran = 0;
public $steps_skipped = 0;
public $blocks_skipped = 0;
public $failure_captured = false;
public $evidence_dir = '';

function __construct($chrome, $quiet, $flow_path) {
$this->chrome = $chrome; $this->quiet = $quiet; $this->flow_path = $flow_path;
$this->evidence_dir = $flow_path;
$this->vars['infinity'] = 1024;
for ($i = 1; $i <= 16; $i++) {
$this->vars['p' . $i] = '';
$env = getenv('TAGUI_PARAM_' . $i);
if ($env !== false && $env !== '') $this->vars['p' . $i] = $env;}}

function set_cli_params($args) {
$index = 1;
foreach ($args as $arg) {
if ($index > 16) break;
if ($arg !== '') $this->vars['p' . $index] = $arg;
$index++;}}

function run_lines($lines) {
$this->steps_ran = 0;
$this->steps_skipped = 0;
$this->blocks_skipped = 0;
$this->sync_repo_vars();
$this->started_at = microtime(true);
if (!$this->quiet) $this->emit_raw("\nSTART - automation started - " . tagui_local_stamp());
$this->execute_range($lines, 0, count($lines) - 1, true);
if (!$this->quiet) {
$url = 'about:blank'; $title = '';
if ($this->chrome !== null) {try {$url = $this->chrome->url(); $title = $this->chrome->title();} catch (Exception $e) {}}
$this->emit_raw("\n" . $url . ' - ' . $title);
$seconds = number_format(microtime(true) - $this->started_at, 1, '.', '');
$this->emit_raw('FINISH - automation finished - ' . $seconds . 's');}
$this->emit_raw($this->run_summary());}

function run_summary() {
return 'SUMMARY - ran ' . $this->steps_ran . ', skipped ' . $this->steps_skipped . ', blocks skipped ' . $this->blocks_skipped;}

function count_step($active) {
if ($active) $this->steps_ran++;
else $this->steps_skipped++;}

function execute_range($lines, $start, $end, $active) {
$i = $start;
while ($i <= $end) {
if ($this->jump !== null) return;
if ($this->stopped && $active) return;
$trimmed = trim($lines[$i]);
if ($trimmed === '' || str_starts_with($trimmed, '//')) {$i++; continue;}
$i = $this->execute_statement($lines, $i, $end, $active);}}

function execute_statement($lines, $i, $end, $active) {
$mark = trim($lines[$i]);
if ($mark !== '{' && $mark !== '}') $this->count_step($active);
if ($this->inside_run) {
$finish = strtolower($mark) === 'run finish';
if ($finish) {
if ($active) $this->run_step('run finish', $i);
else {$this->inside_run = false; $this->run_buffer = '';}}
else if ($active) $this->run_buffer .= ($this->run_buffer === '' ? '' : "\n") . $mark;
return $i + 1;}
$block = $this->language_block($lines, $i, $end);
if ($block !== null) {
if ($block['error'] !== '') {if ($active) $this->fail($i, $block['error']); return $block['next'];}
if ($active) $this->run_language($block['kind'], $block['body'], $i);
return $block['next'];}
$raw = trim($lines[$i]);
$lower = strtolower($raw);
if (preg_match('/^tagui(?:\s|$)/i', $raw)) return $this->execute_include($lines, $i, $end, $active);
if ($lower === '{') {
$close = $this->find_block_end($lines, $i, $end);
if ($close < 0) {if ($active) $this->fail($i, 'number of step { does not tally with }'); return $end + 1;}
if ($active) $this->execute_range($lines, $i + 1, $close - 1, true);
return $close + 1;}
if ($raw === '}') {if ($active) $this->fail($i, 'unexpected }'); return $i + 1;}
if ($lower === 'break' || $lower === 'break;') {if ($active) $this->jump = 'break'; return $i + 1;}
if ($lower === 'continue' || $lower === 'continue;') {if ($active) $this->jump = 'continue'; return $i + 1;}
if (str_starts_with($lower, 'if ')) return $this->execute_if($lines, $i, $end, $active);
if (str_starts_with($lower, 'for ') || str_starts_with($lower, 'for(')) return $this->execute_for($lines, $i, $end, $active);
if (str_starts_with($lower, 'while ') || str_starts_with($lower, 'while(')) return $this->execute_while($lines, $i, $end, $active);
if (str_starts_with($lower, 'frame ') || $lower === 'frame') return $this->execute_frame($lines, $i, $end, $active);
if (str_starts_with($lower, 'popup ') || $lower === 'popup') return $this->execute_popup($lines, $i, $end, $active);
if ($active) {
try {$line = $this->expand_backticks($raw);}
catch (TaguiExprError $e) {$this->fail($i, $e->getMessage()); return $i + 1;}
$this->execute_step($line, $i);}
return $i + 1;}

function execute_if($lines, $i, $end, $active) {
$matched = false;
$first = true;
while ($i <= $end) {
if (!$first) $this->count_step($active);
$first = false;
$base = $this->indent_of($lines[$i]);
try {$line = $this->expand_backticks(trim($lines[$i]));}
catch (TaguiExprError $e) {
if ($active) $this->fail($i, $e->getMessage());
return $this->execute_body($lines, $i + 1, $end, false, $base);}
$lower = strtolower($line);
$is_else_if = str_starts_with($lower, 'else if ');
$is_else = ($lower === 'else');
$is_if = str_starts_with($lower, 'if ');
if (!$is_if && !$is_else_if && !$is_else) break;
$take = false;
if ($is_else) $take = $active && !$matched;
else {
$expr = $this->condition_expression($line);
$evaluated = false;
$value = false;
if ($active && !$matched) {
$value = $this->truthy_expr($expr, $i);
$evaluated = !$this->stopped;}
$take = $evaluated && $value;
if ($evaluated && !$value) {
$this->blocks_skipped++;
$source = trim($lines[$i]);
if ($this->strict_mode() && $this->is_exist_gate($expr)) {
$this->fail($i, 'required check failed - cannot find ' . $source);}
else $this->trace('SKIP - ' . $source);}}
if ($take) $matched = true;
$i++;
$i = $this->execute_body($lines, $i, $end, $take, $base);
if ($is_else) break;
$next = $this->next_code($lines, $i, $end);
if ($next === null) break;
$peek = strtolower(trim($lines[$next]));
if ($peek === 'else' || str_starts_with($peek, 'else if ')) {$i = $next; continue;}
break;}
return $i;}

function execute_body($lines, $i, $end, $active, $base_indent) {
$next = $this->next_code($lines, $i, $end);
if ($next === null) return $i;
if (trim($lines[$next]) === '{') return $this->execute_statement($lines, $next, $end, $active);
if ($this->indent_of($lines[$next]) > $base_indent) {
$last = $next;
for ($j = $next; $j <= $end; $j++) {
$piece = trim($lines[$j]);
if ($piece === '' || str_starts_with($piece, '//')) continue;
if ($this->indent_of($lines[$j]) <= $base_indent) break;
$last = $j;}
$this->execute_range($lines, $next, $last, $active);
return $last + 1;}
return $this->execute_statement($lines, $next, $end, $active);}

function execute_include($lines, $i, $end, $active) {
$raw = trim($lines[$i]);
if (!$active) return $i + 1;
$params = tagui_strip_line_comment(trim(substr($raw, 5)));
if ($params === '') {$this->fail($i, 'filename missing for step ' . $raw); return $i + 1;}
$path = $this->abs_file($params);
if (!is_file($path)) {$this->fail($i, 'file not found for step ' . $raw); return $i + 1;}
$real = realpath($path);
if ($real === false) $real = $path;
if (isset($this->include_stack[$real])) {$this->fail($i, 'cannot open file for step ' . $raw); return $i + 1;}
$included = file($real, FILE_IGNORE_NEW_LINES);
if ($included === false) {$this->fail($i, 'cannot open file for step ' . $raw); return $i + 1;}
$saved = $this->flow_path;
$this->include_stack[$real] = true;
$this->flow_path = dirname($real);
$this->execute_range($included, 0, count($included) - 1, true);
unset($this->include_stack[$real]);
$this->flow_path = $saved;
return $i + 1;}

function execute_for($lines, $i, $end, $active) {
try {$header = $this->expand_backticks(trim($lines[$i]));}
catch (TaguiExprError $e) {if ($active) $this->fail($i, $e->getMessage()); return $i + 1;}
$spec = $this->parse_for(tagui_strip_line_comment($header));
if ($spec === null) {if ($active) $this->fail($i, 'invalid for loop - ' . trim($lines[$i])); return $i + 1;}
$base = $this->indent_of($lines[$i]);
if ($this->next_code($lines, $i + 1, $end) === null) return $end + 1;
if (!$active) return $this->execute_body($lines, $i + 1, $end, false, $base);
$from = $this->loop_bound($spec['from']);
$to = $this->loop_bound($spec['to']);
if ($from === null || $to === null) {
$this->fail($i, 'invalid for loop - ' . trim($lines[$i]));
return $this->execute_body($lines, $i + 1, $end, false, $base);}
if (($to - $from) > 10000) {
$this->fail($i, 'for loop range is too large');
return $this->execute_body($lines, $i + 1, $end, false, $base);}
if ($from > $to) return $this->execute_body($lines, $i + 1, $end, false, $base);
$after = $i + 1;
for ($value = $from; $value <= $to; $value++) {
if ($this->stopped) break;
$this->vars[$spec['var']] = $value;
$after = $this->execute_body($lines, $i + 1, $end, true, $base);
if ($this->jump === 'break') {$this->jump = null; break;}
if ($this->jump === 'continue') {$this->jump = null; continue;}}
return $after;}

function execute_step($line, $index) {
$intent = tagui_keyword_intent($line);
if ($intent === 'live') {
if ($this->live) {$this->fail($index, 'you are already in live mode, type done to quit live mode'); return;}
$this->live_loop(); return;}
if ($intent === 'url') {$this->trace($line); $this->go($line, $index); return;}
if ($intent === 'tap' || $intent === 'rtap' || $intent === 'dtap') {
$target = trim(substr($line, strpos($line, ' ') + 1));
if (tagui_is_visual($target)) {$this->visual_gap($index, $line); return;}
$this->trace($line);
$button = $intent === 'rtap' ? 'right' : 'left';
$clicks = $intent === 'dtap' ? 2 : 1;
$this->pointer($line, $button, $clicks, $index); return;}
if ($intent === 'hover') {
$target = trim(substr($line, strpos($line, ' ') + 1));
if (tagui_is_visual($target)) {$this->visual_gap($index, $line); return;}
$this->trace($line); $this->hover($line, $index); return;}
if ($intent === 'type') {
$params = trim(substr($line, strpos($line, ' ') + 1));
$target = trim(substr($params, 0, (int)stripos($params . ' as ', ' as ')));
if (tagui_is_visual($target)) {$this->visual_gap($index, $line); return;}
$this->trace($line); $this->type_step($line, $index); return;}
if ($intent === 'select') {$this->trace($line); $this->select_step($line, $index); return;}
if ($intent === 'read') {$this->trace($line); $this->read_step($line, $index); return;}
if ($intent === 'show') {$this->show_step($line, $index); return;}
if ($intent === 'echo') {$this->echo_step($line, $index); return;}
if ($intent === 'snap') {$this->trace($line); $this->snap_step($line, $index); return;}
if ($intent === 'wait') {$this->trace($line); $this->wait_step($line, $index); return;}
if ($intent === 'dump') {$this->dump_step($line, $index); return;}
if ($intent === 'timeout') {$this->timeout_step($line, $index); return;}
if ($intent === 'dom') {$this->dom_step($line, $index); return;}
if ($intent === 'upload') {$this->trace($line); $this->upload_step($line, $index); return;}
if ($intent === 'down') {$this->trace($line); $this->download_step($line, $index); return;}
if ($intent === 'receive') {$this->trace($line); $this->receive_step($line, $index); return;}
if ($intent === 'save') {$this->trace($line); $this->save_step($line, $index); return;}
if ($intent === 'write') {$this->trace($line); $this->write_step($line, $index); return;}
if ($intent === 'load') {$this->load_step($line, $index); return;}
if ($intent === 'table') {$this->trace($line); $this->table_step($line, $index); return;}
if ($intent === 'ask') {$this->ask_step($line, $index); return;}
if ($intent === 'api') {$this->trace($line); $this->api_step($line, $index); return;}
if ($intent === 'run') {$this->run_step($line, $index); return;}
if ($intent === 'check') {$this->check_step($line, $index); return;}
if ($intent === 'keyboard' || $intent === 'mouse' || $intent === 'vision') {$this->visual_gap($index, $line); return;}
if ($intent === 'js') {$this->js_step($line, $index); return;}
if ($intent === 'py') {$this->py_step($line, $index); return;}
if ($intent === 'r') {$this->r_step($line, $index); return;}
if ($intent === 'test') {$this->integration_gap($intent, $index); return;}
if (str_starts_with(strtolower($line), 'expect ') || strtolower($line) === 'expect') {
$this->trace($line); $this->expect_step($line, $index); return;}
if ($intent === 'frame' || $intent === 'popup') {
$this->fail($index, $intent . ' is not supported in live mode. It opens a block around the following steps; put it in a flow file.'); return;}
if ($this->is_statement_list($line)) {$this->js_run($this->strip_var_keyword($line), $index); return;}
if ($this->is_assignment($line)) {$this->assign_step($line, $index); return;}
if (tagui_is_code($line)) {$this->js_run($line, $index); return;}
$this->fail($index, 'cannot understand step ' . $line);}

function live_loop() {
$this->live = true;
tagui_write_output("LIVE MODE - type done to quit\n \x08");
while (true) {
$raw = fgets(STDIN);
if ($raw === false) break;
$raw = rtrim($raw, "\r\n");
if (str_starts_with($raw, 'done')) break;
try {
$line = $this->expand_backticks(trim($raw));
if ($line === '' || str_starts_with($line, '//')) continue;
$this->execute_step($line, -1);}
catch (Exception $e) {$this->emit('ERROR - ' . $e->getMessage());}}
$this->live = false;}

function go($url, $index) {
if (!$this->require_chrome($index)) return;
try {$this->chrome->navigate(tagui_file_url($url));}
catch (Exception $e) {$this->fail($index, $e->getMessage());}}

function pointer($line, $button, $clicks, $index) {
$target = trim(substr($line, strpos($line, ' ') + 1));
if ($target === '' || strcasecmp($target, 'click') === 0) {$this->fail($index, 'target missing for ' . $line); return;}
$info = $this->need($target, $index); if ($info === null) return;
try {$this->chrome->click_at((float)$info['x'], (float)$info['y'], $button, $clicks, true);}
catch (Exception $e) {$this->fail($index, $e->getMessage());}}

function hover($line, $index) {
$target = trim(substr($line, strpos($line, ' ') + 1));
$info = $this->need($target, $index); if ($info === null) return;
try {$this->chrome->hover_at((float)$info['x'], (float)$info['y']);}
catch (Exception $e) {$this->fail($index, $e->getMessage());}}

function type_step($line, $index) {
$params = trim(substr($line, strpos($line, ' ') + 1));
$split = stripos($params, ' as ');
if ($split === false) {$this->fail($index, 'target/text missing for ' . $line); return;}
$target = trim(substr($params, 0, $split));
$text = substr($params, $split + 4);
if ($target === '') {$this->fail($index, 'target/text missing for ' . $line); return;}
if ($this->need($target, $index) === null) return;
try {
if (!$this->chrome->type_into($target, $text)) $this->fail($index, 'cannot find ' . $target);}
catch (Exception $e) {$this->fail($index, $e->getMessage());}}

function read_step($line, $index) {
$params = trim(substr($line, strpos($line, ' ') + 1));
$split = stripos($params, ' to ');
if ($split === false) {$this->fail($index, 'target/variable missing for ' . $line); return;}
$target = trim(substr($params, 0, $split));
$name = trim(substr($params, $split + 4));
if (tagui_is_visual($target)) {$this->visual_gap($index, $line, false); return;}
if ($target === '' || $name === '' || !preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $name)) {
$this->fail($index, 'target/variable missing for ' . $line); return;}
if (strcasecmp($target, 'page') === 0) {
if (!$this->require_chrome($index)) return;
$this->vars[$name] = $this->chrome->page_html(); return;}
$info = $this->need($target, $index); if ($info === null) return;
$this->vars[$name] = (string)$info['text'];}

function show_step($line, $index) {
$target = trim(substr($line, strpos($line, ' ') + 1));
if ($target === '') {$this->fail($index, 'target missing for ' . $line); return;}
if (strcasecmp($target, 'page') === 0) {
if (!$this->require_chrome($index)) return;
$this->emit($line . " - \n" . $this->chrome->page_html()); return;}
$info = $this->need($target, $index); if ($info === null) return;
$this->emit(trim((string)$info['text']));}

function echo_step($line, $index) {
$params = trim(substr($line, 4));
if (str_starts_with(strtolower($line), 'echo ')) $params = trim(substr($line, 5));
if ($params === '') {$this->fail($index, 'text missing for ' . $line); return;}
if (str_contains($params, "'") && str_contains($params, '"')) {
$this->fail($index, 'inconsistent quotes in ' . $params); return;}
if (!str_contains($params, "'") && !str_contains($params, '"') && preg_match('/\s/', $params)) {
$this->emit($params); return;}
$expr = $this->add_concat($params);
try {$value = $this->expr_value($expr);}
catch (TaguiExprError $e) {
if (!str_contains($params, "'") && !str_contains($params, '"') && str_contains($e->getMessage(), 'is not defined')) {
$this->emit($params); return;}
$this->fail($index, $e->getMessage()); return;}
$this->emit($this->stringish($value));}

function snap_step($line, $index) {
$params = '';
if (str_contains($line, ' ')) $params = trim(substr($line, strpos($line, ' ') + 1));
$target = 'page'; $file = '';
$split = stripos($params, ' to ');
if ($params === '' || strcasecmp($params, 'page') === 0) {$target = 'page'; $file = $this->next_snap();}
else if ($split === false) {$target = $params; $file = $this->next_snap();}
else {
$target = trim(substr($params, 0, $split));
$file = trim(substr($params, $split + 4));
if ($file === '') $file = $this->next_snap(); else $file = $this->abs_file($file);}
if ($this->is_desktop_snap($target)) {$this->visual_gap($index, 'snap ' . $target . ' to ' . $file, false); return;}
if (!$this->is_page_snap($target) && tagui_is_visual($target)) {$this->visual_gap($index, $line, false); return;}
if (!$this->require_chrome($index)) return;
$clip = null;
if (!$this->is_page_snap($target)) {
$info = $this->need($target, $index); if ($info === null) return;
if ((float)$info['width'] > 0 && (float)$info['height'] > 0) {
$clip = array('x' => (float)$info['left'], 'y' => (float)$info['top'],
'width' => (float)$info['width'], 'height' => (float)$info['height']);}}
try {if (!$this->chrome->screenshot($file, $clip)) $this->fail($index, 'cannot save snapshot ' . $file);}
catch (Exception $e) {$this->fail($index, $e->getMessage());}}

function wait_step($line, $index) {
$params = trim(substr($line, 4));
if (str_starts_with(strtolower($line), 'wait ')) $params = trim(substr($line, 5));
$element = $this->wait_element_target($params);
if ($element !== null) {$this->wait_for_target($element, $index); return;}
if ($params === '' || strtolower($params) === 'wait') $seconds = 5.0;
else $seconds = floatval($params);
if ($seconds < 0) $seconds = 0;
if ($seconds > 120) $seconds = 120;
if ($seconds > 0) usleep((int)($seconds * 1000000));}

function wait_element_mode() {return getenv('TAGUI_MODERN_WAIT_ELEMENT') === '1';}

function strict_mode() {return getenv('TAGUI_MODERN_STRICT') === '1';}

function is_time_wait($params) {
$params = trim((string)$params);
if ($params === '' || strtolower($params) === 'wait') return true;
if (is_numeric($params)) return true;
return preg_match('/^[+-]?(?:\d+\.?\d*|\.\d+)/', $params) === 1;}

function wait_element_target($params) {
$params = trim((string)$params);
if (preg_match('/^(?:for|element)\s+(\S.*)$/i', $params, $match)) return trim($match[1]);
if ($this->wait_element_mode() && $params !== '' && !$this->is_time_wait($params)) return $params;
return null;}

function is_exist_gate($expr) {
return preg_match('/^(?:exist|present|visible)\s*\(.*\)(?:\s*==\s*(?:true|1))?\s*$/i', trim((string)$expr)) === 1;}

function expect_step($line, $index) {
$target = '';
if (str_contains($line, ' ')) $target = trim(substr($line, strpos($line, ' ') + 1));
if ($target === '') {$this->fail($index, 'target missing for ' . $line); return;}
$this->require_target($target, $index, 'required check failed - cannot find ' . $target);}

function wait_for_target($target, $index) {
$this->require_target($target, $index, 'cannot find ' . $target);}

function require_target($target, $index, $message) {
if (tagui_is_visual($target)) {
try {$found = $this->exists_arg(array($target));}
catch (TaguiExprError $e) {$this->fail($index, $e->getMessage()); return;}
if (!$found) $this->fail($index, $message);
return;}
if (!$this->require_chrome($index)) return;
try {$info = $this->chrome->find($target, $this->live);}
catch (Exception $e) {$this->fail($index, $e->getMessage()); return;}
if ($info === null) $this->fail($index, $message);}

function dump_step($line, $index) {
$params = trim(substr($line, 4));
if ($params === '') {$this->fail($index, 'variable missing for ' . $line); return;}
$split = stripos($params, ' to ');
if ($split === false) {$expr = $params; $file = $this->default_text_file(false);}
else {
$expr = trim(substr($params, 0, $split));
$file = $this->abs_file(trim(substr($params, $split + 4)));}
if ($expr === '' || $file === '') {$this->fail($index, 'variable missing for ' . $line); return;}
try {$value = $this->expr_value($this->add_concat($expr));}
catch (TaguiExprError $e) {$this->fail($index, $e->getMessage()); return;}
if (file_put_contents($file, $this->stringish($value)) === false) $this->fail($index, 'cannot save ' . $file);}

function timeout_step($line, $index) {
$params = tagui_strip_line_comment(trim(substr($line, 8)));
if ($params === '') {$this->fail($index, 'time in seconds missing for ' . $line); return;}
$this->timeout = floatval($params);
if ($this->chrome !== null) $this->chrome->timeout = $this->timeout;}

function dom_step($line, $index) {
$params = trim(substr($line, 4));
if ($params === '') {$this->fail($index, 'statement missing for ' . $line); return;}
$lower = strtolower($params);
if ($lower === 'begin' || $lower === 'finish') {
$this->fail($index, 'dom begin / dom finish is a block. Put it in a flow file.'); return;}
$this->dom_run($params, $index);}

function dom_run($code, $index) {
if (!$this->require_chrome($index)) return;
$json = array();
if (isset($this->vars['dom_json']) && (is_array($this->vars['dom_json']) || is_object($this->vars['dom_json']))) $json = $this->vars['dom_json'];
$payload = json_encode($json, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
if ($payload === false) $payload = '{}';
$script = '(function(dom_json){' . $code . '})(' . $payload . ')';
try {$this->vars['dom_result'] = $this->chrome->evaluate($script);}
catch (Exception $e) {$this->fail($index, $this->dom_failure($e->getMessage()));}}

function dom_failure($message) {
if (preg_match('/Cannot read propert(?:y|ies) of null|null is not an object|of null \(reading/i', (string)$message)) {
return 'dom target is null. The selector did not match an element.';}
return (string)$message;}

function language_block($lines, $i, $end) {
$header = strtolower(trim($lines[$i]));
if (!preg_match('/^(js|py|r|vision|dom)\s+begin$/', $header, $match)) return null;
$kind = $match[1];
$body = array();
$finish = $kind . ' finish';
for ($j = $i + 1; $j <= $end; $j++) {
if (strtolower(trim($lines[$j])) === $finish) return array('kind' => $kind, 'body' => implode("\n", $body), 'next' => $j + 1, 'error' => '');
$body[] = rtrim((string)$lines[$j], "\r");}
return array('kind' => $kind, 'body' => '', 'next' => $end + 1, 'error' => 'missing ' . $finish);}

function run_language($kind, $body, $index) {
$body = tagui_dedent($body);
if ($kind === 'js') $this->js_run($body, $index);
else if ($kind === 'py') $this->py_run($body, $index);
else if ($kind === 'r') $this->r_run(tagui_r_join($body), $index);
else if ($kind === 'vision') $this->visual_gap($index, 'vision ' . $body);
else if ($kind === 'dom') $this->dom_run($body, $index);
else $this->fail($index, 'cannot run ' . $kind);}

function js_step($line, $index) {
$params = trim(substr($line, 3));
$lower = strtolower($params);
if ($params === '') {$this->fail($index, 'statement missing for ' . $line); return;}
if ($lower === 'begin' || $lower === 'finish') {
$this->fail($index, 'js begin / js finish is a block. Put it in a flow file.'); return;}
$this->trace($line);
$this->js_run($params, $index);}

function py_step($line, $index) {
$params = trim(substr($line, 3));
$lower = strtolower($params);
if ($params === '') {$this->fail($index, 'Python command(s) missing for ' . $line); return;}
if ($lower === 'begin' || $lower === 'finish') {
$this->fail($index, 'py begin / py finish is a block. Put it in a flow file.'); return;}
$this->trace($line);
$this->py_run($params, $index);}

function r_step($line, $index) {
$params = trim(substr($line, 2));
if (str_starts_with(strtolower($line), 'r ')) $params = trim(substr($line, 2));
$lower = strtolower($params);
if ($params === '') {$this->fail($index, 'R command(s) missing for ' . $line); return;}
if ($lower === 'begin' || $lower === 'finish') {
$this->fail($index, 'r begin / r finish is a block. Put it in a flow file.'); return;}
$this->trace($line);
$this->r_run($params, $index);}

function js_run($code, $index) {
if (!$this->js_runner_available()) {$this->fail($index, 'browser is not open'); return;}
try {$this->run_javascript($code);}
catch (Exception $e) {$this->fail($index, $e->getMessage());}}

function py_run($code, $index) {
try {$result = TaguiHelpers::python($code, $this->timeout);}
catch (Exception $e) {$this->fail($index, $e->getMessage()); return;}
$out = isset($result['out']) ? trim((string)$result['out']) : '';
if (empty($result['ok'])) {
$err = isset($result['err']) && $result['err'] !== '' ? $result['err'] : 'py step failed';
$out = 'ERROR - ' . $err;}
$this->remember_external('py', $out);}

function r_run($code, $index) {
if (trim((string)$code) === '') {$this->fail($index, 'R command(s) missing for r'); return;}
try {$out = TaguiHelpers::r($code, $this->timeout);}
catch (Exception $e) {
$this->remember_external('r', '');
$message = $e->getMessage();
if (!str_contains($message, 'Rscript')) $message = 'cannot execute R command(s). ' . $message;
$this->fail($index, $message);
return;}
$this->remember_external('r', trim((string)$out));}

function remember_external($kind, $out) {
$this->vars[$kind . '_result'] = $out;
$decoded = json_decode($out, true);
$this->vars[$kind . '_json'] = ($out !== '' && json_last_error() === JSON_ERROR_NONE) ? $decoded : null;}

function js_runner_available() {
if ($this->chrome !== null && method_exists($this->chrome, 'evaluate')) return true;
return tagui_which('node') !== '';}

function js_fallback_error($error) {
$message = $error->getMessage();
return str_contains($message, 'unexpected') || str_contains($message, 'unknown method')
|| str_contains($message, 'cannot understand expression') || str_contains($message, 'expected ');}

function expr_value($source) {
try {return $this->expr($source);}
catch (TaguiExprError $e) {
if (!$this->js_fallback_error($e) || !$this->js_runner_available()) throw $e;
try {return $this->run_javascript('return (' . $source . ');');}
catch (Exception $ex) {throw new TaguiExprError($ex->getMessage());}}}

function run_javascript($code) {
$code = tagui_prepare_js($code);
$script = tagui_js_program($code, $this->vars);
if ($this->chrome !== null && method_exists($this->chrome, 'evaluate')) $result = $this->chrome->evaluate($script);
else $result = tagui_js_eval_node($script);
if (!is_array($result)) throw new RuntimeException('page script failed');
if (!empty($result['error'])) throw new RuntimeException('page script failed: ' . $result['error']);
if (isset($result['vars']) && is_array($result['vars'])) {
foreach ($result['vars'] as $key => $value) {
if (!is_string($key) || !preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $key)) continue;
if (!array_key_exists($key, $this->vars) && $value === null) continue;
$this->vars[$key] = $value;}}
if (isset($result['echo']) && is_array($result['echo'])) {
foreach ($result['echo'] as $line) $this->emit((string)$line);}
return array_key_exists('value', $result) ? $result['value'] : null;}

function assign_step($line, $index) {
if (str_starts_with($line, 'var ')) $line = trim(substr($line, 4));
$eq = strpos($line, '=');
$name = trim(substr($line, 0, $eq));
$expr = trim(substr($line, $eq + 1));
if (str_ends_with($expr, ';')) $expr = trim(substr($expr, 0, -1));
if (!preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $name)) {$this->fail($index, 'cannot understand step ' . $line); return;}
try {$this->vars[$name] = $this->expr_value($expr);}
catch (TaguiExprError $e) {$this->fail($index, $e->getMessage());}}

function calls() {
$engine = $this;
return array(
'title' => function ($args) use ($engine) {return $engine->chrome ? $engine->chrome->title() : '';},
'url' => function ($args) use ($engine) {return $engine->chrome ? $engine->chrome->url() : 'about:blank';},
'text' => function ($args) use ($engine) {return $engine->chrome ? $engine->chrome->body_text() : '';},
'exist' => function ($args) use ($engine) {return $engine->exists_arg($args);},
'present' => function ($args) use ($engine) {return $engine->present_arg($args);},
'visible' => function ($args) use ($engine) {return $engine->visible_arg($args);},
'count' => function ($args) use ($engine) {return $engine->count_arg($args);},
'timer' => function ($args) use ($engine) {
if ($engine->started_at <= 0) $engine->started_at = microtime(true);
return microtime(true) - $engine->started_at;},
'mouse_xy' => function ($args) use ($engine) {return $engine->mouse_point('xy');},
'mouse_x' => function ($args) use ($engine) {return $engine->mouse_point('x');},
'mouse_y' => function ($args) use ($engine) {return $engine->mouse_point('y');});}

function exists_arg($args) {return $this->presence_arg($args, 'present', true);}
function present_arg($args) {return $this->presence_arg($args, 'present', false);}
function visible_arg($args) {return $this->presence_arg($args, 'visible', false);}
function image_present($command) {
return TaguiSikuli::run($command, max(30.0, (float)$this->timeout));}
function presence_arg($args, $intent, $wait = false) {
if (!isset($args[0])) return false;
$target = trim((string)$args[0]);
if ($target !== '' && tagui_is_visual($target)) {
$command = $intent . ' ' . $this->visual_target($target);
if (!$wait) {
try {return (bool)$this->image_present($command);}
catch (Exception $e) {throw new TaguiExprError($e->getMessage());}}
$deadline = microtime(true) + (float)$this->timeout;
while (true) {
try {$found = $this->image_present($command);}
catch (Exception $e) {throw new TaguiExprError($e->getMessage());}
if ($found) return true;
if (microtime(true) >= $deadline) return false;
usleep(200000);}}
if ($this->chrome === null) return false;
$info = $this->chrome->find($target, true);
if ($info === null) return false;
if ($intent === 'visible') return !empty($info['visible']);
return true;}
function visual_target($target) {
if (substr($target, 0, 1) === '(') return $target;
return $this->abs_file($target);}
function count_arg($args) {
if (!isset($args[0]) || $this->chrome === null) return 0;
$info = $this->chrome->locate((string)$args[0]);
if ($info === null) return 0;
return (int)$info['count'];}

function expr($source) {
$parser = new TaguiExpr($this->vars, $this->calls());
return $parser->evaluate($source);}

function truthy_expr($source, $index) {
try {
$parser = new TaguiExpr($this->vars, $this->calls(), true);
return $parser->truthy($parser->evaluate($source));}
catch (TaguiExprError $e) {
if ($this->js_fallback_error($e) && $this->js_runner_available()) {
try {return (new TaguiExpr($this->vars, $this->calls(), true))->truthy($this->run_javascript('return (' . $source . ');'));}
catch (Exception $ex) {$this->fail($index, $ex->getMessage()); return false;}}
$this->fail($index, $e->getMessage()); return false;}}

function condition_expression($line) {
$trim = trim($line);
$lower = strtolower($trim);
if (str_starts_with($lower, 'else if ')) $body = trim(substr($trim, 8));
else if (str_starts_with($lower, 'if ')) $body = trim(substr($trim, 3));
else $body = $trim;
return $this->rewrite_condition($body);}

function rewrite_condition($logic) {
$logic = $this->rewrite_contains($logic);
$map = array(
' more than or equals to ' => ' >= ', ' greater than or equals to ' => ' >= ', ' higher than or equals to ' => ' >= ',
' less than or equals to ' => ' <= ', ' lesser than or equals to ' => ' <= ', ' lower than or equals to ' => ' <= ',
' more than or equal to ' => ' >= ', ' greater than or equal to ' => ' >= ', ' higher than or equal to ' => ' >= ',
' less than or equal to ' => ' <= ', ' lesser than or equal to ' => ' <= ', ' lower than or equal to ' => ' <= ',
' more than ' => ' > ', ' greater than ' => ' > ', ' higher than ' => ' > ',
' less than ' => ' < ', ' lesser than ' => ' < ', ' lower than ' => ' < ',
' not equals to ' => ' != ', ' not equal to ' => ' != ', ' equals to ' => ' == ', ' equal to ' => ' == ');
foreach ($map as $from => $to) $logic = str_replace($from, $to, $logic);
$logic = str_replace(' and ', ' && ', $logic);
$logic = str_replace(' or ', ' || ', $logic);
return $logic;}

function rewrite_contains($logic) {
$kinds = array(' not contains ' => '<0', ' not contain ' => '<0', ' contains ' => '>-1', ' contain ' => '>-1');
foreach ($kinds as $keyword => $op) {
while (($pos = strpos($logic, $keyword)) !== false) {
$after = substr($logic, $pos + strlen($keyword));
if (!preg_match('/^\s*([\'"])(.*?)\\1/', $after, $match)) break;
$quoted = $match[1] . $match[2] . $match[1];
$before = rtrim(substr($logic, 0, $pos));
if (!preg_match('/([A-Za-z_][A-Za-z0-9_]*\(\)|[A-Za-z_][A-Za-z0-9_]*)$/', $before, $var)) break;
$var_pos = strrpos($before, $var[1]);
$consumed = $pos + strlen($keyword) + strlen($match[0]);
$logic = substr($logic, 0, $var_pos) . '(' . $var[1] . '.indexOf(' . $quoted . ')' . $op . ')' . substr($logic, $consumed);}
}
return $logic;}

function parse_for($header) {
$header = tagui_strip_line_comment(trim($header));
$bound = '[A-Za-z_][A-Za-z0-9_]*|-?\d+';
if (preg_match('/^for\s+([A-Za-z_][A-Za-z0-9_]*)\s+from\s+(' . $bound . ')\s+to\s+(' . $bound . ')\s*$/i', $header, $m)) {
return array('var' => $m[1], 'from' => $this->for_bound_literal($m[2]), 'to' => $this->for_bound_literal($m[3]));}
if (preg_match('/^for\s*\(\s*([A-Za-z_][A-Za-z0-9_]*)\s*=\s*(' . $bound . ')\s*;\s*\1\s*<=\s*(' . $bound . ')\s*;\s*\1\s*\+\+\s*\)\s*$/', $header, $m)) {
return array('var' => $m[1], 'from' => $this->for_bound_literal($m[2]), 'to' => $this->for_bound_literal($m[3]));}
return null;}

function for_bound_literal($token) {
if (strtolower($token) === 'infinity') return 1024;
if (is_numeric($token)) return (int)$token;
return $token;}

function loop_bound($token) {
if (is_int($token)) return $token;
$token = trim((string)$token);
if (strtolower($token) === 'infinity') return 1024;
if (is_numeric($token)) return (int)$token;
if (preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $token) && array_key_exists($token, $this->vars) && is_numeric($this->vars[$token])) {
return (int)$this->vars[$token];}
return null;}

function sync_repo_vars() {
if (!is_array($this->repo)) return;
foreach ($this->repo as $key => $value) $this->vars[tagui_repo_identifier($key)] = $value;}

function indent_of($line) {
if (preg_match('/^[ \t]*/', (string)$line, $match)) return strlen($match[0]);
return 0;}

function mouse_point($which) {
if ($which === 'x') $command = 'vision xy_mouseLocation = Env.getMouseLocation(); output_sikuli_text(str(xy_mouseLocation.getX()))';
else if ($which === 'y') $command = 'vision xy_mouseLocation = Env.getMouseLocation(); output_sikuli_text(str(xy_mouseLocation.getY()))';
else $command = "vision xy_mouseLocation = Env.getMouseLocation(); xy_x = xy_mouseLocation.getX(); xy_y = xy_mouseLocation.getY(); output_sikuli_text('(' + str(xy_x) + ',' + str(xy_y) + ')')";
try {$ok = TaguiSikuli::run($command, max(30.0, (float)$this->timeout));}
catch (Exception $e) {throw new TaguiExprError($e->getMessage());}
if (!$ok) throw new TaguiExprError('cannot read mouse position');
$text = trim(TaguiSikuli::text());
if ($which === 'xy') return $text;
return (int)$text;}

function default_text_file($append) {
$dir = $this->flow_path !== '' ? $this->flow_path : getcwd();
if ($append) {if ($this->text_count === 0) $this->text_count = 1;}
else $this->text_count++;
return $dir . DIRECTORY_SEPARATOR . 'text' . $this->text_count . '.txt';}

function is_statement_list($line) {
return (bool)preg_match('/;\s*\S/', $line);}

function strip_var_keyword($line) {
$stripped = preg_replace('/\bvar\s+/', '', $line);
return is_string($stripped) ? $stripped : $line;}

function expand_plus_concat($line) {
$engine = $this;
$expanded = preg_replace_callback('/([\'"])\\+([A-Za-z_][A-Za-z0-9_]*)\\+\\1/', function ($match) use ($engine) {
$name = $match[2];
if (!array_key_exists($name, $engine->vars)) return 'undefined';
return $engine->stringish($engine->vars[$name]);}, $line);
return is_string($expanded) ? $expanded : $line;}

function is_assignment($line) {
if (str_starts_with($line, 'var ')) $line = trim(substr($line, 4));
return (bool)preg_match('/^[A-Za-z_][A-Za-z0-9_]*\s*=\s*.+$/', $line);}

function add_concat($source) {
$quote = null;
if (str_contains($source, "'")) $quote = "'";
else if (str_contains($source, '"')) $quote = '"';
$within = false; $out = '';
$length = strlen($source);
for ($i = 0; $i < $length; $i++) {
$ch = $source[$i];
if ($quote !== null && $ch === $quote) $within = !$within;
if (!$within && $ch === ' ') $out .= '+'; else $out .= $ch;}
return preg_replace('/\+{2,}/', '+', $out);}

function expand_backticks($line) {
$line = tagui_apply_repository($line, $this->repo);
$count = substr_count($line, '`');
if ($count < 2 || ($count % 2) === 1) return $this->expand_plus_concat($line);
$keep = $this->backtick_expression_line($line);
$engine = $this;
$expanded = preg_replace_callback('/`([^`]*)`/', function ($match) use ($engine, $keep) {
$inner = $match[1];
if ($inner === '') return $keep ? "''" : '';
if ($keep) return "''+(" . $inner . ")+''";
return $engine->stringish($engine->expr_value($inner));}, $line);
return $this->expand_plus_concat($expanded === null ? $line : $expanded);}

function backtick_expression_line($line) {
$lower = strtolower(ltrim($line));
if (preg_match('/^(echo|dump|check|write|save|if|else if|while)\b/', $lower)) return true;
return $this->is_assignment($line);}

function need($locator, $index) {
if (!$this->require_chrome($index)) return null;
$info = $this->chrome->find($locator, $this->live);
if ($info === null) {$this->fail($index, 'cannot find ' . $locator); return null;}
return $info;}

function require_chrome($index) {
if ($this->chrome !== null) return true;
$this->fail($index, 'browser is not open'); return false;}

function fail($index, $message) {
$prefix = 'ERROR - ';
if ($index >= 0 && !$this->live) $prefix .= '[LINE ' . ($index + 1) . '] ';
$this->emit($prefix . $message);
// Chrome is still open. Visual/OculiX timeouts use this same path; they do not say "cannot find".
if (!$this->live) $this->capture_failure_evidence();
if (!$this->live) $this->stopped = true;}

function capture_failure_evidence() {
if ($this->live || $this->failure_captured) return;
$this->failure_captured = true;
$dir = $this->evidence_dir !== '' ? $this->evidence_dir : $this->flow_path;
if (!is_string($dir) || $dir === '' || !is_dir($dir)) return;
if ($this->chrome === null || (!method_exists($this->chrome, 'capture_tabs') && !method_exists($this->chrome, 'screenshot'))) return;
try {
if (method_exists($this->chrome, 'capture_tabs')) $this->chrome->capture_tabs($dir);
else $this->chrome->screenshot($dir . DIRECTORY_SEPARATOR . 'failure_tab.png', null);}
catch (Exception $e) {}
try {tagui_capture_desktop($dir . DIRECTORY_SEPARATOR . 'failure_desktop.png');} catch (Exception $e) {}}

function is_page_snap($target) {
return strcasecmp(trim((string)$target), 'page') === 0;}

function is_desktop_snap($target) {
$name = strtolower(str_replace('\\', '/', trim((string)$target)));
return str_ends_with($name, 'page.png') || str_ends_with($name, 'page.bmp');}

function redacted_trace($line) {
if (tagui_keyword_intent($line) !== 'type') return $line;
$pos = stripos($line, ' as ');
if ($pos === false) return $line;
return substr($line, 0, $pos + 4) . '[redacted]';}

function trace($line) {if (!$this->quiet && !$this->live) $this->emit($this->redacted_trace($line));}

function emit($text) {$this->emit_raw($text);}

function emit_raw($text) {
if ($this->capture) {$this->captured[] = (string)$text; return;}
tagui_write_output($text . "\n");}

function stringish($value) {
if ($value === true) return 'true'; if ($value === false) return 'false'; if ($value === null) return '';
if (is_float($value)) {if ((float)(int)$value === $value) return (string)(int)$value;}
return (string)$value;}

function next_snap() {$this->snap_count++; return $this->flow_path . DIRECTORY_SEPARATOR . 'snap' . $this->snap_count . '.png';}

function abs_file($filename) {
if ($filename === '') return '';
if ($filename[0] === '/' || $filename[0] === '\\') return $filename;
if (strlen($filename) > 2 && $filename[1] === ':') return $filename;
return $this->flow_path . DIRECTORY_SEPARATOR . $filename;}

function next_code($lines, $i, $end) {
while ($i <= $end) {
$trimmed = trim($lines[$i]);
if ($trimmed !== '' && !str_starts_with($trimmed, '//')) return $i;
$i++;}
return null;}

function find_block_end($lines, $open, $end) {
$depth = 0;
for ($j = $open; $j <= $end; $j++) {
$trimmed = trim($lines[$j]);
if ($trimmed === '{') $depth++;
else if ($trimmed === '}') {$depth--; if ($depth === 0) return $j;}}
return -1;}}

/* -------------------------------------------------------------------------- */
/* Entry point                                                                */
/* -------------------------------------------------------------------------- */

function tagui_png_file($path, $minimum = 32) {
if (!is_string($path) || !is_file($path)) return false;
$size = filesize($path);
if ($size === false || $size < (int)$minimum) return false;
$handle = fopen($path, 'rb');
if ($handle === false) return false;
$magic = fread($handle, 8);
fclose($handle);
return $magic === "\x89PNG\r\n\x1a\n";}

function tagui_run_bounded($command, $seconds) {
$null = DIRECTORY_SEPARATOR === '\\' ? 'NUL' : '/dev/null';
$descriptor = array(
0 => array('file', $null, 'r'),
1 => array('file', $null, 'w'),
2 => array('file', $null, 'w'));
$process = proc_open($command, $descriptor, $pipes);
if (!is_resource($process)) return 127;
$deadline = microtime(true) + max(1, (float)$seconds);
$code = null;
while (microtime(true) < $deadline) {
$status = proc_get_status($process);
if (!is_array($status)) break;
if (!empty($status['running'])) {usleep(100000); continue;}
// The first status call that sees the process stop holds the real exit code.
// proc_close can then return -1 because that wait already collected it.
$code = isset($status['exitcode']) ? (int)$status['exitcode'] : -1;
break;}
if ($code === null) {
proc_terminate($process);
usleep(200000);
$status = proc_get_status($process);
if (is_array($status) && !empty($status['running'])) proc_terminate($process, 9);
proc_close($process);
return 124;}
$closed = proc_close($process);
if ($code < 0 && is_int($closed) && $closed >= 0) return $closed;
return $code;}

function tagui_desktop_commands($path) {
if (DIRECTORY_SEPARATOR === '\\') return array();
if (PHP_OS_FAMILY === 'Darwin') return array(array('screencapture', '-x', $path));
$display = getenv('DISPLAY');
if (!is_string($display) || trim($display) === '') return array();
$commands = array(
array('import', '-window', 'root', $path),
array('gm', 'import', '-window', 'root', $path),
array('scrot', $path),
array('gnome-screenshot', '-f', $path),
array('maim', $path),
array('ffmpeg', '-hide_banner', '-loglevel', 'error', '-y', '-f', 'x11grab', '-i', $display, '-frames:v', '1', $path));
$script = __DIR__ . DIRECTORY_SEPARATOR . 'tagui_desktop.py';
if (is_file($script)) $commands[] = array('python3', $script, $path);
return $commands;}

function tagui_capture_desktop_xwd($path) {
if (DIRECTORY_SEPARATOR === '\\' || PHP_OS_FAMILY === 'Darwin') return false;
$display = getenv('DISPLAY');
if (!is_string($display) || trim($display) === '') return false;
$xwd_bin = tagui_which('xwd');
$convert = tagui_which('convert');
if ($xwd_bin === '' || $convert === '') return false;
$temp = tempnam(sys_get_temp_dir(), 'tagui_xwd_');
if ($temp === false) return false;
$xwd = $temp . '.xwd';
@unlink($temp);
tagui_run_bounded(array($xwd_bin, '-root', '-silent', '-out', $xwd), 8);
if (is_file($xwd) && filesize($xwd) > 100) tagui_run_bounded(array($convert, $xwd, 'png:' . $path), 8);
@unlink($xwd);
if (tagui_png_file($path, 101)) return true;
if (is_file($path)) @unlink($path);
return false;}

function tagui_capture_desktop($path) {
if (!is_string($path) || $path === '') return false;
$dir = dirname($path);
if ($dir === '' || !is_dir($dir)) return false;
if (is_file($path)) @unlink($path);
foreach (tagui_desktop_commands($path) as $command) {
$bin = tagui_which($command[0]);
if ($bin === '') continue;
$command[0] = $bin;
// import and the libX11 grabber can exit -1 or non-zero after writing a good PNG.
tagui_run_bounded($command, 12);
if (tagui_png_file($path, 101)) return true;
if (is_file($path)) @unlink($path);}
return tagui_capture_desktop_xwd($path);}

function tagui_write_output($text) {
echo $text;
fflush(STDOUT);
$log = getenv('TAGUI_LOG');
if (!is_string($log) || $log === '') return;
$handle = fopen($log, 'ab');
if ($handle === false) return;
fwrite($handle, $text);
fclose($handle);}

function tagui_modern_main($argv) {
ini_set('display_errors', 'stderr');
while (ob_get_level() > 0) {ob_end_flush();}
ob_implicit_flush(true);
if (!isset($argv[1]) || $argv[1] === '') {tagui_write_output("ERROR - specify flow filename as first parameter\n"); return 1;}
$flow = $argv[1];
if (!is_file($flow)) {tagui_write_output("ERROR - cannot find $flow\n"); return 1;}
$browser = getenv('tagui_web_browser');
if ($browser !== 'chrome' && $browser !== 'headless') $browser = 'headless';
$quiet = getenv('tagui_quiet_mode') === 'true';
$chrome = new TaguiChrome();
$engine = new TaguiModern($chrome, $quiet, dirname($flow));
$cli = isset($argv[2]) ? array_slice($argv, 2) : array();
$csv_arg = '';
$params = array();
foreach ($cli as $arg) {
if (is_string($arg) && strlen($arg) > 4 && substr(strtolower($arg), -4) === '.csv') $csv_arg = $arg;
else if ($arg !== '') $params[] = $arg;}
$engine->set_cli_params($params);
try {$datasets = tagui_datatable_rows($flow, $csv_arg);}
catch (Exception $e) {tagui_write_output('ERROR - ' . $e->getMessage() . "\n"); return 1;}
$chrome_started = false;
pcntl_async_signals(true);
$stop = function () use ($chrome, &$chrome_started) {
TaguiHelpers::shutdown();
if ($chrome_started) {$chrome->stop(); $chrome_started = false;}};
pcntl_signal(SIGTERM, function () use ($stop) {$stop(); exit(0);});
pcntl_signal(SIGINT, function () use ($stop) {$stop(); exit(130);});
register_shutdown_function($stop);
try {
$chrome->start($browser !== 'chrome');
$chrome->timeout = $engine->timeout;
$chrome_started = true;
$lines = file($flow, FILE_IGNORE_NEW_LINES);
if ($lines === false) $lines = array();
$lines = tagui_translate_lines($lines, tagui_active_language(__DIR__));
$speed = getenv('tagui_speed_mode') === 'true';
$failed = false;
foreach ($datasets as $index => $repo) {
if ($index > 0 && !$speed) sleep(3);
$engine->repo = $repo;
$engine->stopped = false;
$engine->jump = null;
$engine->run_lines($lines);
if ($engine->stopped) $failed = true;}}
catch (Exception $e) {
tagui_write_output('ERROR - ' . $e->getMessage() . "\n");
try {$engine->capture_failure_evidence();} catch (Exception $ignored) {}
$stop();
return 1;}
$stop();
return $failed ? 1 : 0;}

if (PHP_SAPI === 'cli' && isset($argv[0]) && realpath($argv[0]) === realpath(__FILE__)) {
exit(tagui_modern_main($argv));}
