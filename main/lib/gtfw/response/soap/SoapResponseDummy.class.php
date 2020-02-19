<?php
class SoapResponseDummy {
   private $mSoapObject;

   function __construct($soapObj) {
      $this->mSoapObject = $soapObj;
   }

   public function __call($methodName, $paramArray) {
      return call_user_func_array(array(&$this->mSoapObject, 'Service' . $methodName), $paramArray);
   }
}

?>