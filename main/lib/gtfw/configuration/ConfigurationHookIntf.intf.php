<?php
interface ConfigurationHookIntf {
   // return all configuration hook as an array in the form of
   // array('config_name1' => array('config_key1', 'config_key2', ...),
   //       'config_name2' => array('config_key1::subconfig_key1',
   //            'config_key2::subconfig_key2::subsubconfig_key2', ...),
   //       ...
   // )
   // use "::" between each key to separate subkeys
   // example:
   //   $application['db_conn'][0]['db_driv']
   //   $application['db_conn'][0]['db_type']
   //     would be
   //   array('application' => array ('db_conn::0::db_driv', 'db_conn::0::db_type'))
   public function GetConfigurationHooks();
   // this would be the dispatcher for the hook
   // you can use "switch" or "if" statement as you like
   public function RunConfigurationHook($configName, $configKey);
}
?>
