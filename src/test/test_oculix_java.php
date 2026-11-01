<?php

/* OculiX is opt-in. The default jar stays sikulix/sikulix.jar (SikuliX 2 sikulixapi). */

require_once dirname(__DIR__) . '/tagui_oculix_runtime.php';

$failures = 0;

function expect($label, $actual, $expected) {
global $failures;
if ($actual !== $expected) {
$failures++;
fwrite(STDERR, "FAIL $label\n  actual:   " . var_export($actual, true) . "\n  expected: " . var_export($expected, true) . "\n");
} else {
fwrite(STDOUT, "ok   $label\n");}}

function saved($name) {
$value = getenv($name);
return $value === false ? null : $value;}

function restore($name, $value) {
if ($value === null) putenv($name);
else putenv($name . '=' . $value);}

$saved = array(
'TAGUI_JAVA' => saved('TAGUI_JAVA'),
'TAGUI_SIKULI_JAR' => saved('TAGUI_SIKULI_JAR'),
'JAVA_HOME' => saved('JAVA_HOME'));

putenv('TAGUI_JAVA');
putenv('TAGUI_SIKULI_JAR');
putenv('JAVA_HOME');
expect('default jar', TaguiOculix::jar(), 'sikulix/sikulix.jar');
expect('default java', TaguiOculix::java_binary(), 'java');

$work = sys_get_temp_dir() . '/tagui_java_' . getmypid();
mkdir($work);
$home = $work . '/java17';
mkdir($home . '/bin', 0777, true);
$java17 = $home . '/bin/java';
file_put_contents($java17, "#!/bin/sh\necho 'openjdk version \"17.0.12\"' >&2\n");
chmod($java17, 0755);
$java8 = $work . '/java8';
file_put_contents($java8, "#!/bin/sh\necho 'java version \"1.8.0_432\"' >&2\n");
chmod($java8, 0755);
$oculix = $work . '/oculixide.jar';
file_put_contents($oculix, 'jar');
$sikuli = $work . '/sikulixapi-2.0.5.jar';
file_put_contents($sikuli, 'jar');

putenv('JAVA_HOME=' . $home);
expect('java home', TaguiOculix::java_binary(), $java17);
putenv('TAGUI_JAVA=' . $java8);
expect('tagui java wins', TaguiOculix::java_binary(), $java8);
putenv('TAGUI_SIKULI_JAR=' . $sikuli);
expect('explicit sikuli jar', TaguiOculix::jar(), $sikuli);
$pair = TaguiOculix::launch_pair();
expect('sikuli jar keeps java 8', $pair[0], $java8);
expect('sikuli jar path', $pair[1], $sikuli);

putenv('TAGUI_SIKULI_JAR=' . $oculix);
$threw = false;
try {TaguiOculix::launch_pair();}
catch (Exception $e) {
$threw = true;
expect('java 17 message', $e->getMessage(), 'OculiX requires Java 17+; set PATH/JAVA_HOME or TAGUI_JAVA');}
expect('java 8 rejected', $threw, true);

putenv('TAGUI_JAVA=' . $java17);
$pair = TaguiOculix::launch_pair();
expect('oculix java', $pair[0], $java17);
expect('oculix jar', $pair[1], $oculix);

$launch = shell_exec('php -q ' . escapeshellarg(dirname(__DIR__) . '/tagui_oculix_runtime.php') . ' --sikuli-launch 2>/dev/null');
expect('cli launch', $launch, $java17 . "\n" . $oculix . "\n");

putenv('TAGUI_JAVA=' . $java8);
$cli = shell_exec('php -q ' . escapeshellarg(dirname(__DIR__) . '/tagui_oculix_runtime.php') . ' --sikuli-launch 2>/dev/null');
expect('cli rejects java 8', trim((string)$cli), 'OculiX requires Java 17+; set PATH/JAVA_HOME or TAGUI_JAVA');

$shell = file_get_contents(dirname(__DIR__) . '/tagui');
expect('shell keeps default launch', strpos($shell, "\njava -jar sikulix/sikulix.jar -r tagui.sikuli -d 3 >> tagui.sikuli/tagui.log 2>&1 &\n") !== false, true);
expect('shell uses chosen jar', strpos($shell, '"$tagui_java_bin" -jar "$tagui_sikuli_jar" -r tagui.sikuli') !== false, true);

foreach ($saved as $name => $value) restore($name, $value);

if ($failures > 0) {
fwrite(STDERR, $failures . " failed\n");
exit(1);}
fwrite(STDOUT, "all oculix checks passed\n");
