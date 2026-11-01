<?php

/* SHARED STEP TABLE AND LOCATOR ORDER FOR TAGUI
   The modern engine reads this file. Keyword aliases and the locator
   attribute order live here once. Classic Casper mode keeps tebelorg's own
   keyword checks and header text, so a generated script matches that parser.
   tagui_steps_javascript() is the same table as a script. The classic parser
   does not prepend it. */

function tagui_locator_attributes() {
return array('id', 'name', 'class', 'title', 'aria-label', 'text', 'href');}

function tagui_keyword_table() {
// Order matches the historical checks in tagui_parse.php. A shorter prefix
// such as "r " stays after "read " and "run " so those steps win.
return array(
array('url', array('http://', 'https://', 'file://', 'about:blank')),
array('tap', array('tap ', 'click ', 'tap', 'click')),
array('rtap', array('rtap ', 'rclick ', 'rtap', 'rclick')),
array('dtap', array('dtap ', 'dclick ', 'dtap', 'dclick')),
array('hover', array('hover ', 'move ', 'hover', 'move')),
array('type', array('type ', 'enter ', 'type', 'enter')),
array('select', array('select ', 'choose ', 'select', 'choose')),
array('read', array('read ', 'fetch ', 'read', 'fetch')),
array('show', array('show ', 'print ', 'show', 'print')),
array('upload', array('up ', 'upload ', 'up', 'upload')),
array('down', array('down ', 'download ', 'down', 'download')),
array('receive', array('receive ', 'receive')),
array('echo', array('echo ', 'echo')),
array('save', array('save ', 'save')),
array('dump', array('dump ', 'dump')),
array('write', array('write ', 'write')),
array('load', array('load ', 'load')),
array('snap', array('snap ', 'snap')),
array('table', array('table ', 'table')),
array('wait', array('wait ', 'wait')),
array('live', array('live ', 'live')),
array('ask', array('ask ', 'ask')),
array('keyboard', array('keyboard ', 'keyboard')),
array('mouse', array('mouse ', 'mouse')),
array('check', array('check ', 'check')),
array('test', array('test ', 'test')),
array('frame', array('frame ', 'frame')),
array('popup', array('popup ', 'popup')),
array('api', array('api ', 'api')),
array('run', array('run ', 'run')),
array('dom', array('dom ', 'dom')),
array('js', array('js ', 'js')),
array('r', array('r ', 'r')),
array('py', array('py ', 'py')),
array('vision', array('vision ', 'vision')),
array('timeout', array('timeout ', 'timeout')));}

function tagui_keyword_intent($raw_intent) {
$lc = strtolower(trim((string)$raw_intent));
if ($lc === '') return '';
foreach (tagui_keyword_table() as $row) {
$intent = $row[0];
foreach ($row[1] as $prefix) {
$spaced = substr($prefix, -1) === ' ';
if ($spaced) {if (substr($lc, 0, strlen($prefix)) === $prefix) return $intent;}
else if ($lc === $prefix || ($intent === 'url' && substr($lc, 0, strlen($prefix)) === $prefix)) return $intent;}}
return '';}

function tagui_code_prefixes() {
return array(
array('var ', false), array('do ', false), array('{', false), array('}', false),
array('if ', false), array('else', false), array('for ', false), array('while ', false),
array('switch ', false), array('case ', false), array('break;', false), array('break', true),
array('continue;', false), array('continue', true), array('casper.', false), array('this.', false),
array('chrome.', false), array('test.', false), array('//', false), array('function ', false));}

function tagui_is_code($raw_intent) {
$raw = (string)$raw_intent;
if ($raw === '') return false;
foreach (tagui_code_prefixes() as $row) {
$prefix = $row[0]; $exact = $row[1];
if ($exact) {if ($raw === $prefix) return true;}
else if (substr($raw, 0, strlen($prefix)) === $prefix) return true;}
if (substr($raw, -1) === '{' || substr($raw, -1) === '}' || substr($raw, -1) === ';') return true;
if (strpos($raw, '=') !== false) return true;
return false;}

function tagui_is_visual($params) {
$params = trim((string)$params);
if (strlen($params) > 4) {
$ext = strtolower(substr($params, -4));
if ($ext === '.png' || $ext === '.bmp') return true;}
if (strlen($params) > 4 && substr($params, 0, 1) === '(' && substr($params, -1) === ')'
&& (substr_count($params, ',') === 1 || substr_count($params, ',') === 2)
&& !preg_match('/[a-zA-Z]/', $params)) return true;
return false;}

