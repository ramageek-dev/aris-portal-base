<?php
class XlsResponse implements ResponseIntf {
   // use of new library is encouraged as it supports BIFF8 instead of BIFF5 (< Excel 97)
   // useful for text larger than 255 chars
   // NOTE: please read the documentation at http://pear.php.net/package/Spreadsheet_Excel_Writer
   // NOTE TOO: it is indeed a hack to use this library since no PEAR is actually installed
   protected $mUseNewLibrary = false;

   protected $mrWorkbook;
   // default worksheet
   // redefine if you want something else
   protected $mWorksheets = array('Sheet1', 'Sheet2', 'Sheet3');

   protected $mDestination = 'inline'; // this could be inline or attachment

   function __construct() {
      if ($this->mUseNewLibrary) {
         require_once Configuration::Instance()->GetValue('application', 'gtfw_base') .
            'main/lib/WriteExcel/Writer.php';

         $this->mrWorkbook = new Spreadsheet_Excel_Writer_Workbook('-');
         $this->mrWorkbook->setVersion(8); // BIFF8
         if (!empty($this->mWorksheets) && is_array($this->mWorksheets)) {
            $array_temp = array();
            foreach ($this->mWorksheets as $key => $value) {
               $array_temp[$value] = $this->mrWorkbook->addWorksheet($value);
            }
            // reassign previously defined sheetname, so you can refer a worksheet
            // by calling $this->mWorksheets['Sheet1'], etc. later
            $this->mWorksheets = $array_temp;
         }
      } else {
         require_once Configuration::Instance()->GetValue('application', 'gtfw_base') .
            'main/lib/WriteExcel/Worksheet.php';
         require_once Configuration::Instance()->GetValue('application', 'gtfw_base') .
            'main/lib/WriteExcel/Workbook.php';

      $this->mrWorkbook =& new Workbook('-');
      //$this->mrWorkbook = new Spreadsheet_Excel_Writer_Workbook('-');
      if (!empty($this->mWorksheets) && is_array($this->mWorksheets)) {
         $array_temp = array();
         foreach ($this->mWorksheets as $key => $value) {
            $array_temp[$value] =& $this->mrWorkbook->add_worksheet($value);
         }
         // reassign previously defined sheetname, so you can refer a worksheet
         // by calling $this->mWorksheets['Sheet1'], etc. later
         $this->mWorksheets = $array_temp;
         }
      }
   }

   // default to filename.xls
   // if you want something else then you should
   // override this method
   function GetFileName() {
      return 'filename.xls';
   }

   function ProcessRequest() {
      echo 'XlsResponse->ProcessRequest(): This function must be overrided!';
      return NULL;
   }

   function &GetHandler() {
      return $this;
   }

   function Send() {
      header('Content-type: application/vnd.ms-excel');
      header('Content-Disposition: ' . $this->mDestination . '; filename=' . $this->GetFileName());
      header('Pragma: public');
      $this->ProcessRequest();
      $this->mrWorkbook->close();
   }
}
?>
