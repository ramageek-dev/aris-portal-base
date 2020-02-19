<?php

class GTFWConfiguration {
   static $mHandlers = array();
   static $mValues = array();
   static $mConfigDirectory = 'config/';

   private function __construct() {
      die("This is a singleton, no need to instantiate this class");
   }

   static function Load($configFilename, $handler = 'default') {
      // lazy load!
      if (isset(self::$mHandlers[$configFilename.'::'.$handler]))
         return true;

      self::$mHandlers[$configFilename.'::'.$handler] = array( 'handler' => $handler, 'config_file' => $configFilename );

      $handler_class = str_replace(' ', '', ucwords(str_replace('_', ' ', $handler)));
      require_once dirname(__FILE__).'/configuration_handler/' . 'ConfigHandlerBase.class.php';
      require_once dirname(__FILE__).'/configuration_handler/' . $handler .
         '/' . $handler_class . 'ConfigHandler.class.php';
      eval("\$config_handler = new {$handler_class}ConfigHandler('".self::$mConfigDirectory."');");
      //echo("\$config_handler =& new {$handler_class}ConfigHandler(\$mConfigDirectory);");
      //var_dump($config_handler);
      // every config handler should return two values
      // one is the config name ($config_name), it is used to assign
      // config values and the other is config values ($config_values)
      // in the form of array
      // thus, you can query config values in the same way as default
      // handler, i.e. $cfg->mSomeConfig['some_value']
      list($config_name, $config_values) = $config_handler->Load($configFilename);

      if ($config_name === false)
         return false; // die die die!!!

      $config_name = strtolower($config_name);
      self::$mValues[$config_name] = $config_values;
      //var_dump( $config_values );
   }

   static function GetValue($config_name, $key) {
   	return Configuration::Instance()->GetValue($config_name,$key);
   	
//    	return Configuration::Instance()->GetValue($configName,$key);
      if(is_array($key)){
         $keyNew = '';
         for($i=0;$i<count($key);$i++){
             $keyNew .= '[$key['.$i.']]';
         }
         //die($keyNew);
         eval('$result = self::$mValues[strtolower($config_name)]'.$keyNew.';');
        return $result;
      }
      if (!isset(self::$mValues[strtolower($config_name)]))
         return false;
      else
         return self::$mValues[strtolower($config_name)][$key];
   }

   static function IsExist($config_name, $key) {
      if (isset(self::$mValues[strtolower($config_name)]))
         return array_key_exists($key, self::$mValues[strtolower($config_name)]);
      else
         return false;
   }

   static function SetValue($config_name, $key, $value) {
      if (!isset(self::$mValues[strtolower($config_name)]))
         return false;
      else
         return self::$mValues[strtolower($config_name)][$key] = $value;
   }

   static function SetConfigDirectory($value) {
      self::$mConfigDirectory = $value;
   }
}

?>
