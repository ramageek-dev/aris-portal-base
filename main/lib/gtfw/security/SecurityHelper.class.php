<?php
class SecurityHelper extends Database {

   function __construct($connectionNumber = 0) {
      $this->mSqlFile = Configuration::Instance()->GetValue('application', 'gtfw_base') .
         'main/lib/gtfw/security/adodb/'.Configuration::Instance()->GetValue('application', 'db_conn','0','db_type').'/security_helper.sql.php';
      parent::__construct($connectionNumber);
   }

   function IsProtocolCheckPassed($module, $subModule, $action, $type) {
      $result = $this->Open($this->mSqlQueries['get_need_ssl'], array($module, $subModule, $action, $type));

      if ($result !== FALSE && $result[0]['NeedSsl'] == 'Yes') {
         return isset($_SERVER['HTTPS']);
      } elseif ($result !== FALSE && $result[0]['NeedSsl'] == 'No') {
         return !isset($_SERVER['HTTPS']);
      } else {
         return TRUE;
      }
   }
}
?>
