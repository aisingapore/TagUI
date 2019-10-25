<?php

/* TRANSPOSE DATATABLE SCRIPT FOR TAGUI FRAMEWORK ~ TEBEL.ORG */

// load input csv file after doing some due diligence
$source_csv = $argv[1]; if ($source_csv=="") die("ERROR - specify csv filename as first parameter\n");
if (strpos($source_csv,"_transpose.csv")==false) die("ERROR - csv filename must end with '_transpose.csv'\n");
$source_file = fopen($source_csv,'r') or die("ERROR - cannot open " . $source_csv . "\n"); $csv_count = 0;
while (!feof($source_file)) {
  $csv_line = fgetcsv($source_file);
  if (is_array($csv_line) && $csv_line[0] !== null) {
    $csv_data[] = $csv_line;
  }
} fclose($source_file); $csv_count = @count($csv_data);

// do some prep, transpose datatable and output csv file
$csv_result = array(); $target_csv = str_replace("_transpose.csv",".csv",$source_csv);
$target_file = fopen($target_csv,'w') or die("ERROR - cannot open " . $target_csv . " to save\n");
// populate the dummy headers
for($i=0; $i<$csv_count; $i++) {
  $csv_result[0][$i] = $i;
}
$csv_result[0][0] = "[iteration]";
foreach ($csv_data as $row => $columns) {
  if (is_array($columns) || is_object($columns)) {
    foreach ($columns as $row2 => $column2) {
      $csv_result[$row2+1][$row] = $column2;
    }
  }
}
foreach ($csv_result as $csv_line) {fputcsv($target_file,$csv_line);} fclose($target_file);

?>
