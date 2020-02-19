<?php
class Response extends ResponseBase {
   var $mrSoapResponse;

  function Response($filePath, $className) {
      parent::ResponseBase($filePath, $className);

      require_once Configuration::Instance()->GetValue( 'application', 'gtfw_base') . 'main/lib/nusoap/nusoap.php';
      require_once Configuration::Instance()->GetValue( 'application', 'gtfw_base') . 'main/lib/gtfw/response/nusoap/NusoapResponse.class.php';
      require_once $this->mFilePath;
      eval('$this->mrSoapResponse =& new '. $this->mClassName . '();');
   }

   function &GetHandler() {
      return $this->mrSoapResponse;
   }

   function Send() {
      global $HTTP_RAW_POST_DATA;

      $this->mrSoapResponse->service($HTTP_RAW_POST_DATA);
   }
}
?>
