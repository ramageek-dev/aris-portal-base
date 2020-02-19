<?php
class ConfigHandlerBase {
   var $mConfigDirectory;
   // redefine this property in subclass
   private $mConfigFilenameBase;

   function __construct($configDirectory = '') {
      $this->mConfigDirectory = $configDirectory;
   }

   function Load() {
      echo 'ConfigHandlerBase->Load(): This function must be overrided!';
   }
}
?>