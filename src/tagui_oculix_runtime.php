<?php

/* Opt-in OculiX / Java 17 launch check. SikuliX stays the default jar.
   src/tagui calls this only when TAGUI_SIKULI_JAR or TAGUI_JAVA is set.
   `php tagui_oculix_runtime.php --sikuli-launch` prints the java binary and the jar. */

class TaguiOculix {

static function jar() {
// sikulix/sikulix.jar is the SikuliX default (SikuliX 2's sikulixapi jar).
// OculiX is opt-in: set TAGUI_SIKULI_JAR to oculixide.jar.
$env = getenv('TAGUI_SIKULI_JAR');
if (is_string($env) && trim($env) !== '') return trim($env);
return 'sikulix/sikulix.jar';}

static function java_binary() {
$chosen = getenv('TAGUI_JAVA');
if (is_string($chosen) && trim($chosen) !== '') return trim($chosen);
$home = getenv('JAVA_HOME');
if (is_string($home) && trim($home) !== '') {
$name = DIRECTORY_SEPARATOR === '\\' ? 'java.exe' : 'java';
$candidate = rtrim($home, "/\\") . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . $name;
if (is_file($candidate)) return $candidate;}
return 'java';}

static function java_major($binary) {
$output = shell_exec(escapeshellarg($binary) . ' -version 2>&1');
if (!is_string($output)) return 0;
if (preg_match('/version "1\.(\d+)/', $output, $match) === 1) return (int)$match[1];
if (preg_match('/version "(\d+)/', $output, $match) === 1) return (int)$match[1];
return 0;}

static function launch_pair() {
$jar = self::jar();
$java = self::java_binary();
if (stripos($jar, 'oculix') !== false && self::java_major($java) < 17) {
throw new RuntimeException('OculiX requires Java 17+; set PATH/JAVA_HOME or TAGUI_JAVA');}
return array($java, $jar);}}

if (PHP_SAPI === 'cli' && isset($argv[1]) && $argv[1] === '--sikuli-launch') {
try {
$pair = TaguiOculix::launch_pair();
echo $pair[0], "\n", $pair[1], "\n";
exit(0);}
catch (Exception $e) {
echo $e->getMessage(), "\n";
exit(1);}}
