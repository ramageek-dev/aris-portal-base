<?php
class ErrorResponse extends SoapResponse {
   function invoke_method() {
      $this->fault('Error', $_GET['error_info'][0] . ' ' . $_GET['error_info'][1] . ' ' .
         $_GET['error_info'][2] . ' ' . $_GET['error_info'][3]);
   }
}
?>