function tagui_strip_line_comment($text) {
return rtrim((string)preg_replace('/\s+\/\/.*$/', '', (string)$text));}

function tagui_repo_identifier($name) {
$name = str_replace(' ', '_', (string)$name);
$name = preg_replace('/[^A-Za-z0-9_]/', '', $name);
if (!is_string($name) || $name === '') return 'invalid_variable_name';
if (is_numeric(substr($name, 0, 1))) $name = '_' . $name;
return $name;}

function tagui_steps_javascript() {
$attrs = json_encode(tagui_locator_attributes());
$keywords = tagui_keyword_table();
$prefixed = array(); $bare = array();
foreach ($keywords as $row) {
foreach ($row[1] as $prefix) {
if (substr($prefix, -1) === ' ') $prefixed[] = array($prefix, $row[0]);
else if ($row[0] !== 'url') $bare[$prefix] = $row[0];}}
$code = tagui_code_prefixes();
$js = "/* generated from tagui_steps.php — edit that file, not this block */\n";
$js .= "var tagui_locator_attributes = " . $attrs . ";\n";
$js .= "function tagui_locator_xpath(locator, attr, loose) {\n";
$js .= "var quoted = String(locator).replace(/\\\\/g, '\\\\\\\\').replace(/\"/g, '\\\\\"');\n";
$js .= "var test = attr === 'text' ? 'text()' : '@' + attr;\n";
$js .= "if (!loose) return '//*[' + test + '=\"' + quoted + '\"]';\n";
$js .= "return '//*[contains(' + test + ',\"' + quoted + '\")]';}\n";
$js .= "function tagui_keyword_intent(raw_intent) {\n";
$js .= "var lc = String(raw_intent).replace(/^\\s+|\\s+\$/g, '').toLowerCase();\n";
$js .= "if (!lc) return '';\n";
$js .= "var prefixed = " . json_encode($prefixed) . ";\n";
$js .= "var i; for (i = 0; i < prefixed.length; i++) {\n";
$js .= "if (lc.substr(0, prefixed[i][0].length) === prefixed[i][0]) return prefixed[i][1];}\n";
$js .= "if (lc.substr(0,7) === 'http://' || lc.substr(0,8) === 'https://' || lc.substr(0,7) === 'file://' || lc.substr(0,11) === 'about:blank') return 'url';\n";
$js .= "var bare = " . json_encode($bare) . ";\n";
$js .= "if (Object.prototype.hasOwnProperty.call(bare, lc)) return bare[lc];\n";
$js .= "return '';}\n";
$js .= "function tagui_is_code(raw_intent) {\n";
$js .= "var raw = String(raw_intent); if (!raw) return false;\n";
$js .= "var prefixes = " . json_encode($code) . ";\n";
$js .= "var j; for (j = 0; j < prefixes.length; j++) {\n";
$js .= "if (prefixes[j][1]) {if (raw === prefixes[j][0]) return true;}\n";
$js .= "else if (raw.substr(0, prefixes[j][0].length) === prefixes[j][0]) return true;}\n";
$js .= "var last = raw.charAt(raw.length - 1);\n";
$js .= "if (last === '{' || last === '}' || last === ';') return true;\n";
$js .= "if (raw.indexOf('=') > -1) return true; return false;}\n\n";
return $js;}

