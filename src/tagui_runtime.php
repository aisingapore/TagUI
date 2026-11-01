<?php

/* Helpers for the modern engine: Python, R, Sikuli/OculiX, and translation.
   PHP stays the flow runtime. These are the same external programs the classic
   engine already optional-starts, reached without CasperJS. */

function tagui_file_url($url) {
$url = (string)$url;
if (stripos($url, 'file://') !== 0) return $url;
$rest = substr($url, strlen('file://'));
if (stripos($rest, 'localhost') === 0) $rest = substr($rest, strlen('localhost'));
$rest = rawurldecode($rest);
if (DIRECTORY_SEPARATOR === '\\' && preg_match('#^/([A-Za-z]:.*)$#', $rest, $match)) $rest = $match[1];
if ($rest === '') return $url;
$real = realpath($rest);
if (!is_string($real) || $real === '') return $url;
$real = str_replace('\\', '/', $real);
$parts = explode('/', $real);
$encoded = array();
foreach ($parts as $part) $encoded[] = $part === '' ? '' : rawurlencode($part);
$path = implode('/', $encoded);
if (DIRECTORY_SEPARATOR === '\\') return 'file:///' . ltrim($path, '/');
return 'file://' . $path;}

function tagui_png_size($path) {
$handle = fopen($path, 'rb');
if ($handle === false) return null;
$header = fread($handle, 24);
fclose($handle);
if (!is_string($header) || strlen($header) < 24) return null;
if (substr($header, 0, 8) !== "\x89PNG\r\n\x1a\n") return null;
return array('width' => unpack('N', substr($header, 16, 4))[1], 'height' => unpack('N', substr($header, 20, 4))[1]);}

function tagui_local_timezone() {
$candidates = array();
$env = getenv('TZ');
if (is_string($env) && $env !== '') $candidates[] = $env;
$link = @readlink('/etc/localtime');
if (is_string($link) && $link !== '') {
$pos = strpos($link, 'zoneinfo/');
if ($pos !== false) $candidates[] = substr($link, $pos + strlen('zoneinfo/'));}
if (is_file('/etc/timezone')) {
$file = trim((string)file_get_contents('/etc/timezone'));
if ($file !== '') $candidates[] = $file;}
foreach ($candidates as $name) {
try {return new DateTimeZone($name);} catch (Exception $e) {}}
if (DIRECTORY_SEPARATOR !== '\\') {
$offset = trim((string)shell_exec('date +%z 2>/dev/null'));
if (preg_match('/^[+-]\d{4}$/', $offset) === 1) {
try {return new DateTimeZone(substr($offset, 0, 3) . ':' . substr($offset, 3, 2));} catch (Exception $e) {}}}
return new DateTimeZone(date_default_timezone_get());}

function tagui_local_stamp() {
return (new DateTimeImmutable('now', tagui_local_timezone()))->format('D M j Y H:i:s');}

function tagui_which($name) {
$command = DIRECTORY_SEPARATOR === '\\' ? 'where ' : 'command -v ';
$out = array();
exec($command . escapeshellarg($name) . ' 2> ' . (DIRECTORY_SEPARATOR === '\\' ? 'NUL' : '/dev/null'), $out, $code);
if ($code !== 0 || !isset($out[0])) return '';
return trim($out[0]);}

function tagui_configured_language($src_dir) {
$path = $src_dir . DIRECTORY_SEPARATOR . 'tagui_config.txt';
$config = is_file($path) ? (string)file_get_contents($path) : '';
if (preg_match("/tagui_language\\s*=\\s*['\\\"]?([A-Za-z]+)['\\\"]?/", $config, $match)) return strtolower($match[1]);
return 'english';}

function tagui_active_language($src_dir) {
$env = getenv('TAGUI_LANGUAGE');
if (is_string($env) && trim($env) !== '') return strtolower(trim($env));
return tagui_configured_language($src_dir);}

function tagui_load_translator($language) {
if (isset($GLOBALS['tagui_translator_language']) && $GLOBALS['tagui_translator_language'] === $language && function_exists('translate_intent')) return;
$argv = array('translate.php', 'tagui_parse.php', 'from', $language);
$cwd = getcwd();
chdir(dirname(__FILE__));
require dirname(__FILE__) . '/translate.php';
@unlink(dirname(__FILE__) . '/translate.log');
if (is_string($cwd) && $cwd !== '') chdir($cwd);
foreach (array(
'language_count', 'language_data', 'column_from', 'column_to', 'start_keywords',
'conditions_keywords', 'start_conditions_keywords', 'seconds_keywords', 'start_seconds_keywords',
'forloop_keywords', 'to_separator_keywords', 'as_separator_keywords', 'helper_keywords', 'start_helper_keywords'
) as $name) {
if (isset($$name)) $GLOBALS[$name] = $$name;}
$GLOBALS['tagui_translator_language'] = $language;}

