<?php
require_once Configuration::Instance()->GetValue('application', 'gtfw_base') . 'main/lib/ExcelReader/oleread.inc';
require_once Configuration::Instance()->GetValue('application', 'gtfw_base') . 'main/lib/ExcelReader/reader.php';

// you still can use Spreadsheet_Excel_Reader
// this for your convenience only for not having to type long class name
class ExcelReader extends Spreadsheet_Excel_Reader {

}
?>
