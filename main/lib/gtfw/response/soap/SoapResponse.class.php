<?php
if (extension_loaded('soap')) { // use built in extension
   require_once Configuration::Instance()->GetValue('application', 'gtfw_base') .
      'main/lib/gtfw/response/soap/PhpResponse.class.php';

   class SoapResponse extends PhpResponse {

   }
} else { // use NuSOAP
   require_once Configuration::Instance()->GetValue('application', 'gtfw_base') .
      'main/lib/gtfw/response/soap/NuSoapResponse.class.php';

   class SoapResponse extends NuSoapResponse {

   }
}
?>