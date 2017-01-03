<?php

/* ETL BASE SCRIPT FOR TA.ETL TO EXTRACT TRANSFORM LOAD DATA ~ TEBEL.SG */

require_once('PHPExcel/PHPExcel.php'); // for managing xls/xlsx/html - https://github.com/PHPOffice/PHPExcel

// check for first parameter - data extract file in csv, xls, xlsx, html
if ($argv[1]=="") die("ERROR - data extract file missing for first parameter\n"); $data_extract_file = $argv[1];
if (!file_exists($data_extract_file)) die("ERROR - cannot find " . $data_extract_file . "\n");
$ext_len = strlen(pathinfo($data_extract_file, PATHINFO_EXTENSION)); // use in generating csv filename
convert_datafile($data_extract_file,substr($data_extract_file,0,strlen($data_extract_file)-$ext_len).'csv');
$data_extract_file = substr($data_extract_file,0,strlen($data_extract_file)-$ext_len).'csv';
$data_extract = fopen($data_extract_file,'r') or die("ERROR - cannot open " . $data_extract_file . "\n");

// check for second parameter - data transform file in csv, xls, xlsx, html
if ($argv[2]=="") die("ERROR - data transform file missing for second parameter\n"); $data_transform_file = $argv[2];
if (!file_exists($data_transform_file)) die("ERROR - cannot find " . $data_transform_file . "\n");
$ext_len = strlen(pathinfo($data_transform_file, PATHINFO_EXTENSION)); // use in generating csv filename
convert_datafile($data_transform_file,substr($data_transform_file,0,strlen($data_transform_file)-$ext_len).'csv');
$data_transform_file = substr($data_transform_file,0,strlen($data_transform_file)-$ext_len).'csv';
if (count(file($data_transform_file)) != 2) die("ERROR - data transform file does not have exactly 2 rows\n");
$data_transform = fopen($data_transform_file,'r') or die("ERROR - cannot open " . $data_transform_file . "\n");

// check for third parameter - data load file in csv, xls, xlsx, html
if ($argv[3]=="") die("ERROR - data load file missing for third parameter\n"); $data_load_file = $argv[3];
$ext_len = strlen(pathinfo($data_load_file, PATHINFO_EXTENSION)); // use in generating csv filename
$data_load = fopen(substr($data_load_file,0,strlen($data_load_file)-$ext_len).'csv','w') 
or die("ERROR - cannot open " . substr($data_load_file,0,strlen($data_load_file)-$ext_len).'csv' . "\n");

fputcsv($data_load,fgetcsv($data_transform)); // write header to output
$data_transform_mapping = fgetcsv($data_transform); // get transform mapping
fgetcsv($data_extract); // discard header from input to prepare for iteration

while (!feof($data_extract)) { // loop through input, transform data, write to output
$data_extract_row = fgetcsv($data_extract); if ($data_extract_row != "")
fputcsv($data_load,etl_transform($data_extract_row,$data_transform_mapping));}

fclose($data_extract); fclose($data_transform); fclose($data_load); // close all open files
convert_datafile(substr($data_load_file,0,strlen($data_load_file)-$ext_len).'csv',$data_load_file);

function etl_transform($row_extract, $row_mapping) { // perform the actual transformation here
$row_load = $row_mapping; $num_col = count($row_mapping); for ($curr_col=0; $curr_col<$num_col; $curr_col++){
$row_load[$curr_col] = $row_extract[column_to_index($row_mapping[$curr_col])];
$row_load[$curr_col] = apply_format_keyword($row_load[$curr_col],$row_mapping[$curr_col]);} return $row_load;}

function column_to_index($column_value) { // convert column letter into array index
$parsed_value = strip_format_keyword($column_value); $column_range = range('A', 'Z');
return array_search($parsed_value, $column_range);}

function strip_format_keyword($mapping_definition) { // remove keyword and return column letter
$column_letter = str_replace(")","",str_replace(" ","",strtoupper($mapping_definition)));
$column_letter = str_replace("UPPER(","",$column_letter);
$column_letter = str_replace("LOWER(","",$column_letter);
$column_letter = str_replace("TITLE(","",$column_letter);
$column_letter = str_replace("SENTENCE(","",$column_letter);
if ((strlen($column_letter)==1) and ctype_alpha($column_letter)) return $column_letter;
else die("ERROR - mapping definition invalid - " . $mapping_definition . "\n");}

function apply_format_keyword($cell_value,$mapping_definition) { // apply desired formatting
$mapping_definition = str_replace(")","",str_replace(" ","",strtoupper($mapping_definition)));
if (strpos($mapping_definition, "UPPER(") !== false) return strtoupper($cell_value);
else if (strpos($mapping_definition, "LOWER(") !== false) return strtolower($cell_value);
else if (strpos($mapping_definition, "TITLE(") !== false) return ucwords($cell_value);
else if (strpos($mapping_definition, "SENTENCE(") !== false) return ucfirst($cell_value); else return $cell_value;}

