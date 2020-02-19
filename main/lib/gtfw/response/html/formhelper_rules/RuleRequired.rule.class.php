<?php

/**
   How to write a validation rule? Just test $this->mTestValue
 */

require_once Configuration::Instance()->GetValue( 'application', 'gtfw_base').'main/lib/gtfw/response/html/formhelper_rules/ValidationRuleBase.class.php';

class RuleRequired extends ValidationRuleBase {
   function test($test_value) {
      if (trim($test_value) == '') {
         SysLog::Log('RuleRequired: Field '.$this->mElementName.' harus diisi', 'formhelper');
         $this->setError('Field '.$this->mElementName.' harus diisi');
         return false;
      } else {
         SysLog::Log('RuleRequired: Field '.$this->mElementName.' is not empty', 'formhelper');
         return true;
      }
   }

   public function getRuleName() {
      return 'required_input';
   }
}

?>