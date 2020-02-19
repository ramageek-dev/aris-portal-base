<?php

// FormHelper support
require_once Configuration::Instance()->GetValue('application', 'gtfw_base') . 'main/lib/gtfw/response/html/FormHelper.class.php';

require_once Configuration::Instance()->GetValue('application', 'gtfw_base') . 'main/lib/gtfw/response/json/JsonUtils.class.php';

/**
 * Will implement some trivial method as seen on xajax and
 * previous prototype of ajax implementation in gtfw provided by Didit Ahendra
 * @author Akhmad Fathonih
 */

abstract class JsonResponse implements ResponseIntf {
   private $mFormHelpers = array();

   function __construct() {
   }

   public function registerFormHelper($objFormHelper) {
      $this->mFormHelpers[] = $objFormHelper;
   }

   final function Send() {
      $result = $this->ProcessRequest();
      $dbMsg = SysLog::Instance()->getAllError();
      if(isset($result["exec"]) && !empty($dbMsg)){
      	$result["exec"] = "message=".json_encode($dbMsg).";for(var msg in message){console.log(message[msg]);};".$result["exec"];
      }elseif(isset($result["exec"]) && empty($dbMsg)){
      	$result["exec"] = "if(console.clear != undefined)console.clear(); if(window.clear != undefined) window.clear();".$result["exec"];
      }elseif(!isset($result["exec"]) && !empty($dbMsg)){
      	$result["exec"] = "message=".json_encode($dbMsg).";for(var msg in message){console.log(message[msg]);};".$result;
      }
      
      $result = $this->returnJSON($result);
      
      echo "$result";
   }

   protected function returnJSON($value) {
      $result = JsonUtils::Instance()->VarToJSON($value);
      //echo "returnJSON: $result";

      return $result;
   }

   /**
    * override this and return any kind of PHP value. it will be converted to JSON
    * by AjaxReponse internally before handed over to user
    */
   abstract function ProcessRequest();

   function &GetHandler() {
      return $this;
   }
}


interface AjaxExtIntf {
   function GetJsCode();
}

?>