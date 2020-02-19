<?php
class DefaultConfigHandler extends ConfigHandlerBase {
   private $mConfigFilenameBase = '.conf.php';
   var $mValues;

   function Load($configFilename = '') {
      if (!file_exists($this->mConfigDirectory . $configFilename))
         return array(false, false);

      include $this->mConfigDirectory . $configFilename;
      $var_name = basename($configFilename, $this->mConfigFilenameBase);
      
      $res= array($var_name, ${$var_name});
      
      /*
       * For php 5.5.x mysql_connect not available so, driver force to mysqli
       * Modified by Dyan Galih Nugroho Wicaksi(dyan.galih@gmail.com)
       */
      
      if(!function_exists('mysql_connect') && $var_name=='application'){
      	for ($i = 0; $i < sizeof($res[1]['db_conn']); $i++) {
      		if(isset($res[1]['db_conn'][$i]["db_type"])){
      			if($res[1]['db_conn'][$i]["db_type"]=="mysqlt"){
      				$res[1]['db_conn'][$i]["db_type"]="mysqli";
      			}
      		}
      	}
   	}
      
      return $res;
   }
}
?>