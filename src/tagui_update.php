<?php
 
// return TagUI version of given runner file
function tagui_version($tagui_runner_file) {
$tagui_runner = file_get_contents($tagui_runner_file);
if ($tagui_runner == false) die("\nERROR - internet connection to access GitHub is required to update TagUI\n");
$left_anchor = 'echo "tagui v'; $right_anchor = ': ';
$version_start_pos = strpos($tagui_runner, $left_anchor) + strlen($left_anchor);
$tagui_version = substr($tagui_runner, $version_start_pos);
$version_end_pos = strpos($tagui_version, $right_anchor);
$tagui_version = substr($tagui_version, 0, $version_end_pos); return $tagui_version;}

// retrieve TagUI versions for local and master
$tagui_master_version = tagui_version('https://raw.githubusercontent.com/kelaberetiv/TagUI/master/src/tagui');
$tagui_local_version = tagui_version(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'tagui');

// download and update if there is a new version
if (floatval($tagui_local_version) >= floatval($tagui_master_version))
echo "You are already using the latest version of TagUI (v" . $tagui_local_version . ")\n";
else {echo "Downloading and updating to latest version of TagUI (v" . $tagui_master_version . ")\n";
$zip_file = dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . "TagUI-master.zip";
if (file_put_contents($zip_file,fopen("https://github.com/kelaberetiv/TagUI/archive/refs/heads/master.zip",'r')) == false)
echo "ERROR - cannot download new TagUI version from below GitHub URL\n" .
"https://github.com/kelaberetiv/TagUI/archive/refs/heads/master.zip\n";}

?>