function tagui_translate_lines($lines, $language) {
$language = strtolower(trim((string)$language));
if ($language === '' || $language === 'english') return $lines;
tagui_load_translator($language);
$cwd = getcwd();
chdir(sys_get_temp_dir());
$translated = array();
foreach ($lines as $line) $translated[] = rtrim((string)translate_intent($line), "\n");
@unlink(sys_get_temp_dir() . '/translate.log');
if (is_string($cwd) && $cwd !== '') chdir($cwd);
return $translated;}

function tagui_dedent($text) {
$lines = explode("\n", str_replace("\r\n", "\n", (string)$text));
$min = null;
foreach ($lines as $line) {
if (trim($line) === '') continue;
preg_match('/^[ \t]*/', $line, $match);
$width = strlen($match[0]);
if ($min === null || $width < $min) $min = $width;}
if ($min === null || $min === 0) return implode("\n", $lines);
$out = array();
foreach ($lines as $line) {
if (trim($line) === '') $out[] = '';
else $out[] = substr($line, $min);}
return implode("\n", $out);}

function tagui_js_assignment_names($code) {
$names = array();
if (preg_match_all('/(?<![A-Za-z0-9_$.])([A-Za-z_][A-Za-z0-9_]*)\s*=(?!=)/', (string)$code, $matches)) {
foreach ($matches[1] as $name) $names[$name] = true;}
return array_keys($names);}

function tagui_js_helpers() {
return tagui_page_finder_js() . <<<'JS'

function title() {return document.title;}
function url() {return String(document.location.href);}
function text() {return document.body ? (document.body.innerText || document.body.textContent || "") : "";}
function exist(locator) {var info = taguiDescribe(String(locator)); return !!(info && info.found);}
function present(locator) {return exist(locator);}
function visible(locator) {var info = taguiDescribe(String(locator)); return !!(info && info.found && info.visible);}
function count(locator) {var info = taguiDescribe(String(locator)); return info && info.count ? info.count : 0;}
JS;}

function tagui_prepare_js($code) {
if (preg_match('/\bthis\s*\.\s*(?!echo\b)([A-Za-z_][A-Za-z0-9_]*)/', (string)$code, $match)) {
throw new RuntimeException('Casper-only step this.' . $match[1]);}
$rewritten = preg_replace('/\bthis\s*\.\s*echo\s*\(/', 'tagui_echo(', (string)$code);
return is_string($rewritten) ? $rewritten : (string)$code;}

function tagui_js_program($code, $vars) {
$box = array();
if (is_array($vars)) {
foreach ($vars as $key => $value) {
if (!is_string($key) || !preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $key)) continue;
if (is_array($value) || $value === null || is_string($value) || is_int($value) || is_float($value) || is_bool($value)) $box[$key] = $value;}}
foreach (tagui_js_assignment_names($code) as $name) {
if (!array_key_exists($name, $box)) $box[$name] = null;}
$payload = json_encode($box, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
if ($payload === false) $payload = '{}';
$body = tagui_js_helpers() . "\nwith (tagui_vars) {\n" . $code . "\n}";
$body_json = json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
return "(function(){\n" .
"var tagui_box = " . $payload . ";\n" .
"var tagui_proxy = new Proxy(tagui_box, {\n" .
"has: function(target, prop) {return typeof prop === \"string\" && Object.prototype.hasOwnProperty.call(target, prop);},\n" .
"get: function(target, prop) {return target[prop];},\n" .
"set: function(target, prop, value) {target[prop] = value; return true;}\n" .
"});\n" .
"var tagui_echos = [];\n" .
"function tagui_echo(v) {tagui_echos.push(v === undefined || v === null ? '' : String(v));}\n" .
"var tagui_run = new Function(\"tagui_vars\", \"tagui_echo\", " . $body_json . ");\n" .
"var tagui_value;\n" .
"try {tagui_value = tagui_run(tagui_proxy, tagui_echo);}\n" .
"catch (e) {return {error: String(e && e.message ? e.message : e)};}\n" .
"var out = {};\n" .
"for (var k in tagui_box) {\n" .
"if (!Object.prototype.hasOwnProperty.call(tagui_box, k)) continue;\n" .
"var v = tagui_box[k];\n" .
"var t = typeof v;\n" .
"if (v === null || t === \"string\" || t === \"number\" || t === \"boolean\" || (t === \"object\" && v !== null)) out[k] = v;}\n" .
"return {value: tagui_value === undefined ? null : tagui_value, vars: out, echo: tagui_echos};\n" .
"})()";}