function convert_datafile($infile,$outfile) {
if ((strtolower(pathinfo($infile, PATHINFO_EXTENSION))!='csv') and 
(strtolower(pathinfo($infile, PATHINFO_EXTENSION))!='xls') and
(strtolower(pathinfo($infile, PATHINFO_EXTENSION))!='xlsx') and
(strtolower(pathinfo($infile, PATHINFO_EXTENSION))!='html')) 
die("ERROR - unsupported input format " . $infile . "\n");
$infileType = PHPExcel_IOFactory::identify($infile); $objReader = PHPExcel_IOFactory::createReader($infileType);
$objReader->setReadDataOnly(true); $objPHPExcel = $objReader->load($infile); // load file base on detected type   
// if input file is Excel remove first row, which is an empty row with sheet name (not supporting multiple sheets)
if (($infileType == 'Excel5') or ($infileType == 'Excel2007')) $objPHPExcel->getActiveSheet()->removeRow(1);

// check special keyword TA.ETL_COMMENT on cell A1, for special handling if input file
// is a sql description file to read data records from database (below is the format)
// TA.ETL_COMMENTS,DB_SERVER,DB_USER,DB_PASSWORD,DB_NAME,DB_TABLE
// user comments,servername,username,password,database,tablename
if ($objPHPExcel->getActiveSheet()->getCell('A1')=='TA.ETL_COMMENTS') {
if (pathinfo($outfile, PATHINFO_EXTENSION)!='csv') die('ERROR - db extraction to csv format only');
$ext_len = strlen(pathinfo($outfile, PATHINFO_EXTENSION)); // otherwise continue processing
$outfile = substr($outfile,0,strlen($outfile)-$ext_len-1).'_db.csv'; // append _db to filename
// update global variables for the data_extract_file to point to the new csv file generated from database
$GLOBALS['data_extract_file'] = $outfile; $GLOBALS['ext_len'] = strlen(pathinfo($outfile, PATHINFO_EXTENSION));
db_extract_csv($objPHPExcel,$outfile); // used in extraction to save db into csv, then reload csv as infile
$infileType = PHPExcel_IOFactory::identify($outfile); $objReader = PHPExcel_IOFactory::createReader($infileType);
$objReader->setReadDataOnly(true); $objPHPExcel = $objReader->load($outfile);} // load file base on detected type

if ($infile==$outfile) return; // exit if I/O files are same, check here instead of beginning to handle sql case
if (strtoupper(pathinfo($outfile, PATHINFO_EXTENSION))=='CSV') $outfileType = 'CSV'; // set output file type
else if (strtoupper(pathinfo($outfile, PATHINFO_EXTENSION))=='XLS') $outfileType = 'Excel5'; // legacy Excel'95
else if (strtoupper(pathinfo($outfile, PATHINFO_EXTENSION))=='XLSX') $outfileType = 'Excel2007'; // new Excel'07
else if (strtoupper(pathinfo($outfile, PATHINFO_EXTENSION))=='HTML') $outfileType = 'HTML'; // HTML format
// skip below PDF as it requires either tcPDF, DomPDF or mPDF libraries, and need to be installed separately
// else if (strtoupper(pathinfo($outfile, PATHINFO_EXTENSION))=='PDF') $outfileType = 'PDF'; // PDF format
else die("ERROR - unsupported output format " . $outfile . "\n");
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, $outfileType);
if ($outfileType == 'CSV') $objWriter->setUseBOM(true);  $objWriter->save($outfile);}

// for extracting from database to save as csv file
function db_extract_csv($sql_description,$csv_outfile) {
$DB_SERVER = $sql_description->getActiveSheet()->getCell('B2');
$DB_USER = $sql_description->getActiveSheet()->getCell('C2');
$DB_PASSWORD = $sql_description->getActiveSheet()->getCell('D2');
$DB_NAME = $sql_description->getActiveSheet()->getCell('E2');
$DB_TABLE = $sql_description->getActiveSheet()->getCell('F2');
$DB_QUERY = "SELECT * FROM " . $DB_TABLE; // limit scope to read-only

if (($DB_SERVER=="") or ($DB_USER=="") or ($DB_PASSWORD=="") or ($DB_NAME=="") or ($DB_TABLE==""))
die("ERROR - info missing in input sql description file\n");

$db_con = @mysqli_connect($DB_SERVER, $DB_USER, $DB_PASSWORD, $DB_NAME);
if (mysqli_connect_errno()) die("ERROR - " . mysqli_connect_error() ."\n");
$db_result = mysqli_query($db_con, $DB_QUERY);

if (empty($db_result) or (mysqli_num_rows($db_result) == 0))
die("ERROR - nothing found in database from SQL query\n");

$db_num_fields = mysqli_num_fields($db_result); $db_header = array();
for ($curr_col = 0; $curr_col < $db_num_fields; $curr_col++)
$db_header[] = mysqli_fetch_field($db_result)->name;

$db_csv = fopen($csv_outfile, 'w'); fputcsv($db_csv, $db_header);
while($db_row = mysqli_fetch_assoc($db_result)) fputcsv($db_csv, $db_row);
fclose($db_csv); mysqli_close($db_con);}

?>
