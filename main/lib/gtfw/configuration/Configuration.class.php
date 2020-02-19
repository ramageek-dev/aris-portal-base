<?php

// helper function
function GtfwCfg($config_name, $key) {
   return Configuration::Instance()->GetValue($config_name, $key);
}
//

class Configuration {
   static $mrInstance;

   private $mHandlers = array();
   private $mValues = array();
   private $mConfigDirectory = 'config/';
   private $mConfigurationHooks = array();
   private $gtfwVersion = "Gtfw 3.2 - AIO(All In One)";

   private function __construct() {
   }

   function Load($configFilename, $handler = 'default') {
      // lazy load!
      if (isset($this->mHandlers[$configFilename.'::'.$handler]))
         return TRUE;

      $this->mHandlers[$configFilename.'::'.$handler] = array( 'handler' => $handler, 'config_file' => $configFilename );

      $handler_class = str_replace(' ', '', ucwords(str_replace('_', ' ', $handler)));
      require_once dirname(__FILE__) . '/configuration_handler/' . 'ConfigHandlerBase.class.php';
      require_once dirname(__FILE__) . '/configuration_handler/' . $handler .
         '/' . $handler_class . 'ConfigHandler.class.php';
      eval("\$config_handler = new {$handler_class}ConfigHandler('" . $this->mConfigDirectory . "');");

      // every config handler should return two values
      // one is the config name ($config_name), it is used to assign
      // config values and the other is config values ($config_values)
      // in the form of array
      // thus, you can query config values in the same way as default
      // handler, i.e. $cfg->mSomeConfig['some_value']
      list($config_name, $config_values) = $config_handler->Load($configFilename);

      if ($config_name === false)
         return FALSE; // die die die!!!

      $config_name = strtolower($config_name);
      $this->mValues[$config_name] = $config_values;
   }

   // you can dive to the deepest key to get config value directly
   // for example:
   //   GetValue('application', 'db_conn', 0, 'db_driv')
   // will get $application['db_conn'][0]['db_driv'] directly
   // or you can get all values by its config name
   // for example:
   //   GetValue('application') is same effect as GetAllValue('application')
   //
   // config_name is the only mandatory parameter!
   function GetValue($configName) {
   	
      $lower_config_name = strtolower($configName);
      $str_config_key = '';
      $hook_str_config_key = '';
      $num_args = func_num_args();
      if ($num_args > 1) {
      	
         for ($i = 1; $i < $num_args; $i++) {
         	if(is_array(func_get_arg($i))){
         		$data = func_get_arg($i);
         		for($j = 0; $j<sizeof($data);$j++){
         			$str_config_key[] = $data[$j];
         			$hook_str_config_key .= '::' . addslashes($data[$j]);
         		}
         	}else{
	            $str_config_key[] = func_get_arg($i);
	            $hook_str_config_key .= '::' . addslashes(func_get_arg($i));
         	}
         }
      	
         $hook_str_config_key = '\'' . substr($hook_str_config_key, 2) . '\'';
      }
      $str_config_key = '["'.implode('"]["',$str_config_key).'"]';
      
      $values  = array();
      // first, get the values
      eval('if(isset($this->mValues[$lower_config_name]' . $str_config_key . ')) $values = $this->mValues[$lower_config_name]' . $str_config_key . ';');
     

      // then, check for configuration hooks
      if (is_array($values)) {
         // recurse

         if (isset($this->mConfigurationHooks[$lower_config_name])) {

            foreach ($this->mConfigurationHooks[$lower_config_name] as $str_keys => $hook_obj) {
               if ($hook_str_config_key != '' &&
                   strpos($str_keys, substr($hook_str_config_key, 1, -1)) === FALSE) {
                  continue;
               }

               $str_keys_remain = ($hook_str_config_key != '') ? substr(str_replace(substr($hook_str_config_key, 1, -1),
                  '', $str_keys), 2) : $str_keys;
               $keys_split = explode('::', $str_keys_remain);
               $str_tmp = '';
               for ($i = 0; $i < count($keys_split); $i++) {
                  $str_tmp .= '[$keys_split[' . $i . ']]';
               }

               eval('$values' . $str_tmp . ' = $hook_obj->RunConfigurationHook($lower_config_name, $str_keys);');

            }
         }
      } else {
         eval('$hook_is_set = isset($this->mConfigurationHooks[$lower_config_name]' .
            '['. $hook_str_config_key . ']);');
         if ($hook_is_set) {
            eval('$values = $this->mConfigurationHooks[$lower_config_name]' .
            '['. $hook_str_config_key . ']->RunConfigurationHook($lower_config_name, ' .
            $hook_str_config_key . ');');
         }
      }

      return $values;
   }