function tagui_js_eval_node($script) {
$bin = tagui_which('node');
if ($bin === '') throw new RuntimeException('browser is not open');
$descriptor = array(0 => array('pipe', 'r'), 1 => array('pipe', 'w'), 2 => array('pipe', 'w'));
$process = proc_open(array($bin, '-e', 'let s="";process.stdin.on("data",function(d){s+=d;});process.stdin.on("end",function(){try{var r=eval(s);process.stdout.write(JSON.stringify(r));}catch(e){process.stdout.write(JSON.stringify({error:String(e&&e.message||e)}));}});'), $descriptor, $pipes);
if (!is_resource($process)) throw new RuntimeException('cannot start node');
fwrite($pipes[0], $script);
fclose($pipes[0]);
$out = stream_get_contents($pipes[1]);
$err = stream_get_contents($pipes[2]);
fclose($pipes[1]); fclose($pipes[2]);
proc_close($process);
$decoded = json_decode((string)$out, true);
if (!is_array($decoded)) throw new RuntimeException('page script failed: ' . trim($err . ' ' . $out));
return $decoded;}

class TaguiPipe {
public $label = '';
private $process = null;
private $in = null;
private $out = null;
private $bin = '';
private $script_path = '';

function __construct($label) {$this->label = $label;}

function running() {return is_resource($this->process);}

function start($bin, $script) {
if ($this->running()) return;
if ($bin === '' || !is_file($bin) && tagui_which($bin) === '' && !is_executable($bin)) {
throw new RuntimeException($this->label . ' step needs ' . $bin . ' on PATH');}
$path = tempnam(sys_get_temp_dir(), 'tagui_' . $this->label . '_');
if ($path === false) throw new RuntimeException('cannot create ' . $this->label . ' helper');
file_put_contents($path, $script);
$descriptor = array(0 => array('pipe', 'r'), 1 => array('pipe', 'w'), 2 => array('pipe', 'w'));
$argv = array($bin, $path);
if ($this->label === 'py') array_splice($argv, 1, 0, array('-u'));
$process = proc_open($argv, $descriptor, $pipes);
if (!is_resource($process)) {@unlink($path); throw new RuntimeException('cannot start ' . $bin);}
$this->script_path = $path;
$this->process = $process;
$this->in = $pipes[0];
$this->out = $pipes[1];
$this->bin = $bin;
stream_set_blocking($this->in, true);
stream_set_blocking($this->out, false);
fclose($pipes[2]);}

function exec_code($code, $timeout) {
if (!$this->running()) throw new RuntimeException($this->label . ' helper is not running');
$payload = json_encode(array('code' => $code)) . "\n";
$written = 0;
$deadline = microtime(true) + max(1, (float)$timeout);
while ($written < strlen($payload)) {
if (microtime(true) > $deadline) throw new RuntimeException($this->label . ' step exceeded ' . $timeout . 's timeout');
$chunk = fwrite($this->in, substr($payload, $written));
if ($chunk === false) throw new RuntimeException($this->label . ' helper closed');
$written += $chunk;
fflush($this->in);}
$line = '';
while (strpos($line, "\n") === false) {
if (microtime(true) > $deadline) throw new RuntimeException($this->label . ' step exceeded ' . $timeout . 's timeout');
$chunk = fread($this->out, 65536);
if ($chunk === false || $chunk === '') {usleep(20000); continue;}
$line .= $chunk;}
$decoded = json_decode(trim($line), true);
if (!is_array($decoded)) throw new RuntimeException($this->label . ' helper returned ' . trim($line));
return $decoded;}

function request($line, $timeout) {
if (!$this->running()) throw new RuntimeException($this->label . ' helper is not running');
$deadline = microtime(true) + max(1, (float)$timeout);
$payload = $line . "\n";
$written = 0;
while ($written < strlen($payload)) {
if (microtime(true) > $deadline) throw new RuntimeException($this->label . ' step exceeded ' . $timeout . 's timeout');
$chunk = fwrite($this->in, substr($payload, $written));
if ($chunk === false) throw new RuntimeException($this->label . ' helper closed');
$written += $chunk;}
fflush($this->in);
$reply = '';
while (strpos($reply, "\n") === false) {
if (microtime(true) > $deadline) throw new RuntimeException($this->label . ' step exceeded ' . $timeout . 's timeout');
$chunk = fread($this->out, 65536);
if ($chunk === false || $chunk === '') {usleep(20000); continue;}
$reply .= $chunk;}
return rtrim(substr($reply, 0, strpos($reply, "\n")), "\r");}

function stop() {
if (!is_resource($this->process)) return;
if (is_resource($this->in)) {@fwrite($this->in, "finish\n"); @fclose($this->in);}
if (is_resource($this->out)) @fclose($this->out);
proc_terminate($this->process);
proc_close($this->process);
$this->process = null;
if ($this->script_path !== '') {@unlink($this->script_path); $this->script_path = '';}}}

