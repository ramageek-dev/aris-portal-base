<?php
class IniConfigHandler extends ConfigHandlerBase {
   var $mConfigFilenameBase = '.conf.ini';

   function Load($configFilename = '') {
      if (!file_exists($this->mConfigDirectory . $configFilename))
         return array(false, false);

      $var_name = basename($configFilename, $this->mConfigFilenameBase);
      $lines = @file($this->mConfigDirectory . $configFilename);
      if ($lines !== FALSE) {
         $config = array();
         $line_count = count($lines);
         for ($i = 0; $i < $line_count; $i++) {
            $lines[$i] = trim($lines[$i]);
            // doubtful regex, please use with caution
            // toni: what about: /^(.*)?=(.*)?\n$/
            if (preg_match("/^([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)(\s*)(=){1}(\s*)(.*){0,1}/", $lines[$i], $parts) > 0) {
               $config[trim($parts[1])] = $parts[5];
            }
         }
         //var_dump($config);
         return array($var_name, $config);
      }
      return array($var_name, array());
   }
}
?>