   // this soon will be deprecated!
   function GetAllValue($config_name) {
      if (!isset($this->mValues[strtolower($config_name)])) {
         return NULL;
      } else {
         $values = $this->mValues[strtolower($config_name)];
         if (isset($this->mConfigurationHooks[strtolower($config_name)]) &&
             !empty($this->mConfigurationHooks[strtolower($config_name)])) {
            foreach ($this->mConfigurationHooks[strtolower($config_name)] as $data => $obj) {
               $data_split = explode('::', $data);
               $str_config_key = '[$data_split[0]]';
               $hook_str_config_key = '[strtolower($config_name)][\''. $data_split[0];
               for ($i = 1; $i < count($data_split); $i++) {
                  if (!is_numeric($data_split[$i])) {
                     $str_config_key .= '[\'' . addslashes($data_split[$i]) . '\']';
                  } else {
                     $str_config_key .= '[' . $data_split[$i] . ']';
                  }
                  $hook_str_config_key .= '::' . $data_split[$i];
               }
               $hook_str_config_key .= '\']';
               $str_run_parameter = substr($hook_str_config_key, 27, -1);
               eval('$values' . $str_config_key . ' = $this->mConfigurationHooks' .
                  $hook_str_config_key . '->RunConfigurationHook(strtolower($config_name), ' .
                  $str_run_parameter . ');');
            }
         }

         return $values;
      }
   }

   function IsExist($config_name, $key) {
      if (isset($this->mValues[strtolower($config_name)]))
         return array_key_exists($key, $this->mValues[strtolower($config_name)]);
      else
         return FALSE;
   }

   private function SetValue($config_name, $key, $value) {
      if (!isset($this->mValues[strtolower($config_name)][$key]))
         return FALSE;
      else
         return $this->mValues[strtolower($config_name)][$key] = $value;
   }
   
   protected function addNew($config_name, $key, $value){
   	if(!$this->IsExist($config_name, $key)){
   		$this->mValues[strtolower($config_name)][$key] = $value;
   	}
   }

   function RegisterHook(ConfigurationHookIntf $hookObj) {
      $hooks = $hookObj->GetConfigurationHooks();

      if (empty($hooks)) {
         return FALSE;
      }

      foreach ($hooks as $config_name => $config_keys) {
         if (!$config_name) {
            continue;
         }

         if (!isset($this->mConfigurationHooks[strtolower($config_name)])) {
            $this->mConfigurationHooks[strtolower($config_name)] = array();
         }

         foreach ($config_keys as $value) {

            if (!isset($this->mConfigurationHooks[strtolower($config_name)][$value])) {

               $this->mConfigurationHooks[strtolower($config_name)][$value] = $hookObj;

            }
         }
      }

      return TRUE;
   }

   function IsHookRegistered($configName, $configKey) {
      if (isset($this->mConfigurationHooks[strtolower($configName)][$configKey])) {
         return TRUE;
      }

      return FALSE;
   }

   function SetConfigDirectory($value) {
      $this->mConfigDirectory = $value;
   }
   
   function getVersion(){
   	return $this->gtfwVersion;
   }

   function GetTempDir() {
	   $temp_dir = '';
      // begin guessing default temporary directory
      if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
         $temp_dir = $temp_dir ? $temp_dir : getenv('TMP');
         $temp_dir = $temp_dir ? $temp_dir : getenv('TEMP');
      } else {
         $temp_dir = $temp_dir ? $temp_dir : getenv('TMPDIR');
         $temp_dir = $temp_dir ? $temp_dir : getenv('TMP');
         $temp_dir = $temp_dir ? $temp_dir : getenv('TEMP');
      }
      // last effort
      if (!$temp_dir) {
         $temp_file = tempnam(sys_get_temp_dir(), 'na');
         unlink($temp_file);
         $temp_dir = dirname($temp_file);
      }

      return $temp_dir;
   }

   static function Instance() {
      if (!isset(self::$mrInstance))
         self::$mrInstance = new Configuration();

      return self::$mrInstance;
   }
}

?>
