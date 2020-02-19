<?php

class ShmConfigHandler extends ConfigHandlerBase {
   private $mConfigFilenameBase = '.conf.php';
   var $mValues;

   function Load($configFilename = '') {
      if (!class_exists('SharedMemory')) {
         require_once Configuration::Instance()->GetValue('application', 'gtfw_base') . 'main/lib/gtfw/shared_memory/SharedMemory.class.php';
      }

      if (SharedMemory::Instance()->VarIsSet($configFilename)) {
         $var_name = basename($configFilename, $this->mConfigFilenameBase);
         $res = array($var_name, SharedMemory::Instance()->Get($configFilename));

         return $res;
      }
      else
         return array(false, false);
   }
}
?>