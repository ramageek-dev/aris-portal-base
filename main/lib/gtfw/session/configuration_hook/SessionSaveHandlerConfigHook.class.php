<?php
class SessionSaveHandlerConfigHook implements ConfigurationHookIntf {
   // implementing ConfigurationHookIntf interface
   function GetConfigurationHooks() {
      return array('application' => array('session_save_handler'));
   }

   function RunConfigurationHook($configName, $configKey) {
      if ($configName == 'application' && $configKey == 'session_save_handler') {
         return Session::Instance()->GetSaveHandler();
      } else {
         return NULL;
      }
   }
}
?>