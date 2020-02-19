<?php
class BasedirConfigHook implements ConfigurationHookIntf {
   // implementing ConfigurationHookIntf interface
   function GetConfigurationHooks() {
      return array('application' => array('basedir'));
   }

   function RunConfigurationHook($configName, $configKey) {
      if ($configName == 'application' && $configKey == 'basedir') {
         return Session::Instance()->GetSessionBaseDir();
      } else {
         return NULL;
      }
   }
}
?>