function tagui_python_script() {
return <<<'PY'
import json, sys, io, traceback
scope = {"__name__": "__main__"}
while True:
    raw = sys.stdin.readline()
    if raw == "":
        break
    raw = raw.strip()
    if not raw:
        continue
    if raw == "finish":
        break
    try:
        req = json.loads(raw)
    except Exception:
        sys.stdout.write(json.dumps({"ok": False, "out": "", "err": "bad request"}) + "\n")
        sys.stdout.flush()
        continue
    if req.get("op") == "finish":
        break
    buf = io.StringIO()
    old_out, old_err = sys.stdout, sys.stderr
    sys.stdout = sys.stderr = buf
    err = None
    try:
        exec(req.get("code", ""), scope)
    except Exception:
        err = traceback.format_exc().strip().splitlines()[-1]
    sys.stdout, sys.stderr = old_out, old_err
    sys.stdout.write(json.dumps({"ok": err is None, "out": buf.getvalue(), "err": err}) + "\n")
    sys.stdout.flush()
PY;}

function tagui_r_join($body) {
$lines = preg_split('/\r\n|\n|\r/', (string)$body);
if (!is_array($lines)) return '';
$joined = '';
foreach ($lines as $line) {
$indent = str_replace(ltrim($line), '', $line);
$trim = trim($line);
if ($trim === '') continue;
$joined .= $indent . $trim . '[END_OF_LINE]';}
return str_replace(';; ', '; ', str_replace('[END_OF_LINE]', '; ', $joined));}

function tagui_r_script() {
return <<<'R'
sink(file(nullfile(), open = "w"), type = "message")
con <- file("stdin", open = "r")
repeat {
  path <- readLines(con, n = 1, warn = FALSE)
  if (length(path) == 0) break
  path <- trimws(path)
  if (!nzchar(path)) next
  if (path == "finish") break
  err <- ""
  out <- ""
  ok <- tryCatch({
    code <- paste(readLines(path, warn = FALSE), collapse = "\n")
    printed <- capture.output(invisible(eval(parse(text = code), envir = .GlobalEnv)))
    if (length(printed) == 0) out <<- "" else out <<- paste(trimws(printed), collapse = " ")
    TRUE
  }, error = function(e) { err <<- gsub("[\r\n]+", " ", conditionMessage(e)); FALSE })
  if (ok) cat("OK ", out, "\n", sep = "") else cat("ERR ", err, "\n", sep = "")
  flush.console()
}
R;}

class TaguiHelpers {
public static $py = null;
public static $r = null;

static function python($code, $timeout) {
if (self::$py === null || !self::$py->running()) {
$bin = getenv('TAGUI_PYTHON');
if (!is_string($bin) || $bin === '') $bin = tagui_which('python3');
if ($bin === '') $bin = tagui_which('python');
self::$py = new TaguiPipe('py');
self::$py->start($bin === '' ? 'python3' : $bin, tagui_python_script());}
return self::$py->exec_code($code, $timeout);}

static function r($code, $timeout) {
if (self::$r === null || !self::$r->running()) {
$bin = getenv('TAGUI_RSCRIPT');
if (!is_string($bin) || $bin === '') $bin = tagui_which('Rscript');
self::$r = new TaguiPipe('r');
self::$r->start($bin === '' ? 'Rscript' : $bin, tagui_r_script());}
$path = tempnam(sys_get_temp_dir(), 'tagui_r_code_');
if ($path === false) throw new RuntimeException('cannot create R script');
file_put_contents($path, $code);
$reply = self::$r->request($path, $timeout);
@unlink($path);
if (str_starts_with($reply, 'OK ')) return substr($reply, 3);
if (str_starts_with($reply, 'OK')) return '';
if (str_starts_with($reply, 'ERR ')) throw new RuntimeException(substr($reply, 4));
throw new RuntimeException('R helper returned ' . $reply);}

static function shutdown() {
if (self::$py !== null) self::$py->stop();
if (self::$r !== null) self::$r->stop();
self::$py = null;
self::$r = null;
TaguiSikuli::stop();}}