function tagui_page_finder_js() {
$attrs = json_encode(tagui_locator_attributes());
return <<<JS
function taguiElement(locator, root) {
root = root || ((window.__tagui_roots && window.__tagui_roots.length) ? window.__tagui_roots[window.__tagui_roots.length - 1] : document);
function isXPath(value) {return value.charAt(0) === "/" || value.charAt(0) === "(";}
function first(xpath) {
try {var snap = document.evaluate(xpath, root, null, XPathResult.ORDERED_NODE_SNAPSHOT_TYPE, null); if (!snap || snap.snapshotLength < 1) return null; return snap.snapshotItem(0);} catch (e) {return null;}}
function countOf(xpath) {try {var snap = document.evaluate(xpath, root, null, XPathResult.ORDERED_NODE_SNAPSHOT_TYPE, null); return snap ? snap.snapshotLength : 0;} catch (e) {return 0;}}
var attrs = $attrs;
function xpathFor(attr, loose) {
var quoted = String(locator).replace(/\\\\/g, "\\\\\\\\").replace(/"/g, '\\\\"');
var test = attr === "text" ? "text()" : "@" + attr;
if (!loose) return '//*[' + test + '="' + quoted + '"]';
return '//*[contains(' + test + ',"' + quoted + '")]';}
if (isXPath(locator)) return {el: first(locator), count: countOf(locator)};
var el = null; var total = 0;
try {el = root.querySelector(locator); total = root.querySelectorAll(locator).length;} catch (e) {el = null; total = 0;}
if (!el) {
for (var i = 0; i < attrs.length && !el; i++) {
el = first(xpathFor(attrs[i], false));
if (!el) el = first(xpathFor(attrs[i], true));
if (el) total = countOf(el === first(xpathFor(attrs[i], false)) ? xpathFor(attrs[i], false) : xpathFor(attrs[i], true));}}
return {el: el, count: total};}
function taguiDescribe(locator) {
var found = taguiElement(locator);
var el = found.el;
if (!el || !el.getBoundingClientRect) return {found:false, count:0};
try {el.scrollIntoView({block:"center", inline:"center"});} catch (e) {}
var rect = el.getBoundingClientRect();
var style = window.getComputedStyle(el);
var visible = !!style && style.display !== "none" && style.visibility !== "hidden" && style.opacity !== "0" && rect.width > 0 && rect.height > 0;
return {found:true, count: found.count || 1,
x: Math.round(rect.left + rect.width / 2), y: Math.round(rect.top + rect.height / 2),
left: Math.round(rect.left), top: Math.round(rect.top), width: Math.round(rect.width), height: Math.round(rect.height),
text: (el.value || el.innerText || el.textContent || ""), visible: visible};}
JS;}

function tagui_read_csv_rows($path) {
$handle = fopen($path, 'r');
if ($handle === false) throw new RuntimeException('cannot open ' . $path);
$rows = array();
while (($row = fgetcsv($handle)) !== false) {
if (!is_array($row) || (count($row) === 1 && $row[0] === null)) continue;
if (count($row) === 0) throw new RuntimeException('empty row found in ' . $path);
$rows[] = $row;}
fclose($handle);
return $rows;}

function tagui_transpose_datatable($path) {
$rows = tagui_read_csv_rows($path);
$count = count($rows);
if ($count < 2) throw new RuntimeException($path . ' has lesser than 2 columns');
$width = 0;
foreach ($rows as $row) $width = max($width, count($row));
if ($width < 2) throw new RuntimeException($path . ' has lesser than 2 columns');
$transposed = array();
for ($col = 0; $col < $count; $col++) $transposed[0][$col] = (string)$col;
$transposed[0][0] = '[iteration]';
foreach ($rows as $row_index => $columns) {
foreach ($columns as $col_index => $value) {
$transposed[$col_index + 1][$row_index] = $value;}}
$datasets = array();
for ($dataset = 1; $dataset < $count; $dataset++) {
$map = array();
foreach ($transposed as $transposed_row) {
$key = isset($transposed_row[0]) ? (string)$transposed_row[0] : '';
if ($key === '') continue;
$map[$key] = isset($transposed_row[$dataset]) ? (string)$transposed_row[$dataset] : '';}
$datasets[] = $map;}
return $datasets;}

function tagui_object_repository($path) {
if (!is_file($path)) return array();
$rows = tagui_read_csv_rows($path);
$map = array();
foreach ($rows as $index => $row) {
$key = isset($row[0]) ? (string)$row[0] : '';
if ($key === '') continue;
if ($index === 0 && strtoupper($key) === 'OBJECT') continue;
$map[$key] = isset($row[1]) ? (string)$row[1] : '';}
return $map;}

function tagui_datatable_rows($flow, $csv_arg) {
$datasets = array();
if (is_string($csv_arg) && $csv_arg !== '') {
if (!is_file($csv_arg)) throw new RuntimeException('cannot find ' . $csv_arg);
$datasets = tagui_transpose_datatable($csv_arg);}
else {
$sidecar = $flow . '.csv';
if (is_file($sidecar)) {
$rows = tagui_read_csv_rows($sidecar);
if (count($rows) === 0 || count($rows[0]) < 2) throw new RuntimeException($sidecar . ' has lesser than 2 columns');
$sets = count($rows[0]) - 1;
for ($dataset = 1; $dataset <= $sets; $dataset++) {
$map = array();
foreach ($rows as $row) {
$key = isset($row[0]) ? (string)$row[0] : '';
if ($key === '') continue;
$map[$key] = isset($row[$dataset]) ? (string)$row[$dataset] : '';}
$datasets[] = $map;}}}
if (count($datasets) === 0) $datasets[] = array();
$flow_dir = dirname($flow);
$extra = array();
$local = $flow_dir . DIRECTORY_SEPARATOR . 'tagui_local.csv';
$global = getcwd() . DIRECTORY_SEPARATOR . 'tagui_global.csv';
foreach (array($local, $global) as $repo) {
foreach (tagui_object_repository($repo) as $key => $value) $extra[$key] = $value;}
if (count($extra) > 0) {
foreach ($datasets as $index => $map) $datasets[$index] = array_merge($extra, $map);}
return $datasets;}

function tagui_apply_repository($line, $repo) {
if (!is_array($repo) || count($repo) === 0) return $line;
for ($pass = 0; $pass < 2; $pass++) {
foreach ($repo as $key => $value) {
$line = str_replace('`' . $key . '`', (string)$value, $line);}}
return $line;}

trait TaguiModernSteps {
function select_step($line, $index) {
$pair = $this->split_keyword($line, ' as ');
if ($pair === null) {$this->fail($index, 'target/option missing for ' . $line); return;}
if (tagui_is_visual($pair[0]) || tagui_is_visual($pair[1])) {$this->visual_gap($index, $line, false); return;}
if ($this->need($pair[0], $index) === null) return;
$locator = json_encode($pair[0], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
$option = json_encode($pair[1], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
$script = "(function(){\n" . tagui_page_finder_js() . "\nvar el = taguiElement(" . $locator . ").el; if (!el) return false;"
. "var want = " . $option . "; if (want === '[clear]') want = ''; var found = false; var nodes = el.options || el.children;"
. "for (var i = 0; i < nodes.length; i++) {var opt = nodes[i]; if (!found && (opt.value === want || opt.text === want)) {el.selectedIndex = i; found = true;}}"
. "var evt = document.createEvent('UIEvents'); evt.initUIEvent('change', true, true); el.dispatchEvent(evt); return found;})()";
try {if ($this->chrome->evaluate($script) !== true) $this->fail($index, 'cannot find option ' . $pair[1]);}
catch (Exception $e) {$this->fail($index, $e->getMessage());}}

function upload_step($line, $index) {
$pair = $this->split_keyword($line, ' as ');
if ($pair === null) {$this->fail($index, 'filename missing for ' . $line); return;}
$file = $this->abs_file($pair[1]);
if (!is_file($file)) {$this->fail($index, 'cannot find file ' . $pair[1]); return;}
if ($this->need($pair[0], $index) === null) return;
try {if (!$this->chrome->set_file($pair[0], $file)) $this->fail($index, 'unable to upload ' . $pair[0] . ' as ' . $pair[1]);}
catch (Exception $e) {$this->fail($index, $e->getMessage());}}

function download_step($line, $index) {
$params = tagui_strip_line_comment(trim(substr($line, strpos($line, ' ') + 1)));
if ($params === '') {$this->fail($index, 'location missing for ' . $line); return;}
if (preg_match('/^to\s+(.+)$/i', $params, $match)) {$this->download_directory(trim($match[1]), $index); return;}
$split = stripos($params, ' to ');
if ($split === false) {
if (preg_match('#^[a-z][a-z0-9+.-]*://#i', $params)) {$this->fail($index, 'url/filename missing for ' . $line); return;}
$this->download_directory($params, $index); return;}
$url = trim(substr($params, 0, $split));
$file = trim(substr($params, $split + 4));
if ($url === '' || $file === '') {$this->fail($index, 'url/filename missing for ' . $line); return;}
$url = $this->absolute_url($url);
$file = $this->abs_file($file);
try {$bytes = $this->http_get($url);
if ($bytes === null) {$this->fail($index, 'cannot download ' . $url); return;}
if (file_put_contents($file, $bytes) === false) {$this->fail($index, 'cannot save ' . $file); return;}
if ($this->chrome !== null && method_exists($this->chrome, 'note_network')) $this->chrome->note_network($url);}
catch (Exception $e) {$this->fail($index, $e->getMessage());}}

function download_directory($folder, $index) {
$folder = $this->abs_file(trim($folder));
if ($folder === '') {$this->fail($index, 'location missing for download'); return;}
if (!is_dir($folder) && !@mkdir($folder, 0777, true) && !is_dir($folder)) {
$this->fail($index, 'cannot create download folder ' . $folder); return;}
$this->download_dir = $folder;
if ($this->chrome !== null && method_exists($this->chrome, 'set_download_path')) {
try {$this->chrome->set_download_path($folder);}
catch (Exception $e) {$this->fail($index, $e->getMessage());}}}

function receive_step($line, $index) {
$pair = $this->split_keyword($line, ' to ');
if ($pair === null) {$this->fail($index, 'keyword/filename missing for ' . $line); return;}
if (!$this->require_chrome($index)) return;
$url = $this->chrome->await_network_url($pair[0], $this->timeout);
if ($url === '') {$this->fail($index, 'cannot find network response ' . $pair[0]); return;}
$file = $this->abs_file($pair[1]);
$bytes = $this->http_get($url);
if ($bytes === null) {$this->fail($index, 'cannot download ' . $url); return;}
if (file_put_contents($file, $bytes) === false) $this->fail($index, 'cannot save ' . $file);}

function save_step($line, $index) {$this->transfer_text($line, $index);}

function write_step($line, $index) {
$params = trim(substr($line, strpos($line, ' ') + 1));
if ($params === '') {$this->fail($index, 'target missing for ' . $line); return;}
$split = stripos($params, ' to ');
$expr = $split === false ? $params : trim(substr($params, 0, $split));
$file = $split === false ? '' : trim(substr($params, $split + 4));
if ($expr === '') {$this->fail($index, 'target missing for ' . $line); return;}
try {$text = $this->stringish($this->expr_value($this->add_concat($expr)));}
catch (TaguiExprError $e) {$this->fail($index, $e->getMessage()); return;}
$path = $file === '' ? $this->default_text_file(true) : $this->abs_file($file);
if (file_put_contents($path, $text . "\r\n", FILE_APPEND) === false) $this->fail($index, 'cannot save ' . $path);}

function transfer_text($line, $index) {
$params = trim(substr($line, strpos($line, ' ') + 1));
$split = stripos($params, ' to ');
$target = $split === false ? $params : trim(substr($params, 0, $split));
$file = $split === false ? '' : trim(substr($params, $split + 4));
if ($params === '') {$this->fail($index, 'target missing for ' . $line); return;}
if (tagui_is_visual($target)) {$this->visual_gap($index, $line, false); return;}
$text = '';
if (strcasecmp($target, 'page') === 0) {
if (!$this->require_chrome($index)) return;
$text = $this->chrome->page_html();}
else {
$info = $this->need($target, $index); if ($info === null) return;
$text = trim((string)$info['text']);}
$path = $file === '' ? $this->default_text_file(false) : $this->abs_file($file);
if (file_put_contents($path, $text) === false) $this->fail($index, 'cannot save ' . $path);}

function load_step($line, $index) {
$pair = $this->split_keyword($line, ' to ');
if ($pair === null || !preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $pair[1])) {
$this->fail($index, 'variable missing for ' . $line); return;}
$file = $this->abs_file($pair[0]);
if (!is_file($file)) {$this->fail($index, 'cannot find file ' . $pair[0]); return;}
$this->vars[$pair[1]] = trim((string)file_get_contents($file));}

function table_step($line, $index) {
$params = trim(substr($line, strpos($line, ' ') + 1));
if ($params === '') {$this->fail($index, 'target missing for ' . $line); return;}
$split = stripos($params, ' to ');
$target = $split === false ? $params : trim(substr($params, 0, $split));
$file = $split === false ? '' : trim(substr($params, $split + 4));
if (preg_match('/^[0-9]+$/', $target)) $target = '(//table)[' . (int)$target . ']';
if ($this->need($target, $index) === null) return;
$locator = json_encode($target, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
$script = "(function(){\n" . tagui_page_finder_js() . "\nvar el = taguiElement(" . $locator . ").el; if (!el) return null;"
. "var rows = []; var trs = el.querySelectorAll('tr'); for (var r = 0; r < trs.length; r++) {"
. "var cells = trs[r].querySelectorAll('th,td'); var row = []; for (var c = 0; c < cells.length; c++) row.push((cells[c].innerText || '').trim());"
. "if (row.length) rows.push(row);} return rows;})()";
try {$rows = $this->chrome->evaluate($script);}
catch (Exception $e) {$this->fail($index, $e->getMessage()); return;}
if (!is_array($rows)) {$this->fail($index, 'cannot find ' . $target); return;}
$path = $file === '' ? $this->flow_path . DIRECTORY_SEPARATOR . 'table' . (++$this->snap_count) . '.csv' : $this->abs_file($file);
$handle = fopen($path, 'w');
if ($handle === false) {$this->fail($index, 'cannot save ' . $path); return;}
foreach ($rows as $row) fputcsv($handle, $row);
fclose($handle);}

function ask_step($line, $index) {
$prompt = trim(substr($line, strpos($line, ' ') + 1));
if ($prompt === '') {$this->fail($index, 'prompt missing for ' . $line); return;}
$visible = getenv('tagui_web_browser') === 'chrome';
if ($visible && $this->chrome !== null) {
$encoded = json_encode($prompt, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
try {$value = $this->chrome->evaluate('prompt(' . $encoded . ')');}
catch (Exception $e) {$this->fail($index, $e->getMessage()); return;}
$this->vars['ask_result'] = $value === null ? '' : (string)$value;
return;}
$this->emit($prompt . ' ');
$answer = fgets(STDIN);
$this->vars['ask_result'] = $answer === false ? '' : rtrim($answer, "\r\n");}

function api_step($line, $index) {
$url = trim(substr($line, strpos($line, ' ') + 1));
if ($url === '' || strcasecmp($url, 'api') === 0) {$this->fail($index, 'API URL missing for ' . $line); return;}
$config = array('method' => 'GET', 'header' => array(), 'body' => array());
if (isset($this->vars['api_config'])) {
$raw = $this->vars['api_config'];
if (is_string($raw)) $raw = json_decode($raw, true);
if (is_array($raw)) $config = array_merge($config, $raw);}
$method = strtoupper((string)$config['method']);
$header = '';
if (isset($config['header']) && is_array($config['header'])) {
foreach ($config['header'] as $item) {if ($item !== '') $header .= $item . "\r\n";}}
$content = '';
if ($method !== 'GET' && $method !== 'HEAD') $content = json_encode($config['body']);
$context = stream_context_create(array('http' => array(
'method' => $method, 'header' => $header, 'content' => $content, 'timeout' => max(1, (int)$this->timeout), 'ignore_errors' => true)));
$body = @file_get_contents($url, false, $context);
$this->vars['api_result'] = $body === false ? '' : $body;
$decoded = json_decode((string)$this->vars['api_result'], true);
$this->vars['api_json'] = $decoded;}

function run_step($line, $index) {
$lower = strtolower(trim($line));
if ($lower === 'run begin') {$this->inside_run = true; $this->run_buffer = ''; return;}
if ($lower === 'run finish') {
$this->inside_run = false; $command = trim($this->run_buffer); $this->run_buffer = '';
if ($command === '') {$this->fail($index, 'command to run missing for run'); return;}
$this->run_command($command, $index); return;}
if (!empty($this->inside_run)) {$this->run_buffer .= ($this->run_buffer === '' ? '' : "\n") . $line; return;}
$command = trim(substr($line, strpos($line, ' ') + 1));
if ($command === '' || strcasecmp($command, 'run') === 0) {$this->fail($index, 'command to run missing for ' . $line); return;}
$this->run_command($command, $index);}

function run_command($command, $index) {
$shell = DIRECTORY_SEPARATOR === '\\' ? array('cmd', '/c', $command) : array('sh', '-c', $command);
$descriptor = array(0 => array('pipe', 'r'), 1 => array('pipe', 'w'), 2 => array('pipe', 'w'));
$process = proc_open($shell, $descriptor, $pipes);
if (!is_resource($process)) {$this->fail($index, 'cannot run ' . $command); return;}
fclose($pipes[0]);
stream_set_blocking($pipes[1], false); stream_set_blocking($pipes[2], false);
$stdout = ''; $stderr = '';
$deadline = microtime(true) + max($this->timeout, 1);
$timed_out = false;
while (true) {
$stdout .= (string)stream_get_contents($pipes[1]);
$stderr .= (string)stream_get_contents($pipes[2]);
$status = proc_get_status($process);
if (!$status['running']) break;
if (microtime(true) > $deadline) {$timed_out = true; proc_terminate($process); break;}
usleep(50000);}
$stdout .= (string)stream_get_contents($pipes[1]);
$stderr .= (string)stream_get_contents($pipes[2]);
fclose($pipes[1]); fclose($pipes[2]); proc_close($process);
if ($timed_out) {$this->fail($index, 'command to run exceeded ' . $this->timeout . 's timeout'); return;}
$result = trim($stdout) !== '' ? trim($stdout) : trim($stderr);
$result = str_replace("\r\n", "\n", $result);
$this->vars['run_result'] = $result;
$this->vars['run_json'] = array('stdout' => $stdout, 'stderr' => $stderr);}

function check_step($line, $index) {
$params = trim(substr($line, strpos($line, ' ') + 1));
$params = str_replace('||', ' JAVASCRIPT_OR ', $params);
$parts = explode('|', $params);
if (count($parts) !== 3) {$this->fail($index, 'if/true/false missing for ' . $line); return;}
$condition = str_replace(' JAVASCRIPT_OR ', '||', trim($parts[0]));
$when_true = str_replace(' JAVASCRIPT_OR ', '||', trim($parts[1]));
$when_false = str_replace(' JAVASCRIPT_OR ', '||', trim($parts[2]));
$take = $this->truthy_expr($this->rewrite_condition($condition), $index);
try {$text = $this->stringish($this->expr_value($this->add_concat($take ? $when_true : $when_false)));}
catch (TaguiExprError $e) {$this->fail($index, $e->getMessage()); return;}
$this->emit($text);}

function visual_command($line) {
$intent = tagui_keyword_intent($line);
if ($intent === 'type') {
$space = strpos($line, ' ');
$params = $space === false ? '' : substr($line, $space + 1);
$split = stripos($params, ' as ');
if ($split !== false) {
$target = trim(substr($params, 0, $split));
$rest = substr($params, $split);
$prefix = $space === false ? $line : rtrim(substr($line, 0, $space));
return $prefix . ' ' . $this->abs_visual_token($target) . $rest;}}
$self = $this;
$rewritten = preg_replace_callback('/(?<![A-Za-z0-9_])([\'"]?)([^\'"\s]+?\.(?:png|bmp))\1/i', function ($match) use ($self) {
return $match[1] . $self->abs_visual_token($match[2]) . $match[1];}, $line);
return is_string($rewritten) ? $rewritten : $line;}

function abs_visual_token($token) {
$token = (string)$token;
if ($token === '' || $token[0] === '(') return $token;
$bare = trim($token, "\"'");
if ($bare === '' || !tagui_is_visual($bare) || $bare[0] === '(') return $token;
$abs = $this->abs_file($bare);
if ($bare === $token) return $abs;
return $token[0] . $abs . substr($token, -1);}

function visual_gap($index, $line, $trace = true) {
if ($trace) $this->trace($line);
$command = $this->visual_command($line);
try {$ok = TaguiSikuli::run($command, max(30.0, (float)$this->timeout));}
catch (Exception $e) {$this->fail($index, $e->getMessage()); return;}
$text = TaguiSikuli::text();
if (!$ok) {
$this->fail($index, 'visual step failed - ' . $this->redacted_trace($line) . '. ' . TaguiSikuli::failure_detail(TaguiSikuli::last_result()));
return;}
$intent = tagui_keyword_intent($line);
if ($intent === 'read') {
$params = trim(substr($line, strpos($line, ' ') + 1));
$split = stripos($params, ' to ');
if ($split === false) return;
$name = trim(substr($params, $split + 4));
if (preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $name)) $this->vars[$name] = $text;}
else if ($intent === 'show') $this->emit($text);
else if ($intent === 'save') {
$params = trim(substr($line, strpos($line, ' ') + 1));
$split = stripos($params, ' to ');
if ($split === false) return;
$file = $this->abs_file(trim(substr($params, $split + 4)));
if ($file !== '') file_put_contents($file, $text);}}

function integration_gap($intent, $index) {
$this->fail($index, $intent . ' stays on the CasperJS engine');}

function execute_frame($lines, $i, $end, $active) {
try {$line = $this->expand_backticks(trim($lines[$i]));}
catch (TaguiExprError $e) {if ($active) $this->fail($i, $e->getMessage()); return $i + 1;}
$params = trim(substr($line, strpos($line, ' ') + 1));
if ($params === '' || strcasecmp($params, 'frame') === 0) {$this->fail($i, 'name missing for ' . $line); return $i + 1;}
if ($active && !$this->require_chrome($i)) return $i + 1;
if ($this->live) {$this->fail($i, 'frame is not supported in live mode. It opens a block around the following steps; put it in a flow file.'); return $i + 1;}
$base = $this->indent_of($lines[$i]);
if ($this->next_code($lines, $i + 1, $end) === null) return $end + 1;
if (!$active) return $this->execute_body($lines, $i + 1, $end, false, $base);
$names = array_map('trim', explode('|', $params));
$pushed = 0;
foreach ($names as $name) {
if ($name === '') continue;
try {if (!$this->chrome->push_frame($name)) {$this->fail($i, 'cannot find frame ' . $name); break;}}
catch (Exception $e) {$this->fail($i, $e->getMessage()); break;}
$pushed++;}
$after = $this->execute_body($lines, $i + 1, $end, $pushed === count(array_filter($names, function ($name) {return $name !== '';})), $base);
while ($pushed > 0) {try {$this->chrome->pop_frame();} catch (Exception $e) {} $pushed--;}
return $after;}

function execute_popup($lines, $i, $end, $active) {
try {$line = $this->expand_backticks(trim($lines[$i]));}
catch (TaguiExprError $e) {if ($active) $this->fail($i, $e->getMessage()); return $i + 1;}
$params = trim(substr($line, strpos($line, ' ') + 1));
if ($params === '' || strcasecmp($params, 'popup') === 0) {$this->fail($i, 'keyword missing for ' . $line); return $i + 1;}
if ($active && !$this->require_chrome($i)) return $i + 1;
if ($this->live) {$this->fail($i, 'popup is not supported in live mode. It opens a block around the following steps; put it in a flow file.'); return $i + 1;}
$base = $this->indent_of($lines[$i]);
if ($this->next_code($lines, $i + 1, $end) === null) return $end + 1;
if (!$active) return $this->execute_body($lines, $i + 1, $end, false, $base);
$opened = false;
try {$opened = $this->chrome->push_popup($params, $this->timeout);}
catch (Exception $e) {$this->fail($i, $e->getMessage());}
if (!$opened && !$this->stopped) $this->fail($i, 'cannot find popup ' . $params);
$after = $this->execute_body($lines, $i + 1, $end, $opened, $base);
if ($opened) {try {$this->chrome->pop_popup();} catch (Exception $e) {}}
return $after;}

function execute_while($lines, $i, $end, $active) {
try {$line = $this->expand_backticks(trim($lines[$i]));}
catch (TaguiExprError $e) {if ($active) $this->fail($i, $e->getMessage()); return $i + 1;}
if ($this->live) {$this->fail($i, 'while is not supported in live mode. Put it in a flow file.'); return $i + 1;}
$base = $this->indent_of($lines[$i]);
if ($this->next_code($lines, $i + 1, $end) === null) return $end + 1;
$after = $this->execute_body($lines, $i + 1, $end, false, $base);
if (!$active) return $after;
$expr = $this->condition_expression(trim(substr(trim($line), 5)));
$guard = 0;
while ($guard < 10000) {
if ($this->stopped) break;
if (!$this->truthy_expr($expr, $i)) break;
$guard++;
$after = $this->execute_body($lines, $i + 1, $end, true, $base);
if ($this->jump === 'break') {$this->jump = null; break;}
if ($this->jump === 'continue') {$this->jump = null; continue;}}
if ($guard >= 10000) $this->fail($i, 'while loop exceeded 10000 iterations');
return $after;}

function split_keyword($line, $keyword) {
$params = trim(substr($line, strpos($line, ' ') + 1));
$split = stripos($params, $keyword);
if ($split === false) return null;
$left = trim(substr($params, 0, $split));
$right = trim(substr($params, $split + strlen($keyword)));
if ($left === '' || $right === '') return null;
return array($left, $right);}

function absolute_url($url) {
if (preg_match('#^[a-z][a-z0-9+.-]*://#i', $url)) return $url;
if ($this->chrome === null) return $url;
$base = $this->chrome->url();
if ($base === '' || $base === 'about:blank') return $url;
$parts = parse_url($base);
if (!is_array($parts) || empty($parts['scheme']) || empty($parts['host'])) return $url;
$prefix = $parts['scheme'] . '://' . $parts['host'] . (isset($parts['port']) ? ':' . $parts['port'] : '');
if (substr($url, 0, 1) === '/') return $prefix . $url;
$path = isset($parts['path']) ? $parts['path'] : '/';
$dir = substr($path, 0, strrpos($path, '/') + 1);
return $prefix . $dir . $url;}

function http_get($url) {
$context = stream_context_create(array('http' => array('timeout' => max(5, (int)$this->timeout), 'follow_location' => 1)));
$bytes = @file_get_contents($url, false, $context);
return $bytes === false ? null : $bytes;}}
