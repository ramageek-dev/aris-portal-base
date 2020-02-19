<?php
class DispatcherHelper extends Database {

   function __construct($connectionNumber = 0) {
      $this->mSqlFile = Configuration::Instance()->GetValue('application', 'gtfw_base') . 'main/lib/gtfw/dispatcher/dispatcher_helper.sql.php';
      parent::__construct($connectionNumber);
   }

   function TranslateRequestToLong($moduleId) {
      $result = $this->Open($this->mSqlQueries['translate_request'], array($moduleId));

      return ($result !== FALSE) ? array($result[0]['Module'], $result[0]['SubModule'],
         $result[0]['Action'], $result[0]['Type']) : $result;
   }

   function TranslateRequestToShort($module, $subModule, $action, $type) {
      $module_id = $this->Open($this->mSqlQueries['get_module_id'],
         array($module, $subModule, $action, $type));

      return ($module_id !== FALSE) ? $module_id[0]['ModuleId'] : $module_id;
   }
}
?>