class TaguiSikuli {
private static $process = null;
private static $count = 0;
private static $last_result = '';

static function log_path() {return sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'tagui_oculix.log';}

static function startup_failure($log) {
return self::failure_message('OculiX exited before it was ready.', $log);}

static function step_crash($log) {
return self::failure_message('OculiX exited during the visual step.', $log);}

static function failure_message($lead, $log) {
$text = is_file($log) ? (string)file_get_contents($log) : '';
$message = $lead;
$errors = self::error_lines($text);
if ($errors !== '') $message .= ' ' . $errors;
$cause = self::cause_line($text);
if ($cause === '') $cause = self::relevant_line($text);
if ($cause !== '' && !str_contains($message, $cause)) $message .= ' OculiX said: ' . $cause;
$hint = self::environment_hint($text);
if ($hint !== '') $message .= ' ' . $hint;
return $message . ' See ' . $log;}

static function environment_hint($text) {
if (str_contains($text, 'UnsupportedClassVersionError')) {
return 'OculiX requires Java 17+; set PATH/JAVA_HOME or TAGUI_JAVA';}
$permission = self::permission_hint($text);
if ($permission !== '') return $permission;
if (str_contains($text, 'HeadlessException') || str_contains($text, 'running in headless environment')) {
return 'Java is a headless build. Install the full JRE (not the openjdk-*-jre-headless package) and set DISPLAY to an X11 session. Wayland is untested.';}
return '';}

static function permission_hint($text) {
if (str_contains($text, 'Mouse not useable') || str_contains($text, 'Mouse.init')) {
return 'macOS blocked mouse input. Grant Accessibility and Screen Recording to the app that launches TagUI (Terminal, iTerm, or your IDE), then run the flow again.';}
return '';}

static function cause_line($text) {
$lines = preg_split('/\r\n|\n|\r/', (string)$text);
if (!is_array($lines)) return '';
foreach ($lines as $line) {
$line = trim($line);
if ($line === '' || str_starts_with($line, '[DEBUG')) continue;
if (preg_match('/^\.\.\. \d+ more$/', $line) === 1) continue;
if (str_starts_with($line, 'Caused by:') || str_starts_with($line, 'Exception in thread') || preg_match('/\b(?:Exception|Error):/', $line) === 1) {
if (strlen($line) > 300) $line = substr($line, 0, 300);
return $line;}}
return '';}

static function error_lines($text) {
$lines = preg_split('/\r\n|\n|\r/', (string)$text);
if (!is_array($lines)) return '';
$found = array();
foreach ($lines as $line) {
$line = trim($line);
if ($line === '' || stripos($line, '[error]') === false) continue;
if (strlen($line) > 220) $line = substr($line, 0, 220);
$found[] = $line;
if (count($found) >= 4) break;}
return implode(' ', $found);}

static function relevant_line($text) {
$lines = preg_split('/\r\n|\n|\r/', trim((string)$text));
if (!is_array($lines)) return '';
for ($i = count($lines) - 1; $i >= 0; $i--) {
$line = trim($lines[$i]);
if ($line === '' || str_starts_with($line, '[DEBUG')) continue;
if (preg_match('/^\.\.\. \d+ more$/', $line) === 1) continue;
if (strlen($line) > 300) $line = substr($line, 0, 300);
return $line;}
return '';}

static function failure_detail($result) {
$note = trim(preg_replace('/^ERROR\s*/', '', (string)$result));
if ($note !== '' && str_contains($note, 'not on screen')) return 'Image ' . $note . '.';
return 'The target was not found on screen.';}

static function jar() {
$env = getenv('TAGUI_SIKULI_JAR');
if (is_string($env) && $env !== '' && is_file($env)) return $env;
$src = dirname(__FILE__);
foreach (array('sikulix/oculixide.jar', 'sikulix/sikulix.jar') as $relative) {
$path = $src . DIRECTORY_SEPARATOR . $relative;
if (is_file($path)) return $path;}
return '';}

static function java_binary() {
$chosen = getenv('TAGUI_JAVA');
if (is_string($chosen) && trim($chosen) !== '') return trim($chosen);
$home = getenv('JAVA_HOME');
if (is_string($home) && trim($home) !== '') {
$name = DIRECTORY_SEPARATOR === '\\' ? 'java.exe' : 'java';
$candidate = rtrim($home, "/\\") . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . $name;
if (is_file($candidate)) return $candidate;}
return 'java';}

static function run($line, $timeout) {
self::start($timeout);
self::$count++;
$src = dirname(__FILE__);
$in = $src . '/tagui.sikuli/tagui_sikuli.in';
$out = $src . '/tagui.sikuli/tagui_sikuli.out';
@unlink($src . '/tagui.sikuli/tagui_sikuli.txt');
file_put_contents($in, '[' . self::$count . '] ' . $line);
$marker = '[' . self::$count . '] ';
$deadline = microtime(true) + max(5, (float)$timeout);
while (microtime(true) < $deadline) {
$text = is_file($out) ? (string)file_get_contents($out) : '';
if (str_contains($text, $marker)) {
self::$last_result = trim(substr($text, strpos($text, $marker) + strlen($marker)));
return str_contains(self::$last_result, 'SUCCESS');}
if (!self::alive()) throw new RuntimeException(self::step_crash(self::log_path()));
usleep(200000);}
throw new RuntimeException('visual step timed out waiting for OculiX');}

static function last_result() {return self::$last_result;}

static function alive() {
if (!is_resource(self::$process)) return false;
$status = proc_get_status(self::$process);
if (is_array($status) && !empty($status['running'])) return true;
proc_close(self::$process);
self::$process = null;
self::$count = 0;
return false;}

static function start($timeout) {
if (self::alive()) return;
$jar = self::jar();
if ($jar === '') {
throw new RuntimeException('visual step needs OculiX or SikuliX. Set TAGUI_SIKULI_JAR to oculixide.jar (Java 17 or newer; a SikuliX 1.1/2 jar can still run on Java 8). The engine then runs src/tagui.sikuli through that jar. https://github.com/oculix-org/Oculix');}
$src = dirname(__FILE__);
$log = self::log_path();
@unlink($src . '/tagui.sikuli/tagui_sikuli.out');
file_put_contents($log, '');
$descriptor = array(
0 => array('file', DIRECTORY_SEPARATOR === '\\' ? 'NUL' : '/dev/null', 'r'),
1 => array('file', $log, 'a'),
2 => array('file', $log, 'a'));
$java = self::java_binary();
$process = proc_open(array($java, '-jar', $jar, '-r', $src . '/tagui.sikuli'), $descriptor, $pipes, $src);
if (!is_resource($process)) throw new RuntimeException('cannot start ' . $java . ' -jar ' . $jar . '. Set PATH, JAVA_HOME, or TAGUI_JAVA.');
self::$process = $process;
self::$count = 0;
$deadline = microtime(true) + max(90, (float)$timeout);
$out = $src . '/tagui.sikuli/tagui_sikuli.out';
while (microtime(true) < $deadline) {
if (is_file($out) && str_contains((string)file_get_contents($out), '[0] START')) return;
if (!self::alive()) throw new RuntimeException(self::startup_failure($log));
usleep(200000);}
$ready = self::failure_message('OculiX did not become ready.', $log);
if (!str_contains($ready, 'Java is a headless build') && !str_contains($ready, 'macOS blocked')) {
$ready = str_replace('OculiX did not become ready.', 'OculiX did not become ready. A display is required. On Linux install the full JRE (not the openjdk-*-jre-headless package) and set DISPLAY to an X11 session (Xvfb is enough; Wayland is untested).', $ready);}
throw new RuntimeException($ready);}

static function text() {
$path = dirname(__FILE__) . '/tagui.sikuli/tagui_sikuli.txt';
if (!is_file($path)) return '';
return trim((string)file_get_contents($path));}

static function stop() {
if (!is_resource(self::$process)) return;
$in = dirname(__FILE__) . '/tagui.sikuli/tagui_sikuli.in';
@file_put_contents($in, 'finish');
usleep(300000);
proc_terminate(self::$process);
proc_close(self::$process);
self::$process = null;
self::$count = 0;}}
