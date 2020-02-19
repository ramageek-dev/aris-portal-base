<?php
abstract class CliResponse implements ResponseIntf {

   function __construct() {
   }

   final function GetParameters() {
      return unserialize($GLOBALS['serialized_params']);
   }

   // nothing is returned, everything is echoed!
   abstract function ProcessRequest();

   final function Send() {
      if (strtolower(substr(php_sapi_name(), 0, 3)) != 'cli') {
         echo 'This module can not be run from a web server!';
         return FALSE;
      }

      $this->ProcessRequest();
   }

   final function &GetHandler() {
      return $this;
   }
}
?>