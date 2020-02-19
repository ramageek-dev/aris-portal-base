<?php
define('FPDF_FONTPATH', Configuration::Instance()->GetValue('application', 'gtfw_base') .
   'main/lib/fpdf/font/');

require_once Configuration::Instance()->GetValue('application', 'gtfw_base') .
   'main/lib/fpdf/fpdf.php';

// beware not to conflict method names with FPDP since this reponse is direct descendant of FPDF
class PdfResponse extends FPDF implements ResponseIntf {
   // see fpdf manual for further information on
   // this property, default to 'I' (inline)
   var $mDestination = 'I';

   function PdfResponse($orientation = 'P', $unit = 'mm', $format = 'A4') {
      parent::FPDF($orientation, $unit, $format);
   }

   // default to filename.xls
   // if you want something else then you should
   // override this method
   function GetFileName() {
      return 'filename.pdf';
   }

   function ProcessRequest() {
      echo 'PdfResponse->ProcessRequest(): This function must be overrided!';
      return NULL;
   }

   // overrided, see the original method in fpdf.php
   function Output($name = '', $dest = '') {
      $this->ProcessRequest();
      parent::Output($name, $dest);
   }

   function Send() {
      $this->Output($this->GetFileName(), $this->mDestination);
   }

   function &GetHandler() {
      return $this;
   }
}
?>