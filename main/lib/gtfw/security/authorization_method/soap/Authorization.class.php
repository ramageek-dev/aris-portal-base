<?php

class Authorization extends Database implements Authorizable {
   public $mUserId, $mApplicationId;

   function __construct($connectionNumber = 0) {
      $this->mSqlFile = Configuration::Instance()->GetValue('application', 'gtfw_base') .
         'main/lib/gtfw/security/authorization_method/soap/authorization.sql.php';
      parent::__construct($connectionNumber);
   }


   function SetUserId($UserId) {
      $this->mUserId = $UserId;
   }

   function SetApplicationId($AppId) {
      $this->mApplicationId = $AppId;
   }

   function IsAllowedToAccess($module, $subModule, $action, $type) {
      $result = $this->Open($this->mSqlQueries['allowed_to_access'],
         array($this->mApplicationId, $this->mUserId, $module, $subModule, $action, $type));
      if (!$result)
         return FALSE;

      $allowance = $result[0]['Allowance'];
      $access = $result[0]['Access'];
      if ($allowance > 0 || $access == 'All') {
         return TRUE;
      } else {
         return FALSE;
      }
   }

}

?>