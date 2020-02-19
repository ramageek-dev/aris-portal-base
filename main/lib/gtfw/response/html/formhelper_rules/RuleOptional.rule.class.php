<?php

require_once Configuration::Instance()->GetValue( 'application', 'gtfw_base').'main/lib/gtfw/response/html/formhelper_rules/ValidationRuleBase.class.php';

class RuleOptional extends ValidationRuleBase {
   function test($test_value) {
      return true;
   }

   public function getRuleName() {
      return 'optional_input';
   }

   function getCustomError() {
      return false;
   }
}

?>