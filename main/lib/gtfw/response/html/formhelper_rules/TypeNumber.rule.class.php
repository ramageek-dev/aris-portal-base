<?php

require_once Configuration::Instance()->GetValue( 'application', 'gtfw_base').'main/lib/gtfw/response/html/formhelper_rules/ValidationRuleBase.class.php';

class TypeNumber extends ValidationRuleBase {
   function test($test_value) {
      $regexp = '/\d+/';
      if ($test_value != '') {
         SysLog::Log('TypeNumber('.$this->mElementName.'): Field is not empty: '.$test_value, 'formhelper');
         if (!preg_match($regexp, $test_value, $matches)) {
               $this->setError('Field '.$this->mElementName.' harus berupa angka');
               return false;
         } else
            return true;
      } else {
         SysLog::Log('TypeNumber('.$this->mElementName.'): Field is empty', 'formhelper');
         return true;
      }
   }

   // js functionality
   function getCustomError() {
      return 'Field '.$this->mElementName.' harus berupa angka';
   }

   public function getRuleName() {
      return 'type_number';
   }
}

?>