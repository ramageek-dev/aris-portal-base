<?php

/**
   How to write a validation rule? Just test $this->mTestValue
 */

require_once Configuration::Instance()->GetValue('application', 'gtfw_base') .
   'main/lib/gtfw/response/html/formhelper_rules/ValidationRuleBase.class.php';

class TypeEmail extends ValidationRuleBase {
   function test($test_value) {
      SysLog::Log('TypeEmail: testing TestValue: "'.$test_value.'"', 'formhelper');
      if (filter_var($test_value, FILTER_VALIDATE_EMAIL) === FALSE) {
         SysLog::Log('TypeEmail: Field '.$this->mElementName.' harus berupa alamat email', 'formhelper');
         $this->setError('Field '.$this->mElementName.' harus berupa alamat email');
         return false;
      } else {
         SysLog::Log('TypeEmail: Field '.$this->mElementName.' adalah alamat email', 'formhelper');
         return true;
      }
   }

   // js functionality
   function getCustomError() {
      return 'Field ' . $this->mElementName . ' harus berupa alamat email';
   }

   public function getRuleName() {
      return 'type_email';
   }
}

?>